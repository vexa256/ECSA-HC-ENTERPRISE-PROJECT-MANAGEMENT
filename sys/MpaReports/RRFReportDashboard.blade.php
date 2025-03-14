@php
    // Set defaults if not provided.
    $selectedCluster = $selectedCluster ?? 'All clusters';
    $exportReport = isset($analyticsData[0]['timeline']) ? $analyticsData[0]['timeline']->ReportName : $reportType;
    // Build mapping: EntityID => Entity (name)
    $entities = DB::table('mpa_entities')->pluck('Entity', 'EntityID')->toArray();
@endphp

<main class="ios-app">


    <div class="ios-container">
        <!-- Completeness Card -->
        <section class="ios-card" aria-labelledby="completeness-title">
            <div class="ios-card-header">
                <h2 id="completeness-title" class="ios-card-title">Overall Reporting Completeness {{ $reportType }}
                    {{ $selectedYear }}</h2>
            </div>
            <div class="ios-card-content">
                <div id="completenessChart" class="ios-chart ios-skeleton"></div>
            </div>
        </section>

        <!-- Timeline Reports -->
        <section class="ios-card" x-data="{ activeTab: 0, isLoading: true }" x-init="setTimeout(() => isLoading = false, 500)">
            <div class="ios-card-header">
                <h2 class="ios-card-title">Timeline Reports</h2>
            </div>

            <!-- iOS-style Segmented Control -->
            <div class="ios-segment-container">
                <div class="ios-segment">
                    @foreach ($analyticsData as $index => $timelineData)
                        <button class="ios-segment-btn"
                            :class="activeTab === {{ $index }} ? 'ios-segment-btn-active' : ''"
                            @click="activeTab = {{ $index }}">
                            {{ $timelineData['timeline']->ReportName }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Tab Contents -->
            @foreach ($analyticsData as $index => $timelineData)
                <div x-show="activeTab === {{ $index }}" x-cloak x-transition:enter="ios-fade-in"
                    x-transition:leave="ios-fade-out" class="ios-tab-panel">

                    <div id="table-list-{{ $index }}" class="ios-search-container">
                        <div class="ios-search-field">
                            <svg class="ios-search-icon" viewBox="0 0 24 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"
                                    fill="currentColor" />
                            </svg>
                            <input class="ios-search search" placeholder="Search indicators…" />
                            <button class="ios-search-clear" type="button" aria-label="Clear search">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"
                                        fill="currentColor" />
                                </svg>
                            </button>
                        </div>

                        <div class="ios-table-wrapper">
                            <table class="ios-table">
                                <thead>
                                    <tr>
                                        <th class="ios-th ios-th-indicator">Indicator</th>
                                        <th class="ios-th">Type</th>
                                        <th class="ios-th">Target</th>
                                        <th class="ios-th">Achieved</th>
                                        <th class="ios-th">Diff</th>
                                        <th class="ios-th"><span class="sr-only">Actions</span></th>
                                    </tr>
                                </thead>
                                <tbody class="list">
                                    @foreach ($timelineData['indicators'] as $indicatorData)
                                        <tr class="ios-tr">
                                            <td class="ios-td ios-td-indicator indicator">
                                                {{ $indicatorData['indicator']->Indicator }}
                                            </td>
                                            <td class="ios-td responseType">
                                                {{ $indicatorData['indicator']->ResponseType }}
                                            </td>
                                            <td class="ios-td target">
                                                {{ $indicatorData['targetValue'] }}
                                            </td>
                                            <td class="ios-td achieved">
                                                @if ($indicatorData['indicator']->ResponseType === 'Yes/No')
                                                    {{ $indicatorData['computedValue']['yesPercentage'] }}%
                                                @elseif($indicatorData['indicator']->ResponseType === 'Number')
                                                    {{ $indicatorData['computedValue']['sum'] }}
                                                @endif
                                            </td>
                                            <td
                                                class="ios-td difference {{ $indicatorData['difference'] >= 0 ? 'ios-positive' : 'ios-negative' }}">
                                                <span
                                                    class="ios-badge {{ $indicatorData['difference'] >= 0 ? 'ios-badge-success' : 'ios-badge-error' }}">
                                                    {{ $indicatorData['difference'] >= 0 ? '+' : '' }}{{ $indicatorData['difference'] }}
                                                </span>
                                            </td>
                                            <td class="ios-td ios-td-action">
                                                <button
                                                    onclick="openSheet('sheet-{{ $indicatorData['indicator']->id }}')"
                                                    class="ios-btn ios-btn-icon" aria-label="View details">
                                                    <svg class="ios-icon" viewBox="0 0 24 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"
                                                            fill="currentColor" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="ios-table-pagination">
                            <span class="ios-pagination-info">Showing <span class="ios-pagination-current">1-20</span>
                                of <span
                                    class="ios-pagination-total">{{ count($timelineData['indicators']) }}</span></span>
                            <div class="ios-pagination-controls">
                                <button class="ios-btn ios-btn-icon ios-pagination-prev" disabled>
                                    <svg class="ios-icon" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z" fill="currentColor" />
                                    </svg>
                                </button>
                                <button class="ios-btn ios-btn-icon ios-pagination-next">
                                    <svg class="ios-icon" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z" fill="currentColor" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const options = {
                                valueNames: ['indicator', 'responseType', 'target', 'achieved', 'difference'],
                                page: 20,
                                pagination: true
                            };
                            new List('table-list-{{ $index }}', options);
                        });
                    </script>
                </div>
            @endforeach
        </section>
    </div>
