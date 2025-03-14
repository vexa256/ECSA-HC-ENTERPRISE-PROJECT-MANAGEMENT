<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class V2_ALL_PerformanceDashboardController extends Controller
{
    /**
     * Safely get a value from an array with proper null checking
     * @param array $array The array to access
     * @param string|int $key The key to access
     * @param mixed $default The default value if key doesn't exist
     * @return mixed The value or default
     */
    private function safeArrayGet($array, $key, $default = null)
    {
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }
        return $default;
    }

    /**
     * Show the timeline selection screen directly (skipping cluster selection).
     *
     * @return \Illuminate\View\View
     */
    public function showTimelineSelection()
    {
        try {
            Log::info('Timeline selection screen - All Clusters');

            // Fetch all available timelines.
            $timelines = DB::table('ecsahc_timelines')
                ->select('id', 'ReportName', 'Type', 'ReportingID', 'Year', 'ClosingDate', 'status')
                ->orderBy('Year', 'desc')
                ->orderBy('ClosingDate', 'desc')
                ->get();

            Log::info('Found ' . $timelines->count() . ' timelines');

            // Pass the timeline data to the view.
            return view('scrn', [
                'Page'        => 'EcsaPerfV2.all-select-timeline',
                'Title'       => 'Select Timeline',
                'Timelines'   => $timelines,
                'Error'       => $timelines->isEmpty() ? 'No reporting timelines found in the system.' : null,
                'AllClusters' => true, // Flag to indicate we're using all clusters
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching timelines: ' . $e->getMessage());
            return view('scrn', [
                'Page'  => 'EcsaPerfV2.all-select-timeline',
                'Title' => 'Select Timeline',
                'Error' => 'An error occurred while fetching timelines. Please try again later.',
            ]);
        }
    }

    /**
     * Process timeline selection and redirect to performance dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processTimelineSelection(Request $request)
    {
        try {
            $validated = $request->validate([
                'timeline_id' => 'required|string|exists:ecsahc_timelines,ReportingID',
            ]);

            $timelineId = $validated['timeline_id'];

            Log::info('Timeline selected: ' . $timelineId . ' for All Clusters');

            // Redirect to the dashboard with the timeline ID passed as a route parameter.
            return redirect()->route('V2_ALL_performance.dashboard', [
                'timeline_id' => $timelineId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing timeline selection: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while processing your selection.']);
        }
    }

    /**
     * Show the performance dashboard with indicators, scores, and insights for all clusters.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showPerformanceDashboard(Request $request)
    {
        try {
            // Get timeline ID from route parameter or request input.
            $timelineID = $request->route('timeline_id') ?? $request->input('timeline_id');

            Log::info('Dashboard - All Clusters, Timeline ID: ' . ($timelineID ?? 'null'));

            if (! $timelineID) {
                Log::warning('Missing timeline selection');
                return redirect()->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'Please select a timeline.']);
            }

            $timeline = DB::table('ecsahc_timelines')
                ->where('ReportingID', $timelineID)
                ->first();

            if (! $timeline) {
                Log::warning('Selected timeline not found: ' . $timelineID);
                return redirect()->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'Selected timeline not found.']);
            }

            // Get all clusters except "All Clusters/Projects"
            $clusters = DB::table('clusters')
                ->where('ClusterID', '!=', 'All clusters/projects')
                ->get();

            Log::info('Found ' . $clusters->count() . ' clusters for combined analysis');

            if ($clusters->isEmpty()) {
                return view('scrn', [
                    'Page'  => 'EcsaPerfV2.all-indicator-performance',
                    'Title' => 'Performance Dashboard - All Clusters',
                    'Error' => 'No clusters found in the system.',
                ]);
            }

            // Collect all performance data across all clusters
            $allPerformanceData = [];
            $allIndicators      = [];
            $clusterNames       = [];

            foreach ($clusters as $cluster) {
                $clusterID                = $cluster->ClusterID;
                $clusterNames[$clusterID] = $cluster->Cluster_Name;

                // Get indicators for this cluster
                $indicators = DB::table('performance_indicators')
                    ->whereRaw('JSON_CONTAINS(Responsible_Cluster, ?)', [json_encode($clusterID)])
                    ->get();

                foreach ($indicators as $indicator) {
                    $indicatorID = (string) $indicator->id;

                    // Store unique indicators
                    if (! isset($allIndicators[$indicatorID])) {
                        $allIndicators[$indicatorID] = $indicator;
                    }

                    // Process performance data for this indicator and cluster
                    $performanceData = $this->processIndicatorPerformance(
                        collect([$indicator]),
                        $clusterID,
                        $timelineID,
                        $timeline->Year
                    );

                    if (! empty($performanceData)) {
                        // Add cluster information to each performance data item
                        foreach ($performanceData as &$data) {
                            if (isset($cluster) && is_object($cluster)) {
                                $data['cluster']      = $cluster;
                                $allPerformanceData[] = $data;
                            }
                        }
                    }
                }
            }

            // Group performance data by indicator
            $groupedPerformanceData = [];
            foreach ($allPerformanceData as $data) {
                if (isset($data['indicator']) && isset($data['indicator']->id)) {
                    $indicatorID = $data['indicator']->id;
                    if (! isset($groupedPerformanceData[$indicatorID])) {
                        $groupedPerformanceData[$indicatorID] = [];
                    }
                    $groupedPerformanceData[$indicatorID][] = $data;
                }
            }

            // Calculate combined performance summary
            // Calculate cumulative scores for indicators across all clusters
            $cumulativeScores = $this->calculateCumulativeIndicatorScores($allPerformanceData);

            // Calculate the performance summary using cumulative scores
            $performanceSummary = $this->calculateCombinedPerformanceFromCumulativeScores($cumulativeScores, $allPerformanceData);

            // Generate insights based on the combined data
            $insights = $this->generateAIInsights($allPerformanceData, $clusterNames, $timeline);

            return view('scrn', [
                'Page'                   => 'EcsaPerfV2.all-indicator-performance',
                'Title'                  => 'Performance Dashboard - All Clusters',
                'Timeline'               => $timeline,
                'PerformanceData'        => $allPerformanceData,
                'GroupedPerformanceData' => $groupedPerformanceData,
                'Insights'               => $insights,
                'PerformanceSummary'     => $performanceSummary,
                'Error'                  => empty($allPerformanceData) ? 'No performance data found for any cluster.' : null,
                'TimelineID'             => $timelineID,
                'AllClusters'            => true,
                'ClusterNames'           => $clusterNames,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating performance dashboard: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return view('scrn', [
                'Page'  => 'EcsaPerfV2.all-indicator-performance',
                'Title' => 'Performance Dashboard - All Clusters',
                'Error' => 'An error occurred while generating the performance dashboard. Please try again later.',
            ]);
        }
    }

    /**
     * Process indicator performance data.
     *
     * @param \Illuminate\Support\Collection $indicators
     * @param string $clusterID
     * @param string $timelineID
     * @param int $year
     * @return array
     */
    private function processIndicatorPerformance($indicators, $clusterID, $timelineID, $year)
    {
        $performanceData = [];

        foreach ($indicators as $indicator) {
            // Get strategic objective with proper error handling
            $strategicObjective = DB::table('strategic_objectives')
                ->where('StrategicObjectiveID', trim($indicator->SO_ID))
                ->first();

            // If strategic objective not found, create a placeholder with the SO_ID
            if (! $strategicObjective && ! empty($indicator->SO_ID)) {
                $strategicObjective = (object) [
                    'SO_ID'                => trim($indicator->SO_ID),
                    'SO_Name'              => trim($indicator->SO_ID),
                    'Description'          => 'Strategic objective details not found',
                    'StrategicObjectiveID' => trim($indicator->SO_ID),
                ];
            }

            $indicatorIDString = (string) $indicator->id;

            // Find target based on year range
            $target = $this->findTargetForYear($clusterID, $indicatorIDString, $year);

            $performance = DB::table('cluster_performance_mappings')
                ->where('ClusterID', $clusterID)
                ->where('ReportingID', $timelineID)
                ->where('IndicatorID', $indicatorIDString)
                ->first();

            $score = $this->calculatePerformanceScore($indicator, $target, $performance);

            $performanceData[] = [
                'indicator'          => $indicator,
                'strategicObjective' => $strategicObjective,
                'target'             => $target,
                'performance'        => $performance,
                'score'              => $score,
            ];
        }

        return $performanceData;
    }

    // Add this new method after processIndicatorPerformance method
    /**
     * Calculate cumulative scores for each indicator across all clusters.
     *
     * @param array $performanceData All collected performance data across clusters
     * @return array Cumulative indicator scores
     */
    private function calculateCumulativeIndicatorScores($performanceData)
    {
        $indicatorGroups  = [];
        $cumulativeScores = [];

        // Group data by indicator
        foreach ($performanceData as $data) {
            if (! isset($data['indicator']) || ! isset($data['indicator']->id)) {
                continue;
            }

            $indicatorID = $data['indicator']->id;
            if (! isset($indicatorGroups[$indicatorID])) {
                $indicatorGroups[$indicatorID] = [
                    'indicator'          => $data['indicator'],
                    'strategicObjective' => $data['strategicObjective'] ?? null,
                    'data'               => [],
                ];
            }

            $indicatorGroups[$indicatorID]['data'][] = $data;
        }

        // Calculate cumulative scores for each indicator
        foreach ($indicatorGroups as $indicatorID => $group) {
            $indicator          = $group['indicator'];
            $strategicObjective = $group['strategicObjective'];
            $dataPoints         = $group['data'];

            $cumulativeTargetValues  = [];
            $cumulativeActualValues  = [];
            $clusterCoverage         = [];
            $comments                = [];
            $hasQualitativeResponses = false;
            $targetYearRange         = null;

            foreach ($dataPoints as $data) {
                if (isset($data['score']['has_target']) && $data['score']['has_target']) {
                    if (! $targetYearRange && isset($data['score']['target_year_range'])) {
                        $targetYearRange = $data['score']['target_year_range'];
                    }

                    if (isset($data['score']['target_value']) && is_numeric($data['score']['target_value'])) {
                        $cumulativeTargetValues[] = floatval($data['score']['target_value']);
                    }
                }

                if (isset($data['score']['has_performance']) && $data['score']['has_performance']) {
                    if (isset($data['score']['raw_value'])) {
                        if ($indicator->ResponseType === 'Number' && is_numeric($data['score']['raw_value'])) {
                            $cumulativeActualValues[] = floatval($data['score']['raw_value']);
                        } elseif ($indicator->ResponseType === 'Boolean' || $indicator->ResponseType === 'Yes/No') {
                            $value = strtolower($data['score']['raw_value']);
                            if ($value === 'yes' || $value === 'true' || $value === '1') {
                                $cumulativeActualValues[] = 1;
                            } else {
                                $cumulativeActualValues[] = 0;
                            }
                        } elseif ($indicator->ResponseType === 'Text') {
                            $hasQualitativeResponses = true;
                        }
                    }

                    if (isset($data['score']['comment']) && ! empty($data['score']['comment'])) {
                        $clusterName = isset($data['cluster']) && isset($data['cluster']->Cluster_Name)
                        ? $data['cluster']->Cluster_Name
                        : 'Unknown Cluster';
                        $comments[] = $clusterName . ': ' . $data['score']['comment'];
                    }
                }

                if (isset($data['cluster']) && isset($data['cluster']->ClusterID)) {
                    $clusterCoverage[] = $data['cluster']->ClusterID;
                }
            }

            // Calculate cumulative score
            $cumulativeScore = [
                'indicator_id'      => $indicatorID,
                'indicator_number'  => $indicator->Indicator_Number ?? $indicatorID,
                'indicator_name'    => $indicator->Indicator_Name ?? 'Unknown Indicator',
                'has_target'        => ! empty($cumulativeTargetValues),
                'has_performance'   => ! empty($cumulativeActualValues) || $hasQualitativeResponses,
                'target_year_range' => $targetYearRange,
                'clusters_covered'  => array_unique($clusterCoverage),
                'comments'          => $comments,
                'raw_value'         => null,
                'target_value'      => null,
                'percentage'        => null,
                'category'          => 'Not Available',
                'color'             => 'gray',
                'error'             => null,
            ];

            if ($hasQualitativeResponses) {
                $cumulativeScore['category'] = 'Qualitative';
                $cumulativeScore['color']    = 'blue';
            } elseif (! empty($cumulativeTargetValues) && ! empty($cumulativeActualValues)) {
                $totalTarget = array_sum($cumulativeTargetValues);
                $totalActual = array_sum($cumulativeActualValues);

                $cumulativeScore['target_value'] = $totalTarget;
                $cumulativeScore['raw_value']    = $totalActual;

                if ($totalTarget > 0) {
                    // Calculate the cumulative percentage
                    $cumulativePercentage          = ($totalActual / $totalTarget) * 100;
                    $cumulativeScore['percentage'] = $cumulativePercentage;

                    // Determine category based on cumulative percentage
                    if ($cumulativePercentage > 100) {
                        $cumulativeScore['category'] = 'Over Achieved';
                        $cumulativeScore['color']    = 'purple';
                        $cumulativeScore['note']     = 'Exceeded target by ' . number_format($cumulativePercentage - 100, 1) . '%';
                    } elseif ($cumulativePercentage >= 90) {
                        $cumulativeScore['category'] = 'Met';
                        $cumulativeScore['color']    = 'dark-green';
                    } elseif ($cumulativePercentage >= 50) {
                        $cumulativeScore['category'] = 'On Track';
                        $cumulativeScore['color']    = 'light-green';
                    } elseif ($cumulativePercentage >= 10) {
                        $cumulativeScore['category'] = 'In Progress';
                        $cumulativeScore['color']    = 'yellow';
                    } else {
                        $cumulativeScore['category'] = 'Not Performing';
                        $cumulativeScore['color']    = 'red';
                    }
                } else {
                    $cumulativeScore['error'] = 'Invalid target value (zero or non-numeric)';
                }
            } else if (empty($cumulativeTargetValues)) {
                $cumulativeScore['error'] = 'No targets set for this indicator across any cluster';
            } else if (empty($cumulativeActualValues)) {
                $cumulativeScore['error'] = 'No performance data reported for this indicator across any cluster';
            }

            $cumulativeScores[$indicatorID] = [
                'indicator'            => $indicator,
                'strategicObjective'   => $strategicObjective,
                'score'                => $cumulativeScore,
                'original_data_points' => $dataPoints,
            ];
        }

        return $cumulativeScores;
    }

    // Add this new method after the calculateCumulativeIndicatorScores method
    /**
     * Calculate combined performance summary based on cumulative indicator scores.
     *
     * @param array $cumulativeScores The cumulative scores for each indicator
     * @param array $allPerformanceData Original performance data (for cluster-specific metrics)
     * @return array Performance summary
     */
    private function calculateCombinedPerformanceFromCumulativeScores($cumulativeScores, $allPerformanceData)
    {
        $summary = [
            'total_indicators'          => count($cumulativeScores),
            'indicators_with_targets'   => 0,
            'indicators_with_data'      => 0,
            'category_counts'           => [
                'Not Performing' => 0,
                'In Progress'    => 0,
                'On Track'       => 0,
                'Met'            => 0,
                'Over Achieved'  => 0,
                'Qualitative'    => 0,
                'Not Available'  => 0,
            ],
            'overall_score'             => 0,
            'overall_category'          => 'Not Available',
            'strategic_objectives'      => [],
            'perfect_score'             => false,
            'indicators_at_100'         => 0,
            'clusters_data'             => [], // For cluster-specific performance
            'data_completeness'         => 0,
            'data_completeness_warning' => false,
        ];

        // Initialize strategic objectives tracking
        $strategicObjectivesData = [];

        // Track scorable indicators for overall calculation
        $scoreSum           = 0;
        $scorableIndicators = 0;

        // Process each cumulative indicator score
        foreach ($cumulativeScores as $indicatorID => $data) {
            $score              = $data['score'];
            $indicator          = $data['indicator'];
            $strategicObjective = $data['strategicObjective'];

            // Count indicators with targets and data
            if ($score['has_target']) {
                $summary['indicators_with_targets']++;
            }

            if ($score['has_performance']) {
                $summary['indicators_with_data']++;

                // Count by category
                $category = $score['category'];
                if (isset($summary['category_counts'][$category])) {
                    $summary['category_counts'][$category]++;
                }

                // Track indicators at 100% or more
                if (isset($score['percentage']) && $score['percentage'] !== null && $score['percentage'] >= 100) {
                    $summary['indicators_at_100']++;
                }
            }

            // Track strategic objectives
            $soID = null;
            if (isset($strategicObjective)) {
                if (isset($strategicObjective->StrategicObjectiveID)) {
                    $soID = trim($strategicObjective->StrategicObjectiveID);
                } elseif (isset($strategicObjective->SO_ID)) {
                    $soID = trim($strategicObjective->SO_ID);
                }
            }

            if (empty($soID) && isset($indicator->SO_ID)) {
                $soID = trim($indicator->SO_ID);
            }

            if (empty($soID)) {
                $soID = 'Unknown';
            }

            // Initialize strategic objective if not already done
            if (! isset($strategicObjectivesData[$soID])) {
                $soName = $soID;
                $soDesc = '';

                if (isset($strategicObjective)) {
                    if (isset($strategicObjective->SO_Name)) {
                        $soName = $strategicObjective->SO_Name;
                    }
                    if (isset($strategicObjective->Description)) {
                        $soDesc = $strategicObjective->Description;
                    }
                }

                $strategicObjectivesData[$soID] = [
                    'name'                => $soName,
                    'description'         => $soDesc,
                    'indicators'          => 0,
                    'score_sum'           => 0,
                    'scorable_indicators' => 0,
                    'average_score'       => null,
                    'indicators_at_100'   => 0,
                    'perfect_score'       => false,
                    'data_completeness'   => 0,
                ];
            }

            $strategicObjectivesData[$soID]['indicators']++;

            if (isset($score['percentage']) && $score['percentage'] !== null) {
                $cappedSOPercentage = min(100, $score['percentage']); // Cap at 100% for average calculation
                $strategicObjectivesData[$soID]['score_sum'] += $cappedSOPercentage;
                $strategicObjectivesData[$soID]['scorable_indicators']++;

                // Also add to the overall score sum
                $scoreSum += $cappedSOPercentage;
                $scorableIndicators++;

                if ($score['percentage'] >= 100) {
                    $strategicObjectivesData[$soID]['indicators_at_100']++;
                }
            }
        }

        // Process cluster-specific data (this still uses the original performance data)
        $summary['clusters_data'] = $this->calculateClusterSpecificPerformance($allPerformanceData);

        // Calculate data completeness percentage
        if ($summary['total_indicators'] > 0) {
            $summary['data_completeness'] = ($summary['indicators_with_data'] / $summary['total_indicators']) * 100;

            // Set warning flag if data completeness is below threshold
            if ($summary['data_completeness'] < 50) {
                $summary['data_completeness_warning'] = true;
            }
        }

        // Calculate average scores for each strategic objective
        foreach ($strategicObjectivesData as $soID => $so) {
            if ($so['scorable_indicators'] > 0) {
                $strategicObjectivesData[$soID]['average_score'] =
                    $so['score_sum'] / $so['scorable_indicators'];

                $strategicObjectivesData[$soID]['perfect_score'] =
                    ($so['indicators_at_100'] === $so['scorable_indicators']);

                // Calculate data completeness for this strategic objective
                if ($so['indicators'] > 0) {
                    $strategicObjectivesData[$soID]['data_completeness'] =
                        ($so['scorable_indicators'] / $so['indicators']) * 100;
                }
            }
        }

        // Calculate overall score
        if ($scorableIndicators > 0) {
            // Calculate raw score based only on indicators with data
            $rawScore = $scoreSum / $scorableIndicators;

            // Apply data completeness factor to the overall score
            if ($summary['data_completeness_warning']) {
                $dataCompletenessWeight   = $summary['data_completeness'] / 100;
                $summary['overall_score'] = $rawScore * $dataCompletenessWeight;

                $summary['score_note'] = "Score adjusted for data completeness (" .
                number_format($summary['data_completeness'], 1) . "% of indicators have data)";
            } else {
                $summary['overall_score'] = $rawScore;
                $summary['score_note']    = "Based on " .
                number_format($summary['data_completeness'], 1) . "% of indicators with data";
            }

            // Determine overall category
            if ($summary['overall_score'] >= 90) {
                $summary['overall_category'] = 'Met';
            } elseif ($summary['overall_score'] >= 50) {
                $summary['overall_category'] = 'On Track';
            } elseif ($summary['overall_score'] >= 10) {
                $summary['overall_category'] = 'In Progress';
            } else {
                $summary['overall_category'] = 'Not Performing';
            }

            // If data completeness is very low, override category
            if ($summary['data_completeness'] < 20) {
                $summary['overall_category'] = 'Insufficient Data';
                $summary['score_note']       = "Warning: Only " .
                number_format($summary['data_completeness'], 1) .
                    "% of indicators have data. Score may not be representative.";
            }
        }

        // Determine if there's a perfect score
        $summary['perfect_score'] = ($scorableIndicators > 0 &&
            $summary['indicators_at_100'] === $scorableIndicators);

        // Store the strategic objectives data
        $summary['strategic_objectives'] = $strategicObjectivesData;

        return $summary;
    }

    /**
     * Calculate performance data for each cluster.
     *
     * @param array $performanceData
     * @return array
     */
    private function calculateClusterSpecificPerformance($performanceData)
    {
        $clusterData = [];

        foreach ($performanceData as $data) {
            // Skip invalid data entries
            if (! isset($data['indicator']) || ! isset($data['indicator']->id) ||
                ! isset($data['cluster']) || ! isset($data['cluster']->ClusterID)) {
                continue;
            }

            $clusterID = $data['cluster']->ClusterID;

            // Initialize cluster data if not already done
            if (! isset($clusterData[$clusterID])) {
                $clusterData[$clusterID] = [
                    'name'                    => $data['cluster']->Cluster_Name ?? 'Unknown Cluster',
                    'indicators'              => 0,
                    'indicators_with_targets' => 0,
                    'indicators_with_data'    => 0,
                    'score_sum'               => 0,
                    'scorable_indicators'     => 0,
                    'average_score'           => null,
                    'data_completeness'       => 0,
                ];
            }

            // Update cluster-specific counts
            $clusterData[$clusterID]['indicators']++;

            if (isset($data['score']['has_target']) && $data['score']['has_target']) {
                $clusterData[$clusterID]['indicators_with_targets']++;
            }

            if (isset($data['score']['has_performance']) && $data['score']['has_performance']) {
                $clusterData[$clusterID]['indicators_with_data']++;

                // Update cluster score sum for scorable indicators
                if (isset($data['score']['percentage']) && $data['score']['percentage'] !== null) {
                    $cappedPercentage = min(100, $data['score']['percentage']);
                    $clusterData[$clusterID]['score_sum'] += $cappedPercentage;
                    $clusterData[$clusterID]['scorable_indicators']++;
                }
            }
        }

        // Calculate average scores for each cluster
        foreach ($clusterData as $clusterID => $cluster) {
            if ($cluster['scorable_indicators'] > 0) {
                $clusterData[$clusterID]['average_score'] =
                    $cluster['score_sum'] / $cluster['scorable_indicators'];

                // Calculate data completeness for this cluster
                if ($cluster['indicators'] > 0) {
                    $clusterData[$clusterID]['data_completeness'] =
                        ($cluster['indicators_with_data'] / $cluster['indicators']) * 100;
                }
            }
        }

        return $clusterData;
    }

    /**
     * Find the appropriate target for a given year based on year ranges.
     *
     * @param string $clusterID
     * @param string $indicatorID
     * @param int $year
     * @return object|null
     */
    private function findTargetForYear($clusterID, $indicatorID, $year)
    {
        // Get all targets for this cluster and indicator
        $targets = DB::table('cluster_indicator_targets')
            ->where('ClusterID', $clusterID)
            ->where('IndicatorID', $indicatorID)
            ->get();

        if ($targets->isEmpty()) {
            Log::info("No targets found for Cluster: $clusterID, Indicator: $indicatorID");
            return null;
        }

        // Find the target where the year falls within the target range
        foreach ($targets as $target) {
            // Skip targets that don't match the YYYY-YYYY format
            if (! $this->isValidTargetYearFormat($target->Target_Year)) {
                Log::warning("Invalid target year format: {$target->Target_Year} for Indicator: $indicatorID");
                continue;
            }

            $yearRange = $this->parseTargetYearRange($target->Target_Year);

            // Check if the report year falls within this target's range
            if ($year >= $yearRange['start'] && $year <= $yearRange['end']) {
                Log::info("Found target for year $year in range {$target->Target_Year} for Indicator: $indicatorID");
                return $target;
            }
        }

        Log::warning("No matching target found for year $year, Cluster: $clusterID, Indicator: $indicatorID");
        return null;
    }

    /**
     * Validate if a target year string follows the YYYY-YYYY format with end year = start year + 1.
     *
     * @param string $targetYear
     * @return bool
     */
    private function isValidTargetYearFormat($targetYear)
    {
        // Check basic format: YYYY-YYYY
        if (! preg_match('/^\d{4}-\d{4}$/', $targetYear)) {
            return false;
        }

        $yearRange = $this->parseTargetYearRange($targetYear);

        // Validate that end year is exactly one greater than start year
        return ($yearRange['end'] === $yearRange['start'] + 1);
    }

    /**
     * Parse a target year string into start and end years.
     *
     * @param string $targetYear
     * @return array
     */
    private function parseTargetYearRange($targetYear)
    {
        $years = explode('-', $targetYear);

        return [
            'start' => (int) $years[0],
            'end'   => (int) $years[1],
        ];
    }

    /**
     * Calculate performance score for an indicator.
     *
     * @param object $indicator
     * @param object|null $target
     * @param object|null $performance
     * @return array
     */
    private function calculatePerformanceScore($indicator, $target, $performance)
    {
        $score = [
            'raw_value'         => null,
            'percentage'        => null,
            'category'          => 'Not Available',
            'color'             => 'gray',
            'has_target'        => false,
            'has_performance'   => false,
            'error'             => null,
            'target_value'      => null,
            'target_year_range' => null,
        ];

        if (! $target) {
            $score['error'] = 'No target set for this indicator.';
            return $score;
        }

        $score['has_target']        = true;
        $score['target_value']      = $target->Target_Value;
        $score['target_year_range'] = $target->Target_Year;

        if (! $performance) {
            $score['error'] = 'No performance data reported for this indicator.';
            return $score;
        }

        $score['has_performance'] = true;
        $score['raw_value']       = $performance->Response;
        $score['comment']         = $performance->ReportingComment ?? null;

        switch ($indicator->ResponseType) {
            case 'Number':
                if ($target->Target_Value == 0) {
                    if (floatval($performance->Response) == 0) {
                        $score['percentage'] = 100;
                        $score['category']   = 'Met';
                        $score['color']      = 'dark-green';
                    } else {
                        $score['percentage'] = 0;
                        $score['category']   = 'Not Performing';
                        $score['color']      = 'red';
                    }
                } else {
                    // Calculate raw percentage
                    $rawPercentage = (floatval($performance->Response) / floatval($target->Target_Value)) * 100;

                    // Store the raw percentage
                    $score['original_percentage'] = $rawPercentage;

                    // Set the actual percentage (not capped)
                    $score['percentage'] = $rawPercentage;

                    // Determine category based on actual percentage
                    if ($rawPercentage > 100) {
                        $score['category'] = 'Over Achieved';
                        $score['color']    = 'purple';
                        $score['note']     = 'Exceeded target by ' . number_format($rawPercentage - 100, 1) . '%';
                    } elseif ($rawPercentage >= 90) {
                        $score['category'] = 'Met';
                        $score['color']    = 'dark-green';
                    } elseif ($rawPercentage >= 50) {
                        $score['category'] = 'On Track';
                        $score['color']    = 'light-green';
                    } elseif ($rawPercentage >= 10) {
                        $score['category'] = 'In Progress';
                        $score['color']    = 'yellow';
                    } else {
                        $score['category'] = 'Not Performing';
                        $score['color']    = 'red';
                    }
                }
                break;

            case 'Boolean':
            case 'Yes/No':
                $value = strtolower($performance->Response);
                if ($value === 'yes' || $value === 'true' || $value === '1') {
                    $score['percentage'] = 100;
                    $score['category']   = 'Met';
                    $score['color']      = 'dark-green';
                } else {
                    $score['percentage'] = 0;
                    $score['category']   = 'Not Performing';
                    $score['color']      = 'red';
                }
                break;

            case 'Text':
                $score['category'] = 'Qualitative';
                $score['color']    = 'blue';
                break;

            default:
                $score['error'] = 'Unknown response type.';
        }

        return $score;
    }

    /**
     * Calculate combined performance summary across all clusters.
     *
     * @param array $performanceData
     * @return array
     */
    private function calculateCombinedPerformanceSummary($performanceData)
    {
        $summary = [
            'total_indicators'          => 0,
            'indicators_with_targets'   => 0,
            'indicators_with_data'      => 0,
            'category_counts'           => [
                'Not Performing' => 0,
                'In Progress'    => 0,
                'On Track'       => 0,
                'Met'            => 0,
                'Over Achieved'  => 0,
                'Qualitative'    => 0,
                'Not Available'  => 0,
            ],
            'overall_score'             => 0,
            'overall_category'          => 'Not Available',
            'strategic_objectives'      => [],
            'perfect_score'             => false,
            'indicators_at_100'         => 0,
            'clusters_data'             => [],    // Added to track per-cluster performance
            'data_completeness'         => 0,     // Added to track data completeness
            'data_completeness_warning' => false, // Flag for low data completeness
        ];

        // Count unique indicators
        $uniqueIndicators            = [];
        $uniqueIndicatorsWithTargets = [];
        $uniqueIndicatorsWithData    = [];

        foreach ($performanceData as $data) {
            // Skip invalid data entries
            if (! isset($data['indicator']) || ! isset($data['indicator']->id) || ! isset($data['cluster']) || ! isset($data['cluster']->ClusterID)) {
                continue;
            }

            $indicatorID = $data['indicator']->id;
            $clusterID   = $data['cluster']->ClusterID;

            // Track unique indicators
            $uniqueIndicators[$indicatorID] = true;

            if (isset($data['score']['has_target']) && $data['score']['has_target']) {
                $uniqueIndicatorsWithTargets[$indicatorID] = true;
            }

            if (isset($data['score']['has_performance']) && $data['score']['has_performance']) {
                $uniqueIndicatorsWithData[$indicatorID] = true;
            }

            // Initialize cluster data if not already done
            if (! isset($summary['clusters_data'][$clusterID])) {
                $summary['clusters_data'][$clusterID] = [
                    'name'                    => $data['cluster']->Cluster_Name ?? 'Unknown Cluster',
                    'indicators'              => 0,
                    'indicators_with_targets' => 0,
                    'indicators_with_data'    => 0,
                    'score_sum'               => 0,
                    'scorable_indicators'     => 0,
                    'average_score'           => null,
                    'data_completeness'       => 0,
                ];
            }

            // Update cluster-specific counts
            $summary['clusters_data'][$clusterID]['indicators']++;

            if (isset($data['score']['has_target']) && $data['score']['has_target']) {
                $summary['clusters_data'][$clusterID]['indicators_with_targets']++;
            }

            if (isset($data['score']['has_performance']) && $data['score']['has_performance']) {
                $summary['clusters_data'][$clusterID]['indicators_with_data']++;

                // Count by category
                $category = $data['score']['category'] ?? 'Not Available';
                if (isset($summary['category_counts'][$category])) {
                    $summary['category_counts'][$category]++;
                }

                // Track indicators at 100% or more
                if (isset($data['score']['percentage']) && $data['score']['percentage'] !== null && $data['score']['percentage'] >= 100) {
                    $summary['indicators_at_100']++;
                }

                // Update cluster score sum for scorable indicators
                if (isset($data['score']['percentage']) && $data['score']['percentage'] !== null) {
                    $cappedPercentage = min(100, $data['score']['percentage']);
                    $summary['clusters_data'][$clusterID]['score_sum'] += $cappedPercentage;
                    $summary['clusters_data'][$clusterID]['scorable_indicators']++;
                }
            }

            // Track strategic objectives
            $soID = null;
            if (isset($data['strategicObjective'])) {
                if (isset($data['strategicObjective']->StrategicObjectiveID)) {
                    $soID = trim($data['strategicObjective']->StrategicObjectiveID);
                } elseif (isset($data['strategicObjective']->SO_ID)) {
                    $soID = trim($data['strategicObjective']->SO_ID);
                }
            }

            if (empty($soID) && isset($data['indicator']->SO_ID)) {
                $soID = trim($data['indicator']->SO_ID);
            }

            if (empty($soID)) {
                $soID = 'Unknown';
            }

            // Initialize strategic objective if not already done
            if (! isset($summary['strategic_objectives'][$soID])) {
                $soName = $soID;
                $soDesc = '';

                if (isset($data['strategicObjective'])) {
                    if (isset($data['strategicObjective']->SO_Name)) {
                        $soName = $data['strategicObjective']->SO_Name;
                    }
                    if (isset($data['strategicObjective']->Description)) {
                        $soDesc = $data['strategicObjective']->Description;
                    }
                }

                $summary['strategic_objectives'][$soID] = [
                    'name'                => $soName,
                    'description'         => $soDesc,
                    'indicators'          => 0,
                    'score_sum'           => 0,
                    'scorable_indicators' => 0,
                    'average_score'       => null,
                    'indicators_at_100'   => 0,
                    'perfect_score'       => false,
                    'data_completeness'   => 0,
                ];
            }

            $summary['strategic_objectives'][$soID]['indicators']++;
            if (isset($data['score']['percentage']) && $data['score']['percentage'] !== null) {
                $cappedSOPercentage = min(100, $data['score']['percentage']);
                $summary['strategic_objectives'][$soID]['score_sum'] += $cappedSOPercentage;
                $summary['strategic_objectives'][$soID]['scorable_indicators']++;

                if ($data['score']['percentage'] >= 100) {
                    $summary['strategic_objectives'][$soID]['indicators_at_100']++;
                }
            }
        }

        // Set counts of unique indicators
        $summary['total_indicators']        = count($uniqueIndicators);
        $summary['indicators_with_targets'] = count($uniqueIndicatorsWithTargets);
        $summary['indicators_with_data']    = count($uniqueIndicatorsWithData);

        // Calculate data completeness percentage
        if ($summary['total_indicators'] > 0) {
            $summary['data_completeness'] = ($summary['indicators_with_data'] / $summary['total_indicators']) * 100;

            // Set warning flag if data completeness is below threshold (e.g., 50%)
            if ($summary['data_completeness'] < 50) {
                $summary['data_completeness_warning'] = true;
            }
        }

        // Calculate average scores for each strategic objective
        foreach ($summary['strategic_objectives'] as $soID => $so) {
            if ($so['scorable_indicators'] > 0) {
                $summary['strategic_objectives'][$soID]['average_score'] =
                    $so['score_sum'] / $so['scorable_indicators'];

                $summary['strategic_objectives'][$soID]['perfect_score'] =
                    ($so['indicators_at_100'] === $so['scorable_indicators']);

                // Calculate data completeness for this strategic objective
                if ($so['indicators'] > 0) {
                    $summary['strategic_objectives'][$soID]['data_completeness'] =
                        ($so['scorable_indicators'] / $so['indicators']) * 100;
                }
            }
        }

        // Calculate average scores for each cluster
        $totalScoreSum           = 0;
        $totalScorableIndicators = 0;

        foreach ($summary['clusters_data'] as $clusterID => $clusterData) {
            if ($clusterData['scorable_indicators'] > 0) {
                $summary['clusters_data'][$clusterID]['average_score'] =
                    $clusterData['score_sum'] / $clusterData['scorable_indicators'];

                // Calculate data completeness for this cluster
                if ($clusterData['indicators'] > 0) {
                    $summary['clusters_data'][$clusterID]['data_completeness'] =
                        ($clusterData['indicators_with_data'] / $clusterData['indicators']) * 100;
                }

                // Add to overall totals
                $totalScoreSum += $clusterData['score_sum'];
                $totalScorableIndicators += $clusterData['scorable_indicators'];
            }
        }

        // Calculate overall score across all clusters
        if ($totalScorableIndicators > 0) {
            // Calculate raw score based only on indicators with data
            $rawScore = $totalScoreSum / $totalScorableIndicators;

            // Apply data completeness factor to the overall score
            // This ensures the score reflects the fact that many indicators may not have data
            if ($summary['data_completeness_warning']) {
                // If data completeness is below threshold, adjust the score to reflect limited data
                // This weighted approach ensures the score is more representative of overall performance
                $dataCompletenessWeight   = $summary['data_completeness'] / 100;
                $summary['overall_score'] = $rawScore * $dataCompletenessWeight;

                // Add note about adjusted score
                $summary['score_note'] = "Score adjusted for data completeness (" .
                number_format($summary['data_completeness'], 1) . "% of indicators have data)";
            } else {
                // If data completeness is acceptable, use the raw score but still note the completeness
                $summary['overall_score'] = $rawScore;
                $summary['score_note']    = "Based on " .
                number_format($summary['data_completeness'], 1) . "% of indicators with data";
            }

            // Determine overall category
            if ($summary['overall_score'] >= 90) {
                $summary['overall_category'] = 'Met';
            } elseif ($summary['overall_score'] >= 50) {
                $summary['overall_category'] = 'On Track';
            } elseif ($summary['overall_score'] >= 10) {
                $summary['overall_category'] = 'In Progress';
            } else {
                $summary['overall_category'] = 'Not Performing';
            }

            // If data completeness is very low (e.g., below 20%), override category
            if ($summary['data_completeness'] < 20) {
                $summary['overall_category'] = 'Insufficient Data';
                $summary['score_note']       = "Warning: Only " .
                number_format($summary['data_completeness'], 1) .
                    "% of indicators have data. Score may not be representative.";
            }
        }

        // Determine if there's a perfect score
        $summary['perfect_score'] = ($totalScorableIndicators > 0 &&
            $summary['indicators_at_100'] === $totalScorableIndicators);

        return $summary;
    }

    /**
     * Generate AI insights based on combined performance data.
     *
     * @param array $performanceData
     * @param array $clusterNames
     * @param object $timeline
     * @return array
     */
    private function generateAIInsights($performanceData, $clusterNames, $timeline)
    {
        $insights = [
            'observations'        => [],
            'recommendations'     => [],
            'trends'              => [],
            'critical_indicators' => [],
            'cluster_comparisons' => [], // Added for cross-cluster comparisons
            'data_quality'        => [], // Added for data quality insights
        ];

        $summary = $this->calculateCombinedPerformanceSummary($performanceData);

        // Data completeness insights
        if ($summary['total_indicators'] > 0) {
            $dataCompleteness = ($summary['indicators_with_data'] / $summary['total_indicators']) * 100;

            // Add data quality insights
            $insights['data_quality'][] = "Data completeness across all indicators is at " .
            number_format($dataCompleteness, 1) . "%.";

            if ($dataCompleteness < 20) {
                $insights['data_quality'][]    = "WARNING: Data completeness is critically low. Performance scores may not be representative of actual performance.";
                $insights['recommendations'][] = "Prioritize data collection for all indicators before making performance assessments.";
            } elseif ($dataCompleteness < 50) {
                $insights['data_quality'][]    = "WARNING: Data completeness is low. Performance scores should be interpreted with caution.";
                $insights['recommendations'][] = "Improve data collection processes to increase data completeness above 50%.";
            }

            // Add specific insights about data completeness
            if ($dataCompleteness < 100) {
                $insights['observations'][] = "Data reporting is incomplete across clusters. Only " .
                number_format($dataCompleteness, 1) . "% of indicators have performance data.";
                $insights['recommendations'][] = "Improve data collection and reporting processes across all clusters to ensure complete performance data.";
            }

            $targetCompleteness = ($summary['indicators_with_targets'] / $summary['total_indicators']) * 100;
            if ($targetCompleteness < 100) {
                $insights['observations'][] = "Target setting is incomplete across clusters. Only " .
                number_format($targetCompleteness, 1) . "% of indicators have targets set.";
                $insights['recommendations'][] = "Ensure all indicators across all clusters have appropriate targets set for meaningful performance measurement.";
            }
        }

        // Overall performance insights
        if ($summary['overall_score'] > 0) {
            if (isset($summary['score_note'])) {
                $insights['observations'][] = "Overall performance across all clusters is at " .
                number_format($summary['overall_score'], 1) . "% (" . $summary['overall_category'] . "). " .
                    $summary['score_note'];
            } else {
                $insights['observations'][] = "Overall performance across all clusters is at " .
                number_format($summary['overall_score'], 1) . "% (" . $summary['overall_category'] . ").";
            }

            if ($summary['perfect_score']) {
                $insights['observations'][] = "All indicators across all clusters have met their targets at 100%.";
            } else if ($summary['indicators_at_100'] > 0) {
                $perfectPercentage          = ($summary['indicators_at_100'] / $summary['indicators_with_data']) * 100;
                $insights['observations'][] = number_format($perfectPercentage, 1) .
                    "% of indicators across all clusters have fully met their targets (100%).";
            }
        } else {
            $insights['observations'][] = "Unable to calculate overall performance due to insufficient data across clusters.";
        }

        // Performance category distribution insights
        if ($summary['indicators_with_data'] > 0) {
            if ($summary['category_counts']['Not Performing'] > 0) {
                $notPerformingPercentage = ($summary['category_counts']['Not Performing'] / $summary['indicators_with_data']) * 100;
                if ($notPerformingPercentage > 30) {
                    $insights['observations'][] = "A significant portion (" . number_format($notPerformingPercentage, 1) .
                        "%) of indicators across all clusters are in the 'Not Performing' category.";
                    $insights['recommendations'][] = "Conduct a detailed review of underperforming indicators across all clusters to identify systemic issues.";
                }
            }

            if ($summary['category_counts']['Met'] > 0) {
                $metPercentage = ($summary['category_counts']['Met'] / $summary['indicators_with_data']) * 100;
                if ($metPercentage > 70) {
                    $insights['observations'][] = "A high percentage (" . number_format($metPercentage, 1) .
                        "%) of indicators across all clusters have met their targets.";
                    $insights['recommendations'][] = "Consider setting more ambitious targets for indicators that consistently meet current targets.";
                }
            }

            if ($summary['category_counts']['Over Achieved'] > 0) {
                $overAchievedPercentage     = ($summary['category_counts']['Over Achieved'] / $summary['indicators_with_data']) * 100;
                $insights['observations'][] = number_format($overAchievedPercentage, 1) .
                    "% of indicators across all clusters have exceeded their targets (Over Achieved).";
                if ($overAchievedPercentage > 30) {
                    $insights['recommendations'][] = "Review targets for over-achieved indicators to ensure they are appropriately challenging.";
                }
            }
        }

        // Strategic objective insights
        foreach ($summary['strategic_objectives'] as $soID => $so) {
            if ($so['average_score'] !== null) {
                // Add data completeness information for strategic objectives
                if (isset($so['data_completeness']) && $so['data_completeness'] < 50) {
                    $insights['data_quality'][] = "Strategic Objective {$soID} has low data completeness (" .
                    number_format($so['data_completeness'], 1) . "%). Performance assessment may not be accurate.";
                }

                if ($so['average_score'] < 30) {
                    $insights['observations'][] = "Strategic Objective {$soID} ({$so['name']}) is significantly underperforming across all clusters with an average score of " .
                    number_format($so['average_score'], 1) . "%.";
                    $insights['recommendations'][] = "Prioritize interventions for Strategic Objective {$soID} across all clusters to improve performance.";
                } elseif ($so['average_score'] > 90) {
                    $insights['observations'][] = "Strategic Objective {$soID} ({$so['name']}) is performing exceptionally well across all clusters with an average score of " .
                    number_format($so['average_score'], 1) . "%.";

                    if ($so['perfect_score']) {
                        $insights['observations'][] = "All indicators under Strategic Objective {$soID} have fully met their targets across all clusters.";
                    }
                }
            }
        }

        // Cluster comparison insights
        if (count($summary['clusters_data']) > 1) {
            // Find best and worst performing clusters
            $bestCluster           = null;
            $worstCluster          = null;
            $bestScore             = -1;
            $worstScore            = 101;
            $bestDataCompleteness  = 0;
            $worstDataCompleteness = 0;

            foreach ($summary['clusters_data'] as $clusterID => $clusterData) {
                if (isset($clusterData['average_score']) && $clusterData['average_score'] !== null) {
                    if ($clusterData['average_score'] > $bestScore) {
                        $bestScore            = $clusterData['average_score'];
                        $bestCluster          = $clusterID;
                        $bestDataCompleteness = $clusterData['data_completeness'] ?? 0;
                    }

                    if ($clusterData['average_score'] < $worstScore) {
                        $worstScore            = $clusterData['average_score'];
                        $worstCluster          = $clusterID;
                        $worstDataCompleteness = $clusterData['data_completeness'] ?? 0;
                    }
                }
            }

            if ($bestCluster && $worstCluster) {
                $bestConfidencePrefix  = "";
                $worstConfidencePrefix = "";

                if ($bestDataCompleteness < 50) {
                    $bestConfidencePrefix = " (low confidence due to " . number_format($bestDataCompleteness, 1) . "% data completeness)";
                }

                if ($worstDataCompleteness < 50) {
                    $worstConfidencePrefix = " (low confidence due to " . number_format($worstDataCompleteness, 1) . "% data completeness)";
                }

                // Use safeArrayGet to avoid undefined array key errors
                $bestClusterName  = $this->safeArrayGet($clusterNames, $bestCluster, "Unknown Cluster");
                $worstClusterName = $this->safeArrayGet($clusterNames, $worstCluster, "Unknown Cluster");

                $insights['cluster_comparisons'][] = "The best performing cluster is {$bestClusterName} with an average score of " .
                number_format($bestScore, 1) . "%{$bestConfidencePrefix}.";
                $insights['cluster_comparisons'][] = "The lowest performing cluster is {$worstClusterName} with an average score of " .
                number_format($worstScore, 1) . "%{$worstConfidencePrefix}.";

                if ($bestScore - $worstScore > 30 && $bestDataCompleteness >= 50 && $worstDataCompleteness >= 50) {
                    $insights['recommendations'][] = "Consider knowledge sharing between {$bestClusterName} and {$worstClusterName} to improve performance consistency.";
                }
            }

            // Find clusters with data reporting issues
            $clustersWithDataIssues = [];
            foreach ($summary['clusters_data'] as $clusterID => $clusterData) {
                if ($clusterData['indicators'] > 0) {
                    $dataCompleteness = ($clusterData['indicators_with_data'] / $clusterData['indicators']) * 100;
                    if ($dataCompleteness < 70) {
                        // Use safeArrayGet to avoid undefined array key errors
                        $clusterName              = $this->safeArrayGet($clusterNames, $clusterID, "Unknown Cluster");
                        $clustersWithDataIssues[] = $clusterName;
                    }
                }
            }

            if (! empty($clustersWithDataIssues)) {
                $insights['cluster_comparisons'][] = "The following clusters have significant data reporting gaps: " . implode(", ", $clustersWithDataIssues) . ".";
                $insights['recommendations'][]     = "Focus on improving data collection and reporting in " . implode(", ", $clustersWithDataIssues) . ".";
            }
        }

        // Identify critical indicators across all clusters
        $criticalIndicators = [];
        foreach ($performanceData as $data) {
            if (isset($data['score']['category']) && $data['score']['category'] === 'Not Performing' &&
                isset($data['score']['has_performance']) && $data['score']['has_performance']) {

                // Skip if indicator or cluster is not set
                if (! isset($data['indicator']) || ! isset($data['indicator']->id) ||
                    ! isset($data['cluster']) || ! isset($data['cluster']->ClusterID)) {
                    continue;
                }

                $indicatorID = $data['indicator']->id;
                $clusterID   = $data['cluster']->ClusterID;

                // Extract the strategic objective ID
                $soID = 'Unknown';
                if (isset($data['strategicObjective']->SO_ID) && ! empty($data['strategicObjective']->SO_ID)) {
                    $soID = trim($data['strategicObjective']->SO_ID);
                } elseif (isset($data['strategicObjective']->StrategicObjectiveID) && ! empty($data['strategicObjective']->StrategicObjectiveID)) {
                    $soID = trim($data['strategicObjective']->StrategicObjectiveID);
                } elseif (isset($data['indicator']->SO_ID) && ! empty($data['indicator']->SO_ID)) {
                    $soID = trim($data['indicator']->SO_ID);
                }

                $key = $indicatorID . '-' . $soID;

                if (! isset($criticalIndicators[$key])) {
                    $criticalIndicators[$key] = [
                        'indicator_number'    => $data['indicator']->Indicator_Number ?? 'N/A',
                        'indicator_name'      => $data['indicator']->Indicator_Name ?? 'Unknown Indicator',
                        'strategic_objective' => $soID,
                        'clusters'            => [],
                        'lowest_score'        => $data['score']['percentage'] ?? 0,
                        'target_year_range'   => $data['score']['target_year_range'] ?? 'Not set',
                    ];
                }

                // Use safeArrayGet to avoid undefined array key errors
                $clusterName                            = $this->safeArrayGet($clusterNames, $clusterID, "Unknown Cluster");
                $criticalIndicators[$key]['clusters'][] = $clusterName;

                // Track the lowest score for this indicator across clusters
                if (($data['score']['percentage'] ?? 0) < $criticalIndicators[$key]['lowest_score']) {
                    $criticalIndicators[$key]['lowest_score'] = $data['score']['percentage'] ?? 0;
                }
            }
        }

        // Sort critical indicators by lowest score and limit to top 5
        uasort($criticalIndicators, function ($a, $b) {
            return $a['lowest_score'] <=> $b['lowest_score'];
        });

        $insights['critical_indicators'] = array_slice(array_values($criticalIndicators), 0, 5);

        if (count($insights['critical_indicators']) > 0) {
            $insights['recommendations'][] = "Develop targeted intervention plans for the most critical underperforming indicators across all clusters.";
        }

        // Add trend analysis placeholder
        $insights['trends'][]          = "Historical trend analysis across all clusters is not available in the current view.";
        $insights['recommendations'][] = "Implement regular performance reviews to track progress over time across all clusters.";

        return $insights;
    }

    /**
     * Generate and download an Excel performance dashboard report for all clusters.
     *
     * @param string $timelineID
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function generatePerformanceReport($timelineID)
    {
        try {
            Log::info('Generating Excel report for All Clusters - Timeline ID: ' . ($timelineID ?? 'null'));

            if (! $timelineID) {
                return redirect()->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'Timeline must be selected to generate a report.']);
            }

            // Retrieve timeline details
            $timeline = DB::table('ecsahc_timelines')
                ->where('ReportingID', $timelineID)
                ->first();

            if (! $timeline) {
                return redirect()->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'Could not find timeline data for report generation.']);
            }

            // Get all clusters except "All Clusters/Projects"
            $clusters = DB::table('clusters')
                ->where('ClusterID', '!=', 'All clusters/projects')
                ->get();

            if ($clusters->isEmpty()) {
                return redirect()->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'No clusters found in the system.']);
            }

            // Collect all performance data across all clusters
            $allPerformanceData = [];
            $clusterNames       = [];

            foreach ($clusters as $cluster) {
                $clusterID                = $cluster->ClusterID;
                $clusterNames[$clusterID] = $cluster->Cluster_Name;

                // Get indicators for this cluster
                $indicators = DB::table('performance_indicators')
                    ->whereRaw('JSON_CONTAINS(Responsible_Cluster, ?)', [json_encode($clusterID)])
                    ->get();

                if (! $indicators->isEmpty()) {
                    // Process performance data for this cluster
                    $performanceData = $this->processIndicatorPerformance(
                        $indicators,
                        $clusterID,
                        $timelineID,
                        $timeline->Year
                    );

                    if (! empty($performanceData)) {
                        // Add cluster information to each performance data item
                        foreach ($performanceData as &$data) {
                            if (isset($cluster) && is_object($cluster)) {
                                $data['cluster']      = $cluster;
                                $allPerformanceData[] = $data;
                            }
                        }
                    }
                }
            }

            if (empty($allPerformanceData)) {
                return redirect()->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'No performance data found for any cluster.']);
            }

            // Calculate combined performance summary
            $performanceSummary = $this->calculateCombinedPerformanceSummary($allPerformanceData);

            // Generate insights based on the combined data
            $insights = $this->generateAIInsights($allPerformanceData, $clusterNames, $timeline);

            // Create a new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator('ECSA-HC Performance Dashboard')
                ->setLastModifiedBy('ECSA-HC System')
                ->setTitle('All Clusters Performance Report - ' . $timeline->ReportName)
                ->setSubject('Performance Dashboard')
                ->setDescription('Combined performance dashboard for all clusters - ' . $timeline->ReportName)
                ->setKeywords('performance dashboard ecsa-hc all clusters')
                ->setCategory('Performance Reports');

            // Set the active sheet
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Dashboard');

            // Create additional worksheets
            $detailSheet = $spreadsheet->createSheet();
            $detailSheet->setTitle('Detailed Metrics');

            $insightsSheet = $spreadsheet->createSheet();
            $insightsSheet->setTitle('Insights & Recommendations');

            $soSheet = $spreadsheet->createSheet();
            $soSheet->setTitle('Strategic Objectives');

            $clusterSheet = $spreadsheet->createSheet();
            $clusterSheet->setTitle('Cluster Comparison');

            // Add a data quality sheet
            $dataQualitySheet = $spreadsheet->createSheet();
            $dataQualitySheet->setTitle('Data Quality');

            // Generate the dashboard
            $this->generateCombinedDashboardSheet($sheet, $clusterNames, $timeline, $performanceSummary, $allPerformanceData);

            // Generate the detailed metrics sheet
            $this->generateCombinedDetailedMetricsSheet($detailSheet, $allPerformanceData, $timeline);

            // Generate the insights sheet
            $this->generateCombinedInsightsSheet($insightsSheet, $insights, $timeline);

            // Generate the strategic objectives sheet
            $this->generateCombinedStrategicObjectivesSheet($soSheet, $performanceSummary, $timeline);

            // Generate the cluster comparison sheet
            $this->generateClusterComparisonSheet($clusterSheet, $performanceSummary, $timeline);

            // Generate the data quality sheet
            $this->generateDataQualitySheet($dataQualitySheet, $performanceSummary, $timeline);

            // Create charts on the dashboard sheet
            $this->addCombinedPerformanceCharts($spreadsheet, $sheet, $performanceSummary, $allPerformanceData);

            // Set the first sheet as active
            $spreadsheet->setActiveSheetIndex(0);

            // Create the Excel file
            $writer = new Xlsx($spreadsheet);

            // Generate a unique filename
            $fileName = 'All_Clusters_Performance_Report_' . date('Y-m-d_H-i-s') . '.xlsx';
            $filePath = storage_path('app/public/reports/' . $fileName);

            // Ensure the directory exists
            if (! file_exists(storage_path('app/public/reports'))) {
                mkdir(storage_path('app/public/reports'), 0755, true);
            }

            // Save the file
            $writer->save($filePath);

            Log::info('Excel report generated successfully: ' . $filePath);

            // Return the file as a download
            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Error generating Excel report: ' . $e->getMessage() . "\n" . $e->getTraceAsString());

            // Redirect to dashboard with error message
            return redirect()->route('V2_ALL_performance.dashboard', [
                'timeline_id' => $timelineID,
            ])->withErrors(['error' => 'An error occurred while generating the Excel report: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate the main dashboard sheet for combined clusters.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $clusterNames
     * @param object $timeline
     * @param array $performanceSummary
     * @param array $performanceData
     * @return void
     */
    private function generateCombinedDashboardSheet($sheet, $clusterNames, $timeline, $performanceSummary, $performanceData)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(15);

        // Create header with logo placeholder
        $sheet->setCellValue('A1', 'ECSA-HC COMBINED PERFORMANCE DASHBOARD');
        $sheet->mergeCells('A1:F1');

        // Style the header
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => [
                'bold'  => true,
                'size'  => 18,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0F2F1'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(40);

        // Report title
        $sheet->setCellValue('A2', 'All Clusters - ' . $timeline->ReportName);
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2:F2')->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Report metadata
        $sheet->setCellValue('A3', 'Report Date:');
        $sheet->setCellValue('B3', date('F j, Y'));
        $sheet->setCellValue('D3', 'Reporting Period:');
        $sheet->setCellValue('E3', $timeline->Year . ' (' . $timeline->Type . ')');

        $sheet->getStyle('A3:F3')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
        ]);

        // Add a separator
        $sheet->mergeCells('A4:F4');
        $sheet->getStyle('A4:F4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        // Performance summary section
        $sheet->setCellValue('A5', 'PERFORMANCE SUMMARY');
        $sheet->mergeCells('A5:F5');
        $sheet->getStyle('A5:F5')->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Overall score
        $sheet->setCellValue('A6', 'Overall Performance:');
        $sheet->setCellValue('B6', number_format($performanceSummary['overall_score'], 1) . '%');
        $sheet->setCellValue('C6', $performanceSummary['overall_category']);

        // Style the overall score based on category
        $scoreColor = $this->getCategoryColor($performanceSummary['overall_category']);
        $sheet->getStyle('B6')->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 14,
                'color' => ['rgb' => $scoreColor],
            ],
        ]);
        $sheet->getStyle('C6')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => $scoreColor],
            ],
        ]);

        // Add data completeness warning if applicable
        if (isset($performanceSummary['data_completeness_warning']) && $performanceSummary['data_completeness_warning']) {
            $sheet->setCellValue('D6', 'Data Completeness Warning:');
            $sheet->setCellValue('E6', number_format($performanceSummary['data_completeness'], 1) . '% of indicators have data');
            $sheet->getStyle('D6:E6')->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'color' => ['rgb' => 'FF9800'],
                ],
            ]);
        }

        // Indicator statistics
        $sheet->setCellValue('A7', 'Total Indicators:');
        $sheet->setCellValue('B7', $performanceSummary['total_indicators']);

        $sheet->setCellValue('A8', 'Indicators with Targets:');
        $sheet->setCellValue('B8', $performanceSummary['indicators_with_targets']);
        $targetCompleteness = $performanceSummary['total_indicators'] > 0
        ? ($performanceSummary['indicators_with_targets'] / $performanceSummary['total_indicators']) * 100
        : 0;
        $sheet->setCellValue('C8', number_format($targetCompleteness, 1) . '%');

        $sheet->setCellValue('A9', 'Indicators with Data:');
        $sheet->setCellValue('B9', $performanceSummary['indicators_with_data']);
        $dataCompleteness = $performanceSummary['total_indicators'] > 0
        ? ($performanceSummary['indicators_with_data'] / $performanceSummary['total_indicators']) * 100
        : 0;
        $sheet->setCellValue('C9', number_format($dataCompleteness, 1) . '%');

        $sheet->setCellValue('A10', 'Indicators at 100%:');
        $sheet->setCellValue('B10', $performanceSummary['indicators_at_100']);
        $perfectPercentage = $performanceSummary['indicators_with_data'] > 0
        ? ($performanceSummary['indicators_at_100'] / $performanceSummary['indicators_with_data']) * 100
        : 0;
        $sheet->setCellValue('C10', number_format($perfectPercentage, 1) . '%');

        // Total clusters
        $sheet->setCellValue('A11', 'Total Clusters:');
        $sheet->setCellValue('B11', count($performanceSummary['clusters_data']));

        // Add a separator
        $sheet->mergeCells('A12:F12');
        $sheet->getStyle('A12:F12')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);

        // Performance by category section
        $sheet->setCellValue('A13', 'PERFORMANCE BY CATEGORY');
        $sheet->mergeCells('A13:F13');
        $sheet->getStyle('A13:F13')->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Category headers
        $sheet->setCellValue('A14', 'Category');
        $sheet->setCellValue('B14', 'Count');
        $sheet->setCellValue('C14', 'Percentage');

        $sheet->getStyle('A14:C14')->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
        ]);

        // Category data
        $row        = 15;
        $categories = [
            'Over Achieved'  => 'purple',
            'Met'            => 'dark-green',
            'On Track'       => 'light-green',
            'In Progress'    => 'yellow',
            'Not Performing' => 'red',
            'Qualitative'    => 'blue',
            'Not Available'  => 'gray',
        ];

        foreach ($categories as $category => $colorName) {
            $count      = $performanceSummary['category_counts'][$category] ?? 0;
            $percentage = $performanceSummary['indicators_with_data'] > 0
            ? ($count / $performanceSummary['indicators_with_data']) * 100
            : 0;

            $sheet->setCellValue('A' . $row, $category);
            $sheet->setCellValue('B' . $row, $count);
            $sheet->setCellValue('C' . $row, number_format($percentage, 1) . '%');

            // Style based on category
            $color = $this->getCategoryColorByName($colorName);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => $color],
                ],
            ]);

            $row++;
        }

        // Add a separator
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        // Top Strategic Objectives section
        $sheet->setCellValue('A' . $row, 'TOP STRATEGIC OBJECTIVES');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Strategic objectives headers
        $sheet->setCellValue('A' . $row, 'Strategic Objective');
        $sheet->setCellValue('B' . $row, 'Name');
        $sheet->setCellValue('C' . $row, 'Indicators');
        $sheet->setCellValue('D' . $row, 'Name');
        $sheet->setCellValue('C' . $row, 'Indicators');
        $sheet->setCellValue('D' . $row, 'Average Score');
        $sheet->setCellValue('E' . $row, 'Status');

        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
        ]);
        $row++;

        // Sort strategic objectives by average score
        $sortedSOs = $performanceSummary['strategic_objectives'];
        uasort($sortedSOs, function ($a, $b) {
            if ($a['average_score'] === null && $b['average_score'] === null) {
                return 0;
            }

            if ($a['average_score'] === null) {
                return 1;
            }

            if ($b['average_score'] === null) {
                return -1;
            }

            return $b['average_score'] <=> $a['average_score'];
        });

        // Take top 5 strategic objectives
        $count = 0;
        foreach ($sortedSOs as $soID => $so) {
            if ($count >= 5) {
                break;
            }

            if ($so['scorable_indicators'] > 0) {
                $sheet->setCellValue('A' . $row, $soID);
                $sheet->setCellValue('B' . $row, $so['name']);
                $sheet->setCellValue('C' . $row, $so['indicators']);

                if ($so['average_score'] !== null) {
                    $sheet->setCellValue('D' . $row, number_format($so['average_score'], 1) . '%');

                    // Determine status based on average score
                    $status = 'Not Available';
                    if ($so['average_score'] >= 90) {
                        $status = 'Met';
                    } elseif ($so['average_score'] >= 50) {
                        $status = 'On Track';
                    } elseif ($so['average_score'] >= 10) {
                        $status = 'In Progress';
                    } else {
                        $status = 'Not Performing';
                    }

                    $sheet->setCellValue('E' . $row, $status);

                    // Style based on status
                    $color = $this->getCategoryColor($status);
                    $sheet->getStyle('D' . $row . ':E' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => $color],
                        ],
                    ]);
                } else {
                    $sheet->setCellValue('D' . $row, 'N/A');
                    $sheet->setCellValue('E' . $row, 'Not Available');
                }

                $count++;
                $row++;
            }
        }

        // Add a separator
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        // Top Performing Clusters section
        $sheet->setCellValue('A' . $row, 'TOP PERFORMING CLUSTERS');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F5E9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Cluster headers
        $sheet->setCellValue('A' . $row, 'Cluster');
        $sheet->setCellValue('B' . $row, 'Indicators');
        $sheet->setCellValue('C' . $row, 'Average Score');
        $sheet->setCellValue('D' . $row, 'Status');

        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
        ]);
        $row++;

        // Sort clusters by average score
        $sortedClusters = $performanceSummary['clusters_data'];
        uasort($sortedClusters, function ($a, $b) {
            if ($a['average_score'] === null && $b['average_score'] === null) {
                return 0;
            }

            if ($a['average_score'] === null) {
                return 1;
            }

            if ($b['average_score'] === null) {
                return -1;
            }

            return $b['average_score'] <=> $a['average_score'];
        });

        // Take top 5 clusters
        $count = 0;
        foreach ($sortedClusters as $clusterID => $clusterData) {
            if ($count >= 5) {
                break;
            }

            if ($clusterData['scorable_indicators'] > 0) {
                $sheet->setCellValue('A' . $row, $clusterData['name']);
                $sheet->setCellValue('B' . $row, $clusterData['indicators_with_data'] . '/' . $clusterData['indicators']);

                if ($clusterData['average_score'] !== null) {
                    $sheet->setCellValue('C' . $row, number_format($clusterData['average_score'], 1) . '%');

                    // Determine status based on average score
                    $status = 'Not Available';
                    if ($clusterData['average_score'] >= 90) {
                        $status = 'Met';
                    } elseif ($clusterData['average_score'] >= 50) {
                        $status = 'On Track';
                    } elseif ($clusterData['average_score'] >= 10) {
                        $status = 'In Progress';
                    } else {
                        $status = 'Not Performing';
                    }

                    $sheet->setCellValue('D' . $row, $status);

                    // Style based on status
                    $color = $this->getCategoryColor($status);
                    $sheet->getStyle('C' . $row . ':D' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => $color],
                        ],
                    ]);
                } else {
                    $sheet->setCellValue('C' . $row, 'N/A');
                    $sheet->setCellValue('D' . $row, 'Not Available');
                }

                $count++;
                $row++;
            }
        }

        // Add a separator
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        // Critical indicators section
        $sheet->setCellValue('A' . $row, 'CRITICAL INDICATORS REQUIRING ATTENTION');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFEBEE'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Critical indicators headers
        $sheet->setCellValue('A' . $row, 'Indicator');
        $sheet->setCellValue('B' . $row, 'Name');
        $sheet->setCellValue('C' . $row, 'Strategic Objective');
        $sheet->setCellValue('D' . $row, 'Lowest Score');
        $sheet->setCellValue('E' . $row, 'Affected Clusters');

        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
        ]);
        $row++;

        // Critical indicators data
        if (! empty($insights['critical_indicators'])) {
            foreach ($insights['critical_indicators'] as $indicator) {
                $sheet->setCellValue('A' . $row, $indicator['indicator_number']);
                $sheet->setCellValue('B' . $row, $indicator['indicator_name']);
                $sheet->setCellValue('C' . $row, $indicator['strategic_objective']);
                $sheet->setCellValue('D' . $row, number_format($indicator['lowest_score'], 1) . '%');
                $sheet->setCellValue('E' . $row, implode(', ', $indicator['clusters']));

                // Style the score
                $sheet->getStyle('D' . $row)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => 'FF0000'],
                        'bold'  => true,
                    ],
                ]);

                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No critical indicators identified.');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $row++;
        }

        // Add a separator
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        // Key insights section
        $sheet->setCellValue('A' . $row, 'KEY INSIGHTS');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8EAF6'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Key insights data
        if (! empty($insights['observations'])) {
            foreach (array_slice($insights['observations'], 0, 5) as $observation) {
                $sheet->setCellValue('A' . $row, '• ' . $observation);
                $sheet->mergeCells('A' . $row . ':F' . $row);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No insights available.');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $row++;
        }

        // Add a separator
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        // Cluster comparisons section
        $sheet->setCellValue('A' . $row, 'CLUSTER COMPARISONS');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3E5F5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Cluster comparisons data
        if (! empty($insights['cluster_comparisons'])) {
            foreach ($insights['cluster_comparisons'] as $comparison) {
                $sheet->setCellValue('A' . $row, '• ' . $comparison);
                $sheet->mergeCells('A' . $row . ':F' . $row);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No cluster comparisons available.');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $row++;
        }

        // Add a separator
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $row++;

        // Footer
        $sheet->setCellValue('A' . $row, 'Generated on: ' . date('F j, Y, g:i a'));
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row)->applyFromArray([
            'font'      => [
                'italic' => true,
                'size'   => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Apply borders to the entire dashboard
        $lastRow = $row;
        $sheet->getStyle('A1:F' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
    }

    /**
     * Generate the detailed metrics sheet for combined clusters.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $performanceData
     * @param object $timeline
     * @return void
     */
    private function generateCombinedDetailedMetricsSheet($sheet, $performanceData, $timeline)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(20);
        $sheet->getColumnDimension('J')->setWidth(30);

        // Create header
        $sheet->setCellValue('A1', 'DETAILED PERFORMANCE METRICS - ALL CLUSTERS');
        $sheet->mergeCells('A1:J1');

        // Style the header
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font'      => [
                'bold'  => true,
                'size'  => 16,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0F2F1'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Report title
        $sheet->setCellValue('A2', 'Combined Report - ' . $timeline->ReportName);
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2:J2')->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Table headers
        $sheet->setCellValue('A4', 'Indicator ID');
        $sheet->setCellValue('B4', 'Indicator Name');
        $sheet->setCellValue('C4', 'SO ID');
        $sheet->setCellValue('D4', 'Cluster');
        $sheet->setCellValue('E4', 'Target Value');
        $sheet->setCellValue('F4', 'Target Year');
        $sheet->setCellValue('G4', 'Actual Value');
        $sheet->setCellValue('H4', 'Score (%)');
        $sheet->setCellValue('I4', 'Status');
        $sheet->setCellValue('J4', 'Comments');

        // Style the headers
        $sheet->getStyle('A4:J4')->applyFromArray([
            'font'      => [
                'bold' => true,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Populate data
        $row = 5;
        foreach ($performanceData as $data) {
            // Skip invalid data entries
            if (! isset($data['indicator']) || ! isset($data['cluster'])) {
                continue;
            }

            $indicator          = $data['indicator'];
            $score              = $data['score'] ?? [];
            $target             = $data['target'] ?? null;
            $performance        = $data['performance'] ?? null;
            $strategicObjective = $data['strategicObjective'] ?? null;
            $cluster            = $data['cluster'];

            // Extract the strategic objective ID
            $soID = 'Unknown';
            if (isset($strategicObjective->StrategicObjectiveID) && ! empty($strategicObjective->StrategicObjectiveID)) {
                $soID = trim($strategicObjective->StrategicObjectiveID);
            } elseif (isset($strategicObjective->SO_ID) && ! empty($strategicObjective->SO_ID)) {
                $soID = trim($strategicObjective->SO_ID);
            } elseif (isset($indicator->SO_ID) && ! empty($indicator->SO_ID)) {
                $soID = trim($indicator->SO_ID);
            }

            $sheet->setCellValue('A' . $row, $indicator->Indicator_Number ?? $indicator->id);
            $sheet->setCellValue('B' . $row, $indicator->Indicator_Name ?? 'Unknown Indicator');
            $sheet->setCellValue('C' . $row, $soID);
            $sheet->setCellValue('D' . $row, $cluster->Cluster_Name ?? 'Unknown Cluster');

            // Target information
            if (isset($score['has_target']) && $score['has_target']) {
                $sheet->setCellValue('E' . $row, $score['target_value'] ?? 'N/A');
                $sheet->setCellValue('F' . $row, $score['target_year_range'] ?? 'N/A');
            } else {
                $sheet->setCellValue('E' . $row, 'Not Set');
                $sheet->setCellValue('F' . $row, 'Not Set');
            }

            // Performance information
            if (isset($score['has_performance']) && $score['has_performance']) {
                $sheet->setCellValue('G' . $row, $score['raw_value'] ?? 'N/A');
                $sheet->setCellValue('H' . $row, isset($score['percentage']) ? number_format($score['percentage'], 1) . '%' : 'N/A');
                $sheet->setCellValue('I' . $row, $score['category'] ?? 'Not Available');
                $sheet->setCellValue('J' . $row, $score['comment'] ?? '');

                // Style the score cell based on category
                if (isset($score['category'])) {
                    $color = $this->getCategoryColor($score['category']);
                    $sheet->getStyle('H' . $row . ':I' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => $color],
                            'bold'  => true,
                        ],
                    ]);
                }
            } else {
                $sheet->setCellValue('G' . $row, 'No Data');
                $sheet->setCellValue('H' . $row, 'N/A');
                $sheet->setCellValue('I' . $row, 'Not Available');
                $sheet->setCellValue('J' . $row, $score['error'] ?? '');
            }

            $row++;
        }

        // Apply borders to the entire table
        $lastRow = $row - 1;
        if ($lastRow >= 4) {
            $sheet->getStyle('A4:J' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            // Auto-filter
            $sheet->setAutoFilter('A4:J' . $lastRow);
        }

        // Freeze panes
        $sheet->freezePane('A5');
    }
/**
 * Generate the insights sheet for combined clusters.
 *
 * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
 * @param array $insights
 * @param object $timeline
 * @return void
 */
    private function generateCombinedInsightsSheet($sheet, $insights, $timeline)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(75);

        // Create header
        $sheet->setCellValue('A1', 'INSIGHTS & RECOMMENDATIONS - ALL CLUSTERS');
        $sheet->mergeCells('A1:B1');

        // Style the header
        $sheet->getStyle('A1:B1')->applyFromArray([
            'font'      => [
                'bold'  => true,
                'size'  => 16,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0F2F1'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Report title
        $sheet->setCellValue('A2', 'Combined Report - ' . $timeline->ReportName);
        $sheet->mergeCells('A2:B2');
        $sheet->getStyle('A2:B2')->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $row = 4;

        // Data Quality section
        $sheet->setCellValue('A' . $row, 'DATA QUALITY ASSESSMENT');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF9C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        if (! empty($insights['data_quality'])) {
            foreach ($insights['data_quality'] as $item) {
                $sheet->setCellValue('A' . $row, 'Data Quality:');
                $sheet->setCellValue('B' . $row, $item);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'Data Quality:');
            $sheet->setCellValue('B' . $row, 'No data quality insights available.');
            $row++;
        }

        $row++;

        // Key Observations section
        $sheet->setCellValue('A' . $row, 'KEY OBSERVATIONS');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        if (! empty($insights['observations'])) {
            foreach ($insights['observations'] as $index => $observation) {
                $sheet->setCellValue('A' . $row, 'Observation ' . ($index + 1) . ':');
                $sheet->setCellValue('B' . $row, $observation);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'Observations:');
            $sheet->setCellValue('B' . $row, 'No observations available.');
            $row++;
        }

        $row++;

        // Recommendations section
        $sheet->setCellValue('A' . $row, 'RECOMMENDATIONS');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F5E9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        if (! empty($insights['recommendations'])) {
            foreach ($insights['recommendations'] as $index => $recommendation) {
                $sheet->setCellValue('A' . $row, 'Recommendation ' . ($index + 1) . ':');
                $sheet->setCellValue('B' . $row, $recommendation);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'Recommendations:');
            $sheet->setCellValue('B' . $row, 'No recommendations available.');
            $row++;
        }

        $row++;

        // Cluster Comparisons section
        $sheet->setCellValue('A' . $row, 'CLUSTER COMPARISONS');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3E5F5'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        if (! empty($insights['cluster_comparisons'])) {
            foreach ($insights['cluster_comparisons'] as $index => $comparison) {
                $sheet->setCellValue('A' . $row, 'Comparison ' . ($index + 1) . ':');
                $sheet->setCellValue('B' . $row, $comparison);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'Cluster Comparisons:');
            $sheet->setCellValue('B' . $row, 'No cluster comparison insights available.');
            $row++;
        }

        $row++;

        // Trends section
        $sheet->setCellValue('A' . $row, 'PERFORMANCE TRENDS');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFEBEE'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        if (! empty($insights['trends'])) {
            foreach ($insights['trends'] as $index => $trend) {
                $sheet->setCellValue('A' . $row, 'Trend ' . ($index + 1) . ':');
                $sheet->setCellValue('B' . $row, $trend);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'Trends:');
            $sheet->setCellValue('B' . $row, 'No trend insights available.');
            $row++;
        }

        $row++;

        // Critical Indicators section
        $sheet->setCellValue('A' . $row, 'CRITICAL INDICATORS');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFCDD2'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Critical indicators table headers
        $sheet->setCellValue('A' . $row, 'Indicator');
        $sheet->setCellValue('B' . $row, 'Details');
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
        ]);
        $row++;

        if (! empty($insights['critical_indicators'])) {
            foreach ($insights['critical_indicators'] as $indicator) {
                $sheet->setCellValue('A' . $row, $indicator['indicator_number'] . ': ' . $indicator['indicator_name']);

                $details = "Strategic Objective: " . $indicator['strategic_objective'] . "\n";
                $details .= "Lowest Score: " . number_format($indicator['lowest_score'], 1) . "%\n";
                $details .= "Target Year Range: " . $indicator['target_year_range'] . "\n";
                $details .= "Affected Clusters: " . implode(", ", $indicator['clusters']);

                $sheet->setCellValue('B' . $row, $details);
                $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
                $sheet->getRowDimension($row)->setRowHeight(60);

                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'Critical Indicators:');
            $sheet->setCellValue('B' . $row, 'No critical indicators identified.');
            $row++;
        }

        // Apply borders to the entire insights sheet
        $sheet->getStyle('A1:B' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
    }

/**
 * Generate the strategic objectives sheet for combined clusters.
 *
 * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
 * @param array $performanceSummary
 * @param object $timeline
 * @return void
 */
    private function generateCombinedStrategicObjectivesSheet($sheet, $performanceSummary, $timeline)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(60);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(20);

        // Create header
        $sheet->setCellValue('A1', 'STRATEGIC OBJECTIVES PERFORMANCE - ALL CLUSTERS');
        $sheet->mergeCells('A1:H1');

        // Style the header
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font'      => [
                'bold'  => true,
                'size'  => 16,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0F2F1'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Report title
        $sheet->setCellValue('A2', 'Combined Report - ' . $timeline->ReportName);
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2:H2')->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Table headers
        $sheet->setCellValue('A4', 'SO ID');
        $sheet->setCellValue('B4', 'Strategic Objective Name');
        $sheet->setCellValue('C4', 'Description');
        $sheet->setCellValue('D4', 'Total Indicators');
        $sheet->setCellValue('E4', 'Indicators with Data');
        $sheet->setCellValue('F4', 'Data Completeness');
        $sheet->setCellValue('G4', 'Average Score');
        $sheet->setCellValue('H4', 'Status');

        // Style the headers
        $sheet->getStyle('A4:H4')->applyFromArray([
            'font'      => [
                'bold' => true,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Populate data
        $row = 5;
        if (isset($performanceSummary['strategic_objectives']) && ! empty($performanceSummary['strategic_objectives'])) {
            // Sort strategic objectives by average score (descending)
            $sortedSOs = $performanceSummary['strategic_objectives'];
            uasort($sortedSOs, function ($a, $b) {
                if ($a['average_score'] === null && $b['average_score'] === null) {
                    return 0;
                }

                if ($a['average_score'] === null) {
                    return 1;
                }

                if ($b['average_score'] === null) {
                    return -1;
                }

                return $b['average_score'] <=> $a['average_score'];
            });

            foreach ($sortedSOs as $soID => $so) {
                $sheet->setCellValue('A' . $row, $soID);
                $sheet->setCellValue('B' . $row, $so['name']);
                $sheet->setCellValue('C' . $row, $so['description']);
                $sheet->setCellValue('D' . $row, $so['indicators']);

                $indicatorsWithData = $so['scorable_indicators'] ?? 0;
                $sheet->setCellValue('E' . $row, $indicatorsWithData);

                // Calculate data completeness
                $dataCompleteness = $so['indicators'] > 0 ? ($indicatorsWithData / $so['indicators']) * 100 : 0;
                $sheet->setCellValue('F' . $row, number_format($dataCompleteness, 1) . '%');

                // Style data completeness cell based on percentage
                if ($dataCompleteness < 50) {
                    $sheet->getStyle('F' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => 'FF0000'], // Red for low completeness
                            'bold'  => true,
                        ],
                    ]);
                } elseif ($dataCompleteness < 80) {
                    $sheet->getStyle('F' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => 'FFA500'], // Orange for medium completeness
                        ],
                    ]);
                }

                if ($so['average_score'] !== null) {
                    $sheet->setCellValue('G' . $row, number_format($so['average_score'], 1) . '%');

                    // Determine status based on average score
                    $status = 'Not Available';
                    if ($so['average_score'] > 100) {
                        $status = 'Over Achieved';
                    } elseif ($so['average_score'] >= 90) {
                        $status = 'Met';
                    } elseif ($so['average_score'] >= 50) {
                        $status = 'On Track';
                    } elseif ($so['average_score'] >= 10) {
                        $status = 'In Progress';
                    } else {
                        $status = 'Not Performing';
                    }

                    $sheet->setCellValue('H' . $row, $status);

                    // Style the score and status cells based on status
                    $color = $this->getCategoryColor($status);
                    $sheet->getStyle('G' . $row . ':H' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => $color],
                            'bold'  => true,
                        ],
                    ]);
                } else {
                    $sheet->setCellValue('G' . $row, 'N/A');
                    $sheet->setCellValue('H' . $row, 'Not Available');
                }

                // Wrap text in description cell
                $sheet->getStyle('C' . $row)->getAlignment()->setWrapText(true);
                $sheet->getRowDimension($row)->setRowHeight(40);

                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No strategic objectives data available.');
            $sheet->mergeCells('A' . $row . ':H' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $row++;
        }

        // Apply borders to the entire table
        $lastRow = $row - 1;
        if ($lastRow >= 4) {
            $sheet->getStyle('A4:H' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            // Auto-filter
            $sheet->setAutoFilter('A4:H' . $lastRow);
        }

        // Add a summary section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'STRATEGIC OBJECTIVES SUMMARY');
        $sheet->mergeCells('A' . $row . ':H' . $row);
        $sheet->getStyle('A' . $row . ':H' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Count strategic objectives by status
        $statusCounts = [
            'Over Achieved'  => 0,
            'Met'            => 0,
            'On Track'       => 0,
            'In Progress'    => 0,
            'Not Performing' => 0,
            'Not Available'  => 0,
        ];

        foreach ($performanceSummary['strategic_objectives'] as $so) {
            if ($so['average_score'] === null) {
                $statusCounts['Not Available']++;
            } elseif ($so['average_score'] > 100) {
                $statusCounts['Over Achieved']++;
            } elseif ($so['average_score'] >= 90) {
                $statusCounts['Met']++;
            } elseif ($so['average_score'] >= 50) {
                $statusCounts['On Track']++;
            } elseif ($so['average_score'] >= 10) {
                $statusCounts['In Progress']++;
            } else {
                $statusCounts['Not Performing']++;
            }
        }

        // Add summary table
        $sheet->setCellValue('A' . $row, 'Status');
        $sheet->setCellValue('B' . $row, 'Count');
        $sheet->setCellValue('C' . $row, 'Percentage');
        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
        ]);
        $row++;

        $totalSOs = count($performanceSummary['strategic_objectives']);
        foreach ($statusCounts as $status => $count) {
            $percentage = $totalSOs > 0 ? ($count / $totalSOs) * 100 : 0;

            $sheet->setCellValue('A' . $row, $status);
            $sheet->setCellValue('B' . $row, $count);
            $sheet->setCellValue('C' . $row, number_format($percentage, 1) . '%');

            // Style based on status
            $color = $this->getCategoryColor($status);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => $color],
                    'bold'  => true,
                ],
            ]);

            $row++;
        }

        // Apply borders to the summary table
        $sheet->getStyle('A' . ($row - count($statusCounts) - 1) . ':C' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Freeze panes
        $sheet->freezePane('A5');
    }

