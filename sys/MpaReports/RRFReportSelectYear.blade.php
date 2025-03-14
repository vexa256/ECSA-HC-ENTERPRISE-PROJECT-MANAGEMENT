<div class="container mx-auto px-4 py-12">
    <div class="flex justify-center">
        <div class="w-full max-w-md">
            <div
                class="card shadow-lg bg-white/90 backdrop-blur-sm transition-transform hover:-translate-y-2 hover:shadow-2xl">
                <div class="card-body p-6">
                    <h2 class="card-title text-center mb-4 animate__animated animate__fadeInDown">
                        <span class="text-gradient">Select Reporting Year</span>
                    </h2>
                    @if (isset($reportType) && $reportType)
                        <div class="alert alert-info mb-4 animate__animated animate__fadeIn">
                            <div class="flex items-center">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path>
                                        <path d="M12 9h.01"></path>
                                        <path d="M11 12h1v4h1"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    Selected Report Type: <strong>{{ $reportType }}</strong>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-4 animate__animated animate__fadeIn">
                            <div class="flex items-center">
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M12 9v2m0 4v.01"></path>
                                        <path
                                            d="M5 19h14a2 2 0 0 0 1.84 -2.75l-7.1 -12.25a2 2 0 0 0 -3.5 0l-7.1 12.25a2 2 0 0 0 1.75 2.75">
                                        </path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    No report type selected. Please go back and select a report type.
                                </div>
                            </div>
                        </div>
                    @endif
                    <form action="{{ route('rrf.report.dashboard') }}" method="POST" id="yearSelectForm">
                        @csrf
                        <input type="hidden" name="report_type" value="{{ $reportType ?? '' }}">
                        <div class="mb-4 relative">
                            <label for="reporting_year" class="sr-only">Reporting Year</label>
                            <select name="reporting_year" id="reporting_year"
                                class="select select-bordered select-lg w-full" required>
                                <option value="" selected disabled>Choose a reporting year</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}">{{ $year }}</option>
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
                                        class="icon icon-tabler icon-tabler-chart-dots" width="24" height="24"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                        <path d="M3 3v18h18"></path>
                                        <path d="M9 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M19 7m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M14 15m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0"></path>
                                        <path d="M10.16 10.62l2.34 2.88"></path>
                                        <path d="M15.088 13.328l2.837 -4.586"></path>
                                    </svg>
                                </span>
                                Generate Dashboard
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

    /* Custom select arrow */
    #reporting_year {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    #reporting_year:hover,
    #reporting_year:focus {
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

    /* Custom card styling */
    .card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    /* Alert info custom gradient (if needed) */
    .alert-info {
        background: linear-gradient(45deg, #e0f7fa, #b2ebf2);
        border: none;
        color: #006064;
    }
</style>

{{--
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const select = document.getElementById('reporting_year');
      const form = document.getElementById('yearSelectForm');

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
  --}}
