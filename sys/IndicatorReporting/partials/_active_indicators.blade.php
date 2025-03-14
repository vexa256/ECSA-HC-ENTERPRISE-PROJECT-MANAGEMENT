<div class="container mx-auto px-4 py-5">
    <!-- Active Indicators & Search -->
    <div class="mb-8">
        <div class="card bg-base-100 shadow-xl rounded-3xl border border-neutral-200 backdrop-blur-sm bg-opacity-90">
            <div class="card-body">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <h2 class="card-title text-xl font-medium text-orange-600">Active Indicators</h2>
                    <div class="form-control w-full max-w-xs mt-2 md:mt-0">
                        <div class="input-group">
                            <span class="btn btn-square bg-orange-50 border-r-0 text-orange-500 rounded-l-2xl shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input type="text" id="indicatorSearch" placeholder="Search indicators..."
                                class="input input-bordered w-full border-l-0 pl-0 focus:outline-none rounded-r-2xl bg-orange-50/50 text-neutral-700" />
                        </div>
                    </div>
                </div>
                <div class="divider my-2 after:bg-orange-200 before:bg-orange-200"></div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="indicatorCardsContainer">
                    @foreach ($indicators as $indicator)
                        <div class="indicator-card-wrapper">
                            <div class="card bg-base-100 border hover:shadow-md transition-shadow cursor-pointer indicator-card"
                                onclick="window.document.getElementById('indicatorModal-{{ $indicator->IID }}').showModal()">
                                <div class="card-body p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h3 class="font-medium text-sm">{{ $indicator->Indicator }}</h3>
                                            <p class="text-xs mt-4  alert pill opacity-70">
                                                {{ $indicator->SecondaryCategory }}</p>
                                        </div>
                                        <div
                                            class="badge {{ isset($existingReports[$indicator->IID]) ? 'badge-success' : 'badge-warning' }} gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                class="inline-block w-4 h-4 stroke-current">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="{{ isset($existingReports[$indicator->IID]) ? 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z' : 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' }}">
                                                </path>
                                            </svg>
                                            {{ isset($existingReports[$indicator->IID]) ? 'Reported' : 'Pending' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@foreach ($indicators as $indicator)
    @php
        $responseValue = old("responses.{$indicator->IID}", $existingReports[$indicator->IID] ?? '');
        $commentValue = old("comments.{$indicator->IID}", $existingComments[$indicator->IID] ?? '');
    @endphp

    <style>
        .modal {
            padding: 0 !important;
        }

        .modal::backdrop {
            background-color: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
        }

        .full-screen-modal {
            width: 100vw;
            height: 100vh;
            max-width: 100vw !important;
            max-height: 100vh !important;
            margin: 0;
            padding: 0;
            border-radius: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .ios-tab {
            transition: all 0.3s ease;
            position: relative;
        }

        .ios-tab[aria-selected="true"]::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(to right, #f97316, #fb923c);
            border-radius: 2px;
        }

        .ios-input {
            background-color: rgba(249, 250, 251, 0.8);
            border: 1px solid rgba(229, 231, 235, 1);
            transition: all 0.2s ease;
        }

        .ios-input:focus {
            background-color: rgba(255, 255, 255, 1);
            border-color: #fb923c;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }

        .ios-card {
            border-radius: 16px;
            border: 1px solid rgba(229, 231, 235, 0.5);
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
        }

        .ios-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-color: rgba(251, 146, 60, 0.3);
        }

        .ios-button {
            border-radius: 12px;
            transition: all 0.2s ease;
            background: linear-gradient(to bottom, rgba(249, 115, 22, 0.9), rgba(234, 88, 12, 0.9));
            border: none;
            color: white;
            font-weight: 500;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .ios-button:hover {
            background: linear-gradient(to bottom, rgba(249, 115, 22, 1), rgba(234, 88, 12, 1));
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }

        .ios-button:active {
            transform: translateY(0);
        }

        .ios-table th {
            font-weight: 600;
            color: #f97316;
            background-color: rgba(255, 247, 237, 0.8);
            border-bottom: 1px solid rgba(251, 146, 60, 0.2);
        }

        .ios-table td {
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
        }

        .ios-table tr:nth-child(even) td {
            background-color: rgba(249, 250, 251, 0.5);
        }
    </style>

    <dialog id="indicatorModal-{{ $indicator->IID }}" class="modal">
        <div class="modal-box full-screen-modal">
            <div class="flex flex-col h-full">
                <!-- Header -->
                <div
                    class="flex justify-between items-center border-b border-neutral-200 p-4 bg-white/90 backdrop-blur-lg sticky top-0 z-10 shadow-sm">
                    <h3 class="font-bold text-lg flex items-center text-orange-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-orange-500" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        {{ $indicator->Indicator }}
                    </h3>
                    <form method="dialog">
                        <button
                            class="btn btn-sm bg-neutral-100 hover:bg-neutral-200 border-none text-neutral-700 rounded-full w-8 h-8 flex items-center justify-center">✕</button>
                    </form>
                </div>

                <!-- Content - Scrollable area -->
                <div class="flex-1 overflow-y-auto p-4">
                    <div role="tablist" class="tabs tabs-bordered mb-4">
                        <input type="radio" name="tabs_{{ $indicator->IID }}" role="tab" class="tab ios-tab"
                            aria-label="Details" checked />
                        <div role="tabpanel" class="tab-content p-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @php
                                    $detailItems = [
                                        'Secondary Category' => $indicator->SecondaryCategory,
                                        'Definition' => $indicator->IndicatorDefinition,
                                        'Question' => $indicator->IndicatorQuestion,
                                        'Remarks' => $indicator->RemarksComments,
                                        'Source of Data' => $indicator->SourceOfData,
                                        'Response Type' => $indicator->ResponseType,
                                        'Reporting Period' => $indicator->ReportingPeriod,
                                        'Expected Target' => $indicator->ExpectedTarget,
                                        'Baseline PAD 2023' => $indicator->BaselinePAD2023,
                                        'Baseline 2024' => $indicator->Baseline2024,
                                        'Target Year One 2024' => $indicator->TargetYearOne2024,
                                        'Target Year Two 2025' => $indicator->TargetYearTwo2025,
                                        'Target Year Three 2026' => $indicator->TargetYearThree2026,
                                        'Target Year Four 2027' => $indicator->TargetYearFour2027,
                                        'Target Year Five 2028' => $indicator->TargetYearFive2028,
                                        'Target Year Six 2029' => $indicator->TargetYearSix2029,
                                        'Target Year Seven 2030' => $indicator->TargetYearSeven2030,
                                    ];
                                @endphp
                                @foreach ($detailItems as $label => $value)
                                    <div class="ios-card p-4 shadow-sm">
                                        <h4 class="text-xs font-medium text-orange-500 mb-1">{{ $label }}</h4>
                                        @if (strlen($value) > 100)
                                            <p class="text-sm collapsed text-neutral-700"
                                                id="{{ Str::slug($label) }}-{{ $indicator->IID }}"
                                                style="max-height: 3em; overflow: hidden;">
                                                {{ $value }}
                                            </p>
                                            <button
                                                class="text-xs font-medium text-orange-600 hover:text-orange-700 mt-1 transition-colors"
                                                onclick="toggleCollapse('{{ Str::slug($label) }}-{{ $indicator->IID }}', event)">
                                                Show More
                                            </button>
                                        @else
                                            <p class="text-sm text-neutral-700">{{ $value }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <input type="radio" name="tabs_{{ $indicator->IID }}" role="tab" class="tab ios-tab"
                            aria-label="Reporting" />
                        <div role="tabpanel" class="tab-content p-4">
                            <form action="{{ route('indicator.submit') }}" method="POST"
                                id="form-{{ $indicator->IID }}">
                                @csrf
                                <input type="hidden" name="entity_id" value="{{ $entity->EntityID }}">
                                <input type="hidden" name="reporting_period" value="{{ $timeline->ReportingID }}">

                                @if ($indicator->EntityID === 'RRF')
                                    <div
                                        class="alert bg-orange-50 border border-orange-200 rounded-2xl mb-4 text-orange-700">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="stroke-orange-500 shrink-0 h-6 w-6" fill="none"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        <span>({{ $indicator->IndicatorQuestion }})</span>
                                    </div>
                                @endif

                                <div class="form-control w-full mb-4">
                                    <label class="label">
                                        <span class="label-text font-medium text-orange-700">Your Response</span>
                                    </label>

                                    @switch($indicator->ResponseType)
                                        @case('Text')
                                            <textarea class="textarea ios-input h-24 rounded-2xl" name="responses[{{ $indicator->IID }}]"
                                                placeholder="Enter text response" {{ $timeline->status === 'Completed' ? 'disabled' : '' }}>{{ old("responses.{$indicator->IID}", $existingReports[$indicator->IID] ?? '') }}</textarea>
                                        @break

                                        @case('Number')
                                            <input type="number" class="input ios-input w-full rounded-2xl"
                                                name="responses[{{ $indicator->IID }}]" placeholder="Enter a number"
                                                value="{{ old("responses.{$indicator->IID}", $existingReports[$indicator->IID] ?? '') }}"
                                                {{ $timeline->status === 'Completed' ? 'disabled' : '' }}>
                                        @break

                                        @case('Boolean')
                                            <select class="select ios-input w-full rounded-2xl"
                                                name="responses[{{ $indicator->IID }}]"
                                                {{ $timeline->status === 'Completed' ? 'disabled' : '' }}>
                                                <option value="">Select an option</option>
                                                <option value="1"
                                                    {{ old("responses.{$indicator->IID}", $existingReports[$indicator->IID] ?? '') === '1' ? 'selected' : '' }}>
                                                    True</option>
                                                <option value="0"
                                                    {{ old("responses.{$indicator->IID}", $existingReports[$indicator->IID] ?? '') === '0' ? 'selected' : '' }}>
                                                    False</option>
                                            </select>
                                        @break

                                        @case('Percentage')
                                            <label class="relative">
                                                <input type="number" class="input ios-input w-full pr-10 rounded-2xl"
                                                    name="responses[{{ $indicator->IID }}]" placeholder="Enter percentage"
                                                    value="{{ old("responses.{$indicator->IID}", $existingReports[$indicator->IID] ?? '') }}"
                                                    min="0" max="100" step="0.01"
                                                    {{ $timeline->status === 'Completed' ? 'disabled' : '' }}>
                                                <span
                                                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-neutral-500">%</span>
                                            </label>
                                        @break

                                        @case('Yes/No')
                                            <select class="select ios-input w-full rounded-2xl"
                                                name="responses[{{ $indicator->IID }}]"
                                                {{ $timeline->status === 'Completed' ? 'disabled' : '' }}>
                                                <option value="">Select an option</option>
                                                <option value="Yes"
                                                    {{ old("responses.{$indicator->IID}", $existingReports[$indicator->IID] ?? '') === 'Yes' ? 'selected' : '' }}>
                                                    Yes</option>
                                                <option value="No"
                                                    {{ old("responses.{$indicator->IID}", $existingReports[$indicator->IID] ?? '') === 'No' ? 'selected' : '' }}>
                                                    No</option>
                                            </select>
                                        @break

                                        @default
                                            <input type="text" class="input ios-input w-full rounded-2xl"
                                                name="responses[{{ $indicator->IID }}]" placeholder="Enter your response"
                                                value="{{ old("responses.{$indicator->IID}", $existingReports[$indicator->IID] ?? '') }}"
                                                {{ $timeline->status === 'Completed' ? 'disabled' : '' }}>
                                    @endswitch
                                </div>

                                <div class="form-control w-full mb-4">
                                    <label class="label">
                                        <span class="label-text font-medium text-orange-700">Additional Comments</span>
                                    </label>
                                    <textarea required class="textarea ios-input h-24 rounded-2xl" name="comments[{{ $indicator->IID }}]"
                                        placeholder="Provide context" {{ $timeline->status === 'Completed' ? 'disabled' : '' }}>{{ old("comments.{$indicator->IID}", $existingComments[$indicator->IID] ?? '') }}</textarea>
                                </div>

                                @if ($timeline->status !== 'Completed')
                                    <button type="submit" class="ios-button btn px-6 py-2.5 h-auto"
                                        onclick="disableSubmitButton('form-{{ $indicator->IID }}', this)">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                        </svg>
                                        Save Response
                                    </button>
                                @endif
                            </form>
                        </div>

                        <input type="radio" name="tabs_{{ $indicator->IID }}" role="tab" class="tab ios-tab"
                            aria-label="History" />
                        <div role="tabpanel" class="tab-content p-4">
                            <!-- Target Metrics Summary -->
                            <div class="overflow-x-auto mb-6 rounded-2xl shadow-sm border border-neutral-200">

                                @if ($indicator->EntityID !== 'RRF')
                                    <table class="table table-zebra table-xs ios-table w-full">
                                        <thead>
                                            <tr>
                                                <th class="rounded-tl-2xl">Baseline PAD 2023</th>
                                                <th>Baseline 2024</th>
                                                <th>Target 1 (2024)</th>
                                                <th>Target 2 (2025)</th>
                                                <th>Target 3 (2026)</th>
                                                <th>Target 4 (2027)</th>
                                                <th>Target 5 (2028)</th>
                                                <th>Target 6 (2029)</th>
                                                <th>Target 7 (2030)</th>
                                                <th class="rounded-tr-2xl">Expected Target</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>{{ $indicator->BaselinePAD2023 }}</td>
                                                <td>{{ $indicator->Baseline2024 }}</td>
                                                <td>{{ $indicator->TargetYearOne2024 }}</td>
                                                <td>{{ $indicator->TargetYearTwo2025 }}</td>
                                                <td>{{ $indicator->TargetYearThree2026 }}</td>
                                                <td>{{ $indicator->TargetYearFour2027 }}</td>
                                                <td>{{ $indicator->TargetYearFive2028 }}</td>
                                                <td>{{ $indicator->TargetYearSix2029 }}</td>
                                                <td>{{ $indicator->TargetYearSeven2030 }}</td>
                                                <td>{{ $indicator->ExpectedTarget }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                @endif
                            </div>

                            <!-- Historical Reports Table -->
                            @if (isset($indicator->history) && count($indicator->history))
                                <div class="overflow-x-auto rounded-2xl shadow-sm border border-neutral-200">
                                    <table class="table ios-table w-full">
                                        <thead>
                                            <tr>
                                                <th class="rounded-tl-2xl">Report Name</th>
                                                <th>Year</th>
                                                <th>Response</th>
                                                <th>Comments</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($indicator->history as $hist)
                                                <tr>
                                                    <td>{{ $hist->ReportName }}</td>
                                                    <td>{{ $hist->Year }}</td>
                                                    <td>{{ $hist->Response }}</td>
                                                    <td>{{ $hist->Comments }}</td>

                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div
                                    class="alert bg-blue-50 border border-blue-100 text-blue-700 rounded-2xl shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        class="stroke-blue-500 shrink-0 w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>No historical data available for this indicator.</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer - Sticky at bottom -->
                <div class="border-t border-neutral-200 p-4 bg-white/90 backdrop-blur-lg mt-auto">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="text-xs text-neutral-500 max-w-3xl">
                            Once saved, your response is recorded and is exclusively tied to the selected report
                            (<span class="font-semibold text-orange-600">{{ $timeline->ReportName }}</span>) and year
                            (<span class="font-semibold text-orange-600">{{ $timeline->Year }}</span>). You can update
                            it until the
                            reporting period is marked as Completed.
                        </div>
                        <form method="dialog" class="ml-auto">
                            <button
                                class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 border-none rounded-2xl shadow-sm transition-all hover:shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Close
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function setModalHeight() {
                const modal = document.querySelector('.full-screen-modal');
                if (modal) {
                    const vh = window.innerHeight * 0.01;
                    document.documentElement.style.setProperty('--vh', `${vh}px`);
                    modal.style.height = `calc(var(--vh, 1vh) * 100)`;
                }
            }

            // Set the height initially and on resize
            window.addEventListener('resize', setModalHeight);
            setModalHeight();

            // If you're using a framework that dynamically renders the modal,
            // you might need to call setModalHeight() after the modal is opened
            document.getElementById('indicatorModal-{{ $indicator->IID }}').addEventListener('show',
                setModalHeight);

        });
    </script>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize charts if they exist
        if (document.getElementById('reportingProgressChart')) {
            var ctx = document.getElementById('reportingProgressChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Total', 'Reported', 'Remaining'],
                    datasets: [{
                        label: 'Indicators',
                        data: [{{ $totalIndicators }}, {{ $reportedIndicators }},
                            {{ $totalIndicators - $reportedIndicators }}
                        ],
                        borderColor: '#f97316',
                        backgroundColor: 'rgba(249, 115, 22, 0.2)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        if (document.getElementById('completionBreakdownChart')) {
            var ctx2 = document.getElementById('completionBreakdownChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Reported', 'Remaining'],
                    datasets: [{
                        data: [{{ $reportedIndicators }},
                            {{ $totalIndicators - $reportedIndicators }}
                        ],
                        backgroundColor: ['#22c55e', '#f97316']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 15,
                                font: {
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    function disableSubmitButton(formId, btn) {
        btn.disabled = true;
        btn.innerHTML =
            '<svg class="animate-spin -ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...';
        document.getElementById(formId).submit();
    }

    document.getElementById('indicatorSearch').addEventListener('keyup', function() {
        var query = this.value.toLowerCase();
        document.querySelectorAll('.indicator-card-wrapper').forEach(function(card) {
            var indicatorName = card.querySelector('.font-medium').textContent.toLowerCase();
            var indicatorCategory = card.querySelector('.opacity-70').textContent.toLowerCase();
            card.style.display = (indicatorName.includes(query) || indicatorCategory.includes(query)) ?
                "" : "none";
        });
    });

    function toggleCollapse(id, event) {
        event.preventDefault();
        var element = document.getElementById(id);
        var button = event.target;

        if (element.classList.contains('collapsed')) {
            element.style.maxHeight = 'none';
            element.classList.remove('collapsed');
            button.textContent = 'Show Less';
        } else {
            element.style.maxHeight = '3em';
            element.classList.add('collapsed');
            button.textContent = 'Show More';
        }
    }
</script>
