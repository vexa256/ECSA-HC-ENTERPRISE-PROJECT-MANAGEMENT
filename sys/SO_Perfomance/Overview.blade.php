<div class="min-h-screen bg-neutral-100 p-4 md:p-8">
    <div class="container mx-auto">
        <!-- Page Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8">
            <div class="text-center md:text-left mb-4 md:mb-0">
                <h1 class="text-2xl font-semibold text-neutral-800">Strategic Objectives</h1>
                <p class="text-neutral-500 mt-1">{{ $report->ReportName }} - {{ $selectedYear }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                <a href="{{ route('Ecsa_SO_selectReport', ['year' => $selectedYear]) }}"
                    class="btn btn-outline border-orange-500 text-orange-500 hover:bg-orange-500 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    Back
                </a>
                <a href="{{ route('Ecsa_SO_exportCsv', ['year' => $selectedYear, 'report' => $selectedReport]) }}"
                    class="btn bg-orange-500 text-white hover:bg-orange-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    Export
                </a>
            </div>
        </div>

        <!-- Objectives Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($performanceData as $objectiveId => $objective)
                <div class="bg-white rounded-2xl shadow-md p-6">
                    <h2 class="text-lg font-semibold text-neutral-800 mb-2">{{ $objective['name'] }}</h2>
                    <p class="text-neutral-600 text-sm mb-4">{{ $objective['description'] }}</p>
                    <div class="mb-4">
                        <div class="flex items-center mb-2">
                            <span
                                class="text-sm font-medium {{ $objective['allTargetsMet'] ? 'text-green-500' : ($objective['fullyReported'] ? 'text-orange-500' : 'text-red-500') }}">
                                {{ $objective['allTargetsMet'] ? 'All Targets Met' : ($objective['fullyReported'] ? 'In Progress' : 'Incomplete') }}
                            </span>
                        </div>
                        <div class="w-full bg-neutral-200 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $objective['allTargetsMet'] ? 'bg-green-500' : ($objective['fullyReported'] ? 'bg-orange-500' : 'bg-red-500') }}"
                                style="width: {{ $objective['allTargetsMet'] ? '100' : ($objective['fullyReported'] ? '50' : '25') }}%">
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-neutral-500 text-sm">{{ count($objective['indicators']) }} Indicators</span>
                        <label for="modal-objective-{{ $objectiveId }}"
                            class="btn btn-sm bg-orange-500 text-white hover:bg-orange-600">View Details</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

@foreach ($performanceData as $objectiveId => $objective)
    <input type="checkbox" id="modal-objective-{{ $objectiveId }}" class="modal-toggle" />
    <div class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-neutral-100 w-full h-full max-w-none rounded-none p-0">
            <div class="sticky top-0 bg-neutral-100 z-10 px-4 py-3 border-b border-neutral-200">
                <h3 class="font-semibold text-xl text-neutral-800">{{ $objective['name'] }} Details</h3>
            </div>
            <div class="p-4 overflow-y-auto" style="height: calc(100vh - 120px);">
                <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead>
                                <tr class="bg-neutral-50">
                                    <th class="text-left text-neutral-600 py-3 px-4">Indicator</th>
                                    <th class="text-left text-neutral-600 py-3 px-4">Baseline</th>
                                    <th class="text-left text-neutral-600 py-3 px-4">Target</th>
                                    <th class="text-left text-neutral-600 py-3 px-4">Score</th>
                                    <th class="text-left text-neutral-600 py-3 px-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($objective['indicators'] as $indicator)
                                    <tr class="border-t border-neutral-200">
                                        <td class="py-3 px-4">{{ $indicator['name'] }}</td>
                                        <td class="py-3 px-4">{{ $indicator['baseline'] ?? 'N/A' }}</td>
                                        <td class="py-3 px-4">{{ $indicator['target'] ?? 'N/A' }}</td>
                                        <td class="py-3 px-4">{{ $indicator['score'] ?? 'N/A' }}</td>
                                        <td class="py-3 px-4">
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full {{ $indicator['status'] === 'met' ? 'bg-green-100 text-green-800' : ($indicator['status'] === 'progressing' ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($indicator['status']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if (!empty($objective['missingReports']))
                    <div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
                        <h4 class="text-lg font-semibold mb-4 text-neutral-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-orange-500" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            Missing Reports
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($objective['missingReports'] as $missingReport)
                                <div class="bg-neutral-50 rounded-xl p-4">
                                    <h5 class="font-medium text-neutral-800 mb-2">{{ $missingReport['indicator'] }}
                                    </h5>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($missingReport['missingClusters'] as $cluster)
                                            <span
                                                class="px-2 py-1 text-xs font-medium bg-neutral-200 text-neutral-600 rounded-full">{{ $cluster }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            <div class="sticky bottom-0 bg-neutral-100 border-t border-neutral-200 p-4 flex justify-end">
                <label for="modal-objective-{{ $objectiveId }}"
                    class="btn bg-orange-500 text-white hover:bg-orange-600">Close</label>
            </div>
        </div>
    </div>
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            var modalBox = modal.querySelector('.modal-box');
            var contentArea = modalBox.querySelector('.overflow-y-auto');
            var footer = modalBox.querySelector('.sticky.bottom-0');

            function adjustModalHeight() {
                var viewportHeight = window.innerHeight;
                var footerHeight = footer.offsetHeight;
                var headerHeight = modalBox.querySelector('.sticky.top-0').offsetHeight;

                contentArea.style.height = `${viewportHeight - footerHeight - headerHeight}px`;
            }

            // Adjust height when modal is opened
            modal.addEventListener('change', function(event) {
                if (event.target.checked) {
                    setTimeout(adjustModalHeight, 0);
                }
            });

            // Adjust height on window resize
            window.addEventListener('resize', adjustModalHeight);
        });
    });
</script>
