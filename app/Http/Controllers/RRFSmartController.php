<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// For Excel export using PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RRFSmartController extends Controller
{
    /**
     * showSmartRRFScoreboard
     *
     * Gathers scoreboard, chart, and filter data.
     * Admin users (AccountRole='Admin' and UserType='MPA') may select a specific entity or "All Entities"
     * for a consolidated report. Non-admin users see only their assigned entity.
     */
    public function showSmartRRFScoreboard(Request $request)
    {
        // 1) Identify the currently authenticated user.
        $user = Auth::user();
        if (! $user) {
            abort(403, 'No authenticated user');
        }

        // 2) Determine if the user is an Admin+MPA.
        $isAdminMPA = ($user->AccountRole === 'Admin' && $user->UserType === 'MPA');

        // 3) Build the entities list for the dropdown.
        // (For admin users, the view will add a hardcoded "All Entities" option.)
        $entitiesQuery = DB::table('mpa_entities')->orderBy('Entity');
        if ($isAdminMPA) {
            // Exclude IGAD/ECSA‑HC for aggregator mode.
            $entitiesQuery->whereNotIn('EntityID', ['IGAD', 'ECSA-HC']);
        } else {
            $userEntityID = $user->EntityID;
            if (! $userEntityID) {
                return view('scrn', [
                    'Page'         => 'v2_CRF_Reports.RRFReport',
                    'entities'     => [],
                    'timelines'    => [],
                    'scoreboard'   => [],
                    'domainErrors' => ['User has no EntityID – cannot load scoreboard'],
                ]);
            }
            $entitiesQuery->where('EntityID', $userEntityID);
        }
        $entities = $entitiesQuery->get();

        // 4) Retrieve timelines for the Reporting Period dropdown.
        $timelines = DB::table('mpa_timelines')->orderBy('created_at', 'desc')->get();

        // 5) Gather the scoreboard data.
        $data = $this->collectSmartRRFScoreboardData($request, $entities, $isAdminMPA, $user);

        // 6) Return the view with all required data.
        return view('scrn', [
            'Page'         => 'v2_CRF_Reports.RRFReport',
            'entities'     => $entities,
            'timelines'    => $timelines,
            'timeline'     => $data['timeline'],
            'Year'         => $data['year'],
            'scoreboard'   => $data['scoreboard'],
            'domainErrors' => $data['domainErrors'],
            'charts'       => $data['charts'],
        ]);
    }

    /**
     * exportSmartRRFScoreboardExcel
     *
     * Exports the RRF scoreboard data, including raw responses, to an Excel (.xlsx) file.
     * Enhanced to include more data in both ALL and individual entity modes.
     */
    public function exportSmartRRFScoreboardExcel(Request $request)
    {
        // Get the user and determine if they are an Admin+MPA
        $user = Auth::user();
        if (! $user) {
            return redirect()->back()->withErrors(['No authenticated user']);
        }
        $isAdminMPA = ($user->AccountRole === 'Admin' && $user->UserType === 'MPA');

        // Build the entities list for filtering
        $entitiesQuery = DB::table('mpa_entities')->orderBy('Entity');
        if ($isAdminMPA) {
            // Exclude IGAD/ECSA‑HC for aggregator mode
            $entitiesQuery->whereNotIn('EntityID', ['IGAD', 'ECSA-HC']);
        } else {
            $userEntityID = $user->EntityID;
            if (! $userEntityID) {
                return redirect()->back()->withErrors(['User has no EntityID – cannot export scoreboard']);
            }
            $entitiesQuery->where('EntityID', $userEntityID);
        }
        $entities = $entitiesQuery->get();

        // Get the data using the existing method with all required parameters
        $data            = $this->collectSmartRRFScoreboardData($request, $entities, $isAdminMPA, $user);
        $scoreboard      = $data['scoreboard'] ?? [];
        $timeline        = $data['timeline'];
        $year            = $data['year'];
        $domainErrors    = $data['domainErrors'];
        $effectiveEntity = $request->input('entity_id', 'ALL');
        $isAggregated    = ($effectiveEntity === "ALL");

        // Handle errors and empty data
        if (! empty($domainErrors)) {
            return redirect()->back()->withErrors($domainErrors);
        }
        if (empty($scoreboard)) {
            return redirect()->back()->withErrors(['No RRF scoreboard data found for export.']);
        }

        // Build Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('RRF Scoreboard');

        // Title and Metadata
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'RRF Smart Scoreboard Export');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Timeline and Year
        $sheet->setCellValue('A2', "Timeline: " . ($timeline ? $timeline->ReportName : 'N/A'));
        $sheet->mergeCells('A2:J2');
        $sheet->setCellValue('A3', "Year: " . ($year ?: 'N/A'));
        $sheet->mergeCells('A3:J3');

        // Export Mode
        $sheet->setCellValue('A4', "Export Mode: " . ($isAggregated ? "Aggregated (All Entities)" : "Individual Entity: $effectiveEntity"));
        $sheet->mergeCells('A4:J4');

        // Export Date
        $sheet->setCellValue('A5', "Export Date: " . date('Y-m-d H:i:s'));
        $sheet->mergeCells('A5:J5');

        // Errors, if any
        $headerRow = 7;
        if (! empty($domainErrors)) {
            $sheet->setCellValue('A6', 'Errors: ' . implode(' | ', $domainErrors));
            $sheet->mergeCells('A6:J6');
            $sheet->getStyle('A6:J6')->applyFromArray([
                'font' => ['color' => ['rgb' => 'FF0000']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => 'FFEEEE']],
            ]);
            $headerRow = 8;
        }

        // Header row with expanded columns
        $headers = [
            "Indicator ID",
            "Entity",
            "Indicator Name",
            "Target",
            "Actual",
            "Score %",
            "Status",
            "Raw Responses",
            "Indicator Details",
            "Data Quality",
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $col++;
        }

        // Style the header row
        $sheet->getStyle("A{$headerRow}:J{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => '3C78D8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Auto-size columns
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Data rows
        $rowIndex = $headerRow + 1;
        foreach ($scoreboard as $row) {
            // Basic data
            $sheet->setCellValue("A{$rowIndex}", $row['indicatorID'] ?? '');
            $sheet->setCellValue("B{$rowIndex}", $row['entityID'] ?? '');
            $sheet->setCellValue("C{$rowIndex}", $row['indicatorName'] ?? '');

            // Target and Actual values
            $targetValue = $row['targetValue'] ?? '';
            $actualValue = $row['actualValue'] ?? '';
            $sheet->setCellValue("D{$rowIndex}", $targetValue);
            $sheet->setCellValue("E{$rowIndex}", $actualValue);

            // Score and Status
            $scorePercent = isset($row['scorePercent']) && $row['scorePercent'] !== 'N/A' ? $row['scorePercent'] : '';
            $quickStatus  = $row['quickStatus'] ?? '';
            $sheet->setCellValue("F{$rowIndex}", $scorePercent);
            $sheet->setCellValue("G{$rowIndex}", $quickStatus);

            // Process rawResponses with enhanced formatting
            $rawResponses = $this->formatRawResponsesForExcel($row);
            $sheet->setCellValue("H{$rowIndex}", $rawResponses);

            // Fetch and add indicator details
            $indicatorDetails = $this->getIndicatorDetailsForExcel($row['indicatorID'] ?? '');
            $sheet->setCellValue("I{$rowIndex}", $indicatorDetails);

            // Data quality information
            $dataQuality = '';
            if (! empty($row['errors'])) {
                $dataQuality = "ISSUES DETECTED:\n" . implode("\n", $row['errors']);
            } else {
                $dataQuality = "No issues detected";

                // Add validation checks
                if ($isAggregated) {
                    if (is_numeric($targetValue) && is_numeric($actualValue) && $targetValue > 0) {
                        $ratio = $actualValue / $targetValue;
                        $dataQuality .= "\nActual/Target Ratio: " . round($ratio * 100, 1) . "%";
                    }
                }

                // Add completeness info for individual mode
                if (! $isAggregated && isset($row['rawResponses']) && is_array($row['rawResponses'])) {
                    if (isset($row['rawResponses']['expected']) && isset($row['rawResponses']['reported'])) {
                        $expectedCount = count($row['rawResponses']['expected']);
                        $reportedCount = count($row['rawResponses']['reported']);
                        if ($expectedCount > 0) {
                            $completeness = round(($reportedCount / $expectedCount) * 100, 1);
                            $dataQuality .= "\nReporting Completeness: {$completeness}%";
                        }
                    }
                }
            }
            $sheet->setCellValue("J{$rowIndex}", $dataQuality);

            // Apply cell formatting for status
            $this->colorStatusCell($sheet, "G{$rowIndex}", $quickStatus);

            // Highlight rows with errors
            if (! empty($row['errors'])) {
                $sheet->getStyle("A{$rowIndex}:J{$rowIndex}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color'    => ['rgb' => 'FFDDDD'],
                    ],
                ]);
            }

            // Apply text wrapping for long content
            $sheet->getStyle("C{$rowIndex}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("H{$rowIndex}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("I{$rowIndex}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("J{$rowIndex}")->getAlignment()->setWrapText(true);

            $rowIndex++;
        }

        // Apply borders to data region
        $lastDataRow = $rowIndex - 1;
        $sheet->getStyle("A{$headerRow}:J{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFAAAAAA'],
                ],
            ],
        ]);

        // Add summary statistics sheet
        $this->addSummaryStatisticsSheet($spreadsheet, $scoreboard, $isAggregated);

        // Add metadata sheet with export details
        $this->addMetadataSheet($spreadsheet, $request, $timeline, $year, $effectiveEntity);

        // Freeze panes for better navigation
        $sheet->freezePane("A" . ($headerRow + 1));

        // Output Excel file
        $filename = 'RRF_Scoreboard_Export_' . ($isAggregated ? 'All' : $effectiveEntity) . '_' . date('Ymd_His') . '.xlsx';
        $writer   = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $excelData = ob_get_clean();

        return response($excelData, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0, must-revalidate, no-cache, no-store',
        ]);
    }

    /**
     * collectSmartRRFScoreboardData
     *
     * Gathers scoreboard data, computes chart data, and enforces strict internal data validation.
     * When a single entity is selected, scoring and target comparisons are skipped,
     * and raw responses are returned. Completeness is computed instead.
     *
     * @param Request $request
     * @param mixed   $entities   Allowed entities (for filtering)
     * @param bool    $isAdminMPA
     * @param mixed   $user
     * @return array
     */
    private function collectSmartRRFScoreboardData(Request $request, $entities = null, $isAdminMPA = false, $user = null)
    {
        $domainErrors = [];
        $scoreboard   = [];

        // 1) Get user.
        if (! $user) {
            $user = Auth::user();
        }
        if (! $user) {
            $domainErrors[] = "No authenticated user found.";
            return [
                'timeline'     => null,
                'year'         => null,
                'scoreboard'   => [],
                'domainErrors' => $domainErrors,
                'charts'       => [],
            ];
        }

                                                         // 2) Determine the mode.
                                                         // If request('entity_id') is "ALL" then we are in aggregated (consolidated) mode.
                                                         // Otherwise, if a specific entity is selected, then we are in individual mode.
        $requestedEntity = $request->input('entity_id'); // either specific entity or "ALL"
        $isAggregated    = ($requestedEntity === "ALL");

        // 3) Get filter parameters.
        $timelineID   = $request->input('reporting_period');
        $selectedYear = $request->input('year');
        $timeline     = null;
        $isLast       = false;
        if (! empty($timelineID)) {
            $timeline = DB::table('mpa_timelines')
                ->where('ReportingID', $timelineID)
                ->first();
            if ($timeline) {
                $isLast = ($timeline->LastBiAnnual == 1);
            } else {
                $domainErrors[] = "Invalid timeline: {$timelineID}";
            }
        }

        // 4) Determine final year.
        $year = $timeline ? $timeline->Year : ($selectedYear ?: null);

        // 5) Determine target column (only used in aggregated mode).
        $targetColumn = $this->mapYearToTargetColumn($year, $isLast);

        // 6) Build base query for RRF indicators.
        // In both modes we always fetch the global RRF indicators.
        $indQuery = DB::table('mpa_indicators')->where('PrimaryCategory', 'RRF');
        // For aggregated mode we may want to restrict indicators further by allowed entities.
        // For individual mode we want all global indicators regardless of the indicator's EntityID.
        if ($isAggregated) {
            // Aggregated mode.
            if ($isAdminMPA) {
                // Optionally: if an admin selects a specific entity (even if not "ALL"), you may decide
                // to filter by that entity. But per our new requirements, in individual mode we ignore target/scoring.
                // So here, if $requestedEntity is "ALL", then effectiveEntity is "ALL".
                $effectiveEntity = "ALL";
            } else {
                $effectiveEntity = $user->EntityID;
                $indQuery->where('EntityID', $effectiveEntity);
            }
        } else {
            // Individual mode: show all global indicators.
            $effectiveEntity = $requestedEntity;
            // Do NOT filter by EntityID on indicators.
        }

        $indicators = $indQuery->get();
        if ($indicators->isEmpty()) {
            $domainErrors[] = "No RRF indicators found for the current scenario.";
            return [
                'timeline'     => $timeline,
                'year'         => $year,
                'scoreboard'   => [],
                'domainErrors' => $domainErrors,
                'charts'       => [],
            ];
        }

        // 7) Process each indicator.
        foreach ($indicators as $indicator) {
            $rowErrors = [];
            if (! empty($timelineID) && $this->checkDuplicates($indicator->IID, $indicator->EntityID, $timelineID)) {
                $rowErrors[] = "Duplicate data for IID={$indicator->IID}, Entity={$indicator->EntityID}, timeline={$timelineID}";
            }
            // Branch into individual vs. aggregated processing.
            if ($effectiveEntity !== "ALL") {
                // ----- INDIVIDUAL MODE: Show what the single entity reported.
                $report = DB::table('mpa_reports')
                    ->where('IID', $indicator->IID)
                    ->where('EntityID', $effectiveEntity)
                    ->where('ReportingID', $timelineID)
                    ->first();
                $rawResponses = [
                    'expected' => [$effectiveEntity],
                    'reported' => [],
                ];
                $actualValue = null;
                if ($report) {
                    $rawResponses['reported'][] = [
                        'Entity'   => $effectiveEntity,
                        'Response' => $report->Response,
                    ];
                    $actualValue = $report->Response;
                }
                $status       = ($actualValue !== null ? 'Reported' : 'Not Reported');
                $scoreboard[] = [
                    'indicatorID'   => $indicator->IID,
                    'entityID'      => $effectiveEntity,
                    'indicatorName' => $indicator->Indicator,
                    'targetValue'   => 'N/A',
                    'actualValue'   => $actualValue,
                    'scorePercent'  => 'N/A',
                    'quickStatus'   => $status,
                    'errors'        => $rowErrors,
                    'rawResponses'  => $rawResponses,
                ];
            } else {
                // ----- AGGREGATED MODE: Use existing scoring logic.
                $row          = $this->scoreOneIndicator($indicator, $timelineID, $targetColumn, $rowErrors, $effectiveEntity);
                $scoreboard[] = $row;
            }
        }

        // 8) In individual mode, compute reporting completeness.
        if ($effectiveEntity !== "ALL") {
            $total    = count($indicators);
            $reported = 0;
            foreach ($scoreboard as $row) {
                if ($row['actualValue'] !== null) {
                    $reported++;
                }
            }
            // Add an extra "completeness" entry to the charts data.
            $charts = [
                'reportingCompleteness' => [
                    'type' => 'donut',
                    'data' => [
                        'labels'   => ['Reported', 'Not Reported'],
                        'datasets' => [
                            [
                                'data' => [$reported, $total - $reported],
                            ],
                        ],
                    ],
                ],
            ];
        } else {
            // Aggregated mode uses the existing charts computation.
            $charts = $this->computeRRFCharts($scoreboard);
        }

        // 9) Run internal self-test of scoreboard.
        $testErrors   = $this->selfTestScoreboard($scoreboard);
        $domainErrors = array_merge($domainErrors, $testErrors);

        // 10) Return the collected data.
        return [
            'timeline'     => $timeline,
            'year'         => $year,
            'scoreboard'   => $scoreboard,
            'domainErrors' => $domainErrors,
            'charts'       => $charts,
        ];
    }

    /**
     * scoreOneIndicator
     *
     * Retrieves actual value(s) from mpa_reports and target from the indicator,
     * aggregates responses (if in aggregated mode), converts values,
     * and applies scoring logic.
     * In individual mode (when $effectiveEntity is not "ALL"), we simply return the raw response.
     *
     * @param object $indicator
     * @param string $timelineID
     * @param string $targetColumn
     * @param array  $errors
     * @param string $effectiveEntity Either a specific EntityID or "ALL" for aggregated mode.
     * @return array
     */
    private function scoreOneIndicator($indicator, $timelineID, $targetColumn, array $errors, $effectiveEntity)
    {
        $iid           = $indicator->IID;
        $entityID      = $effectiveEntity;
        $indicatorName = $indicator->Indicator;
        $responseType  = $indicator->ResponseType ?? 'Text';
        $scoringLogic  = $indicator->meta_scoring_logic ?? 'none';
        $targetFormat  = $indicator->meta_target_format ?? 'number';
        $conversion    = $indicator->meta_conversion_method ?? 'none';

        $onTrackThreshold  = property_exists($indicator, 'custom_ontrack_threshold') ? floatval($indicator->custom_ontrack_threshold) : 80.0;
        $exceededThreshold = property_exists($indicator, 'custom_exceeded_threshold') ? floatval($indicator->custom_exceeded_threshold) : 100.0;
        $rangeMin          = property_exists($indicator, 'meta_min') ? $indicator->meta_min : null;
        $rangeMax          = property_exists($indicator, 'meta_max') ? $indicator->meta_max : null;

        // Retrieve target value from the indicator (only used in aggregated mode).
        $targetValue = null;
        if ($targetColumn && property_exists($indicator, $targetColumn)) {
            $targetValue = $indicator->{$targetColumn};
        }

        // Retrieve actual responses.
        $rawResponses = [];
        $actualValue  = null;
        if (! empty($timelineID)) {
            if ($effectiveEntity === "ALL") {
                // Aggregated mode: get reports for all allowed entities.
                $allowedEntities = DB::table('mpa_entities')
                    ->whereNotIn('EntityID', ['IGAD', 'ECSA-HC'])
                    ->pluck('EntityID')
                    ->toArray();
                $reports = DB::table('mpa_reports')
                    ->where('IID', $iid)
                    ->whereIn('EntityID', $allowedEntities)
                    ->where('ReportingID', $timelineID)
                    ->get();
                // Build structured rawResponses.
                $rawResponses = [
                    'expected' => $allowedEntities,
                    'reported' => [],
                ];
                foreach ($reports as $r) {
                    $rawResponses['reported'][] = [
                        'Entity'   => $r->EntityID,
                        'Response' => $r->Response,
                    ];
                }
                $actualValue = $this->aggregateResponses($reports, $responseType, $conversion, $errors, $rawResponses);
            } else {
                // Individual mode.
                $report = DB::table('mpa_reports')
                    ->where('IID', $iid)
                    ->where('EntityID', $effectiveEntity)
                    ->where('ReportingID', $timelineID)
                    ->first();
                $rawResponses = [
                    'expected' => [$effectiveEntity],
                    'reported' => [],
                ];
                if ($report) {
                    $rawResponses['reported'][] = [
                        'Entity'   => $effectiveEntity,
                        'Response' => $report->Response,
                    ];
                    $actualValue = $report->Response;
                }
            }
        }

        // In aggregated mode, perform conversion and scoring.
        if ($effectiveEntity === "ALL") {
            // For Yes/No indicators that have been aggregated to a percentage, treat the actual value as a Percentage type
            $effectiveResponseType = $responseType;
            if (in_array($responseType, ['Yes/No', 'Boolean']) && is_numeric($actualValue)) {
                $effectiveResponseType = 'Percentage';
            }
            $parsedActual = $this->convertAndValidateResponse($actualValue, $effectiveResponseType, $conversion, $errors);
            $parsedTarget = $this->convertAndValidateResponse($targetValue, $targetFormat, 'none', $errors, true);
            if (is_null($parsedActual) && is_null($parsedTarget)) {
                return [
                    'indicatorID'   => $iid,
                    'entityID'      => $effectiveEntity,
                    'indicatorName' => $indicatorName,
                    'targetValue'   => $targetValue,
                    'actualValue'   => $actualValue,
                    'scorePercent'  => null,
                    'quickStatus'   => 'No Data',
                    'errors'        => $errors,
                    'rawResponses'  => $rawResponses,
                ];
            }
            // For Yes/No indicators that have been aggregated to a percentage, use direct comparison scoring
            if (in_array($responseType, ['Yes/No', 'Boolean']) && is_numeric($actualValue) && is_numeric($parsedTarget)) {
                // Direct comparison for percentage-based indicators derived from Yes/No
                if ($parsedTarget == 0) {
                    // If target is 0, any positive actual value exceeds the target
                    $scorePercent = $parsedActual > 0 ? 200.0 : 100.0;
                    $quickStatus  = $parsedActual > 0 ? 'Exceeded' : 'On Track';
                } else {
                    // Calculate how close the actual is to the target as a percentage
                    $scorePercent = ($parsedActual / $parsedTarget) * 100.0;

                    // Cap the score at 200%
                    if ($scorePercent > 200) {
                        $scorePercent = 200.0;
                    }

                    // Determine status based on thresholds
                    if ($scorePercent >= $exceededThreshold) {
                        $quickStatus = 'Exceeded';
                    } elseif ($scorePercent >= $onTrackThreshold) {
                        $quickStatus = 'On Track';
                    } else {
                        $quickStatus = 'Behind';
                    }
                }
            } else {
                // Use standard scoring logic for other indicator types
                list($scorePercent, $quickStatus) = $this->applyScoring(
                    $scoringLogic,
                    $parsedActual,
                    $parsedTarget,
                    $rangeMin,
                    $rangeMax,
                    $onTrackThreshold,
                    $exceededThreshold,
                    $errors
                );
            }
            return [
                'indicatorID'   => $iid,
                'entityID'      => $effectiveEntity,
                'indicatorName' => $indicatorName,
                'targetValue'   => $targetValue,
                'actualValue'   => $actualValue,
                'scorePercent'  => is_null($scorePercent) ? null : round($scorePercent, 2),
                'quickStatus'   => $quickStatus,
                'errors'        => $errors,
                'rawResponses'  => $rawResponses,
            ];
        } else {
            // Individual mode: simply return the raw response; no scoring.
            $status = ($actualValue !== null ? 'Reported' : 'Not Reported');
            return [
                'indicatorID'   => $iid,
                'entityID'      => $effectiveEntity,
                'indicatorName' => $indicatorName,
                'targetValue'   => 'N/A',
                'actualValue'   => $actualValue,
                'scorePercent'  => 'N/A',
                'quickStatus'   => $status,
                'errors'        => $errors,
                'rawResponses'  => $rawResponses,
            ];
        }
    }

    /**
     * aggregateResponses
     *
     * An AI-like method to intelligently aggregate multiple responses.
     * - For Number type: converts and then sums the values to provide a cumulative total.
     * - For Percentage, Yes/No, and Boolean types: converts, removes outliers, and averages.
     * - For Text: returns the mode (most common value).
     * - Otherwise, returns the first valid response.
     *
     * @param \Illuminate\Support\Collection $reports
     * @param string $responseType
     * @param string $conversion
     * @param array &$errors
     * @param array $rawResponses Optional array containing expected and reported entities
     * @return mixed
     */
    private function aggregateResponses($reports, $responseType, $conversion, array &$errors, $rawResponses = null)
    {
        if ($reports->isEmpty()) {
            return null;
        }

        // For Number indicators, compute a cumulative sum.
        if ($responseType === 'Number') {
            $values = [];
            foreach ($reports as $r) {
                $converted = $this->convertAndValidateResponse($r->Response, $responseType, $conversion, $errors);
                if (! is_null($converted)) {
                    $values[] = $converted;
                }
            }
            if (empty($values)) {
                return null;
            }
            $sum = array_sum($values);
            return $sum;
        }
        // For Yes/No and Boolean responses, calculate percentage of "Yes" responses out of total expected entities
        // Note: This returns a percentage value (0-100  responses out of total expected entities
        // Note: This returns a percentage value (0-100), not a Yes/No value
        elseif (in_array($responseType, ['Yes/No', 'Boolean'])) {
            $yesCount      = 0;
            $totalExpected = 0;

            // If we have raw responses with expected entities, use that for total expected
            if (isset($rawResponses) && is_array($rawResponses) && isset($rawResponses['expected'])) {
                $totalExpected = count($rawResponses['expected']);
            }

            // Count the number of "Yes" responses
            foreach ($reports as $r) {
                $response = strtolower(trim($r->Response));
                if (in_array($response, ['yes', '1', 'true'])) {
                    $yesCount++;
                }
            }

            // Calculate percentage: (Yes count / Total expected) * 100
            if ($totalExpected > 0) {
                return ($yesCount / $totalExpected) * 100;
            }
            return 0;
        }
        // For Percentage responses, use outlier-filtered averaging.
        elseif ($responseType === 'Percentage') {
            $values = [];
            foreach ($reports as $r) {
                $converted = $this->convertAndValidateResponse($r->Response, $responseType, $conversion, $errors);
                if (! is_null($converted)) {
                    $values[] = $converted;
                }
            }
            if (empty($values)) {
                return null;
            }
            $mean     = array_sum($values) / count($values);
            $variance = 0;
            foreach ($values as $v) {
                $variance += pow($v - $mean, 2);
            }
            $variance /= count($values);
            $stdDev   = sqrt($variance);
            $filtered = array_filter($values, function ($v) use ($mean, $stdDev) {
                return abs($v - $mean) <= (2 * $stdDev);
            });
            $finalValues = ! empty($filtered) ? $filtered : $values;
            $avg         = array_sum($finalValues) / count($finalValues);
            return $avg;
        }
        // For Text responses, return the mode.
        elseif ($responseType === 'Text') {
            $counts = [];
            foreach ($reports as $r) {
                $text = trim($r->Response);
                if ($text !== '') {
                    if (! isset($counts[$text])) {
                        $counts[$text] = 0;
                    }
                    $counts[$text]++;
                }
            }
            if (! empty($counts)) {
                arsort($counts);
                return key($counts);
            }
            return null;
        }
        // For any other type, return the first valid converted response.
        else {
            foreach ($reports as $r) {
                $converted = $this->convertAndValidateResponse($r->Response, $responseType, $conversion, $errors);
                if (! is_null($converted)) {
                    return $converted;
                }
            }
            return null;
        }
    }

    /**
     * convertAndValidateResponse
     *
     * Converts the input value based on the expected type and conversion method.
     */
    private function convertAndValidateResponse($value, $type, $conversion, array &$errors, $isTarget = false)
    {
        if (is_null($value) || $value === '') {
            return null;
        }
        $valStr = trim((string) $value);
        // Perform conversion.
        if ($conversion === 'strip_percentage') {
            $valStr = str_replace('%', '', $valStr);
        } elseif ($conversion === 'fraction_to_decimal') {
            if (is_numeric($valStr)) {
                $floatVal = floatval($valStr);
                $valStr   = (string) ($floatVal * 100.0);
            }
        }
        // Parsing.
        if ($type === 'Yes/No' || $type === 'Boolean') {
            $low = strtolower($valStr);
            if (in_array($low, ['yes', '1', 'true'])) {
                return 1.0;
            } elseif (in_array($low, ['no', '0', 'false'])) {
                return 0.0;
            } else {
                $errors[] = "Invalid yes/no value: {$value}";
                return null;
            }
        } elseif ($type === 'Number' || $type === 'Percentage' || $isTarget) {
            if (! is_numeric($valStr)) {
                $errors[] = "Non-numeric value: {$value}";
                return null;
            }
            $floatVal = floatval($valStr);
            if ($type === 'Percentage' && ($floatVal < 0 || $floatVal > 100)) {
                $errors[] = "Percentage out of range: {$floatVal}";
            }
            return $floatVal;
        } else {
            return $valStr;
        }
    }

    /**
     * applyScoring
     *
     * Applies the scoring logic based on the specified method and thresholds.
     */
    private function applyScoring($scoringLogic, $actual, $target,
        $rangeMin, $rangeMax,
        $onTrackThreshold, $exceededThreshold,
        array &$errors) {
        if (is_null($actual)) {
            return [null, 'No Data'];
        }
        if (is_null($target) && $scoringLogic !== 'none') {
            return [null, 'No Target'];
        }
        if ($scoringLogic === 'none') {
            return [null, 'Informational'];
        }
        $scorePercent = null;
        switch ($scoringLogic) {
            case 'greater_is_better':
                if ($target == 0) {
                    // If target is 0, any positive actual value exceeds the target
                    $scorePercent = $actual > 0 ? 200.0 : 100.0;
                } else {
                    $scorePercent = ($actual / $target) * 100.0;
                }
                break;
            case 'less_is_better':
                if ($actual == 0) {
                    $errors[] = "Actual is 0; cannot compute ratio for 'less_is_better'";
                    return [null, 'Data Error'];
                }
                $scorePercent = ($target / $actual) * 100.0;
                break;
            case 'exact_match':
                $scorePercent = ($actual == $target) ? 100.0 : 0.0;
                break;
            case 'range':
                if (! is_null($rangeMin) && ! is_null($rangeMax)) {
                    if ($actual < $rangeMin) {
                        $scorePercent = 0.0;
                    } elseif ($actual > $rangeMax) {
                        $scorePercent = 120.0;
                    } else {
                        $scorePercent = 100.0;
                    }
                } else {
                    $scorePercent = ($actual == $target) ? 100.0 : 0.0;
                }
                break;
            default:
                $errors[] = "Unknown scoring logic: {$scoringLogic}";
                return [null, 'Data Error'];
        }
        if (! is_null($scorePercent)) {
            if ($scorePercent < 0) {
                $scorePercent = 0.0;
            }
            if ($scorePercent > 200) {
                $scorePercent = 200.0;
            }
        }
        if (is_null($scorePercent)) {
            return [null, 'No Data'];
        } elseif ($scorePercent >= $exceededThreshold) {
            return [$scorePercent, 'Exceeded'];
        } elseif ($scorePercent >= $onTrackThreshold) {
            return [$scorePercent, 'On Track'];
        } else {
            return [$scorePercent, 'Behind'];
        }
    }

    /**
     * mapYearToTargetColumn
     *
     * Maps the provided year (and the isLast flag) to the appropriate target column.
     */
    private function mapYearToTargetColumn($year, $isLast)
    {
        if (! $year) {
            return null;
        }
        switch ($year) {
            case '2023':
                return 'BaselinePAD2023';
            case '2024':
                return 'TargetYearOne2024';
            case '2025':
                return 'TargetYearTwo2025';
            case '2026':
                return 'TargetYearThree2026';
            case '2027':
                return 'TargetYearFour2027';
            case '2028':
                return 'TargetYearFive2028';
            case '2029':
                return 'TargetYearSix2029';
            case '2030':
                return 'TargetYearSeven2030';
            default:
                return null;
        }
    }

    /**
     * checkDuplicates
     *
     * Checks whether more than one report exists for the same (IID, EntityID, ReportingID).
     */
    private function checkDuplicates($iid, $entityID, $timelineID)
    {
        $count = DB::table('mpa_reports')
            ->where('IID', $iid)
            ->where('EntityID', $entityID)
            ->where('ReportingID', $timelineID)
            ->count();
        return ($count > 1);
    }

    /**
     * selfTestScoreboard
     *
     * Validates that each row's quickStatus is consistent with its scorePercent.
     */
    private function selfTestScoreboard(array $scoreboard)
    {
        $errors = [];
        foreach ($scoreboard as $r) {
            $status = strtolower($r['quickStatus'] ?? '');
            $pct    = $r['scorePercent'] ?? null;
            if (is_null($pct) || $pct === 'N/A') {
                continue;
            }
            if ($status === 'exceeded' && $pct < 100) {
                $errors[] = "Self-test fail: status 'Exceeded' but score={$pct} (<100) for IID={$r['indicatorID']}";
            }
            if ($status === 'on track' && ($pct < 80 || $pct >= 100)) {
                $errors[] = "Self-test fail: status 'On Track' but score={$pct} not in [80..100) for IID={$r['indicatorID']}";
            }
            if ($status === 'behind' && $pct >= 80) {
                $errors[] = "Self-test fail: status 'Behind' but score={$pct} >=80 for IID={$r['indicatorID']}";
            }
        }
        return $errors;
    }

    /**
     * computeRRFCharts
     *
     * Builds chart data definitions for the front-end.
     */
    private function computeRRFCharts(array $scoreboard)
    {
        // Status Distribution Chart.
        $statusCounts = [
            'Exceeded'      => 0,
            'On Track'      => 0,
            'Behind'        => 0,
            'No Data'       => 0,
            'No Target'     => 0,
            'Informational' => 0,
            'Data Error'    => 0,
        ];
        foreach ($scoreboard as $r) {
            $st = $r['quickStatus'] ?? 'No Data';
            if (! isset($statusCounts[$st])) {
                $statusCounts[$st] = 0;
            }
            $statusCounts[$st]++;
        }
        $distLabels = array_keys($statusCounts);
        $distData   = array_values($statusCounts);

        $chartStatusDistribution = [
            'type' => 'bar',
            'data' => [
                'labels'   => $distLabels,
                'datasets' => [
                    [
                        'label' => 'Indicator Status Distribution',
                        'data'  => $distData,
                    ],
                ],
            ],
        ];

        // Actual vs Target Chart (only for numeric indicators in aggregated mode).
        $indicatorLabels = [];
        $actualValues    = [];
        $targetValues    = [];
        foreach ($scoreboard as $r) {
            $name    = $r['indicatorID'] ?? '??';
            $indName = mb_substr($r['indicatorName'] ?? '', 0, 30);
            $label   = $name . '-' . $indName;
            $act     = $r['actualValue'] ?? null;
            $targ    = $r['targetValue'] ?? null;
            if (is_numeric($act) && is_numeric($targ)) {
                $indicatorLabels[] = $label;
                $actualValues[]    = floatval($act);
                $targetValues[]    = floatval($targ);
            }
        }
        $chartActualVsTarget = [
            'type' => 'bar',
            'data' => [
                'labels'   => $indicatorLabels,
                'datasets' => [
                    [
                        'label' => 'Actual',
                        'data'  => $actualValues,
                    ],
                    [
                        'label' => 'Target',
                        'data'  => $targetValues,
                    ],
                ],
            ],
        ];

        return [
            'statusDistribution' => $chartStatusDistribution,
            'actualVsTarget'     => $chartActualVsTarget,
        ];
    }

    /**
     * colorStatusCell
     *
     * Applies Excel cell formatting based on the quickStatus value.
     */
    private function colorStatusCell($sheet, $cellCoord, $status)
    {
        $fillColor = '999999'; // Default gray
        $fontColor = 'FFFFFF'; // White

        switch (strtolower($status)) {
            case 'exceeded':
                $fillColor = '219653'; // Green
                break;
            case 'on track':
                $fillColor = '2F80ED'; // Blue
                break;
            case 'behind':
                $fillColor = 'F2994A'; // Orange
                break;
            case 'informational':
                $fillColor = '9B51E0'; // Purple
                break;
            case 'no data':
                $fillColor = '828282'; // Gray
                break;
            case 'no target':
                $fillColor = 'BDBDBD'; // Light gray
                break;
            case 'data error':
                $fillColor = 'EB5757'; // Red
                break;
            case 'reported':
                $fillColor = '27AE60'; // Green
                break;
            case 'not reported':
                $fillColor = 'E0E0E0'; // Light gray
                $fontColor = '333333'; // Dark gray
                break;
        }

        $sheet->getStyle($cellCoord)->applyFromArray([
            'fill'      => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['rgb' => $fillColor],
            ],
            'font'      => [
                'color' => ['rgb' => $fontColor],
                'bold'  => true,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    /**
     * formatRawResponsesForExcel
     *
     * Formats raw responses data for Excel export with enhanced details.
     */
    private function formatRawResponsesForExcel($row)
    {
        $rawResponses = '';
        if (isset($row['rawResponses']) && is_array($row['rawResponses'])) {
            if (isset($row['rawResponses']['expected']) && isset($row['rawResponses']['reported'])) {
                // Format expected entities
                $expected     = implode(", ", $row['rawResponses']['expected']);
                $rawResponses = "Expected Entities:\n" . $expected . "\n\n";

                // Format reported responses with more detail
                $rawResponses .= "Reported Responses:\n";
                if (count($row['rawResponses']['reported']) > 0) {
                    foreach ($row['rawResponses']['reported'] as $rep) {
                        $rawResponses .= "• {$rep['Entity']}: {$rep['Response']}\n";
                    }
                } else {
                    $rawResponses .= "No responses recorded\n";
                }

                // Add reporting statistics
                $expectedCount = count($row['rawResponses']['expected']);
                $reportedCount = count($row['rawResponses']['reported']);
                $rawResponses .= "\nReporting Statistics:\n";
                $rawResponses .= "• Total Expected: {$expectedCount}\n";
                $rawResponses .= "• Total Reported: {$reportedCount}\n";

                if ($expectedCount > 0) {
                    $completeness = round(($reportedCount / $expectedCount) * 100, 1);
                    $rawResponses .= "• Completeness: {$completeness}%";
                }
            } else {
                // Handle simple array of responses
                $rawResponses = implode("\n", $row['rawResponses']);
            }
        } else {
            $rawResponses = "No raw response data available";
        }

        return $rawResponses;
    }

    /**
     * getIndicatorDetailsForExcel
     *
     * Retrieves comprehensive indicator details for Excel export.
     */
    private function getIndicatorDetailsForExcel($indicatorId)
    {
        $indicatorDetails = '';
        $indicator        = DB::table('mpa_indicators')->where('IID', $indicatorId)->first();

        if ($indicator) {
            $details = [];

            // Basic indicator information
            if (! empty($indicator->ResponseType)) {
                $details[] = "Response Type: {$indicator->ResponseType}";
            }

            if (! empty($indicator->PrimaryCategory)) {
                $details[] = "Category: {$indicator->PrimaryCategory}";
            }

            // Scoring and calculation information
            if (! empty($indicator->meta_scoring_logic)) {
                $details[] = "Scoring Logic: {$indicator->meta_scoring_logic}";
            }

            if (! empty($indicator->meta_conversion_method)) {
                $details[] = "Conversion Method: {$indicator->meta_conversion_method}";
            }

            if (property_exists($indicator, 'custom_ontrack_threshold')) {
                $details[] = "On Track Threshold: {$indicator->custom_ontrack_threshold}%";
            }

            if (property_exists($indicator, 'custom_exceeded_threshold')) {
                $details[] = "Exceeded Threshold: {$indicator->custom_exceeded_threshold}%";
            }

            // Definition and metadata
            if (! empty($indicator->IndicatorDefinition)) {
                $details[] = "Definition: {$indicator->IndicatorDefinition}";
            }

            if (! empty($indicator->IndicatorQuestion)) {
                $details[] = "Question: {$indicator->IndicatorQuestion}";
            }

            if (! empty($indicator->SourceOfData)) {
                $details[] = "Data Source: {$indicator->SourceOfData}";
            }

            if (! empty($indicator->RemarksComments)) {
                $details[] = "Remarks: {$indicator->RemarksComments}";
            }

            // Target information for different years
            $targetColumns = [
                'BaselinePAD2023', 'TargetYearOne2024', 'TargetYearTwo2025',
                'TargetYearThree2026', 'TargetYearFour2027', 'TargetYearFive2028',
                'TargetYearSix2029', 'TargetYearSeven2030',
            ];

            $targetInfo = [];
            foreach ($targetColumns as $column) {
                if (property_exists($indicator, $column) && ! empty($indicator->{$column})) {
                    $year         = substr($column, -4);
                    $targetInfo[] = "{$year}: {$indicator->{$column}}";
                }
            }

            if (! empty($targetInfo)) {
                $details[] = "Targets by Year: " . implode(", ", $targetInfo);
            }

            $indicatorDetails = implode("\n", $details);
        } else {
            $indicatorDetails = "No indicator details available";
        }

        return $indicatorDetails;
    }

    /**
     * addSummaryStatisticsSheet
     *
     * Adds a summary statistics sheet to the Excel workbook.
     */
    private function addSummaryStatisticsSheet($spreadsheet, $scoreboard, $isAggregated)
    {
        $statsSheet = $spreadsheet->createSheet();
        $statsSheet->setTitle('Summary Statistics');

        // Add title
        $statsSheet->setCellValue('A1', 'RRF Scoreboard Summary Statistics');
        $statsSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
        ]);
        $statsSheet->mergeCells('A1:C1');

        // Status Distribution
        $statsSheet->setCellValue('A3', 'Status Distribution');
        $statsSheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
        ]);

        $statsSheet->setCellValue('A4', 'Status');
        $statsSheet->setCellValue('B4', 'Count');
        $statsSheet->setCellValue('C4', 'Percentage');
        $statsSheet->getStyle('A4:C4')->applyFromArray([
            'font'    => ['bold' => true],
            'fill'    => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['rgb' => 'E0E0E0'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Calculate status counts
        $statusCounts    = [];
        $totalIndicators = count($scoreboard);

        foreach ($scoreboard as $row) {
            $status = $row['quickStatus'] ?? 'Unknown';
            if (! isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
            $statusCounts[$status]++;
        }

        // Add status counts to sheet
        $statsRow = 5;
        foreach ($statusCounts as $status => $count) {
            $percentage = ($totalIndicators > 0) ? round(($count / $totalIndicators) * 100, 1) : 0;

            $statsSheet->setCellValue("A{$statsRow}", $status);
            $statsSheet->setCellValue("B{$statsRow}", $count);
            $statsSheet->setCellValue("C{$statsRow}", "{$percentage}%");

            // Apply status color to the row
            $this->colorStatusCell($statsSheet, "A{$statsRow}", $status);

            $statsRow++;
        }

        // Add total row
        $statsSheet->setCellValue("A{$statsRow}", "Total");
        $statsSheet->setCellValue("B{$statsRow}", $totalIndicators);
        $statsSheet->setCellValue("C{$statsRow}", "100%");
        $statsSheet->getStyle("A{$statsRow}:C{$statsRow}")->applyFromArray([
            'font'    => ['bold' => true],
            'borders' => [
                'top' => [
                    'borderStyle' => Border::BORDER_DOUBLE,
                    'color'       => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Add performance metrics section (for aggregated mode)
        if ($isAggregated) {
            $statsRow += 2;
            $statsSheet->setCellValue("A{$statsRow}", "Performance Metrics");
            $statsSheet->getStyle("A{$statsRow}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 12],
            ]);

            $statsRow++;
            $statsSheet->setCellValue("A{$statsRow}", "Metric");
            $statsSheet->setCellValue("B{$statsRow}", "Value");
            $statsSheet->getStyle("A{$statsRow}:B{$statsRow}")->applyFromArray([
                'font'    => ['bold' => true],
                'fill'    => [
                    'fillType' => Fill::FILL_SOLID,
                    'color'    => ['rgb' => 'E0E0E0'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FF000000'],
                    ],
                ],
            ]);

            // Calculate performance metrics
            $exceededCount = $statusCounts['Exceeded'] ?? 0;
            $onTrackCount  = $statusCounts['On Track'] ?? 0;
            $behindCount   = $statusCounts['Behind'] ?? 0;

            $scoredIndicators     = $exceededCount + $onTrackCount + $behindCount;
            $performingIndicators = $exceededCount + $onTrackCount;

            $statsRow++;
            $statsSheet->setCellValue("A{$statsRow}", "Total Scored Indicators");
            $statsSheet->setCellValue("B{$statsRow}", $scoredIndicators);

            $statsRow++;
            $statsSheet->setCellValue("A{$statsRow}", "Performing Indicators (On Track + Exceeded)");
            $statsSheet->setCellValue("B{$statsRow}", $performingIndicators);

            $statsRow++;
            $performanceRate = ($scoredIndicators > 0) ? round(($performingIndicators / $scoredIndicators) * 100, 1) : 0;
            $statsSheet->setCellValue("A{$statsRow}", "Overall Performance Rate");
            $statsSheet->setCellValue("B{$statsRow}", "{$performanceRate}%");

            // Calculate average score
            $statsRow++;
            $totalScore = 0;
            $scoreCount = 0;

            foreach ($scoreboard as $row) {
                if (isset($row['scorePercent']) && is_numeric($row['scorePercent'])) {
                    $totalScore += $row['scorePercent'];
                    $scoreCount++;
                }
            }

            $averageScore = ($scoreCount > 0) ? round($totalScore / $scoreCount, 1) : 0;
            $statsSheet->setCellValue("A{$statsRow}", "Average Score Percentage");
            $statsSheet->setCellValue("B{$statsRow}", "{$averageScore}%");
        }

        // Auto-size columns in stats sheet
        foreach (range('A', 'C') as $col) {
            $statsSheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * addMetadataSheet
     *
     * Adds a metadata sheet with export details and parameters.
     */
    private function addMetadataSheet($spreadsheet, $request, $timeline, $year, $effectiveEntity)
    {
        $metaSheet = $spreadsheet->createSheet();
        $metaSheet->setTitle('Export Metadata');

        // Add title
        $metaSheet->setCellValue('A1', 'RRF Scoreboard Export Metadata');
        $metaSheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
        ]);
        $metaSheet->mergeCells('A1:B1');

        // Add export parameters
        $metaSheet->setCellValue('A3', 'Parameter');
        $metaSheet->setCellValue('B3', 'Value');
        $metaSheet->getStyle('A3:B3')->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['rgb' => 'E0E0E0'],
            ],
        ]);

        $row = 4;

        // Export date and time
        $metaSheet->setCellValue("A{$row}", "Export Date");
        $metaSheet->setCellValue("B{$row}", date('Y-m-d H:i:s'));
        $row++;

        // Export mode
        $metaSheet->setCellValue("A{$row}", "Export Mode");
        $metaSheet->setCellValue("B{$row}", ($effectiveEntity === "ALL") ? "Aggregated (All Entities)" : "Individual Entity: {$effectiveEntity}");
        $row++;

        // Timeline information
        $metaSheet->setCellValue("A{$row}", "Timeline");
        $metaSheet->setCellValue("B{$row}", $timeline ? $timeline->ReportName : 'N/A');
        $row++;

        $metaSheet->setCellValue("A{$row}", "Timeline ID");
        $metaSheet->setCellValue("B{$row}", $timeline ? $timeline->ReportingID : 'N/A');
        $row++;

        $metaSheet->setCellValue("A{$row}", "Year");
        $metaSheet->setCellValue("B{$row}", $year ?: 'N/A');
        $row++;

        // User information
        $user = Auth::user();
        if ($user) {
            $metaSheet->setCellValue("A{$row}", "Exported By");
            $metaSheet->setCellValue("B{$row}", $user->name);
            $row++;

            $metaSheet->setCellValue("A{$row}", "User Role");
            $metaSheet->setCellValue("B{$row}", $user->AccountRole);
            $row++;

            $metaSheet->setCellValue("A{$row}", "User Entity");
            $metaSheet->setCellValue("B{$row}", $user->EntityID ?: 'N/A');
            $row++;
        }

        // Request parameters
        $metaSheet->setCellValue("A{$row}", "Request Parameters");
        $row++;

        foreach ($request->all() as $key => $value) {
            if ($key !== '_token') {
                $metaSheet->setCellValue("A{$row}", $key);
                $metaSheet->setCellValue("B{$row}", is_array($value) ? json_encode($value) : $value);
                $row++;
            }
        }

        // Auto-size columns
        foreach (range('A', 'B') as $col) {
            $metaSheet->getColumnDimension($col)->setAutoSize(true);
        }
    }
}