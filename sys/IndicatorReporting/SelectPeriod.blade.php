<div class="p-4 space-y-4">
    <!-- Top Banner (gradient) -->
    <div
        class="card shadow hover:shadow-lg transform transition hover:-translate-y-1 bg-gradient-cool rounded-lg text-white">
        <div class="card-body flex items-center space-x-4 p-6">
            <div class="rounded-full bg-white p-3">
                <i class="fas fa-calendar-alt fa-2x text-primary"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold mb-1">Reporting Timeline</h1>
                <p class="text-white/70 text-sm">
                    Select the reporting period for {{ $entity->Entity }}
                </p>
            </div>
        </div>
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Left Column (Reporting Period Form) -->
        <div class="md:col-span-2">
            <div class="card shadow hover-elevate-up transition rounded-lg bg-base-100">
                <div class="card-body p-6">
                    <h2 class="text-xl font-semibold mb-4 flex items-center space-x-2">
                        <i class="fas fa-clock text-primary"></i>
                        <span>Choose Reporting Period</span>
                    </h2>

                    <!-- Form -->
                    <form action="{{ route('indicator.show') }}" method="GET" id="periodForm" class="space-y-4">
                        <input type="hidden" name="entity_id" value="{{ $entity->EntityID }}">

                        <!-- Reporting Period Select -->
                        <div class="form-control">
                            <label class="label font-semibold" for="reporting_period">
                                <span class="label-text">Reporting Period</span>
                            </label>
                            <select name="reporting_period" id="reporting_period"
                                class="select select-bordered w-full custom-select" required>
                                <option value="" selected disabled>Select Reporting Period</option>
                                @foreach ($reportingPeriods as $period)
                                    <option value="{{ $period->ReportingID }}">
                                        {{ $period->ReportName }} ({{ $period->Year }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button type="submit" id="proceedButton"
                                class="btn btn-primary w-full text-lg custom-btn-hover" disabled>
                                <i class="fas fa-chart-line mr-2"></i>
                                Proceed to Indicators
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right Column (Why It Matters) -->
        <div>
            <div class="card shadow hover-elevate-up transition rounded-lg bg-base-100 flex flex-col h-full">
                <div class="card-body p-6 flex-1">
                    <h3 class="text-lg font-semibold mb-4 flex items-center space-x-2">
                        <i class="fas fa-lightbulb text-warning"></i>
                        <span>Why This Matters</span>
                    </h3>
                    <ul class="timeline-list space-y-3">
                        <li class="timeline-item relative pl-8">
                            <span class="timeline-point bg-primary"></span>
                            <p class="m-0">Ensures data consistency across reporting periods</p>
                        </li>
                        <li class="timeline-item relative pl-8">
                            <span class="timeline-point bg-success"></span>
                            <p class="m-0">Facilitates accurate trend analysis over time</p>
                        </li>
                        <li class="timeline-item relative pl-8">
                            <span class="timeline-point bg-info"></span>
                            <p class="m-0">Aligns with strategic planning cycles</p>
                        </li>
                    </ul>
                </div>
                <div class="card-footer bg-base-200 border-t-0 p-4 flex items-center space-x-3">
                    <i class="fas fa-info-circle text-primary fa-2x"></i>
                    <small class="text-gray-600 leading-5">
                        Select the most recent completed period for up-to-date reporting.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Tailwind / DaisyUI styling & animations -->
<style>
    /* Gradient background (retained from original snippet) */
    .bg-gradient-cool {
        background: linear-gradient(135deg, #3a7bd5, #00d2ff);
    }

    /* Subtle hover-lift effect */
    .hover-elevate-up:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0, 0, 0, .15) !important;
    }

    /* Custom select border (optional) */
    .custom-select {
        border: 2px solid #e9ecef !important;
        border-radius: 0.5rem !important;
    }

    .custom-select:focus {
        border-color: #3a7bd5 !important;
        box-shadow: 0 0 0 0.2rem rgba(58, 123, 213, 0.25) !important;
    }

    /* Extra hover effect on the button */
    .custom-btn-hover:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .15);
    }

    /* Timeline styling */
    .timeline-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .timeline-item {
        position: relative;
    }

    .timeline-point {
        position: absolute;
        left: -1.7rem;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 9999px;
    }

    /* Pulse animation for button */
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(58, 123, 213, 0.7);
        }

        70% {
            box-shadow: 0 0 0 10px rgba(58, 123, 213, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(58, 123, 213, 0);
        }
    }

    .btn-pulse {
        animation: pulse 1.5s infinite;
    }
</style>

<!-- Script for enabling/disabling the button -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const periodSelect = document.getElementById('reporting_period');
        const proceedButton = document.getElementById('proceedButton');

        function updateButtonState() {
            const isSelected = periodSelect.value !== "";
            proceedButton.disabled = !isSelected;
            if (isSelected) {
                proceedButton.classList.add('btn-pulse');
            } else {
                proceedButton.classList.remove('btn-pulse');
            }
        }

        periodSelect.addEventListener('change', updateButtonState);
        updateButtonState(); // run on load
    });
</script>