</main>

<!-- iOS-style Bottom Sheets for Indicator Details -->
@foreach ($analyticsData as $timelineData)
    @foreach ($timelineData['indicators'] as $indicatorData)
        <div id="sheet-{{ $indicatorData['indicator']->id }}" class="ios-sheet">
            <div class="ios-sheet-backdrop" onclick="closeSheet('sheet-{{ $indicatorData['indicator']->id }}')">
            </div>
            <div class="ios-sheet-container">
                <div class="ios-sheet-handle"></div>
                <div class="ios-sheet-header">
                    <h3 class="ios-sheet-title">{{ $indicatorData['indicator']->Indicator }}</h3>
                    <button onclick="closeSheet('sheet-{{ $indicatorData['indicator']->id }}')"
                        class="ios-btn ios-btn-icon" aria-label="Close">
                        <svg class="ios-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"
                                fill="currentColor" />
                        </svg>
                    </button>
                </div>

                <div class="ios-sheet-content">
                    <div class="ios-grid">
                        <div class="ios-grid-col">
                            <div class="ios-chart-container">
                                <div id="historicalChart{{ $indicatorData['indicator']->id }}"
                                    class="ios-chart ios-skeleton"></div>
                            </div>
                        </div>
                        <div class="ios-grid-col">
                            <h4 class="ios-section-title">Detailed Responses</h4>
                            <div class="ios-table-wrapper ios-table-wrapper-sheet">
                                <table class="ios-table">
                                    <thead>
                                        <tr>
                                            <th class="ios-th">Entity</th>
                                            <th class="ios-th">Response</th>
                                            <th class="ios-th">Comment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($indicatorData['historicalData'] as $historyItem)
                                            <tr class="ios-tr">
                                                <td class="ios-td">
                                                    <div class="ios-entity">
                                                        <div class="ios-entity-avatar">
                                                            {{ substr($entities[$historyItem->EntityID] ?? $historyItem->EntityID, 0, 2) }}
                                                        </div>
                                                        <div class="ios-entity-name">
                                                            {{ $entities[$historyItem->EntityID] ?? $historyItem->EntityID }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="ios-td">
                                                    @if (strtolower(trim($historyItem->Response)) === 'yes')
                                                        <span class="ios-badge ios-badge-success">Yes</span>
                                                    @elseif (strtolower(trim($historyItem->Response)) === 'no')
                                                        <span class="ios-badge ios-badge-error">No</span>
                                                    @else
                                                        {{ $historyItem->Response }}
                                                    @endif
                                                </td>
                                                <td class="ios-td ios-comment">
                                                    {{ $historyItem->Comments }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endforeach

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/list.js@2.3.1/dist/list.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Optimized Sheet Functions
    let activeSheet = null;

    function openSheet(sheetId) {
        if (activeSheet) {
            closeSheet(activeSheet);
        }

        const sheet = document.getElementById(sheetId);
        sheet.classList.add('ios-sheet-open');
        document.body.classList.add('ios-sheet-active');
        activeSheet = sheetId;

        // Add haptic feedback
        if (window.navigator && window.navigator.vibrate) {
            window.navigator.vibrate(5);
        }

        // Lazy load chart when sheet opens
        const chartId = sheet.querySelector('.ios-chart').id;
        if (chartId && window.chartInitFunctions && window.chartInitFunctions[chartId]) {
            window.chartInitFunctions[chartId]();
            delete window.chartInitFunctions[chartId]; // Only initialize once
        }
    }

    function closeSheet(sheetId) {
        const sheet = document.getElementById(sheetId);
        sheet.classList.remove('ios-sheet-open');
        document.body.classList.remove('ios-sheet-active');
        activeSheet = null;

        // Add haptic feedback
        if (window.navigator && window.navigator.vibrate) {
            window.navigator.vibrate(3);
        }
    }

    // Handle ESC key to close sheet
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && activeSheet) {
            closeSheet(activeSheet);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize search clear buttons
        document.querySelectorAll('.ios-search-clear').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                input.value = '';
                input.focus();
                input.dispatchEvent(new Event('keyup'));
            });
        });

        // Initialize sheet drag to close
        document.querySelectorAll('.ios-sheet-handle').forEach(handle => {
            let startY = 0;
            let currentY = 0;

            handle.addEventListener('touchstart', function(e) {
                startY = e.touches[0].clientY;
            });

            handle.addEventListener('touchmove', function(e) {
                const sheet = this.closest('.ios-sheet');
                currentY = e.touches[0].clientY;
                const deltaY = currentY - startY;

                if (deltaY > 0) {
                    sheet.querySelector('.ios-sheet-container').style.transform =
                        `translateY(${deltaY}px)`;
                }
            });

            handle.addEventListener('touchend', function() {
                const sheet = this.closest('.ios-sheet');
                const container = sheet.querySelector('.ios-sheet-container');

                if (currentY - startY > 100) {
                    closeSheet(sheet.id);
                } else {
                    container.style.transform = '';
                }
            });
        });

        // Store chart initialization functions to be called when needed
        window.chartInitFunctions = {};

        // Initialize completeness chart with optimized options
        const completenessData = @json($completenessData);
        const completenessOptions = {
            series: [{
                name: 'Expected',
                data: completenessData.map(item => item.expectedCount)
            }, {
                name: 'Reported',
                data: completenessData.map(item => item.reportedCount)
            }, {
                name: 'Completeness (%)',
                data: completenessData.map(item => item.completeness)
            }],
            chart: {
                type: 'bar',
                height: 400,
                fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                toolbar: {
                    show: false
                },
                animations: {
                    enabled: true,
                    easing: 'easeinout',
                    speed: 500
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    dataLabels: {
                        position: 'top',
                    },
                },
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val.toFixed(1) + '%';
                },
                offsetX: 30,
                style: {
                    fontSize: '12px',
                    colors: ['#304758']
                }
            },
            stroke: {
                show: true,
                width: 1,
                colors: ['#fff']
            },
            xaxis: {
                categories: completenessData.map(item => item.timeline.ReportName),
                labels: {
                    style: {
                        fontSize: '12px',
                        fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '12px',
                        fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                    }
                }
            },
            tooltip: {
                shared: true,
                intersect: false,
                theme: 'light',
                style: {
                    fontSize: '12px',
                    fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                }
            },
            colors: ['#FF9500', '#34C759', '#007AFF'],
            grid: {
                borderColor: '#F2F2F7',
                strokeDashArray: 4,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: false
                    }
                }
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                fontSize: '13px',
                fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                markers: {
                    radius: 12,
                    width: 12,
                    height: 12
                }
            },
            title: {
                text: 'Overall Reporting Completeness',
                align: 'center',
                style: {
                    fontSize: '18px',
                    fontWeight: 600,
                    fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                }
            },
            subtitle: {
                text: 'Comparison of Expected vs Reported Data',
                align: 'center',
                style: {
                    fontSize: '14px',
                    fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                }
            }
        };

        // Remove skeleton class when chart is rendered
        const completenessChart = new ApexCharts(document.querySelector("#completenessChart"),
            completenessOptions);
        completenessChart.render().then(() => {
            document.querySelector("#completenessChart").classList.remove('ios-skeleton');
        });

        // Lazy initialize indicator detail charts
        @foreach ($analyticsData as $timelineData)
            @foreach ($timelineData['indicators'] as $indicatorData)
                window.chartInitFunctions['historicalChart{{ $indicatorData['indicator']->id }}'] =
                    function() {
                        const indicatorId = {{ $indicatorData['indicator']->id }};
                        const historical = @json($indicatorData['historicalData']);
                        const responseType = "{{ $indicatorData['indicator']->ResponseType }}".trim();
                        const totalExpected = {{ $totalCountries }};
                        const totalActual = historical.length;
                        let detailSeries = [];

                        if (responseType === 'Yes/No') {
                            let yesCount = 0,
                                noCount = 0;
                            historical.forEach(function(item) {
                                const resp = String(item.Response).toLowerCase().trim();
                                if (resp === 'yes') {
                                    yesCount++;
                                } else if (resp === 'no') {
                                    noCount++;
                                }
                            });
                            const score = {{ $indicatorData['computedValue']['yesPercentage'] ?? 0 }};
                            detailSeries = [{
                                    name: 'Expected',
                                    data: [totalExpected]
                                },
                                {
                                    name: 'Actual',
                                    data: [totalActual]
                                },
                                {
                                    name: 'Yes',
                                    data: [yesCount]
                                },
                                {
                                    name: 'No',
                                    data: [noCount]
                                },
                                {
                                    name: 'Score (%)',
                                    data: [score]
                                }
                            ];
                        } else if (responseType === 'Number') {
                            const sumScore = {{ $indicatorData['computedValue']['sum'] ?? 0 }};
                            detailSeries = [{
                                    name: 'Expected',
                                    data: [totalExpected]
                                },
                                {
                                    name: 'Actual',
                                    data: [totalActual]
                                },
                                {
                                    name: 'Score',
                                    data: [sumScore]
                                }
                            ];
                        } else {
                            detailSeries = [{
                                    name: 'Expected',
                                    data: [totalExpected]
                                },
                                {
                                    name: 'Actual',
                                    data: [totalActual]
                                }
                            ];
                        }

                        const detailOptions = {
                            series: detailSeries,
                            chart: {
                                type: 'bar',
                                height: 350,
                                fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                                toolbar: {
                                    show: false
                                },
                                animations: {
                                    enabled: true,
                                    speed: 500
                                }
                            },
                            plotOptions: {
                                bar: {
                                    horizontal: true,
                                    dataLabels: {
                                        position: 'top',
                                    },
                                },
                            },
                            dataLabels: {
                                enabled: true,
                                formatter: function(val) {
                                    return val.toFixed(1);
                                },
                                offsetX: 30,
                                style: {
                                    fontSize: '12px',
                                    colors: ['#304758']
                                }
                            },
                            stroke: {
                                show: true,
                                width: 1,
                                colors: ['#fff']
                            },
                            xaxis: {
                                categories: [''],
                                labels: {
                                    show: false
                                },
                                axisBorder: {
                                    show: false
                                },
                                axisTicks: {
                                    show: false
                                }
                            },
                            yaxis: {
                                labels: {
                                    style: {
                                        fontSize: '12px',
                                        fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                                    }
                                }
                            },
                            tooltip: {
                                shared: true,
                                intersect: false,
                                theme: 'light',
                                style: {
                                    fontSize: '12px',
                                    fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                                }
                            },
                            colors: ['#FF9500', '#5AC8FA', '#34C759', '#FF3B30', '#007AFF'],
                            grid: {
                                borderColor: '#F2F2F7',
                                strokeDashArray: 4,
                                xaxis: {
                                    lines: {
                                        show: true
                                    }
                                },
                                yaxis: {
                                    lines: {
                                        show: false
                                    }
                                }
                            },
                            legend: {
                                position: 'top',
                                horizontalAlign: 'right',
                                fontSize: '13px',
                                fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                                markers: {
                                    radius: 12,
                                    width: 12,
                                    height: 12
                                }
                            },
                            title: {
                                text: 'Indicator Details',
                                align: 'center',
                                style: {
                                    fontSize: '18px',
                                    fontWeight: 600,
                                    fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                                }
                            },
                            subtitle: {
                                text: '{{ $indicatorData['indicator']->Indicator }}',
                                align: 'center',
                                style: {
                                    fontSize: '14px',
                                    fontFamily: '-apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif',
                                }
                            }
                        };

                        const detailChart = new ApexCharts(document.querySelector("#historicalChart" +
                            indicatorId), detailOptions);
                        detailChart.render().then(() => {
                            document.querySelector("#historicalChart" + indicatorId).classList.remove(
                                'ios-skeleton');
                        });
                    };
            @endforeach
        @endforeach
    });
