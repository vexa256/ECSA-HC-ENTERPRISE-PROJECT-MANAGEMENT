<div class="container mx-auto px-4 py-8">
    <header class="mb-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="avatar placeholder">
                    <div class="bg-primary text-primary-content rounded-full w-16">
                        <span class="text-2xl">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" class="w-8 h-8">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl font-bold mb-1">Available Reports</h1>
                    <p class="text-base-content/70">{{ $Desc }}</p>
                </div>
            </div>
            <div>
                <a href="{{ route('Ecsa_SelectCluster') }}" class="btn btn-outline btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Cluster Selection
                </a>
            </div>
        </div>
    </header>

    <main>
        <div class="card bg-base-100 shadow-xl transition-all duration-300 hover:shadow-2xl">
            <div class="card-body">
                <h2 class="card-title text-xl mb-6">Available Reports</h2>
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Type</th>
                                <th>Closing Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($timelines as $timeline)
                                <tr class="hover:bg-base-200 transition-colors duration-200">
                                    <td>{{ $timeline->ReportName }}</td>
                                    <td>{{ $timeline->Type }}</td>
                                    <td>{{ \Carbon\Carbon::parse($timeline->ClosingDate)->format('M d, Y') }}</td>
                                    <td>
                                        <span
                                            class="badge {{ $timeline->status === 'Completed' ? 'badge-success' : ($timeline->status === 'In Progress' ? 'badge-warning' : 'badge-secondary') }}">
                                            {{ $timeline->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('Ecsa_SelectStrategicObjective') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="UserID" value="{{ $UserID }}">
                                            <input type="hidden" name="ClusterID" value="{{ $ClusterID }}">
                                            <input type="hidden" name="ReportingID"
                                                value="{{ $timeline->ReportingID }}">
                                            <button type="submit" class="btn btn-neutral btn-sm">
                                                Select
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;500;600;700&display=swap');

    body {
        font-family: 'SF Pro Display', sans-serif;
    }

    .table th:first-child {
        position: static;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.classList.add('scale-[1.02]', 'shadow-md');
                this.style.transition = 'all 0.3s ease';
            });
            row.addEventListener('mouseleave', function() {
                this.classList.remove('scale-[1.02]', 'shadow-md');
            });
        });

        // Add haptic feedback for mobile devices
        const buttons = document.querySelectorAll('button');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                if ('vibrate' in navigator) {
                    navigator.vibrate(50);
                }
            });
        });
    });
</script>
