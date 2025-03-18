<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// For spreadsheet creation:
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
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class V2_ALL_PerformanceDashboardController extends Controller
{
    /**
     * Safely get a value from an array with proper null checking.
     */
    private function safeArrayGet($array, $key, $default = null)
    {
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }
        return $default;
    }

    /**
     * Display the timeline selection view.
     */
    public function showTimelineSelection()
    {
        try {
            Log::info('Timeline selection screen - All Clusters.');

            $timelines = DB::table('ecsahc_timelines')
                ->select('id', 'ReportName', 'Type', 'ReportingID', 'Year', 'ClosingDate', 'status')
                ->orderBy('Year', 'desc')
                ->orderBy('ClosingDate', 'desc')
                ->get();

            Log::info('Found ' . $timelines->count() . ' timelines.');

            return view('scrn', [
                'Page'        => 'EcsaPerfV2.all-select-timeline',
                'Title'       => 'Select Timeline',
                'Timelines'   => $timelines,
                'Error'       => $timelines->isEmpty() ? 'No reporting timelines found in the system.' : null,
                'AllClusters' => true,
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
     * Process the user’s timeline selection and redirect to the performance dashboard.
     */
    public function processTimelineSelection(Request $request)
    {
        try {
            $validated = $request->validate([
                'timeline_id' => 'required|string|exists:ecsahc_timelines,ReportingID',
            ]);

            $timelineId = $validated['timeline_id'];
            Log::info('Timeline selected: ' . $timelineId . ' for All Clusters');

            return redirect()->route('V2_ALL_performance.dashboard', [
                'timeline_id' => $timelineId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing timeline selection: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'An error occurred while processing your selection.']);
        }
    }

    /**
     * Main performance dashboard for all clusters in the given timeline.
     * Aggregates multi-cluster, multi-target logic into one consolidated approach.
     */
    public function showPerformanceDashboard(Request $request)
    {
        try {
            // 1) Check timeline
            $timelineID = $request->route('timeline_id') ?? $request->input('timeline_id');
            if (! $timelineID) {
                return redirect()
                    ->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'Please select a timeline.']);
            }

            // 2) Fetch the timeline
            $timeline = DB::table('ecsahc_timelines')
                ->where('ReportingID', $timelineID)
                ->first();
            if (! $timeline) {
                return redirect()
                    ->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'Selected timeline not found.']);
            }

            // 3) Get all clusters (except "All clusters/projects")
            $clusters = DB::table('clusters')
                ->where('ClusterID', '!=', 'All clusters/projects')
                ->get();
            if ($clusters->isEmpty()) {
                return view('scrn', [
                    'Page'  => 'EcsaPerfV2.all-indicator-performance',
                    'Title' => 'Performance Dashboard - All Clusters',
                    'Error' => 'No clusters found in the system.',
                ]);
            }

            // Map cluster IDs => cluster names
            $clusterNames = [];
            foreach ($clusters as $c) {
                $clusterNames[$c->ClusterID] = $c->Cluster_Name;
            }

            // 4) Collect performance data for all clusters
            //    (REFINED for multi-target logic)
            $allPerformanceData = $this->collectAllClusterPerformance($clusters, $timelineID, $timeline->Year);
            if (empty($allPerformanceData)) {
                return view('scrn', [
                    'Page'         => 'EcsaPerfV2.all-indicator-performance',
                    'Title'        => 'Performance Dashboard - All Clusters',
                    'Timeline'     => $timeline,
                    'Error'        => 'No performance data found for any cluster.',
                    'ClusterNames' => $clusterNames,
                    'TimelineID'   => $timelineID,
                    'AllClusters'  => true,
                ]);
            }

            // 5) Summaries
            $performanceSummary = $this->calculateCombinedPerformanceSummary($allPerformanceData);

            // 6) Insights
            $insights = $this->generateAIInsights($allPerformanceData, $clusterNames, $timeline);

            // 7) Return to the view
            return view('scrn', [
                'Page'               => 'EcsaPerfV2.all-indicator-performance',
                'Title'              => 'Performance Dashboard - All Clusters',
                'Timeline'           => $timeline,
                'PerformanceData'    => $allPerformanceData,
                'PerformanceSummary' => $performanceSummary,
                'Insights'           => $insights,
                'Error'              => null,
                'ClusterNames'       => $clusterNames,
                'TimelineID'         => $timelineID,
                'AllClusters'        => true,
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
     * Collect performance data for each cluster, each of which may have unique targets for the same indicator.
     * -- REFACTORED for multi-target scenario.
     */
    private function collectAllClusterPerformance($clusters, $timelineID, $year)
    {
        $allData = [];
        foreach ($clusters as $cluster) {
            $clusterID = $cluster->ClusterID;

            // 1) fetch all indicators that mention this cluster in their "Responsible_Cluster" JSON
            $indicators = DB::table('performance_indicators')
                ->whereRaw('JSON_CONTAINS(Responsible_Cluster, ?)', [json_encode($clusterID)])
                ->get();

            // 2) For each indicator, get all possible targets that match this timeline's year
            foreach ($indicators as $indicator) {
                // cluster-specific targets
                $targets = $this->findTargetsForYear($clusterID, $indicator->id, $year);

                // performance (single record per cluster/timeline/indicator)
                $performance = DB::table('cluster_performance_mappings')
                    ->where('ClusterID', $clusterID)
                    ->where('ReportingID', $timelineID)
                    ->where('IndicatorID', $indicator->id)
                    ->first();

                // If no target found, we still produce one row with "no target set"
                if (empty($targets)) {
                    $score = $this->calculatePerformanceScore($indicator, null, $performance);

                    $allData[] = [
                        'indicator'          => $indicator,
                        'strategicObjective' => $this->fetchStrategicObjective($indicator),
                        'target'             => null,
                        'performance'        => $performance,
                        'score'              => $score,
                        'cluster'            => $cluster,
                    ];
                } else {
                    // For each matching target, produce a separate row
                    foreach ($targets as $target) {
                        $score = $this->calculatePerformanceScore($indicator, $target, $performance);

                        $allData[] = [
                            'indicator'          => $indicator,
                            'strategicObjective' => $this->fetchStrategicObjective($indicator),
                            'target'             => $target,
                            'performance'        => $performance,
                            'score'              => $score,
                            'cluster'            => $cluster,
                        ];
                    }
                }
            }
        }
        return $allData;
    }

    /**
     * Return *all* cluster-indicator target records whose Target_Year includes the given $year.
     * If none matches, the calling code still produces a row for "no target set."
     */
    private function findTargetsForYear($clusterID, $indicatorID, $year)
    {
        // get all target rows for cluster+indicator
        $targets = DB::table('cluster_indicator_targets')
            ->where('ClusterID', $clusterID)
            ->where('IndicatorID', $indicatorID)
            ->get();

        if ($targets->isEmpty()) {
            return [];
        }

        $matching = [];
        foreach ($targets as $t) {
            if ($this->isYearInTargetRange($year, $t->Target_Year)) {
                $matching[] = $t;
            }
        }
        return $matching;
    }

    /**
     * Parse "YYYY-YYYY" => ['start'=>YYYY, 'end'=>YYYY].
     */
    private function parseTargetYearRange($targetYear)
    {
        if (! preg_match('/^\d{4}-\d{4}$/', $targetYear)) {
            return ['start' => null, 'end' => null];
        }
        $split = explode('-', $targetYear);
        return [
            'start' => (int) $split[0],
            'end'   => (int) $split[1],
        ];
    }

    /**
     * Check if $reportYear falls within the $targetYearRange (YYYY-YYYY).
     */
    private function isYearInTargetRange($reportYear, $targetYearRange)
    {
        $range = $this->parseTargetYearRange($targetYearRange);
        if ($range['start'] === null || $range['end'] === null) {
            return false;
        }
        return ($reportYear >= $range['start'] && $reportYear <= $range['end']);
    }

    /**
     * Helper: fetch the strategic objective record for the given indicator's SO_ID,
     * or return a fallback object if not found.
     */
    private function fetchStrategicObjective($indicator)
    {
        if (! empty($indicator->SO_ID)) {
            $strategicObjective = DB::table('strategic_objectives')
                ->where('StrategicObjectiveID', trim($indicator->SO_ID))
                ->first();

            if (! $strategicObjective) {
                return (object) [
                    'SO_ID'       => trim($indicator->SO_ID),
                    'SO_Name'     => trim($indicator->SO_ID),
                    'Description' => 'Strategic objective details not found',
                ];
            }
            return $strategicObjective;
        }
        // fallback
        return (object) [
            'SO_ID'       => 'Unknown',
            'SO_Name'     => 'Unknown Strategic Objective',
            'Description' => '',
        ];
    }

    /**
     * For each cluster+indicator, compute the “score” array. This also sets category, color, error, etc.
     * If $target is null, "no target" is shown in the view.
     */
    private function calculatePerformanceScore($indicator, $target, $performance)
    {
        $score = [
            'has_target'        => false,
            'has_performance'   => false,
            'raw_value'         => null,
            'percentage'        => null,
            'category'          => 'Not Available',
            'color'             => 'gray',
            'error'             => null,
            'target_value'      => null,
            'target_year_range' => null,
            'comment'           => null,
        ];

        // If no target, just mark an error and return
        if (! $target) {
            $score['error'] = 'No target set for this indicator.';
            return $score;
        }
        $score['has_target']        = true;
        $score['target_value']      = $target->Target_Value;
        $score['target_year_range'] = $target->Target_Year;

        // If no performance => can't do actual vs. target
        if (! $performance) {
            $score['error'] = 'No performance data reported for this indicator.';
            return $score;
        }
        $score['has_performance'] = true;
        $score['raw_value']       = $performance->Response;
        $score['comment']         = $performance->ReportingComment ?? null;

        // Now compute category
        switch ($indicator->ResponseType) {
            case 'Number':
                $tVal = floatval($target->Target_Value);
                $aVal = floatval($performance->Response);
                if ($tVal == 0) {
                    if ($aVal == 0) {
                        $score['percentage'] = 100;
                        $score['category']   = 'Met';
                        $score['color']      = 'dark-green';
                    } else {
                        $score['percentage'] = 0;
                        $score['category']   = 'Not Performing';
                        $score['color']      = 'red';
                    }
                } else {
                    $pct                 = ($aVal / $tVal) * 100;
                    $score['percentage'] = $pct;
                    if ($pct > 100) {
                        $score['category'] = 'Over Achieved';
                        $score['color']    = 'purple';
                    } elseif ($pct >= 90) {
                        $score['category'] = 'Met';
                        $score['color']    = 'dark-green';
                    } elseif ($pct >= 50) {
                        $score['category'] = 'On Track';
                        $score['color']    = 'light-green';
                    } elseif ($pct >= 10) {
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
                $val = strtolower($performance->Response);
                if (in_array($val, ['yes', 'true', '1'], true)) {
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
     * Summarize all cluster+indicator data: how many unique indicators, how many have targets/data,
     * plus category tallies, cluster-level stats, strategic-objective-level stats, overall score, etc.
     */
    private function calculateCombinedPerformanceSummary(array $allPerformanceData)
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
            'score_note'                => null,
            'indicators_at_100'         => 0,
            'data_completeness'         => 0,
            'data_completeness_warning' => false,
            'strategic_objectives'      => [],
            'clusters_data'             => [],
        ];

        // track unique indicator IDs
        $uniqueIndicators = [];
        foreach ($allPerformanceData as $row) {
            if (isset($row['indicator']->id)) {
                $uniqueIndicators[$row['indicator']->id] = true;
            }
        }
        $summary['total_indicators'] = count($uniqueIndicators);

        // track indicator-level target/data coverage
        $indicatorHasTarget = [];
        $indicatorHasData   = [];

        // cluster stats
        $clustersMap = [];
        // strategic objectives
        $strategicObjMap = [];
        // scorable lines
        static $alreadyScored = [];
        // track so we don't double count cluster->indicator or so->indicator
        static $countedClusterInd       = [];
        static $countedClusterIndTarget = [];
        static $countedClusterIndData   = [];
        static $countedSOIndicator      = [];

        foreach ($allPerformanceData as $item) {
            if (! isset($item['indicator']) || ! isset($item['cluster'])) {
                continue;
            }
            $indicatorID = $item['indicator']->id;
            $clusterID   = $item['cluster']->ClusterID;
            $score       = $item['score'];

            // track if this indicator has a target
            if ($score['has_target']) {
                $indicatorHasTarget[$indicatorID] = true;
            }
            // track if this indicator has data
            if ($score['has_performance']) {
                $indicatorHasData[$indicatorID] = true;
            }

            // cluster-level aggregator
            if (! isset($clustersMap[$clusterID])) {
                $clustersMap[$clusterID] = [
                    'name'                    => $item['cluster']->Cluster_Name ?? 'Unknown Cluster',
                    'indicators'              => 0,
                    'indicators_with_targets' => 0,
                    'indicators_with_data'    => 0,
                    'score_sum'               => 0,
                    'scorable_indicators'     => 0,
                    'average_score'           => null,
                    'data_completeness'       => 0,
                ];
            }

            // avoid double counting the same cluster+indicator
            $ciKey = $clusterID . '_' . $indicatorID;
            if (! isset($countedClusterInd[$ciKey])) {
                $clustersMap[$clusterID]['indicators']++;
                $countedClusterInd[$ciKey] = true;
            }
            if ($score['has_target']) {
                $ctKey = 't_' . $ciKey;
                if (! isset($countedClusterIndTarget[$ctKey])) {
                    $clustersMap[$clusterID]['indicators_with_targets']++;
                    $countedClusterIndTarget[$ctKey] = true;
                }
            }
            if ($score['has_performance']) {
                $cdKey = 'd_' . $ciKey;
                if (! isset($countedClusterIndData[$cdKey])) {
                    $clustersMap[$clusterID]['indicators_with_data']++;
                    $countedClusterIndData[$cdKey] = true;
                }
                // category
                $cat = $score['category'] ?? 'Not Available';
                if (isset($summary['category_counts'][$cat])) {
                    $summary['category_counts'][$cat]++;
                }
                if (is_numeric($score['percentage']) && $score['percentage'] >= 0) {
                    $capPct = min(100, $score['percentage']);
                    $clustersMap[$clusterID]['score_sum'] += $capPct;
                    $clustersMap[$clusterID]['scorable_indicators']++;
                    if ($score['percentage'] >= 100) {
                        $summary['indicators_at_100']++;
                    }
                    // mark scorable so we can do overall
                    if (! isset($alreadyScored[$ciKey])) {
                        $alreadyScored[$ciKey] = true;
                    }
                }
            }

            // strategic objective aggregator
            $soID = 'Unknown';
            if (! empty($item['strategicObjective'])) {
                if (! empty($item['strategicObjective']->SO_ID)) {
                    $soID = trim($item['strategicObjective']->SO_ID);
                } elseif (! empty($item['strategicObjective']->StrategicObjectiveID)) {
                    $soID = trim($item['strategicObjective']->StrategicObjectiveID);
                }
            } elseif (! empty($item['indicator']->SO_ID)) {
                $soID = trim($item['indicator']->SO_ID);
            }
            if (! isset($strategicObjMap[$soID])) {
                $soName = $soID;
                $soDesc = '';
                if (isset($item['strategicObjective']->SO_Name)) {
                    $soName = $item['strategicObjective']->SO_Name;
                }
                if (isset($item['strategicObjective']->Description)) {
                    $soDesc = $item['strategicObjective']->Description;
                }
                $strategicObjMap[$soID] = [
                    'name'                => $soName,
                    'description'         => $soDesc,
                    'indicators'          => 0,
                    'scorable_indicators' => 0,
                    'score_sum'           => 0,
                    'average_score'       => null,
                    'indicators_at_100'   => 0,
                ];
            }

            $soIndKey = $soID . '_' . $indicatorID;
            if (! isset($countedSOIndicator[$soIndKey])) {
                $strategicObjMap[$soID]['indicators']++;
                $countedSOIndicator[$soIndKey] = true;
            }
            if (is_numeric($score['percentage']) && $score['percentage'] >= 0) {
                $capSOPct = min(100, $score['percentage']);
                $strategicObjMap[$soID]['score_sum'] += $capSOPct;
                $strategicObjMap[$soID]['scorable_indicators']++;
                if ($score['percentage'] >= 100) {
                    $strategicObjMap[$soID]['indicators_at_100']++;
                }
            }
        }

        // fill summary
        $summary['indicators_with_targets'] = count($indicatorHasTarget);
        $summary['indicators_with_data']    = count($indicatorHasData);

        // data completeness
        if ($summary['total_indicators'] > 0) {
            $dc                           = ($summary['indicators_with_data'] / $summary['total_indicators']) * 100;
            $summary['data_completeness'] = $dc;
            if ($dc < 50) {
                $summary['data_completeness_warning'] = true;
            }
        }

        // finalize cluster-level
        foreach ($clustersMap as $cid => &$c) {
            if ($c['scorable_indicators'] > 0) {
                $c['average_score'] = $c['score_sum'] / $c['scorable_indicators'];
            }
            if ($c['indicators'] > 0) {
                $c['data_completeness'] = ($c['indicators_with_data'] / $c['indicators']) * 100;
            }
        }
        $summary['clusters_data'] = $clustersMap;

        // finalize strategic objective-level
        foreach ($strategicObjMap as $soID => &$sobj) {
            if ($sobj['scorable_indicators'] > 0) {
                $sobj['average_score'] = $sobj['score_sum'] / $sobj['scorable_indicators'];
            }
        }
        $summary['strategic_objectives'] = $strategicObjMap;

        // overall score
        $scoreSum   = 0;
        $scoreCount = 0;
        foreach ($allPerformanceData as $row) {
            $clusterID   = $row['cluster']->ClusterID;
            $indicatorID = $row['indicator']->id;
            $pct         = $row['score']['percentage'] ?? null;
            if (is_numeric($pct) && $pct >= 0) {
                $key = $clusterID . '_' . $indicatorID;
                if (isset($alreadyScored[$key])) {
                    $scoreSum += min(100, $pct);
                    $scoreCount++;
                    unset($alreadyScored[$key]);
                }
            }
        }
        if ($scoreCount > 0) {
            $rawScore = $scoreSum / $scoreCount;
            if ($summary['data_completeness_warning']) {
                $weight                   = $summary['data_completeness'] / 100;
                $summary['overall_score'] = $rawScore * $weight;
                $summary['score_note']    = "Score adjusted for data completeness ("
                . number_format($summary['data_completeness'], 1) . "% of indicators have data)";
            } else {
                $summary['overall_score'] = $rawScore;
                $summary['score_note']    = "Based on "
                . number_format($summary['data_completeness'], 1) . "% of indicators with data";
            }

            if ($summary['overall_score'] >= 90) {
                $summary['overall_category'] = 'Met';
            } elseif ($summary['overall_score'] >= 50) {
                $summary['overall_category'] = 'On Track';
            } elseif ($summary['overall_score'] >= 10) {
                $summary['overall_category'] = 'In Progress';
            } else {
                $summary['overall_category'] = 'Not Performing';
            }
            if ($summary['data_completeness'] < 20) {
                $summary['overall_category'] = 'Insufficient Data';
                $summary['score_note']       = "Warning: Only "
                . number_format($summary['data_completeness'], 1)
                    . "% of indicators have data. Score may not be representative.";
            }
        }

        return $summary;
    }

    /**
     * generateAIInsights():
     *   Our advanced “observations” + “recommendations” based on the same summary approach, ensuring consistency.
     */
    private function generateAIInsights(array $allPerformanceData, array $clusterNames, $timeline)
    {
        $insights = [
            'observations'        => [],
            'recommendations'     => [],
            'trends'              => [],
            'critical_indicators' => [],
            'cluster_comparisons' => [],
            'data_quality'        => [],
        ];

        $summary = $this->calculateCombinedPerformanceSummary($allPerformanceData);

        // data completeness
        if ($summary['total_indicators'] > 0) {
            $dc                         = ($summary['indicators_with_data'] / $summary['total_indicators']) * 100;
            $insights['data_quality'][] = "Data completeness stands at " . number_format($dc, 1) . "% for this timeline.";
            if ($dc < 20) {
                $insights['data_quality'][]    = "CRITICAL: Under 20% completeness => results highly unreliable.";
                $insights['recommendations'][] = "Focus on improving reporting coverage above 20%.";
            } elseif ($dc < 50) {
                $insights['data_quality'][]    = "WARNING: Data completeness under 50%. Interpret results carefully.";
                $insights['recommendations'][] = "Coordinate with clusters to improve data coverage beyond 50%.";
            }
        }

        // overall performance
        if ($summary['overall_score'] > 0) {
            $insights['observations'][] = "Overall performance is "
            . number_format($summary['overall_score'], 1) . "% ("
                . $summary['overall_category'] . ").";
        } else {
            $insights['observations'][] = "Unable to compute overall performance due to insufficient data coverage.";
        }

        // category distribution
        $catCounts = $summary['category_counts'];
        $countData = max($summary['indicators_with_data'], 1);
        if (! empty($catCounts)) {
            $notPerf = $this->safeArrayGet($catCounts, 'Not Performing', 0);
            $npPct   = ($notPerf / $countData) * 100;
            if ($npPct > 30) {
                $insights['observations'][] = number_format($npPct, 1)
                    . "% of reported indicators fall under 'Not Performing'.";
                $insights['recommendations'][] = "Investigate root causes for these underperforming indicators and develop targeted interventions.";
            }
        }

        // cluster comparisons
        if (! empty($summary['clusters_data']) && count($summary['clusters_data']) > 1) {
            $bestCluster  = null;
            $worstCluster = null;
            $bestScore    = -999999;
            $worstScore   = 999999;

            foreach ($summary['clusters_data'] as $cid => $cd) {
                if (isset($cd['average_score']) && is_numeric($cd['average_score'])) {
                    $sc = floatval($cd['average_score']);
                    if ($sc > $bestScore) {
                        $bestScore   = $sc;
                        $bestCluster = $cd;
                    }
                    if ($sc < $worstScore) {
                        $worstScore   = $sc;
                        $worstCluster = $cd;
                    }
                }
            }
            if ($bestCluster && $worstCluster) {
                $insights['cluster_comparisons'][] = "Highest performing cluster: "
                . $bestCluster['name'] . " at " . number_format($bestScore, 1) . "%";
                $insights['cluster_comparisons'][] = "Lowest performing cluster: "
                . $worstCluster['name'] . " at " . number_format($worstScore, 1) . "%";
                $gap = $bestScore - $worstScore;
                if ($gap > 30) {
                    $insights['recommendations'][] = "Significant gap (>30%) => conduct knowledge-sharing from best to worst cluster.";
                }
            }
        }

        // critical indicators => "Not Performing" but do have data
        $crit = [];
        foreach ($allPerformanceData as $row) {
            $cat = $this->safeArrayGet($row['score'], 'category', 'Not Available');
            if ($cat === 'Not Performing' && ! empty($row['score']['has_performance'])) {
                $iNum  = $row['indicator']->Indicator_Number ?? 'N/A';
                $iName = $row['indicator']->Indicator_Name ?? 'Unknown';
                $pct   = $row['score']['percentage'] ?? 0;
                $soID  = 'Unknown';
                if (! empty($row['strategicObjective']->SO_ID)) {
                    $soID = trim($row['strategicObjective']->SO_ID);
                }
                $key = $iNum . '_' . $soID;
                if (! isset($crit[$key])) {
                    $crit[$key] = [
                        'indicator_number'    => $iNum,
                        'indicator_name'      => $iName,
                        'strategic_objective' => $soID,
                        'clusters'            => [],
                        'lowest_score'        => $pct,
                        'target_year_range'   => $this->safeArrayGet($row['score'], 'target_year_range', 'N/A'),
                    ];
                }
                $clName                   = $this->safeArrayGet($clusterNames, $row['cluster']->ClusterID, 'Unknown Cluster');
                $crit[$key]['clusters'][] = $clName;
                if ($pct < $crit[$key]['lowest_score']) {
                    $crit[$key]['lowest_score'] = $pct;
                }
            }
        }
        usort($crit, function ($a, $b) {
            return $a['lowest_score'] <=> $b['lowest_score'];
        });
        $insights['critical_indicators'] = array_slice($crit, 0, 5);

        // placeholder for trends
        $insights['trends'][] = "Trend analysis not available at the moment. Please enable historical logging for time-series data.";

        return $insights;
    }

    /**
     * Generate an Excel-based performance report for all clusters in the chosen timeline,
     * creating multiple sheets (Dashboard, Detailed, Insights, etc.).
     */
    public function generatePerformanceReport($timelineID)
    {
        try {
            Log::info('Generating Excel report for All Clusters - Timeline ID: ' . ($timelineID ?? 'null'));

            if (! $timelineID) {
                return redirect()
                    ->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'Timeline must be selected to generate a report.']);
            }

            // 1) fetch timeline
            $timeline = DB::table('ecsahc_timelines')
                ->where('ReportingID', $timelineID)
                ->first();
            if (! $timeline) {
                return redirect()
                    ->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'Could not find timeline data for report generation.']);
            }

            // 2) get clusters
            $clusters = DB::table('clusters')
                ->where('ClusterID', '!=', 'All clusters/projects')
                ->get();
            if ($clusters->isEmpty()) {
                return redirect()
                    ->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'No clusters found in the system.']);
            }

            // map cluster names
            $clusterNames = [];
            foreach ($clusters as $c) {
                $clusterNames[$c->ClusterID] = $c->Cluster_Name;
            }

            // 3) gather data
            $allPerformanceData = $this->collectAllClusterPerformance($clusters, $timelineID, $timeline->Year);
            if (empty($allPerformanceData)) {
                return redirect()
                    ->route('V2_ALL_performance.timeline.selection')
                    ->withErrors(['error' => 'No performance data found for any cluster.']);
            }

            // 4) build summary & insights
            $performanceSummary = $this->calculateCombinedPerformanceSummary($allPerformanceData);
            $insights           = $this->generateAIInsights($allPerformanceData, $clusterNames, $timeline);

            // 5) Create spreadsheet
            $spreadsheet = new Spreadsheet();
            $spreadsheet->getProperties()
                ->setCreator('ECSA-HC Performance Dashboard')
                ->setLastModifiedBy('ECSA-HC System')
                ->setTitle('All Clusters Performance Report - ' . $timeline->ReportName)
                ->setSubject('Performance Dashboard')
                ->setDescription('Combined performance dashboard for all clusters - ' . $timeline->ReportName)
                ->setKeywords('performance dashboard ecsa-hc all clusters')
                ->setCategory('Performance Reports');

            // make multiple sheets
            $dashboardSheet = $spreadsheet->getActiveSheet();
            $dashboardSheet->setTitle('Dashboard');

            $detailSheet = $spreadsheet->createSheet();
            $detailSheet->setTitle('Detailed Metrics');

            $insightsSheet = $spreadsheet->createSheet();
            $insightsSheet->setTitle('Insights & Recommendations');

            $soSheet = $spreadsheet->createSheet();
            $soSheet->setTitle('Strategic Objectives');

            $clusterSheet = $spreadsheet->createSheet();
            $clusterSheet->setTitle('Cluster Comparison');

            // fill each sheet
            $this->generateCombinedDashboardSheet($dashboardSheet, $clusterNames, $timeline, $performanceSummary, $allPerformanceData);
            $this->generateCombinedDetailedMetricsSheet($detailSheet, $allPerformanceData, $timeline);
            $this->generateCombinedInsightsSheet($insightsSheet, $insights, $timeline);
            $this->generateCombinedStrategicObjectivesSheet($soSheet, $performanceSummary, $timeline);
            $this->generateClusterComparisonSheet($clusterSheet, $performanceSummary, $timeline);

            // add charts to the dashboard
            $this->addCombinedPerformanceCharts($spreadsheet, $dashboardSheet, $performanceSummary, $allPerformanceData);

            // finalize
            $spreadsheet->setActiveSheetIndex(0);
            $writer = new Xlsx($spreadsheet);

            $fileName = 'All_Clusters_Performance_Report_' . date('Y-m-d_H-i-s') . '.xlsx';
            $filePath = storage_path('app/public/reports/' . $fileName);

            if (! file_exists(storage_path('app/public/reports'))) {
                mkdir(storage_path('app/public/reports'), 0755, true);
            }

            $writer->save($filePath);
            Log::info('Excel report generated successfully: ' . $filePath);

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Error generating Excel report: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return redirect()
                ->route('V2_ALL_performance.dashboard', ['timeline_id' => $timelineID])
                ->withErrors(['error' => 'An error occurred while generating the Excel report: ' . $e->getMessage()]);
        }
    }

    /**
     * generateCombinedDashboardSheet():
     *   Summarize the “overview” scoreboard with a simpler table or layout.
     */
    private function generateCombinedDashboardSheet($sheet, $clusterNames, $timeline, $performanceSummary, $allPerformanceData)
    {
        // Example layout
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(30);

        $sheet->setCellValue('A1', 'ECSA-HC ALL CLUSTERS PERFORMANCE DASHBOARD');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1:D1')->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        $sheet->setCellValue('A2', 'Timeline:');
        $sheet->setCellValue('B2', $timeline->ReportName ?? 'N/A');

        $sheet->setCellValue('A3', 'Year:');
        $sheet->setCellValue('B3', $timeline->Year ?? 'N/A');

        // Overall Performance
        $sheet->setCellValue('A5', 'Overall Score');
        $sheet->setCellValue('B5', number_format($performanceSummary['overall_score'], 1) . '%');
        $sheet->setCellValue('C5', $performanceSummary['overall_category']);

        // Data completeness
        $sheet->setCellValue('A6', 'Data Completeness');
        $sheet->setCellValue('B6', number_format($performanceSummary['data_completeness'], 1) . '%');

        // total indicators
        $sheet->setCellValue('A7', 'Indicators (Total)');
        $sheet->setCellValue('B7', $performanceSummary['total_indicators']);

        // w/ targets
        $sheet->setCellValue('A8', 'Indicators w/Targets');
        $sheet->setCellValue('B8', $performanceSummary['indicators_with_targets']);
        // w/ data
        $sheet->setCellValue('A9', 'Indicators w/Data');
        $sheet->setCellValue('B9', $performanceSummary['indicators_with_data']);

        // indicators at 100%
        $sheet->setCellValue('A10', 'Indicators >= 100%');
        $sheet->setCellValue('B10', $performanceSummary['indicators_at_100']);

        // optional note
        if (! empty($performanceSummary['score_note'])) {
            $sheet->setCellValue('A12', 'Note:');
            $sheet->setCellValue('B12', $performanceSummary['score_note']);
            $sheet->mergeCells('B12:D12');
        }
    }

    /**
     * generateCombinedDetailedMetricsSheet():
     *   Create a sheet listing each row from allPerformanceData.
     */
    private function generateCombinedDetailedMetricsSheet($sheet, $allPerformanceData, $timeline)
    {
        $sheet->setCellValue('A1', 'Detailed Performance Metrics (All Clusters)');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // headers
        $sheet->setCellValue('A3', 'Cluster');
        $sheet->setCellValue('B3', 'Indicator');
        $sheet->setCellValue('C3', 'Target (Year)');
        $sheet->setCellValue('D3', 'Performance');
        $sheet->setCellValue('E3', 'Score (%)');
        $sheet->setCellValue('F3', 'Category');

        $sheet->getStyle('A3:F3')->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        $row = 4;
        foreach ($allPerformanceData as $item) {
            $clusterName = $item['cluster']->Cluster_Name ?? 'Unknown Cluster';
            $indNum      = $item['indicator']->Indicator_Number ?? 'N/A';
            $indName     = $item['indicator']->Indicator_Name ?? 'Unknown';

            $hasTarget = $item['score']['has_target'];
            $targetVal = $hasTarget ? $item['score']['target_value'] : 'No target';
            $tRange    = $hasTarget ? $item['score']['target_year_range'] : 'N/A';

            $hasPerf = $item['score']['has_performance'];
            $perfVal = $hasPerf ? $item['score']['raw_value'] : 'No data';

            $pct = $item['score']['percentage'] !== null
            ? number_format($item['score']['percentage'], 1) . '%' : 'N/A';
            $cat = $item['score']['category'] ?? 'Not Available';

            $sheet->setCellValue('A' . $row, $clusterName);
            $sheet->setCellValue('B' . $row, $indNum . ' - ' . $indName);
            $sheet->setCellValue('C' . $row, $targetVal . ' (' . $tRange . ')');
            $sheet->setCellValue('D' . $row, $perfVal);
            $sheet->setCellValue('E' . $row, $pct);
            $sheet->setCellValue('F' . $row, $cat);

            $row++;
        }

        $sheet->getStyle('A3:F' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
    }

    /**
     * generateCombinedInsightsSheet():
     *   Show dataQuality, observations, recommendations, cluster_comparisons, trends, and critical_indicators.
     */
    private function generateCombinedInsightsSheet($sheet, $insights, $timeline)
    {
        $sheet->setTitle('Insights & Recommendations');

        $sheet->setCellValue('A1', 'Insights & Recommendations');
        $sheet->mergeCells('A1:A1');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        $row = 3;
        // Data Quality
        $sheet->setCellValue('A' . $row, 'Data Quality:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        if (! empty($insights['data_quality'])) {
            foreach ($insights['data_quality'] as $dq) {
                $sheet->setCellValue('A' . $row, '- ' . $dq);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No data quality insights.');
            $row++;
        }

        $row++;
        // Observations
        $sheet->setCellValue('A' . $row, 'Observations:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        if (! empty($insights['observations'])) {
            foreach ($insights['observations'] as $obs) {
                $sheet->setCellValue('A' . $row, '- ' . $obs);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No observations found.');
            $row++;
        }

        $row++;
        // Recommendations
        $sheet->setCellValue('A' . $row, 'Recommendations:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        if (! empty($insights['recommendations'])) {
            foreach ($insights['recommendations'] as $rec) {
                $sheet->setCellValue('A' . $row, '- ' . $rec);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No recommendations found.');
            $row++;
        }

        $row++;
        // Cluster Comparisons
        $sheet->setCellValue('A' . $row, 'Cluster Comparisons:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        if (! empty($insights['cluster_comparisons'])) {
            foreach ($insights['cluster_comparisons'] as $cc) {
                $sheet->setCellValue('A' . $row, '- ' . $cc);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No cluster comparison insights found.');
            $row++;
        }

        $row++;
        // Trends
        $sheet->setCellValue('A' . $row, 'Performance Trends:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        if (! empty($insights['trends'])) {
            foreach ($insights['trends'] as $t) {
                $sheet->setCellValue('A' . $row, '- ' . $t);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No performance trends found.');
            $row++;
        }

        $row++;
        // Critical Indicators
        $sheet->setCellValue('A' . $row, 'Critical Indicators:');
        $sheet->getStyle('A' . $row)->getFont()->setBold(true);
        $row++;
        if (! empty($insights['critical_indicators'])) {
            foreach ($insights['critical_indicators'] as $ci) {
                $line = "Indicator " . $ci['indicator_number'] . " (SO: " . $ci['strategic_objective']
                . ") => Lowest score: " . number_format($ci['lowest_score'], 1) . "% across clusters "
                . implode(', ', $ci['clusters'])
                    . ". Target Range: " . $ci['target_year_range'];
                $sheet->setCellValue('A' . $row, '- ' . $line);
                $row++;
            }
        } else {
            $sheet->setCellValue('A' . $row, 'No critical indicators identified.');
            $row++;
        }
    }

    /**
     * generateCombinedStrategicObjectivesSheet():
     *   Summarize each strategic objective from $performanceSummary['strategic_objectives'].
     */
    private function generateCombinedStrategicObjectivesSheet($sheet, $performanceSummary, $timeline)
    {
        $sheet->setTitle('Strategic Objectives');
        $sheet->setCellValue('A1', 'Strategic Objectives Overview');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A3', 'SO ID');
        $sheet->setCellValue('B3', 'Name');
        $sheet->setCellValue('C3', 'Indicators');
        $sheet->setCellValue('D3', 'Average Score');
        $sheet->setCellValue('E3', 'Status');

        $sheet->getStyle('A3:E3')->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        $row = 4;
        if (! empty($performanceSummary['strategic_objectives'])) {
            foreach ($performanceSummary['strategic_objectives'] as $soID => $so) {
                $sheet->setCellValue('A' . $row, $soID);
                $sheet->setCellValue('B' . $row, $so['name']);
                $sheet->setCellValue('C' . $row, $so['indicators']);
                if ($so['average_score'] !== null) {
                    $val = $so['average_score'];
                    $sheet->setCellValue('D' . $row, number_format($val, 1) . '%');

                    if ($val > 100) {
                        $sheet->setCellValue('E' . $row, 'Over Achieved');
                    } elseif ($val >= 90) {
                        $sheet->setCellValue('E' . $row, 'Met');
                    } elseif ($val >= 50) {
                        $sheet->setCellValue('E' . $row, 'On Track');
                    } elseif ($val >= 10) {
                        $sheet->setCellValue('E' . $row, 'In Progress');
                    } else {
                        $sheet->setCellValue('E' . $row, 'Not Performing');
                    }
                } else {
                    $sheet->setCellValue('D' . $row, 'N/A');
                    $sheet->setCellValue('E' . $row, 'Not Available');
                }
                $row++;
            }
        }

        $sheet->getStyle('A3:E' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
    }

    /**
     * generateClusterComparisonSheet():
     *   Show each cluster’s performance from $performanceSummary['clusters_data'].
     */
    private function generateClusterComparisonSheet($sheet, $performanceSummary, $timeline)
    {
        $sheet->setTitle('Cluster Comparison');
        $sheet->setCellValue('A1', 'Cluster Performance Comparison');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A3', 'Cluster');
        $sheet->setCellValue('B3', 'Total Indicators');
        $sheet->setCellValue('C3', 'Indicators w/Data');
        $sheet->setCellValue('D3', 'Average Score');
        $sheet->setCellValue('E3', 'Status');

        $sheet->getStyle('A3:E3')->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        $row = 4;
        if (! empty($performanceSummary['clusters_data'])) {
            foreach ($performanceSummary['clusters_data'] as $clusterID => $clusterData) {
                $sheet->setCellValue('A' . $row, $clusterData['name']);
                $sheet->setCellValue('B' . $row, $clusterData['indicators']);
                $sheet->setCellValue('C' . $row, $clusterData['indicators_with_data']);

                if ($clusterData['average_score'] !== null) {
                    $val = $clusterData['average_score'];
                    $sheet->setCellValue('D' . $row, number_format($val, 1) . '%');
                    if ($val > 100) {
                        $sheet->setCellValue('E' . $row, 'Over Achieved');
                    } elseif ($val >= 90) {
                        $sheet->setCellValue('E' . $row, 'Met');
                    } elseif ($val >= 50) {
                        $sheet->setCellValue('E' . $row, 'On Track');
                    } elseif ($val >= 10) {
                        $sheet->setCellValue('E' . $row, 'In Progress');
                    } else {
                        $sheet->setCellValue('E' . $row, 'Not Performing');
                    }
                } else {
                    $sheet->setCellValue('D' . $row, 'N/A');
                    $sheet->setCellValue('E' . $row, 'Not Available');
                }
                $row++;
            }
        }

        $sheet->getStyle('A3:E' . ($row - 1))->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
    }

    /**
     * addCombinedPerformanceCharts():
     *   Optionally embed Excel-based charts in the "Dashboard" sheet.
     */
    private function addCombinedPerformanceCharts(Spreadsheet $spreadsheet, $sheet, $performanceSummary, $allPerformanceData)
    {
        // We'll create a hidden "ChartData" sheet for storing category counts.
        $dataSheet = $spreadsheet->createSheet();
        $dataSheet->setTitle('ChartData-Hidden');

        // 1) write category distribution data
        $dataSheet->setCellValue('A1', 'Category');
        $dataSheet->setCellValue('B1', 'Count');

        $cats = [
            'Not Performing', 'In Progress', 'On Track', 'Met', 'Over Achieved', 'Qualitative', 'Not Available',
        ];
        $catCounts = $performanceSummary['category_counts'];
        $row       = 2;
        foreach ($cats as $c) {
            $dataSheet->setCellValue('A' . $row, $c);
            $dataSheet->setCellValue('B' . $row, $this->safeArrayGet($catCounts, $c, 0));
            $row++;
        }

        // build a pie chart
        $labelsRange = 'ChartData-Hidden!$A$2:$A$' . ($row - 1);
        $valuesRange = 'ChartData-Hidden!$B$2:$B$' . ($row - 1);

        $seriesVals = new DataSeriesValues('Number', $valuesRange, null, ($row - 2));
        $categories = new DataSeriesValues('String', $labelsRange, null, ($row - 2));
        $dataSeries = new DataSeries(
            DataSeries::TYPE_PIECHART,
            null,
            range(0, 0),
            [], // no series names
            [$categories],
            [$seriesVals]
        );

        $plotArea = new PlotArea(null, [$dataSeries]);
        $legend   = new Legend(Legend::POSITION_RIGHT, null, false);
        $title    = new Title('Category Distribution');

        $chart = new Chart(
            'CategoryPieChart',
            $title,
            $legend,
            $plotArea,
            true,
            0,
            null,
            null
        );

        // place chart on main "Dashboard"
        $chart->setTopLeftPosition('F5');
        $chart->setBottomRightPosition('M20');
        $sheet->addChart($chart);

        // hide the data sheet
        $dataSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
    }
}