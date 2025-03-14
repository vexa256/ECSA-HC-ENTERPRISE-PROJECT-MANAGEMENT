<header class="mb-6">
    <div class="container px-4 mx-auto">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight">
                    Select Cluster for Reporting
                </h2>
                <p class="mt-1 text-sm text-neutral-500">{{ $Desc }}</p>
            </div>
            <div>
                <a href="{{ route('Ecsa_SelectTimeline') }}" class="btn btn-outline" aria-label="Back to user selection">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="mr-2">
                        <path d="m12 19-7-7 7-7"></path>
                        <path d="M19 12H5"></path>
                    </svg>
                    Back to User Selection
                </a>
            </div>
        </div>
    </div>
</header>

<main class="container px-4 mx-auto">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-1">
        <section class="md:col-span-8">
            <div
                class="overflow-hidden transition-all duration-200 shadow-sm card bg-base-100 rounded-xl hover:shadow-md">
                <div class="p-6 card-body">
                    <h3 class="mb-5 text-xl font-medium card-title">Select a Cluster for {{ $userName }}</h3>
                    <form action="{{ route('Ecsa_SelectTimeline') }}" method="POST">
                        @csrf
                        <input type="hidden" name="UserID" value="{{ $user->UserID }}">
                        <input type="hidden" name="userName" value="{{ $userName }}">

                        <div class="w-full mb-6 form-control">
                            <label for="ClusterID" class="label">
                                <span class="font-medium label-text">Select Cluster</span>
                            </label>
                            <select
                                class="select select-bordered w-full h-12 focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all duration-200 @error('ClusterID') select-error @enderror"
                                id="ClusterID" name="ClusterID" required aria-required="true"
                                aria-invalid="@error('ClusterID') true @else false @enderror">
                                <option value="" disabled selected>Select a cluster...</option>
                                @foreach ($clusters as $cluster)
                                    <option value="{{ $cluster->ClusterID }}"
                                        {{ old('ClusterID') == $cluster->ClusterID ? 'selected' : '' }}>
                                        {{ $cluster->Cluster_Name }} - {{ $cluster->Description }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ClusterID')
                                <label class="label">
                                    <span class="text-sm label-text-alt text-error">{{ $message }}</span>
                                </label>
                            @enderror
                        </div>

                        <div class="mt-8">
                            <button type="submit" class="w-full h-12 btn btn-neutral group">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="mr-2 transition-transform duration-300 group-hover:translate-x-1">
                                    <path d="M3 3v18h18"></path>
                                    <circle cx="9" cy="9" r="2"></circle>
                                    <circle cx="19" cy="7" r="2"></circle>
                                    <circle cx="14" cy="15" r="2"></circle>
                                    <path d="m10.16 10.62 2.34 2.88"></path>
                                    <path d="m15.088 13.328 2.837-4.586"></path>
                                </svg>
                                Continue to Timeline Selection
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>


    </div>
</main>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Enhanced select functionality
        const selectElement = document.getElementById('ClusterID');

        // Add focus and blur event listeners for micro-interactions
        selectElement.addEventListener('focus', function() {
            this.parentElement.classList.add('select-focused');
        });

        selectElement.addEventListener('blur', function() {
            this.parentElement.classList.remove('select-focused');
        });

        // Optional: Add haptic feedback for mobile devices
        selectElement.addEventListener('change', function() {
            if (window.navigator && window.navigator.vibrate) {
                window.navigator.vibrate(50); // Subtle vibration on select change
            }
        });

        // Keyboard navigation enhancement
        selectElement.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
</script>
