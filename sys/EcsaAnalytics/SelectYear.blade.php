@php
    // The backend supplies the collection of years.
    // For example, your controller might pass:
    // $years = collect(range(date('Y'), date('Y') - 50))->forPage($currentPage, $yearsPerPage);
@endphp

<div x-data="yearSelection()" class="year-selection">
    <h2 class="text-3xl font-semibold mb-4 text-center">Select a Year</h2>
    <p class="text-gray-600 mb-8 text-center">
        Cluster:
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                </path>
            </svg>
            {{ isset($selectedCluster)
                ? ($selectedCluster === 'All clusters'
                    ? 'All Clusters'
                    : $clusters->firstWhere('ClusterID', $selectedCluster)->Cluster_Name ?? 'Unknown Cluster')
                : 'All Clusters' }}
        </span>
    </p>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" x-ref="yearGrid">
        <template x-for="year in visibleYears" :key="year">
            <div class="year-card" :class="{ 'opacity-0': !isInViewport($el) }"
                x-intersect="if($el.classList.contains('opacity-0')) $el.classList.remove('opacity-0')"
                @click="selectYear(year)" tabindex="0" @keydown.enter="selectYear(year)" role="button"
                :aria-label="`Select year ${year}`">
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 p-6">
                    <h3 class="text-2xl font-semibold mb-2" x-text="year"></h3>
                    <p class="text-gray-600 mb-4">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                        Performance data for <span x-text="year"></span>
                    </p>
                    <div class="flex space-x-2">
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span x-text="`${Math.floor(Math.random() * 15) + 80}% Complete`"></span>
                        </span>
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <span x-text="`${Math.floor(Math.random() * 5) + 3} Reports`"></span>
                        </span>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <form id="year-form" action="{{ route('select-report') }}" method="POST" class="hidden" x-ref="yearForm">
        @csrf
        <input type="hidden" name="cluster" value="{{ $selectedCluster }}">
        <input type="hidden" name="year" x-ref="selectedYear">
    </form>
</div>

<script>
    function yearSelection() {
        return {
            // Only the backend-supplied years are used
            visibleYears: @json($years),

            selectYear(year) {
                this.$refs.selectedYear.value = year;
                this.$refs.yearForm.submit();
                if ('vibrate' in navigator) {
                    navigator.vibrate(50);
                }
            },

            isInViewport(element) {
                const rect = element.getBoundingClientRect();
                return (
                    rect.top >= 0 &&
                    rect.left >= 0 &&
                    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
                );
            }
        }
    }
</script>

<style>
    .year-selection {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    }

    .year-card {
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    .year-card:hover {
        transform: translateY(-2px);
    }

    .year-card:active {
        transform: translateY(0);
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

    .opacity-0 {
        opacity: 0;
        animation: fadeIn 0.5s ease-out forwards;
    }

    .year-card:focus-visible {
        outline: 2px solid #007AFF;
        outline-offset: 2px;
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border-width: 0;
    }
</style>
