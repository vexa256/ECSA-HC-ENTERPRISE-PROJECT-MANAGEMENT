<div class="h-screen bg-base-100 flex items-center justify-center p-4 overflow-hidden">
    <div class="card bg-base-100 shadow-xl border border-base-200 w-full max-w-md overflow-hidden">
        <div class="card-body p-6 flex flex-col justify-between h-full">
            <div class="text-center mb-4">
                <h1 class="text-3xl font-bold text-primary">ECSA-HC</h1>
                <p class="text-base-content/70">Strategic Performance Tracking</p>
            </div>

            <h2 class="text-xl font-bold mb-2 text-center">Select Report for {{ $selectedYear }}</h2>

            <form id="reportSelectForm" class="flex flex-col justify-between flex-grow">
                <div class="form-control mb-4">
                    <label for="reportSelect" class="label">
                        <span class="label-text">Choose a report:</span>
                    </label>
                    <select id="reportSelect" class="select select-bordered w-full max-h-40" required>
                        <option value="" selected disabled>Select a report</option>
                        @foreach ($reports as $report)
                            <option value="{{ $report->ReportingID }}">{{ $report->ReportName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-between items-center mt-6 flex-shrink-0">
                    <a href="{{ route('Ecsa_SO_selectYear') }}" class="btn btn-outline">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                                clip-rule="evenodd" />
                        </svg>
                        Back
                    </a>
                    <button type="submit" id="submitButton" class="btn btn-primary" disabled>
                        Continue
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Dark Mode Toggle fixed at bottom-right -->
<div class="fixed bottom-4 right-4 z-10">
    <label class="swap swap-rotate">
        <input type="checkbox" id="darkModeToggle" class="theme-controller" value="dark" />
        <svg class="swap-on fill-current w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path
                d="M5.64,17l-.71.71a1,1,0,0,0,0,1.41,1,1,0,0,0,1.41,0l.71-.71A1,1,0,0,0,5.64,17ZM5,12a1,1,0,0,0-1-1H3a1,1,0,0,0,0,2H4A1,1,0,0,0,5,12Zm7-7a1,1,0,0,0,1-1V3a1,1,0,0,0-2,0V4A1,1,0,0,0,12,5ZM5.64,7.05a1,1,0,0,0,.7.29,1,1,0,0,0,.71-.29,1,1,0,0,0,0-1.41l-.71-.71A1,1,0,0,0,4.93,6.34Zm12,.29a1,1,0,0,0,.7-.29l.71-.71a1,1,0,1,0-1.41-1.41L17,5.64a1,1,0,0,0,0,1.41A1,1,0,0,0,17.66,7.34ZM21,11H20a1,1,0,0,0,0,2h1a1,1,0,0,0,0-2Zm-9,8a1,1,0,0,0-1,1v1a1,1,0,0,0,2,0V20A1,1,0,0,0,12,19ZM18.36,17A1,1,0,0,0,17,18.36l.71.71a1,1,0,0,0,1.41,0,1,1,0,0,0,0-1.41ZM12,6.5A5.5,5.5,0,1,0,17.5,12,5.51,5.51,0,0,0,12,6.5Zm0,9A3.5,3.5,0,1,1,15.5,12,3.5,3.5,0,0,1,12,15.5Z" />
        </svg>
        <svg class="swap-off fill-current w-8 h-8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path
                d="M21.64,13a1,1,0,0,0-1.05-.14,8.05,8.05,0,0,1-3.37.73A8.15,8.15,0,0,1,9.08,5.49a8.59,8.59,0,0,1,.25-2A1,1,0,0,0,8,2.36,10.14,10.14,0,1,0,22,14.05,1,1,0,0,0,21.64,13Zm-9.5,6.69A8.14,8.14,0,0,1,7.08,5.22v.27A10.15,10.15,0,0,0,17.22,15.63a9.79,9.79,0,0,0,2.1-.22A8.11,8.11,0,0,1,12.14,19.73Z" />
        </svg>
    </label>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        -webkit-font-smoothing: antialiased;
    }

    /* Ensure the entire page fits exactly within the viewport */
    html,
    body {
        height: 100%;
        overflow: hidden;
        margin: 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const reportSelect = document.getElementById('reportSelect');
        const reportSelectForm = document.getElementById('reportSelectForm');
        const submitButton = document.getElementById('submitButton');

        // Set theme based on saved preference or system preference
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            darkModeToggle.checked = true;
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            darkModeToggle.checked = false;
        }

        // Dark mode toggle functionality
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
        });

        // Enable or disable the submit button based on selection
        reportSelect.addEventListener('change', function() {
            submitButton.disabled = !this.value;
            if (this.value) {
                submitButton.classList.add('animate-pulse');
                setTimeout(() => {
                    submitButton.classList.remove('animate-pulse');
                }, 500);
            }
        });

        // Handle form submission
        reportSelectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const selectedReport = reportSelect.value;
            if (selectedReport) {
                submitButton.classList.add('loading');
                setTimeout(() => {
                    window.location.href =
                        `/Ecsa_SO_showPerformance?year={{ $selectedYear }}&report=${selectedReport}`;
                }, 300);
            }
        });

        // Subtle focus animations on the select element
        reportSelect.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.transition = 'transform 0.3s ease';
        });
        reportSelect.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
    });
</script>
