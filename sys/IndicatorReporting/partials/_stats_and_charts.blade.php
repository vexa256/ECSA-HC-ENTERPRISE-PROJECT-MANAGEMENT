<!-- Stats and Charts with iOS-inspired daisyUI design -->
<div class="p-4 w-full">
    <!-- Stat Cards with iOS-style design -->


    <!-- Cards container replicating col-md-3 behavior in Bootstrap -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <!-- Card 1 -->
        <div class="card bg-accent-content text-light shadow-lg rounded-2xl overflow-hidden">
            <div class="card-body p-4 flex flex-row items-center text-center">
                <div class="rounded-full bg-white p-3 mr-4">
                    <!-- Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div>
                    <h6 class="opacity-80 text-sm font-medium mb-1" style="color: white">
                        Total Indicators
                    </h6>
                    <h2 class="text-2xl font-bold" style="color: white">
                        {{ $totalIndicators }}
                    </h2>
                </div>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="card bg-success text-success-content shadow-lg rounded-2xl overflow-hidden">
            <div class="card-body p-4 flex flex-row items-center text-center">
                <div class="rounded-full bg-white p-3 mr-4">
                    <!-- Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h6 class="opacity-80 text-sm font-medium mb-1">
                        Reported Indicators
                    </h6>
                    <h2 class="text-2xl font-bold">
                        {{ $reportedIndicators }}
                    </h2>
                </div>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="card bg-warning text-warning-content shadow-lg rounded-2xl overflow-hidden">
            <div class="card-body p-4 flex flex-row items-center text-center">
                <div class="rounded-full bg-white p-3 mr-4">
                    <!-- Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-warning" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h6 class="opacity-80 text-sm font-medium mb-1">
                        Remaining Indicators
                    </h6>
                    <h2 class="text-2xl font-bold">
                        {{ $totalIndicators - $reportedIndicators }}
                    </h2>
                </div>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="card bg-info text-info-content shadow-lg rounded-2xl overflow-hidden">
            <div class="card-body p-4 flex flex-row items-center text-center">
                <div class="rounded-full bg-white p-3 mr-4">
                    <!-- Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-info" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <div>
                    <h6 class="opacity-80 text-sm font-medium mb-1">
                        Overall Progress
                    </h6>
                    <h2 class="text-2xl font-bold">
                        {{ number_format($progress, 2) }}%
                    </h2>
                </div>
            </div>
            <div class="h-1 w-full bg-info-content bg-opacity-20">
                <div class="h-1 bg-white" style="width: {{ $progress }}%;"></div>
            </div>
        </div>
    </div>





















    <!-- Charts Section with iOS-style design -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">

        <!-- Reporting Progress Chart -->
        <div class="card bg-base-100 shadow-lg rounded-2xl overflow-hidden border border-base-200">
            <div
                class="card-header bg-gradient-to-r from-base-100 to-base-200 p-4 flex justify-between items-center text-center border-b border-base-200">
                <h5 class="font-medium text-base-content">Reporting Progress</h5>
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-sm btn-ghost btn-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                    </label>
                    <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                        <li>
                            <a class="flex items-center text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Export Data
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Chart
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="h-64">
                    <canvas id="reportingProgressChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Completion Breakdown Chart -->
        <div class="card bg-base-100 shadow-lg rounded-2xl overflow-hidden border border-base-200">
            <div
                class="card-header bg-gradient-to-r from-base-100 to-base-200 p-4 flex justify-between items-center text-center border-b border-base-200">
                <h5 class="font-medium text-base-content">Completion Breakdown</h5>
                <div class="dropdown dropdown-end">
                    <label tabindex="0" class="btn btn-sm btn-ghost btn-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                        </svg>
                    </label>
                    <ul tabindex="0" class="dropdown-content menu p-2 shadow-lg bg-base-100 rounded-box w-52">
                        <li>
                            <a class="flex items-center text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Export Data
                            </a>
                        </li>
                        <li>
                            <a class="flex items-center text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print Chart
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="h-64">
                    <canvas id="completionBreakdownChart"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // iOS-inspired chart styles
        Chart.defaults.font.family =
            '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif';
        Chart.defaults.font.size = 12;
        Chart.defaults.color = '#64748b';

        // Reporting Progress Chart
        var ctx = document.getElementById('reportingProgressChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Total', 'Reported', 'Remaining'],
                datasets: [{
                    label: 'Indicators',
                    data: [
                        {{ $totalIndicators }},
                        {{ $reportedIndicators }},
                        {{ $totalIndicators - $reportedIndicators }}
                    ],
                    borderColor: '#38bdf8', // iOS blue
                    backgroundColor: 'rgba(56, 189, 248, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#38bdf8',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1e293b',
                        bodyColor: '#475569',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        cornerRadius: 12,
                        displayColors: false,
                        padding: 12
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(226, 232, 240, 0.5)',
                            drawBorder: false
                        },
                        ticks: {
                            padding: 10
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            padding: 10
                        }
                    }
                }
            }
        });

        // Completion Breakdown Chart
        var ctx2 = document.getElementById('completionBreakdownChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['Reported', 'Remaining'],
                datasets: [{
                    data: [
                        {{ $reportedIndicators }},
                        {{ $totalIndicators - $reportedIndicators }}
                    ],
                    backgroundColor: [
                        '#4ade80', // iOS green
                        '#fbbf24' // iOS yellow
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    borderRadius: 4,
                    hoverOffset: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1e293b',
                        bodyColor: '#475569',
                        borderColor: '#e2e8f0',
                        borderWidth: 1,
                        cornerRadius: 12,
                        displayColors: false,
                        padding: 12
                    }
                }
            }
        });
    });
</script>
