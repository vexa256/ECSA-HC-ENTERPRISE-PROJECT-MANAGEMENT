@php
    /*
    This view removes all baseline/extra columns and focuses on:
      - The selected report year’s target (if any),
      - The user’s historical submissions (report name, response, comment),
      - A wizard approach for quick navigation:
          1) Select an indicator,
          2) Show the target for the selected year & let user pick a past report,
          3) Display the selected report’s performance details.
    */
    $timelineSums = [];
    $entitySums = [];

    foreach ($analyticsData as $tData) {
        $timeline = $tData['timeline'];
        $timelineId = $timeline->id;

        if (!isset($timelineSums[$timelineId])) {
            $timelineSums[$timelineId] = [
                'name' => $timeline->ReportName ?? 'Untitled Report',
                'sum' => 0,
                'count' => 0,
            ];
        }

        foreach ($tData['entities'] as $entData) {
            $entityObj = $entData['entity'];
            $completeness = (float) $entData['completeness'];

            $timelineSums[$timelineId]['sum'] += $completeness;
            $timelineSums[$timelineId]['count']++;

            $entityId = $entityObj->EntityID;
            if (!isset($entitySums[$entityId])) {
                $entitySums[$entityId] = [
                    'name' => $entityObj->Entity,
                    'sum' => 0,
                    'count' => 0,
                ];
            }
            $entitySums[$entityId]['sum'] += $completeness;
            $entitySums[$entityId]['count']++;
        }
    }

    $timelineChartLabels = [];
    $timelineChartData = [];
    foreach ($timelineSums as $tid => $info) {
        if ($info['count'] > 0) {
            $avg = round($info['sum'] / $info['count'], 2);
            $timelineChartLabels[] = $info['name'];
            $timelineChartData[] = $avg;
        }
    }

    $entityChartLabels = [];
    $entityChartData = [];
    foreach ($entitySums as $eid => $info) {
        if ($info['count'] > 0) {
            $avg = round($info['sum'] / $info['count'], 2);
            $entityChartLabels[] = $info['name'];
            $entityChartData[] = $avg;
        }
    }

    // Map year->column for target
    $yearToColumnMap = [
        '2023' => 'BaselinePAD2023',
        '2024' => 'TargetYearOne2024',
        '2025' => 'TargetYearTwo2025',
        '2026' => 'TargetYearThree2026',
        '2027' => 'TargetYearFour2027',
        '2028' => 'TargetYearFive2028',
        '2029' => 'TargetYearSix2029',
        '2030' => 'TargetYearSeven2030',
    ];
    $highlightColumn = $yearToColumnMap[$selectedYear] ?? null;

    // Define an array of 20 safe, non-controversial Iconify icon names.
    $iosIcons = [
        'mdi:account-circle',
        'mdi:airplane',
        'mdi:alarm',
        'mdi:apple',
        'mdi:bag-personal',
        'mdi:bank',
        'mdi:camera',
        'mdi:car',
        'mdi:cellphone',
        'mdi:clock',
        'mdi:coffee',
        'mdi:compass',
        'mdi:credit-card',
        'mdi:database',
        'mdi:email',
        'mdi:filmstrip',
        'mdi:fire',
        'mdi:gamepad',
        'mdi:gift',
        'mdi:heart',
    ];
@endphp

