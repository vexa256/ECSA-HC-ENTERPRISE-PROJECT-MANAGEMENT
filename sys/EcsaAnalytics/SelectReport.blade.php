<div class="container mx-auto p-4">
    <div class="max-w-5xl mx-auto">
        <h1 class="text-4xl font-bold text-center mb-3">Select a Report</h1>
        <div class="flex justify-center items-center mb-8">
            <span class="badge badge-lg bg-blue-100 text-blue-800 mr-2">
                <iconify-icon icon="mdi:layers" class="mr-1"></iconify-icon>
                {{ $selectedCluster === 'All clusters' ? 'All Clusters' : $clusters->firstWhere('ClusterID', $selectedCluster)->Cluster_Name }}
            </span>
            <iconify-icon icon="mdi:chevron-right" class="text-gray-400 mx-2"></iconify-icon>
            <span class="badge badge-lg bg-green-100 text-green-800">
                <iconify-icon icon="mdi:calendar" class="mr-1"></iconify-icon>
                {{ $selectedYear }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($reports as $report)
                <div class="card bg-base-100 shadow-sm hover:shadow-md transition-all duration-300 cursor-pointer report-card"
                    data-report="{{ $report->ReportingID }}">
                    <div class="card-body">
                        <div class="flex justify-between items-center mb-3">
                            <h3 class="card-title text-lg">
                                <iconify-icon icon="mdi:file-document-outline"
                                    class="mr-2 text-gray-500"></iconify-icon>
                                {{ $report->ReportName }}
                            </h3>
                            <span
                                class="badge {{ $report->status === 'Completed' ? 'badge-success' : ($report->status === 'In Progress' ? 'badge-warning' : 'badge-error') }}">
                                {{ $report->status }}
                            </span>
                        </div>
                        <p class="text-gray-600 flex-grow">{{ Str::limit($report->Description, 100) }}</p>
                        <div class="mt-4 space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">
                                    <iconify-icon icon="mdi:clock-outline" class="mr-1"></iconify-icon>
                                    Closing Date:
                                </span>
                                <span class="badge badge-info">
                                    {{ \Carbon\Carbon::parse($report->ClosingDate)->format('M d, Y') }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-500">
                                    <iconify-icon icon="mdi:chart-pie" class="mr-1"></iconify-icon>
                                    Type:
                                </span>
                                <span class="badge badge-primary">
                                    {{ $report->Type }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 pb-4">
                        <progress
                            class="progress w-full {{ $report->status === 'Completed' ? 'progress-success' : ($report->status === 'In Progress' ? 'progress-warning' : 'progress-error') }}"
                            value="{{ $report->status === 'Completed' ? '100' : ($report->status === 'In Progress' ? '50' : '0') }}"
                            max="100">
                        </progress>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<form id="report-form" action="{{ route('performance-overview') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="cluster" value="{{ $selectedCluster }}">
    <input type="hidden" name="year" value="{{ $selectedYear }}">
    <input type="hidden" name="report" id="selected-report">
</form>

<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const reportCards = document.querySelectorAll('.report-card');
        const reportForm = document.getElementById('report-form');
        const selectedReportInput = document.getElementById('selected-report');

        reportCards.forEach(card => {
            card.addEventListener('click', function() {
                const report = this.dataset.report;
                selectedReportInput.value = report;
                reportForm.submit();
            });
        });
    });
</script>

<style>
    .report-card {
        transition: all 0.3s ease;
    }

    .report-card:hover {
        transform: translateY(-2px);
    }

    .report-card:hover .card-title {
        color: hsl(var(--p));
    }

    .progress {
        height: 4px;
    }
</style>