/**
 * Generate the cluster comparison sheet.
 *
 * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
 * @param array $performanceSummary
 * @param object $timeline
 * @return void
 */
    private function generateClusterComparisonSheet($sheet, $performanceSummary, $timeline)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20);
        $sheet->getColumnDimension('G')->setWidth(20);

        // Create header
        $sheet->setCellValue('A1', 'CLUSTER PERFORMANCE COMPARISON');
        $sheet->mergeCells('A1:G1');

        // Style the header
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font'      => [
                'bold'  => true,
                'size'  => 16,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0F2F1'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Report title
        $sheet->setCellValue('A2', 'Combined Report - ' . $timeline->ReportName);
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2:G2')->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Table headers
        $sheet->setCellValue('A4', 'Cluster Name');
        $sheet->setCellValue('B4', 'Total Indicators');
        $sheet->setCellValue('C4', 'Indicators with Targets');
        $sheet->setCellValue('D4', 'Indicators with Data');
        $sheet->setCellValue('E4', 'Data Completeness');
        $sheet->setCellValue('F4', 'Average Score');
        $sheet->setCellValue('G4', 'Status');

        // Style the headers
        $sheet->getStyle('A4:G4')->applyFromArray([
            'font'      => [
                'bold' => true,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Populate data
        $row = 5;
        if (isset($performanceSummary['clusters_data']) && ! empty($performanceSummary['clusters_data'])) {
            // Sort clusters by average score (descending)
            $sortedClusters = $performanceSummary['clusters_data'];
            uasort($sortedClusters, function ($a, $b) {
                if ($a['average_score'] === null && $b['average_score'] === null) {
                    return 0;
                }

                if ($a['average_score'] === null) {
                    return 1;
                }

                if ($b['average_score'] === null) {
                    return -1;
                }

                return $b['average_score'] <=> $a['average_score'];
            });

            foreach ($sortedClusters as $clusterID => $cluster) {
                $sheet->setCellValue('A' . $row, $cluster['name']);
                $sheet->setCellValue('B' . $row, $cluster['indicators']);
                $sheet->setCellValue('C' . $row, $cluster['indicators_with_targets']);
                $sheet->setCellValue('D' . $row, $cluster['indicators_with_data']);

                // Calculate data completeness
                $dataCompleteness = $cluster['indicators'] > 0 ? ($cluster['indicators_with_data'] / $cluster['indicators']) * 100 : 0;
                $sheet->setCellValue('E' . $row, number_format($dataCompleteness, 1) . '%');

                // Style data completeness cell based on percentage
                if ($dataCompleteness < 50) {
                    $sheet->getStyle('E' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => 'FF0000'], // Red for low completeness
                            'bold'  => true,
                        ],
                    ]);
                } elseif ($dataCompleteness < 80) {
                    $sheet->getStyle('E' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => 'FFA500'], // Orange for medium completeness
                        ],
                    ]);
                }

                if ($cluster['average_score'] !== null) {
                    $sheet->setCellValue('F' . $row, number_format($cluster['average_score'], 1) . '%');

                    // Determine status based on average score
                    $status = 'Not Available';
                    if ($cluster['average_score'] > 100) {
                        $status = 'Over Achieved';
                    } elseif ($cluster['average_score'] >= 90) {
                        $status = 'Met';
                    } elseif ($cluster['average_score'] >= 50) {
                        $status = 'On Track';
                    } elseif ($cluster['average_score'] >= 10) {
                        $status = 'In Progress';
                    } else {
                        $status = 'Not Performing';
                    }

                    $sheet->setCellValue('G' . $row, $status);

                    // Style the score and status cells based on status
                    $color = $this->getCategoryColor($status);
                    $sheet->getStyle('F' . $row . ':G' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => $color],
                            'bold'  => true,
                        ],
                    ]);
                } else {
                    $sheet->setCellValue('F' . $row, 'N/A');
                    $sheet->setCellValue('G' . $row, 'Not Available');
                }

                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No cluster data available.');
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $row++;
        }

        // Apply borders to the entire table
        $lastRow = $row - 1;
        if ($lastRow >= 4) {
            $sheet->getStyle('A4:G' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);

            // Auto-filter
            $sheet->setAutoFilter('A4:G' . $lastRow);
        }

        // Add a comparison analysis section
        $row += 2;
        $sheet->setCellValue('A' . $row, 'CLUSTER PERFORMANCE ANALYSIS');
        $sheet->mergeCells('A' . $row . ':G' . $row);
        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Find best and worst performing clusters
        $bestCluster  = null;
        $worstCluster = null;
        $bestScore    = -1;
        $worstScore   = 101;

        foreach ($performanceSummary['clusters_data'] as $clusterID => $cluster) {
            if ($cluster['average_score'] !== null) {
                if ($cluster['average_score'] > $bestScore) {
                    $bestScore   = $cluster['average_score'];
                    $bestCluster = $cluster;
                }

                if ($cluster['average_score'] < $worstScore) {
                    $worstScore   = $cluster['average_score'];
                    $worstCluster = $cluster;
                }
            }
        }

        // Add analysis text
        if ($bestCluster && $worstCluster) {
            $sheet->setCellValue('A' . $row, 'Best Performing Cluster:');
            $sheet->setCellValue('B' . $row, $bestCluster['name']);
            $sheet->setCellValue('C' . $row, number_format($bestScore, 1) . '%');
            $sheet->getStyle('C' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => '008000'], // Green for best performer
                    'bold'  => true,
                ],
            ]);
            $row++;

            $sheet->setCellValue('A' . $row, 'Lowest Performing Cluster:');
            $sheet->setCellValue('B' . $row, $worstCluster['name']);
            $sheet->setCellValue('C' . $row, number_format($worstScore, 1) . '%');
            $sheet->getStyle('C' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => 'FF0000'], // Red for worst performer
                    'bold'  => true,
                ],
            ]);
            $row++;

            $performanceGap = $bestScore - $worstScore;
            $sheet->setCellValue('A' . $row, 'Performance Gap:');
            $sheet->setCellValue('B' . $row, number_format($performanceGap, 1) . '%');
            $row++;

            // Add recommendation based on gap
            $row++;
            $sheet->setCellValue('A' . $row, 'Recommendation:');
            if ($performanceGap > 30) {
                $sheet->setCellValue('B' . $row, 'Significant performance gap detected. Consider knowledge sharing between clusters to improve consistency.');
            } elseif ($performanceGap > 15) {
                $sheet->setCellValue('B' . $row, 'Moderate performance gap detected. Review practices in lower performing clusters.');
            } else {
                $sheet->setCellValue('B' . $row, 'Performance is relatively consistent across clusters.');
            }
            $sheet->mergeCells('B' . $row . ':G' . $row);
            $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
            $sheet->getRowDimension($row)->setRowHeight(40);
        } else {
            $sheet->setCellValue('A' . $row, 'Insufficient data to perform cluster comparison analysis.');
            $sheet->mergeCells('A' . $row . ':G' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
        }

        // Freeze panes
        $sheet->freezePane('A5');
    }

