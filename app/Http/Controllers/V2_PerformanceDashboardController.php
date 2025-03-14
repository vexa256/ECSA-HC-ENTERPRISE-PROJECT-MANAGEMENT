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

class V2_PerformanceDashboardController extends Controller
{
    /**
     * Show the cluster selection screen.
     *
     * @return \Illuminate\View\View
     */
    public function showClusterSelection()
    {
        try {
            Log::info('Showing cluster selection screen');

            // Fetch all available clusters.
            $clusters = DB::table('clusters')
                ->select('id', 'Cluster_Name', 'ClusterID', 'Description')
                ->orderBy('Cluster_Name')
                ->get();

            Log::info('Found ' . $clusters->count() . ' clusters');

            return view('scrn', [
                'Page'     => 'EcsaPerfV2.select-cluster',
                'Title'    => 'Select Cluster',
                'Clusters' => $clusters,
                'Error'    => $clusters->isEmpty() ? 'No clusters found in the system.' : null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching clusters: ' . $e->getMessage());
            return view('scrn', [
                'Page'  => 'EcsaPerfV2.select-cluster',
                'Title' => 'Select Cluster',
                'Error' => 'An error occurred while fetching clusters. Please try again later.',
            ]);
        }
    }

    /**
     * Process cluster selection and redirect to timeline selection.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processClusterSelection(Request $request)
    {
        try {
            $validated = $request->validate([
                'cluster_id' => 'required|string|exists:clusters,ClusterID',
            ]);

            $clusterId = $validated['cluster_id'];
            Log::info('Cluster selected: ' . $clusterId);

            // Redirect to the timeline selection page with the cluster_id passed as a route parameter.
            return redirect()->route('performance.timeline.selection', ['cluster_id' => $clusterId]);
        } catch (\Exception $e) {
            Log::error('Error processing cluster selection: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while processing your selection.']);
        }
    }

    /**
     * Show the timeline selection screen for the chosen cluster.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showTimelineSelection(Request $request)
    {
        try {
            // Get the cluster_id from the route parameter or request input.
            $clusterID = $request->route('cluster_id') ?? $request->input('cluster_id');
            Log::info('Timeline selection screen - Cluster ID: ' . ($clusterID ?? 'null'));

            if (! $clusterID) {
                return redirect()->route('performance.cluster.selection')
                    ->withErrors(['error' => 'Please select a cluster first.']);
            }

            // Retrieve cluster details.
            $cluster = DB::table('clusters')
                ->where('ClusterID', $clusterID)
                ->first();

            if (! $cluster) {
                Log::warning('Selected cluster not found: ' . $clusterID);
                return redirect()->route('performance.cluster.selection')
                    ->withErrors(['error' => 'Selected cluster not found.']);
            }

            // Fetch all available timelines.
            $timelines = DB::table('ecsahc_timelines')
                ->select('id', 'ReportName', 'Type', 'ReportingID', 'Year', 'ClosingDate', 'status')
                ->orderBy('Year', 'desc')
                ->orderBy('ClosingDate', 'desc')
                ->get();

            Log::info('Found ' . $timelines->count() . ' timelines');

            // Pass the cluster details and cluster_id to the view.
            return view('scrn', [
                'Page'      => 'EcsaPerfV2.select-timeline',
                'Title'     => 'Select Timeline',
                'Cluster'   => $cluster,
                'Timelines' => $timelines,
                'Error'     => $timelines->isEmpty() ? 'No reporting timelines found in the system.' : null,
                'ClusterID' => $clusterID,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching timelines: ' . $e->getMessage());
            return view('scrn', [
                'Page'  => 'EcsaPerfV2.select-timeline',
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
                'cluster_id'  => 'required|string|exists:clusters,ClusterID',
            ]);

            $timelineId = $validated['timeline_id'];
            $clusterId  = $validated['cluster_id'];

            Log::info('Timeline selected: ' . $timelineId . ' with Cluster: ' . $clusterId);

            // Redirect to the dashboard with both cluster and timeline IDs passed as route parameters.
            return redirect()->route('performance.dashboard', [
                'cluster_id'  => $clusterId,
                'timeline_id' => $timelineId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing timeline selection: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'An error occurred while processing your selection.']);
        }
    }

    /**
     * Show the performance dashboard with indicators, scores, and insights.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showPerformanceDashboard(Request $request)
    {
        try {
            // Get cluster and timeline IDs from route parameters or request inputs.
            $clusterID  = $request->route('cluster_id') ?? $request->input('cluster_id');
            $timelineID = $request->route('timeline_id') ?? $request->input('timeline_id');

            Log::info('Dashboard - Cluster ID: ' . ($clusterID ?? 'null') . ', Timeline ID: ' . ($timelineID ?? 'null'));

            if (! $clusterID) {
                Log::warning('Missing cluster selection');
                return redirect()->route('performance.cluster.selection')
                    ->withErrors(['error' => 'Please select a cluster first.']);
            }

            if (! $timelineID) {
                Log::warning('Missing timeline selection');
                return redirect()->route('performance.timeline.selection', ['cluster_id' => $clusterID])
                    ->withErrors(['error' => 'Please select a timeline.']);
            }

            $cluster = DB::table('clusters')
                ->where('ClusterID', $clusterID)
                ->first();

            if (! $cluster) {
                Log::warning('Selected cluster not found: ' . $clusterID);
                return redirect()->route('performance.cluster.selection')
                    ->withErrors(['error' => 'Selected cluster not found.']);
            }

            $timeline = DB::table('ecsahc_timelines')
                ->where('ReportingID', $timelineID)
                ->first();

            if (! $timeline) {
                Log::warning('Selected timeline not found: ' . $timelineID);
                return redirect()->route('performance.timeline.selection', ['cluster_id' => $clusterID])
                    ->withErrors(['error' => 'Selected timeline not found.']);
            }

            $indicators = DB::table('performance_indicators')
                ->whereRaw('JSON_CONTAINS(Responsible_Cluster, ?)', [json_encode($clusterID)])
                ->get();

            Log::info('Found ' . $indicators->count() . ' indicators for cluster ' . $clusterID);

            $performanceData = [];
            if (! $indicators->isEmpty()) {
                $performanceData    = $this->processIndicatorPerformance($indicators, $clusterID, $timelineID, $timeline->Year);
                $performanceSummary = $this->calculatePerformanceSummary($performanceData);
                $insights           = $this->generateAIInsights($performanceData, $cluster, $timeline);
            } else {
                $performanceSummary = $this->getEmptyPerformanceSummary();
                $insights           = [];
            }

            return view('scrn', [
                'Page'               => 'EcsaPerfV2.indicator-performance',
                'Title'              => 'Performance Dashboard',
                'Cluster'            => $cluster,
                'Timeline'           => $timeline,
                'PerformanceData'    => $performanceData,
                'Insights'           => $insights,
                'PerformanceSummary' => $performanceSummary,
                'Error'              => $indicators->isEmpty() ? 'No indicators assigned to this cluster.' : null,
                'ClusterID'          => $clusterID,  // Make sure these are passed to the view
                'TimelineID'         => $timelineID, // Make sure these are passed to the view
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating performance dashboard: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return view('scrn', [
                'Page'  => 'EcsaPerfV2.indicator-performance',
                'Title' => 'Performance Dashboard',
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

            // UPDATED: Find target based on year range instead of exact match
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
            'target_year_range' => null, // Added to store the target year range
        ];

        if (! $target) {
            $score['error'] = 'No target set for this indicator.';
            return $score;
        }

        $score['has_target']        = true;
        $score['target_value']      = $target->Target_Value;
        $score['target_year_range'] = $target->Target_Year; // Store the target year range

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
     * Get empty performance summary structure.
     *
     * @return array
     */
    private function getEmptyPerformanceSummary()
    {
        return [
            'total_indicators'        => 0,
            'indicators_with_targets' => 0,
            'indicators_with_data'    => 0,
            'category_counts'         => [
                'Not Performing' => 0,
                'In Progress'    => 0,
                'On Track'       => 0,
                'Met'            => 0,
                'Over Achieved'  => 0,
                'Qualitative'    => 0,
                'Not Available'  => 0,
            ],
            'overall_score'           => 0,
            'overall_category'        => 'Not Available',
            'strategic_objectives'    => [],
            'perfect_score'           => false,
            'indicators_at_100'       => 0,
        ];
    }

    /**
     * Calculate overall performance summary.
     *
     * @param array $performanceData
     * @return array
     */
    private function calculatePerformanceSummary($performanceData)
    {
        $summary                     = $this->getEmptyPerformanceSummary();
        $summary['total_indicators'] = count($performanceData);

        if (empty($performanceData)) {
            return $summary;
        }

        // Track strategic objectives by their actual ID
        $strategicObjectives = [];
        $indicatorsAt100     = 0;
        $scorableIndicators  = 0;
        $totalScoreSum       = 0;

        foreach ($performanceData as $data) {
            if ($data['score']['has_target']) {
                $summary['indicators_with_targets']++;
            }
            if ($data['score']['has_performance']) {
                $summary['indicators_with_data']++;
            }

            // Count indicators by category
            $category = $data['score']['category'];
            if (isset($summary['category_counts'][$category])) {
                $summary['category_counts'][$category]++;
            }

            // Count indicators that achieved 100% or more
            if ($data['score']['percentage'] !== null && $data['score']['percentage'] >= 100) {
                $indicatorsAt100++;
            }

            // Count scorable indicators (those with percentage values)
            if ($data['score']['percentage'] !== null) {
                $scorableIndicators++;

                // For overall score calculation, cap individual percentages at 100%
                // This prevents over-achieved indicators from skewing the overall score
                $cappedPercentage = min(100, $data['score']['percentage']);
                $totalScoreSum += $cappedPercentage;
            }

            // Ensure we use the correct SO_ID
            $soID = null;
            if (isset($data['strategicObjective']->StrategicObjectiveID)) {
                $soID = trim($data['strategicObjective']->StrategicObjectiveID);
            } elseif (isset($data['strategicObjective']->SO_ID)) {
                $soID = trim($data['strategicObjective']->SO_ID);
            } elseif (isset($data['indicator']->SO_ID)) {
                $soID = trim($data['indicator']->SO_ID);
            } else {
                $soID = 'Unknown';
            }

            // Initialize strategic objective if not already done
            if (! isset($strategicObjectives[$soID])) {
                $strategicObjectives[$soID] = [
                    'name'                => isset($data['strategicObjective']->SO_Name) ?
                    $data['strategicObjective']->SO_Name : $soID,
                    'description'         => isset($data['strategicObjective']->Description) ?
                    $data['strategicObjective']->Description : '',
                    'indicators'          => 0,
                    'score_sum'           => 0,
                    'scorable_indicators' => 0,
                    'average_score'       => null,
                    'indicators_at_100'   => 0,
                    'perfect_score'       => false,
                ];
            }

            $strategicObjectives[$soID]['indicators']++;
            if ($data['score']['percentage'] !== null) {
                // For strategic objectives, also cap percentages at 100% for average calculation
                $cappedSOPercentage = min(100, $data['score']['percentage']);
                $strategicObjectives[$soID]['score_sum'] += $cappedSOPercentage;
                $strategicObjectives[$soID]['scorable_indicators']++;

                // Count indicators at 100% or more for this strategic objective
                if ($data['score']['percentage'] >= 100) {
                    $strategicObjectives[$soID]['indicators_at_100']++;
                }
            }
        }

        // Calculate average scores for each strategic objective
        foreach ($strategicObjectives as $soID => $so) {
            if ($so['scorable_indicators'] > 0) {
                // Calculate average score
                $strategicObjectives[$soID]['average_score'] =
                    $so['score_sum'] / $so['scorable_indicators'];

                // Determine if this strategic objective has a perfect score
                $strategicObjectives[$soID]['perfect_score'] =
                    ($so['indicators_at_100'] === $so['scorable_indicators']);
            }
        }

        // Set the strategic objectives in the summary
        $summary['strategic_objectives'] = $strategicObjectives;

        // Store the count of indicators that achieved 100% or more
        $summary['indicators_at_100'] = $indicatorsAt100;

        // Determine if the cluster has a perfect score (all indicators at 100% or more)
        $summary['perfect_score'] = ($scorableIndicators > 0 && $indicatorsAt100 === $scorableIndicators);

        // Calculate overall score
        if ($scorableIndicators > 0) {
            // Calculate the average score across all indicators (using capped percentages)
            $summary['overall_score'] = $totalScoreSum / $scorableIndicators;

            // If perfect score is required for 100%, adjust the overall score
            if (! $summary['perfect_score'] && $summary['overall_score'] === 100) {
                // If not all indicators are at 100% but the average is 100,
                // adjust to 99.9% to indicate it's not a perfect score
                $summary['overall_score'] = 99.9;
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
        }

        return $summary;
    }

    /**
     * Generate AI insights based on performance data.
     *
     * @param array $performanceData
     * @param object $cluster
     * @param object $timeline
     * @return array
     */
    /**
     * Generate AI insights based on performance data.
     *
     * @param array $performanceData
     * @param object $cluster
     * @param object $timeline
     * @return array
     */
    private function generateAIInsights($performanceData, $cluster, $timeline)
    {
        $insights = [
            'observations'        => [],
            'recommendations'     => [],
            'trends'              => [],
            'critical_indicators' => [],
        ];

        $summary = $this->calculatePerformanceSummary($performanceData);

        if ($summary['overall_score'] > 0) {
            $insights['observations'][] = "Overall performance for {$cluster->Cluster_Name} is at " .
            number_format($summary['overall_score'], 1) . "% (" . $summary['overall_category'] . ").";

            // Add insight about perfect score if relevant
            if ($summary['perfect_score']) {
                $insights['observations'][] = "All indicators have met their targets at 100%.";
            } else if ($summary['indicators_at_100'] > 0) {
                $perfectPercentage          = ($summary['indicators_at_100'] / $summary['indicators_with_data']) * 100;
                $insights['observations'][] = number_format($perfectPercentage, 1) .
                    "% of indicators have fully met their targets (100%).";
            }
        } else {
            $insights['observations'][] = "Unable to calculate overall performance due to insufficient data.";
        }

        if ($summary['total_indicators'] > 0) {
            $dataCompleteness = ($summary['indicators_with_data'] / $summary['total_indicators']) * 100;
            if ($dataCompleteness < 100) {
                $insights['observations'][] = "Data reporting is incomplete. Only " .
                number_format($dataCompleteness, 1) . "% of indicators have performance data.";
                $insights['recommendations'][] = "Improve data collection and reporting processes to ensure all indicators have performance data.";
            }
            $targetCompleteness = ($summary['indicators_with_targets'] / $summary['total_indicators']) * 100;
            if ($targetCompleteness < 100) {
                $insights['observations'][] = "Target setting is incomplete. Only " .
                number_format($targetCompleteness, 1) . "% of indicators have targets set.";
                $insights['recommendations'][] = "Ensure all indicators have appropriate targets set for meaningful performance measurement.";
            }
        } else {
            $insights['observations'][] = "No indicators found for analysis.";
        }

        if ($summary['indicators_with_data'] > 0) {
            if ($summary['category_counts']['Not Performing'] > 0) {
                $notPerformingPercentage = ($summary['category_counts']['Not Performing'] / $summary['indicators_with_data']) * 100;
                if ($notPerformingPercentage > 30) {
                    $insights['observations'][] = "A significant portion (" . number_format($notPerformingPercentage, 1) .
                        "%) of indicators are in the 'Not Performing' category.";
                    $insights['recommendations'][] = "Conduct a detailed review of underperforming indicators to identify systemic issues.";
                }
            }
            if ($summary['category_counts']['Met'] > 0) {
                $metPercentage = ($summary['category_counts']['Met'] / $summary['indicators_with_data']) * 100;
                if ($metPercentage > 70) {
                    $insights['observations'][] = "A high percentage (" . number_format($metPercentage, 1) .
                        "%) of indicators have met their targets.";
                    $insights['recommendations'][] = "Consider setting more ambitious targets for indicators that consistently meet current targets.";
                }
            }

            // Add insight about Over Achieved indicators if present
            if ($summary['category_counts']['Over Achieved'] > 0) {
                $overAchievedPercentage     = ($summary['category_counts']['Over Achieved'] / $summary['indicators_with_data']) * 100;
                $insights['observations'][] = number_format($overAchievedPercentage, 1) .
                    "% of indicators have exceeded their targets (Over Achieved).";
                if ($overAchievedPercentage > 30) {
                    $insights['recommendations'][] = "Review targets for over-achieved indicators to ensure they are appropriately challenging.";
                }
            }
        }

        foreach ($summary['strategic_objectives'] as $soID => $so) {
            if ($so['average_score'] !== null) {
                if ($so['average_score'] < 30) {
                    $insights['observations'][] = "Strategic Objective {$soID} ({$so['name']}) is significantly underperforming with an average score of " .
                    number_format($so['average_score'], 1) . "%.";
                    $insights['recommendations'][] = "Prioritize interventions for Strategic Objective {$soID} to improve performance.";
                } elseif ($so['average_score'] > 90) {
                    $insights['observations'][] = "Strategic Objective {$soID} ({$so['name']}) is performing exceptionally well with an average score of " .
                    number_format($so['average_score'], 1) . "%.";

                    // Add insight about perfect score for this strategic objective
                    if ($so['perfect_score']) {
                        $insights['observations'][] = "All indicators under Strategic Objective {$soID} have fully met their targets.";
                    }
                }
            }
        }

        // Add insights about target year ranges
        $targetRanges = [];
        foreach ($performanceData as $data) {
            if (isset($data['score']['target_year_range']) && ! empty($data['score']['target_year_range'])) {
                $targetRange = $data['score']['target_year_range'];
                if (! in_array($targetRange, $targetRanges)) {
                    $targetRanges[] = $targetRange;
                }
            }
        }

        if (count($targetRanges) > 0) {
            $insights['observations'][] = "Performance is being measured against " . count($targetRanges) .
            " target year range(s): " . implode(', ', $targetRanges) . ".";

            // Add insight about report year in relation to target ranges
            $yearRangeForReport = null;
            foreach ($targetRanges as $range) {
                $yearRange = $this->parseTargetYearRange($range);
                if ($timeline->Year >= $yearRange['start'] && $timeline->Year <= $yearRange['end']) {
                    $yearRangeForReport = $range;
                    break;
                }
            }

            if ($yearRangeForReport) {
                $insights['observations'][] = "The current report year ({$timeline->Year}) falls within the target range {$yearRangeForReport}.";
            } else {
                $insights['recommendations'][] = "The current report year ({$timeline->Year}) does not match any target range. Consider updating targets to include this reporting period.";
            }
        } else {
            $insights['recommendations'][] = "No valid target year ranges found. Ensure all indicators have targets set in the YYYY-YYYY format.";
        }

        foreach ($performanceData as $data) {
            if ($data['score']['category'] === 'Not Performing' && $data['score']['has_performance']) {
                // Extract the strategic objective ID more carefully
                $soID = 'Unknown';

                // First try to get from strategicObjective object
                if (isset($data['strategicObjective'])) {
                    if (isset($data['strategicObjective']->SO_ID) && ! empty($data['strategicObjective']->SO_ID)) {
                        $soID = trim($data['strategicObjective']->SO_ID);
                    } elseif (isset($data['strategicObjective']->StrategicObjectiveID) && ! empty($data['strategicObjective']->StrategicObjectiveID)) {
                        $soID = trim($data['strategicObjective']->StrategicObjectiveID);
                    }
                }

                // If not found in strategicObjective, try to get from indicator
                if ($soID === 'Unknown' && isset($data['indicator']) && isset($data['indicator']->SO_ID) && ! empty($data['indicator']->SO_ID)) {
                    $soID = trim($data['indicator']->SO_ID);
                }

                $insights['critical_indicators'][] = [
                    'indicator_number'    => $data['indicator']->Indicator_Number,
                    'indicator_name'      => $data['indicator']->Indicator_Name,
                    'score'               => $data['score']['percentage'] ?? 0,
                    'strategic_objective' => $soID,
                    'target_year_range'   => $data['score']['target_year_range'] ?? 'Not set',
                ];
            }
        }

        usort($insights['critical_indicators'], function ($a, $b) {
            return $a['score'] <=> $b['score'];
        });

        $insights['critical_indicators'] = array_slice($insights['critical_indicators'], 0, 5);

        if (count($insights['critical_indicators']) > 0) {
            $insights['recommendations'][] = "Develop targeted intervention plans for the most critical underperforming indicators.";
        }

        $insights['trends'][]          = "Historical trend analysis is not available in the current view.";
        $insights['recommendations'][] = "Implement regular performance reviews to track progress over time.";

        return $insights;
    }

    /**
     * Generate and download an Excel performance dashboard report.
     *
     * @param Request $request
     * @return **/

    /**
     * Generate and download an Excel performance dashboard report.
     *
     * @param string $clusterID
     * @param string $timelineID
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function generatePerformanceReport($clusterID, $timelineID)
    {
        try {
            Log::info('Generating Excel report - Cluster ID: ' . ($clusterID ?? 'null') . ', Timeline ID: ' . ($timelineID ?? 'null'));

            if (! $clusterID || ! $timelineID) {
                // Redirect to cluster selection if parameters are missing
                return redirect()->route('performance.cluster.selection')
                    ->withErrors(['error' => 'Cluster and timeline must be selected to generate a report.']);
            }

            // Retrieve cluster and timeline details
            $cluster = DB::table('clusters')
                ->where('ClusterID', $clusterID)
                ->first();

            $timeline = DB::table('ecsahc_timelines')
                ->where('ReportingID', $timelineID)
                ->first();

            if (! $cluster || ! $timeline) {
                return redirect()->route('performance.dashboard', [
                    'cluster_id'  => $clusterID,
                    'timeline_id' => $timelineID,
                ])->withErrors(['error' => 'Could not find cluster or timeline data for report generation.']);
            }

            // Get indicators for this cluster
            $indicators = DB::table('performance_indicators')
                ->whereRaw('JSON_CONTAINS(Responsible_Cluster, ?)', [json_encode($clusterID)])
                ->get();

            if ($indicators->isEmpty()) {
                return redirect()->route('performance.dashboard', [
                    'cluster_id'  => $clusterID,
                    'timeline_id' => $timelineID,
                ])->withErrors(['error' => 'No indicators found for this cluster.']);
            }

            // Process performance data
            $performanceData    = $this->processIndicatorPerformance($indicators, $clusterID, $timelineID, $timeline->Year);
            $performanceSummary = $this->calculatePerformanceSummary($performanceData);
            $insights           = $this->generateAIInsights($performanceData, $cluster, $timeline);

            // Create a new Spreadsheet
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator('ECSA-HC Performance Dashboard')
                ->setLastModifiedBy('ECSA-HC System')
                ->setTitle($cluster->Cluster_Name . ' Performance Report - ' . $timeline->ReportName)
                ->setSubject('Performance Dashboard')
                ->setDescription('Performance dashboard for ' . $cluster->Cluster_Name . ' - ' . $timeline->ReportName)
                ->setKeywords('performance dashboard ecsa-hc')
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

            // Generate the dashboard
            $this->generateDashboardSheet($sheet, $cluster, $timeline, $performanceSummary, $performanceData);

            // Generate the detailed metrics sheet
            $this->generateDetailedMetricsSheet($detailSheet, $performanceData, $cluster, $timeline);

            // Generate the insights sheet
            $this->generateInsightsSheet($insightsSheet, $insights, $cluster, $timeline);

            // Generate the strategic objectives sheet
            $this->generateStrategicObjectivesSheet($soSheet, $performanceSummary, $cluster, $timeline);

            // Create charts on the dashboard sheet
            $this->addPerformanceCharts($spreadsheet, $sheet, $performanceSummary, $performanceData);

            // Set the first sheet as active
            $spreadsheet->setActiveSheetIndex(0);

            // Create the Excel file
            $writer = new Xlsx($spreadsheet);

            // Generate a unique filename
            $fileName = $cluster->Cluster_Name . '_Performance_Report_' . date('Y-m-d_H-i-s') . '.xlsx';
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
            return redirect()->route('performance.dashboard', [
                'cluster_id'  => $clusterID,
                'timeline_id' => $timelineID,
            ])->withErrors(['error' => 'An error occurred while generating the Excel report: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate the main dashboard sheet.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param object $cluster
     * @param object $timeline
     * @param array $performanceSummary
     * @param array $performanceData
     * @return void
     */
    private function generateDashboardSheet($sheet, $cluster, $timeline, $performanceSummary, $performanceData)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('C')->setWidth(25);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(25);
        $sheet->getColumnDimension('F')->setWidth(15);

        // Create header with logo placeholder
        $sheet->setCellValue('A1', 'ECSA-HC PERFORMANCE DASHBOARD');
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
        $sheet->setCellValue('A2', $cluster->Cluster_Name . ' - ' . $timeline->ReportName);
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

        // Perfect score indicator
        $sheet->setCellValue('A11', 'Perfect Score:');
        $sheet->setCellValue('B11', $performanceSummary['perfect_score'] ? 'Yes' : 'No');
        $sheet->getStyle('B11')->applyFromArray([
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => $performanceSummary['perfect_score'] ? '4CAF50' : '757575'],
            ],
        ]);

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
        $sheet->setCellValue('D' . $row, 'Score');
        $sheet->setCellValue('E' . $row, 'Target Year Range');

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
                $sheet->setCellValue('D' . $row, number_format($indicator['score'], 1) . '%');
                $sheet->setCellValue('E' . $row, $indicator['target_year_range']);

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

        // Key recommendations section
        $sheet->setCellValue('A' . $row, 'KEY RECOMMENDATIONS');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 12,
            ],
            'fill'      => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E0F7FA'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        // Key recommendations data
        if (! empty($insights['recommendations'])) {
            foreach (array_slice($insights['recommendations'], 0, 5) as $recommendation) {
                $sheet->setCellValue('A' . $row, '• ' . $recommendation);
                $sheet->mergeCells('A' . $row . ':F' . $row);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No recommendations available.');
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
     * Generate the detailed metrics sheet.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $performanceData
     * @param object $cluster
     * @param object $timeline
     * @return void
     */
    private function generateDetailedMetricsSheet($sheet, $performanceData, $cluster, $timeline)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(30);

        // Create header
        $sheet->setCellValue('A1', 'DETAILED PERFORMANCE METRICS');
        $sheet->mergeCells('A1:I1');

        // Style the header
        $sheet->getStyle('A1:I1')->applyFromArray([
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
        $sheet->setCellValue('A2', $cluster->Cluster_Name . ' - ' . $timeline->ReportName);
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2:I2')->applyFromArray([
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
        $sheet->setCellValue('D4', 'Target Value');
        $sheet->setCellValue('E4', 'Target Year');
        $sheet->setCellValue('F4', 'Actual Value');
        $sheet->setCellValue('G4', 'Score (%)');
        $sheet->setCellValue('H4', 'Status');
        $sheet->setCellValue('I4', 'Comments');

        // Style the headers
        $sheet->getStyle('A4:I4')->applyFromArray([
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
            $indicator          = $data['indicator'];
            $score              = $data['score'];
            $target             = $data['target'];
            $performance        = $data['performance'];
            $strategicObjective = $data['strategicObjective'];

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
            $sheet->setCellValue('B' . $row, $indicator->Indicator_Name);
            $sheet->setCellValue('C' . $row, $soID);

            // Target information
            if ($score['has_target']) {
                $sheet->setCellValue('D' . $row, $score['target_value']);
                $sheet->setCellValue('E' . $row, $score['target_year_range'] ?? 'N/A');
            } else {
                $sheet->setCellValue('D' . $row, 'Not Set');
                $sheet->setCellValue('E' . $row, 'Not Set');
            }

            // Performance information
            if ($score['has_performance']) {
                $sheet->setCellValue('F' . $row, $score['raw_value']);
                $sheet->setCellValue('G' . $row, $score['percentage'] !== null ? number_format($score['percentage'], 1) . '%' : 'N/A');
                $sheet->setCellValue('H' . $row, $score['category']);
                $sheet->setCellValue('I' . $row, $score['comment'] ?? '');

                // Style the score cell based on category
                $color = $this->getCategoryColor($score['category']);
                $sheet->getStyle('G' . $row . ':H' . $row)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => $color],
                        'bold'  => true,
                    ],
                ]);
            } else {
                $sheet->setCellValue('F' . $row, 'No Data');
                $sheet->setCellValue('G' . $row, 'N/A');
                $sheet->setCellValue('H' . $row, 'Not Available');
                $sheet->setCellValue('I' . $row, $score['error'] ?? '');
            }

            $row++;
        }

        // Apply borders to the entire table
        $lastRow = $row - 1;
        $sheet->getStyle('A4:I' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Auto-filter
        $sheet->setAutoFilter('A4:I' . $lastRow);

        // Freeze panes
        $sheet->freezePane('A5');
    }

    /**
     * Generate the insights sheet.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $insights
     * @param object $cluster
     * @param object $timeline
     * @return void
     */
    private function generateInsightsSheet($sheet, $insights, $cluster, $timeline)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(75);

        // Create header
        $sheet->setCellValue('A1', 'INSIGHTS & RECOMMENDATIONS');
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
        $sheet->setCellValue('A2', $cluster->Cluster_Name . ' - ' . $timeline->ReportName);
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

        // Observations section
        $row = 4;
        $sheet->setCellValue('A' . $row, 'KEY OBSERVATIONS');
        $sheet->mergeCells('A' . $row . ':B' . $row);
        $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
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

        if (! empty($insights['observations'])) {
            foreach ($insights['observations'] as $index => $observation) {
                $sheet->setCellValue('A' . $row, 'Observation ' . ($index + 1));
                $sheet->setCellValue('B' . $row, $observation);
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No observations available.');
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $row++;
        }

        $row++; // Add space

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
                'startColor' => ['rgb' => 'E0F7FA'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $row++;

        if (! empty($insights['recommendations'])) {
            foreach ($insights['recommendations'] as $index => $recommendation) {
                $sheet->setCellValue('A' . $row, 'Recommendation ' . ($index + 1));
                $sheet->setCellValue('B' . $row, $recommendation);
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No recommendations available.');
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $row++;
        }

        $row++; // Add space

        // Trends section
        $sheet->setCellValue('A' . $row, 'TRENDS');
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

        if (! empty($insights['trends'])) {
            foreach ($insights['trends'] as $index => $trend) {
                $sheet->setCellValue('A' . $row, 'Trend ' . ($index + 1));
                $sheet->setCellValue('B' . $row, $trend);
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No trend data available.');
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $row++;
        }

        $row++; // Add space

        // Critical indicators section
        $sheet->setCellValue('A' . $row, 'CRITICAL INDICATORS REQUIRING ATTENTION');
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

        if (! empty($insights['critical_indicators'])) {
            // Headers
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

            foreach ($insights['critical_indicators'] as $indicator) {
                $sheet->setCellValue('A' . $row, $indicator['indicator_number']);
                $details = "Name: " . $indicator['indicator_name'] . "\n";
                $details .= "Strategic Objective: " . $indicator['strategic_objective'] . "\n";
                $details .= "Score: " . number_format($indicator['score'], 1) . "%\n";
                $details .= "Target Year Range: " . $indicator['target_year_range'];

                $sheet->setCellValue('B' . $row, $details);
                $sheet->getStyle('B' . $row)->getAlignment()->setWrapText(true);
                $sheet->getRowDimension($row)->setRowHeight(80);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No critical indicators identified.');
            $sheet->mergeCells('A' . $row . ':B' . $row);
            $sheet->getStyle('A' . $row)->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
            $row++;
        }

        // Apply borders to the entire sheet
        $lastRow = $row - 1;
        $sheet->getStyle('A4:B' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);
    }

    /**
     * Generate the strategic objectives sheet.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param array $performanceSummary
     * @param object $cluster
     * @param object $timeline
     * @return void
     */
    private function generateStrategicObjectivesSheet($sheet, $performanceSummary, $cluster, $timeline)
    {
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(15);

        // Create header
        $sheet->setCellValue('A1', 'STRATEGIC OBJECTIVES PERFORMANCE');
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
        $sheet->setCellValue('A2', $cluster->Cluster_Name . ' - ' . $timeline->ReportName);
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
        $sheet->setCellValue('A4', 'SO ID');
        $sheet->setCellValue('B4', 'Strategic Objective Name');
        $sheet->setCellValue('C4', 'Total Indicators');
        $sheet->setCellValue('D4', 'Scorable Indicators');
        $sheet->setCellValue('E4', 'Indicators at 100%');
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
        foreach ($performanceSummary['strategic_objectives'] as $soID => $so) {
            $sheet->setCellValue('A' . $row, $soID);
            $sheet->setCellValue('B' . $row, $so['name']);
            $sheet->setCellValue('C' . $row, $so['indicators']);
            $sheet->setCellValue('D' . $row, $so['scorable_indicators']);
            $sheet->setCellValue('E' . $row, $so['indicators_at_100']);

            if ($so['average_score'] !== null) {
                $sheet->setCellValue('F' . $row, number_format($so['average_score'], 1) . '%');

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

                $sheet->setCellValue('G' . $row, $status);

                // Style based on status
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

        // Apply borders to the entire table
        $lastRow = $row - 1;
        $sheet->getStyle('A4:G' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Auto-filter
        $sheet->setAutoFilter('A4:G' . $lastRow);

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
    private function addPerformanceCharts($spreadsheet, $sheet, $performanceSummary, $performanceData)
    {
        // Create a new worksheet for chart data
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('ChartData');

        // Prepare category distribution data
        $dataSheet->setCellValue('A1', 'Category');
        $dataSheet->setCellValue('B1', 'Count');

        $row        = 2;
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
            $count = $performanceSummary['category_counts'][$category] ?? 0;
            if ($count > 0) {
                $dataSheet->setCellValue('A' . $row, $category);
                $dataSheet->setCellValue('B' . $row, $count);
                $row++;
            }
        }

        // Only create chart if we have data
        if ($row > 2) {
            // Create category distribution pie chart
            $pieChart = new Chart(
                'pie1',
                new Title('Performance by Category'),
                new Legend(Legend::POSITION_RIGHT, null, false),
                new PlotArea(null, [
                    new DataSeries(
                        DataSeries::TYPE_PIECHART,
                        null,
                        range(0, $row - 3), // Adjust range to match data points
                        [new DataSeriesValues('String', 'ChartData!$A$2:$A$' . ($row - 1), null, $row - 2)],
                        [new DataSeriesValues('Number', 'ChartData!$B$2:$B$' . ($row - 1), null, $row - 2)],
                        []// Fix: Provide an empty array instead of null for plotValues
                    )
                ])
            );

            // Set chart position (column, row, width, height)
            $pieChart->setTopLeftPosition('G5');
            $pieChart->setBottomRightPosition('M15');

            // Add the chart to the dashboard sheet
            $sheet->addChart($pieChart);
        }

        // Prepare strategic objectives data
        $dataSheet->setCellValue('D1', 'Strategic Objective');
        $dataSheet->setCellValue('E1', 'Average Score');

        $row     = 2;
        $soCount = 0;

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
        foreach ($sortedSOs as $soID => $so) {
            if ($soCount >= 5) {
                break;
            }

            if ($so['average_score'] !== null) {
                $dataSheet->setCellValue('D' . $row, $soID);
                $dataSheet->setCellValue('E' . $row, $so['average_score']);
                $row++;
                $soCount++;
            }
        }

        if ($soCount > 0) {
            // Create strategic objectives bar chart
            $barChart = new Chart(
                'bar1',
                new Title('Top Strategic Objectives Performance'),
                new Legend(Legend::POSITION_TOP, null, false),
                new PlotArea(null, [
                    new DataSeries(
                        DataSeries::TYPE_BARCHART,
                        DataSeries::GROUPING_STANDARD,
                        range(0, $soCount - 1),
                        [new DataSeriesValues('String', 'ChartData!$D$2:$D$' . ($row - 1), null, $soCount)],
                        [new DataSeriesValues('Number', 'ChartData!$E$2:$E$' . ($row - 1), null, $soCount)],
                        []// Fix: Provide an empty array instead of null for plotValues
                    )
                ])
            );

            // Set chart position (column, row, width, height)
            $barChart->setTopLeftPosition('G17');
            $barChart->setBottomRightPosition('M27');

            // Add the chart to the dashboard sheet
            $sheet->addChart($barChart);
        }

        // Hide the data sheet
        $dataSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
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
                return '8E24AA'; // Purple
            case 'Met':
                return '2E7D32'; // Dark green
            case 'On Track':
                return '4CAF50'; // Light green
            case 'In Progress':
                return 'FFC107'; // Yellow
            case 'Not Performing':
                return 'F44336'; // Red
            case 'Qualitative':
                return '2196F3'; // Blue
            case 'Not Available':
            default:
                return '757575'; // Gray
        }
    }

    /**
     * Get the color for a performance category by name.
     *
     * @param string $colorName
     * @return string
     */
    private function getCategoryColorByName($colorName)
    {
        switch ($colorName) {
            case 'purple':
                return '8E24AA'; // Purple
            case 'dark-green':
                return '2E7D32'; // Dark green
            case 'light-green':
                return '4CAF50'; // Light green

            case 'yellow':
                return 'FFC107'; // Yellow
            case 'red':
                return 'F44336'; // Red
            case 'blue':
                return '2196F3'; // Blue
            case 'gray':
            default:
                return '757575'; // Gray
        }
    }
}