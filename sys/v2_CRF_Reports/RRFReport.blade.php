<!-- ApexCharts CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/4.3.0/apexcharts.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/4.3.0/apexcharts.min.css" />

<!-- Microsoft Fluent UI inspired styles -->
<style>
    /* Modal fullscreen styles */
    .modal-fullscreen {
        width: 100vw !important;
        max-width: 100vw !important;
        height: 100vh !important;
        margin: 0 !important;
        padding: 0 !important;
        border-radius: 0 !important;
    }

    .modal-fullscreen .modal-content {
        height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .modal-fullscreen .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 1rem 1.5rem;
    }

    .modal-fullscreen .modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
    }

    .modal-fullscreen .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 1rem 1.5rem;
    }

    /* Microsoft Fluent UI inspired card styles */
    .ms-card {
        border: 1px solid #e5e7eb;
        border-radius: 4px;
        background: white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }

    .ms-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .ms-card-header {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        font-weight: 600;
        color: #111827;
        background-color: #f9fafb;
    }

    .ms-card-body {
        padding: 1rem;
    }

    /* Fix for status badges */
    .status-badge {
        white-space: nowrap;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
        font-size: 0.75rem;
        height: auto !important;
        min-height: 1.5rem;
    }

    /* Print styles */
    @media print {

        .btn,
        form,
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

        .badge,
        .status-badge {
            border: 1px solid #ddd !important;
            padding: 4px 8px !important;
        }
    }
</style>