<!-- Include Iconify and List.js CDNs -->
{{-- <script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/2.3.1/list.min.js"></script>

<div class="container mx-auto py-6 bg-gray-50">
    <div class="text-center mb-8">
        <h2 class="text-primary font-bold text-3xl">Reporting Completeness for Year: {{ $selectedYear }}</h2>
        <p class="text-gray-600 mx-auto max-w-2xl">
            Below is an overview of reporting completeness across timelines and entities.
            Click <strong>"View Data"</strong> on any card for detailed insights.
        </p>
    </div>

    <!-- Charts Section -->
    <div class="grid md:grid-cols-2 gap-6 mb-8">
        <div class="card bg-white shadow-lg border border-gray-200">
            <div class="card-body p-6">
                <h5 class="card-title text-primary text-xl mb-4">Avg Completeness by Timeline</h5>
                <div id="chartTimeline"></div>
            </div>
        </div>
        <div class="card bg-white shadow-lg border border-gray-200">
            <div class="card-body p-6">
                <h5 class="card-title text-primary text-xl mb-4">Avg Completeness by Entity</h5>
                <div id="chartEntities"></div>
            </div>
        </div>
    </div>

    <!-- Timeline Cards -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($analyticsData as $idx => $data)
            @php
                $timeline = $data['timeline'];
                $entities = $data['entities'];
            @endphp
            <div class="card bg-white shadow-md border border-gray-200">
                <div class="card-body p-6">
                    <h4 class="card-title text-primary text-2xl mb-2">
                        {{ $timeline->ReportName ?? 'Untitled Report' }}
                    </h4>
                    <p class="text-gray-600 text-sm mb-4">
                        Reporting Type: {{ $timeline->Type }} <br>
                        Year: {{ $timeline->Year }}
                    </p>
                    @if (!empty($timeline->Description))
                        <p class="text-gray-500 text-xs mb-4">{{ $timeline->Description }}</p>
                    @endif
                    <!-- Modal Trigger Button -->
                    <label for="modalTimeline{{ $timeline->id }}" class="btn btn-neutral btn-sm cursor-pointer">
                        <i class="fas fa-chart-line mr-1"></i> View Data
                    </label>
                </div>
            </div>

            <!-- Full Screen Modal (iOS–themed) -->
            <input type="checkbox" id="modalTimeline{{ $timeline->id }}" class="modal-toggle" />
            <div class="modal overflow-hidden">
                <div class="modal-box w-full h-full max-w-full max-h-full p-0 m-0 border-0 shadow-2xl flex flex-col">
                    <!-- Modal Header -->
                    <div class="px-6 py-4 flex items-center justify-between border-b border-gray-300 bg-white">
                        <h3 class="font-bold text-gray-800 text-2xl">
                            {{ $timeline->ReportName ?? 'Untitled Report' }} | {{ $timeline->Type }} -
                            {{ $timeline->Year }}
                        </h3>
                        <!-- Close Button -->
                        <label for="modalTimeline{{ $timeline->id }}"
                            class="btn btn-neutral text-2xl cursor-pointer">&times;</label>
                    </div>
                    <!-- Modal Body (scrollable) -->
                    <div class="flex-1 overflow-y-auto p-6 bg-white">
                        @if (count($entities) === 0)
                            <p class="text-center text-error font-bold">No entities found.</p>
                        @else
                            @php
                                $sumIndicators = 0;
                                foreach ($entities as $ent) {
                                    $sumIndicators += $ent['expectedCount'];
                                }
                            @endphp

                            @if ($sumIndicators === 0)
                                <div class="alert alert-warning text-center">
                                    <h5>No Indicators Found</h5>
                                </div>
                            @else
                                <!-- Entity Tabs -->
                                <div class="tabs tabs-boxed mb-4" id="entityTabs-{{ $timeline->id }}">
                                    @foreach ($entities as $eIndex => $entData)
                                        @php
                                            $entObj = $entData['entity'];
                                        @endphp
                                        <a href="javascript:void(0)"
                                            data-target="#entity-{{ $timeline->id }}-{{ $entObj->id }}"
                                            class="tab @if ($eIndex === 0) tab-active @endif">
                                            {{ $entObj->Entity }}
                                        </a>
                                    @endforeach
                                </div>

                                <div class="entity-content">
                                    @foreach ($entities as $eIndex => $entData)
                                        @php
                                            $entObj = $entData['entity'];
                                            $completeness = $entData['completeness'];
                                            $expectedCount = $entData['expectedCount'];
                                            $reportedCount = $entData['reportedCount'];
                                            $missingIndicators = $entData['missingIndicators'];
                                            $expectedIndicators = $entData['expectedIndicators'];
                                            $historicalData = $entData['historicalData'];
                                        @endphp

                                        <div id="entity-{{ $timeline->id }}-{{ $entObj->id }}"
                                            class="entity-tab @if ($eIndex !== 0) hidden @endif">
                                            @if ($expectedCount === 0)
                                                <p class="text-error">
                                                    No indicators found for <strong>{{ $entObj->Entity }}</strong>.
                                                </p>
                                            @else
                                                <!-- Performance Summary (iOS style) -->
                                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                                    <div class="border border-gray-300 p-4 rounded text-center">
                                                        <span class="text-gray-600 text-sm">Expected</span>
                                                        <h4 class="text-green-700 font-bold text-3xl">
                                                            {{ $expectedCount }}</h4>
                                                    </div>
                                                    <div class="border border-gray-300 p-4 rounded text-center">
                                                        <span class="text-gray-600 text-sm">Reported</span>
                                                        <h4 class="text-blue-700 font-bold text-3xl">
                                                            {{ $reportedCount }}</h4>
                                                    </div>
                                                    <div class="border border-gray-300 p-4 rounded text-center">
                                                        <span class="text-gray-600 text-sm">Completeness(%)</span>
                                                        <h4 class="text-primary font-bold text-3xl">{{ $completeness }}
                                                        </h4>
                                                    </div>
                                                </div>

                                                <!-- Inner Tabs: Overview, Missing, Historical Data Wizard -->
                                                <div class="tabs tabs-boxed mb-4 inner-tabs"
                                                    id="innerTabs-{{ $timeline->id }}-{{ $entObj->id }}">
                                                    <a href="javascript:void(0)"
                                                        data-target="#ov-{{ $timeline->id }}-{{ $entObj->id }}"
                                                        class="tab tab-active">Overview</a>
                                                    <a href="javascript:void(0)"
                                                        data-target="#miss-{{ $timeline->id }}-{{ $entObj->id }}"
                                                        class="tab">Missing</a>
                                                    <a href="javascript:void(0)"
                                                        data-target="#hist-{{ $timeline->id }}-{{ $entObj->id }}"
                                                        class="tab">Historical Data Wizard</a>
                                                </div>

                                                <div class="inner-tab-content">
                                                    <!-- Overview Content with iOS–like bordered, paginated list (4 per page) using List.js -->
                                                    <div id="ov-{{ $timeline->id }}-{{ $entObj->id }}"
                                                        class="p-4 inner-tab block">
                                                        <h5 class="font-bold text-primary text-xl mb-3">
                                                            {{ $entObj->Entity }} – Overview</h5>
                                                        <h6 class="text-gray-600 mb-2 text-sm">All Expected Indicators
                                                            ({{ $expectedCount }})
                                                            :</h6>
                                                        <div id="indicatorList-{{ $timeline->id }}-{{ $entObj->id }}"
                                                            class="indicator-list border border-gray-200 rounded p-4">
                                                            <!-- Optional search input -->
                                                            <input class="search input input-bordered mb-4 w-full"
                                                                placeholder="Search indicators" />
                                                            <ul class="list divide-y divide-gray-200">
                                                                @foreach ($expectedIndicators as $ind)
                                                                    <li class="flex items-center p-3">
                                                                        <span class="iconify text-xl mr-3"
                                                                            data-icon="{{ $iosIcons[array_rand($iosIcons)] }}"
                                                                            data-inline="false"></span>
                                                                        <span
                                                                            class="indicator-name flex-1 text-gray-800">{{ $ind->Indicator }}</span>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                            <ul class="pagination mt-4 flex justify-center gap-2"></ul>
                                                        </div>
                                                    </div>

                                                    <!-- Missing Content with List.js pagination -->
                                                    <div id="miss-{{ $timeline->id }}-{{ $entObj->id }}"
                                                        class="p-4 inner-tab hidden">
                                                        <h5 class="font-bold text-warning text-xl mb-3">Missing
                                                            ({{ count($missingIndicators) }})</h5>
                                                        @if (count($missingIndicators) === 0)
                                                            <p class="text-green-700">All required indicators are
                                                                reported.</p>
                                                        @else
                                                            <div id="missingIndicatorList-{{ $timeline->id }}-{{ $entObj->id }}"
                                                                class="indicator-list border border-gray-200 rounded p-4">
                                                                <input class="search input input-bordered mb-4 w-full"
                                                                    placeholder="Search missing indicators" />
                                                                <table class="table table-zebra table-compact w-full">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Indicator</th>
                                                                            <th>Primary Cat</th>
                                                                            <th>Secondary Cat</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="list">
                                                                        @foreach ($missingIndicators as $mInd)
                                                                            <tr>
                                                                                <td class="text-sm indicator-name">
                                                                                    {{ $mInd->Indicator }}</td>
                                                                                <td class="text-sm">
                                                                                    {{ $mInd->PrimaryCategory }}</td>
                                                                                <td class="text-sm">
                                                                                    {{ $mInd->SecondaryCategory }}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                                <ul class="pagination mt-4 flex justify-center gap-2">
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Historical Data Wizard -->
                                                    <div id="hist-{{ $timeline->id }}-{{ $entObj->id }}"
                                                        class="p-4 inner-tab hidden">
                                                        <h5 class="font-bold text-secondary text-xl mb-3">Intelligent
                                                            Wizard: Indicator Performance</h5>
                                                        <p class="text-gray-600 text-sm mb-4">
                                                            Quickly navigate an indicator’s target for the selected year
                                                            and see any past reports (name, response, comment).
                                                        </p>

                                                        <div class="wizard-container"
                                                            id="wizard-{{ $timeline->id }}-{{ $entObj->id }}">
                                                            <!-- Wizard Step Indicators -->
                                                            <div
                                                                class="flex justify-center items-center space-x-6 mb-4">
                                                                <div class="wizard-step-item active" data-step="1">
                                                                    <span
                                                                        class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold">1</span>
                                                                    <span class="text-sm font-medium block mt-1">Select
                                                                        Indicator</span>
                                                                </div>
                                                                <div class="wizard-step-item" data-step="2">
                                                                    <span
                                                                        class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold">2</span>
                                                                    <span class="text-sm font-medium block mt-1">Select
                                                                        Past Report</span>
                                                                </div>
                                                                <div class="wizard-step-item" data-step="3">
                                                                    <span
                                                                        class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-bold">3</span>
                                                                    <span
                                                                        class="text-sm font-medium block mt-1">Performance
                                                                        Details</span>
                                                                </div>
                                                            </div>

                                                            <!-- Wizard Step 1 -->
                                                            <div class="wizard-step-content show" data-step="1">
                                                                <div class="mb-4">
                                                                    <label class="label font-bold">
                                                                        <span class="label-text">Choose an
                                                                            Indicator:</span>
                                                                    </label>
                                                                    <select
                                                                        class="select select-bordered w-full shadow-sm rounded wizardIndicatorSelect"
                                                                        data-indicators='@json($expectedIndicators)'
                                                                        data-hist='@json($historicalData)'
                                                                        data-highlightcol='{{ $highlightColumn }}'>
                                                                        <option value="" selected disabled>
                                                                            Select...</option>
                                                                        @foreach ($expectedIndicators as $oneInd)
                                                                            <option value="{{ $oneInd->IID }}">
                                                                                {{ $oneInd->Indicator }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                <button class="btn btn-neutral wizard-next-btn"
                                                                    disabled>
                                                                    Next <i class="fas fa-arrow-right ml-1"></i>
                                                                </button>
                                                            </div>

                                                            <!-- Wizard Step 2 -->
                                                            <div class="wizard-step-content" data-step="2">
                                                                <div
                                                                    class="mb-4 border border-gray-300 rounded p-4 bg-gray-50">
                                                                    <div class="mb-2">
                                                                        <strong>Selected Year’s Target:</strong>
                                                                        <span
                                                                            id="wizSelectedYearTarget-{{ $timeline->id }}-{{ $entObj->id }}"
                                                                            class="ml-2 font-bold text-primary"></span>
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <strong>Indicator:</strong>
                                                                        <span
                                                                            id="wizIndicatorName-{{ $timeline->id }}-{{ $entObj->id }}"
                                                                            class="ml-2"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="mb-4">
                                                                    <label class="label font-bold">
                                                                        <span class="label-text">Select a Past Report
                                                                            to View Performance:</span>
                                                                    </label>
                                                                    <select
                                                                        class="select select-bordered w-full shadow-sm rounded wizardReportSelect"
                                                                        disabled>
                                                                        <option value="" selected disabled>Select
                                                                            a Report</option>
                                                                    </select>
                                                                </div>
                                                                <div class="flex justify-between">
                                                                    <button class="btn btn-neutral wizard-prev-btn">
                                                                        <i class="fas fa-arrow-left mr-1"></i> Back
                                                                    </button>
                                                                    <button class="btn btn-neutral wizard-next-btn"
                                                                        disabled>
                                                                        Next <i class="fas fa-arrow-right ml-1"></i>
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <!-- Wizard Step 3 -->
                                                            <div class="wizard-step-content" data-step="3">
                                                                <div
                                                                    class="border border-gray-300 rounded p-4 bg-gray-50 mb-4">
                                                                    <div class="font-bold text-base mb-2">
                                                                        <span
                                                                            id="wizReportName-{{ $timeline->id }}-{{ $entObj->id }}"></span>
                                                                    </div>
                                                                    <div class="mb-1">
                                                                        <strong>Response:</strong>
                                                                        <span
                                                                            id="wizResponse-{{ $timeline->id }}-{{ $entObj->id }}"></span>
                                                                    </div>
                                                                    <div class="mb-1">
                                                                        <strong>Comment:</strong>
                                                                        <span
                                                                            id="wizComment-{{ $timeline->id }}-{{ $entObj->id }}"></span>
                                                                    </div>
                                                                    <div class="text-xs text-gray-500">
                                                                        Reported By:
                                                                        <span
                                                                            id="wizReportedBy-{{ $timeline->id }}-{{ $entObj->id }}"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="flex justify-between">
                                                                    <button class="btn btn-neutral wizard-prev-btn">
                                                                        <i class="fas fa-arrow-left mr-1"></i> Back
                                                                    </button>
                                                                    <button
                                                                        class="btn btn-neutral wizard-finish-btn">Finish</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- End Wizard -->
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                    <!-- Modal Footer (fixed) -->
                    <div class="border-t border-gray-300 px-6 py-4 bg-white flex justify-end">
                        <label for="modalTimeline{{ $timeline->id }}" class="btn btn-neutral cursor-pointer">
                            Close
                        </label>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@latest"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart: Timelines using a vibrant Material Purple color
        var timelineOptions = {
            series: [{
                name: "Completeness",
                data: @json($timelineChartData)
            }],
            chart: {
                type: 'bar',
                height: 300
            },
            xaxis: {
                categories: @json($timelineChartLabels)
            },
            yaxis: {
                max: 100,
                labels: {
                    formatter: (val) => val + "%"
                }
            },
            dataLabels: {
                enabled: true,
                formatter: (val) => val + "%"
            },
            title: {
                text: 'Avg Completeness by Timeline',
                align: 'center'
            },
            colors: ['#9C27B0'],
            plotOptions: {
                bar: {
                    borderRadius: 4
                }
            }
        };
        new ApexCharts(document.querySelector("#chartTimeline"), timelineOptions).render();

        // Chart: Entities using a bold Material Red color
        var entityOptions = {
            series: [{
                name: "Completeness",
                data: @json($entityChartData)
            }],
            chart: {
                type: 'bar',
                height: 300
            },
            xaxis: {
                categories: @json($entityChartLabels)
            },
            yaxis: {
                max: 100,
                labels: {
                    formatter: (val) => val + "%"
                }
            },
            dataLabels: {
                enabled: true,
                formatter: (val) => val + "%"
            },
            title: {
                text: 'Avg Completeness by Entity',
                align: 'center'
            },
            colors: ['#F44336'],
            plotOptions: {
                bar: {
                    borderRadius: 4
                }
            }
        };
        new ApexCharts(document.querySelector("#chartEntities"), entityOptions).render();
    });
</script>

<!-- Tab Switching JS -->
<script>
    // Handle both entity-level and inner tab switching
    document.addEventListener('DOMContentLoaded', function() {
        // Entity-level tabs
        document.querySelectorAll('[id^="entityTabs-"]').forEach(function(tabContainer) {
            tabContainer.querySelectorAll('a.tab').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    const targetId = tab.getAttribute('data-target');
                    // Remove active class from siblings
                    tabContainer.querySelectorAll('a.tab').forEach(function(sibling) {
                        sibling.classList.remove('tab-active');
                    });
                    tab.classList.add('tab-active');
                    // Find parent container of entity content
                    const entityContent = tabContainer.nextElementSibling;
                    if (entityContent) {
                        entityContent.querySelectorAll('.entity-tab').forEach(function(
                            content) {
                            content.classList.add('hidden');
                        });
                        document.querySelector(targetId).classList.remove('hidden');
                    }
                });
            });
        });

        // Inner tabs (Overview, Missing, Wizard)
        document.querySelectorAll('[id^="innerTabs-"]').forEach(function(innerTabContainer) {
            innerTabContainer.querySelectorAll('a.tab').forEach(function(tab) {
                tab.addEventListener('click', function() {
                    const targetId = tab.getAttribute('data-target');
                    // Remove active class from siblings
                    innerTabContainer.querySelectorAll('a.tab').forEach(function(
                        sibling) {
                        sibling.classList.remove('tab-active');
                    });
                    tab.classList.add('tab-active');
                    // Find inner tab content container (sibling of the inner tabs)
                    const innerContent = innerTabContainer.nextElementSibling;
                    if (innerContent) {
                        innerContent.querySelectorAll('.inner-tab').forEach(function(
                            content) {
                            content.classList.add('hidden');
                        });
                        document.querySelector(targetId).classList.remove('hidden');
                    }
                });
            });
        });
    });
