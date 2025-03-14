<div class="container px-4 py-8 mx-auto">
    <header class="mb-8">
        <div class="flex flex-col items-center justify-between md:flex-row">
            <div>
                <h1 class="mb-2 text-2xl font-bold">Select Strategic Objective</h1>
                <p class="text-base-content/70">{{ $Desc }}</p>
            </div>
            <a href="{{ route('Ecsa_SelectTimeline', ['UserID' => $UserID, 'ClusterID' => $ClusterID]) }}"
                class="mt-4 btn btn-outline btn-primary md:mt-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Back to Timeline Selection
            </a>
        </div>
    </header>

    <main class="grid grid-cols-1 gap-8 lg:grid-cols-1">
        <section class="lg:col-span-8">
            <div class="shadow-xl card bg-base-100">
                <div class="card-body">
                    <h2 class="mb-6 text-xl card-title">Select a Strategic Objective</h2>
                    <form action="{{ route('Ecsa_ReportPerformanceIndicators') }}" method="GET"
                        id="strategicObjectiveForm">
                        @csrf
                        <input type="hidden" name="UserID" value="{{ $UserID }}">
                        <input type="hidden" name="ClusterID" value="{{ $ClusterID }}">
                        <input type="hidden" name="ReportingID" value="{{ $ReportingID }}">
                        <input type="hidden" name="userName" value="{{ $userName }}">
                        <input type="hidden" name="clusterName" value="{{ $clusterName }}">
                        <input type="hidden" name="timelineName" value="{{ $timelineName }}">

                        <div class="w-full mb-4 form-control">
                            <label for="StrategicObjectiveID" class="label">
                                <span class="label-text">Strategic Objective</span>
                            </label>
                            <select
                                class="select select-bordered w-full @error('StrategicObjectiveID') select-error @enderror"
                                id="StrategicObjectiveID" name="StrategicObjectiveID" required>
                                <option value="">Select a strategic objective...</option>
                                @foreach ($strategicObjectives as $objective)
                                    <option value="{{ $objective->StrategicObjectiveID }}"
                                        data-description="{{ $objective->Description }}"
                                        {{ old('StrategicObjectiveID') == $objective->StrategicObjectiveID ? 'selected' : '' }}>
                                        {{ $objective->SO_Number }} - {{ $objective->SO_Name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('StrategicObjectiveID')
                                <label class="label">
                                    <span class="label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div id="objectiveDescription" class="hidden mt-4 shadow-lg alert alert-info" role="alert">
                            <div>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    class="flex-shrink-0 w-6 h-6 stroke-current">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="font-bold">Objective Description</h3>
                                    <p class="text-sm"></p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="w-full btn btn-neutral" id="submitBtn" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 8.688c0-.864.933-1.405 1.683-.977l7.108 4.062a1.125 1.125 0 010 1.953l-7.108 4.062A1.125 1.125 0 013 16.81V8.688zM12.75 8.688c0-.864.933-1.405 1.683-.977l7.108 4.062a1.125 1.125 0 010 1.953l-7.108 4.062a1.125 1.125 0 01-1.683-.977V8.688z" />
                                </svg>
                                Continue to Performance Indicators
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <aside class="space-y-6 lg:col-span-4" style="display: none">
            <div class="card bg-primary text-primary-content">
                <div class="card-body">
                    <h3 class="card-title">Why Select a Strategic Objective?</h3>
                    <p>Choosing a strategic objective allows you to:</p>
                    <ul class="mt-2 space-y-2">
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Focus on specific organizational goals</span>
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Align reporting with strategic priorities</span>
                        </li>
                        <li class="flex items-start">
                            <svg xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0 w-6 h-6 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <span>Track progress towards key objectives</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card bg-warning text-warning-content">
                <div class="card-body">
                    <h3 class="card-title">Reporting Context</h3>
                    <div class="space-y-2">
                        <div>
                            <span class="font-semibold">Selected User:</span>
                            <p>{{ $userName }}</p>
                        </div>
                        <div>
                            <span class="font-semibold">Selected Cluster:</span>
                            <p>{{ $clusterName }}</p>
                        </div>
                        <div>
                            <span class="font-semibold">Reporting Timeline:</span>
                            <p>{{ $timelineName }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </main>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=SF+Pro+Display:wght@400;500;600;700&display=swap');

    body {
        font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, sans-serif;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const selectElement = document.getElementById('StrategicObjectiveID');
        const descriptionElement = document.getElementById('objectiveDescription');
        const submitBtn = document.getElementById('submitBtn');

        selectElement.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const description = selectedOption.dataset.description;

            if (description) {
                descriptionElement.querySelector('p').textContent = description;
                descriptionElement.classList.remove('hidden');
            } else {
                descriptionElement.classList.add('hidden');
            }

            submitBtn.disabled = !this.value;

            // Haptic feedback for mobile devices
            if ('vibrate' in navigator) {
                navigator.vibrate(50);
            }
        });

        // Micro-interaction for button
        submitBtn.addEventListener('mouseenter', function() {
            this.classList.add('scale-105');
            this.style.transition = 'all 0.3s ease';
        });

        submitBtn.addEventListener('mouseleave', function() {
            this.classList.remove('scale-105');
        });

        // Keyboard navigation
        selectElement.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
</script>