@if ($Page === 'v2_CRF_Reports.RRFReport')
    <div class="container mx-auto px-4 py-6">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-primary flex items-center">
                <span class="iconify inline-block mr-2" data-icon="lucide:bar-chart-3"></span>
                RRF Smart Scoreboard
            </h1>
            @if (isset($timeline) && $timeline)
                <div class="badge badge-lg badge-primary mt-2 md:mt-0">
                    {{ $timeline->ReportName }} ({{ $Year ?? 'N/A' }})
                </div>
            @endif
        </div>

        <!-- Filter Form (Always Displayed First) -->
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <span class="iconify inline-block mr-2" data-icon="lucide:filter"></span>
                    Filter Options
                </h2>

                <form action="{{ route('rrf.scoreboard') }}" method="GET"
                    class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Entity Selection -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Entity <span class="text-error">*</span></span>
                        </label>
                        <select required name="entity_id" class="select select-bordered w-full"
                            {{ count($entities) <= 1 ? 'disabled' : '' }}>
                            <option value="">Select an entity</option>

                            @php
                                $user = auth()->user();
                                $isAdminMPA = $user->UserType === 'MPA' && $user->AccountRole === 'Admin';
                            @endphp

                            <!-- Show "All Entities" option only for Admin MPA users -->
                            @if ($isAdminMPA)
                                <option value="ALL" {{ request('entity_id') == 'ALL' ? 'selected' : '' }}>All
                                    Entities</option>
                                @foreach ($entities as $entity)
                                    <option value="{{ $entity->EntityID }}"
                                        {{ request('entity_id') == $entity->EntityID ? 'selected' : '' }}>
                                        {{ $entity->Entity }}
                                    </option>
                                @endforeach
                            @else
                                <!-- Show only assigned entity for non-Admin MPA users -->
                                <option value="{{ $user->EntityID }}" selected>
                                    {{ $entities->firstWhere('EntityID', $user->EntityID)->Entity ?? 'Your Entity' }}
                                </option>
                            @endif
                        </select>

                        @if (!$isAdminMPA)
                            <label class="label">
                                <span class="label-text-alt">You only have access to your assigned entity</span>
                            </label>
                        @endif
                    </div>

                    <!-- Reporting Period -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Reporting Period</span>
                        </label>
                        <select required name="reporting_period" class="select select-bordered w-full">
                            <option value="">Select a reporting period</option>
                            @foreach ($timelines as $timeline)
                                <option value="{{ $timeline->ReportingID }}"
                                    {{ request('reporting_period') == $timeline->ReportingID ? 'selected' : '' }}>
                                    {{ $timeline->ReportName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-3 mt-2">
                        <button type="submit" class="btn btn-sm btn-primary w-full md:w-auto">
                            <span class="iconify inline-block mr-2" data-icon="lucide:search"></span>
                            Generate Scoreboard
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Display an informational alert if no entity is selected -->
        @if (!request('entity_id'))
            <div class="card bg-base-100 shadow-xl mb-6">
                <div class="card-body">
                    <div class="alert alert-info">
                        <span class="iconify inline-block mr-2" data-icon="lucide:info"></span>
                        <span>Please select an entity from the dropdown above to view the RRF scoreboard data.</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Laravel Validation Errors -->
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

        <!-- Controller/Domain Errors -->
        @if (isset($domainErrors) && is_array($domainErrors) && !empty($domainErrors))
            <div class="alert alert-error mb-6">
                <span class="iconify inline-block mr-2" data-icon="lucide:alert-triangle"></span>
                <div>
                    <h3 class="font-bold">Data Accuracy Issues Detected</h3>
                    <ul class="mt-1 list-disc list-inside">
                        @foreach ($domainErrors as $domainError)
                            <li>{{ $domainError }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @else
            {{-- <div class="alert alert-success mb-6">
                <span class="iconify inline-block mr-2" data-icon="lucide:check-circle"></span>
                <span>Data Accuracy Verified – All indicators appear properly formatted.</span>
            </div> --}}
        @endif

        <!-- Scoreboard Content (only shown when an entity is selected and scoreboard data exists) -->
        @if (request('entity_id') && !empty($scoreboard))
            <!-- Summary Charts -->
            <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-6">
                <!-- Status Distribution Chart -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h2 class="card-title text-lg">
                            <span class="iconify inline-block mr-2" data-icon="lucide:pie-chart"></span>
                            Status Distribution
                        </h2>
                        <div id="statusDistributionChart" class="h-64"></div>
                    </div>
                </div>

                <!-- Dynamic Chart: Use Actual vs Target if aggregated mode; else Reporting Completeness -->
                {{-- Chart code commented out as before --}}
            </div>

            <!-- Export Options -->
            <div class="flex justify-end mb-6 gap-2">
                <a href="{{ route('rrf.scoreboard.export', request()->query()) }}" class="btn btn-outline btn-sm">
                    <span class="iconify inline-block mr-2" data-icon="lucide:file-spreadsheet"></span>
                    Export Excel
                </a>
                <button type="button" class="btn btn-outline btn-sm" onclick="window.print()">
                    <span class="iconify inline-block mr-2" data-icon="lucide:printer"></span>
                    Print
                </button>
            </div>

            <!-- Scoreboard Table -->
            <div class="card bg-base-100 shadow-xl overflow-x-auto mb-6">
                <div class="card-body p-0">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr class="bg-base-200">
                                {{-- <th class="bg-base-200">Indicator ID</th> --}}
                                <th class="bg-base-200">Entity</th>
                                <th class="bg-base-200">Indicator Name</th>
                                <th class="bg-base-200 text-center">Target</th>
                                <th class="bg-base-200 text-center">Actual</th>
                                <th class="bg-base-200 text-center">Score %</th>
                                <th class="bg-base-200 text-center">Status</th>
                                <th class="bg-base-200 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($scoreboard as $index => $row)
                                <tr class="{{ !empty($row['errors']) ? 'bg-error bg-opacity-10' : '' }}">
                                    {{-- <td>{{ $row['indicatorID'] ?? 'N/A' }}</td> --}}
                                    <td>{{ $row['entityID'] ?? 'N/A' }}</td>
                                    <td class="max-w-md">
                                        <div class="font-medium">{{ $row['indicatorName'] ?? 'N/A' }}</div>
                                        @if (!empty($row['errors']))
                                            <div class="text-xs text-error mt-1">
                                                @foreach ($row['errors'] as $error)
                                                    <div>{{ $error }}</div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
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
                                                } elseif (in_array($row['quickStatus'], ['No Data', 'No Target'])) {
                                                    $statusColor = 'badge-ghost';
                                                } elseif ($row['quickStatus'] === 'Informational') {
                                                    $statusColor = 'badge-secondary';
                                                }
                                            }
                                        @endphp
                                        <div class="status-badge badge {{ $statusColor }}">
                                            {{ $row['quickStatus'] ?? 'Unknown' }}</div>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-ghost"
                                            onclick="openDetailModal('detailModal{{ $index }}')">
                                            <span class="iconify" data-icon="lucide:eye"></span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Detail Modals (Fullscreen Microsoft-inspired design) -->
            @foreach ($scoreboard as $index => $row)
                <div id="detailModal{{ $index }}" class="modal">
                    <div class="modal-box modal-fullscreen">
                        <div class="modal-content">
                            <!-- Modal Header -->
                            <div class="modal-header flex justify-between items-center">
                                <h3 class="text-xl font-bold">Indicator Details</h3>
                                <button class="btn btn-sm btn-circle"
                                    onclick="closeDetailModal('detailModal{{ $index }}')">
                                    <span class="iconify" data-icon="lucide:x"></span>
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <div class="modal-body">
                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                    <!-- Left Column: Basic Information -->
                                    <div class="lg:col-span-1">
                                        <!-- Indicator Overview Card -->
                                        <div class="ms-card">
                                            <div class="ms-card-header flex items-center">
                                                <span class="iconify mr-2" data-icon="lucide:info"></span>
                                                Indicator Overview
                                            </div>
                                            <div class="ms-card-body">
                                                <div class="space-y-4">
                                                    <div>
                                                        <h4 class="text-sm text-gray-500">Indicator ID</h4>
                                                        <p class="font-medium">{{ $row['indicatorID'] ?? 'N/A' }}</p>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-sm text-gray-500">Entity</h4>
                                                        <p class="font-medium">{{ $row['entityID'] ?? 'N/A' }}</p>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-sm text-gray-500">Indicator Name</h4>
                                                        <p class="font-medium">{{ $row['indicatorName'] ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Status Card -->
                                        <div class="ms-card mt-6">
                                            <div class="ms-card-header flex items-center">
                                                <span class="iconify mr-2" data-icon="lucide:activity"></span>
                                                Performance Status
                                            </div>
                                            <div class="ms-card-body">
                                                <div class="space-y-4">
                                                    @php
                                                        $statusColor = 'badge-ghost';
                                                        $statusIcon = 'lucide:help-circle';
                                                        $statusDescription = 'No status information available.';

                                                        if (isset($row['quickStatus'])) {
                                                            if ($row['quickStatus'] === 'Exceeded') {
                                                                $statusColor = 'badge-success';
                                                                $statusIcon = 'lucide:trending-up';
                                                                $statusDescription =
                                                                    'Performance has exceeded the target.';
                                                            } elseif ($row['quickStatus'] === 'On Track') {
                                                                $statusColor = 'badge-info';
                                                                $statusIcon = 'lucide:check-circle';
                                                                $statusDescription =
                                                                    'Performance is on track with the target.';
                                                            } elseif ($row['quickStatus'] === 'Behind') {
                                                                $statusColor = 'badge-warning';
                                                                $statusIcon = 'lucide:alert-triangle';
                                                                $statusDescription =
                                                                    'Performance is behind the target.';
                                                            } elseif ($row['quickStatus'] === 'Data Error') {
                                                                $statusColor = 'badge-error';
                                                                $statusIcon = 'lucide:x-circle';
                                                                $statusDescription = 'There are errors in the data.';
                                                            } elseif ($row['quickStatus'] === 'No Data') {
                                                                $statusColor = 'badge-ghost';
                                                                $statusIcon = 'lucide:database-x';
                                                                $statusDescription =
                                                                    'No data is available for this indicator.';
                                                            } elseif ($row['quickStatus'] === 'No Target') {
                                                                $statusColor = 'badge-ghost';
                                                                $statusIcon = 'lucide:target-off';
                                                                $statusDescription =
                                                                    'No target has been set for this indicator.';
                                                            } elseif ($row['quickStatus'] === 'Informational') {
                                                                $statusColor = 'badge-secondary';
                                                                $statusIcon = 'lucide:info';
                                                                $statusDescription =
                                                                    'This is an informational indicator.';
                                                            }
                                                        }
                                                    @endphp

                                                    <div class="flex items-center">
                                                        <span class="iconify text-2xl mr-3"
                                                            data-icon="{{ $statusIcon }}"></span>
                                                        <div>
                                                            <div class="status-badge badge {{ $statusColor }} mb-1">
                                                                {{ $row['quickStatus'] ?? 'Unknown' }}
                                                            </div>
                                                            <p class="text-sm text-gray-600">{{ $statusDescription }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="grid grid-cols-3 gap-4 mt-4">
                                                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                                                            <h5 class="text-xs text-gray-500 mb-1">Target</h5>
                                                            <p class="font-semibold">
                                                                {{ $row['targetValue'] ?? 'N/A' }}</p>
                                                        </div>
                                                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                                                            <h5 class="text-xs text-gray-500 mb-1">Actual</h5>
                                                            <p class="font-semibold">
                                                                {{ $row['actualValue'] ?? 'N/A' }}</p>
                                                        </div>
                                                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                                                            <h5 class="text-xs text-gray-500 mb-1">Score</h5>
                                                            <p class="font-semibold">
                                                                {{ isset($row['scorePercent']) && $row['scorePercent'] !== null ? $row['scorePercent'] . '%' : 'N/A' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Middle Column: Data Quality & Raw Responses -->
                                    <div class="lg:col-span-1">
                                        <!-- Data Quality Card -->
                                        <div class="ms-card">
                                            <div class="ms-card-header flex items-center">
                                                <span class="iconify mr-2" data-icon="lucide:shield-check"></span>
                                                Data Quality Check
                                            </div>
                                            <div class="ms-card-body">
                                                @if (!empty($row['errors']))
                                                    <div class="bg-red-50 p-4 rounded-md mb-4">
                                                        <div class="flex items-center mb-2">
                                                            <span class="iconify text-red-500 mr-2"
                                                                data-icon="lucide:alert-circle"></span>
                                                            <h4 class="font-semibold text-red-700">Issues Detected</h4>
                                                        </div>
                                                        <ul
                                                            class="list-disc list-inside text-red-600 text-sm space-y-1">
                                                            @foreach ($row['errors'] as $error)
                                                                <li>{{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @else
                                                    <div class="bg-green-50 p-4 rounded-md">
                                                        <div class="flex items-center">
                                                            <span class="iconify text-green-500 mr-2"
                                                                data-icon="lucide:check-circle"></span>
                                                            <h4 class="font-semibold text-green-700">No Issues Detected
                                                            </h4>
                                                        </div>
                                                        <p class="text-green-600 text-sm mt-1">Data quality checks
                                                            passed successfully.</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Raw Responses Card -->
                                        <div class="ms-card mt-6">
                                            <div class="ms-card-header flex items-center">
                                                <span class="iconify mr-2" data-icon="lucide:message-square"></span>
                                                Reported Responses
                                            </div>
                                            <div class="ms-card-body">
                                                @if (isset($row['rawResponses']) && is_array($row['rawResponses']) && isset($row['rawResponses']['expected']))
                                                    <div class="mb-4">
                                                        <h4 class="text-sm font-semibold">Expected to Report:</h4>
                                                        <p class="text-sm bg-gray-50 p-2 rounded-md">
                                                            {{ implode(', ', $row['rawResponses']['expected']) }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-sm font-semibold">Reported Responses:</h4>
                                                        @if (count($row['rawResponses']['reported']))
                                                            <ul class="space-y-2">
                                                                @foreach ($row['rawResponses']['reported'] as $resp)
                                                                    <li class="p-2 bg-gray-50 rounded-md text-sm">
                                                                        <strong>{{ $resp['Entity'] }}</strong>:
                                                                        {{ $resp['Response'] }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        @else
                                                            <p class="text-sm text-gray-500">No responses recorded.</p>
                                                        @endif
                                                    </div>
                                                @elseif(isset($row['rawResponses']) && is_array($row['rawResponses']))
                                                    <ul class="space-y-2">
                                                        @foreach ($row['rawResponses'] as $resp)
                                                            <li class="p-2 bg-gray-50 rounded-md text-sm">
                                                                {{ $resp }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <div class="text-center py-6 text-gray-500">
                                                        <span class="iconify block mx-auto mb-2 text-3xl"
                                                            data-icon="lucide:inbox"></span>
                                                        <p>No responses recorded</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Column: Indicator Details -->
                                    <div class="lg:col-span-1">
                                        @php
                                            $indicatorDetails = DB::table('mpa_indicators')
                                                ->where('IID', $row['indicatorID'] ?? '')
                                                ->first();
                                        @endphp

                                        <div class="ms-card">
                                            <div class="ms-card-header flex items-center">
                                                <span class="iconify mr-2" data-icon="lucide:file-text"></span>
                                                Indicator Details
                                            </div>
                                            <div class="ms-card-body">
                                                @if ($indicatorDetails)
                                                    <div class="space-y-4">
                                                        @if ($indicatorDetails->IndicatorDefinition)
                                                            <div>
                                                                <h4 class="text-sm font-semibold text-gray-700 mb-1">
                                                                    Definition</h4>
                                                                <p class="text-sm bg-gray-50 p-3 rounded-md">
                                                                    {{ $indicatorDetails->IndicatorDefinition }}
                                                                </p>
                                                            </div>
                                                        @endif

                                                        @if ($indicatorDetails->IndicatorQuestion)
                                                            <div>
                                                                <h4 class="text-sm font-semibold text-gray-700 mb-1">
                                                                    Question</h4>
                                                                <p class="text-sm bg-gray-50 p-3 rounded-md">
                                                                    {{ $indicatorDetails->IndicatorQuestion }}
                                                                </p>
                                                            </div>
                                                        @endif

                                                        @if ($indicatorDetails->RemarksComments)
                                                            <div>
                                                                <h4 class="text-sm font-semibold text-gray-700 mb-1">
                                                                    Remarks</h4>
                                                                <p class="text-sm bg-gray-50 p-3 rounded-md">
                                                                    {{ $indicatorDetails->RemarksComments }}
                                                                </p>
                                                            </div>
                                                        @endif

                                                        @if ($indicatorDetails->SourceOfData)
                                                            <div>
                                                                <h4 class="text-sm font-semibold text-gray-700 mb-1">
                                                                    Source of Data</h4>
                                                                <p class="text-sm bg-gray-50 p-3 rounded-md">
                                                                    {{ $indicatorDetails->SourceOfData }}
                                                                </p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <div class="text-center py-6 text-gray-500">
                                                        <span class="iconify block mx-auto mb-2 text-3xl"
                                                            data-icon="lucide:file-x"></span>
                                                        <p>No additional details available</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="modal-footer flex justify-end">
                                <button class="btn btn-primary"
                                    onclick="closeDetailModal('detailModal{{ $index }}')">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <!-- Modal Control Script -->
    <script>
        function openDetailModal(modalId) {
            document.getElementById(modalId).classList.add('modal-open');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }

        function closeDetailModal(modalId) {
            document.getElementById(modalId).classList.remove('modal-open');
            document.body.style.overflow = 'auto'; // Restore scrolling
        }
    </script>

    <!-- Chart Initialization -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (!empty($scoreboard) && isset($charts) && !empty($charts))
                // Status Distribution Chart
                @if (isset($charts['statusDistribution']))
                    const statusDistributionData = @json($charts['statusDistribution']['data'] ?? []);
                    if (document.getElementById('statusDistributionChart')) {
                        const statusLabels = statusDistributionData.labels || [];
                        const statusData = (statusDistributionData.datasets && statusDistributionData.datasets[0]
                            .data) || [];
                        const statusColors = statusLabels.map(label => {
                            switch (label) {
                                case 'Exceeded':
                                    return '#10b981';
                                case 'On Track':
                                    return '#0ea5e9';
                                case 'Behind':
                                    return '#f59e0b';
                                case 'Data Error':
                                    return '#ef4444';
                                case 'No Data':
                                    return '#6b7280';
                                case 'No Target':
                                    return '#9ca3af';
                                case 'Informational':
                                    return '#8b5cf6';
                                default:
                                    return '#d1d5db';
                            }
                        });
                        const statusDistributionChart = new ApexCharts(document.getElementById(
                            'statusDistributionChart'), {
                            series: statusData,
                            labels: statusLabels,
                            chart: {
                                type: 'donut',
                                height: 250
                            },
                            colors: statusColors,
                            legend: {
                                position: 'bottom'
                            },
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        width: 300
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }],
                            tooltip: {
                                y: {
                                    formatter: val => val
                                }
                            }
                        });
                        statusDistributionChart.render();
                    }
                @endif

                // Actual vs Target Chart for aggregated mode OR Reporting Completeness for individual mode.
                @if (isset($charts['actualVsTarget']))
                    const actualVsTargetData = @json($charts['actualVsTarget']['data'] ?? []);
                    if (document.getElementById('actualVsTargetChart')) {
                        const indicatorLabels = actualVsTargetData.labels || [];
                        const actualValues = (actualVsTargetData.datasets && actualVsTargetData.datasets[0].data) ||
                            [];
                        const targetValues = (actualVsTargetData.datasets && actualVsTargetData.datasets[1].data) ||
                            [];
                        const actualVsTargetChart = new ApexCharts(document.getElementById('actualVsTargetChart'), {
                            series: [{
                                name: 'Actual',
                                data: actualValues
                            }, {
                                name: 'Target',
                                data: targetValues
                            }],
                            chart: {
                                type: 'bar',
                                height: 250,
                                stacked: false,
                                toolbar: {
                                    show: false
                                }
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: false,
                                    columnWidth: '55%',
                                    endingShape: 'rounded'
                                }
                            },
                            dataLabels: {
                                enabled: false
                            },
                            stroke: {
                                show: true,
                                width: 2,
                                colors: ['transparent']
                            },
                            xaxis: {
                                categories: indicatorLabels,
                                labels: {
                                    show: false
                                }
                            },
                            yaxis: {
                                title: {
                                    text: 'Value'
                                }
                            },
                            fill: {
                                opacity: 1
                            },
                            tooltip: {
                                y: {
                                    formatter: val => val
                                }
                            },
                            colors: ['#0ea5e9', '#10b981']
                        });
                        actualVsTargetChart.render();
                    }
                @elseif (isset($charts['reportingCompleteness']))
                    const reportingCompletenessData = @json($charts['reportingCompleteness']['data'] ?? []);
                    if (document.getElementById('reportingCompletenessChart')) {
                        const completenessLabels = reportingCompletenessData.labels || [];
                        const completenessData = (reportingCompletenessData.datasets && reportingCompletenessData
                            .datasets[0].data) || [];
                        const reportingCompletenessChart = new ApexCharts(document.getElementById(
                            'reportingCompletenessChart'), {
                            series: completenessData,
                            labels: completenessLabels,
                            chart: {
                                type: 'donut',
                                height: 250
                            },
                            legend: {
                                position: 'bottom'
                            },
                            responsive: [{
                                breakpoint: 480,
                                options: {
                                    chart: {
                                        width: 300
                                    },
                                    legend: {
                                        position: 'bottom'
                                    }
                                }
                            }],
                            tooltip: {
                                y: {
                                    formatter: val => val
                                }
                            }
                        });
                        reportingCompletenessChart.render();
                    }
                @endif
            @endif
        });
    </script>
@endif
