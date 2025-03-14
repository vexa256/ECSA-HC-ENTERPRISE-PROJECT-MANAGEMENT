<div class="min-h-screen flex flex-col justify-center bg-base-200 p-4 pt-1">
    <div class="w-full max-w-md mx-auto">
        <header class="text-center mb-6">
            <h2 class="text-2xl font-semibold text-neutral">
                Select Reporting Year
            </h2>
            <p class="mt-2 text-sm text-accent-content">
                Choose a year to view cluster performance breakdown
            </p>
        </header>

        <main>
            <div class="card bg-base-100 shadow-lg rounded-2xl overflow-hidden">
                <div class="card-body p-6">
                    <h3 class="card-title text-lg font-medium mb-4 text-center">Available Years</h3>
                    <div class="form-control">
                        <label for="yearSelect" class="label justify-center">
                            <span class="label-text text-sm font-medium">Select a Year</span>
                        </label>
                        <select id="yearSelect"
                            class="select select-bordered w-full bg-base-200 border-neutral-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
                            aria-label="Select a year">
                            <option value="">Choose a year...</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="card-actions justify-center mt-6">
                        <button type="button" id="continueBtn" class="btn btn-neutral w-full sm:w-auto" disabled>
                            Continue
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        @apply bg-base-200;
    }

    .select:focus,
    .btn:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
    }

    .btn {
        transition: all 0.2s ease-in-out;
    }

    .btn:active {
        transform: scale(0.98);
    }

    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }

        100% {
            background-position: 1000px 0;
        }
    }

    .skeleton {
        animation: shimmer 2s infinite linear;
        background: linear-gradient(to right, #f6f7f8 8%, #edeef1 18%, #f6f7f8 33%);
        background-size: 1000px 100%;
    }
</style>

<style>
    html {
        padding: 0px;
        margin: 0px;
        overflow: hidden;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const yearSelect = document.getElementById('yearSelect');
        const continueBtn = document.getElementById('continueBtn');

        yearSelect.addEventListener('change', function() {
            continueBtn.disabled = !this.value;
            if (this.value) {
                continueBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                continueBtn.classList.add('hover:bg-neutral-focus');
            } else {
                continueBtn.classList.add('opacity-50', 'cursor-not-allowed');
                continueBtn.classList.remove('hover:bg-neutral-focus');
            }
        });

        continueBtn.addEventListener('click', function() {
            const selectedYear = yearSelect.value;
            if (selectedYear) {
                // Add loading state
                this.classList.add('loading');
                this.disabled = true;

                // Simulate haptic feedback
                if (window.navigator && window.navigator.vibrate) {
                    window.navigator.vibrate(50);
                }

                // Navigate to the report page
                window.location.href = "{{ route('Ecsa_CP_selectReport') }}?year=" + selectedYear;
            }
        });

        // Keyboard navigation
        yearSelect.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.click();
            }
        });

        continueBtn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.click();
            }
        });
    });
</script>


<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap');

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        @apply bg-base-200;
    }

    .select:focus,
    .btn:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
    }

    .btn {
        transition: all 0.2s ease-in-out;
    }

    .btn:active {
        transform: scale(0.98);
    }

    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }

        100% {
            background-position: 1000px 0;
        }
    }

    .skeleton {
        animation: shimmer 2s infinite linear;
        background: linear-gradient(to right, #f6f7f8 8%, #edeef1 18%, #f6f7f8 33%);
        background-size: 1000px 100%;
    }
</style>



<style>
    html {

        padding: 0px;
        margin: 0px;
        overflow: hidden;
    }
</style>
