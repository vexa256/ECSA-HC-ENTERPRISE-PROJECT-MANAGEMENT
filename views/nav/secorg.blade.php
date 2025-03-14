@if(auth()->check())
    @php
        $user = auth()->user();
    @endphp

    @if($user->AccountRole === 'Admin')
        <div class="hidden panel-section" data-section="org">
            <h3 class="mb-4 text-xl font-semibold text-base-content">Data Entry</h3>
            <div class="grid grid-cols-2 gap-3 mb-6">
                @if($user->UserType === 'MPA')
                    <a href="{{ route('MgtEntities') }}"
                        class="flex items-center p-3 transition-all duration-300 rounded-lg menu-item bg-base-100 hover:bg-accent hover:text-accent-content">
                        <span class="mr-3 text-xl menu-icon iconify text-accent" data-icon="lucide:building"></span>
                        <span class="text-sm font-medium text-base-content">MPA Entities</span>
                    </a>
                    <a href="{{ route('MgtMpaUsers') }}"
                        class="flex items-center p-3 transition-all duration-300 rounded-lg menu-item bg-base-100 hover:bg-secondary hover:text-secondary-content">
                        <span class="mr-3 text-xl menu-icon iconify text-secondary" data-icon="lucide:users"></span>
                        <span class="text-sm font-medium text-base-content">MPA Users</span>
                    </a>
                @endif
            </div>
            <div class="space-y-2">
                @if($user->UserType === 'ECSA-HC')
                    <a href="{{ route('MgtClusters') }}"
                        class="flex items-center p-2 transition-all duration-300 rounded-md menu-item hover:bg-accent hover:text-accent-content">
                        <span class="mr-2 text-lg menu-icon iconify text-accent" data-icon="lucide:grid"></span>
                        <span class="text-sm text-base-content">ECSA‑HC Clusters</span>
                    </a>
                    <a href="{{ route('MgtEcsaUsers') }}"
                        class="flex items-center p-2 transition-all duration-300 rounded-md menu-item hover:bg-secondary hover:text-secondary-content">
                        <span class="mr-2 text-lg menu-icon iconify text-secondary" data-icon="lucide:users"></span>
                        <span class="text-sm text-base-content">ECSA‑HC Users</span>
                    </a>
                @endif
            </div>
        </div>
    @endif
@endif