</script>

<!-- Wizard JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const wizardContainers = document.querySelectorAll('.wizard-container');

        wizardContainers.forEach((wizardEl) => {
            let currentStep = 1;
            const stepContents = wizardEl.querySelectorAll('.wizard-step-content');
            const stepItems = wizardEl.querySelectorAll('.wizard-step-item');

            const nextBtns = wizardEl.querySelectorAll('.wizard-next-btn');
            const prevBtns = wizardEl.querySelectorAll('.wizard-prev-btn');
            const finishBtn = wizardEl.querySelector('.wizard-finish-btn');

            // Step 1 Elements
            const indicatorSelect = wizardEl.querySelector('.wizardIndicatorSelect');

            // Step 2 Elements
            const reportSelect = wizardEl.querySelector('.wizardReportSelect');
            const yearTargetEl = wizardEl.querySelector(
                `#wizSelectedYearTarget-${wizardEl.id.split('-').slice(1).join('-')}`);
            const indicatorNameEl = wizardEl.querySelector(
                `#wizIndicatorName-${wizardEl.id.split('-').slice(1).join('-')}`);

            // Step 3 Elements
            const reportNameEl = wizardEl.querySelector(
                `#wizReportName-${wizardEl.id.split('-').slice(1).join('-')}`);
            const responseEl = wizardEl.querySelector(
                `#wizResponse-${wizardEl.id.split('-').slice(1).join('-')}`);
            const commentEl = wizardEl.querySelector(
                `#wizComment-${wizardEl.id.split('-').slice(1).join('-')}`);
            const reportedByEl = wizardEl.querySelector(
                `#wizReportedBy-${wizardEl.id.split('-').slice(1).join('-')}`);

            // Show step
            function goToStep(step) {
                stepContents.forEach((item) => item.classList.remove('show'));
                stepItems.forEach((sItem) => sItem.classList.remove('active'));

                const targetContent = wizardEl.querySelector(
                    `.wizard-step-content[data-step="${step}"]`);
                const targetItem = wizardEl.querySelector(`.wizard-step-item[data-step="${step}"]`);
                if (targetContent && targetItem) {
                    targetContent.classList.add('show');
                    targetItem.classList.add('active');
                }
                currentStep = step;
            }

            // Step 1: On Indicator select
            if (indicatorSelect) {
                indicatorSelect.addEventListener('change', function() {
                    const step1Next = wizardEl.querySelector(
                        '.wizard-step-content[data-step="1"] .wizard-next-btn');
                    if (step1Next) step1Next.disabled = false;
                });
            }

            // Next Buttons
            nextBtns.forEach((btn) => {
                btn.addEventListener('click', function() {
                    if (currentStep === 1) {
                        // We have chosen an indicator
                        const indicatorsData = JSON.parse(indicatorSelect.dataset
                            .indicators || '[]');
                        const histData = JSON.parse(indicatorSelect.dataset.hist ||
                            '{}');
                        const highlightCol = indicatorSelect.dataset.highlightcol;
                        const chosenIID = indicatorSelect.value;

                        // Find the chosen indicator
                        let foundIndicator = null;
                        for (let i = 0; i < indicatorsData.length; i++) {
                            if (indicatorsData[i].IID === chosenIID) {
                                foundIndicator = indicatorsData[i];
                                break;
                            }
                        }

                        if (foundIndicator) {
                            // Step 2: Show the target for the selected year
                            let yearTargetVal = highlightCol && foundIndicator[
                                highlightCol] ? foundIndicator[highlightCol] : 'N/A';
                            if (yearTargetEl) {
                                yearTargetEl.textContent = yearTargetVal;
                            }
                            if (indicatorNameEl) {
                                indicatorNameEl.textContent = foundIndicator.Indicator;
                            }

                            // Populate the report dropdown from histData
                            if (reportSelect) {
                                reportSelect.innerHTML =
                                    `<option value="" disabled selected>Select a Report</option>`;
                                const chosenHistRows = histData[chosenIID] || [];
                                if (chosenHistRows.length > 0) {
                                    chosenHistRows.forEach((row, idx) => {
                                        const opt = document.createElement(
                                            'option');
                                        opt.value = idx;
                                        opt.textContent = row.ReportName ?
                                            `${row.ReportName} (${row.Year})` :
                                            `Unnamed Report (${row.Year})`;
                                        reportSelect.appendChild(opt);
                                    });
                                    reportSelect.disabled = false;
                                } else {
                                    reportSelect.disabled = true;
                                }
                            }

                            // Disable step 2 next until report is selected
                            const step2Next = wizardEl.querySelector(
                                '.wizard-step-content[data-step="2"] .wizard-next-btn'
                            );
                            if (step2Next) {
                                step2Next.disabled = true;
                            }
                        }
                    } else if (currentStep === 2) {
                        // User has selected a past report
                        const chosenIID = indicatorSelect.value;
                        const histData = JSON.parse(indicatorSelect.dataset.hist ||
                            '{}');
                        const chosenHist = histData[chosenIID] || [];

                        const rowIndex = reportSelect.value;
                        const chosenRow = chosenHist[rowIndex];
                        if (chosenRow) {
                            if (reportNameEl) reportNameEl.textContent = chosenRow
                                .ReportName ?
                                `${chosenRow.ReportName} (${chosenRow.Year})` :
                                `Unnamed Report (${chosenRow.Year})`;
                            if (responseEl) responseEl.textContent = chosenRow
                                .Response || 'N/A';
                            if (commentEl) commentEl.textContent = chosenRow.Comments ||
                                'N/A';
                            if (reportedByEl) reportedByEl.textContent = chosenRow
                                .ReportedBy || 'N/A';
                        }
                    }
                    goToStep(currentStep + 1);
                });
            });

            // Prev Buttons
            prevBtns.forEach((btn) => {
                btn.addEventListener('click', function() {
                    goToStep(currentStep - 1);
                });
            });

            // Step 2: Once user picks a report => enable "Next"
            if (reportSelect) {
                reportSelect.addEventListener('change', function() {
                    const step2Next = wizardEl.querySelector(
                        '.wizard-step-content[data-step="2"] .wizard-next-btn');
                    if (step2Next) step2Next.disabled = false;
                });
            }

            // Finish
            if (finishBtn) {
                finishBtn.addEventListener('click', function() {
                    // Reset wizard
                    goToStep(1);
                    if (indicatorSelect) indicatorSelect.value = "";
                    if (reportSelect) {
                        reportSelect.innerHTML =
                            `<option value="" disabled selected>Select a Report</option>`;
                        reportSelect.disabled = true;
                    }
                    const step1Next = wizardEl.querySelector(
                        '.wizard-step-content[data-step="1"] .wizard-next-btn');
                    if (step1Next) step1Next.disabled = true;
                    alert("Wizard completed. You may select another indicator if needed.");
                });
            }

            goToStep(1); // default
        });
    });
