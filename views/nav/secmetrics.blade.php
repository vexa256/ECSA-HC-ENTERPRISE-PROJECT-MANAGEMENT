<div class="panel-section hidden" data-section="metrics">
    <h3 class="text-xl font-semibold mb-4 text-base-content">Metrics</h3>
    <div class="grid grid-cols-2 gap-3 mb-6">
        <a href="{{ route('MgtMpaTimelines') }}"
            class="menu-item flex items-center p-3 bg-base-100 rounded-lg hover:bg-error hover:text-error-content transition-all duration-300">
            <span class="menu-icon iconify text-xl text-error mr-3" data-icon="lucide:clock"></span>
            <span class="text-sm font-medium text-base-content">MPA Timelines</span>
        </a>
        <a href="{{ route('MgtSO') }}"
            class="menu-item flex items-center p-3 bg-base-100 rounded-lg hover:bg-neutral hover:text-neutral-content transition-all duration-300">
            <span class="menu-icon iconify text-xl text-neutral mr-3" data-icon="lucide:target"></span>
            <span class="text-sm font-medium text-base-content">ECSA‑HC Objectives</span>
        </a>
    </div>
    <div class="space-y-2">
        <a href="{{ route('MgtEcsaTimelines') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-error hover:text-error-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-error mr-2" data-icon="lucide:clock"></span>
            <span class="text-sm text-base-content">ECSA‑HC Timelines</span>
        </a>
        <a href="{{ route('MgtEcsaTimelinesStatus') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-neutral hover:text-neutral-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-neutral mr-2" data-icon="lucide:activity"></span>
            <span class="text-sm text-base-content">ECSA Timelines Status</span>
        </a>
        <a href="{{ route('MgtMpaTimelinesStatus') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-accent hover:text-accent-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-accent mr-2" data-icon="lucide:activity"></span>
            <span class="text-sm text-base-content">MPA Timelines Status</span>
        </a>
        <a href="{{ route('mpaIndicators.SelectEntity') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-secondary hover:text-secondary-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-secondary mr-2" data-icon="lucide:bar-chart-2"></span>
            <span class="text-sm text-base-content">MPA CRF Indicators</span>
        </a>
        <a href="{{ route('mpaRRF.ShowRRFIndicators') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-primary hover:text-primary-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-primary mr-2" data-icon="lucide:bar-chart"></span>
            <span class="text-sm text-base-content">MPA RRF Indicators</span>
        </a>
        <a href="{{ route('SelectSo') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-accent hover:text-accent-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-accent mr-2" data-icon="lucide:trending-up"></span>
            <span class="text-sm text-base-content">ECSA‑HC Indicators</span>
        </a>
    </div>
</div>
