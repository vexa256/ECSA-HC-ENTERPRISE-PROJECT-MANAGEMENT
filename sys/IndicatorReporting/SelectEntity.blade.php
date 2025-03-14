<div class="pt-8 bg-base-100 flex items-center justify-center">
    <div class="w-full max-w-6xl">
        <div class="text-center mb-4">
            <h1 class="text-3xl font-bold mb-2">Entity Selection </h1>
            <p class="text-lg text-base-content/70">Choose your entity to access tailored reports and analytics</p>
        </div>

        <div class="grid gap-6 md:grid-cols-3 pt-8">
            <div class="md:col-span-2 bg-base-200 rounded-lg shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                    Available Entities
                </h2>
                <form action="{{ route('reporting.period.select') }}" method="GET" id="entityForm">
                    <div class="mb-4">
                        <select name="entity_id" id="entity_id" class="select select-bordered w-full" required>
                            @if (auth()->user()->AccountRole === 'Admin')
                                <option value="" selected disabled>-- Select an Entity --</option>
                            @endif

                            @foreach ($entities as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-full">
                        Continue to Reporting Period
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </button>
                </form>
            </div>

            <div class="bg-base-200 rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Why Select an Entity?
                </h3>
                <ul class="space-y-2">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Tailored Indicators</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <span>Specific Timelines</span>
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        <span>Relevant Analytics</span>
                    </li>
                </ul>
                <div class="mt-4 text-sm text-base-content/70">
                    <p class="flex items-center">
                        <svg class="w-4 h-4 mr-2 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                        Need help? Contact support at atimothy@ecsahc.org
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var entitySelect = document.getElementById('entity_id');
        var submitButton = document.querySelector('button[type="submit"]');

        entitySelect.addEventListener('change', function() {
            if (this.value) {
                submitButton.classList.add('animate-pulse');
                setTimeout(() => {
                    submitButton.classList.remove('animate-pulse');
                }, 1000);
            }
        });
    });
</script>

<style>
    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .animate-pulse {
        animation: pulse 1s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
