<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
    <!-- Header Section -->
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">{{ $reportTitle }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Cluster: <span class="font-medium text-primary">{{ $cluster->Cluster_Name }}</span>
                    @if($reportType === 'specific')
                        • Timeline: <span class="font-medium text-primary">{{ $timelines[0]->ReportName }}</span>
                    @else
                        • Year: <span class="font-medium text-primary">{{ $reportYear }}</span>
                        @if(!empty($timelineType))
                            • Type: <span class="font-medium text-primary">{{ $timelineType }}</span>
                        @endif
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="filter-button" class="btn btn-sm btn-outline gap-2">
                    <i class="iconify-inline" data-icon="lucide:filter"></i>
                    Filter
                </button>
                <button type="button" id="export-button" class="btn btn-sm btn-outline gap-2">
                    <i class="iconify-inline" data-icon="lucide:download"></i>
                    Export
                </button>
                <a href="{{ route('indicator.select.timeline', ['cluster_id' => $cluster->ClusterID]) }}" class="btn btn-sm btn-ghost gap-2">
                    <i class="iconify-inline" data-icon="lucide:arrow-left"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Error Alert -->
    @if(isset($error) || session('error'))
    <div class="mx-6 mt-4 alert alert-error shadow-sm text-sm">
        <i class="iconify-inline" data-icon="lucide:alert-circle"></i>
        <span>{{ $error ?? session('error') }}</span>
    </div>
    @endif

    <!-- Performance Summary Dashboard -->
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Overall Performance Score -->
            @php
                $overallScore = 0;
                $indicatorsWithData = 0;

                foreach ($performanceResults as $result) {
                    if ($result['score'] !== null) {
                        $overallScore += $result['score'];
                        $indicatorsWithData++;
                    }
                }

                $averageScore = $indicatorsWithData > 0 ? $overallScore / $indicatorsWithData : 0;
                $scoreColor = 'text-red-500';
                $scoreBackground = 'bg-red-50 dark:bg-red-900/20';

                if ($averageScore >= 90) {
                    $scoreColor = 'text-green-500';
                    $scoreBackground = 'bg-green-50 dark:bg-green-900/20';
                } elseif ($averageScore >= 50) {
                    $scoreColor = 'text-blue-500';
                    $scoreBackground = 'bg-blue-50 dark:bg-blue-900/20';
                } elseif ($averageScore >= 10) {
                    $scoreColor = 'text-orange-500';
                    $scoreBackground = 'bg-orange-50 dark:bg-orange-900/20';
                }

                // Calculate category counts
                $categoryCounts = [
                    'Met' => 0,
                    'On Track' => 0,
                    'In Progress' => 0,
                    'Not Performing' => 0,
                    'No Data' => 0,
                    'No Target' => 0,
                    'Invalid Target' => 0
                ];

                foreach ($performanceResults as $result) {
                    $category = $result['performanceCategory'];
                    if (isset($categoryCounts[$category])) {
                        $categoryCounts[$category]++;
                    }
                }

                $totalIndicators = count($performanceResults);
                $dataCompleteness = $totalIndicators > 0 ? ($indicatorsWithData / $totalIndicators) * 100 : 0;
            @endphp

            <div class="card {{ $scoreBackground }} shadow-sm">
                <div class="card-body p-4">
                    <h2 class="card-title text-sm text-gray-600 dark:text-gray-300">Overall Performance</h2>
                    <div class="flex items-end justify-between">
                        <div class="flex items-baseline">
                            <span class="text-3xl font-bold {{ $scoreColor }}">{{ number_format($averageScore, 1) }}%</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $indicatorsWithData }} of {{ $totalIndicators }} indicators
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Performance Category</div>
                        <div class="text-sm font-semibold {{ $scoreColor }}">
                            @if($averageScore < 10)
                                Not Performing
                            @elseif($averageScore < 50)
                                In Progress
                            @elseif($averageScore < 90)
                                On Track
                            @else
                                Met
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Completeness -->
            <div class="card bg-blue-50 dark:bg-blue-900/20 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="card-title text-sm text-gray-600 dark:text-gray-300">Data Completeness</h2>
                    <div class="flex items-end justify-between">
                        <div class="flex items-baseline">
                            <span class="text-3xl font-bold text-blue-500">{{ number_format($dataCompleteness, 1) }}%</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $indicatorsWithData }} with data
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ min($dataCompleteness, 100) }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Distribution -->
            <div class="card bg-purple-50 dark:bg-purple-900/20 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="card-title text-sm text-gray-600 dark:text-gray-300">Performance Distribution</h2>
                    <div class="flex items-center justify-between mt-1">
                        <div class="flex flex-col gap-1">
                            @php
                                $categoryColors = [
                                    'Met' => 'bg-green-500',
                                    'On Track' => 'bg-blue-500',
                                    'In Progress' => 'bg-orange-500',
                                    'Not Performing' => 'bg-red-500',
                                    'No Data' => 'bg-gray-400',
                                    'No Target' => 'bg-gray-600',
                                    'Invalid Target' => 'bg-gray-800'
                                ];
                            @endphp

                            @foreach(['Met', 'On Track', 'In Progress', 'Not Performing'] as $category)
                                @if($categoryCounts[$category] > 0)
                                    <div class="flex items-center gap-2">
                                        <div class="w-3 h-3 rounded-full {{ $categoryColors[$category] }}"></div>
                                        <span class="text-xs text-gray-600 dark:text-gray-300">{{ $category }}: {{ $categoryCounts[$category] }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <button type="button" id="view-distribution-button" class="btn btn-xs btn-ghost">
                            Details
                        </button>
                    </div>
                </div>
            </div>

            <!-- Data Quality -->
            <div class="card bg-amber-50 dark:bg-amber-900/20 shadow-sm">
                <div class="card-body p-4">
                    <h2 class="card-title text-sm text-gray-600 dark:text-gray-300">Data Quality</h2>
                    <div class="flex items-end justify-between">
                        <div class="flex items-baseline">
                            <span class="text-3xl font-bold text-amber-500">
                                {{ $dataAccuracyIssues['count'] }}
                            </span>
                            <span class="ml-1 text-sm text-gray-500 dark:text-gray-400">issues</span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $dataAccuracyIssues['summary']['error'] }} errors
                            </span>
                        </div>
                    </div>
                    <div class="mt-2">
                        <div class="flex items-center gap-2">
                            @if($dataAccuracyIssues['summary']['error'] > 0)
                                <div class="badge badge-error badge-sm">{{ $dataAccuracyIssues['summary']['error'] }} errors</div>
                            @endif
                            @if($dataAccuracyIssues['summary']['warning'] > 0)
                                <div class="badge badge-warning badge-sm">{{ $dataAccuracyIssues['summary']['warning'] }} warnings</div>
                            @endif
                            @if($dataAccuracyIssues['summary']['info'] > 0)
                                <div class="badge badge-info badge-sm">{{ $dataAccuracyIssues['summary']['info'] }} notices</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Charts -->
        <div class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Performance Overview</h2>
                <div class="tabs tabs-boxed bg-gray-100 dark:bg-gray-700 p-1">
                    <button type="button" class="tab tab-active" data-tab="overview">Overview</button>
                    <button type="button" class="tab" data-tab="strategic">Strategic Objectives</button>
                    <button type="button" class="tab" data-tab="indicators">Indicators</button>
                    <button type="button" class="tab" data-tab="insights">Insights</button>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Overview Tab -->
                <div id="tab-overview" class="tab-pane active">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Performance Distribution Chart -->
                        <div class="card bg-white dark:bg-gray-800 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title text-base text-gray-700 dark:text-gray-200">Performance Category Distribution</h3>
                                <div id="performance-distribution-chart" class="h-64"></div>
                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    @foreach(['Met', 'On Track', 'In Progress', 'Not Performing', 'No Data', 'No Target'] as $category)
                                        @if($categoryCounts[$category] > 0)
                                            <div class="flex items-center gap-2">
                                                <div class="w-3 h-3 rounded-full {{ $categoryColors[$category] }}"></div>
                                                <span class="text-xs text-gray-600 dark:text-gray-300">
                                                    {{ $category }}: {{ $categoryCounts[$category] }}
                                                    ({{ number_format(($categoryCounts[$category] / $totalIndicators) * 100, 1) }}%)
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Strategic Objectives Performance -->
                        <div class="card bg-white dark:bg-gray-800 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title text-base text-gray-700 dark:text-gray-200">Strategic Objectives Performance</h3>
                                <div id="strategic-objectives-chart" class="h-64"></div>
                                <div class="mt-2">
                                    <button type="button" id="view-so-details-button" class="btn btn-sm btn-outline w-full">
                                        View Detailed Breakdown
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Top Performers -->
                        <div class="card bg-white dark:bg-gray-800 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title text-base text-gray-700 dark:text-gray-200">Top Performing Indicators</h3>
                                <div class="overflow-x-auto">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Indicator</th>
                                                <th>Score</th>
                                                <th>Category</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                // Sort by score (descending)
                                                $sortedResults = $performanceResults;
                                                usort($sortedResults, function($a, $b) {
                                                    if ($a['score'] === null) return 1;
                                                    if ($b['score'] === null) return -1;
                                                    return $b['score'] <=> $a['score'];
                                                });
                                                $topPerformers = array_slice($sortedResults, 0, 5);
                                            @endphp

                                            @foreach($topPerformers as $result)
                                                @if($result['score'] !== null)
                                                    <tr>
                                                        <td class="whitespace-normal">
                                                            <div class="font-medium">{{ $result['indicator']['Indicator_Number'] }}</div>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[200px]" title="{{ $result['indicator']['Indicator_Name'] }}">
                                                                {{ $result['indicator']['Indicator_Name'] }}
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="font-medium
                                                                @if($result['score'] >= 90) text-green-500
                                                                @elseif($result['score'] >= 50) text-blue-500
                                                                @elseif($result['score'] >= 10) text-orange-500
                                                                @else text-red-500 @endif">
                                                                {{ number_format($result['score'], 1) }}%
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="badge
                                                                @if($result['performanceCategory'] == 'Met') badge-success
                                                                @elseif($result['performanceCategory'] == 'On Track') badge-info
                                                                @elseif($result['performanceCategory'] == 'In Progress') badge-warning
                                                                @elseif($result['performanceCategory'] == 'Not Performing') badge-error
                                                                @else badge-ghost @endif">
                                                                {{ $result['performanceCategory'] }}
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Key Insights -->
                        <div class="card bg-white dark:bg-gray-800 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title text-base text-gray-700 dark:text-gray-200">Key Insights</h3>
                                <ul class="space-y-2 mt-2">
                                    @foreach(array_slice($insights['summary'] ?? [], 0, 5) as $insight)
                                        <li class="flex items-start gap-2">
                                            <i class="iconify-inline text-blue-500 mt-0.5" data-icon="lucide:info"></i>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $insight }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-4">
                                    <button type="button" id="view-all-insights-button" class="btn btn-sm btn-outline w-full">
                                        View All Insights
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Strategic Objectives Tab -->
                <div id="tab-strategic" class="tab-pane hidden">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Strategic Objectives Radar Chart -->
                        <div class="card bg-white dark:bg-gray-800 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title text-base text-gray-700 dark:text-gray-200">Strategic Objectives Performance Comparison</h3>
                                <div id="strategic-radar-chart" class="h-80"></div>
                            </div>
                        </div>

                        <!-- Strategic Objectives Breakdown -->
                        <div class="space-y-6">
                            @foreach($resultsBySO as $soId => $soData)
                                @php
                                    $so = $soData['so'];
                                    $indicators = $soData['indicators'];

                                    // Calculate SO average score
                                    $soTotalScore = 0;
                                    $soIndicatorsWithData = 0;

                                    foreach($indicators as $indicator) {
                                        if ($indicator['score'] !== null) {
                                            $soTotalScore += $indicator['score'];
                                            $soIndicatorsWithData++;
                                        }
                                    }

                                    $soAverageScore = $soIndicatorsWithData > 0 ? $soTotalScore / $soIndicatorsWithData : 0;

                                    // Determine color based on score
                                    $soScoreColor = 'text-red-500';
                                    $soScoreBg = 'bg-red-50 dark:bg-red-900/20';

                                    if ($soAverageScore >= 90) {
                                        $soScoreColor = 'text-green-500';
                                        $soScoreBg = 'bg-green-50 dark:bg-green-900/20';
                                    } elseif ($soAverageScore >= 50) {
                                        $soScoreColor = 'text-blue-500';
                                        $soScoreBg = 'bg-blue-50 dark:bg-blue-900/20';
                                    } elseif ($soAverageScore >= 10) {
                                        $soScoreColor = 'text-orange-500';
                                        $soScoreBg = 'bg-orange-50 dark:bg-orange-900/20';
                                    }

                                    // Count performance categories for this SO
                                    $soCategoryCounts = [
                                        'Met' => 0,
                                        'On Track' => 0,
                                        'In Progress' => 0,
                                        'Not Performing' => 0,
                                        'No Data' => 0,
                                        'No Target' => 0,
                                        'Invalid Target' => 0
                                    ];

                                    foreach($indicators as $indicator) {
                                        $category = $indicator['performanceCategory'];
                                        if (isset($soCategoryCounts[$category])) {
                                            $soCategoryCounts[$category]++;
                                        }
                                    }
                                @endphp

                                <div class="card bg-white dark:bg-gray-800 shadow-sm">
                                    <div class="card-body">
                                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                            <div>
                                                <h3 class="card-title text-base text-gray-700 dark:text-gray-200">
                                                    {{ $so->SO_Number }}: {{ $so->SO_Name }}
                                                </h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ $so->Description }}
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-4">
                                                <div class="{{ $soScoreBg }} px-4 py-2 rounded-lg">
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Performance Score</div>
                                                    <div class="text-xl font-bold {{ $soScoreColor }}">{{ number_format($soAverageScore, 1) }}%</div>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline toggle-indicators" data-so="{{ $soId }}">
                                                    <span class="show-text">Show Indicators</span>
                                                    <span class="hide-text hidden">Hide Indicators</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Performance Category Pills -->
                                        <div class="flex flex-wrap gap-2 mt-4">
                                            @foreach(['Met', 'On Track', 'In Progress', 'Not Performing', 'No Data', 'No Target'] as $category)
                                                @if($soCategoryCounts[$category] > 0)
                                                    <div class="badge
                                                        @if($category == 'Met') badge-success
                                                        @elseif($category == 'On Track') badge-info
                                                        @elseif($category == 'In Progress') badge-warning
                                                        @elseif($category == 'Not Performing') badge-error
                                                        @elseif($category == 'No Data') badge-ghost
                                                        @else badge-ghost @endif">
                                                        {{ $category }}: {{ $soCategoryCounts[$category] }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        <!-- Indicators Table (Hidden by Default) -->
                                        <div class="indicators-table hidden mt-6" id="indicators-{{ $soId }}">
                                            <div class="overflow-x-auto">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Indicator</th>
                                                            <th>Target</th>
                                                            <th>Actual</th>
                                                            <th>Performance</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($indicators as $indicator)
                                                            <tr>
                                                                <td class="whitespace-normal">
                                                                    <div class="font-medium">{{ $indicator['indicator']['Indicator_Number'] }}</div>
                                                                    <div class="text-xs text-gray-500 dark:text-gray-400 max-w-[250px]">
                                                                        {{ $indicator['indicator']['Indicator_Name'] }}
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    @if($indicator['target'])
                                                                        <div class="font-medium">{{ $indicator['target']['Target_Value'] }}</div>
                                                                    @else
                                                                        <div class="text-xs text-gray-500 dark:text-gray-400">No target set</div>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($indicator['actual'])
                                                                        <div class="font-medium">{{ $indicator['actual']['Response'] }}</div>
                                                                    @else
                                                                        <div class="text-xs text-gray-500 dark:text-gray-400">No data</div>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($indicator['score'] !== null)
                                                                        <div class="font-medium
                                                                            @if($indicator['score'] >= 90) text-green-500
                                                                            @elseif($indicator['score'] >= 50) text-blue-500
                                                                            @elseif($indicator['score'] >= 10) text-orange-500
                                                                            @else text-red-500 @endif">
                                                                            {{ number_format($indicator['score'], 1) }}%
                                                                        </div>
                                                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                            {{ $indicator['performanceCategory'] }}
                                                                        </div>
                                                                    @else
                                                                        <div class="badge badge-ghost">
                                                                            {{ $indicator['performanceCategory'] }}
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <button type="button" class="btn btn-xs btn-ghost view-indicator-details"
                                                                        data-indicator-id="{{ $indicator['indicator']['id'] }}">
                                                                        Details
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
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Indicators Tab -->
                <div id="tab-indicators" class="tab-pane hidden">
                    <div class="card bg-white dark:bg-gray-800 shadow-sm">
                        <div class="card-body">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                                <h3 class="card-title text-base text-gray-700 dark:text-gray-200">All Indicators</h3>
                                <div class="flex items-center gap-2">
                                    <div class="form-control">
                                        <input type="text" id="indicator-search" placeholder="Search indicators..." class="input input-sm input-bordered w-full max-w-xs" />
                                    </div>
                                    <div class="form-control">
                                        <select id="category-filter" class="select select-sm select-bordered">
                                            <option value="">All Categories</option>
                                            @foreach(['Met', 'On Track', 'In Progress', 'Not Performing', 'No Data', 'No Target'] as $category)
                                                @if($categoryCounts[$category] > 0)
                                                    <option value="{{ $category }}">{{ $category }} ({{ $categoryCounts[$category] }})</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="table table-sm" id="indicators-table">
                                    <thead>
                                        <tr>
                                            <th>Indicator</th>
                                            <th>Strategic Objective</th>
                                            <th>Target</th>
                                            <th>Actual</th>
                                            <th>Performance</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($performanceResults as $result)
                                            @php
                                                $soId = $result['indicator']['SO_ID'];
                                                $so = $strategicObjectives[$soId] ?? null;
                                            @endphp
                                            <tr data-category="{{ $result['performanceCategory'] }}">
                                                <td class="whitespace-normal">
                                                    <div class="font-medium">{{ $result['indicator']['Indicator_Number'] }}</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 max-w-[250px]">
                                                        {{ $result['indicator']['Indicator_Name'] }}
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($so)
                                                        <div class="text-xs">{{ $so->SO_Number }}</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[150px]" title="{{ $so->SO_Name }}">
                                                            {{ $so->SO_Name }}
                                                        </div>
                                                    @else
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">Unknown</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($result['target'])
                                                        <div class="font-medium">{{ $result['target']['Target_Value'] }}</div>
                                                    @else
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">No target set</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($result['actual'])
                                                        <div class="font-medium">{{ $result['actual']['Response'] }}</div>
                                                    @else
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">No data</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($result['score'] !== null)
                                                        <div class="font-medium
                                                            @if($result['score'] >= 90) text-green-500
                                                            @elseif($result['score'] >= 50) text-blue-500
                                                            @elseif($result['score'] >= 10) text-orange-500
                                                            @else text-red-500 @endif">
                                                            {{ number_format($result['score'], 1) }}%
                                                        </div>
                                                        <div class="badge
                                                            @if($result['performanceCategory'] == 'Met') badge-success
                                                            @elseif($result['performanceCategory'] == 'On Track') badge-info
                                                            @elseif($result['performanceCategory'] == 'In Progress') badge-warning
                                                            @elseif($result['performanceCategory'] == 'Not Performing') badge-error
                                                            @else badge-ghost @endif">
                                                            {{ $result['performanceCategory'] }}
                                                        </div>
                                                    @else
                                                        <div class="badge badge-ghost">
                                                            {{ $result['performanceCategory'] }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-xs btn-ghost view-indicator-details"
                                                        data-indicator-id="{{ $result['indicator']['id'] }}">
                                                        Details
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

                <!-- Insights Tab -->
                <div id="tab-insights" class="tab-pane hidden">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Key Insights -->
                        <div class="card bg-white dark:bg-gray-800 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title text-base text-gray-700 dark:text-gray-200">Performance Insights</h3>
                                <ul class="space-y-3 mt-3">
                                    @foreach($insights['summary'] ?? [] as $insight)
                                        <li class="flex items-start gap-2">
                                            <i class="iconify-inline text-blue-500 mt-0.5" data-icon="lucide:info"></i>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $insight }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Recommendations -->
                        <div class="card bg-white dark:bg-gray-800 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title text-base text-gray-700 dark:text-gray-200">Recommendations</h3>
                                <ul class="space-y-3 mt-3">
                                    @foreach($insights['recommendations'] ?? [] as $recommendation)
                                        <li class="flex items-start gap-2">
                                            <i class="iconify-inline
                                                @if($recommendation['priority'] == 'high') text-red-500
                                                @elseif($recommendation['priority'] == 'medium') text-orange-500
                                                @else text-blue-500 @endif mt-0.5"
                                                data-icon="lucide:lightbulb"></i>
                                            <div>
                                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ $recommendation['text'] }}</span>
                                                @if($recommendation['priority'] == 'high')
                                                    <span class="badge badge-error badge-sm ml-1">High Priority</span>
                                                @elseif($recommendation['priority'] == 'medium')
                                                    <span class="badge badge-warning badge-sm ml-1">Medium Priority</span>
                                                @endif
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <!-- Trends -->
                        <div class="card bg-white dark:bg-gray-800 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title text-base text-gray-700 dark:text-gray-200">Trends & Patterns</h3>
                                <ul class="space-y-3 mt-3">
                                    @foreach($insights['trends'] ?? [] as $trend)
                                        <li class="flex items-start gap-2">
                                            <i class="iconify-inline text-purple-500 mt-0.5" data-icon="lucide:trending-up"></i>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">{{ $trend['text'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                @if(!empty($historicalData['byYear']))
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Historical Performance</h4>
                                        <div id="historical-trend-chart" class="h-48"></div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Anomalies -->
                        <div class="card bg-white dark:bg-gray-800 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title text-base text-gray-700 dark:text-gray-200">Anomalies & Data Issues</h3>

                                @if(!empty($insights['anomalies']))
                                    <ul class="space-y-3 mt-3">
                                        @foreach($insights['anomalies'] as $anomaly)
                                            <li class="flex items-start gap-2">
                                                <i class="iconify-inline
                                                    @if($anomaly['severity'] == 'warning') text-orange-500
                                                    @elseif($anomaly['severity'] == 'error') text-red-500
                                                    @else text-blue-500 @endif mt-0.5"
                                                    data-icon="lucide:alert-triangle"></i>
                                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ $anomaly['description'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="alert alert-success text-sm mt-3">
                                        <i class="iconify-inline" data-icon="lucide:check-circle"></i>
                                        <span>No significant anomalies detected in the data.</span>
                                    </div>
                                @endif

                                @if(!empty($dataAccuracyIssues['all']))
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Data Quality Issues</h4>
                                        <div class="overflow-x-auto">
                                            <table class="table table-xs">
                                                <thead>
                                                    <tr>
                                                        <th>Indicator</th>
                                                        <th>Issue</th>
                                                        <th>Severity</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach(array_slice($dataAccuracyIssues['all'], 0, 5) as $issue)
                                                        <tr>
                                                            <td>{{ $issue['indicator']['Indicator_Number'] }}</td>
                                                            <td>{{ $issue['issue'] }}</td>
                                                            <td>
                                                                <div class="badge
                                                                    @if($issue['severity'] == 'error') badge-error
                                                                    @elseif($issue['severity'] == 'warning') badge-warning
                                                                    @else badge-info @endif badge-sm">
                                                                    {{ $issue['severity'] }}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        @if(count($dataAccuracyIssues['all']) > 5)
                                            <div class="mt-2 text-right">
                                                <button type="button" id="view-all-issues-button" class="btn btn-xs btn-ghost">
                                                    View all {{ count($dataAccuracyIssues['all']) }} issues
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Filter Modal -->
    <div id="filter-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Filter Report</h3>
            <form action="{{ route('indicator.filter') }}" method="GET" class="mt-4">
                <input type="hidden" name="cluster_id" value="{{ $cluster->ClusterID }}">
                <input type="hidden" name="report_type" value="{{ $reportType }}">

                @if($reportType === 'specific')
                    <input type="hidden" name="timeline_id" value="{{ $timelines[0]->id ?? '' }}">
                @else
                    <input type="hidden" name="report_year" value="{{ $reportYear }}">
                    <input type="hidden" name="timeline_type" value="{{ $timelineType }}">
                @endif

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Strategic Objective</span>
                    </label>
                    <select name="strategic_objective" class="select select-bordered w-full">
                        <option value="">All Strategic Objectives</option>
                        @foreach($strategicObjectives as $so)
                            <option value="{{ $so->SO_ID }}" {{ $strategicObjectiveFilter == $so->SO_ID ? 'selected' : '' }}>
                                {{ $so->SO_Number }}: {{ $so->SO_Name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Performance Category</span>
                    </label>
                    <select name="performance_category" class="select select-bordered w-full">
                        <option value="">All Categories</option>
                        @foreach($performanceCategories as $category)
                            <option value="{{ $category }}" {{ $performanceCategoryFilter == $category ? 'selected' : '' }}>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn btn-ghost close-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
        <div class="modal-backdrop"></div>
    </div>

    <!-- Indicator Details Modal -->
    <div id="indicator-details-modal" class="modal">
        <div class="modal-box max-w-3xl">
            <h3 class="font-bold text-lg" id="indicator-details-title">Indicator Details</h3>
            <div id="indicator-details-content" class="mt-4">
                <!-- Content will be populated by JavaScript -->
                <div class="skeleton h-32 w-full"></div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn close-modal">Close</button>
            </div>
        </div>
        <div class="modal-backdrop"></div>
    </div>

    <!-- Distribution Details Modal -->
    <div id="distribution-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Performance Distribution</h3>
            <div class="mt-4">
                <div id="distribution-detail-chart" class="h-64"></div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach(['Met', 'On Track', 'In Progress', 'Not Performing', 'No Data', 'No Target', 'Invalid Target'] as $category)
                        @if($categoryCounts[$category] > 0)
                            <div class="flex items-center justify-between p-2 rounded-lg
                                @if($category == 'Met') bg-green-50 dark:bg-green-900/20
                                @elseif($category == 'On Track') bg-blue-50 dark:bg-blue-900/20
                                @elseif($category == 'In Progress') bg-orange-50 dark:bg-orange-900/20
                                @elseif($category == 'Not Performing') bg-red-50 dark:bg-red-900/20
                                @else bg-gray-50 dark:bg-gray-700/50 @endif">
                                <div class="flex items-center gap-2">
                                    <div class="w-4 h-4 rounded-full {{ $categoryColors[$category] }}"></div>
                                    <span class="font-medium">{{ $category }}</span>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold">{{ $categoryCounts[$category] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ number_format(($categoryCounts[$category] / $totalIndicators) * 100, 1) }}%
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn close-modal">Close</button>
            </div>
        </div>
        <div class="modal-backdrop"></div>
    </div>

    <!-- Strategic Objectives Details Modal -->
    <div id="so-details-modal" class="modal">
        <div class="modal-box max-w-4xl">
            <h3 class="font-bold text-lg">Strategic Objectives Performance</h3>
            <div class="mt-4">
                <div id="so-detail-chart" class="h-80"></div>
                <div class="mt-6 overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Strategic Objective</th>
                                <th>Performance Score</th>
                                <th>Indicators</th>
                                <th>Category Breakdown</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resultsBySO as $soId => $soData)
                                @php
                                    $so = $soData['so'];
                                    $indicators = $soData['indicators'];

                                    // Calculate SO average score
                                    $soTotalScore = 0;
                                    $soIndicatorsWithData = 0;

                                    foreach($indicators as $indicator) {
                                        if ($indicator['score'] !== null) {
                                            $soTotalScore += $indicator['score'];
                                            $soIndicatorsWithData++;
                                        }
                                    }

                                    $soAverageScore = $soIndicatorsWithData > 0 ? $soTotalScore / $soIndicatorsWithData : 0;

                                    // Count performance categories for this SO
                                    $soCategoryCounts = [
                                        'Met' => 0,
                                        'On Track' => 0,
                                        'In Progress' => 0,
                                        'Not Performing' => 0,
                                        'No Data' => 0,
                                        'No Target' => 0,
                                        'Invalid Target' => 0
                                    ];

                                    foreach($indicators as $indicator) {
                                        $category = $indicator['performanceCategory'];
                                        if (isset($soCategoryCounts[$category])) {
                                            $soCategoryCounts[$category]++;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <div class="font-medium">{{ $so->SO_Number }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $so->SO_Name }}</div>
                                    </td>
                                    <td>
                                        <div class="font-medium
                                            @if($soAverageScore >= 90) text-green-500
                                            @elseif($soAverageScore >= 50) text-blue-500
                                            @elseif($soAverageScore >= 10) text-orange-500
                                            @else text-red-500 @endif">
                                            {{ number_format($soAverageScore, 1) }}%
                                        </div>
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ count($indicators) }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $soIndicatorsWithData }} with data
                                        </div>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach(['Met', 'On Track', 'In Progress', 'Not Performing'] as $category)
                                                @if($soCategoryCounts[$category] > 0)
                                                    <div class="badge
                                                        @if($category == 'Met') badge-success
                                                        @elseif($category == 'On Track') badge-info
                                                        @elseif($category == 'In Progress') badge-warning
                                                        @elseif($category == 'Not Performing') badge-error
                                                        @else badge-ghost @endif badge-sm">
                                                        {{ $category }}: {{ $soCategoryCounts[$category] }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn close-modal">Close</button>
            </div>
        </div>
        <div class="modal-backdrop"></div>
    </div>

    <!-- All Insights Modal -->
    <div id="all-insights-modal" class="modal">
        <div class="modal-box max-w-4xl">
            <h3 class="font-bold text-lg">Performance Insights & Recommendations</h3>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Key Insights</h4>
                    <ul class="space-y-3">
                        @foreach($insights['summary'] ?? [] as $insight)
                            <li class="flex items-start gap-2">
                                <i class="iconify-inline text-blue-500 mt-0.5" data-icon="lucide:info"></i>
                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ $insight }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <h4 class="font-medium text-gray-700 dark:text-gray-200 mt-6 mb-2">Trends & Patterns</h4>
                    <ul class="space-y-3">
                        @foreach($insights['trends'] ?? [] as $trend)
                            <li class="flex items-start gap-2">
                                <i class="iconify-inline text-purple-500 mt-0.5" data-icon="lucide:trending-up"></i>
                                <span class="text-sm text-gray-600 dark:text-gray-300">{{ $trend['text'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h4 class="font-medium text-gray-700 dark:text-gray-200 mb-2">Recommendations</h4>
                    <ul class="space-y-3">
                        @foreach($insights['recommendations'] ?? [] as $recommendation)
                            <li class="flex items-start gap-2">
                                <i class="iconify-inline
                                    @if($recommendation['priority'] == 'high') text-red-500
                                    @elseif($recommendation['priority'] == 'medium') text-orange-500
                                    @else text-blue-500 @endif mt-0.5"
                                    data-icon="lucide:lightbulb"></i>
                                <div>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $recommendation['text'] }}</span>
                                    @if($recommendation['priority'] == 'high')
                                        <span class="badge badge-error badge-sm ml-1">High Priority</span>
                                    @elseif($recommendation['priority'] == 'medium')
                                        <span class="badge badge-warning badge-sm ml-1">Medium Priority</span>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    <h4 class="font-medium text-gray-700 dark:text-gray-200 mt-6 mb-2">Anomalies & Data Issues</h4>
                    @if(!empty($insights['anomalies']))
                        <ul class="space-y-3">
                            @foreach($insights['anomalies'] as $anomaly)
                                <li class="flex items-start gap-2">
                                    <i class="iconify-inline
                                        @if($anomaly['severity'] == 'warning') text-orange-500
                                        @elseif($anomaly['severity'] == 'error') text-red-500
                                        @else text-blue-500 @endif mt-0.5"
                                        data-icon="lucide:alert-triangle"></i>
                                    <span class="text-sm text-gray-600 dark:text-gray-300">{{ $anomaly['description'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="alert alert-success text-sm">
                            <i class="iconify-inline" data-icon="lucide:check-circle"></i>
                            <span>No significant anomalies detected in the data.</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn close-modal">Close</button>
            </div>
        </div>
        <div class="modal-backdrop"></div>
    </div>

    <!-- Data Quality Issues Modal -->
    <div id="data-issues-modal" class="modal">
        <div class="modal-box max-w-4xl">
            <h3 class="font-bold text-lg">Data Quality Issues</h3>
            <div class="mt-4">
                <div class="flex items-center gap-4 mb-4">
                    <div class="badge badge-error">{{ $dataAccuracyIssues['summary']['error'] }} Errors</div>
                    <div class="badge badge-warning">{{ $dataAccuracyIssues['summary']['warning'] }} Warnings</div>
                    <div class="badge badge-info">{{ $dataAccuracyIssues['summary']['info'] }} Notices</div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Indicator</th>
                                <th>Issue</th>
                                <th>Details</th>
                                <th>Severity</th>
                                <th>Recommendation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dataAccuracyIssues['all'] as $issue)
                                <tr>
                                    <td>
                                        <div class="font-medium">{{ $issue['indicator']['Indicator_Number'] }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-[150px]" title="{{ $issue['indicator']['Indicator_Name'] }}">
                                            {{ $issue['indicator']['Indicator_Name'] }}
                                        </div>
                                    </td>
                                    <td>{{ $issue['issue'] }}</td>
                                    <td class="whitespace-normal max-w-[200px]">{{ $issue['details'] }}</td>
                                    <td>
                                        <div class="badge
                                            @if($issue['severity'] == 'error') badge-error
                                            @elseif($issue['severity'] == 'warning') badge-warning
                                            @else badge-info @endif">
                                            {{ $issue['severity'] }}
                                        </div>
                                    </td>
                                    <td class="whitespace-normal max-w-[200px]">{{ $issue['recommendation'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn close-modal">Close</button>
            </div>
        </div>
        <div class="modal-backdrop"></div>
    </div>

    <!-- Export Modal -->
    <div id="export-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg">Export Report</h3>
            <form action="{{ route('indicator.export') }}" method="GET" class="mt-4">
                <input type="hidden" name="cluster_id" value="{{ $cluster->ClusterID }}">
                <input type="hidden" name="report_type" value="{{ $reportType }}">

                @if($reportType === 'specific')
                    <input type="hidden" name="timeline_id" value="{{ $timelines[0]->id ?? '' }}">
                @else
                    <input type="hidden" name="report_year" value="{{ $reportYear }}">
                    <input type="hidden" name="timeline_type" value="{{ $timelineType }}">
                @endif

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Export Format</span>
                    </label>
                    <select name="export_format" class="select select-bordered w-full">
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="pdf">PDF Document</option>
                    </select>
                </div>

                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Content to Include</span>
                    </label>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="include_summary" value="1" class="checkbox checkbox-sm" checked>
                            <span>Performance Summary</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="include_details" value="1" class="checkbox checkbox-sm" checked>
                            <span>Detailed Indicator Data</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="include_charts" value="1" class="checkbox checkbox-sm" checked>
                            <span>Charts & Visualizations</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="include_insights" value="1" class="checkbox checkbox-sm" checked>
                            <span>Insights & Recommendations</span>
                        </label>
                    </div>
                </div>

                <div class="modal-action">
                    <button type="button" class="btn btn-ghost close-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Export</button>
                </div>
            </form>
        </div>
        <div class="modal-backdrop"></div>
    </div>

   <!-- Keep the existing JavaScript section but replace it with this improved version -->

<!-- JavaScript -->
<!-- JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Global chart objects
    let charts = {
        performanceDistribution: null,
        distributionDetail: null,
        strategicObjectives: null,
        soDetail: null,
        strategicRadar: null,
        historicalTrend: null
    };

    // Add a debug function to help troubleshoot
    function debug(message, data) {
        console.log(`DEBUG: ${message}`, data);
    }

    document.addEventListener('DOMContentLoaded', function() {
        debug('DOM loaded, initializing...');

        // First, set up tab switching
        setupTabSwitching();

        // Set up other UI interactions
        setupToggleButtons();
        setupModals();
        setupIndicatorDetails();
        setupTableFilters();

        // Initialize charts last, with a slight delay to ensure DOM is ready
        setTimeout(function() {
            initializeCharts();
        }, 300);
    });

    function setupTabSwitching() {
        debug('Setting up tab switching');
        const tabs = document.querySelectorAll('.tab');
        const tabPanes = document.querySelectorAll('.tab-pane');

        if (!tabs.length || !tabPanes.length) {
            console.error('Tabs or tab panes not found');
            return;
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                debug('Tab clicked:', tabId);

                // Update active tab
                tabs.forEach(t => t.classList.remove('tab-active'));
                this.classList.add('tab-active');

                // Show corresponding tab content
                tabPanes.forEach(pane => {
                    pane.classList.add('hidden');
                    if (pane.id === `tab-${tabId}`) {
                        pane.classList.remove('hidden');
                        debug(`Showing tab pane: ${pane.id}`);

                        // Redraw charts in the visible tab
                        setTimeout(() => {
                            redrawChartsForTab(tabId);
                        }, 100);
                    }
                });
            });
        });
    }

    function redrawChartsForTab(tabId) {
        debug(`Redrawing charts for tab: ${tabId}`);

        try {
            if (tabId === 'overview') {
                if (charts.performanceDistribution) {
                    debug('Redrawing performance distribution chart');
                    charts.performanceDistribution.render();
                }
                if (charts.strategicObjectives) {
                    debug('Redrawing strategic objectives chart');
                    charts.strategicObjectives.render();
                }
            } else if (tabId === 'strategic') {
                if (charts.strategicRadar) {
                    debug('Redrawing strategic radar chart');
                    charts.strategicRadar.render();
                }
            } else if (tabId === 'insights') {
                if (charts.historicalTrend) {
                    debug('Redrawing historical trend chart');
                    charts.historicalTrend.render();
                }
            }
        } catch (error) {
            console.error('Error redrawing charts for tab:', error);
        }
    }

    function setupToggleButtons() {
        debug('Setting up toggle buttons');
        const toggleButtons = document.querySelectorAll('.toggle-indicators');

        toggleButtons.forEach(button => {
            button.addEventListener('click', function() {
                const soId = this.getAttribute('data-so');
                const table = document.getElementById(`indicators-${soId}`);
                const showText = this.querySelector('.show-text');
                const hideText = this.querySelector('.hide-text');

                if (!table || !showText || !hideText) {
                    console.error('Toggle elements not found');
                    return;
                }

                if (table.classList.contains('hidden')) {
                    table.classList.remove('hidden');
                    showText.classList.add('hidden');
                    hideText.classList.remove('hidden');
                } else {
                    table.classList.add('hidden');
                    showText.classList.remove('hidden');
                    hideText.classList.add('hidden');
                }
            });
        });
    }

    function setupModals() {
        debug('Setting up modals');
        const modalTriggers = {
            'filter-button': 'filter-modal',
            'view-distribution-button': 'distribution-modal',
            'view-so-details-button': 'so-details-modal',
            'view-all-insights-button': 'all-insights-modal',
            'view-all-issues-button': 'data-issues-modal',
            'export-button': 'export-modal'
        };

        Object.keys(modalTriggers).forEach(triggerId => {
            const trigger = document.getElementById(triggerId);
            const modalId = modalTriggers[triggerId];

            if (!trigger) {
                console.warn(`Modal trigger not found: ${triggerId}`);
                return;
            }

            trigger.addEventListener('click', function() {
                const modal = document.getElementById(modalId);

                if (!modal) {
                    console.error(`Modal not found: ${modalId}`);
                    return;
                }

                modal.classList.add('modal-open');
                debug(`Opened modal: ${modalId}`);

                // Redraw charts in modals
                setTimeout(() => {
                    try {
                        if (modalId === 'distribution-modal' && charts.distributionDetail) {
                            debug('Redrawing distribution detail chart in modal');
                            charts.distributionDetail.render();
                        } else if (modalId === 'so-details-modal' && charts.soDetail) {
                            debug('Redrawing SO detail chart in modal');
                            charts.soDetail.render();
                        }
                    } catch (error) {
                        console.error('Error redrawing chart in modal:', error);
                    }
                }, 100);
            });
        });

        // Close modals
        document.querySelectorAll('.close-modal').forEach(button => {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    modal.classList.remove('modal-open');
                }
            });
        });

        // Close modals when clicking on backdrop
        document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
            backdrop.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    modal.classList.remove('modal-open');
                }
            });
        });
    }

    function setupIndicatorDetails() {
        debug('Setting up indicator details');
        document.querySelectorAll('.view-indicator-details').forEach(button => {
            button.addEventListener('click', function() {
                const indicatorId = this.getAttribute('data-indicator-id');
                const modal = document.getElementById('indicator-details-modal');
                const contentDiv = document.getElementById('indicator-details-content');
                const titleElement = document.getElementById('indicator-details-title');

                if (!modal || !contentDiv || !titleElement) {
                    console.error('Indicator details modal elements not found');
                    return;
                }

                debug('Loading indicator details for ID:', indicatorId);

                // Find the indicator data from the server-rendered data
                let indicatorData = null;

                // This section will be populated with PHP-generated JavaScript
                // containing the indicator data for each result
                @foreach($performanceResults as $index => $result)
                    if ('{{ $result['indicator']['id'] }}' === indicatorId) {
                        indicatorData = {
                            index: {{ $index }},
                            indicator: {
                                id: '{{ $result['indicator']['id'] }}',
                                number: '{{ $result['indicator']['Indicator_Number'] }}',
                                name: '{{ addslashes($result['indicator']['Indicator_Name']) }}',
                                responseType: '{{ $result['indicator']['ResponseType'] }}',
                                soId: '{{ $result['indicator']['SO_ID'] }}'
                            },
                            score: {{ $result['score'] ?? 'null' }},
                            percentageAchieved: {{ $result['percentageAchieved'] ?? 'null' }},
                            performanceCategory: '{{ $result['performanceCategory'] }}',
                            analysisComment: '{{ isset($result['analysisComment']) ? addslashes($result['analysisComment']) : '' }}',
                            target: {
                                value: '{{ isset($result['target']['Target_Value']) ? addslashes($result['target']['Target_Value']) : '' }}',
                                responseType: '{{ $result['target']['ResponseType'] ?? '' }}',
                                year: '{{ $result['target']['Target_Year'] ?? '' }}'
                            },
                            actual: {
                                value: '{{ isset($result['actual']['Response']) ? addslashes($result['actual']['Response']) : '' }}',
                                comment: '{{ isset($result['actual']['ReportingComment']) ? addslashes($result['actual']['ReportingComment']) : '' }}'
                            }
                        };
                    }
                @endforeach

                if (indicatorData) {
                    debug('Found indicator data:', indicatorData);

                    // Update modal title
                    titleElement.textContent = `${indicatorData.indicator.number}: ${indicatorData.indicator.name}`;

                    // Build content HTML
                    let html = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Performance Details</h4>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Performance Score</div>
                                        <div class="text-xl font-bold
                                            ${indicatorData.score >= 90 ? 'text-green-500' :
                                            indicatorData.score >= 50 ? 'text-blue-500' :
                                            indicatorData.score >= 10 ? 'text-orange-500' : 'text-red-500'}">
                                            ${indicatorData.score !== null ? indicatorData.score.toFixed(1) + '%' : 'N/A'}
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Category</div>
                                        <div class="badge
                                            ${indicatorData.performanceCategory === 'Met' ? 'badge-success' :
                                            indicatorData.performanceCategory === 'On Track' ? 'badge-info' :
                                            indicatorData.performanceCategory === 'In Progress' ? 'badge-warning' :
                                            indicatorData.performanceCategory === 'Not Performing' ? 'badge-error' : 'badge-ghost'}">
                                            ${indicatorData.performanceCategory}
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Target</div>
                                        <div class="font-medium">${indicatorData.target.value || 'Not set'}</div>
                                    </div>
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Actual</div>
                                        <div class="font-medium">${indicatorData.actual.value || 'No data'}</div>
                                    </div>
                                    ${indicatorData.analysisComment ? `
                                    <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Analysis</div>
                                        <div class="text-sm mt-1">${indicatorData.analysisComment}</div>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Additional Information</h4>
                                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                                    <div class="mb-3">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Strategic Objective</div>
                                        <div class="font-medium">${indicatorData.indicator.soId}</div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Response Type</div>
                                        <div class="font-medium">${indicatorData.indicator.responseType}</div>
                                    </div>
                                    ${indicatorData.actual.comment ? `
                                    <div class="mb-3">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Reporting Comment</div>
                                        <div class="text-sm">${indicatorData.actual.comment}</div>
                                    </div>
                                    ` : ''}
                                    ${indicatorData.target.year ? `
                                    <div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Target Year</div>
                                        <div class="font-medium">${indicatorData.target.year}</div>
                                    </div>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `;

                    contentDiv.innerHTML = html;
                    modal.classList.add('modal-open');
                } else {
                    console.error('Indicator data not found for ID:', indicatorId);
                }
            });
        });
    }

    function setupTableFilters() {
        debug('Setting up table filters');
        const indicatorSearch = document.getElementById('indicator-search');
        const categoryFilter = document.getElementById('category-filter');
        const indicatorsTable = document.getElementById('indicators-table');

        if (!indicatorSearch || !categoryFilter || !indicatorsTable) {
            console.warn('Table filter elements not found');
            return;
        }

        const rows = indicatorsTable.querySelectorAll('tbody tr');

        function filterTable() {
            const searchTerm = indicatorSearch.value.toLowerCase();
            const category = categoryFilter.value;

            debug(`Filtering table - search: "${searchTerm}", category: "${category}"`);

            rows.forEach(row => {
                const rowCategory = row.getAttribute('data-category');
                const text = row.textContent.toLowerCase();
                const categoryMatch = !category || rowCategory === category;
                const searchMatch = !searchTerm || text.includes(searchTerm);

                if (categoryMatch && searchMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        indicatorSearch.addEventListener('input', filterTable);
        categoryFilter.addEventListener('change', filterTable);
    }

    function initializeCharts() {
        debug('Initializing charts');

        try {
            // Make sure chart containers have explicit dimensions
            ensureChartContainerDimensions();

            // Initialize each chart
            initPerformanceDistributionChart();
            initDistributionDetailChart();
            initStrategicObjectivesChart();
            initSODetailChart();
            initStrategicRadarChart();
            initHistoricalTrendChart();
        } catch (error) {
            console.error('Error initializing charts:', error);
        }
    }

    function ensureChartContainerDimensions() {
        // Make sure chart containers have explicit dimensions
        const chartContainers = [
            "#performance-distribution-chart",
            "#distribution-detail-chart",
            "#strategic-objectives-chart",
            "#so-detail-chart",
            "#strategic-radar-chart",
            "#historical-trend-chart"
        ];

        chartContainers.forEach(selector => {
            const container = document.querySelector(selector);
            if (container) {
                // Set minimum height if not already set
                if (!container.style.minHeight) {
                    container.style.minHeight = '250px';
                }

                // Add a border for debugging
                container.style.border = '1px dashed #ccc';
            }
        });
    }

    function initPerformanceDistributionChart() {
        const element = document.querySelector("#performance-distribution-chart");
        if (!element) {
            console.warn('Performance distribution chart element not found');
            return;
        }

        debug('Initializing performance distribution chart');

        // Prepare data
        const series = [
            @foreach(['Met', 'On Track', 'In Progress', 'Not Performing', 'No Data', 'No Target'] as $category)
                {{ $categoryCounts[$category] ?? 0 }},
            @endforeach
        ];

        // Check if all values are zero
        const hasData = series.some(value => value > 0);
        if (!hasData) {
            debug('No data for performance distribution chart');
            element.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-500">No data available</p></div>';
            return;
        }

        const labels = [
            @foreach(['Met', 'On Track', 'In Progress', 'Not Performing', 'No Data', 'No Target'] as $category)
                '{{ $category }}',
            @endforeach
        ];

        debug('Chart data:', { series, labels });

        try {
            // Create chart
            charts.performanceDistribution = new ApexCharts(element, {
                chart: {
                    type: 'donut',
                    height: 250,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: {
                        enabled: false // Disable animations for more reliable rendering
                    }
                },
                series: series,
                labels: labels,
                colors: [
                    '#4CAF50', // Met - Green
                    '#2196F3', // On Track - Blue
                    '#FF9800', // In Progress - Orange
                    '#F44336', // Not Performing - Red
                    '#9E9E9E', // No Data - Gray
                    '#607D8B'  // No Target - Blue Gray
                ],
                legend: { show: false },
                dataLabels: { enabled: false },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                name: { show: true },
                                value: {
                                    show: true,
                                    formatter: function(val) { return val; }
                                },
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function(w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    }
                                }
                            }
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: { chart: { height: 200 } }
                }]
            });

            charts.performanceDistribution.render();
        } catch (error) {
            console.error('Error creating performance distribution chart:', error);
            element.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-red-500">Error rendering chart</p></div>';
        }
    }

    function initDistributionDetailChart() {
        const element = document.querySelector("#distribution-detail-chart");
        if (!element) {
            console.warn('Distribution detail chart element not found');
            return;
        }

        debug('Initializing distribution detail chart');

        try {
            charts.distributionDetail = new ApexCharts(element, {
                chart: {
                    type: 'donut',
                    height: 250,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: {
                        enabled: false
                    }
                },
                series: [
                    @foreach(['Met', 'On Track', 'In Progress', 'Not Performing', 'No Data', 'No Target'] as $category)
                        {{ $categoryCounts[$category] ?? 0 }},
                    @endforeach
                ],
                labels: [
                    @foreach(['Met', 'On Track', 'In Progress', 'Not Performing', 'No Data', 'No Target'] as $category)
                        '{{ $category }}',
                    @endforeach
                ],
                colors: [
                    '#4CAF50', '#2196F3', '#FF9800', '#F44336', '#9E9E9E', '#607D8B'
                ],
                legend: { position: 'bottom' },
                dataLabels: {
                    enabled: true,
                    formatter: function(val, opts) {
                        return opts.w.globals.seriesTotals[opts.seriesIndex];
                    }
                },
                plotOptions: {
                    pie: { donut: { size: '60%' } }
                }
            });

            charts.distributionDetail.render();
        } catch (error) {
            console.error('Error creating distribution detail chart:', error);
            element.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-red-500">Error rendering chart</p></div>';
        }
    }

    function initStrategicObjectivesChart() {
        const element = document.querySelector("#strategic-objectives-chart");
        if (!element) {
            console.warn('Strategic objectives chart element not found');
            return;
        }

        debug('Initializing strategic objectives chart');

        // Prepare data
        const strategicObjectivesData = [];
        const strategicObjectivesLabels = [];
        const strategicObjectivesColors = [];

        @foreach($resultsBySO as $soId => $soData)
            @php
                $so = $soData['so'];
                $indicators = $soData['indicators'];

                // Calculate SO average score
                $soTotalScore = 0;
                $soIndicatorsWithData = 0;

                foreach($indicators as $indicator) {
                    if ($indicator['score'] !== null) {
                        $soTotalScore += $indicator['score'];
                        $soIndicatorsWithData++;
                    }
                }

                $soAverageScore = $soIndicatorsWithData > 0 ? $soTotalScore / $soIndicatorsWithData : 0;
            @endphp

            strategicObjectivesData.push({{ number_format($soAverageScore, 1) }});
            strategicObjectivesLabels.push('{{ $so->SO_Number }}');

            @if($soAverageScore >= 90)
                strategicObjectivesColors.push('#4CAF50');
            @elseif($soAverageScore >= 50)
                strategicObjectivesColors.push('#2196F3');
            @elseif($soAverageScore >= 10)
                strategicObjectivesColors.push('#FF9800');
            @else
                strategicObjectivesColors.push('#F44336');
            @endif
        @endforeach

        debug('Strategic objectives data:', {
            data: strategicObjectivesData,
            labels: strategicObjectivesLabels
        });

        try {
            charts.strategicObjectives = new ApexCharts(element, {
                chart: {
                    type: 'bar',
                    height: 250,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: {
                        enabled: false
                    }
                },
                series: [{
                    name: 'Performance Score',
                    data: strategicObjectivesData
                }],
                xaxis: { categories: strategicObjectivesLabels },
                colors: strategicObjectivesColors,
                plotOptions: {
                    bar: {
                        distributed: true,
                        borderRadius: 5,
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) { return val + '%'; },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                grid: { borderColor: '#e0e0e0' },
                yaxis: {
                    max: 100,
                    title: { text: 'Performance Score (%)' }
                },
                tooltip: {
                    y: { formatter: function(val) { return val + '%'; } }
                }
            });

            charts.strategicObjectives.render();
        } catch (error) {
            console.error('Error creating strategic objectives chart:', error);
            element.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-red-500">Error rendering chart</p></div>';
        }
    }

    function initSODetailChart() {
        const element = document.querySelector("#so-detail-chart");
        if (!element) {
            console.warn('SO detail chart element not found');
            return;
        }

        debug('Initializing SO detail chart');

        // Reuse data from strategic objectives chart
        const strategicObjectivesData = [];
        const strategicObjectivesLabels = [];
        const strategicObjectivesColors = [];

        @foreach($resultsBySO as $soId => $soData)
            @php
                $so = $soData['so'];
                $indicators = $soData['indicators'];

                // Calculate SO average score
                $soTotalScore = 0;
                $soIndicatorsWithData = 0;

                foreach($indicators as $indicator) {
                    if ($indicator['score'] !== null) {
                        $soTotalScore += $indicator['score'];
                        $soIndicatorsWithData++;
                    }
                }

                $soAverageScore = $soIndicatorsWithData > 0 ? $soTotalScore / $soIndicatorsWithData : 0;
            @endphp

            strategicObjectivesData.push({{ number_format($soAverageScore, 1) }});
            strategicObjectivesLabels.push('{{ $so->SO_Number }}');

            @if($soAverageScore >= 90)
                strategicObjectivesColors.push('#4CAF50');
            @elseif($soAverageScore >= 50)
                strategicObjectivesColors.push('#2196F3');
            @elseif($soAverageScore >= 10)
                strategicObjectivesColors.push('#FF9800');
            @else
                strategicObjectivesColors.push('#F44336');
            @endif
        @endforeach

        try {
            charts.soDetail = new ApexCharts(element, {
                chart: {
                    type: 'bar',
                    height: 350,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: {
                        enabled: false
                    }
                },
                series: [{
                    name: 'Performance Score',
                    data: strategicObjectivesData
                }],
                xaxis: { categories: strategicObjectivesLabels },
                colors: strategicObjectivesColors,
                plotOptions: {
                    bar: {
                        distributed: true,
                        borderRadius: 5,
                        dataLabels: { position: 'top' }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) { return val + '%'; },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                grid: { borderColor: '#e0e0e0' },
                yaxis: {
                    max: 100,
                    title: { text: 'Performance Score (%)' }
                },
                tooltip: {
                    y: { formatter: function(val) { return val + '%'; } }
                }
            });

            charts.soDetail.render();
        } catch (error) {
            console.error('Error creating SO detail chart:', error);
            element.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-red-500">Error rendering chart</p></div>';
        }
    }

    function initStrategicRadarChart() {
        const element = document.querySelector("#strategic-radar-chart");
        if (!element) {
            console.warn('Strategic radar chart element not found');
            return;
        }

        debug('Initializing strategic radar chart');

        // Reuse data from strategic objectives chart
        const strategicObjectivesData = [];
        const strategicObjectivesLabels = [];

        @foreach($resultsBySO as $soId => $soData)
            @php
                $so = $soData['so'];
                $indicators = $soData['indicators'];

                // Calculate SO average score
                $soTotalScore = 0;
                $soIndicatorsWithData = 0;

                foreach($indicators as $indicator) {
                    if ($indicator['score'] !== null) {
                        $soTotalScore += $indicator['score'];
                        $soIndicatorsWithData++;
                    }
                }

                $soAverageScore = $soIndicatorsWithData > 0 ? $soTotalScore / $soIndicatorsWithData : 0;
            @endphp

            strategicObjectivesData.push({{ number_format($soAverageScore, 1) }});
            strategicObjectivesLabels.push('{{ $so->SO_Number }}');
        @endforeach

        try {
            charts.strategicRadar = new ApexCharts(element, {
                chart: {
                    type: 'radar',
                    height: 350,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: {
                        enabled: false
                    }
                },
                series: [{
                    name: 'Performance Score',
                    data: strategicObjectivesData
                }],
                labels: strategicObjectivesLabels,
                plotOptions: {
                    radar: {
                        size: 140,
                        polygons: {
                            strokeColors: '#e9e9e9',
                            fill: {
                                colors: ['#f8f8f8', '#fff']
                            }
                        }
                    }
                },
                colors: ['#FF4560'],
                markers: {
                    size: 5,
                    colors: ['#FF4560'],
                    strokeWidth: 2
                },
                tooltip: {
                    y: { formatter: function(val) { return val + '%'; } }
                },
                yaxis: {
                    max: 100,
                    tickAmount: 5
                }
            });

            charts.strategicRadar.render();
        } catch (error) {
            console.error('Error creating strategic radar chart:', error);
            element.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-red-500">Error rendering chart</p></div>';
        }
    }

    function initHistoricalTrendChart() {
        const element = document.querySelector("#historical-trend-chart");
        if (!element) {
            console.warn('Historical trend chart element not found');
            return;
        }

        debug('Initializing historical trend chart');

        @php
            $years = array_keys($historicalData['byYear'] ?? []);
            $yearData = [];

            foreach ($years as $year) {
                $totalScore = 0;
                $count = 0;

                foreach ($historicalData['byYear'][$year]['indicators'] ?? [] as $indicatorId => $data) {
                    $totalScore += $data['value'] ?? 0;
                    $count++;
                }

                $yearData[$year] = $count > 0 ? $totalScore / $count : 0;
            }
        @endphp

        const historicalYears = [@foreach($years as $year) '{{ $year }}', @endforeach];
        const historicalData = [@foreach($years as $year) {{ number_format($yearData[$year] ?? 0, 1) }}, @endforeach];

        debug('Historical data:', { years: historicalYears, data: historicalData });

        if (historicalYears.length > 0) {
            try {
                charts.historicalTrend = new ApexCharts(element, {
                    chart: {
                        type: 'line',
                        height: 200,
                        fontFamily: 'inherit',
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        animations: {
                            enabled: false
                        }
                    },
                    series: [{
                        name: 'Average Performance',
                        data: historicalData
                    }],
                    xaxis: { categories: historicalYears },
                    colors: ['#6366F1'],
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    markers: {
                        size: 5
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return val + '%';
                            }
                        }
                    },
                    grid: {
                        borderColor: '#e0e0e0'
                    }
                });

                charts.historicalTrend.render();
            } catch (error) {
                console.error('Error creating historical trend chart:', error);
                element.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-red-500">Error rendering chart</p></div>';
            }
        } else {
            element.innerHTML = '<div class="flex items-center justify-center h-full"><p class="text-gray-500">No historical data available</p></div>';
        }
    }

    // Log data for debugging
    console.log('Performance Categories:', {
    @foreach(['Met', 'On Track', 'In Progress', 'Not Performing', 'No Data', 'No Target'] as $category)
        '{{ $category }}': {{ $categoryCounts[$category] ?? 0 }}@if(!$loop->last),@endif
    @endforeach
});

    console.log('Strategic Objectives:', @json($resultsBySO));
    console.log('Historical Data:', @json($historicalData));
</script>
