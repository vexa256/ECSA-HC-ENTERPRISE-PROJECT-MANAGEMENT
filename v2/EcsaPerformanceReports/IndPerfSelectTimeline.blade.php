<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <!-- Header Section -->
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-medium text-gray-800 dark:text-white">Select Reporting Timeline</h2>
            <a href="{{ route('indicator.select.cluster') }}" class="btn btn-ghost btn-sm">
                <i class="iconify-inline" data-icon="lucide:arrow-left"></i>
                Back
            </a>
        </div>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Cluster: <span class="font-medium text-primary">{{ $cluster->Cluster_Name }}</span>
        </p>
    </div>

    <!-- Error Alert -->
    @if(isset($error) || session('error'))
    <div class="mx-6 mt-4 alert alert-error shadow-sm text-sm">
        <i class="iconify-inline" data-icon="lucide:alert-circle"></i>
        <span>{{ $error ?? session('error') }}</span>
    </div>
    @endif

    <form action="{{ route('indicator.report') }}" method="GET" id="timeline-form">
        <input type="hidden" name="cluster_id" value="{{ $clusterId }}">
        <input type="hidden" name="report_type" id="report-type-input" value="specific">

        <!-- iOS-style Segmented Control -->
        <div class="px-6 pt-5">
            <div class="flex bg-gray-100 dark:bg-gray-700 p-1 rounded-lg w-full md:w-auto">
                <button type="button" id="tab-specific" class="flex-1 md:flex-none md:px-6 py-2 rounded-md bg-white dark:bg-gray-600 shadow-sm text-sm font-medium text-gray-800 dark:text-white flex items-center justify-center gap-1.5 transition-all">
                    <i class="iconify-inline" data-icon="lucide:calendar"></i>
                    Specific Timeline
                </button>
                <button type="button" id="tab-annual" class="flex-1 md:flex-none md:px-6 py-2 rounded-md text-sm font-medium text-gray-600 dark:text-gray-300 flex items-center justify-center gap-1.5 transition-all">
                    <i class="iconify-inline" data-icon="lucide:bar-chart-2"></i>
                    Annual Report
                </button>
            </div>

            <!-- Explanation of current selection -->
            <div class="mt-3 text-sm text-gray-500 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg flex items-start">
                <i class="iconify-inline text-blue-500 dark:text-blue-400 mt-0.5 mr-2" data-icon="lucide:info"></i>
                <div id="selection-explanation">
                    <p><span class="font-medium text-blue-700 dark:text-blue-300">Specific Timeline:</span> Generate a report for a single reporting period. This shows performance data for exactly one timeline.</p>
                </div>
            </div>
        </div>

        <!-- Specific Timeline Selection -->
        <div id="specific-timeline-content" class="px-6 py-4">
            @if(count($timelines ?? []) > 0)
                @if(count($timelineTypes ?? []) > 1)
                <div class="mb-4">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 block">Filter by type:</label>
                    <select class="select select-bordered w-full max-w-xs text-base" id="timeline-type-filter">
                        <option value="">All Timeline Types</option>
                        @foreach($timelineTypes ?? [] as $type)
                            <option value="{{ $type->Type }}">{{ $type->Type }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Filter the list below to show only specific types of timelines</p>
                </div>
                @endif

                <div id="timelines-container" class="space-y-4 max-h-[400px] overflow-y-auto pr-2 pb-2">
                    @foreach($organizedTimelines ?? [] as $type => $yearTimelines)
                        <div class="timeline-type-group" data-type="{{ $type }}">
                            <div class="font-medium text-sm text-gray-600 dark:text-gray-300 mb-2 flex items-center">
                                <i class="iconify-inline mr-1.5" data-icon="lucide:layers"></i>
                                {{ $type }}
                            </div>

                            @foreach($yearTimelines as $year => $timelines)
                                <div class="ml-2 mb-4">
                                    <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5 flex items-center">
                                        <i class="iconify-inline mr-1" data-icon="lucide:calendar"></i>
                                        {{ $year }}
                                    </div>

                                    @foreach($timelines as $timeline)
                                        <label for="timeline_{{ $timeline->id }}" class="flex items-center p-3 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer mb-2.5">
                                            <input type="radio" name="timeline_id" id="timeline_{{ $timeline->id }}"
                                                value="{{ $timeline->id }}" class="radio radio-sm radio-primary">
                                            <div class="ml-3 flex-1">
                                                <div class="font-medium text-gray-900 dark:text-white">{{ $timeline->ReportName }}</div>
                                                <div class="flex flex-wrap items-center text-xs text-gray-500 dark:text-gray-400 mt-0.5 gap-2">
                                                    <span class="flex items-center">
                                                        <i class="iconify-inline mr-1" data-icon="lucide:calendar"></i>
                                                        {{ $timeline->Year }}
                                                    </span>
                                                    <span class="flex items-center">
                                                        <i class="iconify-inline mr-1" data-icon="lucide:clock"></i>
                                                        @if($timeline->status == 'Completed')
                                                            <span class="text-green-600 dark:text-green-400">Completed</span>
                                                        @elseif($timeline->status == 'In Progress')
                                                            <span class="text-amber-600 dark:text-amber-400">In Progress</span>
                                                        @else
                                                            <span>{{ $timeline->status }}</span>
                                                        @endif
                                                    </span>
                                                </div>
                                                @if($timeline->Description)
                                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ $timeline->Description }}</div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
                <div id="empty-filter-message" class="hidden alert alert-info text-sm mt-3">
                    <i class="iconify-inline" data-icon="lucide:info"></i>
                    <span>No timelines found for the selected type</span>
                </div>
            @else
                <div class="alert alert-warning text-sm">
                    <i class="iconify-inline" data-icon="lucide:alert-triangle"></i>
                    <span>No timelines are available for selection.</span>
                </div>
            @endif
        </div>

        <!-- Annual Report Selection -->
        <div id="annual-report-content" class="px-6 py-4 hidden">
            <div class="grid grid-cols-1 gap-5">
                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 block">Select Year:</label>
                    <select name="report_year" class="select select-bordered w-full text-base" id="annual-year-select">
                        <option value="">Select a year</option>
                        @foreach($years ?? [] as $year)
                            <option value="{{ $year->Year }}">{{ $year->Year }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Choose which year's data to aggregate</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5 block">Aggregation Method:</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <label class="flex items-start p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer">
                            <input type="radio" name="timeline_type" value="" class="radio radio-sm radio-primary mt-0.5" checked>
                            <div class="ml-3">
                                <div class="font-medium text-gray-900 dark:text-white">All Report Types</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Aggregate data from all reporting periods in the selected year
                                </div>
                            </div>
                        </label>

                        @foreach($timelineTypes ?? [] as $type)
                        <label class="flex items-start p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer">
                            <input type="radio" name="timeline_type" value="{{ $type->Type }}" class="radio radio-sm radio-primary mt-0.5">
                            <div class="ml-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $type->Type }} Only</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Aggregate data from {{ strtolower($type->Type) }} only
                                </div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-5 bg-amber-50 dark:bg-amber-900/20 p-3 rounded-lg text-sm">
                <div class="flex items-start">
                    <i class="iconify-inline text-amber-500 dark:text-amber-400 mt-0.5 mr-2" data-icon="lucide:lightbulb"></i>
                    <div>
                        <p class="font-medium text-amber-800 dark:text-amber-300">How Annual Reports Work</p>
                        <p class="mt-1 text-amber-700 dark:text-amber-200 text-xs">
                            Annual reports aggregate data across multiple reporting periods. The system handles different indicator types appropriately:
                        </p>
                        <ul class="mt-1 text-amber-700 dark:text-amber-200 text-xs list-disc list-inside space-y-1">
                            <li>Numeric indicators: Values are summed or averaged based on the reporting type</li>
                            <li>Yes/No indicators: The most recent value is used</li>
                            <li>Text indicators: The most recent value is used</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700 flex justify-end">
            <button type="submit" id="submit-button" class="btn btn-primary btn-sm px-4 gap-2">
                Generate Report
                <i class="iconify-inline" data-icon="lucide:chart"></i>
            </button>
        </div>
    </form>

    <!-- Debug Information (Development Only) -->
    @if(isset($errorDetails) && app()->environment('local', 'development'))
    <div class="m-6 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg text-xs text-red-800 dark:text-red-300">
        <p class="font-medium">Error Details (Debug Only):</p>
        <pre class="mt-1 whitespace-pre-wrap">{{ $errorDetails }}</pre>
    </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const tabSpecific = document.getElementById('tab-specific');
        const tabAnnual = document.getElementById('tab-annual');
        const reportTypeInput = document.getElementById('report-type-input');
        const specificContent = document.getElementById('specific-timeline-content');
        const annualContent = document.getElementById('annual-report-content');
        const selectionExplanation = document.getElementById('selection-explanation');
        const annualYearSelect = document.getElementById('annual-year-select');
        const timelineForm = document.getElementById('timeline-form');
        const submitButton = document.getElementById('submit-button');

        // Tab switching - Specific Timeline
        tabSpecific.addEventListener('click', function() {
            // Update UI
            tabSpecific.classList.add('bg-white', 'dark:bg-gray-600', 'shadow-sm', 'text-gray-800', 'dark:text-white');
            tabSpecific.classList.remove('text-gray-600', 'dark:text-gray-300');

            tabAnnual.classList.remove('bg-white', 'dark:bg-gray-600', 'shadow-sm', 'text-gray-800', 'dark:text-white');
            tabAnnual.classList.add('text-gray-600', 'dark:text-gray-300');

            // Show/hide content
            specificContent.classList.remove('hidden');
            annualContent.classList.add('hidden');

            // Update form
            reportTypeInput.value = 'specific';

            // Update explanation
            selectionExplanation.innerHTML = `
                <p><span class="font-medium text-blue-700 dark:text-blue-300">Specific Timeline:</span> Generate a report for a single reporting period. This shows performance data for exactly one timeline.</p>
            `;

            // Update validation
            document.querySelectorAll('input[name="timeline_id"]').forEach(input => {
                input.setAttribute('required', '');
            });
            annualYearSelect.removeAttribute('required');
        });

        // Tab switching - Annual Report
        tabAnnual.addEventListener('click', function() {
            // Update UI
            tabAnnual.classList.add('bg-white', 'dark:bg-gray-600', 'shadow-sm', 'text-gray-800', 'dark:text-white');
            tabAnnual.classList.remove('text-gray-600', 'dark:text-gray-300');

            tabSpecific.classList.remove('bg-white', 'dark:bg-gray-600', 'shadow-sm', 'text-gray-800', 'dark:text-white');
            tabSpecific.classList.add('text-gray-600', 'dark:text-gray-300');

            // Show/hide content
            specificContent.classList.add('hidden');
            annualContent.classList.remove('hidden');

            // Update form
            reportTypeInput.value = 'annual';

            // Update explanation
            selectionExplanation.innerHTML = `
                <p><span class="font-medium text-blue-700 dark:text-blue-300">Annual Report:</span> Aggregate data across multiple reporting periods for a comprehensive view of yearly performance.</p>
            `;

            // Update validation
            document.querySelectorAll('input[name="timeline_id"]').forEach(input => {
                input.removeAttribute('required');
            });
            annualYearSelect.setAttribute('required', '');
        });

        // Timeline type filter functionality
        const typeFilter = document.getElementById('timeline-type-filter');
        if (typeFilter) {
            const typeGroups = document.querySelectorAll('.timeline-type-group');
            const emptyMessage = document.getElementById('empty-filter-message');

            typeFilter.addEventListener('change', function() {
                const selectedType = this.value;
                let visibleGroups = 0;

                typeGroups.forEach(group => {
                    if (selectedType === '' || group.dataset.type === selectedType) {
                        group.style.display = 'block';
                        visibleGroups++;
                    } else {
                        group.style.display = 'none';
                    }
                });

                // Show/hide empty message
                if (visibleGroups === 0 && selectedType !== '') {
                    emptyMessage.classList.remove('hidden');
                    emptyMessage.querySelector('span').textContent = `No timelines found for type "${selectedType}"`;
                } else {
                    emptyMessage.classList.add('hidden');
                }
            });
        }

        // Form validation
        timelineForm.addEventListener('submit', function(e) {
            const reportType = reportTypeInput.value;

            if (reportType === 'annual') {
                if (!annualYearSelect.value) {
                    e.preventDefault();

                    // Show error using DaisyUI toast or alert
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-error text-sm mt-4';
                    errorDiv.innerHTML = `
                        <i class="iconify-inline" data-icon="lucide:alert-circle"></i>
                        <span>Please select a year for the annual report</span>
                    `;

                    // Insert before the submit button
                    annualContent.appendChild(errorDiv);

                    // Remove after 3 seconds
                    setTimeout(() => {
                        errorDiv.remove();
                    }, 3000);

                    // Scroll to error
                    annualYearSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            } else {
                const selectedTimeline = document.querySelector('input[name="timeline_id"]:checked');
                if (!selectedTimeline) {
                    e.preventDefault();

                    // Show error
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-error text-sm mt-4';
                    errorDiv.innerHTML = `
                        <i class="iconify-inline" data-icon="lucide:alert-circle"></i>
                        <span>Please select a specific timeline</span>
                    `;

                    // Insert at the end of the container
                    const container = document.getElementById('timelines-container');
                    if (container) {
                        container.after(errorDiv);
                    } else {
                        specificContent.appendChild(errorDiv);
                    }

                    // Remove after 3 seconds
                    setTimeout(() => {
                        errorDiv.remove();
                    }, 3000);

                    // Scroll to the timelines container
                    if (container) {
                        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            }
        });
    });
</script>

