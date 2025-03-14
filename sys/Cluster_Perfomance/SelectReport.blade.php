<div class="container mx-auto px-4">
    <!-- Page Header -->
    <div class="py-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
            <div>
                <h2 class="text-2xl font-bold">
                    Select Performance Report
                </h2>
                <div class="text-gray-500 mt-1">Choose a report for {{ $selectedYear }} to view cluster performance
                    breakdown</div>
            </div>
            <div class="mt-4 md:mt-0">
                <a href="{{ route('Ecsa_CP_selectYear') }}" class="btn btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-arrow-left">
                        <path d="m12 19-7-7 7-7" />
                        <path d="M19 12H5" />
                    </svg>
                    Back to Year Selection
                </a>
            </div>
        </div>
    </div>

    <!-- Page Body -->
    <div class="py-4">
        <div class="max-w-md mx-auto">
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title">Available Reports for {{ $selectedYear }}</h3>

                    <div class="py-4">
                        @if ($reports->isEmpty())
                            <div class="alert alert-info">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-info">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M12 16v-4" />
                                    <path d="M12 8h.01" />
                                </svg>
                                No reports available for the selected year.
                            </div>
                        @else
                            @foreach ($reports as $report)
                                <div class="form-control mb-3">
                                    <label class="label cursor-pointer justify-start gap-3">
                                        <input type="radio" name="report" id="report{{ $report->id }}"
                                            value="{{ $report->ReportingID }}" class="radio radio-primary" />
                                        <div>
                                            <span class="label-text font-medium">{{ $report->ReportName }}</span>
                                            <span class="label-text text-gray-500 block text-sm">
                                                Closing Date:
                                                {{ \Carbon\Carbon::parse($report->ClosingDate)->format('d M Y') }}
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="card-actions justify-end mt-4">
                        <button type="button" class="btn btn-primary" id="viewReportBtn" disabled>
                            View Report
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-bar-chart-2">
                                <path d="M18 20V10" />
                                <path d="M12 20V4" />
                                <path d="M6 20v-6" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reportInputs = document.querySelectorAll('input[name="report"]');
        const viewReportBtn = document.getElementById('viewReportBtn');

        reportInputs.forEach(input => {
            input.addEventListener('change', function() {
                viewReportBtn.disabled = false;
            });
        });

        viewReportBtn.addEventListener('click', function() {
            const selectedReport = document.querySelector('input[name="report"]:checked');
            if (selectedReport) {
                window.location.href =
                    "{{ route('Ecsa_CP_showPerformance') }}?year={{ $selectedYear }}&report=" +
                    selectedReport.value;
            }
        });
    });
</script>
