<div class="panel-section" data-section="home">
    <h3 class="text-xl font-semibold mb-4 text-base-content">Home</h3>
    <div class="grid grid-cols-1 gap-3 mb-6">
        <a href="{{ url('./') }}"
           class="menu-item flex items-center p-3 bg-base-100 rounded-lg hover:bg-neutral-focus hover:text-neutral-content transition-all duration-300">
            <span class="menu-icon iconify text-xl text-neutral mr-3" data-icon="lucide:home"></span>
            <span class="text-sm font-medium text-base-content">Home</span>
        </a>
    </div>

    <div>
        <h4 class="text-xs font-semibold text-base-content opacity-70 uppercase mb-2">

            Quick Links

        </h4>
        <div class="space-y-2">
            {{-- MPA specific links --}}
            @if(auth()->user()->UserType === 'MPA')
                <a href="{{ route('crf.scoreboard') }}"
                   class="menu-item flex items-center p-2 rounded-md hover:bg-secondary hover:text-secondary-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-secondary mr-2" data-icon="lucide:pie-chart"></span>
                    <span class="text-sm text-base-content">MPA CRF Dashboard</span>
                </a>
                <a href="{{ route('rrf.scoreboard') }}"
                   class="menu-item flex items-center p-2 rounded-md hover:bg-success hover:text-success-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-success mr-2" data-icon="lucide:bar-chart"></span>
                    <span class="text-sm text-base-content">MPA RRF Dashboard</span>
                </a>
                <a href="{{ url('/entity/select') }}"
                   class="menu-item flex items-center p-2 rounded-md hover:bg-info hover:text-info-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-info mr-2" data-icon="lucide:file-text"></span>
                    <span class="text-sm text-base-content">MPA Report</span>
                </a>


                <a href="{{ route('mpa.reports.completeness.select_year') }}"
                class="menu-item flex items-center p-2 rounded-md hover:bg-primary hover:text-primary-content transition-all duration-300">
                <span class="menu-icon iconify text-lg text-primary mr-2" data-icon="lucide:check-circle"></span>
                <span class="text-sm text-base-content">MPA Reporting Completeness</span>
            </a>
            @endif

            {{-- ECSA-HC specific links --}}
            @if(auth()->user()->UserType === 'ECSA-HC')
                <a href="{{ route('targets.index') }}"
                   class="menu-item flex items-center p-2 rounded-md hover:bg-warning hover:text-warning-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-warning mr-2" data-icon="lucide:target"></span>
                    <span class="text-sm text-base-content">ECSA-HC Targets</span>
                </a>
                <a href="{{ route('Ecsa_SelectUser') }}"
                   class="menu-item flex items-center p-2 rounded-md hover:bg-info hover:text-info-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-info mr-2" data-icon="lucide:file-text"></span>
                    <span class="text-sm text-base-content">ECSA-HC Report</span>
                </a>
                <a href="{{ route('performance.cluster.selection') }}"
                   class="menu-item flex items-center p-2 rounded-md hover:bg-error hover:text-error-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-error mr-2" data-icon="lucide:trending-up"></span>
                    <span class="text-sm text-base-content">ECSA-HC IND performance</span>
                </a>
                <a href="{{ route('V2_ALL_performance.timeline.selection') }}"
                   class="menu-item flex items-center p-2 rounded-md hover:bg-primary hover:text-primary-content transition-all duration-300">
                    <span class="menu-icon iconify text-lg text-primary mr-2" data-icon="lucide:layout-dashboard"></span>
                    <span class="text-sm text-base-content">ECSA-HC Dashboard</span>
                </a>
            @endif
        </div>
    </div>

    {{--
        Other sections remain commented out if not needed.
        They can be refactored similarly if they require conditional rendering.
    --}}
</div>
