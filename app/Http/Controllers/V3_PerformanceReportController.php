<?php
namespace App\Http\Controllers;

use App\Exports\PerformanceReportExport;
use App\Services\PerformanceCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class V3_PerformanceReportController extends Controller
{
    protected $calculationService;

    /**
     * Constructor with dependency injection for the calculation service
     */
    public function __construct(PerformanceCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Display the report selection form
     */
    public function index()
    {
        // Get available reporting periods
        $reportingPeriods = DB::table('ecsahc_timelines')
            ->select('id', 'ReportName', 'Year', 'Type')
            ->orderBy('Year', 'desc')
            ->orderBy('ClosingDate', 'desc')
            ->get();

        // Get available clusters
        $clusters = DB::table('clusters')
            ->select('id', 'ClusterID', 'Cluster_Name')
            ->orderBy('Cluster_Name')
            ->get();

        // Get strategic objectives
        $strategicObjectives = DB::table('strategic_objectives')
            ->select('id', 'StrategicObjectiveID', 'SO_Name', 'Description')
            ->orderBy('SO_Number')
            ->get();

        return view('scrn', [
            'Page'                => 'V3_Reports.index',
            'reportingPeriods'    => $reportingPeriods,
            'clusters'            => $clusters,
            'strategicObjectives' => $strategicObjectives,
        ]);
    }

    /**
     * Generate cluster performance report
     */
    public function getClusterPerformanceReport(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'cluster_id'   => 'required|string|exists:clusters,ClusterID',
            'reporting_id' => 'required|string|exists:ecsahc_timelines,ReportingID',
        ]);

        $clusterId   = $validated['cluster_id'];
        $reportingId = $validated['reporting_id'];

        // Get reporting period details
        $reportingPeriod = DB::table('ecsahc_timelines')
            ->where('ReportingID', $reportingId)
            ->first();

        if (! $reportingPeriod) {
            return redirect()->back()->with('error', 'Invalid reporting period selected.');
        }

        // Get cluster details
        $cluster = DB::table('clusters')
            ->where('ClusterID', $clusterId)
            ->first();

        if (! $cluster) {
            return redirect()->back()->with('error', 'Invalid cluster selected.');
        }

        // Extract year from reporting period for target matching
        $year       = $reportingPeriod->Year;
        $targetYear = $year . '-' . ($year + 1);

        // Get performance data with targets
        $performanceData = $this->getPerformanceData($clusterId, $reportingId, $targetYear);

        // Calculate summary statistics
        $summary = $this->calculatePerformanceSummary($performanceData);

        // Group by strategic objective
        $performanceByStrategicObjective = $this->groupByStrategicObjective($performanceData);

        return view('scrn', [
            'Page'                            => 'V3_Reports.cluster_performance',
            'cluster'                         => $cluster,
            'reportingPeriod'                 => $reportingPeriod,
            'performanceData'                 => $performanceData,
            'summary'                         => $summary,
            'performanceByStrategicObjective' => $performanceByStrategicObjective,
        ]);
    }

    /**
     * Generate strategic objective performance report
     */
    public function getStrategicObjectiveReport(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'so_id'        => 'required|string|exists:strategic_objectives,StrategicObjectiveID',
            'reporting_id' => 'required|string|exists:ecsahc_timelines,ReportingID',
        ]);

        $soId        = $validated['so_id'];
        $reportingId = $validated['reporting_id'];

        // Get reporting period details
        $reportingPeriod = DB::table('ecsahc_timelines')
            ->where('ReportingID', $reportingId)
            ->first();

        if (! $reportingPeriod) {
            return redirect()->back()->with('error', 'Invalid reporting period selected.');
        }

        // Get strategic objective details
        $strategicObjective = DB::table('strategic_objectives')
            ->where('StrategicObjectiveID', $soId)
            ->first();

        if (! $strategicObjective) {
            return redirect()->back()->with('error', 'Invalid strategic objective selected.');
        }

        // Extract year from reporting period for target matching
        $year       = $reportingPeriod->Year;
        $targetYear = $year . '-' . ($year + 1);

        // Get performance data for this strategic objective across all clusters
        $performanceData = $this->getSOPerformanceData($soId, $reportingId, $targetYear);

        // Calculate summary statistics
        $summary = $this->calculatePerformanceSummary($performanceData);

        // Group by cluster
        $performanceByCluster = $this->groupByCluster($performanceData);

        return view('scrn', [
            'Page'                 => 'V3_Reports.strategic_objective_performance',
            'strategicObjective'   => $strategicObjective,
            'reportingPeriod'      => $reportingPeriod,
            'performanceData'      => $performanceData,
            'summary'              => $summary,
            'performanceByCluster' => $performanceByCluster,
        ]);
    }

    /**
     * Generate indicator performance report
     */
    public function getIndicatorPerformanceReport(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'indicator_id' => 'required|string|exists:performance_indicators,Indicator_Number',
            'reporting_id' => 'required|string|exists:ecsahc_timelines,ReportingID',
        ]);

        $indicatorId = $validated['indicator_id'];
        $reportingId = $validated['reporting_id'];

        // Get reporting period details
        $reportingPeriod = DB::table('ecsahc_timelines')
            ->where('ReportingID', $reportingId)
            ->first();

        if (! $reportingPeriod) {
            return redirect()->back()->with('error', 'Invalid reporting period selected.');
        }

        // Get indicator details
        $indicator = DB::table('performance_indicators')
            ->where('Indicator_Number', $indicatorId)
            ->first();

        if (! $indicator) {
            return redirect()->back()->with('error', 'Invalid indicator selected.');
        }

        // Extract year from reporting period for target matching
        $year       = $reportingPeriod->Year;
        $targetYear = $year . '-' . ($year + 1);

        // Get performance data for this indicator across all clusters
        $performanceData = $this->getIndicatorPerformanceData($indicatorId, $reportingId, $targetYear);

        return view('scrn', [
            'Page'            => 'V3_Reports.indicator_performance',
            'indicator'       => $indicator,
            'reportingPeriod' => $reportingPeriod,
            'performanceData' => $performanceData,
        ]);
    }

    /**
     * Generate performance summary for a year
     */
    public function generatePerformanceSummary(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'year'       => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'clusters'   => 'nullable|array',
            'clusters.*' => 'exists:clusters,ClusterID',
        ]);

        $year       = $validated['year'];
        $clusterIds = $validated['clusters'] ?? [];
        $targetYear = $year . '-' . ($year + 1);

        // Get all reporting periods for the year
        $reportingPeriods = DB::table('ecsahc_timelines')
            ->where('Year', $year)
            ->where('Type', 'Quarterly Reports')
            ->orderBy('ClosingDate')
            ->get();

        if ($reportingPeriods->isEmpty()) {
            return redirect()->back()->with('error', 'No reporting periods found for the selected year.');
        }

        // Get clusters (either filtered or all)
        $clusterQuery = DB::table('clusters');
        if (! empty($clusterIds)) {
            $clusterQuery->whereIn('ClusterID', $clusterIds);
        }
        $clusters = $clusterQuery->orderBy('Cluster_Name')->get();

        // Initialize summary data structure
        $summaryData = [];
        foreach ($clusters as $cluster) {
            $summaryData[$cluster->ClusterID] = [
                'cluster_name' => $cluster->Cluster_Name,
                'periods'      => [],
                'average'      => 0,
                'trend'        => 'Stable',
            ];
        }

        // Calculate performance for each period and cluster
        foreach ($reportingPeriods as $period) {
            foreach ($clusters as $cluster) {
                $performanceData = $this->getPerformanceData($cluster->ClusterID, $period->ReportingID, $targetYear);
                $summary         = $this->calculatePerformanceSummary($performanceData);

                $summaryData[$cluster->ClusterID]['periods'][$period->ReportingID] = [
                    'period_name'                => $period->ReportName,
                    'achievement_percentage'     => $summary['average_achievement_percentage'],
                    'performance_category'       => $summary['overall_performance_category'],
                    'indicators_with_targets'    => $summary['indicators_with_targets'],
                    'indicators_missing_targets' => $summary['indicators_missing_targets'],
                ];
            }
        }

        // Calculate averages and trends
        foreach ($summaryData as $clusterId => &$data) {
            if (! empty($data['periods'])) {
                $totalPercentage = 0;
                $periodCount     = 0;
                $periodValues    = [];

                foreach ($data['periods'] as $periodData) {
                    if ($periodData['achievement_percentage'] !== null) {
                        $totalPercentage += $periodData['achievement_percentage'];
                        $periodCount++;
                        $periodValues[] = $periodData['achievement_percentage'];
                    }
                }

                $data['average'] = $periodCount > 0 ? $totalPercentage / $periodCount : null;

                // Determine trend (simple version)
                if (count($periodValues) >= 2) {
                    $firstHalf  = array_slice($periodValues, 0, floor(count($periodValues) / 2));
                    $secondHalf = array_slice($periodValues, floor(count($periodValues) / 2));

                    $firstAvg  = array_sum($firstHalf) / count($firstHalf);
                    $secondAvg = array_sum($secondHalf) / count($secondHalf);

                    $difference = $secondAvg - $firstAvg;

                    if ($difference > 5) {
                        $data['trend'] = 'Improving';
                    } elseif ($difference < -5) {
                        $data['trend'] = 'Declining';
                    } else {
                        $data['trend'] = 'Stable';
                    }
                }
            }
        }

        return view('scrn', [
            'Page'             => 'V3_Reports.annual_summary',
            'year'             => $year,
            'reportingPeriods' => $reportingPeriods,
            'clusters'         => $clusters,
            'summaryData'      => $summaryData,
        ]);
    }

    /**
     * Export report to Excel
     */
    public function exportReportToExcel(Request $request)
    {
        // Validate request
        $validated = $request->validate([
            'report_type'  => 'required|in:cluster,strategic_objective,indicator,summary',
            'cluster_id'   => 'required_if:report_type,cluster|nullable|string|exists:clusters,ClusterID',
            'so_id'        => 'required_if:report_type,strategic_objective|nullable|string|exists:strategic_objectives,StrategicObjectiveID',
            'indicator_id' => 'required_if:report_type,indicator|nullable|string|exists:performance_indicators,Indicator_Number',
            'reporting_id' => 'required_unless:report_type,summary|nullable|string|exists:ecsahc_timelines,ReportingID',
            'year'         => 'required_if:report_type,summary|nullable|integer|min:2000|max:' . (date('Y') + 1),
        ]);

        $reportType = $validated['report_type'];
        $fileName   = 'performance_report_' . date('Y-m-d') . '.xlsx';

        switch ($reportType) {
            case 'cluster':
                $clusterId       = $validated['cluster_id'];
                $reportingId     = $validated['reporting_id'];
                $reportingPeriod = DB::table('ecsahc_timelines')->where('ReportingID', $reportingId)->first();
                $year            = $reportingPeriod->Year;
                $targetYear      = $year . '-' . ($year + 1);
                $performanceData = $this->getPerformanceData($clusterId, $reportingId, $targetYear);
                $fileName        = 'cluster_performance_' . $clusterId . '_' . date('Y-m-d') . '.xlsx';
                return Excel::download(new PerformanceReportExport($performanceData, 'cluster'), $fileName);

            case 'strategic_objective':
                $soId            = $validated['so_id'];
                $reportingId     = $validated['reporting_id'];
                $reportingPeriod = DB::table('ecsahc_timelines')->where('ReportingID', $reportingId)->first();
                $year            = $reportingPeriod->Year;
                $targetYear      = $year . '-' . ($year + 1);
                $performanceData = $this->getSOPerformanceData($soId, $reportingId, $targetYear);
                $fileName        = 'so_performance_' . $soId . '_' . date('Y-m-d') . '.xlsx';
                return Excel::download(new PerformanceReportExport($performanceData, 'strategic_objective'), $fileName);

            case 'indicator':
                $indicatorId     = $validated['indicator_id'];
                $reportingId     = $validated['reporting_id'];
                $reportingPeriod = DB::table('ecsahc_timelines')->where('ReportingID', $reportingId)->first();
                $year            = $reportingPeriod->Year;
                $targetYear      = $year . '-' . ($year + 1);
                $performanceData = $this->getIndicatorPerformanceData($indicatorId, $reportingId, $targetYear);
                $fileName        = 'indicator_performance_' . $indicatorId . '_' . date('Y-m-d') . '.xlsx';
                return Excel::download(new PerformanceReportExport($performanceData, 'indicator'), $fileName);

            case 'summary':
                $year = $validated['year'];
                // Logic for summary export would go here
                $fileName = 'annual_summary_' . $year . '_' . date('Y-m-d') . '.xlsx';
                // This would need additional implementation
                return redirect()->back()->with('error', 'Summary export not yet implemented.');

            default:
                return redirect()->back()->with('error', 'Invalid report type selected.');
        }
    }

    /**
     * Get performance data for a specific cluster and reporting period
     */
    protected function getPerformanceData($clusterId, $reportingId, $targetYear)
    {
        // Get all performance mappings for this cluster and reporting period
        $performanceMappings = DB::table('cluster_performance_mappings')
            ->where('ClusterID', $clusterId)
            ->where('ReportingID', $reportingId)
            ->get();

        $result = [];

        foreach ($performanceMappings as $mapping) {
            // Get indicator details
            $indicator = DB::table('performance_indicators')
                ->where('Indicator_Number', $mapping->IndicatorID)
                ->first();

            if (! $indicator) {
                Log::warning("Indicator not found: {$mapping->IndicatorID} for cluster {$clusterId}");
                continue;
            }

            // Get strategic objective
            $strategicObjective = DB::table('strategic_objectives')
                ->where('StrategicObjectiveID', $mapping->SO_ID)
                ->first();

            // Get target for this indicator
            $target = DB::table('cluster_indicator_targets')
                ->where('ClusterID', $clusterId)
                ->where('IndicatorID', $mapping->IndicatorID)
                ->where('Target_Year', $targetYear)
                ->first();

            // Calculate achievement percentage and performance category
            $achievementPercentage = null;
            $performanceCategory   = 'N/A';
            $targetStatus          = 'Missing Target';

            if ($target) {
                $targetStatus          = 'Has Target';
                $achievementPercentage = $this->calculationService->calculateAchievementPercentage(
                    $mapping->Response,
                    $target->Target_Value,
                    $mapping->ResponseType
                );
                $performanceCategory = $this->calculationService->getPerformanceCategory($achievementPercentage);
            }

            $result[] = [
                'indicator_id'           => $mapping->IndicatorID,
                'indicator_name'         => $indicator->Indicator_Name,
                'so_id'                  => $mapping->SO_ID,
                'so_name'                => $strategicObjective ? $strategicObjective->SO_Name : 'Unknown',
                'response'               => $mapping->Response,
                'response_type'          => $mapping->ResponseType,
                'target_value'           => $target ? $target->Target_Value : null,
                'target_status'          => $targetStatus,
                'reporting_comment'      => $mapping->ReportingComment,
                'achievement_percentage' => $achievementPercentage,
                'performance_category'   => $performanceCategory,
            ];
        }

        return $result;
    }

    /**
     * Get performance data for a specific strategic objective across all clusters
     */
    protected function getSOPerformanceData($soId, $reportingId, $targetYear)
    {
        // Get all performance mappings for this strategic objective and reporting period
        $performanceMappings = DB::table('cluster_performance_mappings')
            ->where('SO_ID', $soId)
            ->where('ReportingID', $reportingId)
            ->get();

        $result = [];

        foreach ($performanceMappings as $mapping) {
            // Get indicator details
            $indicator = DB::table('performance_indicators')
                ->where('Indicator_Number', $mapping->IndicatorID)
                ->first();

            if (! $indicator) {
                Log::warning("Indicator not found: {$mapping->IndicatorID} for SO {$soId}");
                continue;
            }

            // Get cluster details
            $cluster = DB::table('clusters')
                ->where('ClusterID', $mapping->ClusterID)
                ->first();

            // Get target for this indicator
            $target = DB::table('cluster_indicator_targets')
                ->where('ClusterID', $mapping->ClusterID)
                ->where('IndicatorID', $mapping->IndicatorID)
                ->where('Target_Year', $targetYear)
                ->first();

            // Calculate achievement percentage and performance category
            $achievementPercentage = null;
            $performanceCategory   = 'N/A';
            $targetStatus          = 'Missing Target';

            if ($target) {
                $targetStatus          = 'Has Target';
                $achievementPercentage = $this->calculationService->calculateAchievementPercentage(
                    $mapping->Response,
                    $target->Target_Value,
                    $mapping->ResponseType
                );
                $performanceCategory = $this->calculationService->getPerformanceCategory($achievementPercentage);
            }

            $result[] = [
                'indicator_id'           => $mapping->IndicatorID,
                'indicator_name'         => $indicator->Indicator_Name,
                'cluster_id'             => $mapping->ClusterID,
                'cluster_name'           => $cluster ? $cluster->Cluster_Name : 'Unknown',
                'response'               => $mapping->Response,
                'response_type'          => $mapping->ResponseType,
                'target_value'           => $target ? $target->Target_Value : null,
                'target_status'          => $targetStatus,
                'reporting_comment'      => $mapping->ReportingComment,
                'achievement_percentage' => $achievementPercentage,
                'performance_category'   => $performanceCategory,
            ];
        }

        return $result;
    }

    /**
     * Get performance data for a specific indicator across all clusters
     */
    protected function getIndicatorPerformanceData($indicatorId, $reportingId, $targetYear)
    {
        // Get all performance mappings for this indicator and reporting period
        $performanceMappings = DB::table('cluster_performance_mappings')
            ->where('IndicatorID', $indicatorId)
            ->where('ReportingID', $reportingId)
            ->get();

        $result = [];

        foreach ($performanceMappings as $mapping) {
            // Get cluster details
            $cluster = DB::table('clusters')
                ->where('ClusterID', $mapping->ClusterID)
                ->first();

            // Get strategic objective
            $strategicObjective = DB::table('strategic_objectives')
                ->where('StrategicObjectiveID', $mapping->SO_ID)
                ->first();

            // Get target for this indicator
            $target = DB::table('cluster_indicator_targets')
                ->where('ClusterID', $mapping->ClusterID)
                ->where('IndicatorID', $indicatorId)
                ->where('Target_Year', $targetYear)
                ->first();

            // Calculate achievement percentage and performance category
            $achievementPercentage = null;
            $performanceCategory   = 'N/A';
            $targetStatus          = 'Missing Target';

            if ($target) {
                $targetStatus          = 'Has Target';
                $achievementPercentage = $this->calculationService->calculateAchievementPercentage(
                    $mapping->Response,
                    $target->Target_Value,
                    $mapping->ResponseType
                );
                $performanceCategory = $this->calculationService->getPerformanceCategory($achievementPercentage);
            }

            $result[] = [
                'cluster_id'             => $mapping->ClusterID,
                'cluster_name'           => $cluster ? $cluster->Cluster_Name : 'Unknown',
                'so_id'                  => $mapping->SO_ID,
                'so_name'                => $strategicObjective ? $strategicObjective->SO_Name : 'Unknown',
                'response'               => $mapping->Response,
                'response_type'          => $mapping->ResponseType,
                'target_value'           => $target ? $target->Target_Value : null,
                'target_status'          => $targetStatus,
                'reporting_comment'      => $mapping->ReportingComment,
                'achievement_percentage' => $achievementPercentage,
                'performance_category'   => $performanceCategory,
            ];
        }

        return $result;
    }

    /**
     * Calculate performance summary statistics
     */
    protected function calculatePerformanceSummary($performanceData)
    {
        $totalIndicators            = count($performanceData);
        $indicatorsWithTargets      = 0;
        $indicatorsMissingTargets   = 0;
        $totalAchievementPercentage = 0;
        $achievementCount           = 0;

        $performanceCategories = [
            'Not Performing' => 0,
            'In Progress'    => 0,
            'On Track'       => 0,
            'Met'            => 0,
            'N/A'            => 0,
        ];

        foreach ($performanceData as $data) {
            if ($data['target_status'] === 'Has Target') {
                $indicatorsWithTargets++;
                if ($data['achievement_percentage'] !== null) {
                    $totalAchievementPercentage += $data['achievement_percentage'];
                    $achievementCount++;
                }
                $performanceCategories[$data['performance_category']]++;
            } else {
                $indicatorsMissingTargets++;
                $performanceCategories['N/A']++;
            }
        }

        $averageAchievementPercentage = $achievementCount > 0 ? $totalAchievementPercentage / $achievementCount : null;
        $overallPerformanceCategory   = $this->calculationService->getPerformanceCategory($averageAchievementPercentage);

        return [
            'total_indicators'               => $totalIndicators,
            'indicators_with_targets'        => $indicatorsWithTargets,
            'indicators_missing_targets'     => $indicatorsMissingTargets,
            'average_achievement_percentage' => $averageAchievementPercentage,
            'overall_performance_category'   => $overallPerformanceCategory,
            'performance_categories'         => $performanceCategories,
        ];
    }

    /**
     * Group performance data by strategic objective
     */
    protected function groupByStrategicObjective($performanceData)
    {
        $result = [];

        foreach ($performanceData as $data) {
            $soId = $data['so_id'];

            if (! isset($result[$soId])) {
                $result[$soId] = [
                    'so_id'      => $soId,
                    'so_name'    => $data['so_name'],
                    'indicators' => [],
                    'summary'    => [
                        'total_indicators'               => 0,
                        'indicators_with_targets'        => 0,
                        'indicators_missing_targets'     => 0,
                        'total_achievement_percentage'   => 0,
                        'achievement_count'              => 0,
                        'average_achievement_percentage' => null,
                        'performance_category'           => 'N/A',
                    ],
                ];
            }

            $result[$soId]['indicators'][] = $data;
            $result[$soId]['summary']['total_indicators']++;

            if ($data['target_status'] === 'Has Target') {
                $result[$soId]['summary']['indicators_with_targets']++;
                if ($data['achievement_percentage'] !== null) {
                    $result[$soId]['summary']['total_achievement_percentage'] += $data['achievement_percentage'];
                    $result[$soId]['summary']['achievement_count']++;
                }
            } else {
                $result[$soId]['summary']['indicators_missing_targets']++;
            }
        }

        // Calculate averages and performance categories
        foreach ($result as $soId => &$soData) {
            if ($soData['summary']['achievement_count'] > 0) {
                $soData['summary']['average_achievement_percentage'] =
                    $soData['summary']['total_achievement_percentage'] / $soData['summary']['achievement_count'];
                $soData['summary']['performance_category'] =
                $this->calculationService->getPerformanceCategory($soData['summary']['average_achievement_percentage']);
            }
        }

        return $result;
    }

    /**
     * Group performance data by cluster
     */
    protected function groupByCluster($performanceData)
    {
        $result = [];

        foreach ($performanceData as $data) {
            $clusterId = $data['cluster_id'];

            if (! isset($result[$clusterId])) {
                $result[$clusterId] = [
                    'cluster_id'   => $clusterId,
                    'cluster_name' => $data['cluster_name'],
                    'indicators'   => [],
                    'summary'      => [
                        'total_indicators'               => 0,
                        'indicators_with_targets'        => 0,
                        'indicators_missing_targets'     => 0,
                        'total_achievement_percentage'   => 0,
                        'achievement_count'              => 0,
                        'average_achievement_percentage' => null,
                        'performance_category'           => 'N/A',
                    ],
                ];
            }

            $result[$clusterId]['indicators'][] = $data;
            $result[$clusterId]['summary']['total_indicators']++;

            if ($data['target_status'] === 'Has Target') {
                $result[$clusterId]['summary']['indicators_with_targets']++;
                if ($data['achievement_percentage'] !== null) {
                    $result[$clusterId]['summary']['total_achievement_percentage'] += $data['achievement_percentage'];
                    $result[$clusterId]['summary']['achievement_count']++;
                }
            } else {
                $result[$clusterId]['summary']['indicators_missing_targets']++;
            }
        }

        // Calculate averages and performance categories
        foreach ($result as $clusterId => &$clusterData) {
            if ($clusterData['summary']['achievement_count'] > 0) {
                $clusterData['summary']['average_achievement_percentage'] =
                    $clusterData['summary']['total_achievement_percentage'] / $clusterData['summary']['achievement_count'];
                $clusterData['summary']['performance_category'] =
                $this->calculationService->getPerformanceCategory($clusterData['summary']['average_achievement_percentage']);
            }
        }

        return $result;
    }
}