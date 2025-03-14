{{-- scrn.blade.php --}}

{{-- Include ApexCharts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/4.3.0/apexcharts.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/4.3.0/apexcharts.min.css">

@if ($Page === 'v2_CRF_Reports.CRFScorecardReport')
    <div class="container mx-auto px-4 py-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-primary">
                <span class="iconify inline-block mr-2" data-icon="lucide:bar-chart-3"></span>
                CRF Scoreboard
            </h1>

            @if (isset($Entity) && $Entity)
                <div class="badge badge-lg badge-primary mt-2 md:mt-0">{{ $Entity->Entity }}</div>
            @endif
        </div>

        <!-- Filter Form (Always Displayed First) -->
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="iconify inline-block mr-2" data-icon="lucide:filter"></span>
                    Filter Options
                </h2>

                <form action="{{ route('crf.scoreboard') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @php
                        // Get the currently authenticated user
                        $currentUser = \Illuminate\Support\Facades\Auth::user();

                        // If user's role is Admin, they see ALL entities
if ($currentUser && $currentUser->AccountRole === 'Admin') {
    $entities = DB::table('mpa_entities')->orderBy('Entity')->get();
} else {
    // Otherwise, show only the entity they’re attached to
    $entities = DB::table('mpa_entities')
        ->where('EntityID', $currentUser->EntityID)
        ->orderBy('Entity')
                                ->get();
                        }
                    @endphp

                    <!-- Entity Selection -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">
                                Entity <span class="text-error">*</span>
                            </span>
                        </label>
                        <select name="entity_id" class="select select-bordered w-full" required>
                            <option value="">Select an entity</option>
                            @foreach ($entities as $entity)
                                <option value="{{ $entity->EntityID }}"
                                    @if ((isset($Entity) && $Entity && $Entity->EntityID === $entity->EntityID) || old('entity_id') == $entity->EntityID) selected @endif>
                                    {{ $entity->Entity }}
                                </option>
                            @endforeach
                        </select>
                        <label class="label">
                            <span class="label-text-alt text-error">Required</span>
                        </label>
                    </div>

                    <!-- Reporting Period -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Reporting Period</span>
                        </label>
                        <select name="reporting_period" class="select select-bordered w-full">
                            <option value="">Select a reporting period</option>
                            @php
                                $timelines = DB::table('mpa_timelines')->orderBy('created_at', 'desc')->get();
                            @endphp
                            @foreach ($timelines as $timeline)
                                <option value="{{ $timeline->ReportingID }}"
                                    @if (
                                        (isset($Timeline) && $Timeline && $Timeline->ReportingID === $timeline->ReportingID) ||
                                            old('reporting_period') === $timeline->ReportingID) selected @endif>
                                    {{ $timeline->ReportName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Year Override (hidden by default) -->
                    <div class="form-control" style="display: none">
                        <label class="label">
                            <span class="label-text font-medium">Year (Optional Override)</span>
                        </label>
                        <input type="text" name="year" class="input input-bordered" placeholder="YYYY"
                            value="{{ $Year ?? old('year') }}" pattern="[0-9]{4}">
                        <label class="label">
                            <span class="label-text-alt">Format: YYYY (e.g., 2025)</span>
                        </label>
                    </div>

                    <!-- Submit button -->
                    <div class="md:col-span-3 mt-2">
                        <button type="submit" class="btn btn-neutral outline btn-sm w-full md:w-auto">
                            <span class="iconify inline-block mr-2" data-icon="lucide:search"></span>
                            Generate Scoreboard
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Laravel Validation Errors (only shown after form submission) -->
        @if ($errors->any())
            <div class="alert alert-error mb-6">
                <span class="iconify inline-block mr-2" data-icon="lucide:alert-triangle"></span>
                <div>
                    <h3 class="font-bold">Validation Errors</h3>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Controller/Domain Errors (these come from $domainErrors in the controller) -->
        @if (isset($domainErrors) && is_array($domainErrors) && !empty($domainErrors))
            <div class="alert alert-error mb-6">
                <span class="iconify inline-block mr-2" data-icon="lucide:alert-triangle"></span>
                <div>
                    <h3 class="font-bold">Errors Encountered</h3>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($domainErrors as $domainError)
                            <li>{{ $domainError }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Welcome Message (shown only when no entity is selected) -->
        @if (!isset($Entity) || !$Entity)
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <h2 class="card-title">
                        <span class="iconify inline-block mr-2" data-icon="lucide:info"></span>
                        Welcome to CRF Scoreboard
                    </h2>
                    <p class="py-4">
                        Please select an entity from the dropdown above to view the CRF scoreboard data.
                        You can optionally select a specific reporting period or year to filter the results.
                    </p>
                    <div class="alert alert-info">
                        <div>
                            <span class="iconify inline-block mr-2" data-icon="lucide:lightbulb"></span>
                            <span>Start by selecting an entity to generate the scoreboard.</span>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- No Data Message (shown when entity is selected but no data is found) -->
        @if (isset($Entity) && $Entity && (!isset($scorecards) || count($scorecards) === 0))
            <div class="alert alert-warning mb-6">
                <span class="iconify inline-block mr-2" data-icon="lucide:alert-circle"></span>
                <div>
                    <h3 class="font-bold">No Data Available</h3>
                    <p>No scorecards found for {{ $Entity->Entity }} with the selected criteria.</p>
                    <p class="mt-2">Try selecting a different reporting period or year.</p>
                </div>
            </div>
        @endif

        <!-- Scoreboard Content (only shown when entity is selected and data exists) -->
        @if (isset($Entity) && $Entity && isset($scorecards) && count($scorecards) > 0)
            <!-- New Reporting Summary Chart (only when a single entity is selected, not "All") -->
            @if (strtolower($Entity->Entity) !== 'all')
                @php
                    $supposedCount = 0;
                    $reportedCount = 0;
                    foreach ($scorecards as $scorecard) {
                        if (!is_null($scorecard['targetValue'])) {
                            $supposedCount++;
                            if (!is_null($scorecard['actualValue'])) {
                                $reportedCount++;
                            }
                        }
                    }
                    $notReportedCount = $supposedCount - $reportedCount;
                @endphp
                <div class="card bg-base-100 shadow-xl mb-6">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="iconify inline-block mr-2" data-icon="lucide:bar-chart"></span>
                            Reporting Summary
                        </h2>
                        <div id="reportingChart" class="w-full h-64"></div>
                    </div>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        var options = {
                            chart: {
                                type: 'bar',
                                height: 350
                            },
                            series: [{
                                name: 'Count',
                                data: [{{ $supposedCount }}, {{ $reportedCount }}, {{ $notReportedCount }}]
                            }],
                            xaxis: {
                                categories: ['Supposed to Report', 'Reported', 'Not Reported']
                            },
                            colors: ['#3B82F6', '#10B981', '#EF4444'],
                            title: {
                                text: 'Indicator Reporting Overview',
                                align: 'center'
                            },
                            tooltip: {
                                y: {
                                    formatter: function(val) {
                                        return val + " indicator(s)";
                                    }
                                }
                            }
                        };

                        var chart = new ApexCharts(document.querySelector("#reportingChart"), options);
                        chart.render();
                    });
                </script>
            @endif

            <!-- Scoreboard Summary -->
            @if (isset($chartData) && isset($chartData['statusDistribution']))
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Average Score Card -->
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="iconify inline-block mr-2" data-icon="lucide:percent"></span>
                                Average Score
                            </h2>
                            <div class="flex items-center justify-center h-32">
                                @if (isset($chartData['averageScore']) && $chartData['averageScore'] !== null)
                                    <div class="radial-progress text-primary"
                                        style="--value:{{ min(100, $chartData['averageScore']) }}; --size:8rem; --thickness: 1rem;">
                                        <span class="text-2xl font-bold">{{ $chartData['averageScore'] }}%</span>
                                    </div>
                                @else
                                    <div class="text-xl text-gray-500">No scored indicators</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Status Distribution (Apex Donut) -->
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h2 class="card-title text-lg">
                                <span class="iconify inline-block mr-2" data-icon="lucide:pie-chart"></span>
                                Status Distribution
                            </h2>
                            <!-- We'll replace the canvas with a plain div for ApexCharts -->
                            <div id="statusChart" class="w-full h-64"></div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Scoreboard Table -->
            <div class="card bg-base-100 shadow-xl overflow-x-auto mb-6">
                <div class="card-body p-0">
                    <table class="table table-zebra w-full ">
                        <thead>
                            <tr class="bg-gradient-to-r from-blue-100 via-purple-100 to-pink-100 text-gray-800">
                                <th class="text-center">Indicator</th>
                                <th class="text-center">Baseline</th>
                                <th class="text-center">Target ({{ $Year ?? 'N/A' }})</th>
                                <th class="text-center">Actual</th>
                                <th class="text-center">Score %</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($scorecards as $index => $row)
                                <tr class="{{ !empty($row['rowErrors']) ? 'bg-error bg-opacity-10' : '' }}">
                                    <td class="max-w-md">
                                        <div class="font-medium">{{ $row['indicatorName'] }}</div>
                                        @if (!empty($row['rowErrors']))
                                            <div class="text-xs text-error mt-1">
                                                @foreach ($row['rowErrors'] as $error)
                                                    <div>{{ $error }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $row['baselineValue'] ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $row['targetValue'] ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $row['actualValue'] ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        @if (isset($row['scorePercent']) && $row['scorePercent'] !== null)
                                            {{ $row['scorePercent'] }}%
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $statusColor = 'badge-ghost';
                                            if (isset($row['quickStatus'])) {
                                                if ($row['quickStatus'] === 'Exceeded') {
                                                    $statusColor = 'badge-success';
                                                } elseif ($row['quickStatus'] === 'On Track') {
                                                    $statusColor = 'badge-info';
                                                } elseif ($row['quickStatus'] === 'Behind') {
                                                    $statusColor = 'badge-warning';
                                                } elseif ($row['quickStatus'] === 'Data Error') {
                                                    $statusColor = 'badge-error';
                                                }
                                            }
                                        @endphp
                                        <div class="badge {{ $statusColor }} whitespace-nowrap">
                                            {{ $row['quickStatus'] ?? 'Unknown' }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-neutral outline btn-sm"
                                            onclick="document.getElementById('detailModal{{ $index }}').classList.add('modal-open')">
                                            <span class="iconify" data-icon="lucide:eye"></span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Export Options -->
            <div class="flex justify-end mb-6 gap-2">
                <button class="btn btn-neutral outline btn-sm" onclick="window.print()">
                    <span class="iconify inline-block mr-2" data-icon="lucide:printer"></span>
                    Print
                </button>

                <!-- CSV Export Button -->
                <button class="btn btn-neutral outline btn-sm" id="exportCSV">
                    <span class="iconify inline-block mr-2" data-icon="lucide:download"></span>
                    Export CSV
                </button>
            </div>

            <!-- Detail Modals -->
            @foreach ($scorecards as $index => $row)
                <div id="detailModal{{ $index }}" class="modal">
                    <div class="modal-box max-w-3xl">
                        <h3 class="font-bold text-lg">Indicator Details</h3>
                        <div class="py-4">
                            <div class="grid grid-cols-1 gap-4">
                                <div>
                                    <h4 class="font-semibold">Indicator Name</h4>
                                    <p>{{ $row['indicatorName'] }}</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <h4 class="font-semibold">Baseline</h4>
                                        <p>{{ $row['baselineValue'] ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold">Target ({{ $Year ?? 'N/A' }})</h4>
                                        <p>{{ $row['targetValue'] ?? 'N/A' }}</p>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold">Actual</h4>
                                        <p>{{ $row['actualValue'] ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <h4 class="font-semibold">Score</h4>
                                        <p>
                                            @if (isset($row['scorePercent']) && $row['scorePercent'] !== null)
                                                {{ $row['scorePercent'] }}%
                                            @else
                                                N/A
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold">Status</h4>
                                        @php
                                            $statusColor = 'badge-ghost';
                                            if (isset($row['quickStatus'])) {
                                                if ($row['quickStatus'] === 'Exceeded') {
                                                    $statusColor = 'badge-success';
                                                } elseif ($row['quickStatus'] === 'On Track') {
                                                    $statusColor = 'badge-info';
                                                } elseif ($row['quickStatus'] === 'Behind') {
                                                    $statusColor = 'badge-warning';
                                                } elseif ($row['quickStatus'] === 'Data Error') {
                                                    $statusColor = 'badge-error';
                                                }
                                            }
                                        @endphp
                                        <div class="badge {{ $statusColor }} whitespace-nowrap">
                                            {{ $row['quickStatus'] ?? 'Unknown' }}
                                        </div>
                                    </div>
                                </div>
                                @if (!empty($row['rowErrors']))
                                    <div>
                                        <h4 class="font-semibold text-error">Errors</h4>
                                        <ul class="list-disc list-inside text-error">
                                            @foreach ($row['rowErrors'] as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @php
                                    // Get additional indicator details if available
                                    $indicatorDetails = null;
                                    if (isset($row['indicatorID'])) {
                                        $indicatorDetails = DB::table('mpa_indicators')
                                            ->where('IID', $row['indicatorID'])
                                            ->first();
                                    }
                                @endphp

                                @if ($indicatorDetails)
                                    @if ($indicatorDetails->IndicatorDefinition)
                                        <div>
                                            <h4 class="font-semibold">Definition</h4>
                                            <p>{{ $indicatorDetails->IndicatorDefinition }}</p>
                                        </div>
                                    @endif

                                    @if ($indicatorDetails->IndicatorQuestion)
                                        <div>
                                            <h4 class="font-semibold">Question</h4>
                                            <p>{{ $indicatorDetails->IndicatorQuestion }}</p>
                                        </div>
                                    @endif

                                    @if ($indicatorDetails->RemarksComments)
                                        <div>
                                            <h4 class="font-semibold">Remarks</h4>
                                            <p>{{ $indicatorDetails->RemarksComments }}</p>
                                        </div>
                                    @endif

                                    @if ($indicatorDetails->SourceOfData)
                                        <div>
                                            <h4 class="font-semibold">Source of Data</h4>
                                            <p>{{ $indicatorDetails->SourceOfData }}</p>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="modal-action">
                            <button class="btn btn-neutral outline btn-sm"
                                onclick="document.getElementById('detailModal{{ $index }}').classList.remove('modal-open')">Close</button>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- ApexCharts + CSV Export Script -->
    <script>
        /**
         * For CSV export, we want to safely escape fields by wrapping them in quotes
         * and doubling any internal quotes. This ensures correct CSV formatting.
         */
        function csvSafe(value) {
            const str = (value ?? '').toString();
            // Replace any " with "" inside the string
            const escaped = str.replace(/"/g, '""');
            return `"${escaped}"`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            // APEXCHARTS: Build the status distribution donut only if we have data
            @if (isset($Entity) && $Entity && isset($chartData) && isset($chartData['statusDistribution']))
                var statusChartEl = document.querySelector("#statusChart");
                if (statusChartEl) {
                    var statusLabels = @json($chartData['statusDistribution']['labels'] ?? []);
                    var statusData = @json($chartData['statusDistribution']['datasets'][0]['data'] ?? []);

                    // Map status -> color
                    var statusColors = statusLabels.map(function(label) {
                        switch (label) {
                            case 'Exceeded':
                                return '#10b981'; // green
                            case 'On Track':
                                return '#0ea5e9'; // blue
                            case 'Behind':
                                return '#f59e0b'; // orange
                            case 'Data Error':
                                return '#ef4444'; // red
                            case 'No Data':
                                return '#6b7280'; // gray
                            case 'No Target':
                                return '#9ca3af'; // lighter gray
                            case 'Informational':
                                return '#8b5cf6'; // purple
                            default:
                                return '#d1d5db'; // fallback gray
                        }
                    });

                    var options = {
                        chart: {
                            type: 'donut',
                            height: '100%'
                        },
                        labels: statusLabels,
                        series: statusData,
                        colors: statusColors,
                        legend: {
                            position: 'bottom'
                        },
                    };
                    var chart = new ApexCharts(statusChartEl, options);
                    chart.render();
                }
            @endif

            // CSV Export
            const exportCSVBtn = document.getElementById('exportCSV');
            if (exportCSVBtn) {
                exportCSVBtn.addEventListener('click', function() {
                    @if (isset($Entity) && $Entity && isset($scorecards) && count($scorecards) > 0)
                        const scorecards = @json($scorecards);
                        const entityName = @json($Entity->Entity);
                        const year = @json($Year ?? 'All');

                        // CSV Header
                        let csvContent = "data:text/csv;charset=utf-8,";
                        csvContent += "Indicator,Baseline,Target,Actual,Score %,Status\n";

                        // Build CSV rows
                        scorecards.forEach(function(row) {
                            const indicatorName = csvSafe(row.indicatorName);
                            const baseline = csvSafe(row.baselineValue ?? 'N/A');
                            const target = csvSafe(row.targetValue ?? 'N/A');
                            const actual = csvSafe(row.actualValue ?? 'N/A');
                            const score = (row.scorePercent !== null) ?
                                csvSafe(row.scorePercent + '%') :
                                csvSafe('N/A');
                            const status = csvSafe(row.quickStatus ?? 'Unknown');

                            csvContent += [
                                indicatorName,
                                baseline,
                                target,
                                actual,
                                score,
                                status
                            ].join(",") + "\n";
                        });

                        // Encode & force download
                        const encodedUri = encodeURI(csvContent);
                        const link = document.createElement("a");
                        link.setAttribute("href", encodedUri);
                        link.setAttribute("download", `${entityName}_Scoreboard_${year}.csv`);
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    @endif
                });
            }
        });
    </script>

    <!-- Print Styles -->
    <style>
        @media print {

            .btn,
            .form-control,
            .card-body form,
            .modal,
            .modal-action {
                display: none !important;
            }

            body,
            .container {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            th,
            td {
                border: 1px solid #ddd !important;
                padding: 8px !important;
            }

            .badge {
                border: 1px solid #ddd !important;
                padding: 4px 8px !important;
            }
        }
    </style>
@endif
