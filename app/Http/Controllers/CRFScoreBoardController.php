<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// For Excel export (requires: composer require phpoffice/phpspreadsheet)
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CRFScoreBoardController extends Controller
{
    /**
     * showCRFScoreboard
     *
     * Displays the CRF scoreboard in the Blade view (scrn).
     * This logic collects:
     *  - An entity (and validates if it exists)
     *  - A reporting_period (timeline) if provided
     *  - A year, either from the timeline or user override
     * Then it retrieves the CRF indicators and attempts advanced scoring
     * for the chosen entity/year.
     *
     * The scoreboard data is passed to the view as $scorecards, $chartData, etc.
     *
     * NOTE: This sample merges the advanced logic from your prior code snippets
     *       to ensure safe fallback for missing data and robust error handling.
     */
    public function showCRFScoreboard(Request $request)
    {
        // 1. Parse request
        $selectedEntity   = $request->input('entity_id');
        $selectedTimeline = $request->input('reporting_period');
        $selectedYear     = $request->input('year');

        // We'll store "domain" (non-validation) errors separately
        $domainErrors = [];

        // If no entity, just show the scoreboard page with no data
        if (empty($selectedEntity)) {
            // Return the 'scrn' view with minimal data
            // so the user can select filters on the first load
            return view('scrn', [
                'Page' => 'v2_CRF_Reports.CRFScorecardReport',
                // No entity => user sees the "Welcome to CRF Scoreboard" block
            ]);
        }

        // 2. Validate the entity
        $entityRecord = DB::table('mpa_entities')->where('EntityID', $selectedEntity)->first();
        if (! $entityRecord) {
            $domainErrors[] = "Invalid entity selected: {$selectedEntity}";
            // We can continue, but the scoreboard likely won't show data
        }

        // 3. If timeline is given, try to get the timeline record
        $timelineRecord = null;
        if (! empty($selectedTimeline)) {
            $timelineRecord = DB::table('mpa_timelines')
                ->where('ReportingID', $selectedTimeline)
                ->first();
            if (! $timelineRecord) {
                $domainErrors[] = "Invalid reporting period specified.";
            }
        }

        // Derive the year
        $year = null;
        if ($timelineRecord) {
            $year = $timelineRecord->Year; // basic approach
                                           // If it's a lastBiAnnual=1, you might treat it as the annual for that year, etc.
        } else {
            // fallback to user-provided
            $year = $selectedYear;
        }
        if (empty($year)) {
            // We can mark an error or let it go with "no year => no target column"
            $domainErrors[] = "No valid year identified (neither timeline nor user override).";
        }

        // 4. Map year -> target column
        $mapYearToTargetColumn = function ($y) {
            switch ($y) {
                case '2023':return 'BaselinePAD2023';
                case '2024':return 'TargetYearOne2024';
                case '2025':return 'TargetYearTwo2025';
                case '2026':return 'TargetYearThree2026';
                case '2027':return 'TargetYearFour2027';
                case '2028':return 'TargetYearFive2028';
                case '2029':return 'TargetYearSix2029';
                case '2030':return 'TargetYearSeven2030';
                default:return null; // unknown
            }
        };
        $targetColumn = $mapYearToTargetColumn($year);

        // 5. Get all CRF indicators for the chosen entity
        $indicators = DB::table('mpa_indicators')
            ->where('PrimaryCategory', 'CRF')
            ->where('EntityID', $selectedEntity)
            ->get();
        if ($indicators->isEmpty()) {
            $domainErrors[] = "No CRF indicators found for {$selectedEntity}";
        }

        // 6. Build the scoreboard with advanced logic
        $statusCounts = [
            'Exceeded'      => 0,
            'On Track'      => 0,
            'Behind'        => 0,
            'No Data'       => 0,
            'No Target'     => 0,
            'Informational' => 0,
            'Data Error'    => 0,
        ];
        $scorecards = [];

        foreach ($indicators as $indicator) {
            $indicatorID   = $indicator->IID;
            $indicatorName = $indicator->Indicator;
            $baselineValue = $indicator->BaselinePAD2023 ?? null; // or any fallback

            $scoringLogic = $indicator->meta_scoring_logic ?? 'none';
            $responseType = $indicator->ResponseType ?? 'Text';

            $targetValue = null;
            if ($targetColumn && isset($indicator->{$targetColumn})) {
                $targetValue = $indicator->{$targetColumn};
            }

            $actualValue  = null;
            $scorePercent = null;
            $quickStatus  = "No Data";
            $rowErrors    = [];

            // Possibly retrieve actual from mpa_reports
            $reportRow = null;
            if (! empty($selectedTimeline)) {
                $reportRow = DB::table('mpa_reports')
                    ->where('IID', $indicatorID)
                    ->where('EntityID', $selectedEntity)
                    ->where('ReportingID', $selectedTimeline)
                    ->first();
            }

            if ($reportRow) {
                $actualValue = $reportRow->Response;
                if ($actualValue === null) {
                    $quickStatus = "No Data";
                }
            }

            if (is_null($targetValue)) {
                // no target => either "No Target / No Data" if actual is also missing
                if (is_null($actualValue)) {
                    $quickStatus = "No Target / No Data";
                } else {
                    $quickStatus = "No Target";
                }
            }

            // Attempt scoring if we have actual + target
            if (! is_null($targetValue) && ! is_null($actualValue)) {
                if ($scoringLogic === 'none') {
                    $scorePercent = null; // no scoring
                } else {
                    // Validate numeric / yes-no
                    $validTypes = ['Number', 'Percentage', 'Boolean', 'Yes/No'];
                    if (in_array($responseType, $validTypes)) {
                        if ($responseType === 'Yes/No' || $responseType === 'Boolean') {
                            $validYesNoValues = [0, 1, '0', '1', '0.0', '1.0', 'Yes', 'No',
                                'yes', 'no', 'YES', 'NO', true, false];

                            if (! in_array($actualValue, $validYesNoValues, true)) {
                                $rowErrors[] = "Invalid yes/no => {$actualValue}";
                            } else {
                                // Normalize actual value
                                $normalizedActual = in_array(
                                    trim(strtolower((string) $actualValue)),
                                    ['1', '1.0', 'yes'],
                                    true
                                ) ? 1 : 0;

                                // Normalize target value (if exists)
                                if (! is_null($targetValue)) {
                                    $normalizedTarget = in_array(
                                        trim(strtolower((string) $targetValue)),
                                        ['1', '1.0', 'yes'],
                                        true
                                    ) ? 1 : 0;
                                }

                                // Use normalized values for scoring
                                $actualFloat = (float) $normalizedActual;
                                $targetFloat = isset($normalizedTarget) ? (float) $normalizedTarget : 0.0;

                                // Special handling for Yes/No scoring
                                // For Yes/No indicators:
                                // If target=No (0) and actual=Yes (1), this exceeds expectations (100%)
                                // If target=Yes (1) and actual=Yes (1), this meets expectations (100%)
                                // If target=No (0) and actual=No (0), this meets expectations (100%)
                                // If target=Yes (1) and actual=No (0), this fails expectations (0%)
                                if ($normalizedTarget == 0 && $normalizedActual == 1) {
                                    $scorePercent = 100.0; // Exceeding a "No" target with a "Yes" actual
                                } else if ($normalizedTarget == $normalizedActual) {
                                    $scorePercent = 100.0; // Meeting the target exactly
                                } else {
                                    $scorePercent = 0.0; // Not meeting the target
                                }

                                // Skip the rest of the scoring logic for this iteration
                                // but DO NOT skip adding this indicator to the scorecards
                            }
                        } else {
                            if (! is_numeric($actualValue)) {
                                $rowErrors[] = "Non-numeric actual => {$actualValue}";
                            } else {
                                // Only process numeric scoring if we're not dealing with Yes/No
                                // and we have valid numeric values
                                $actualFloat = floatval($actualValue);
                                $targetFloat = floatval($targetValue);

                                // Now check if target is zero for ratio-based scoring
                                if ($targetFloat == 0.0 && $scoringLogic !== 'exact_match') {
                                    $rowErrors[] = "Target=0 => cannot compute ratio with " . $scoringLogic;
                                } else {
                                    switch ($scoringLogic) {
                                        case 'greater_is_better':
                                            $ratio        = ($actualFloat / $targetFloat) * 100.0;
                                            $scorePercent = $ratio;
                                            break;
                                        case 'less_is_better':
                                            if ($actualFloat > 0) {
                                                $ratio        = ($targetFloat / $actualFloat) * 100.0;
                                                $scorePercent = $ratio;
                                            } else {
                                                $rowErrors[] = "Actual=0 => cannot do less_is_better logic.";
                                            }
                                            break;
                                        case 'exact_match':
                                            $scorePercent = ($actualFloat == $targetFloat) ? 100.0 : 0.0;
                                            break;
                                        case 'range':
                                            if (! isset($indicator->meta_extra)) {
                                                $rowErrors[] = "range logic => missing meta_extra";
                                            } else {
                                                $extra = json_decode($indicator->meta_extra, true);
                                                if (isset($extra['min']) && isset($extra['max'])) {
                                                    $minVal = floatval($extra['min']);
                                                    $maxVal = floatval($extra['max']);
                                                    if ($actualFloat < $minVal || $actualFloat > $maxVal) {
                                                        $scorePercent = 0.0;
                                                    } else {
                                                        $scorePercent = 100.0;
                                                    }
                                                } else {
                                                    $rowErrors[] = "range => meta_extra missing min/max";
                                                }
                                            }
                                            break;
                                        default:
                                            $rowErrors[] = "Unknown scoring logic => {$scoringLogic}";
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (! is_null($scorePercent)) {
                if ($scorePercent < 0) {
                    $scorePercent = 0.0;
                } elseif ($scorePercent > 100) {
                    $scorePercent = 100.0;
                }
            }

            if (! empty($rowErrors)) {
                $quickStatus = "Data Error";
            } else {
                // If no row errors, interpret quickStatus from scorePercent
                if (! is_null($scorePercent)) {
                    if ($scorePercent >= 100.0) {
                        $quickStatus = "Exceeded";
                    } elseif ($scorePercent >= 80.0) {
                        $quickStatus = "On Track";
                    } else {
                        $quickStatus = "Behind";
                    }
                } else {
                    // Possibly no data or no scoring
                    if ($scoringLogic === 'none' || $scoringLogic === 'informational') {
                        $quickStatus = "Informational";
                    } elseif (is_null($actualValue) && is_null($targetValue)) {
                        $quickStatus = "No Data";
                    } elseif (is_null($targetValue)) {
                        $quickStatus = "No Target";
                    } else {
                        // fallback
                        $quickStatus = "No Data";
                    }
                }
            }

            if (! array_key_exists($quickStatus, $statusCounts)) {
                $statusCounts[$quickStatus] = 0;
            }
            $statusCounts[$quickStatus]++;

            $scorecards[] = [
                'indicatorID'   => $indicatorID,
                'indicatorName' => $indicatorName,
                'baselineValue' => $baselineValue,
                'targetValue'   => $targetValue,
                'actualValue'   => $actualValue,
                'scorePercent'  => is_null($scorePercent) ? null : round($scorePercent, 2),
                'quickStatus'   => $quickStatus,
                'rowErrors'     => $rowErrors,
            ];
        }

        // Build $chartData
        $chartStatusLabels = array_keys($statusCounts);
        $chartStatusValues = array_values($statusCounts);

        $scoredCount = 0;
        $sumScores   = 0.0;
        foreach ($scorecards as $row) {
            if (! is_null($row['scorePercent'])) {
                $scoredCount++;
                $sumScores += $row['scorePercent'];
            }
        }
        $averageScore = ($scoredCount > 0) ? round($sumScores / $scoredCount, 2) : null;

        $chartData = [
            'statusDistribution' => [
                'labels'   => $chartStatusLabels,
                'datasets' => [
                    [
                        'label' => 'CRF Indicator Status Distribution',
                        'data'  => $chartStatusValues,
                    ],
                ],
            ],
            'averageScore'       => $averageScore,
        ];

        // Return the 'scrn' view
        return view('scrn', [
            'Page'         => 'v2_CRF_Reports.CRFScorecardReport',
            'Entity'       => $entityRecord,
            'Timeline'     => $timelineRecord,
            'Year'         => $year,
            'scorecards'   => $scorecards,
            'chartData'    => $chartData,
            'domainErrors' => $domainErrors, // to display in the view
        ]);
    }

    /**
     * exportCRFScoreboardExcel
     *
     * Creates an Excel export (XLSX) with color-coded statuses, using the same data logic
     * from showCRFScoreboard. Must have installed phpoffice/phpspreadsheet.
     */
    public function exportCRFScoreboardExcel(Request $request)
    {
        // 1. Gather scoreboard data from your existing helper or logic
        $scoreboardData = $this->collectScoreboardData($request);

        // If there are domain errors or no data, bail out
        if (isset($scoreboardData['domainErrors']) && count($scoreboardData['domainErrors']) > 0) {
            return redirect()->back()->withErrors($scoreboardData['domainErrors']);
        }
        if (empty($scoreboardData['scorecards']) || count($scoreboardData['scorecards']) === 0) {
            return redirect()->back()->withErrors(['No scoreboard data found for Excel export.']);
        }

        // Extract needed pieces
        $entityObj   = $scoreboardData['Entity'] ?? null;
        $timelineObj = $scoreboardData['Timeline'] ?? null;
        $year        = $scoreboardData['Year'] ?? null;
        $scorecards  = $scoreboardData['scorecards'];

        $entityName    = $entityObj ? $entityObj->Entity : 'Unknown Entity';
        $reportingName = $timelineObj ? $timelineObj->ReportName : 'N/A';
        $yearLabel     = $year ?? 'N/A';

        // 2. Create a new Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('CRF Scoreboard');

        // Optional: define some column widths upfront
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 3. Add a "Header Section" at the top

        // Title row (merged across columns A-F)
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'MPA CRF Scoreboard Export');
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font'      => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // A few lines with entity, timeline, year, etc.
        $sheet->setCellValue('A2', "Reporting Entity: {$entityName}");
        $sheet->mergeCells('A2:F2');

        $sheet->setCellValue('A3', "Reporting Period: {$reportingName}");
        $sheet->mergeCells('A3:F3');

        $sheet->setCellValue('A4', "Selected Year: {$yearLabel}");
        $sheet->mergeCells('A4:F4');

        // If you want to mention baseline and target years specifically:
        // "Baseline for 2023, Target for 20xx"
        // (This is optional; adapt to your logic as needed)
        $sheet->setCellValue('A5', "Note: Baseline typically references 2023 (BaselinePAD2023). The Target references {$yearLabel} if applicable.");
        $sheet->mergeCells('A5:F5');
        $sheet->getStyle('A5:F5')->getAlignment()->setWrapText(true);

        // We'll start the table headers in row 7
        $headerRow = 7;

        // 4. Create the table header
        $sheet->setCellValue('A' . $headerRow, 'Indicator');
        $sheet->setCellValue('B' . $headerRow, 'Baseline (2023)');
        $sheet->setCellValue('C' . $headerRow, "Target ({$yearLabel})");
        $sheet->setCellValue('D' . $headerRow, 'Actual');
        $sheet->setCellValue('E' . $headerRow, 'Score %');
        $sheet->setCellValue('F' . $headerRow, 'Status');

        // Style the header row
        $sheet->getStyle("A{$headerRow}:F{$headerRow}")->applyFromArray([
            'font'      => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill'      => [
                'fillType' => Fill::FILL_SOLID,
                'color'    => ['rgb' => '4B5563'], // Dark gray background
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

                                    // 5. Fill the scoreboard rows
        $rowIndex = $headerRow + 1; // 8, 9, etc.

        foreach ($scorecards as $row) {
            $indicatorName = $row['indicatorName'] ?? 'N/A';
            $baselineVal   = $row['baselineValue'] ?? 'N/A';
            $targetVal     = $row['targetValue'] ?? 'N/A';
            $actualVal     = $row['actualValue'] ?? 'N/A';
            $scorePct      = isset($row['scorePercent']) ? $row['scorePercent'] . '%' : '-';
            $status        = $row['quickStatus'] ?? 'Unknown';

            $sheet->setCellValue('A' . $rowIndex, $indicatorName);
            $sheet->setCellValue('B' . $rowIndex, $baselineVal);
            $sheet->setCellValue('C' . $rowIndex, $targetVal);
            $sheet->setCellValue('D' . $rowIndex, $actualVal);
            $sheet->setCellValue('E' . $rowIndex, $scorePct);
            $sheet->setCellValue('F' . $rowIndex, $status);

                                                             // Color-code the status cell (column F) based on logic
            $statusColor = $this->mapStatusToColor($status); // same helper function as before
            $sheet->getStyle("F{$rowIndex}")->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color'    => ['rgb' => $statusColor],
                ],
                'font' => [
                    'color' => ['rgb' => 'FFFFFF'],
                ],
            ]);

            $rowIndex++;
        }

        // Optional: Add borders to the data region (A7:Fxx)
        $lastDataRow = $rowIndex - 1;
        $sheet->getStyle("A{$headerRow}:F{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['argb' => 'FFAAAAAA'],
                ],
            ],
        ]);

                                  // Optional: Freeze top rows so they remain visible when scrolling
                                  // e.g. freeze the first 6 rows (header lines) + 1 row of table header => row 7
        $sheet->freezePane('A8'); // cell after row 7

        // 6. Output as XLSX
        $filename = 'CRF_Scoreboard_' . str_replace(' ', '_', $entityName) . '_' . $yearLabel . '.xlsx';
        $writer   = IOFactory::createWriter($spreadsheet, 'Xlsx');

        // Force download response
        ob_start();
        $writer->save('php://output');
        $excelContent = ob_get_clean();

        return response($excelContent, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'max-age=0, must-revalidate, no-cache, no-store',
        ]);
    }
    /**
     * mapStatusToColor
     * Converts scoreboard quickStatus to a hex color for Excel fill.
     */
    private function mapStatusToColor($status)
    {
        switch ($status) {
            case 'Exceeded':return '10B981';   // green
            case 'On Track':return '0EA5E9';   // blue
            case 'Behind':return 'F59E0B';     // orange
            case 'Data Error':return 'EF4444'; // red
            case 'No Data':
            case 'No Target':
            case 'Informational':return '6B7280'; // gray
            default:return '9CA3AF';              // light gray
        }
    }

    /**
     * collectScoreboardData
     * A helper used by exportCRFScoreboardExcel to mimic showCRFScoreboard logic
     * but without returning a view. Instead, it returns an array with keys:
     *  - 'Entity' => the entity record
     *  - 'Year' => the final year
     *  - 'scorecards' => array of scoreboard rows
     *  - 'domainErrors' => any errors
     */
    private function collectScoreboardData(Request $request)
    {
        $data = [
            'Entity'       => null,
            'Timeline'     => null,
            'Year'         => null,
            'scorecards'   => [],
            'domainErrors' => [],
        ];

        $selectedEntity   = $request->input('entity_id');
        $selectedTimeline = $request->input('reporting_period');
        $selectedYear     = $request->input('year');

        if (empty($selectedEntity)) {
            $data['domainErrors'][] = "No entity provided.";
            return $data;
        }

        // same entity
        $entity = DB::table('mpa_entities')->where('EntityID', $selectedEntity)->first();
        if (! $entity) {
            $data['domainErrors'][] = "Invalid entity: {$selectedEntity}";
            return $data;
        }
        $data['Entity'] = $entity;

        // timeline
        $timeline = null;
        if ($selectedTimeline) {
            $timeline = DB::table('mpa_timelines')->where('ReportingID', $selectedTimeline)->first();
            if (! $timeline) {
                $data['domainErrors'][] = "Invalid reporting period specified.";
            }
        }
        $data['Timeline'] = $timeline;

        // year
        $year = null;
        if ($timeline) {
            $year = $timeline->Year;
        } else {
            $year = $selectedYear;
        }
        if (empty($year)) {
            $data['domainErrors'][] = "No valid year found (no timeline year or user year).";
        }
        $data['Year'] = $year;

        // if we have domainErrors, skip the rest
        if (count($data['domainErrors']) > 0) {
            return $data;
        }

        // target column
        $mapYearToTargetColumn = function ($y) {
            switch ($y) {
                case '2023':return 'BaselinePAD2023';
                case '2024':return 'TargetYearOne2024';
                case '2025':return 'TargetYearTwo2025';
                case '2026':return 'TargetYearThree2026';
                case '2027':return 'TargetYearFour2027';
                case '2028':return 'TargetYearFive2028';
                case '2029':return 'TargetYearSix2029';
                case '2030':return 'TargetYearSeven2030';
                default:return null;
            }
        };
        $targetColumn = $mapYearToTargetColumn($year);

        // fetch indicators
        $indicators = DB::table('mpa_indicators')
            ->where('PrimaryCategory', 'CRF')
            ->where('EntityID', $selectedEntity)
            ->get();
        if ($indicators->isEmpty()) {
            $data['domainErrors'][] = "No CRF indicators found for entity: {$selectedEntity}";
            return $data;
        }

        // advanced scoreboard logic
        $scorecards = [];
        foreach ($indicators as $indicator) {
            $indicatorID   = $indicator->IID;
            $indicatorName = $indicator->Indicator;
            $baselineValue = $indicator->BaselinePAD2023 ?? null;
            $scoringLogic  = $indicator->meta_scoring_logic ?? 'none';
            $responseType  = $indicator->ResponseType ?? 'Text';

            $targetValue = null;
            if ($targetColumn && isset($indicator->{$targetColumn})) {
                $targetValue = $indicator->{$targetColumn};
            }

            $actualValue  = null;
            $scorePercent = null;
            $quickStatus  = "No Data";
            $rowErrors    = [];

            // read from mpa_reports if timeline
            $reportRow = null;
            if ($selectedTimeline) {
                $reportRow = DB::table('mpa_reports')
                    ->where('IID', $indicatorID)
                    ->where('EntityID', $selectedEntity)
                    ->where('ReportingID', $selectedTimeline)
                    ->first();
            }
            if ($reportRow) {
                $actualValue = $reportRow->Response;
                if ($actualValue === null) {
                    $quickStatus = "No Data";
                }
            }

            if (is_null($targetValue)) {
                if (is_null($actualValue)) {
                    $quickStatus = "No Target / No Data";
                } else {
                    $quickStatus = "No Target";
                }
            }

            if (! is_null($targetValue) && ! is_null($actualValue)) {
                if ($scoringLogic === 'none') {
                    $scorePercent = null; // no scoring
                } else {
                    $validTypes = ['Number', 'Percentage', 'Boolean', 'Yes/No'];
                    if (in_array($responseType, $validTypes)) {
                        if ($responseType === 'Yes/No' || $responseType === 'Boolean') {
                            $validYesNoValues = [0, 1, '0', '1', '0.0', '1.0', 'Yes', 'No',
                                'yes', 'no', 'YES', 'NO', true, false];

                            if (! in_array($actualValue, $validYesNoValues, true)) {
                                $rowErrors[] = "Invalid yes/no => {$actualValue}";
                            } else {
                                // Normalize actual value
                                $normalizedActual = in_array(
                                    trim(strtolower((string) $actualValue)),
                                    ['1', '1.0', 'yes'],
                                    true
                                ) ? 1 : 0;

                                // Normalize target value (if exists)
                                if (! is_null($targetValue)) {
                                    $normalizedTarget = in_array(
                                        trim(strtolower((string) $targetValue)),
                                        ['1', '1.0', 'yes'],
                                        true
                                    ) ? 1 : 0;
                                }

                                // Use normalized values for scoring
                                $actualFloat = (float) $normalizedActual;
                                $targetFloat = isset($normalizedTarget) ? (float) $normalizedTarget : 0.0;

                                // Special handling for Yes/No scoring
                                // For Yes/No indicators:
                                // If target=No (0) and actual=Yes (1), this exceeds expectations (100%)
                                // If target=Yes (1) and actual=Yes (1), this meets expectations (100%)
                                // If target=No (0) and actual=No (0), this meets expectations (100%)
                                // If target=Yes (1) and actual=No (0), this fails expectations (0%)
                                if ($normalizedTarget == 0 && $normalizedActual == 1) {
                                    $scorePercent = 100.0; // Exceeding a "No" target with a "Yes" actual
                                } else if ($normalizedTarget == $normalizedActual) {
                                    $scorePercent = 100.0; // Meeting the target exactly
                                } else {
                                    $scorePercent = 0.0; // Not meeting the target
                                }

                                // Skip the rest of the scoring logic for this iteration
                                // but DO NOT skip adding this indicator to the scorecards
                            }
                        } else {
                            if (! is_numeric($actualValue)) {
                                $rowErrors[] = "Non-numeric => {$actualValue}";
                            } else {
                                // Only process numeric scoring if we're not dealing with Yes/No
                                // and we have valid numeric values
                                $actualFloat = floatval($actualValue);
                                $targetFloat = floatval($targetValue);

                                // Now check if target is zero for ratio-based scoring
                                if ($targetFloat == 0.0 && $scoringLogic !== 'exact_match') {
                                    $rowErrors[] = "Target=0 => cannot compute ratio with " . $scoringLogic;
                                } else {
                                    switch ($scoringLogic) {
                                        case 'greater_is_better':
                                            $ratio        = ($actualFloat / $targetFloat) * 100.0;
                                            $scorePercent = $ratio;
                                            break;
                                        case 'less_is_better':
                                            if ($actualFloat > 0) {
                                                $ratio        = ($targetFloat / $actualFloat) * 100.0;
                                                $scorePercent = $ratio;
                                            } else {
                                                $rowErrors[] = "Actual=0 => can't do less_is_better";
                                            }
                                            break;
                                        case 'exact_match':
                                            $scorePercent = ($actualFloat == $targetFloat) ? 100.0 : 0.0;
                                            break;
                                        case 'range':
                                            if (! isset($indicator->meta_extra)) {
                                                $rowErrors[] = "range => missing meta_extra";
                                            } else {
                                                $ext = json_decode($indicator->meta_extra, true);
                                                if (isset($ext['min']) && isset($ext['max'])) {
                                                    $minVal = floatval($ext['min']);
                                                    $maxVal = floatval($ext['max']);
                                                    if ($actualFloat < $minVal || $actualFloat > $maxVal) {
                                                        $scorePercent = 0.0;
                                                    } else {
                                                        $scorePercent = 100.0;
                                                    }
                                                } else {
                                                    $rowErrors[] = "range => missing min/max in meta_extra";
                                                }
                                            }
                                            break;
                                        default:
                                            $rowErrors[] = "Unknown logic => {$scoringLogic}";
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (! is_null($scorePercent)) {
                if ($scorePercent < 0) {
                    $scorePercent = 0.0;
                }

                if ($scorePercent > 100) {
                    $scorePercent = 100.0;
                }
            }

            if (! empty($rowErrors)) {
                $quickStatus = "Data Error";
            } else {
                if (! is_null($scorePercent)) {
                    if ($scorePercent >= 100) {
                        $quickStatus = "Exceeded";
                    } elseif ($scorePercent >= 80) {
                        $quickStatus = "On Track";
                    } else {
                        $quickStatus = "Behind";
                    }
                } else {
                    if ($scoringLogic === 'none' || $scoringLogic === 'informational') {
                        $quickStatus = "Informational";
                    } elseif (is_null($actualValue) && is_null($targetValue)) {
                        $quickStatus = "No Data";
                    } elseif (is_null($targetValue)) {
                        $quickStatus = "No Target";
                    } else {
                        $quickStatus = "No Data";
                    }
                }
            }

            $scorecards[] = [
                'indicatorID'   => $indicatorID,
                'indicatorName' => $indicatorName,
                'baselineValue' => $baselineValue,
                'targetValue'   => $targetValue,
                'actualValue'   => $actualValue,
                'scorePercent'  => is_null($scorePercent) ? null : round($scorePercent, 2),
                'quickStatus'   => $quickStatus,
                'rowErrors'     => $rowErrors,
            ];
        }

        $data['scorecards'] = $scorecards;
        return $data;
    }

}