/**
 * Generate the data quality sheet.
 *
 * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
 * @param array $performanceSummary
 * @param object $timeline
 * @return void
 */
    private function generateDataQualitySheet($sheet, $performanceSummary, $timeline)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(20);

        // Create header
        $sheet->setCellValue('A1', 'DATA QUALITY ASSESSMENT');
        $sheet->mergeCells('A1:F1');

        // Style the header
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => [
                'bold'  => true,
                'size'  => 16,
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF9C4'],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Report title
        $sheet->setCellValue('A2', 'Combined Report - ' . $timeline->ReportName);
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2:F2')->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Overall data quality section
        $row = 4;
        $sheet->setCellValue('A' . $row, 'OVERALL DATA QUALITY');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Overall data quality metrics
        $sheet->setCellValue('A' . $row, 'Total Indicators:');
        $sheet->setCellValue('B' . $row, $performanceSummary['total_indicators']);
        $row++;

        $sheet->setCellValue('A' . $row, 'Indicators with Targets:');
        $sheet->setCellValue('B' . $row, $performanceSummary['indicators_with_targets']);
        $targetCompleteness = $performanceSummary['total_indicators'] > 0
        ? ($performanceSummary['indicators_with_targets'] / $performanceSummary['total_indicators']) * 100
        : 0;
        $sheet->setCellValue('C' . $row, number_format($targetCompleteness, 1) . '%');

        // Style target completeness
        if ($targetCompleteness < 50) {
            $sheet->getStyle('C' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => 'FF0000'], // Red for low completeness
                    'bold'  => true,
                ],
            ]);
        } elseif ($targetCompleteness < 80) {
            $sheet->getStyle('C' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => 'FFA500'], // Orange for medium completeness
                ],
            ]);
        }
        $row++;

        $sheet->setCellValue('A' . $row, 'Indicators with Data:');
        $sheet->setCellValue('B' . $row, $performanceSummary['indicators_with_data']);
        $dataCompleteness = $performanceSummary['total_indicators'] > 0
        ? ($performanceSummary['indicators_with_data'] / $performanceSummary['total_indicators']) * 100
        : 0;
        $sheet->setCellValue('C' . $row, number_format($dataCompleteness, 1) . '%');

        // Style data completeness
        if ($dataCompleteness < 50) {
            $sheet->getStyle('C' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => 'FF0000'], // Red for low completeness
                    'bold'  => true,
                ],
            ]);
        } elseif ($dataCompleteness < 80) {
            $sheet->getStyle('C' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => 'FFA500'], // Orange for medium completeness
                ],
            ]);
        }
        $row++;

        // Data quality assessment
        $row++;
        $sheet->setCellValue('A' . $row, 'Data Quality Assessment:');
        if ($dataCompleteness < 20) {
            $sheet->setCellValue('B' . $row, 'Critical - Data completeness is extremely low. Performance assessments are not reliable.');
            $sheet->getStyle('B' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => 'FF0000'], // Red for critical
                    'bold'  => true,
                ],
            ]);
        } elseif ($dataCompleteness < 50) {
            $sheet->setCellValue('B' . $row, 'Warning - Data completeness is low. Performance scores should be interpreted with caution.');
            $sheet->getStyle('B' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => 'FFA500'], // Orange for warning
                    'bold'  => true,
                ],
            ]);
        } elseif ($dataCompleteness < 80) {
            $sheet->setCellValue('B' . $row, 'Moderate - Data completeness is acceptable but could be improved for more accurate assessments.');
            $sheet->getStyle('B' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => '008000'], // Green for moderate
                ],
            ]);
        } else {
            $sheet->setCellValue('B' . $row, 'Good - Data completeness is high, providing a reliable basis for performance assessment.');
            $sheet->getStyle('B' . $row)->applyFromArray([
                'font' => [
                    'color' => ['rgb' => '006400'], // Dark green for good
                    'bold'  => true,
                ],
            ]);
        }
        $sheet->mergeCells('B' . $row . ':F' . $row);
        $row += 2;

        // Cluster data quality section
        $sheet->setCellValue('A' . $row, 'CLUSTER DATA QUALITY');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Cluster data quality table headers
        $sheet->setCellValue('A' . $row, 'Cluster');
        $sheet->setCellValue('B' . $row, 'Total Indicators');
        $sheet->setCellValue('C' . $row, 'Indicators with Targets');
        $sheet->setCellValue('D' . $row, 'Target Completeness');
        $sheet->setCellValue('E' . $row, 'Indicators with Data');
        $sheet->setCellValue('F' . $row, 'Data Completeness');

        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $row++;

        // Populate cluster data quality
        if (isset($performanceSummary['clusters_data']) && ! empty($performanceSummary['clusters_data'])) {
            // Sort clusters by data completeness (descending)
            $sortedClusters = $performanceSummary['clusters_data'];
            uasort($sortedClusters, function ($a, $b) {
                $aCompleteness = $a['indicators'] > 0 ? ($a['indicators_with_data'] / $a['indicators']) * 100 : 0;
                $bCompleteness = $b['indicators'] > 0 ? ($b['indicators_with_data'] / $b['indicators']) * 100 : 0;

                return $bCompleteness <=> $aCompleteness;
            });

            foreach ($sortedClusters as $clusterID => $cluster) {
                $sheet->setCellValue('A' . $row, $cluster['name']);
                $sheet->setCellValue('B' . $row, $cluster['indicators']);
                $sheet->setCellValue('C' . $row, $cluster['indicators_with_targets']);

                // Calculate target completeness
                $targetCompleteness = $cluster['indicators'] > 0 ? ($cluster['indicators_with_targets'] / $cluster['indicators']) * 100 : 0;
                $sheet->setCellValue('D' . $row, number_format($targetCompleteness, 1) . '%');

                // Style target completeness cell
                if ($targetCompleteness < 50) {
                    $sheet->getStyle('D' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => 'FF0000'], // Red for low completeness
                        ],
                    ]);
                } elseif ($targetCompleteness < 80) {
                    $sheet->getStyle('D' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => 'FFA500'], // Orange for medium completeness
                        ],
                    ]);
                }

                $sheet->setCellValue('E' . $row, $cluster['indicators_with_data']);

                // Calculate data completeness
                $dataCompleteness = $cluster['indicators'] > 0 ? ($cluster['indicators_with_data'] / $cluster['indicators']) * 100 : 0;
                $sheet->setCellValue('F' . $row, number_format($dataCompleteness, 1) . '%');

                // Style data completeness cell
                if ($dataCompleteness < 50) {
                    $sheet->getStyle('F' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => 'FF0000'], // Red for low completeness
                        ],
                    ]);
                } elseif ($dataCompleteness < 80) {
                    $sheet->getStyle('F' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => 'FFA500'], // Orange for medium completeness
                        ],
                    ]);
                }

                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No cluster data available.');
            $sheet->mergeCells('A' . $row . ':F' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $row++;
        }

        // Apply borders to the cluster table
        $clusterTableLastRow = $row - 1;
        if ($clusterTableLastRow >= ($row - 1)) {
            $sheet->getStyle('A' . ($row - count($performanceSummary['clusters_data']) - 1) . ':F' . $clusterTableLastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
        }

        $row += 2;

        // Strategic objectives data quality section
        $sheet->setCellValue('A' . $row, 'STRATEGIC OBJECTIVES DATA QUALITY');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E3F2FD'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Strategic objectives data quality table headers
        $sheet->setCellValue('A' . $row, 'Strategic Objective');
        $sheet->setCellValue('B' . $row, 'Total Indicators');
        $sheet->setCellValue('C' . $row, 'Indicators with Data');
        $sheet->setCellValue('D' . $row, 'Data Completeness');
        $sheet->setCellValue('E' . $row, 'Assessment');

        $sheet->getStyle('A' . $row . ':E' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F5F5F5'],
            ],
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $row++;

        // Populate strategic objectives data quality
        if (isset($performanceSummary['strategic_objectives']) && ! empty($performanceSummary['strategic_objectives'])) {
            // Sort strategic objectives by data completeness (ascending)
            $sortedSOs = $performanceSummary['strategic_objectives'];
            uasort($sortedSOs, function ($a, $b) {
                $aCompleteness = $a['indicators'] > 0 ? ($a['scorable_indicators'] / $a['indicators']) * 100 : 0;
                $bCompleteness = $b['indicators'] > 0 ? ($b['scorable_indicators'] / $b['indicators']) * 100 : 0;

                return $aCompleteness <=> $bCompleteness; // Sort ascending to highlight problematic SOs first
            });

            foreach ($sortedSOs as $soID => $so) {
                $sheet->setCellValue('A' . $row, $soID . ': ' . $so['name']);
                $sheet->setCellValue('B' . $row, $so['indicators']);
                $sheet->setCellValue('C' . $row, $so['scorable_indicators']);

                // Calculate data completeness
                $dataCompleteness = $so['indicators'] > 0 ? ($so['scorable_indicators'] / $so['indicators']) * 100 : 0;
                $sheet->setCellValue('D' . $row, number_format($dataCompleteness, 1) . '%');

                // Determine assessment
                $assessment = '';
                if ($dataCompleteness < 20) {
                    $assessment = 'Critical - Insufficient data';
                    $sheet->getStyle('D' . $row . ':E' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => 'FF0000'], // Red for critical
                            'bold'  => true,
                        ],
                    ]);
                } elseif ($dataCompleteness < 50) {
                    $assessment = 'Warning - Low data completeness';
                    $sheet->getStyle('D' . $row . ':E' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => 'FFA500'], // Orange for warning
                        ],
                    ]);
                } elseif ($dataCompleteness < 80) {
                    $assessment = 'Moderate - Acceptable data completeness';
                } else {
                    $assessment = 'Good - High data completeness';
                    $sheet->getStyle('D' . $row . ':E' . $row)->applyFromArray([
                        'font' => [
                            'color' => ['rgb' => '008000'], // Green for good
                        ],
                    ]);
                }

                $sheet->setCellValue('E' . $row, $assessment);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No strategic objectives data available.');
            $sheet->mergeCells('A' . $row . ':E' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $row++;
        }

        // Apply borders to the strategic objectives table
        $soTableLastRow = $row - 1;
        if ($soTableLastRow >= ($row - 1)) {
            $sheet->getStyle('A' . ($row - count($performanceSummary['strategic_objectives']) - 1) . ':E' . $soTableLastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ],
                ],
            ]);
        }

        $row += 2;

        // Recommendations section
        $sheet->setCellValue('A' . $row, 'DATA QUALITY RECOMMENDATIONS');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8F5E9'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Add recommendations based on data quality
        if ($dataCompleteness < 50) {
            $sheet->setCellValue('A' . $row, '1.');
            $sheet->setCellValue('B' . $row, 'Prioritize data collection for indicators across all clusters to improve overall data completeness.');
            $sheet->mergeCells('B' . $row . ':F' . $row);
            $row++;

            $sheet->setCellValue('A' . $row, '2.');
            $sheet->setCellValue('B' . $row, 'Implement a data quality monitoring system to track reporting compliance.');
            $sheet->mergeCells('B' . $row . ':F' . $row);
            $row++;

            $sheet->setCellValue('A' . $row, '3.');
            $sheet->setCellValue('B' . $row, 'Focus on improving data collection for strategic objectives with critical data gaps.');
            $sheet->mergeCells('B' . $row . ':F' . $row);
            $row++;
        } else {
            $sheet->setCellValue('A' . $row, '1.');
            $sheet->setCellValue('B' . $row, 'Continue maintaining good data collection practices.');
            $sheet->mergeCells('B' . $row . ':F' . $row);
            $row++;

            $sheet->setCellValue('A' . $row, '2.');
            $sheet->setCellValue('B' . $row, 'Consider implementing data quality audits to ensure accuracy of reported data.');
            $sheet->mergeCells('B' . $row . ':F' . $row);
            $row++;
        }

        // Freeze panes
        $sheet->freezePane('A5');
    }

