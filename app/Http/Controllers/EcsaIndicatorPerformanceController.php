<?php
namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EcsaIndicatorPerformanceController extends Controller
{
    public function selectCluster()
    {
        // If the current user is not an admin, show only the user's attached cluster.
        if (Auth::user()->AccountRole !== 'Admin') {
            $clusters = DB::table('clusters')
                ->where('ClusterID', Auth::user()->ClusterID)
                ->get();
        } else {
            $clusters = DB::table('clusters')->get();
        }
        $Page = 'EcsaAnalytics.SelectCluster';
        return view('scrn', compact('Page', 'clusters'));
    }

    public function selectYear(Request $request)
    {
        $selectedCluster = $request->input('cluster', 'All clusters');
        $clusters        = DB::table('clusters')->get();
        $years           = DB::table('ecsahc_timelines')
            ->distinct()
            ->pluck('Year')
            ->sort()
            ->reverse()
            ->values();

        $Page = 'EcsaAnalytics.SelectYear';
        return view('scrn', compact('Page', 'years', 'clusters', 'selectedCluster'));
    }

    public function selectReport(Request $request)
    {
        $selectedCluster = $request->input('cluster');
        $selectedYear    = $request->input('year');
        $reports         = DB::table('ecsahc_timelines')
            ->where('Year', $selectedYear)
            ->get();

        // Retrieve clusters for proper display.
        $clusters = DB::table('clusters')->get();

        $Page = 'EcsaAnalytics.SelectReport';
        return view('scrn', compact('Page', 'reports', 'clusters', 'selectedCluster', 'selectedYear'));
    }

    public function showPerformance(Request $request)
    {
        $selectedCluster = $request->input('cluster');
        $selectedYear    = $request->input('year');
        $selectedReport  = $request->input('report');

        $clusters = DB::table('clusters')->get();
        $report   = DB::table('ecsahc_timelines')
            ->where('ReportingID', $selectedReport)
            ->first();

        // Build performance data including historical scores and detailed analysis.
        $performanceData = $this->getPerformanceData($selectedCluster, $selectedYear, $selectedReport);

        $Page = 'EcsaAnalytics.IndicatorPerformance';
        return view('scrn', compact(
            'Page',
            'performanceData',
            'clusters',
            'selectedCluster',
            'selectedYear',
            'selectedReport',
            'report'
        ));
    }

    /**
     * Retrieves and builds performance data for each strategic objective and its indicators.
     * For each indicator, calculates baseline, target, current score, status,
     * cluster responses, fetches historical scores, and runs advanced AI-like analysis.
     */
    private function getPerformanceData($selectedCluster, $selectedYear, $selectedReport)
    {
        $objectives      = DB::table('strategic_objectives')->limit(100)->get();
        $performanceData = [];

        foreach ($objectives as $objective) {
            $indicators = DB::table('performance_indicators')
                ->where('SO_ID', $objective->StrategicObjectiveID)
                ->limit(100)
                ->get();

            $objectiveData = [
                'name'        => $objective->SO_Name,
                'description' => $objective->Description,
                'indicators'  => [],
                'status'      => 'not performing',
            ];

            $metCount           = 0;
            $filteredIndicators = [];

            foreach ($indicators as $indicator) {
                // Decode the Responsible_Cluster JSON field.
                $responsibleClusters = json_decode($indicator->Responsible_Cluster, true);
                if (! is_array($responsibleClusters)) {
                    $responsibleClusters = [];
                }
                $isGlobal = in_array("All clusters/projects", $responsibleClusters);

                // Filter: If a specific cluster is selected and indicator is not global,
                // only include if the cluster is among those responsible.
                if (! $isGlobal && $selectedCluster !== 'All clusters' && ! in_array($selectedCluster, $responsibleClusters)) {
                    continue;
                }

                $baseline         = $this->getBaselineForYear($indicator->id, $selectedYear);
                $target           = $this->getTargetForYear($indicator->id, $selectedYear);
                $score            = $this->calculateScore($indicator, $selectedCluster, $selectedReport);
                $status           = $this->calculateStatus($score, $baseline, $target, $indicator);
                $clusterResponses = $this->getClusterResponses($indicator->id, $selectedReport);

                // Fetch historical scores for this indicator (using its IID) for trend analysis.
                $historicalScores = $this->getHistoricalScores($indicator, $selectedCluster);

                $indicatorData = [
                    'name'                => $indicator->Indicator_Name,
                    'baseline'            => $baseline,
                    'target'              => $target,
                    'score'               => $score,
                    'status'              => $status,
                    'responseType'        => $indicator->ResponseType,
                    'isGlobal'            => $isGlobal,
                    'responsibleClusters' => $responsibleClusters,
                    'clusterResponses'    => $clusterResponses,
                    'historicalScores'    => $historicalScores,
                ];

                // Run AI-like analysis to generate analysis text and recommendations.
                $analysisData                     = $this->analyzeIndicatorPerformance($indicatorData, $clusterResponses);
                $indicatorData['analysis']        = $analysisData['analysis'];
                $indicatorData['recommendations'] = $analysisData['recommendations'];

                $filteredIndicators[] = $indicatorData;
                if ($status === 'met') {
                    $metCount++;
                }
            }

            // Sort indicators so that global ones appear first.
            usort($filteredIndicators, function ($a, $b) {
                return $b['isGlobal'] <=> $a['isGlobal'];
            });

            $objectiveData['indicators']                       = $filteredIndicators;
            $totalIndicators                                   = count($filteredIndicators);
            $objectiveData['status']                           = $this->calculateObjectiveStatus($metCount, $totalIndicators);
            $performanceData[$objective->StrategicObjectiveID] = $objectiveData;
        }
        return $performanceData;
    }

    private function getBaselineForYear($indicatorId, $year)
    {
        // The baseline is stored in Baseline_2023_2024 (assumed static for all years).
        return DB::table('performance_indicators')
            ->where('id', $indicatorId)
            ->value('Baseline_2023_2024');
    }

    /**
     * Dynamically retrieves the target value for the given reporting year.
     * The first target year is defined as 2024.
     */
    private function getTargetForYear($indicatorId, $year)
    {
        $indicator = DB::table('performance_indicators')
            ->where('id', $indicatorId)
            ->first();
        $firstTargetYear = 2024;
        $maxTargets      = 3; // Supports Target_Year1, Target_Year2, Target_Year3.
        $offset          = $year - $firstTargetYear;
        if ($offset >= 0 && $offset < $maxTargets) {
            $targetColumn = 'Target_Year' . ($offset + 1);
            return $indicator->{$targetColumn};
        }
        return null;
    }

    /**
     * Calculate the current score for an indicator based on responses.
     * For "Number" responses, sums the responses.
     * For "Yes/No"/"Boolean", computes percentage of affirmative responses.
     */
    private function calculateScore($indicator, $clusterId, $reportId)
    {
        $query = DB::table('cluster_performance_mappings')
            ->where('IndicatorID', $indicator->id)
            ->where('ReportingID', $reportId);

        $responsibleClusters = json_decode($indicator->Responsible_Cluster, true);
        if (! is_array($responsibleClusters)) {
            $responsibleClusters = [];
        }

        if ($clusterId !== 'All clusters' && ! in_array("All clusters/projects", $responsibleClusters)) {
            $query->where('ClusterID', $clusterId);
        }

        $responses = $query->pluck('Response')->toArray();

        switch ($indicator->ResponseType) {
            case 'Number':
                return array_sum($responses);
            case 'Yes/No':
            case 'Boolean':
                $affirmativeCount = count(array_filter($responses, function ($response) {
                    return in_array(strtolower($response), ['yes', 'true', '1']);
                }));
                if ($clusterId !== 'All clusters') {
                    $expectedCount = in_array($clusterId, $responsibleClusters) ? 1 : 0;
                } else {
                    $expectedCount = count($responsibleClusters);
                }
                return ($expectedCount > 0) ? ($affirmativeCount / $expectedCount * 100) : 0;
            default:
                return null;
        }
    }

    /**
     * Determine the performance status based on the score relative to baseline and target.
     * Thresholds:
     * - Not Performing: < 10%
     * - In Progress: 10% to < 50%
     * - On Track: 50% to < 90%
     * - Met: 90% and above
     */
    private function calculateStatus($score, $baseline, $target, $indicator)
    {
        if ($score === null || $baseline === null || $target === null) {
            return 'not performing';
        }
        if ($indicator->ResponseType === 'Number') {
            $range = $target - $baseline;
            if ($range == 0) {
                return ($score >= $target) ? 'met' : 'not performing';
            }
            $percentage = (($score - $baseline) / $range) * 100;
        } elseif (in_array($indicator->ResponseType, ['Yes/No', 'Boolean'])) {
            $percentage = $score;
        } else {
            return 'N/A';
        }
        if ($percentage < 10) {
            return 'not performing';
        } elseif ($percentage < 50) {
            return 'in progress';
        } elseif ($percentage < 90) {
            return 'on track';
        } else {
            return 'met';
        }
    }

    /**
     * Retrieves cluster responses for a given indicator and report.
     */
    private function getClusterResponses($indicatorId, $reportId)
    {
        return DB::table('cluster_performance_mappings')
            ->where('IndicatorID', $indicatorId)
            ->where('ReportingID', $reportId)
            ->get()
            ->map(function ($item) {
                return (array) $item;
            })
            ->keyBy('ClusterID')
            ->toArray();
    }

    /**
     * Retrieves historical scores for a given indicator.
     * It joins the performance mappings with timelines based on ReportingID,
     * orders them by the ClosingDate, and calculates a score for each historical report.
     *
     * @param object $indicator A performance indicator record.
     * @param string $clusterId The selected cluster (or 'All clusters').
     * @return array An array of objects with keys: reportingID, date, and score.
     */
    private function getHistoricalScores($indicator, $clusterId)
    {
        $reports = DB::table('cluster_performance_mappings')
            ->join('ecsahc_timelines', 'cluster_performance_mappings.ReportingID', '=', 'ecsahc_timelines.ReportingID')
            ->where('cluster_performance_mappings.IndicatorID', $indicator->id)
            ->select('ecsahc_timelines.ReportingID', 'ecsahc_timelines.ClosingDate')
            ->distinct()
            ->orderBy('ecsahc_timelines.ClosingDate', 'asc')
            ->get();

        $historicalScores = [];
        foreach ($reports as $report) {
            $score = $this->calculateScore($indicator, $clusterId, $report->ReportingID);
            if ($score !== null) {
                $historicalScores[] = [
                    'reportingID' => $report->ReportingID,
                    'date'        => $report->ClosingDate,
                    'score'       => $score,
                ];
            }
        }
        return $historicalScores;
    }

    /**
     * Calculates the overall objective status based on the percentage of indicators that are 'met'.
     */
    private function calculateObjectiveStatus($metCount, $totalIndicators)
    {
        $percentage = ($totalIndicators > 0) ? ($metCount / $totalIndicators * 100) : 0;
        if ($percentage < 10) {
            return 'not performing';
        } elseif ($percentage < 50) {
            return 'in progress';
        } elseif ($percentage < 90) {
            return 'on track';
        } else {
            return 'met';
        }
    }

    /**
     * Advanced AI-like analysis function.
     * Evaluates an indicator’s performance (including historical trends) and returns detailed analysis and recommendations.
     *
     * @param array $indicator Indicator data including baseline, target, score, status, and historicalScores.
     * @param array $clusterResponses Optional cluster responses.
     * @return array Returns ['analysis' => string, 'recommendations' => array]
     */
    private function analyzeIndicatorPerformance(array $indicator, array $clusterResponses = []): array
    {
        $baseline = isset($indicator['baseline']) ? (float) $indicator['baseline'] : null;
        $target   = isset($indicator['target']) ? (float) $indicator['target'] : null;
        $score    = isset($indicator['score']) ? (float) $indicator['score'] : null;
        $status   = isset($indicator['status']) ? strtolower($indicator['status']) : 'not performing';

        $analysis        = '';
        $recommendations = [];

        if ($baseline === null || $target === null || $score === null) {
            $analysis          = "Insufficient data to analyze '{$indicator['name']}'.";
            $recommendations[] = "Ensure baseline, target, and current score are available.";
            return ['analysis' => $analysis, 'recommendations' => $recommendations];
        }

        $range = $target - $baseline;
        if ($range <= 0) {
            $progressPercent = ($score >= $target) ? 100 : 0;
        } else {
            $progressPercent = (($score - $baseline) / $range) * 100;
            $progressPercent = max(0, min(100, $progressPercent));
        }

        $analysis = "Indicator '{$indicator['name']}' Analysis:\n";
        $analysis .= "Baseline: {$baseline}, Target: {$target}, Current Score: {$score}\n";
        $analysis .= "Progress: " . round($progressPercent, 2) . "% towards the target.\n";

        if ($progressPercent < 10) {
            $analysis .= "Performance is critically low.\n";
            $recommendations[] = "Immediately review implementation strategies and allocate additional resources.";
            $recommendations[] = "Hold a strategic meeting with responsible teams.";
        } elseif ($progressPercent < 50) {
            $analysis .= "Limited progress is observed.\n";
            $recommendations[] = "Investigate barriers to performance and enhance monitoring.";
            $recommendations[] = "Consider revising strategies to accelerate improvement.";
        } elseif ($progressPercent < 90) {
            $analysis .= "Performance is improving, but further gains are needed.\n";
            $recommendations[] = "Maintain current efforts while exploring incremental enhancements.";
            $recommendations[] = "Collect feedback from clusters to identify optimization areas.";
        } else {
            $analysis .= "Indicator performance is near or at target.\n";
            $recommendations[] = "Document best practices and consider raising future targets.";
        }

        // Incorporate historical trend data if available.
        if (isset($indicator['historicalScores']) && count($indicator['historicalScores']) > 0) {
            $analysis .= "Historical Trend:\n";
            // Sort historical scores by date ascending.
            usort($indicator['historicalScores'], function ($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });
            foreach ($indicator['historicalScores'] as $entry) {
                $formattedDate = date('Y-m-d', strtotime($entry['date']));
                $analysis .= "$formattedDate: Score = " . round($entry['score'], 2) . "\n";
            }
        }

        // Analyze cluster response variability.
        if (! empty($clusterResponses)) {
            $values = [];
            foreach ($clusterResponses as $response) {
                if (isset($response['Response']) && is_numeric($response['Response'])) {
                    $values[] = (float) $response['Response'];
                }
            }
            if (! empty($values)) {
                $average  = array_sum($values) / count($values);
                $variance = array_sum(array_map(function ($val) use ($average) {
                    return pow($val - $average, 2);
                }, $values)) / count($values);
                $stdDev = sqrt($variance);
                $analysis .= "Cluster Responses: Average = " . round($average, 2) . ", Std Dev = " . round($stdDev, 2) . "\n";
                if ($stdDev > ($range * 0.2)) {
                    $recommendations[] = "High variability in cluster responses detected. Standardize processes for consistency.";
                } else {
                    $recommendations[] = "Cluster responses are consistent.";
                }
            }
        }

        return ['analysis' => $analysis, 'recommendations' => $recommendations];
    }

    public function exportCsv(Request $request)
    {
        $selectedCluster = $request->input('cluster');
        $selectedYear    = $request->input('year');
        $selectedReport  = $request->input('report');

        $performanceData = $this->getPerformanceData($selectedCluster, $selectedYear, $selectedReport);
        $filename        = "performance_report_{$selectedCluster}_{$selectedYear}.csv";
        $headers         = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0",
        ];
        $columns = ['Objective', 'Indicator', 'Baseline', 'Target', 'Score', 'Status'];

        $callback = function () use ($performanceData, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($performanceData as $objective) {
                foreach ($objective['indicators'] as $indicator) {
                    fputcsv($file, [
                        $objective['name'],
                        $indicator['name'],
                        $indicator['baseline'],
                        $indicator['target'],
                        $indicator['score'],
                        $indicator['status'],
                    ]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}