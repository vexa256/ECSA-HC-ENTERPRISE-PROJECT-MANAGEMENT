<div class="h-screen flex items-center justify-center bg-base-100 overflow-hidden">
    <div class="card bg-base-100 shadow-xl border border-base-200 w-full max-w-md">
        <div class="card-body p-6 relative">
            <!-- Dark mode toggle positioned absolutely -->
            <div class="absolute top-4 right-4">
                <label class="swap swap-rotate">
                    <input type="checkbox" id="darkModeSwitch" class="theme-controller" value="dark" />

                </label>
            </div>

            <div>
                <h2 class="text-2xl font-bold text-center mb-2 animate-fade-in">Select Reporting Year</h2>
                <p class="text-base-content/60 text-center mb-6 animate-fade-in-delay-1">
                    Choose a year to view strategic objective performance
                </p>

                <form id="yearSelectForm" class="animate-fade-in-delay-2 space-y-4">
                    <div class="form-control">
                        <select id="yearSelect" class="select select-bordered w-full text-base" required>
                            <option value="" selected disabled>Select a year</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-control">
                        <button type="submit" id="submitButton" class="btn btn-primary w-full" disabled>
                            Continue
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    /* Force everything to fit exactly in the viewport without scrolling */
    html,
    body {
        height: 100%;
        overflow: hidden;
        margin: 0;
    }

    :root {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        -webkit-font-smoothing: antialiased;
    }

    .animate-fade-in {
        animation: fadeIn 0.8s ease-in-out forwards;
    }

    .animate-fade-in-delay-1 {
        opacity: 0;
        animation: fadeIn 0.8s ease-in-out forwards;
        animation-delay: 0.3s;
    }

    .animate-fade-in-delay-2 {
        opacity: 0;
        animation: fadeIn 0.8s ease-in-out forwards;
        animation-delay: 0.6s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23666'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem;
        padding-right: 2.5rem;
        border-radius: 0.75rem;
        height: 3rem;
        font-size: 1rem;
        transition: all 0.2s ease;
    }

    .select:focus {
        box-shadow: 0 0 0 2px rgba(var(--p) / 0.2);
    }

    .btn {
        border-radius: 0.75rem;
        height: 3rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-image: linear-gradient(to right, hsl(var(--p)), hsl(var(--s)));
        border: none;
        box-shadow: 0 4px 10px rgba(var(--p) / 0.2);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(var(--p) / 0.3);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    .card {
        border-radius: 1.5rem;
        backdrop-filter: blur(10px);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeSwitch = document.getElementById('darkModeSwitch');
        const yearSelect = document.getElementById('yearSelect');
        const yearSelectForm = document.getElementById('yearSelectForm');
        const submitButton = document.getElementById('submitButton');

        // Check for saved theme preference or system preference
        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            darkModeSwitch.checked = true;
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            darkModeSwitch.checked = false;
        }

        // Dark mode toggle
        darkModeSwitch.addEventListener('change', function() {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('theme', 'light');
            }
        });

        // Enable/disable submit button based on selection
        yearSelect.addEventListener('change', function() {
            submitButton.disabled = !this.value;
            if (this.value) {
                submitButton.classList.add('animate-pulse');
                setTimeout(() => {
                    submitButton.classList.remove('animate-pulse');
                }, 500);
            }
        });

        // Form submission
        yearSelectForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const selectedYear = yearSelect.value;
            if (selectedYear) {
                submitButton.classList.add('loading');
                setTimeout(() => {
                    window.location.href = `/Ecsa_SO_selectReport?year=${selectedYear}`;
                }, 300);
            }
        });
    });
</script>
