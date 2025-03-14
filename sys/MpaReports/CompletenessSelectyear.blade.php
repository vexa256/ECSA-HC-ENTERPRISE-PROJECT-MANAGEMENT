{{-- resources/views/MpaReports/CompletenessSelectyear.blade.php --}}
<div style="margin-top:10%; margin-left:2%; margin-right:2%"
    class="fixed inset-0 flex items-start justify-center bg-gray-100 overflow-hidden pt-4">
    <div class="w-full max-w-md mx-4">
        <div class="card bg-white shadow-xl pt-5 mt-5">
            <div class="card-body p-6 ">
                <h2
                    class="card-title text-center text-2xl font-semibold text-primary mb-6 flex items-center justify-center mt-5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Select Reporting Year
                </h2>

                @if (session('error'))
                    <div class="alert alert-error mb-4 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 flex-shrink-0 stroke-current"
                            fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                <form action="{{ route('mpa.reports.completeness.index') }}" method="GET" id="yearSelectForm">
                    <div class="form-control mb-6">
                        <select name="reporting_year" id="reporting_year"
                            class="select select-bordered w-full bg-gray-50 rounded-xl border-gray-200 focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 h-12"
                            required>
                            <option value="" selected disabled>Choose reporting year</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control mt-6">
                        <button type="submit"
                            class="btn border-0 bg-blue-500 hover:bg-blue-600 text-white rounded-xl h-12 normal-case font-medium flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('yearSelectForm');
        const yearSelect = document.getElementById('reporting_year');

        // Prevent scrolling on the body for a true modal feel
        document.body.style.overflow = 'hidden';

        yearSelect.addEventListener('change', function() {
            if (this.value) {
                // Add a subtle highlight effect when the option changes
                this.classList.add('ring', 'ring-blue-300');
                setTimeout(() => {
                    this.classList.remove('ring', 'ring-blue-300');
                    form.submit();
                }, 300);
            }
        });

        form.addEventListener('submit', function(event) {
            if (!yearSelect.value) {
                event.preventDefault();
                yearSelect.classList.add('select-error', 'animate-shake');
                setTimeout(() => {
                    yearSelect.classList.remove('animate-shake');
                }, 500);
            } else {
                const button = this.querySelector('button[type="submit"]');
                button.classList.add('opacity-75');
                button.innerHTML =
                    '<span class="loading loading-spinner loading-sm mr-2"></span> Loading...';
            }
        });
    });
</script>
