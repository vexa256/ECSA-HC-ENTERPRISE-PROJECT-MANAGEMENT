<div class="panel-section hidden" data-section="reports">
    <h3 class="text-xl font-semibold mb-4 text-base-content">Reports</h3>
    <div class="grid grid-cols-2 gap-3 mb-6">
        <a href="{{ route('Ecsa_SelectUser') }}"
            class="menu-item flex items-center p-3 bg-base-100 rounded-lg hover:bg-accent hover:text-accent-content transition-all duration-300">
            <span class="menu-icon iconify text-xl text-accent mr-3" data-icon="lucide:file-text"></span>
            <span class="text-sm font-medium text-base-content">ECSA‑HC Reports</span>
        </a>
        <a href="{{ route('Reportselectcluster') }}"
            class="menu-item flex items-center p-3 bg-base-100 rounded-lg hover:bg-secondary hover:text-secondary-content transition-all duration-300">
            <span class="menu-icon iconify text-xl text-secondary mr-3" data-icon="lucide:trending-up"></span>
            <span class="text-sm font-medium text-base-content">ECSA‑HC Perf.</span>
        </a>
    </div>
    <div class="space-y-2">
        <a href="{{ route('entity.select') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-accent hover:text-accent-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-accent mr-2" data-icon="lucide:file-text"></span>
            <span class="text-sm text-base-content">MPA Report on Indicators</span>
        </a>
        <a href="{{ route('Ecsa_SO_selectYear') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-secondary hover:text-secondary-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-secondary mr-2" data-icon="lucide:trending-up"></span>
            <span class="text-sm text-base-content">ECSA‑HC SO Perf.</span>
        </a>
        <a href="{{ route('Ecsa_CP_selectYear') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-neutral hover:text-neutral-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-neutral mr-2" data-icon="lucide:bar-chart-2"></span>
            <span class="text-sm text-base-content">ECSA‑HC Cluster Perf.</span>
        </a>
        <a href="{{ route('mpa.reports.completeness.select_year') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-primary hover:text-primary-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-primary mr-2" data-icon="lucide:check-circle"></span>
            <span class="text-sm text-base-content">MPA Reporting Completeness</span>
        </a>
        <a href="{{ route('rrf.report.selectReport') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-error hover:text-error-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-error mr-2" data-icon="lucide:activity"></span>
            <span class="text-sm text-base-content">MPA RRF Performance</span>
        </a>
    </div>
</div>
