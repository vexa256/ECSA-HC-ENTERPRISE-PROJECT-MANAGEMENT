 <!-- Navigation -->
 <nav class="flex-1 flex flex-col justify-between py-4">
    <!-- Top Icons -->
    <div class="space-y-4 px-2">
        <a href="{{ url('./') }}" data-section="home"
            class="sidebar-item w-full flex flex-col items-center justify-center rounded-lg hover:bg-base-200 transition-colors group p-2">
            <span class="iconify w-6 h-6 text-base-content opacity-70 group-hover:opacity-100 transition-colors"
                data-icon="lucide:home"></span>
            <span class="text-xs mt-1 text-base-content opacity-70 group-hover:opacity-100">Home</span>
        </a>
        <a href="#" data-section="org"
            class="sidebar-item w-full flex flex-col items-center justify-center rounded-lg hover:bg-base-200 transition-colors group p-2">
            <span class="iconify w-6 h-6 text-base-content opacity-70 group-hover:opacity-100 transition-colors"
                data-icon="lucide:briefcase"></span>
            <span class="text-xs mt-1 text-base-content opacity-70 group-hover:opacity-100">Data Entry</span>
        </a>
        <a href="#" data-section="metrics"
            class="sidebar-item w-full flex flex-col items-center justify-center rounded-lg hover:bg-base-200 transition-colors group p-2">
            <span class="iconify w-6 h-6 text-base-content opacity-70 group-hover:opacity-100 transition-colors"
                data-icon="lucide:target"></span>
            <span class="text-xs mt-1 text-base-content opacity-70 group-hover:opacity-100">Metrics</span>
        </a>
        {{-- <a href="#" data-section="reports"
            class="sidebar-item w-full flex flex-col items-center justify-center rounded-lg hover:bg-base-200 transition-colors group p-2">
            <span class="iconify w-6 h-6 text-base-content opacity-70 group-hover:opacity-100 transition-colors"
                data-icon="lucide:file-text"></span>
            <span class="text-xs mt-1 text-base-content opacity-70 group-hover:opacity-100">Reports</span>
        </a> --}}
        {{-- <a href="#" data-section="hr"
            class="sidebar-item w-full flex flex-col items-center justify-center rounded-lg hover:bg-base-200 transition-colors group p-2">
            <span class="iconify w-6 h-6 text-base-content opacity-70 group-hover:opacity-100 transition-colors"
                data-icon="lucide:users"></span>
            <span class="text-xs mt-1 text-base-content opacity-70 group-hover:opacity-100">HR</span>
        </a> --}}
    </div>

    <!-- Bottom Icons -->
    {{-- <div class="space-y-4 px-2">
        <button
            class="w-full flex flex-col items-center justify-center rounded-lg hover:bg-base-200 transition-colors group p-2">
            <span class="iconify w-6 h-6 text-base-content opacity-70 group-hover:opacity-100 transition-colors"
                data-icon="lucide:settings"></span>
            <span class="text-xs mt-1 text-base-content opacity-70 group-hover:opacity-100">Settings</span>
        </button>
        <button
            class="w-full flex flex-col items-center justify-center rounded-lg hover:bg-base-200 transition-colors group p-2">
            <span class="iconify w-6 h-6 text-base-content opacity-70 group-hover:opacity-100 transition-colors"
                data-icon="lucide:help-circle"></span>
            <span class="text-xs mt-1 text-base-content opacity-70 group-hover:opacity-100">Help</span>
        </button>
    </div> --}}
</nav>
