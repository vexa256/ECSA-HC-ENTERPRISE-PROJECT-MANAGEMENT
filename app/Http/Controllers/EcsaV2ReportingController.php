<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EcsaV2ReportingController extends Controller
{
    /**
     * Render the main view with the specified partial.
     */
    private function renderView($page, $data = [])
    {
        return view('scrn', array_merge($data, [
            'Page'          => $page,
            'notifications' => session('notifications', [])
        ]));
    }

    /**
     * Show the form to select a cluster and reporting timeline.
     */
    public function selectClusterAndTimeline()
    {
        // Fetch all clusters excluding "All clusters/projects"
        $clusters = DB::table('clusters')
            ->where('ClusterID', '!=', 'All clusters/projects')
            ->orderBy('Cluster_Name')
            ->get();

        // Fetch all active reporting timelines
        $timelines = DB::table('ecsahc_timelines')
            ->where('status', 'In Progress')
            ->orderBy('ClosingDate')
            ->get();

        return $this->renderView('ecsaReporting.select-cluster-timeline', [
            'clusters'  => $clusters,
            'timelines' => $timelines,
        ]);
    }

    /**
     * Handle the submission of the cluster and timeline selection form.
     */
    public function handleClusterAndTimelineSelection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ClusterID'   => 'required|exists:clusters,ClusterID',
            'ReportingID' => 'required|exists:ecsahc_timelines,ReportingID',
        ]);

        if ($validator->fails()) {
            return redirect()->route('cluster.reporting.select')
                ->with('notifications', [
                    'type'    => 'error',
                    'message' => 'Invalid cluster or timeline selection',
                ]);
        }

        // Store the selected cluster and timeline in session for later use
        session([
            'selectedClusterID'   => $request->ClusterID,
            'selectedReportingID' => $request->ReportingID,
        ]);

        return redirect()->route('cluster.reporting.enterData');
    }

    /**
     * Show the form to enter performance data and track progress in one view.
     */
    public function enterPerformanceData()
    {
        // Retrieve the selected cluster and timeline from session
        $clusterID   = session('selectedClusterID');
        $reportingID = session('selectedReportingID');

        if (! $clusterID || ! $reportingID) {
            return redirect()->route('cluster.reporting.select')
                ->with('notifications', [
                    'type'    => 'error',
                    'message' => 'No cluster or timeline selected',
                ]);
        }

        // Fetch the selected cluster and timeline details
        $cluster  = DB::table('clusters')->where('ClusterID', $clusterID)->first();
        $timeline = DB::table('ecsahc_timelines')->where('ReportingID', $reportingID)->first();

        // Fetch indicators assigned to the selected cluster
        $indicators = DB::table('performance_indicators')
            ->whereJsonContains('Responsible_Cluster', $clusterID)
            ->select('id', 'Indicator_Number', 'Indicator_Name', 'SO_ID', 'ResponseType')
            ->get();

        // Fetch existing performance data for the selected cluster and timeline
        $existingData = DB::table('cluster_performance_mappings')
            ->where('ClusterID', $clusterID)
            ->where('ReportingID', $reportingID)
            ->get()
            ->keyBy('IndicatorID');

        // Calculate reporting progress
        $totalIndicators    = $indicators->count();
        $reportedIndicators = $existingData->count();
        $progress           = $totalIndicators > 0 ? round(($reportedIndicators / $totalIndicators) * 100, 2) : 0;

        return $this->renderView('ecsaReporting.enter-performance-data', [
            'cluster'            => $cluster,
            'timeline'           => $timeline,
            'indicators'         => $indicators,
            'existingData'       => $existingData,
            'progress'           => $progress,
            'totalIndicators'    => $totalIndicators,
            'reportedIndicators' => $reportedIndicators,
        ]);
    }

    /**
     * Handle the submission of performance data.
     */
    public function submitPerformanceData(Request $request)
    {
        // Retrieve the selected cluster and timeline from session
        $clusterID   = session('selectedClusterID');
        $reportingID = session('selectedReportingID');

        if (! $clusterID || ! $reportingID) {
            return redirect()->route('cluster.reporting.select')
                ->with('notifications', [
                    'type'    => 'error',
                    'message' => 'No cluster or timeline selected',
                ]);
        }

        // Validate the submitted data
        $validator = Validator::make($request->all(), [
            'indicators'            => 'required|array',
            'indicators.*.id'       => 'required|exists:performance_indicators,id',
            'indicators.*.response' => 'required',
            'indicators.*.comment'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Process each indicator response
        foreach ($request->indicators as $indicatorData) {
            $indicatorID = $indicatorData['id'];
            $response    = $indicatorData['response'];
            $comment     = $indicatorData['comment'] ?? null;

            // Fetch the indicator details to validate the response type
            $indicator = DB::table('performance_indicators')
                ->where('id', $indicatorID)
                ->first();

            if (! $indicator) {
                continue; // Skip invalid indicators
            }

            // Validate the response based on the indicator's ResponseType
            $responseValidationRules = [];
            switch ($indicator->ResponseType) {
                case 'Number':
                    $responseValidationRules = ['numeric'];
                    break;
                case 'Yes/No':
                    $responseValidationRules = ['in:Yes,No'];
                    break;
                case 'Boolean':
                    $responseValidationRules = ['boolean'];
                    break;
                case 'Text':
                    $responseValidationRules = ['string'];
                    break;
            }

            $responseValidator = Validator::make(['response' => $response], [
                'response' => $responseValidationRules,
            ]);

            if ($responseValidator->fails()) {
                return redirect()->back()
                    ->with('notifications', [
                        'type'    => 'error',
                        'message' => "Invalid response type for indicator {$indicator->Indicator_Number}",
                    ])
                    ->withInput();
            }

            // Insert or update the performance data
            DB::table('cluster_performance_mappings')->updateOrInsert(
                [
                    'ClusterID'   => $clusterID,
                    'ReportingID' => $reportingID,
                    'IndicatorID' => $indicatorID,
                ],
                [
                    'Response'         => $response,
                    'ReportingComment' => $comment,
                    'ResponseType'     => $indicator->ResponseType,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]
            );
        }

        return redirect()->route('cluster.reporting.enterData')
            ->with('notifications', [
                'type'    => 'success',
                'message' => 'Performance data submitted successfully',
            ]);
    }
}