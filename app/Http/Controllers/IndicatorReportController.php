<?php
namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class IndicatorReportController extends Controller
{
    /**
     * Display the entity selection page.
     *
     * Retrieves the list of entities from the mpa_entities table and passes it to the view.
     *
     * @return \Illuminate\View\View
     */
    public function selectEntity()
    {
        $user = Auth::user();

        // If the logged in user is MPA and not an Admin, restrict to their own entity.
        if ($user->UserType === 'MPA' && $user->AccountRole !== 'Admin') {
            $entities = DB::table('mpa_entities')
                ->where('EntityID', $user->EntityID)
                ->pluck('Entity', 'EntityID');
        } else {
            // Otherwise, show all entities.
            $entities = DB::table('mpa_entities')->pluck('Entity', 'EntityID');
        }

        $Page = 'IndicatorReporting.SelectEntity';

        return view('scrn', compact('Page', 'entities'));
    }

    /**
     * Display the reporting period selection page.
     *
     * Validates the selected entity, retrieves the entity details, and then retrieves all reporting periods
     * from the mpa_timelines table. The reporting periods are ordered by Year (descending) and ReportingID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function selectReportingPeriod(Request $request)
    {
        // Validate that the 'entity_id' field is provided and exists in the mpa_entities table.
        $validator = Validator::make($request->all(), [
            'entity_id' => 'required|exists:mpa_entities,EntityID',
        ]);

        // If validation fails, redirect back to the entity selection page with errors.
        if ($validator->fails()) {
            return redirect()->route('entity.select')->withErrors($validator);
        }

        // Retrieve the selected entity ID.
        $entityID = $request->input('entity_id');

        // Retrieve the corresponding entity record.
        $entity = DB::table('mpa_entities')->where('EntityID', $entityID)->first();

        // Retrieve available reporting periods from the mpa_timelines table,
        // ordered by Year (descending) and then by ReportingID.
        $reportingPeriods = DB::table('mpa_timelines')
            ->orderBy('Year', 'desc')
            ->orderBy('ReportingID')
            ->get(['ReportingID', 'ReportName', 'Year', 'Type']);

        // Set the view identifier.
        $Page = 'IndicatorReporting.SelectPeriod';

        // Render the view "scrn" with the reporting periods and entity details.
        return view('scrn', compact('Page', 'reportingPeriods', 'entity'));
    }

    /**
     * Display the indicators for reporting.
     *
     * This method:
     * 1. Validates that both 'entity_id' and 'reporting_period' are provided.
     * 2. Retrieves the selected entity and timeline (reporting period) from the database.
     * 3. Determines the allowed reporting types based on the timeline.
     * 4. Retrieves indicators from the mpa_indicators table that match the allowed reporting types.
     *    - For entities other than "ECSA-HC" and "IGAD": returns indicators where EntityID equals the selected entity
     *      OR is hard-coded to "RRF" (representing universal RRF indicators).
     *    - For "ECSA-HC" and "IGAD": returns only indicators matching the selected entity.
     * 5. If no indicators are found matching these criteria, the user is redirected back to the entity selection
     *    page with an error message.
     * 6. For each indicator, attaches a default "yearlyTargets" array and retrieves historical reporting data.
     * 7. Retrieves any existing responses and comments for the current reporting period.
     * 8. Calculates progress metrics (total indicators, reported indicators, and overall progress percentage).
     * 9. Passes all the data to the view.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showIndicators(Request $request)
    {
        // Validate that 'entity_id' and 'reporting_period' are present and valid.
        $validator = Validator::make($request->all(), [
            'entity_id'        => 'required|exists:mpa_entities,EntityID',
            'reporting_period' => 'required|exists:mpa_timelines,ReportingID',
        ]);

        // If validation fails, redirect back to the entity selection page with error messages.
        if ($validator->fails()) {
            return redirect()->route('entity.select')->withErrors($validator);
        }

        // Retrieve input values.
        $entityID        = $request->input('entity_id');
        $reportingPeriod = $request->input('reporting_period');

        // Retrieve the selected entity record.
        $entity = DB::table('mpa_entities')->where('EntityID', $entityID)->first();

        // Retrieve the reporting period (timeline) record.
        $timeline = DB::table('mpa_timelines')->where('ReportingID', $reportingPeriod)->first();

        // If the timeline is not found, redirect back with an error message.
        if (! $timeline) {
            return redirect()->route('reporting.period.select')
                ->with('error', 'Invalid reporting period selected.');
        }

        // Determine allowed reporting types based on the current timeline.
        $reportingTypes = $this->getReportingTypes($timeline);

        // Retrieve indicators from the mpa_indicators table that match the allowed reporting types.
        // Apply different filtering based on the selected entity:
        // - For entities other than "ECSA-HC" and "IGAD", include indicators where EntityID equals the selected entity
        //   OR is hard-coded to "RRF" (regional indicators).
        if (! in_array($entityID, ['ECSA-HC', 'IGAD'])) {
            $indicators = DB::table('mpa_indicators')
                ->whereIn('ReportingPeriod', $reportingTypes)
                ->where(function ($query) use ($entityID) {
                    $query->where('EntityID', $entityID)
                        ->orWhere('EntityID', '=', 'RRF');
                })
                ->get();
        } else {
            // For ECSA-HC and IGAD, only return indicators that match the selected entity.
            $indicators = DB::table('mpa_indicators')
                ->whereIn('ReportingPeriod', $reportingTypes)
                ->where('EntityID', $entityID)
                ->get();
        }

        // If no indicators are found matching the criteria, redirect the user to the entity selection page
        // with an error message.
        if ($indicators->isEmpty()) {
            return redirect()->route('entity.select')
                ->with('error', 'No indicators found for the selected criteria. Please select a different entity or reporting period. The chosen entity may not have any indicators for this report.');

        }

        // For each indicator, attach additional information:
        // a) Set a default "yearlyTargets" array using the timeline's Year.
        // b) Retrieve historical reporting data by joining mpa_reports with mpa_timelines.

        $indicators = $indicators->map(function ($indicator) use ($timeline, $entityID) {
            $indicator->history = DB::table('mpa_reports')
                ->join('mpa_timelines', 'mpa_reports.ReportingID', '=', 'mpa_timelines.ReportingID')
                ->select(
                    'mpa_timelines.ReportName',
                    'mpa_timelines.Year',
                    'mpa_reports.Response',
                    'mpa_reports.Comments',
                    'mpa_reports.ReportedBy'
                )
                ->where('mpa_reports.IID', $indicator->IID)
                ->where('mpa_reports.EntityID', $entityID) // <--- ADD THIS LINE
                ->orderBy('mpa_timelines.Year', 'desc')
                ->get();

            return $indicator;
        });

        // Retrieve existing responses and comments for the current reporting period.
        $existingReportsRaw = DB::table('mpa_reports')
            ->select('IID', 'Response', 'Comments')
            ->where('EntityID', $entityID)
            ->where('ReportingID', $reportingPeriod)
            ->get();

        // Build associative arrays for quick lookup in the view.
        $existingReports  = [];
        $existingComments = [];
        foreach ($existingReportsRaw as $report) {
            $existingReports[$report->IID]  = $report->Response;
            $existingComments[$report->IID] = $report->Comments;
        }

        // Calculate progress metrics.
        $totalIndicators    = $indicators->count();
        $reportedIndicators = count($existingReports);
        $progress           = $totalIndicators > 0 ? ($reportedIndicators / $totalIndicators) * 100 : 0;

        // Set the view identifier.
        $Page = 'IndicatorReporting.ShowIndicators';

        // Pass all collected data to the view.
        return view('scrn', compact(
            'Page',
            'indicators',
            'entity',
            'reportingPeriod',
            'existingReports',
            'existingComments',
            'timeline',
            'progress',
            'totalIndicators',
            'reportedIndicators'
        ));
    }

    /**
     * Submit the indicator reports.
     *
     * Validates the incoming report data, then iterates over each indicator's response.
     * It uses updateOrInsert to save each indicator's response and comment in the mpa_reports table.
     * The ReportedBy field is set using the currently logged-in user's UserID (or defaults to "Unknown").
     * A database transaction is used to ensure atomicity.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitReports(Request $request)
    {
        // Validate that the required fields are provided.
        $validator = Validator::make($request->all(), [
            'entity_id'        => 'required|exists:mpa_entities,EntityID',
            'reporting_period' => 'required|exists:mpa_timelines,ReportingID',
            'responses'        => 'required|array',
            'responses.*'      => 'required',
        ]);

        // If validation fails, redirect back with error messages.
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Retrieve input values.
        $entityID        = $request->input('entity_id');
        $reportingPeriod = $request->input('reporting_period');
        $responses       = $request->input('responses');
        $comments        = $request->input('comments', []);

        // Begin a database transaction.
        DB::beginTransaction();
        try {
            // Iterate over each indicator's response.
            foreach ($responses as $iid => $response) {
                // Update or insert the report record for this indicator.
                DB::table('mpa_reports')->updateOrInsert(
                    [
                        'IID'         => $iid,
                        'EntityID'    => $entityID,
                        'ReportingID' => $reportingPeriod,
                    ],
                    [
                        'Response'   => $response,
                        'Comments'   => $comments[$iid] ?? null,
                        'ReportedBy' => auth()->user()->UserID ?? 'Unknown',
                    ]
                );
            }
            // Commit the transaction.
            DB::commit();
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error.
            DB::rollBack();
            return redirect()->back()->with('error', 'An error occurred while submitting reports. Please try again.');
        }

        // Redirect back with a success message.
        return redirect()->back()->with('success', 'Reports submitted successfully.');
    }

    /**
     * Display the summary of submitted reports.
     *
     * Retrieves summary data by:
     * - Validating that the selected entity exists.
     * - Retrieving the current timeline (reporting period).
     * - Joining mpa_reports with mpa_indicators to obtain report details.
     * - Calculating progress metrics (total and reported indicators).
     * - Passing all summary data to the view.
     *
     * @param  string  $entityID
     * @param  string  $reportingPeriod
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showReportSummary($entityID, $reportingPeriod)
    {
        // Retrieve the selected entity.
        $entity = DB::table('mpa_entities')->where('EntityID', $entityID)->first();
        if (! $entity) {
            return redirect()->route('entity.select')->with('error', 'Invalid entity selected.');
        }

        // Retrieve the reporting period (timeline).
        $timeline = DB::table('mpa_timelines')->where('ReportingID', $reportingPeriod)->first();
        if (! $timeline) {
            return redirect()->route('reporting.period.select')->with('error', 'Invalid reporting period.');
        }

        // Determine allowed reporting types.
        $reportingTypes = $this->getReportingTypes($timeline);

        // Retrieve reports by joining mpa_reports with mpa_indicators.
        $reports = DB::table('mpa_reports')
            ->join('mpa_indicators', 'mpa_reports.IID', '=', 'mpa_indicators.IID')
            ->where('mpa_reports.EntityID', $entityID)
            ->where('mpa_reports.ReportingID', $reportingPeriod)
            ->select('mpa_reports.*', 'mpa_indicators.Indicator', 'mpa_indicators.ExpectedTarget')
            ->get();

        // Count total indicators (based on allowed reporting types) for the entity.
        $totalIndicators = DB::table('mpa_indicators')
            ->where('EntityID', $entityID)
            ->whereIn('ReportingPeriod', $reportingTypes)
            ->count();

        // Calculate the number of reported indicators and overall progress.
        $reportedIndicators = $reports->count();
        $progress           = $totalIndicators > 0 ? ($reportedIndicators / $totalIndicators) * 100 : 0;

        // Set the view identifier.
        $Page = 'IndicatorReporting.Summary';

        // Render the view with summary data.
        return view('scrn', compact('Page', 'reports', 'entity', 'timeline', 'progress', 'totalIndicators', 'reportedIndicators'));
    }

    /**
     * Get the reporting types for a given timeline.
     *
     * Returns an array of allowed reporting types based on the timeline's type.
     * For example, if the timeline is "Bi-Annual" and it's the last period for that year,
     * "Annually" might also be allowed.
     *
     * @param  object  $timeline
     * @return array
     */
    private function getReportingTypes($timeline)
    {
        // Start with the timeline's type.
        $reportingTypes = [$timeline->Type];

        // If timeline is "Bi-Annual" and it's the last bi-annual period of the year, also allow "Annually".
        if ($timeline->Type === 'Bi-Annual' && $this->isLastBiAnnualReporting($timeline)) {
            $reportingTypes[] = 'Annually';
        }

        return $reportingTypes;
    }

    /**
     * Check if the given timeline is the last bi-annual reporting period for its year.
     *
     * Counts later bi-annual periods in the same year. If none exist, returns true.
     *
     * @param  object  $timeline
     * @return bool
     */
    private function isLastBiAnnualReporting($timeline)
    {
        // Only applicable for "Bi-Annual" timelines.
        if ($timeline->Type !== 'Bi-Annual') {
            return false;
        }

        // Count any bi-annual periods later in the same year.
        $laterBiAnnualPeriods = DB::table('mpa_timelines')
            ->where('Year', $timeline->Year)
            ->where('Type', 'Bi-Annual')
            ->where('ReportingID', '>', $timeline->ReportingID)
            ->count();

        // If no later periods exist, this is the last one.
        return $laterBiAnnualPeriods === 0;
    }
}