/**
 * Add performance charts to the dashboard sheet.
 *
 * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet
 * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
 * @param array $performanceSummary
 * @param array $performanceData
 * @return void
 */
    private function addCombinedPerformanceCharts($spreadsheet, $sheet, $performanceSummary, $performanceData)
    {
        // 1. Category Distribution Pie Chart
        $categories = [
            'Over Achieved',
            'Met',
            'On Track',
            'In Progress',
            'Not Performing',
            'Qualitative',
            'Not Available',
        ];

        $categoryValues = [];
        foreach ($categories as $category) {
            $categoryValues[] = $performanceSummary['category_counts'][$category] ?? 0;
        }

        // Create a new worksheet for chart data
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('ChartData');

        // Add category data
        $dataSheet->setCellValue('A1', 'Category');
        $dataSheet->setCellValue('B1', 'Count');

        for ($i = 0; $i < count($categories); $i++) {
            $dataSheet->setCellValue('A' . ($i + 2), $categories[$i]);
            $dataSheet->setCellValue('B' . ($i + 2), $categoryValues[$i]);
        }

        // Create the pie chart
        $categoryChart = new Chart(
            'categoryPieChart',
            new Title('Performance by Category'),
            new Legend(Legend::POSITION_RIGHT, null, false),
            new PlotArea(
                null,
                [
                    new DataSeries(
                        DataSeries::TYPE_PIECHART,
                        null,
                        range(0, count($categories) - 1),
                        [
                            new DataSeriesValues('String', 'ChartData!$A$2:$A$' . (count($categories) + 1), null, count($categories)),
                        ],
                        [
                            new DataSeriesValues('Number', 'ChartData!$B$2:$B$' . (count($categories) + 1), null, count($categories)),
                        ]
                    ),
                ]
            )
        );

        // Set chart position
        $categoryChart->setTopLeftPosition('H5');
        $categoryChart->setBottomRightPosition('N15');

        // Add the chart to the dashboard sheet
        $sheet->addChart($categoryChart);

        // 2. Strategic Objectives Bar Chart
        // Sort strategic objectives by average score
        $sortedSOs = $performanceSummary['strategic_objectives'];
        uasort($sortedSOs, function ($a, $b) {
            if ($a['average_score'] === null && $b['average_score'] === null) {
                return 0;
            }

            if ($a['average_score'] === null) {
                return 1;
            }

            if ($b['average_score'] === null) {
                return -1;
            }

            return $b['average_score'] <=> $a['average_score'];
        });

        // Take top 5 strategic objectives
        $topSOs = array_slice($sortedSOs, 0, 5);

        // Add strategic objectives data
        $dataSheet->setCellValue('D1', 'Strategic Objective');
        $dataSheet->setCellValue('E1', 'Score');

        $row = 2;
        foreach ($topSOs as $soID => $so) {
            if ($so['average_score'] !== null) {
                $dataSheet->setCellValue('D' . $row, $soID);
                $dataSheet->setCellValue('E' . $row, $so['average_score']);
                $row++;
            }
        }

        // Create the bar chart
        $soChart = new Chart(
            'soBarChart',
            new Title('Top Strategic Objectives Performance'),
            new Legend(Legend::POSITION_BOTTOM, null, false),
            new PlotArea(
                null,
                [
                    new DataSeries(
                        DataSeries::TYPE_BARCHART,
                        null,
                        range(0, count($topSOs) - 1),
                        [
                            new DataSeriesValues('String', 'ChartData!$D$2:$D$' . ($row - 1), null, $row - 2),
                        ],
                        [
                            new DataSeriesValues('Number', 'ChartData!$E$2:$E$' . ($row - 1), null, $row - 2),
                        ]
                    ),
                ]
            )
        );

        // Set chart position
        $soChart->setTopLeftPosition('H16');
        $soChart->setBottomRightPosition('N26');

        // Add the chart to the dashboard sheet
        $sheet->addChart($soChart);

        // 3. Cluster Comparison Bar Chart
        // Sort clusters by average score
        $sortedClusters = $performanceSummary['clusters_data'];
        uasort($sortedClusters, function ($a, $b) {
            if ($a['average_score'] === null && $b['average_score'] === null) {
                return 0;
            }

            if ($a['average_score'] === null) {
                return 1;
            }

            if ($b['average_score'] === null) {
                return -1;
            }

            return $b['average_score'] <=> $a['average_score'];
        });

        // Take top 5 clusters
        $topClusters = array_slice($sortedClusters, 0, 5);

        // Add cluster data
        $dataSheet->setCellValue('G1', 'Cluster');
        $dataSheet->setCellValue('H1', 'Score');

        $row = 2;
        foreach ($topClusters as $clusterID => $cluster) {
            if ($cluster['average_score'] !== null) {
                $dataSheet->setCellValue('G' . $row, $cluster['name']);
                $dataSheet->setCellValue('H' . $row, $cluster . $row, $cluster['name']);
                $dataSheet->setCellValue('H' . $row, $cluster['average_score']);
                $row++;
            }
        }

        // Create the bar chart
        $clusterChart = new Chart(
            'clusterBarChart',
            new Title('Cluster Performance Comparison'),
            new Legend(Legend::POSITION_BOTTOM, null, false),
            new PlotArea(
                null,
                [
                    new DataSeries(
                        DataSeries::TYPE_BARCHART,
                        null,
                        range(0, count($topClusters) - 1),
                        [
                            new DataSeriesValues('String', 'ChartData!$G$2:$G$' . ($row - 1), null, $row - 2),
                        ],
                        [
                            new DataSeriesValues('Number', 'ChartData!$H$2:$H$' . ($row - 1), null, $row - 2),
                        ]
                    ),
                ]
            )
        );

        // Set chart position
        $clusterChart->setTopLeftPosition('H27');
        $clusterChart->setBottomRightPosition('N37');

        // Add the chart to the dashboard sheet
        $sheet->addChart($clusterChart);

        // Hide the data sheet
        $dataSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
    }

/**
 * Get the color for a performance category.
 *
 * @param string $category
 * @return string
 */
    private function getCategoryColor($category)
    {
        switch ($category) {
            case 'Over Achieved':
                return 'AF52DE'; // Purple
            case 'Met':
                return '30D158'; // Dark green
            case 'On Track':
                return '34C759'; // Light green
            case 'In Progress':
                return 'FFCC00'; // Yellow
            case 'Not Performing':
                return 'FF3B30'; // Red
            case 'Qualitative':
                return '5AC8FA'; // Blue
            case 'Insufficient Data':
                return 'FF9500'; // Orange
            case 'Not Available':
            default:
                return '8E8E93'; // Gray
        }
    }

/**
 * Get the color by color name.
 *
 * @param string $colorName
 * @return string
 */
    private function getCategoryColorByName($colorName)
    {
        switch ($colorName) {
            case 'purple':
                return 'AF52DE';
            case 'dark-green':
                return '30D158';
            case 'light-green':
                return '34C759';
            case 'yellow':
                return 'FFCC00';
            case 'red':
                return 'FF3B30';
            case 'blue':
                return '5AC8FA';
            case 'orange':
                return 'FF9500';
            case 'gray':
            default:
                return '8E8E93';
        }
    }
}