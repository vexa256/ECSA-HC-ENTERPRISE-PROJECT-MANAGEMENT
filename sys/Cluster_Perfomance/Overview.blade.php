<div class="min-h-screen bg-gray-100 text-gray-900 font-sans">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <header class="mb-8">
            <h1 class="text-3xl font-semibold mb-2">Cluster Performance Breakdown</h1>
            <p class="text-gray-600">{{ $report->ReportName }} - {{ $selectedYear }}</p>
        </header>

        <!-- Action Buttons -->
        <div class="flex space-x-4 mb-8">
            <a href="{{ route('Ecsa_CP_selectReport', ['year' => $selectedYear]) }}" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7" />
                    <path d="M19 12H5" />
                </svg>
                Back
            </a>
            <a href="{{ route('Ecsa_CP_exportCsv', ['year' => $selectedYear, 'report' => $selectedReport]) }}"
                class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                Export CSV
            </a>
        </div>

        <!-- Overview Cards with Material iOS Colors -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Report Status Card -->
            <div class="card shadow-sm" style="background-image: linear-gradient(to bottom right, #4CAF50, #8BC34A);">
                <div class="card-body">
                    <h2 class="card-title text-lg text-white">Report Status</h2>
                    <p class="text-2xl font-semibold text-white">
                        {{ $report->status }}
                    </p>
                </div>
            </div>
            <!-- Year Card -->
            <div class="card shadow-sm" style="background-image: linear-gradient(to bottom right, #2196F3, #64B5F6);">
                <div class="card-body">
                    <h2 class="card-title text-lg text-white">Year</h2>
                    <p class="text-2xl font-semibold text-white">{{ $report->Year }}</p>
                </div>
            </div>
            <!-- Closing Date Card -->
            <div class="card shadow-sm" style="background-image: linear-gradient(to bottom right, #9C27B0, #BA68C8);">
                <div class="card-body">
                    <h2 class="card-title text-lg text-white">Closing Date</h2>
                    <p class="text-2xl font-semibold text-white">
                        {{ \Carbon\Carbon::parse($report->ClosingDate)->format('d M Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Performance Charts -->
        <div class="grid grid-cols-1 gap-8 mb-8">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-4">All Clusters Performance</h2>
                    <div id="allClustersPerformanceChart" class="h-96"></div>
                </div>
            </div>
            <!-- Two charts side by side on medium screens and up -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Overall Performance Distribution</h2>
                        <div id="overallPerformanceChart" class="h-64"></div>
                    </div>
                </div>
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Top Performing Clusters</h2>
                        <div id="topPerformingClustersChart" class="h-64"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cluster Performance List -->
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title mb-4">Cluster Performance</h2>
                <div class="overflow-x-auto">
                    <table class="table table-compact w-full">
                        <thead>
                            <tr>
                                <th>Cluster</th>
                                <th>Performance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($performanceData as $clusterId => $clusterData)
                                <tr class="hover">
                                    <td>{{ $clusterData['clusterName'] }}</td>
                                    <td>
                                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                                            <div class="bg-primary h-2.5 rounded-full"
                                                style="width: {{ $clusterData['overallMetPercentage'] }}%"></div>
                                        </div>
                                        <span class="text-sm">{{ $clusterData['overallMetPercentage'] }}%</span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge {{ $clusterData['overallMetPercentage'] >= 70 ? 'badge-success' : ($clusterData['overallMetPercentage'] >= 40 ? 'badge-warning' : 'badge-error') }}">
                                            {{ $clusterData['overallMetPercentage'] >= 70 ? 'Good' : ($clusterData['overallMetPercentage'] >= 40 ? 'Average' : 'Poor') }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary"
                                            onclick="showClusterDetails('{{ $clusterId }}')">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Cluster Details Modal -->
    <dialog id="clusterDetailsModal" class="modal modal-full">
        <div class="modal-box w-full h-full flex flex-col p-0">
            <div class="p-6 bg-gray-50 border-b">
                <h3 class="font-bold text-2xl" id="clusterDetailsTitle"></h3>
            </div>
            <div id="clusterDetailsContent" class="flex-grow overflow-y-auto p-6">
                <!-- Content will be dynamically inserted here -->
            </div>
            <div class="modal-action p-6 bg-gray-50 border-t">
                <form method="dialog">
                    <button class="btn btn-neutral rounded-full px-6">Close</button>
                </form>
            </div>
        </div>
    </dialog>

    <!-- Missing Reports Modal -->
    <dialog id="missingReportsModal" class="modal modal-full">
        <div class="modal-box w-full h-full flex flex-col p-0">
            <div class="p-6 bg-gray-50 border-b">
                <h3 class="font-bold text-2xl" id="missingReportsTitle"></h3>
            </div>
            <div id="missingReportsContent" class="flex-grow overflow-y-auto p-6">
                <!-- Content will be dynamically inserted here -->
            </div>
            <div class="modal-action p-6 bg-gray-50 border-t">
                <form method="dialog">
                    <button class="btn btn-neutral rounded-full px-6">Close</button>
                </form>
            </div>
        </div>
    </dialog>
</div>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    // Define materialColors in the global scope
    const materialColors = [
        '#F44336', '#E91E63', '#9C27B0', '#673AB7', '#3F51B5', '#2196F3', '#03A9F4', '#00BCD4',
        '#009688', '#4CAF50', '#8BC34A', '#CDDC39', '#FFEB3B', '#FFC107', '#FF9800', '#FF5722'
    ];

    document.addEventListener('DOMContentLoaded', function() {
        // Chart data preparation
        const performanceData = @json($performanceData);
        const overallPerformance = {
            met: 0,
            progressing: 0,
            notPerforming: 0
        };
        const clusterPerformance = [];

        Object.values(performanceData).forEach(cluster => {
            overallPerformance.met += cluster.metCount;
            overallPerformance.progressing += cluster.progressingCount;
            overallPerformance.notPerforming += cluster.notPerformingCount;
            clusterPerformance.push({
                cluster: cluster.clusterName,
                performance: cluster.overallMetPercentage
            });
        });

        // Sort cluster performance
        clusterPerformance.sort((a, b) => b.performance - a.performance);

        // All Clusters Performance Chart
        new ApexCharts(document.querySelector("#allClustersPerformanceChart"), {
            series: [{
                data: clusterPerformance.map(cp => cp.performance)
            }],
            chart: {
                type: 'bar',
                height: 400,
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    distributed: true,
                    dataLabels: {
                        position: 'top',
                    },
                }
            },
            dataLabels: {
                enabled: true,
                offsetX: -6,
                style: {
                    fontSize: '12px',
                    colors: ['#fff']
                }
            },
            xaxis: {
                categories: clusterPerformance.map(cp => cp.cluster),
                labels: {
                    formatter: function(val) {
                        return val + "%"
                    }
                }
            },
            yaxis: {
                labels: {
                    show: true
                }
            },
            colors: materialColors,
            title: {
                text: 'All Clusters Performance',
                align: 'center',
                style: {
                    fontSize: '18px'
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return value + "%"
                    }
                }
            }
        }).render();

        // Overall Performance Chart
        new ApexCharts(document.querySelector("#overallPerformanceChart"), {
            series: [overallPerformance.met, overallPerformance.progressing, overallPerformance
                .notPerforming
            ],
            chart: {
                type: 'donut',
                height: 256,
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif'
            },
            labels: ['Met', 'Progressing', 'Not Performing'],
            colors: ['#4CAF50', '#FFC107', '#F44336'],
            legend: {
                position: 'bottom'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        }).render();

        // Top Performing Clusters Chart
        new ApexCharts(document.querySelector("#topPerformingClustersChart"), {
            series: [{
                data: clusterPerformance.slice(0, 5).map(cp => cp.performance)
            }],
            chart: {
                type: 'bar',
                height: 256,
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif'
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    distributed: true,
                    dataLabels: {
                        position: 'top',
                    },
                }
            },
            dataLabels: {
                enabled: true,
                offsetX: -6,
                style: {
                    fontSize: '12px',
                    colors: ['#fff']
                }
            },
            xaxis: {
                categories: clusterPerformance.slice(0, 5).map(cp => cp.cluster),
                labels: {
                    formatter: function(val) {
                        return val + "%"
                    }
                }
            },
            colors: materialColors.slice(0, 5),
            title: {
                text: 'Top 5 Performing Clusters',
                align: 'center',
                style: {
                    fontSize: '14px'
                }
            },
            tooltip: {
                y: {
                    formatter: function(value) {
                        return value + "%"
                    }
                }
            }
        }).render();
    });

    function showClusterDetails(clusterId) {
        const modal = document.getElementById('clusterDetailsModal');
        const title = document.getElementById('clusterDetailsTitle');
        const content = document.getElementById('clusterDetailsContent');
        const clusterData = @json($performanceData)[clusterId];

        title.textContent = `${clusterData.clusterName} Details`;

        let html = `
        <div class="mb-6">
          <h4 class="font-semibold text-lg mb-4">Performance Breakdown</h4>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl shadow-lg p-4 flex flex-col justify-between" style="background: linear-gradient(135deg, #4CAF50, #8BC34A);">
              <span class="text-white font-semibold">Met</span>
              <div class="text-3xl font-bold text-white mt-2">${clusterData.overallMetPercentage}%</div>
              <div class="w-full bg-white/30 h-2 rounded-full mt-2">
                <div class="bg-white h-2 rounded-full" style="width: ${clusterData.overallMetPercentage}%"></div>
              </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-4 flex flex-col justify-between" style="background: linear-gradient(135deg, #FFC107, #FFE082);">
              <span class="text-white font-semibold">Progressing</span>
              <div class="text-3xl font-bold text-white mt-2">${clusterData.overallProgressingPercentage}%</div>
              <div class="w-full bg-white/30 h-2 rounded-full mt-2">
                <div class="bg-white h-2 rounded-full" style="width: ${clusterData.overallProgressingPercentage}%"></div>
              </div>
            </div>
            <div class="bg-white rounded-2xl shadow-lg p-4 flex flex-col justify-between" style="background: linear-gradient(135deg, #F44336, #FF8A65);">
              <span class="text-white font-semibold">Not Performing</span>
              <div class="text-3xl font-bold text-white mt-2">${clusterData.overallNotPerformingPercentage}%</div>
              <div class="w-full bg-white/30 h-2 rounded-full mt-2">
                <div class="bg-white h-2 rounded-full" style="width: ${clusterData.overallNotPerformingPercentage}%"></div>
              </div>
            </div>
          </div>
        </div>
        <div>
          <h4 class="font-semibold text-lg mb-4">Indicator Details</h4>
          <div class="overflow-x-auto">
            <table class="table table-compact w-full">
              <thead>
                <tr>
                  <th>Indicator</th>
                  <th>Baseline</th>
                  <th>Target</th>
                  <th>Score</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                ${clusterData.indicators.map(indicator => `
                  <tr>
                    <td>${indicator.name}</td>
                    <td>${indicator.baseline ?? 'N/A'}</td>
                    <td>${indicator.target ?? 'N/A'}</td>
                    <td>${indicator.score ?? 'N/A'}</td>
                    <td>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        indicator.status === 'met' ? 'bg-green-100 text-green-800' :
                        (indicator.status === 'progressing' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800')
                      }">
                        ${indicator.status.charAt(0).toUpperCase() + indicator.status.slice(1)}
                      </span>
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      `;

        if (!clusterData.missingReports.length) {
            html += `
          <div class="mt-6">
            <p class="text-green-600 font-semibold">No missing reports for this cluster.</p>
          </div>
        `;
        } else {
            html += `
          <div class="mt-6">
            <button class="btn btn-warning animate-pulse" onclick="showMissingReports('${clusterId}')">
              View Missing Reports
            </button>
          </div>
        `;
        }

        content.innerHTML = html;
        modal.showModal();
    }

    function showMissingReports(clusterId) {
        const clusterDetailsModal = document.getElementById('clusterDetailsModal');
        const missingReportsModal = document.getElementById('missingReportsModal');
        const title = document.getElementById('missingReportsTitle');
        const content = document.getElementById('missingReportsContent');
        const clusterData = @json($performanceData)[clusterId];

        clusterDetailsModal.close();

        title.textContent = `Missing Reports for ${clusterData.clusterName}`;

        let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
        clusterData.missingReports.forEach((report, index) => {
            const cardColor = materialColors[index % materialColors.length];
            html += `
          <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-4" style="background: linear-gradient(135deg, ${cardColor}, ${lightenColor(cardColor, 20)});">
              <h4 class="text-lg font-semibold text-white">${report.indicator}</h4>
            </div>
            <div class="p-4">
              <p class="text-sm text-gray-600 mb-2">Missing Clusters:</p>
              <div class="flex flex-wrap gap-2">
                ${report.missingClusters.map(cluster => `
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: ${cardColor}20; color: ${cardColor};">
                    ${cluster}
                  </span>
                `).join('')}
              </div>
            </div>
          </div>
        `;
        });
        html += '</div>';

        content.innerHTML = html;
        missingReportsModal.showModal();
    }

    // Helper function to lighten a color
    function lightenColor(color, percent) {
        const num = parseInt(color.replace("#", ""), 16),
            amt = Math.round(2.55 * percent),
            R = (num >> 16) + amt,
            G = (num >> 8 & 0x00FF) + amt,
            B = (num & 0x0000FF) + amt;
        return "#" + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 + (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
            (B < 255 ? B < 1 ? 0 : B : 255)).toString(16).slice(1);
    }

    // Close missing reports modal and reopen cluster details modal
    document.getElementById('missingReportsModal').addEventListener('close', function() {
        document.getElementById('clusterDetailsModal').showModal();
    });
</script>

<style>
    /* iOS-inspired styles */
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    .btn {
        @apply rounded-full text-sm font-medium px-4 py-2 transition-all duration-200;
    }

    .btn-primary {
        @apply bg-blue-500 text-white hover:bg-blue-600;
    }

    .btn-outline {
        @apply border border-gray-300 text-gray-700 hover:bg-gray-100;
    }

    .card {
        @apply rounded-2xl border border-gray-200;
    }

    .badge {
        @apply rounded-full px-2 py-1 text-xs font-medium;
    }

    .table {
        @apply rounded-lg overflow-hidden;
    }

    .table th {
        @apply bg-gray-100 text-gray-600 font-medium text-sm uppercase;
    }

    .table td {
        @apply text-sm py-3;
    }

    .modal-box {
        @apply rounded-2xl shadow-lg p-6;
    }

    /* Custom scrollbar for iOS feel */
    .overflow-y-auto {
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
    }

    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: transparent;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 3px;
    }

    #missingReportsContent {
        scrollbar-width: thin;
        scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
    }

    #missingReportsContent::-webkit-scrollbar {
        width: 4px;
    }

    #missingReportsContent::-webkit-scrollbar-track {
        background: transparent;
    }

    #missingReportsContent::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
        border-radius: 2px;
    }

    /* Animations */
    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: .5;
        }
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }

    .modal-full .modal-box {
        max-width: 100%;
        width: 100%;
        height: 100vh;
        max-height: 100vh;
        border-radius: 0;
    }

    @media (max-width: 640px) {
        .modal-full .modal-box {
            padding: 1rem;
        }
    }
</style>