</script>

<style>
    /* Modern iOS Design System */
    :root {
        /* iOS 16+ Colors */
        --ios-blue: #007AFF;
        --ios-blue-light: #5AC8FA;
        --ios-green: #34C759;
        --ios-red: #FF3B30;
        --ios-orange: #FF9500;
        --ios-yellow: #FFCC00;
        --ios-purple: #5856D6;
        --ios-pink: #FF2D55;

        /* Neutral Colors */
        --ios-gray-1: #8E8E93;
        --ios-gray-2: #AEAEB2;
        --ios-gray-3: #C7C7CC;
        --ios-gray-4: #D1D1D6;
        --ios-gray-5: #E5E5EA;
        --ios-gray-6: #F2F2F7;

        /* Text Colors */
        --ios-text-primary: #000000;
        --ios-text-secondary: rgba(60, 60, 67, 0.85);
        --ios-text-tertiary: rgba(60, 60, 67, 0.6);
        --ios-text-quaternary: rgba(60, 60, 67, 0.4);

        /* Background Colors */
        --ios-bg-primary: #FFFFFF;
        --ios-bg-secondary: #F2F2F7;
        --ios-bg-tertiary: #FFFFFF;

        /* Shadows */
        --ios-shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.03);
        --ios-shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
        --ios-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.07), 0 2px 4px -1px rgba(0, 0, 0, 0.05);
        --ios-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.03);

        /* Spacing */
        --ios-space-1: 4px;
        --ios-space-2: 8px;
        --ios-space-3: 12px;
        --ios-space-4: 16px;
        --ios-space-5: 20px;
        --ios-space-6: 24px;
        --ios-space-8: 32px;
        --ios-space-10: 40px;

        /* Border Radius */
        --ios-radius-sm: 6px;
        --ios-radius-md: 10px;
        --ios-radius-lg: 14px;
        --ios-radius-xl: 18px;
        --ios-radius-full: 9999px;

        /* Transitions */
        --ios-transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        --ios-transition-normal: 300ms cubic-bezier(0.4, 0, 0.2, 1);
        --ios-transition-slow: 500ms cubic-bezier(0.4, 0, 0.2, 1);
        --ios-transition-sheet: 400ms cubic-bezier(0.16, 1, 0.3, 1);

        /* Font Sizes */
        --ios-text-xs: 11px;
        --ios-text-sm: 13px;
        --ios-text-base: 15px;
        --ios-text-lg: 17px;
        --ios-text-xl: 19px;
        --ios-text-2xl: 22px;
        --ios-text-3xl: 28px;
    }

    /* Base Styles */
    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Segoe UI", sans-serif;
        color: var(--ios-text-primary);
        background-color: var(--ios-bg-secondary);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        overflow-x: hidden;
    }

    body.ios-sheet-active {
        overflow: hidden;
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border-width: 0;
    }

    /* Layout */
    .ios-app {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .ios-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 var(--ios-space-4);
    }

    /* Navigation */
    .ios-nav {
        background-color: var(--ios-bg-primary);
        position: sticky;
        top: 0;
        z-index: 100;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        background-color: rgba(255, 255, 255, 0.8);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .ios-nav .ios-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: 56px;
    }

    .ios-nav-title {
        font-size: var(--ios-text-lg);
        font-weight: 600;
        color: var(--ios-text-primary);
    }

    .ios-export {
        margin-left: auto;
    }

    /* Cards */
    .ios-card {
        background-color: var(--ios-bg-primary);
        border-radius: var(--ios-radius-lg);
        margin-bottom: var(--ios-space-5);
        overflow: hidden;
        box-shadow: var(--ios-shadow-xs);
        transition: transform var(--ios-transition-normal), box-shadow var(--ios-transition-normal);
    }

    .ios-card:hover {
        box-shadow: var(--ios-shadow-md);
        transform: translateY(-1px);
    }

    .ios-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--ios-space-4) var(--ios-space-5);
    }

    .ios-card-title {
        font-size: var(--ios-text-lg);
        font-weight: 600;
        color: var(--ios-text-primary);
        margin: 0;
    }

    .ios-card-content {
        padding: var(--ios-space-4);
    }

    /* Buttons */
    .ios-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: var(--ios-text-base);
        font-weight: 500;
        padding: var(--ios-space-2) var(--ios-space-4);
        border-radius: var(--ios-radius-md);
        border: none;
        cursor: pointer;
        transition: all var(--ios-transition-fast);
        white-space: nowrap;
        height: 36px;
        background: none;
        color: var(--ios-text-primary);
    }

    .ios-btn-primary {
        background-color: var(--ios-blue);
        color: white;
    }

    .ios-btn-primary:hover {
        background-color: var(--ios-blue-light);
    }

    .ios-btn-icon {
        width: 36px;
        height: 36px;
        padding: 0;
        border-radius: var(--ios-radius-full);
    }

    .ios-btn-icon:hover {
        background-color: var(--ios-gray-6);
    }

    .ios-btn .ios-icon {
        width: 18px;
        height: 18px;
        margin-right: var(--ios-space-2);
    }

    .ios-btn-icon .ios-icon {
        width: 20px;
        height: 20px;
        margin-right: 0;
    }

    /* Icons */
    .ios-icon {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
    }

    /* Segmented Control */
    .ios-segment-container {
        padding: 0 var(--ios-space-5);
        margin-bottom: var(--ios-space-4);
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .ios-segment-container::-webkit-scrollbar {
        display: none;
    }

    .ios-segment {
        display: inline-flex;
        background-color: var(--ios-gray-6);
        border-radius: var(--ios-radius-md);
        padding: var(--ios-space-1);
    }

    .ios-segment-btn {
        position: relative;
        padding: var(--ios-space-2) var(--ios-space-4);
        font-size: var(--ios-text-sm);
        font-weight: 500;
        color: var(--ios-text-tertiary);
        background-color: transparent;
        border: none;
        border-radius: var(--ios-radius-md);
        cursor: pointer;
        transition: all var(--ios-transition-fast);
        white-space: nowrap;
        z-index: 1;
    }

    .ios-segment-btn-active {
        color: var(--ios-text-primary);
        font-weight: 600;
        background-color: var(--ios-bg-primary);
        box-shadow: var(--ios-shadow-xs);
    }

    /* Transitions */
    .ios-fade-in {
        animation: fadeIn var(--ios-transition-normal);
    }

    .ios-fade-out {
        animation: fadeOut var(--ios-transition-normal);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }

        to {
            opacity: 0;
        }
    }

    /* Search */
    .ios-search-container {
        padding: 0 var(--ios-space-4);
    }

    .ios-search-field {
        position: relative;
        display: flex;
        align-items: center;
        margin-bottom: var(--ios-space-5);
    }

    .ios-search-icon {
        position: absolute;
        left: var(--ios-space-3);
        width: 16px;
        height: 16px;
        color: var(--ios-gray-1);
        pointer-events: none;
    }

    .ios-search {
        width: 100%;
        height: 36px;
        padding: 0 var(--ios-space-8) 0 var(--ios-space-8);
        font-size: var(--ios-text-base);
        color: var(--ios-text-primary);
        background-color: var(--ios-gray-6);
        border: none;
        border-radius: var(--ios-radius-full);
        transition: background-color var(--ios-transition-fast);
    }

    .ios-search:focus {
        outline: none;
        background-color: var(--ios-gray-5);
    }

    .ios-search-clear {
        position: absolute;
        right: var(--ios-space-3);
        display: flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        color: var(--ios-gray-1);
        background-color: var(--ios-gray-3);
        border: none;
        border-radius: var(--ios-radius-full);
        cursor: pointer;
        opacity: 0.7;
        transition: opacity var(--ios-transition-fast);
    }

    .ios-search-clear:hover {
        opacity: 1;
    }

    .ios-search-clear svg {
        width: 10px;
        height: 10px;
    }

    /* Table */
    .ios-table-wrapper {
        border-radius: var(--ios-radius-md);
        overflow-x: auto;
        background-color: var(--ios-bg-primary);
        margin-bottom: var(--ios-space-4);
        -webkit-overflow-scrolling: touch;
    }

    .ios-table-wrapper-sheet {
        max-height: 300px;
        overflow-y: auto;
    }

    .ios-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .ios-th {
        position: sticky;
        top: 0;
        padding: var(--ios-space-3) var(--ios-space-4);
        font-size: var(--ios-text-xs);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--ios-text-tertiary);
        background-color: var(--ios-bg-primary);
        text-align: left;
        border-bottom: 1px solid var(--ios-gray-6);
        z-index: 10;
    }

    .ios-tr {
        transition: background-color var(--ios-transition-fast);
    }

    .ios-tr:hover {
        background-color: var(--ios-gray-6);
    }

    .ios-td {
        padding: var(--ios-space-3) var(--ios-space-4);
        font-size: var(--ios-text-sm);
        color: var(--ios-text-primary);
        border-bottom: 1px solid var(--ios-gray-6);
        vertical-align: middle;
    }

    .ios-td-indicator {
        max-width: 300px;
        white-space: normal;
        word-wrap: break-word;
    }

    .ios-td-action {
        width: 50px;
        text-align: center;
    }

    /* Badges */
    .ios-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: var(--ios-space-1) var(--ios-space-2);
        font-size: var(--ios-text-xs);
        font-weight: 500;
        border-radius: var(--ios-radius-sm);
        line-height: 1;
    }

    .ios-badge-success {
        background-color: rgba(52, 199, 89, 0.1);
        color: var(--ios-green);
    }

    .ios-badge-error {
        background-color: rgba(255, 59, 48, 0.1);
        color: var(--ios-red);
    }

    /* Entity */
    .ios-entity {
        display: flex;
        align-items: center;
        gap: var(--ios-space-2);
    }

    .ios-entity-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        background-color: var(--ios-blue);
        color: white;
        font-size: var(--ios-text-xs);
        font-weight: 600;
        border-radius: var(--ios-radius-full);
        text-transform: uppercase;
    }

    .ios-entity-name {
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Comment */
    .ios-comment {
        font-style: italic;
        color: var(--ios-text-tertiary);
    }

    /* Section Title */
    .ios-section-title {
        font-size: var(--ios-text-base);
        font-weight: 600;
        color: var(--ios-text-primary);
        margin: var(--ios-space-5) 0 var(--ios-space-3);
    }

    /* Chart */
    .ios-chart {
        height: 400px;
        width: 100%;
    }

    .ios-chart-container {
        margin-bottom: var(--ios-space-5);
    }

    /* Skeleton Loading */
    .ios-skeleton {
        position: relative;
        overflow: hidden;
        background-color: var(--ios-gray-6);
        border-radius: var(--ios-radius-md);
    }

    .ios-skeleton::after {
        content: "";
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        transform: translateX(-100%);
        background-image: linear-gradient(90deg,
                rgba(255, 255, 255, 0) 0,
                rgba(255, 255, 255, 0.2) 20%,
                rgba(255, 255, 255, 0.5) 60%,
                rgba(255, 255, 255, 0));
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        100% {
            transform: translateX(100%);
        }
    }

    /* Bottom Sheet */
    .ios-sheet {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1000;
        visibility: hidden;
        pointer-events: none;
    }

    .ios-sheet-open {
        visibility: visible;
        pointer-events: auto;
    }

    .ios-sheet-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        opacity: 0;
        transition: opacity var(--ios-transition-sheet);
    }

    .ios-sheet-open .ios-sheet-backdrop {
        opacity: 1;
    }

    .ios-sheet-container {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        max-height: 90vh;
        background-color: var(--ios-bg-primary);
        border-top-left-radius: var(--ios-radius-xl);
        border-top-right-radius: var(--ios-radius-xl);
        box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
        transform: translateY(100%);
        transition: transform var(--ios-transition-sheet);
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .ios-sheet-open .ios-sheet-container {
        transform: translateY(0);
    }

    .ios-sheet-handle {
        position: absolute;
        top: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 36px;
        height: 5px;
        background-color: var(--ios-gray-3);
        border-radius: var(--ios-radius-full);
        z-index: 1;
    }

    .ios-sheet-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--ios-space-5) var(--ios-space-5) var(--ios-space-4);
        border-bottom: 1px solid var(--ios-gray-6);
    }

    .ios-sheet-title {
        font-size: var(--ios-text-lg);
        font-weight: 600;
        color: var(--ios-text-primary);
    }

    .ios-sheet-content {
        padding: var(--ios-space-4) var(--ios-space-5) var(--ios-space-8);
    }

    /* Grid System */
    .ios-grid {
        display: flex;
        flex-wrap: wrap;
        margin: -var(--ios-space-4);
    }

    .ios-grid-col {
        flex: 1 1 50%;
        padding: var(--ios-space-4);
    }

    /* Pagination */
    .ios-table-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--ios-space-2) var(--ios-space-4);
        font-size: var(--ios-text-sm);
        color: var(--ios-text-tertiary);
    }

    .ios-pagination-controls {
        display: flex;
        gap: var(--ios-space-2);
    }

    .ios-pagination-prev:disabled,
    .ios-pagination-next:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Utility Classes */
    .ios-positive {
        color: var(--ios-green);
    }

    .ios-negative {
        color: var(--ios-red);
    }

    [x-cloak] {
        display: none !important;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .ios-card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .ios-card-title {
            margin-bottom: var(--ios-space-2);
        }

        .ios-table-pagination {
            flex-direction: column;
            gap: var(--ios-space-2);
            align-items: flex-start;
        }

        .ios-pagination-controls {
            align-self: flex-end;
        }

        .ios-grid-col {
            flex: 1 1 100%;
        }
    }

    .ios-td-indicator {
        max-width: 300px;
        white-space: normal;
        word-wrap: break-word;
    }

    .ios-chart {
        height: 400px;
        width: 100%;
    }

    .ios-grid {
        display: flex;
        flex-wrap: wrap;
        margin: -var(--ios-space-4);
    }

    .ios-grid-col {
        flex: 1 1 50%;
        padding: var(--ios-space-4);
    }

    @media (max-width: 768px) {
        .ios-grid-col {
            flex: 1 1 100%;
        }
    }
</style>
