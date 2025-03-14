@if(auth()->check())
    @php
        $user = auth()->user();
    @endphp

    @if($user->AccountRole === 'Admin')
        <div class="hidden panel-section" data-section="metrics">
            <h3 class="mb-4 text-xl font-semibold text-base-content">Metrics</h3>
            <div class="grid grid-cols-2 gap-3 mb-6">
                @if($user->UserType === 'MPA')
                    <a href="{{ route('MgtMpaTimelines') }}"
                        class="flex items-center p-3 transition-all duration-300 rounded-lg menu-item bg-base-100 hover:bg-error hover:text-error-content">
                        <span class="mr-3 text-xl menu-icon iconify text-error" data-icon="lucide:clock"></span>
                        <span class="text-sm font-medium text-base-content">MPA Timelines</span>
                    </a>
                @endif

                @if($user->UserType === 'ECSA-HC')
                    <a href="{{ route('MgtSO') }}"
                        class="flex items-center p-3 transition-all duration-300 rounded-lg menu-item bg-base-100 hover:bg-neutral hover:text-neutral-content">
                        <span class="mr-3 text-xl menu-icon iconify text-neutral" data-icon="lucide:target"></span>
                        <span class="text-sm font-medium text-base-content">ECSA‑HC Objectives</span>
                    </a>
                @endif
            </div>
            <div class="space-y-2">
                @if($user->UserType === 'ECSA-HC')
                    <a href="{{ route('MgtEcsaTimelines') }}"
                        class="flex items-center p-2 transition-all duration-300 rounded-md menu-item hover:bg-error hover:text-error-content">
                        <span class="mr-2 text-lg menu-icon iconify text-error" data-icon="lucide:clock"></span>
                        <span class="text-sm text-base-content">ECSA‑HC Timelines</span>
                    </a>
                    <a href="{{ route('MgtEcsaTimelinesStatus') }}"
                        class="flex items-center p-2 transition-all duration-300 rounded-md menu-item hover:bg-neutral hover:text-neutral-content">
                        <span class="mr-2 text-lg menu-icon iconify text-neutral" data-icon="lucide:activity"></span>
                        <span class="text-sm text-base-content">ECSA Timelines Status</span>
                    </a>
                    <a href="{{ route('SelectSo') }}"
                        class="flex items-center p-2 transition-all duration-300 rounded-md menu-item hover:bg-accent hover:text-accent-content">
                        <span class="mr-2 text-lg menu-icon iconify text-accent" data-icon="lucide:trending-up"></span>
                        <span class="text-sm text-base-content">ECSA‑HC Indicators</span>
                    </a>
                @endif

                @if($user->UserType === 'MPA')
                    <a href="{{ route('MgtMpaTimelinesStatus') }}"
                        class="flex items-center p-2 transition-all duration-300 rounded-md menu-item hover:bg-accent hover:text-accent-content">
                        <span class="mr-2 text-lg menu-icon iconify text-accent" data-icon="lucide:activity"></span>
                        <span class="text-sm text-base-content">MPA Timelines Status</span>
                    </a>
                    <a href="{{ route('mpaIndicators.SelectEntity') }}"
                        class="flex items-center p-2 transition-all duration-300 rounded-md menu-item hover:bg-secondary hover:text-secondary-content">
                        <span class="mr-2 text-lg menu-icon iconify text-secondary" data-icon="lucide:bar-chart-2"></span>
                        <span class="text-sm text-base-content">MPA CRF Indicators</span>
                    </a>
                    <a href="{{ route('mpaRRF.ShowRRFIndicators') }}"
                        class="flex items-center p-2 transition-all duration-300 rounded-md menu-item hover:bg-primary hover:text-primary-content">
                        <span class="mr-2 text-lg menu-icon iconify text-primary" data-icon="lucide:bar-chart"></span>
                        <span class="text-sm text-base-content">MPA RRF Indicators</span>
                    </a>
                @endif
            </div>
        </div>
    @endif
@endif
