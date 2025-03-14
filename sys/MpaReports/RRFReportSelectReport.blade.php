<div class="container mx-auto px-4 py-12">
    <div class="flex justify-center">
        <div class="w-full max-w-md">
            <div
                class="card shadow-lg bg-white/90 backdrop-blur-sm transition-transform hover:-translate-y-2 hover:shadow-2xl">
                <div class="card-body p-6">
                    <h2 class="card-title text-center mb-4 animate__animated animate__fadeInDown">
                        <span class="text-gradient">Select RRF Report Type</span>
                    </h2>
                    <form action="{{ route('rrf.report.selectYear') }}" method="GET" id="reportTypeForm">
                        <div class="mb-4 relative">
                            <label for="report_type" class="sr-only">Report Type</label>
                            <select name="report_type" id="report_type" class="select select-bordered select-lg w-full"
                                required>
                                <option value="" selected disabled>Choose a report type</option>
                                @foreach ($reportTypes as $type)
                                    <option value="{{ $type }}">{{ $type }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-600" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                class="btn btn-primary btn-lg animate__animated animate__pulse animate__infinite w-full">
                                <span class="mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="icon icon-tabler icon-tabler-chart-arrows" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M3 18h14"></path>
                                        <path d="M9 9l3 3l3 -3"></path>
                                        <path d="M14 15l3 3l3 -3"></path>
                                        <path d="M3 3v18"></path>
                                        <path d="M3 12h9"></path>
                                        <path d="M18 3l3 3l-3 3"></path>
                                    </svg>
                                </span>
                                Next
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-gradient {
        background: linear-gradient(45deg, #12c2e9, #c471ed, #f64f59);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-fill-color: transparent;
    }

    /* Custom styles for the select arrow */
    #report_type {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    #report_type:hover,
    #report_type:focus {
        box-shadow: 0 0 15px rgba(18, 194, 233, 0.5);
    }

    /* Custom button gradient styling */
    .btn-primary {
        background: linear-gradient(45deg, #12c2e9, #c471ed, #f64f59);
        border: none;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const select = document.getElementById('report_type');
        const form = document.getElementById('reportTypeForm');

        select.addEventListener('change', function() {
            select.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                select.classList.remove('animate__animated', 'animate__pulse');
            }, 1000);
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            if (select.value) {
                this.classList.add('animate__animated', 'animate__fadeOutUp');
                setTimeout(() => {
                    this.submit();
                }, 500);
            }
        });
    });
</script>
