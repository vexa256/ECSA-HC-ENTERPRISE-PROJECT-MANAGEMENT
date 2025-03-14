<div class="panel-section hidden" data-section="org">
    <h3 class="text-xl font-semibold mb-4 text-base-content">Data Entry</h3>
    <div class="grid grid-cols-2 gap-3 mb-6">
        <a href="{{ route('MgtEntities') }}"
            class="menu-item flex items-center p-3 bg-base-100 rounded-lg hover:bg-accent hover:text-accent-content transition-all duration-300">
            <span class="menu-icon iconify text-xl text-accent mr-3" data-icon="lucide:building"></span>
            <span class="text-sm font-medium text-base-content">MPA Entities</span>
        </a>
        <a href="{{ route('MgtMpaUsers') }}"
            class="menu-item flex items-center p-3 bg-base-100 rounded-lg hover:bg-secondary hover:text-secondary-content transition-all duration-300">
            <span class="menu-icon iconify text-xl text-secondary mr-3" data-icon="lucide:users"></span>
            <span class="text-sm font-medium text-base-content">MPA Users</span>
        </a>
    </div>
    <div class="space-y-2">
        <a href="{{ route('MgtClusters') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-accent hover:text-accent-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-accent mr-2" data-icon="lucide:grid"></span>
            <span class="text-sm text-base-content">ECSA‑HC Clusters</span>
        </a>
        <a href="{{ route('MgtEcsaUsers') }}"
            class="menu-item flex items-center p-2 rounded-md hover:bg-secondary hover:text-secondary-content transition-all duration-300">
            <span class="menu-icon iconify text-lg text-secondary mr-2" data-icon="lucide:users"></span>
            <span class="text-sm text-base-content">ECSA‑HC Users</span>
        </a>
    </div>
</div>
