<?php
namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EcsaReportingController extends Controller
{
                                             // Configuration constants
    const TARGET_ACHIEVEMENT_THRESHOLD = 90; // 90% threshold for target achievement
    const INDICATORS_PER_PAGE          = 3;

                                                  // Performance status thresholds
    const PERFORMANCE_MET_THRESHOLD         = 90; // ≥90% = "Met"
    const PERFORMANCE_ON_TRACK_THRESHOLD    = 50; // 50-89% = "On Track"
    const PERFORMANCE_IN_PROGRESS_THRESHOLD = 10; // 10-49% = "In Progress"
                                                  // <10% = "Not Performing"

    /**
     * Validate request and redirect if validation fails
     *
     * @param Request $request
     * @param array $rules
     * @param string $redirectRoute
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function validateAndRedirect(Request $request, array $rules, string $redirectRoute)
    {
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->route('Ecsa_' . $redirectRoute)
                ->withErrors($validator)
                ->withInput();
        }
        return null;
    }

    /**
     * Get timeline year from reporting ID
     *
     * @param string $reportingId
     * @return int
     */
    private function getTimelineYear(string $reportingId)
    {
        $timeline = DB::table('ecsahc_timelines')
            ->where('ReportingID', $reportingId)
            ->first();

        return $timeline->Year ?? date('Y');
    }

    /**
     * Get existing baseline for an indicator
     *
     * @param string $indicatorId
     * @param string $clusterId
     * @return mixed
     */
    private function getExistingBaseline(string $indicatorId, string $clusterId)
    {
        return DB::table('cluster_indicator_targets')
            ->where('IndicatorID', $indicatorId)
            ->where('ClusterID', $clusterId)
            ->value('Baseline2024');
    }

    /**
     * Display the user selection screen
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function SelectUser()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $currentUser = Auth::user();
        if ($currentUser->UserType !== 'ECSA-HC') {
            return redirect()->route('entity.select');
        }

        $users = DB::table('users')
            ->where('UserType', 'ECSA-HC')
            ->orderBy('name', 'asc')
            ->get();

        return view('scrn', [
            "Desc"  => "Select an ECSA-HC user to begin reporting",
            "Page"  => "EcsaReporting.EcsaSelectUser",
            "users" => $users,
        ]);
    }

    /**
     * Display the cluster selection screen
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function SelectCluster(Request $request)
    {
        $redirectResult = $this->validateAndRedirect($request, ['UserID' => 'required|exists:users,UserID'], 'SelectUser');
        if ($redirectResult) {
            return $redirectResult;
        }

        $user = DB::table('users')->where('UserID', $request->UserID)->first();
        if (! $user) {
            return redirect()->route('Ecsa_SelectUser')->with('error', 'User not found.');
        }

        $clusters = DB::table('clusters')
            ->where('ClusterID', $user->ClusterID)
            ->get();

        if ($clusters->isEmpty()) {
            return redirect()->route('Ecsa_SelectUser')->with('error', 'No clusters found for the selected user.');
        }

        return view('scrn', [
            "Desc"     => "Select a cluster for reporting",
            "Page"     => "EcsaReporting.EcsaSelectCluster",
            "clusters" => $clusters,
            "user"     => $user,
            "userName" => $user->name,
        ]);
    }

    /**
     * Display the timeline selection screen
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function SelectTimeline(Request $request)
    {
        $rules = [
            'UserID'    => 'required|exists:users,UserID',
            'ClusterID' => 'required|exists:clusters,ClusterID',
        ];

        $redirectResult = $this->validateAndRedirect($request, $rules, 'SelectCluster');
        if ($redirectResult) {
            return $redirectResult;
        }

        $user    = DB::table('users')->where('UserID', $request->UserID)->first();
        $cluster = DB::table('clusters')->where('ClusterID', $request->ClusterID)->first();

        if (! $user || ! $cluster) {
            return redirect()->route('Ecsa_SelectCluster')->with('error', 'User or Cluster not found.');
        }

        $timelines = DB::table('ecsahc_timelines')
            ->where('status', 'In Progress')
            ->get();

        return view('scrn', [
            "Desc"        => "Select a timeline for reporting",
            "Page"        => "EcsaReporting.EcsaSelectTimeline",
            "timelines"   => $timelines,
            "UserID"      => $request->UserID,
            "ClusterID"   => $request->ClusterID,
            "userName"    => $user->name,
            "clusterName" => $cluster->Cluster_Name,
        ]);
    }

    /**
     * Display the strategic objective selection screen
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function SelectStrategicObjective(Request $request)
    {
        $rules = [
            'UserID'      => 'required|exists:users,UserID',
            'ClusterID'   => 'required|exists:clusters,ClusterID',
            'ReportingID' => 'required|exists:ecsahc_timelines,ReportingID',
        ];

        $redirectResult = $this->validateAndRedirect($request, $rules, 'SelectTimeline');
        if ($redirectResult) {
            return $redirectResult;
        }

        $user     = DB::table('users')->where('UserID', $request->UserID)->first();
        $cluster  = DB::table('clusters')->where('ClusterID', $request->ClusterID)->first();
        $timeline = DB::table('ecsahc_timelines')->where('ReportingID', $request->ReportingID)->first();

        if (! $user || ! $cluster || ! $timeline) {
            return redirect()->route('Ecsa_SelectTimeline')->with('error', 'User, Cluster, or Timeline not found.');
        }

        if (trim($user->ClusterID) !== trim($request->ClusterID)) {
            return redirect()->route('Ecsa_SelectCluster')->with('error', 'Selected cluster does not match user\'s assigned cluster.');
        }

        $strategicObjectives = DB::table('strategic_objectives')
            ->whereExists(function ($query) use ($request) {
                $query->select(DB::raw(1))
                    ->from('performance_indicators')
                    ->whereColumn('performance_indicators.SO_ID', 'strategic_objectives.StrategicObjectiveID')
                    ->whereJsonContains('performance_indicators.Responsible_Cluster', trim($request->ClusterID));
            })
            ->get();

        return view('scrn', [
            "Desc"                => "Select a strategic objective for reporting",
            "Page"                => "EcsaReporting.EcsaStrategicObjectives",
            "strategicObjectives" => $strategicObjectives,
            "UserID"              => $request->UserID,
            "ClusterID"           => $request->ClusterID,
            "ReportingID"         => $request->ReportingID,
            "userName"            => $user->name,
            "clusterName"         => $cluster->Cluster_Name,
            "timelineName"        => $timeline->ReportName,
        ]);
    }

    /**
     * Display the performance indicators reporting screen
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function ReportPerformanceIndicators(Request $request)
    {
        $rules = [
            'UserID'               => 'required|exists:users,UserID',
            'ClusterID'            => 'required|exists:clusters,ClusterID',
            'ReportingID'          => 'required|exists:ecsahc_timelines,ReportingID',
            'StrategicObjectiveID' => 'required|exists:strategic_objectives,StrategicObjectiveID',
        ];

        $redirectResult = $this->validateAndRedirect($request, $rules, 'SelectStrategicObjective');
        if ($redirectResult) {
            return $redirectResult;
        }

        // Fetch all required entities
        $user               = DB::table('users')->where('UserID', $request->UserID)->first();
        $cluster            = DB::table('clusters')->where('ClusterID', $request->ClusterID)->first();
        $timeline           = DB::table('ecsahc_timelines')->where('ReportingID', $request->ReportingID)->first();
        $strategicObjective = DB::table('strategic_objectives')
            ->where('StrategicObjectiveID', $request->StrategicObjectiveID)
            ->first();

        if (! $user || ! $cluster || ! $timeline || ! $strategicObjective) {
            return redirect()->route('Ecsa_SelectStrategicObjective')->with('error', 'Required data not found.');
        }

        // Get current timeline year
        $timelineYear = $timeline->Year;

        // Get indicators with proper target filtering
        $indicators = DB::table('performance_indicators')
            ->leftJoin('cluster_indicator_targets', function ($join) use ($request, $timelineYear) {
                $join->on('performance_indicators.id', '=', 'cluster_indicator_targets.IndicatorID')
                    ->where('cluster_indicator_targets.ClusterID', $request->ClusterID)
                    ->where('cluster_indicator_targets.Target_Year', $timelineYear);
            })
            ->where('SO_ID', $request->StrategicObjectiveID)
            ->whereJsonContains('Responsible_Cluster', trim($request->ClusterID))
            ->select(
                'performance_indicators.*',
                'cluster_indicator_targets.Target_Value',
                'cluster_indicator_targets.Target_Year',
                'cluster_indicator_targets.Baseline2024'
            )
            ->get();

        if ($indicators->isEmpty()) {
            return redirect()->route('Ecsa_SelectStrategicObjective')->with('error', 'No performance indicators found.');
        }

        // Get existing reports
        $existingReportsQuery = DB::table('cluster_performance_mappings')
            ->join('users', 'cluster_performance_mappings.UserID', '=', 'users.UserID')
            ->leftJoin('cluster_indicator_targets', function ($join) use ($request) {
                $join->on('cluster_performance_mappings.IndicatorID', '=', 'cluster_indicator_targets.IndicatorID')
                    ->where('cluster_indicator_targets.ClusterID', '=', $request->ClusterID);
            })
            ->where('cluster_performance_mappings.ClusterID', $request->ClusterID)
            ->where('cluster_performance_mappings.ReportingID', $request->ReportingID)
            ->where('cluster_performance_mappings.SO_ID', $request->StrategicObjectiveID)
            ->select(
                'cluster_performance_mappings.*',
                'users.name as reporter_name',
                'users.email as reporter_email',
                'cluster_indicator_targets.Baseline2024 as baseline'
            );

        $existingReports = $existingReportsQuery->get()->keyBy('IndicatorID');

        // Progress calculations
        $totalIndicators    = $indicators->count();
        $reportedIndicators = $existingReports->count();
        $progressPercentage = $totalIndicators > 0 ? ($reportedIndicators / $totalIndicators) * 100 : 0;

        // Target achievement calculation
        $metTargets = 0;
        foreach ($existingReports as $report) {
            $indicator = $indicators->firstWhere('id', $report->IndicatorID);
            if (! $indicator) {
                continue;
            }

            // Use cluster-specific target value (no fallback to an undefined property)
            $targetValue   = $indicator->Target_Value;
            $reportedValue = $report->Response;

            if ($indicator->ResponseType === 'Number' && is_numeric($reportedValue) && $targetValue) {
                $achievement = ($reportedValue / $targetValue) * 100;
                if ($achievement >= self::TARGET_ACHIEVEMENT_THRESHOLD) {
                    $metTargets++;
                }
            } elseif ($report->ResponseType === 'Yes/No' && $reportedValue === 'Yes') {
                $metTargets++;
            }
        }

        $totalTargets                = $indicators->where('Target_Value', '!=', null)->count();
        $targetAchievementPercentage = $totalTargets > 0 ? ($metTargets / $totalTargets) * 100 : 0;

        // Missing baseline detection - check cluster_indicator_targets table
        $missingBaseline = $indicators
            ->where('ResponseType', 'Number')
            ->whereNull('Baseline2024')
            ->map(function ($indicator) {
                return (object) [
                    'id'               => $indicator->id,
                    'Indicator_Number' => $indicator->Indicator_Number,
                    'Indicator_Name'   => $indicator->Indicator_Name,
                ];
            })
            ->values();

        // Pagination handling
        $currentPage         = $request->get('page', 1);
        $offset              = ($currentPage - 1) * self::INDICATORS_PER_PAGE;
        $paginatedIndicators = $indicators->slice($offset, self::INDICATORS_PER_PAGE);

        return view('scrn', [
            "Desc"                        => "Report on performance indicators",
            "Page"                        => "EcsaReporting.EcsaReport",
            "indicators"                  => $indicators,
            "UserID"                      => $request->UserID,
            "ClusterID"                   => $request->ClusterID,
            "ReportingID"                 => $request->ReportingID,
            "StrategicObjectiveID"        => $request->StrategicObjectiveID,
            "userName"                    => $user->name,
            "clusterName"                 => $cluster->Cluster_Name,
            "timelineName"                => $timeline->ReportName,
            "objectiveName"               => $strategicObjective->SO_Name . ' | ' . $strategicObjective->Description,
            "existingReports"             => $existingReports,
            "progressPercentage"          => $progressPercentage,
            "totalIndicators"             => $totalIndicators,
            "reportedIndicators"          => $reportedIndicators,
            "timelineStatus"              => $timeline->status,
            "totalTargets"                => $totalTargets,
            "metTargets"                  => $metTargets,
            "targetAchievementPercentage" => $targetAchievementPercentage,
            "missingBaseline"             => $missingBaseline,
            "currentPage"                 => $currentPage,
            "perPage"                     => self::INDICATORS_PER_PAGE,
            "timelineYear"                => $timelineYear,
        ]);
    }

    /**
     * Save performance report data
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function SavePerformanceReport(Request $request)
    {
        $rules = [
            'UserID'               => 'required|exists:users,UserID',
            'ClusterID'            => 'required|exists:clusters,ClusterID',
            'ReportingID'          => 'required|exists:ecsahc_timelines,ReportingID',
            'StrategicObjectiveID' => 'required|exists:strategic_objectives,StrategicObjectiveID',
            'IndicatorID'          => 'required|exists:performance_indicators,id',
            'Response'             => 'required',
            'ResponseType'         => 'required|in:Text,Number,Boolean,Yes/No',
            'Comment'              => 'nullable|string',
            'Baseline'             => 'nullable|numeric',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->route('Ecsa_ReportPerformanceIndicators', $request->only([
                'UserID', 'ClusterID', 'ReportingID', 'StrategicObjectiveID',
            ]))->withErrors($validator)->withInput();
        }

        // Validate response based on type
        if ($request->ResponseType === 'Yes/No' && ! in_array($request->Response, ['Yes', 'No'])) {
            return redirect()->back()->with('error', 'Invalid response for Yes/No type');
        }

        if ($request->ResponseType === 'Boolean' && ! in_array($request->Response, ['True', 'False'])) {
            return redirect()->back()->with('error', 'Invalid response for Boolean type');
        }

        // Update or create performance mapping
        DB::table('cluster_performance_mappings')->updateOrInsert(
            [
                'ClusterID'   => $request->ClusterID,
                'ReportingID' => $request->ReportingID,
                'SO_ID'       => $request->StrategicObjectiveID,
                'UserID'      => $request->UserID,
                'IndicatorID' => $request->IndicatorID,
            ],
            [
                'Response'         => $request->Response,
                'ResponseType'     => $request->ResponseType,
                'ReportingComment' => $request->Comment,
                'updated_at'       => now(),
            ]
        );

        // If baseline is provided, update it in the cluster_indicator_targets table
        if ($request->has('Baseline') && ! is_null($request->Baseline)) {
            // Check if a target record exists
            $targetExists = DB::table('cluster_indicator_targets')
                ->where('ClusterID', $request->ClusterID)
                ->where('IndicatorID', $request->IndicatorID)
                ->exists();

            if ($targetExists) {
                // Update existing target record
                DB::table('cluster_indicator_targets')
                    ->where('ClusterID', $request->ClusterID)
                    ->where('IndicatorID', $request->IndicatorID)
                    ->update(['Baseline2024' => $request->Baseline]);
            } else {
                // Create a new target record with baseline
                $timelineYear = $this->getTimelineYear($request->ReportingID);
                DB::table('cluster_indicator_targets')->insert([
                    'ClusterTargetID' => 'target_' . uniqid('', true),
                    'ClusterID'       => $request->ClusterID,
                    'IndicatorID'     => $request->IndicatorID,
                    'Target_Year'     => $timelineYear,
                    'Target_Value'    => 0, // Default value, should be updated later
                    'ResponseType'    => $request->ResponseType,
                    'Baseline2024'    => $request->Baseline,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        }

        return redirect()->route('Ecsa_ReportPerformanceIndicators', [
            'UserID'               => $request->UserID,
            'ClusterID'            => $request->ClusterID,
            'ReportingID'          => $request->ReportingID,
            'StrategicObjectiveID' => $request->StrategicObjectiveID,
        ])->with('success', 'Report saved successfully!');
    }

    /**
     * Display the reporting summary
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function GetReportingSummary(Request $request)
    {
        $rules = [
            'UserID'               => 'required|exists:users,UserID',
            'ClusterID'            => 'required|exists:clusters,ClusterID',
            'ReportingID'          => 'required|exists:ecsahc_timelines,ReportingID',
            'StrategicObjectiveID' => 'required|exists:strategic_objectives,StrategicObjectiveID',
        ];

        $redirectResult = $this->validateAndRedirect($request, $rules, 'ReportPerformanceIndicators');
        if ($redirectResult) {
            return $redirectResult;
        }

        $user               = DB::table('users')->where('UserID', $request->UserID)->first();
        $cluster            = DB::table('clusters')->where('ClusterID', $request->ClusterID)->first();
        $timeline           = DB::table('ecsahc_timelines')->where('ReportingID', $request->ReportingID)->first();
        $strategicObjective = DB::table('strategic_objectives')
            ->where('StrategicObjectiveID', $request->StrategicObjectiveID)
            ->first();

        if (! $user || ! $cluster || ! $timeline || ! $strategicObjective) {
            return redirect()->route('Ecsa_ReportPerformanceIndicators')->with('error', 'Required data not found.');
        }

        $reports = DB::table('cluster_performance_mappings')
            ->join('users', 'cluster_performance_mappings.UserID', '=', 'users.UserID')
            ->leftJoin('cluster_indicator_targets', function ($join) use ($request) {
                $join->on('cluster_performance_mappings.IndicatorID', '=', 'cluster_indicator_targets.IndicatorID')
                    ->where('cluster_indicator_targets.ClusterID', '=', $request->ClusterID);
            })
            ->leftJoin('performance_indicators', 'cluster_performance_mappings.IndicatorID', '=', 'performance_indicators.id')
            ->select(
                'cluster_performance_mappings.*',
                'users.name as reporter_name',
                'users.email as reporter_email',
                'cluster_indicator_targets.Baseline2024 as baseline'
            )
            ->where('cluster_performance_mappings.ClusterID', $request->ClusterID)
            ->where('cluster_performance_mappings.ReportingID', $request->ReportingID)
            ->where('cluster_performance_mappings.SO_ID', $request->StrategicObjectiveID)
            ->get();

        return view('scrn', [
            "Desc"                 => "Reporting Summary",
            "Page"                 => "EcsaReporting.ReportingSummary",
            "reports"              => $reports,
            "UserID"               => $request->UserID,
            "ClusterID"            => $request->ClusterID,
            "ReportingID"          => $request->ReportingID,
            "StrategicObjectiveID" => $request->StrategicObjectiveID,
            "userName"             => $user->name,
            "clusterName"          => $cluster->Cluster_Name,
            "timelineName"         => $timeline->ReportName,
            "objectiveName"        => $strategicObjective->SO_Name . ' | ' . $strategicObjective->Description,
        ]);
    }

    /**
     * Mark indicators as not applicable
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function MarkIndicatorsNotApplicable(Request $request)
    {
        $rules = [
            'UserID'               => 'required|exists:users,UserID',
            'ClusterID'            => 'required|exists:clusters,ClusterID',
            'ReportingID'          => 'required|exists:ecsahc_timelines,ReportingID',
            'StrategicObjectiveID' => 'required|exists:strategic_objectives,StrategicObjectiveID',
            'IndicatorIDs'         => 'required|array',
            'IndicatorIDs.*'       => 'required|exists:performance_indicators,id',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->route('Ecsa_ReportPerformanceIndicators', $request->only([
                'UserID', 'ClusterID', 'ReportingID', 'StrategicObjectiveID',
            ]))->withErrors($validator)->withInput();
        }

        foreach ($request->IndicatorIDs as $indicatorId) {
            // Get indicator response type
            $responseType = DB::table('performance_indicators')
                ->where('id', $indicatorId)
                ->value('ResponseType');

            if (! $responseType) {
                continue;
            }

            // Update or create performance mapping
            DB::table('cluster_performance_mappings')->updateOrInsert(
                [
                    'ClusterID'   => $request->ClusterID,
                    'ReportingID' => $request->ReportingID,
                    'SO_ID'       => $request->StrategicObjectiveID,
                    'UserID'      => $request->UserID,
                    'IndicatorID' => $indicatorId,
                ],
                [
                    'Response'         => 'Not Applicable',
                    'ResponseType'     => $responseType,
                    'ReportingComment' => 'Marked as not applicable',
                    'updated_at'       => now(),
                ]
            );
        }

        return redirect()->route('Ecsa_ReportPerformanceIndicators', [
            'UserID'               => $request->UserID,
            'ClusterID'            => $request->ClusterID,
            'ReportingID'          => $request->ReportingID,
            'StrategicObjectiveID' => $request->StrategicObjectiveID,
        ])->with('success', 'Indicators marked as not applicable successfully!');
    }
}