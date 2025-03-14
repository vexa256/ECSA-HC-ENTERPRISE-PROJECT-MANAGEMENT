@php
    // Filter out strategic objectives with no indicators.
    $filteredPerformanceData = array_filter($performanceData, function ($objective) {
        return count($objective['indicators']) > 0;
    });
@endphp

<div class="bg-base-100 min-h-screen">
    <style>
        /* Wizard styles */
        .wizard-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .wizard-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .step-circle {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e5e7eb;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 0.5rem;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .step-circle.active {
            background-color: #3b82f6;
            color: white;
        }

        .step-title {
            font-size: 0.875rem;
            text-align: center;
        }

        .wizard-content {
            min-height: 300px;
            transition: all 0.3s ease;
        }

        /* Full-screen modals */
        dialog.modal {
            width: 100vw !important;
            height: 100vh !important;
            max-width: 100vw !important;
            max-height: 100vh !important;
        }

        /* Button and badge fixes */
        .btn,
        .badge {
            font-size: 0.875rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>

    <!-- Page Header -->
    <div class="bg-primary text-primary-content shadow-lg">
        <div class="container mx-auto p-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-accent-content">
                        ECSA-HC Strategic Objectives Performance Analysis Dashboard
                    </h1>
                    <p class="text-sm opacity-90 text-danger">
                        <span id="reportName">{{ $report->ReportName }}</span> |
                        <span id="reportYear">{{ $selectedYear }}</span>
                    </p>
                </div>
                <div class="flex gap-2 mt-4 md:mt-0">
                    <button class="btn btn-sm btn-outline btn-accent"
                        onclick="document.getElementById('filterModal').showModal()">
                        <i class="iconify" data-icon="lucide:filter"></i> Filter
                    </button>
                    <button class="btn btn-sm btn-outline btn-accent" onclick="window.print()">
                        <i class="iconify" data-icon="lucide:printer"></i> Print
                    </button>
                    <a href="{{ route('export-csv', ['cluster' => $selectedCluster, 'year' => $selectedYear, 'report' => $selectedReport]) }}"
                        class="btn btn-sm btn-outline btn-accent">
                        <i class="iconify" data-icon="lucide:download"></i> Export
                    </a>
                    <button class="btn btn-sm btn-outline btn-accent"
                        onclick="document.getElementById('helpModal').showModal()">
                        <i class="iconify" data-icon="lucide:help-circle"></i> Help
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Summary Cards -->
    <div class="container mx-auto p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stats shadow bg-base-100">
                <div class="stat">
                    <div class="stat-figure text-primary">
                        <i class="iconify text-3xl" data-icon="lucide:target"></i>
                    </div>
                    <div class="stat-title">Total Objectives</div>
                    <div class="stat-value text-primary" id="totalObjectives">
                        {{ count($filteredPerformanceData) }}
                    </div>
                </div>
            </div>
            <div class="stats shadow bg-base-100">
                <div class="stat">
                    <div class="stat-figure text-secondary">
                        <i class="iconify text-3xl" data-icon="lucide:list-checks"></i>
                    </div>
                    <div class="stat-title">Total Indicators</div>
                    <div class="stat-value text-secondary" id="totalIndicators">
                        @php
                            $indicatorCount = 0;
                            foreach ($filteredPerformanceData as $objective) {
                                $indicatorCount += count($objective['indicators']);
                            }
                            echo $indicatorCount;
                        @endphp
                    </div>
                </div>
            </div>
            <div class="stats shadow bg-base-100">
                <div class="stat">
                    <div class="stat-figure text-success">
                        <i class="iconify text-3xl" data-icon="lucide:check-circle"></i>
                    </div>
                    <div class="stat-title">Met Targets</div>
                    <div class="stat-value text-success" id="metTargets">
                        @php
                            $metCount = 0;
                            foreach ($filteredPerformanceData as $objective) {
                                foreach ($objective['indicators'] as $indicator) {
                                    if ($indicator['status'] === 'met') {
                                        $metCount++;
                                    }
                                }
                            }
                            echo $metCount;
                        @endphp
                    </div>
                </div>
            </div>
            <div class="stats shadow bg-base-100">
                <div class="stat">
                    <div class="stat-figure text-warning">
                        <i class="iconify text-3xl" data-icon="lucide:alert-triangle"></i>
                    </div>
                    <div class="stat-title">Needs Attention</div>
                    <div class="stat-value text-warning" id="needsAttention">
                        @php
                            $needsAttentionCount = 0;
                            foreach ($filteredPerformanceData as $objective) {
                                foreach ($objective['indicators'] as $indicator) {
                                    if (
                                        $indicator['status'] === 'not performing' ||
                                        $indicator['status'] === 'in progress'
                                    ) {
                                        $needsAttentionCount++;
                                    }
                                }
                            }
                            echo $needsAttentionCount;
                        @endphp
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Overview Chart -->
    <div class="container mx-auto p-4">
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title">
                    <i class="iconify mr-2" data-icon="lucide:bar-chart-2"></i>
                    Performance Overview
                </h2>
                <div id="performanceOverviewChart" class="h-80"></div>
            </div>
        </div>
    </div>

    <!-- Strategic Objectives Wizard -->
    <div class="container mx-auto p-4">
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <i class="iconify mr-2" data-icon="lucide:layers"></i>
                    Strategic Objectives Wizard
                </h2>
                <div class="wizard" id="strategicObjectivesWizard">
                    <div class="wizard-steps mb-4">
                        <!-- Steps dynamically generated -->
                    </div>
                    <div class="wizard-content">
                        <!-- Content dynamically generated -->
                    </div>
                    <div class="wizard-actions mt-6 flex justify-between">
                        <button class="btn btn-neutral" id="prevStep" disabled>Previous</button>
                        <button class="btn btn-neutral" id="nextStep">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance by Cluster -->
    <div class="container mx-auto p-4">
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <h2 class="card-title">
                    <i class="iconify mr-2" data-icon="lucide:git-branch"></i>
                    Performance by Cluster
                </h2>
                <div id="clusterComparisonChart" class="h-80"></div>
            </div>
        </div>
    </div>

    <!-- Filter Modal (Full Screen) -->
    <dialog id="filterModal" class="modal modal-full">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Filter Dashboard</h3>
            <div id="step1" class="step-content">
                <form action="{{ route('select-year') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Select Cluster</span>
                        </label>
                        <select class="select select-bordered w-full" name="cluster" id="clusterFilter">
                            <option value="All clusters">All Clusters</option>
                            @foreach ($clusters as $cluster)
                                <option value="{{ $cluster->ClusterID }}"
                                    {{ $selectedCluster == $cluster->ClusterID ? 'selected' : '' }}>
                                    {{ $cluster->Cluster_Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn btn-ghost"
                            onclick="document.getElementById('filterModal').close()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Next</button>
                    </div>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    <!-- Help Modal (Full Screen) -->
    <dialog id="helpModal" class="modal modal-full">
        <form method="dialog" class="modal-box">
            <h3 class="font-bold text-lg">Dashboard Help</h3>
            <div class="py-4">
                <h4 class="font-semibold mb-2">Understanding Performance Status</h4>
                <ul class="list-disc pl-5 space-y-2">
                    <li><span class="badge badge-success">Met</span> - Performance is 90% or above the target</li>
                    <li><span class="badge badge-info">On Track</span> - Performance is between 50% and 90% of the
                        target</li>
                    <li><span class="badge badge-warning">In Progress</span> - Performance is between 10% and 50% of
                        the target</li>
                    <li><span class="badge badge-accent">Not Performing</span> - Performance is below 10% of the target
                    </li>
                </ul>
                <h4 class="font-semibold mt-4 mb-2">Using the Dashboard</h4>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Click on any strategic objective to expand and see its indicators</li>
                    <li>Use the filter button to change the cluster, year, or report</li>
                    <li>Click "Details" on any indicator to view full information, including historical trends,
                        analysis, and recommendations</li>
                    <li>Use the Export button to download the data as CSV</li>
                </ul>
            </div>
            <div class="modal-action"><button class="btn">Close</button></div>
        </form>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    <!-- Indicator Details Modal (Full Screen) -->
    <dialog id="indicatorDetailsModal" class="modal modal-full">
        <form method="dialog" class="modal-box max-w-4xl">
            <h3 class="font-bold text-lg mb-4" id="indicatorDetailsTitle">Indicator Details</h3>
            <div id="indicatorDetailsContent">
                <!-- Populated by JavaScript -->
            </div>
            <div class="modal-action"><button class="btn">Close</button></div>
        </form>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    <!-- Full Screen Indicator Analysis Modal -->
    <dialog id="fullScreenAnalysisModal" class="modal modal-full">
        <form method="dialog" class="modal-box w-full h-full max-w-none">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold" id="analysisModalTitle">Comprehensive Indicator Analysis</h2>
                <button class="btn btn-sm btn-circle">✕</button>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="card bg-base-100 shadow-xl mb-6">
                        <div class="card-body">
                            <h3 class="card-title">Performance Trend</h3>
                            <div id="indicatorTrendChart" class="h-80"></div>
                        </div>
                    </div>
                    <div class="card bg-base-100 shadow-xl mb-6">
                        <div class="card-body">
                            <h3 class="card-title">Cluster Comparison</h3>
                            <div id="clusterComparisonChart" class="h-80"></div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-1">
                    <div class="card bg-base-100 shadow-xl mb-6">
                        <div class="card-body">
                            <h3 class="card-title">Indicator Details</h3>
                            <div id="fullScreenIndicatorDetails" class="space-y-4">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    <div class="card bg-base-100 shadow-xl mb-6">
                        <div class="card-body">
                            <h3 class="card-title">Cluster Responses</h3>
                            <div id="clusterResponsesTable" class="overflow-x-auto">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                    <div class="card bg-base-100 shadow-xl">
                        <div class="card-body">
                            <h3 class="card-title">Analysis & Recommendations</h3>
                            <div id="analysisRecommendationsContent">
                                <!-- Populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    <!-- JavaScript for Charts and Interactivity -->
    <script>
        // Data passed from the controller.
        const performanceData = @json($filteredPerformanceData);
        const clusters = @json($clusters);
        const selectedCluster = @json($selectedCluster);
        const selectedYear = @json($selectedYear);
        const selectedReport = @json($selectedReport);

        // Colors.
        const colors = {
            met: '#34C759',
            onTrack: '#007AFF',
            inProgress: '#FF9500',
            notPerforming: '#FF3B30'
        };

        // --- Graph Functions ---
        window.initializeCharts = function() {
            initializePerformanceOverviewChart();
            initializeClusterPerformanceChart();
        };

        window.initializePerformanceOverviewChart = function() {
            const categories = [];
            const metData = [];
            const onTrackData = [];
            const inProgressData = [];
            const notPerformingData = [];
            Object.entries(performanceData).forEach(([objectiveId, objective]) => {
                categories.push(objectiveId);
                let metCount = 0,
                    onTrackCount = 0,
                    inProgressCount = 0,
                    notPerformingCount = 0;
                objective.indicators.forEach(indicator => {
                    if (indicator.status === 'met') metCount++;
                    else if (indicator.status === 'on track') onTrackCount++;
                    else if (indicator.status === 'in progress') inProgressCount++;
                    else notPerformingCount++;
                });
                metData.push(metCount);
                onTrackData.push(onTrackCount);
                inProgressData.push(inProgressCount);
                notPerformingData.push(notPerformingCount);
            });
            const options = {
                series: [{
                        name: 'Met',
                        data: metData,
                        color: colors.met
                    },
                    {
                        name: 'On Track',
                        data: onTrackData,
                        color: colors.onTrack
                    },
                    {
                        name: 'In Progress',
                        data: inProgressData,
                        color: colors.inProgress
                    },
                    {
                        name: 'Not Performing',
                        data: notPerformingData,
                        color: colors.notPerforming
                    }
                ],
                chart: {
                    type: 'bar',
                    height: 350,
                    stacked: true,
                    toolbar: {
                        show: true
                    },
                    zoom: {
                        enabled: true
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        legend: {
                            position: 'bottom',
                            offsetX: -10,
                            offsetY: 0
                        }
                    }
                }],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        borderRadius: 5,
                        dataLabels: {
                            total: {
                                enabled: true,
                                style: {
                                    fontSize: '13px',
                                    fontWeight: 900
                                }
                            }
                        }
                    }
                },
                xaxis: {
                    categories: categories,
                    labels: {
                        rotate: -45,
                        style: {
                            fontSize: '12px'
                        }
                    }
                },
                legend: {
                    position: 'right',
                    offsetY: 40
                },
                fill: {
                    opacity: 1
                },
                dataLabels: {
                    enabled: false
                }
            };
            const chart = new ApexCharts(document.querySelector("#performanceOverviewChart"), options);
            chart.render();
        };

        window.initializeClusterPerformanceChart = function() {
            const clusterIds = new Set();
            const clusterNames = {};
            clusters.forEach(cluster => {
                clusterIds.add(cluster.ClusterID);
                clusterNames[cluster.ClusterID] = cluster.Cluster_Name;
            });
            const categories = Array.from(clusterIds);
            const metData = Array(categories.length).fill(0);
            const onTrackData = Array(categories.length).fill(0);
            const inProgressData = Array(categories.length).fill(0);
            const notPerformingData = Array(categories.length).fill(0);
            Object.entries(performanceData).forEach(([objectiveId, objective]) => {
                objective.indicators.forEach(indicator => {
                    let targetClusters = indicator.isGlobal ? categories : indicator
                        .responsibleClusters;
                    targetClusters.forEach(clusterId => {
                        const idx = categories.indexOf(clusterId);
                        if (idx === -1) return;
                        if (indicator.status === 'met') metData[idx]++;
                        else if (indicator.status === 'on track') onTrackData[idx]++;
                        else if (indicator.status === 'in progress') inProgressData[idx]++;
                        else notPerformingData[idx]++;
                    });
                });
            });
            const displayCategories = categories.map(id => clusterNames[id] || id);
            const options = {
                series: [{
                        name: 'Met',
                        data: metData,
                        color: colors.met
                    },
                    {
                        name: 'On Track',
                        data: onTrackData,
                        color: colors.onTrack
                    },
                    {
                        name: 'In Progress',
                        data: inProgressData,
                        color: colors.inProgress
                    },
                    {
                        name: 'Not Performing',
                        data: notPerformingData,
                        color: colors.notPerforming
                    }
                ],
                chart: {
                    type: 'bar',
                    height: 350,
                    stacked: true,
                    toolbar: {
                        show: true
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 5,
                        dataLabels: {
                            total: {
                                enabled: true,
                                style: {
                                    fontSize: '13px',
                                    fontWeight: 900
                                }
                            }
                        }
                    }
                },
                xaxis: {
                    categories: displayCategories
                },
                yaxis: {
                    title: {
                        text: 'Clusters'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left',
                    offsetX: 40
                },
                fill: {
                    opacity: 1
                },
                dataLabels: {
                    enabled: false
                }
            };
            const chart = new ApexCharts(document.querySelector("#clusterComparisonChart"), options);
            chart.render();
        };

        window.renderPerformanceTrendChart = function(indicator) {
            let trendData = [];
            let trendCategories = [];
            if (indicator.historicalScores && indicator.historicalScores.length > 0) {
                indicator.historicalScores.sort((a, b) => new Date(a.date) - new Date(b.date));
                indicator.historicalScores.forEach(entry => {
                    trendData.push(entry.score);
                    const formattedDate = new Date(entry.date).toLocaleDateString(undefined, {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    });
                    trendCategories.push(formattedDate);
                });
            }
            if (trendData.length === 0) {
                trendData.push(indicator.score || 0);
                trendCategories.push("Current");
            }
            const trendOptions = {
                series: [{
                    name: "Performance Score",
                    data: trendData
                }],
                chart: {
                    type: 'line',
                    height: 350,
                    zoom: {
                        enabled: false
                    }
                },
                title: {
                    text: 'Performance Trend Over Time',
                    align: 'center'
                },
                xaxis: {
                    categories: trendCategories,
                    title: {
                        text: 'Reporting Date'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Score'
                    },
                    min: 0
                },
                tooltip: {
                    x: {
                        format: 'dd MMM yyyy'
                    },
                    y: {
                        formatter: val => val.toFixed(2)
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                }
            };
            const trendChart = new ApexCharts(document.querySelector("#indicatorTrendChart"), trendOptions);
            trendChart.render();
        };

        window.renderClusterComparisonChart = function(indicator) {
            const clusterData = [];
            const clusterLabels = [];
            if (indicator.clusterResponses) {
                Object.entries(indicator.clusterResponses).forEach(([cid, resp]) => {
                    clusterLabels.push(cid);
                    const val = parseFloat(resp.Response);
                    clusterData.push(isNaN(val) ? 0 : val);
                });
            }
            const comparisonOptions = {
                series: [{
                    name: 'Response Value',
                    data: clusterData
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: true
                    }
                },
                title: {
                    text: 'Cluster Comparison of Responses',
                    align: 'center'
                },
                xaxis: {
                    categories: clusterLabels,
                    title: {
                        text: 'Cluster'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Response Value'
                    },
                    min: 0
                },
                tooltip: {
                    y: {
                        formatter: val => val.toFixed(2)
                    }
                },
                dataLabels: {
                    enabled: true,
                    style: {
                        fontSize: '12px'
                    }
                }
            };
            const comparisonChart = new ApexCharts(document.querySelector("#clusterComparisonChart"),
                comparisonOptions);
            comparisonChart.render();
        };

        // --- Modal Functions ---
        window.showIndicatorDetails = function(objectiveId, indicatorIndex) {
            const objective = performanceData[objectiveId];
            const indicator = objective.indicators[indicatorIndex];
            document.getElementById('indicatorDetailsTitle').textContent = indicator.name;
            let content = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-title">Baseline</div>
                            <div class="stat-value">${indicator.baseline ?? 'N/A'}</div>
                            <div class="stat-desc">Starting point</div>
                        </div>
                    </div>
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-title">Target</div>
                            <div class="stat-value">${indicator.target ?? 'N/A'}</div>
                            <div class="stat-desc">Goal</div>
                        </div>
                    </div>
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-title">Current Score</div>
                            <div class="stat-value">${indicator.score ?? 'N/A'}</div>
                            <div class="stat-desc">Current performance</div>
                        </div>
                    </div>
                    <div class="stats shadow">
                        <div class="stat">
                            <div class="stat-title">Status</div>
                            <div class="stat-value">
                                <div class="badge ${indicator.status === 'met' ? 'badge-success' : indicator.status === 'on track' ? 'badge-info' : indicator.status === 'in progress' ? 'badge-warning' : 'badge-error'}">
                                    ${indicator.status.charAt(0).toUpperCase() + indicator.status.slice(1)}
                                </div>
                            </div>
                            <div class="stat-desc">Performance status</div>
                        </div>
                    </div>
                </div>
                <div class="divider">Responsible Clusters</div>
                <div class="flex flex-wrap gap-2 mb-4">
                    ${indicator.responsibleClusters.map(cid => `<div class="badge badge-outline">${cid}</div>`).join('')}
                </div>
                <div class="divider">Cluster Responses</div>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Cluster</th>
                                <th>Response</th>
                                <th>Comment</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${Object.entries(indicator.clusterResponses || {}).map(([cid, resp]) => ` <
                tr >
                <
                td > $ {
                    cid
                } < /td> <
            td > $ {
                resp.Response
            } < /td> <
            td > $ {
                resp.ReportingComment
            } < /td> < /
            tr >
                `).join('')}
                        </tbody>
                    </table>
                </div>
                <div class="divider">Analysis & Recommendations</div>
                <div class="p-4 bg-gray-100 rounded">
                    <p class="mb-2 whitespace-pre-line">${indicator.analysis ?? 'No analysis available.'}</p>
                    <ul class="list-disc pl-5">
                        ${ (indicator.recommendations && indicator.recommendations.length)
                            ? indicator.recommendations.map(r => `<li>${r}</li>`).join('')
                            : '<li>No recommendations available.</li>' }
                    </ul>
                </div>
                <div class="mt-6 flex justify-end">
                    <button class="btn btn-primary" onclick="showFullScreenAnalysis('${objectiveId}', ${indicatorIndex})">
                        <i class="iconify mr-2" data-icon="lucide:maximize"></i> Full Analysis
                    </button>
                </div>
            `;
            document.getElementById('indicatorDetailsContent').innerHTML = content;
            document.getElementById('indicatorDetailsModal').showModal();
        };

        window.showFullScreenAnalysis = function(objectiveId, indicatorIndex) {
            document.getElementById('indicatorDetailsModal').close();
            const objective = performanceData[objectiveId];
            const indicator = objective.indicators[indicatorIndex];
            document.getElementById('analysisModalTitle').textContent = `${objectiveId}: ${indicator.name}`;

            let detailsContent = `
                <div class="space-y-2">
                    <div class="flex justify-between"><span class="font-semibold">Baseline:</span><span>${indicator.baseline ?? 'N/A'}</span></div>
                    <div class="flex justify-between"><span class="font-semibold">Target:</span><span>${indicator.target ?? 'N/A'}</span></div>
                    <div class="flex justify-between"><span class="font-semibold">Current Score:</span><span>${indicator.score ?? 'N/A'}</span></div>
                    <div class="flex justify-between">
                        <span class="font-semibold">Status:</span>
                        <span class="badge ${indicator.status === 'met' ? 'badge-success' : indicator.status === 'on track' ? 'badge-info' : indicator.status === 'in progress' ? 'badge-warning' : 'badge-error'}">
                            ${indicator.status.charAt(0).toUpperCase() + indicator.status.slice(1)}
                        </span>
                    </div>
                    <div class="flex justify-between"><span class="font-semibold">Response Type:</span><span>${indicator.responseType}</span></div>
                    <div class="flex justify-between"><span class="font-semibold">Global Indicator:</span><span>${indicator.isGlobal ? 'Yes' : 'No'}</span></div>
                </div>
                <div class="divider">Responsible Clusters</div>
                <div class="flex flex-wrap gap-2">
                    ${indicator.responsibleClusters.map(cid => `<div class="badge badge-outline">${cid}</div>`).join('')}
                </div>
            `;
            document.getElementById('fullScreenIndicatorDetails').innerHTML = detailsContent;

            let responsesContent = `
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Cluster</th>
                            <th>Response</th>
                            <th>Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Object.entries(indicator.clusterResponses || {}).map(([cid, resp]) => ` <
                tr >
                <
                td > $ {
                    cid
                } < /td> <
            td > $ {
                resp.Response
            } < /td> <
            td > $ {
                resp.ReportingComment
            } < /td> < /
            tr >
                `).join('')}
                    </tbody>
                </table>
            `;
            document.getElementById('clusterResponsesTable').innerHTML = responsesContent;

            let analysisRecommendations = `
                <div class="p-4 bg-gray-100 rounded">
                    <h4 class="font-bold mb-2">Analysis Summary</h4>
                    <p class="whitespace-pre-line">${indicator.analysis ?? 'No analysis available.'}</p>
                </div>
                <div class="divider"></div>
                <div class="p-4 bg-gray-100 rounded">
                    <h4 class="font-bold mb-2">Recommendations</h4>
                    <ul class="list-disc pl-5">
                        ${ (indicator.recommendations && indicator.recommendations.length)
                            ? indicator.recommendations.map(r => `<li>${r}</li>`).join('')
                            : '<li>No recommendations available.</li>' }
                    </ul>
                </div>
            `;
            document.getElementById('analysisRecommendationsContent').innerHTML = analysisRecommendations;

            // Render the Performance Trend chart using historicalScores.
            window.renderPerformanceTrendChart(indicator);
            // Render the Cluster Comparison chart.
            window.renderClusterComparisonChart(indicator);

            document.getElementById('fullScreenAnalysisModal').showModal();
        };

        // --- Wizard Functions ---
        const wizardSteps = Object.keys(performanceData);
        let currentStep = 0;
        window.initializeWizard = function() {
            const stepsContainer = document.querySelector('#strategicObjectivesWizard .wizard-steps');
            const contentContainer = document.querySelector('#strategicObjectivesWizard .wizard-content');
            wizardSteps.forEach((step, index) => {
                const stepElement = document.createElement('div');
                stepElement.classList.add('wizard-step');
                stepElement.innerHTML = `
                    <div class="step-circle ${index === 0 ? 'active' : ''}">${index + 1}</div>
                    <div class="step-title">${step}</div>
                `;
                stepsContainer.appendChild(stepElement);
            });
            window.updateWizardContent();
            document.getElementById('prevStep').addEventListener('click', () => window.navigateWizard(-1));
            document.getElementById('nextStep').addEventListener('click', () => window.navigateWizard(1));
        };

        window.updateWizardContent = function() {
            const contentContainer = document.querySelector('#strategicObjectivesWizard .wizard-content');
            const currentObjective = performanceData[wizardSteps[currentStep]];
            let content = `
                <h3 class="text-xl font-bold mb-4">${currentObjective.name}</h3>
                <p class="mb-4">${currentObjective.description}</p>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th class="text-center">Baseline</th>
                                <th class="text-center">Target</th>
                                <th class="text-center">Current</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${currentObjective.indicators.map((indicator, index) => `
                                                                <tr>
                                                                    <td>${indicator.name}</td>
                                                                    <td class="text-center">${indicator.baseline ?? 'N/A'}</td>
                                                                    <td class="text-center">${indicator.target ?? 'N/A'}</td>
                                                                    <td class="text-center">${indicator.score ?? 'N/A'}</td>
                                                                    <td class="text-center">
                                                                        <div class="badge ${indicator.status === 'met' ? 'badge-success' : indicator.status === 'on track' ? 'badge-info' : indicator.status === 'in progress' ? 'badge-warning' : 'badge-error'}">
                                                                            ${indicator.status.charAt(0).toUpperCase() + indicator.status.slice(1)}
                                                                        </div>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <button class="btn btn-xs btn-neutral p-5" onclick="window.showIndicatorDetails('${wizardSteps[currentStep]}', ${index})">
                                                                            <i class="iconify" data-icon="lucide:eye"></i> Details
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
            contentContainer.innerHTML = content;
            document.getElementById('prevStep').disabled = currentStep === 0;
            document.getElementById('nextStep').disabled = currentStep === wizardSteps.length - 1;
            document.getElementById('nextStep').textContent = currentStep === wizardSteps.length - 1 ? 'Finish' :
                'Next';
            document.querySelectorAll('.wizard-step').forEach((step, index) => {
                step.querySelector('.step-circle').classList.toggle('active', index === currentStep);
            });
        };

        window.navigateWizard = function(direction) {
            currentStep += direction;
            if (currentStep < 0) currentStep = 0;
            if (currentStep >= wizardSteps.length) currentStep = wizardSteps.length - 1;
            window.updateWizardContent();
        };

        // --- DOMContentLoaded ---
        document.addEventListener('DOMContentLoaded', function() {
            window.initializeCharts();
            window.initializeWizard();
        });
    </script>

    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</div>
