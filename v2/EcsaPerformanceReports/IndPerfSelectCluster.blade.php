<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <!-- Header Section -->
    <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700">
        <h2 class="text-xl font-medium text-gray-800 dark:text-white">Performance vs. Target Report</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Select a cluster to begin your analysis</p>
    </div>

    <!-- Error Alert -->
    @if(isset($error))
    <div class="mx-6 mt-4 alert alert-error shadow-sm text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>{{ $error }}</span>
    </div>
    @endif

    <form action="{{ route('indicator.select.timeline') }}" method="GET">
        <!-- Cluster Selection -->
        <div class="px-6 py-4">
            @if(count($clusters) > 0)
                <div class="space-y-2">
                    @foreach($clusters as $cluster)
                        <label for="cluster_{{ $cluster->ClusterID }}" class="flex items-center p-3 rounded-xl border border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors cursor-pointer">
                            <input type="radio" name="cluster_id" id="cluster_{{ $cluster->ClusterID }}"
                                value="{{ $cluster->ClusterID }}" class="radio radio-sm radio-primary" required>
                            <div class="ml-3">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $cluster->Cluster_Name }}</div>
                                @if($cluster->Description)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">{{ $cluster->Description }}</div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-5 w-5" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    <span>No clusters are available for selection.</span>
                </div>
            @endif
        </div>

        <!-- Action Footer -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700 flex justify-end">
            <button type="submit" class="btn btn-primary btn-sm px-4 gap-2">
                Continue
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </form>

    <!-- Debug Information (Development Only) -->
    @if(isset($errorDetails) && app()->environment('local', 'development'))
    <div class="m-6 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg text-xs text-red-800 dark:text-red-300">
        <p class="font-medium">Error Details (Debug Only):</p>
        <pre class="mt-1 whitespace-pre-wrap">{{ $errorDetails }}</pre>
    </div>
    @endif
</div>