</script>

<!-- Initialize List.js for both Indicator Lists and Missing Indicators Lists with 4 items per page -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Overview Indicator Lists and Missing Indicator Lists
        document.querySelectorAll('.indicator-list').forEach(function(container) {
            new List(container.id, {
                valueNames: ['indicator-name'],
                page: 4,
                pagination: {
                    innerWindow: 1,
                    left: 0,
                    right: 0,
                    paginationClass: 'pagination'
                }
            });
        });
    });
</script>

<style>
    /* Ensure no horizontal scrolling inside modal */
    .modal-box {
        overflow-x: hidden;
    }

    /* Style for bordered iOS-like list */
    .indicator-list {
        border: 1px solid #e5e7eb;
        /* gray-200 */
        border-radius: 0.375rem;
        /* rounded */
        padding: 1rem;
        background-color: #ffffff;
    }

    .indicator-list .list li {
        padding: 0.75rem;
    }

    /* Pagination button styling using DaisyUI with proper classes */
    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .pagination li {
        list-style: none;
    }

    .pagination li button {
        @apply btn btn-sm btn-neutral;
    }

    /* Override table spacing for compact tables */
    .table td,
    .table th {
        padding: 0.4rem;
    }

    /* Wizard styles */
    .wizard-step-item {
        opacity: 0.5;
        transition: opacity 0.3s ease;
    }

    .wizard-step-item.active {
        opacity: 1;
    }

    .wizard-step-content {
        display: none;
    }

    .wizard-step-content.show {
        display: block;
    }

    .wizard-next-btn,
    .wizard-prev-btn,
    .wizard-finish-btn {
        min-width: 100px;
    }
</style>
