<div class="bg-gray-50 min-h-screen p-8">
    <div class="max-w-6xl mx-auto">
        <header class="mb-8">
            <h1 class="text-3xl font-semibold text-gray-800 mb-4">Select a Cluster</h1>
            <div class="flex justify-between items-center">
                <div class="relative">
                    <input type="text" id="cluster-search" placeholder="Search clusters"
                        class="input input-bordered w-64 pl-10" />
                    <i class="iconify absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                        data-icon="mdi:magnify"></i>
                </div>
                <div class="btn-group">
                    <button id="list-view-btn" class="btn btn-sm btn-active">
                        <i class="iconify mr-2" data-icon="mdi:view-list"></i>List
                    </button>
                    <button id="grid-view-btn" class="btn btn-sm">
                        <i class="iconify mr-2" data-icon="mdi:view-grid"></i>Grid
                    </button>
                </div>
            </div>
        </header>

        <main>
            <ul id="cluster-container" class="space-y-4">
                @if (Auth::user()->AccountRole === 'Admin')
                    <li class="cluster-item bg-white shadow-sm rounded-lg p-4" data-cluster="All clusters">
                        <div class="flex items-center">
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
                                <i class="iconify w-6 h-6 text-blue-600" data-icon="mdi:earth"></i>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-lg font-semibold text-gray-800">All Clusters</h2>
                                <p class="text-sm text-gray-600">View comprehensive data across all clusters</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="badge badge-primary">Admin</span>
                                <i class="iconify w-5 h-5 text-gray-400" data-icon="mdi:chevron-right"></i>
                            </div>
                        </div>
                    </li>
                @endif

                @foreach ($clusters as $cluster)
                    <li class="cluster-item bg-white shadow-sm rounded-lg p-4" data-cluster="{{ $cluster->ClusterID }}">
                        <div class="flex items-center">
                            @php
                                $colors = [
                                    ['bg-green-100', 'text-green-600'],
                                    ['bg-purple-100', 'text-purple-600'],
                                    ['bg-orange-100', 'text-orange-600'],
                                    ['bg-pink-100', 'text-pink-600'],
                                    ['bg-indigo-100', 'text-indigo-600'],
                                ];
                                $colorPair = $colors[array_rand($colors)];

                                $icons = ['mdi:chart-box', 'mdi:flask', 'mdi:heart-pulse', 'mdi:pill', 'mdi:dna'];
                                $icon = $icons[array_rand($icons)];
                            @endphp
                            <div
                                class="w-12 h-12 rounded-full {{ $colorPair[0] }} flex items-center justify-center mr-4">
                                <i class="iconify w-6 h-6 {{ $colorPair[1] }}" data-icon="{{ $icon }}"></i>
                            </div>
                            <div class="flex-1">
                                <h2 class="text-lg font-semibold text-gray-800">{{ $cluster->Cluster_Name }}</h2>
                                {{-- <p class="text-sm text-gray-600">{{ Str::limit($cluster->Description, 100) }}</p> --}}
                            </div>
                            <div class="flex items-center space-x-2">
                                {{-- <span class="text-sm text-gray-500">ID: {{ $cluster->ClusterID }}</span> --}}
                                <span class="badge badge-success gap-1">
                                    <i class="iconify w-3 h-3" data-icon="mdi:check-circle"></i>
                                    {{ rand(5, 20) }} active
                                </span>
                                <i class="iconify w-5 h-5 text-gray-400" data-icon="mdi:chevron-right"></i>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </main>
    </div>
</div>

<form id="cluster-form" action="{{ route('select-year') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="cluster" id="selected-cluster">
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const clusterItems = document.querySelectorAll('.cluster-item');
        const clusterForm = document.getElementById('cluster-form');
        const selectedClusterInput = document.getElementById('selected-cluster');
        const clusterSearch = document.getElementById('cluster-search');
        const listViewBtn = document.getElementById('list-view-btn');
        const gridViewBtn = document.getElementById('grid-view-btn');
        const clusterContainer = document.getElementById('cluster-container');

        clusterItems.forEach(item => {
            item.addEventListener('click', function() {
                const clusterId = this.dataset.cluster;
                selectedClusterInput.value = clusterId;

                // Add selection animation
                this.classList.add('ring-2', 'ring-blue-500');
                this.querySelector('.iconify:last-child').classList.add('text-blue-500');

                // Submit form after brief delay for animation
                setTimeout(() => {
                    clusterForm.submit();
                }, 300);
            });
        });

        // Implement search functionality
        clusterSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            clusterItems.forEach(item => {
                const clusterName = item.querySelector('h2').textContent.toLowerCase();
                const clusterDescription = item.querySelector('p').textContent.toLowerCase();
                if (clusterName.includes(searchTerm) || clusterDescription.includes(
                        searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Implement view switching
        listViewBtn.addEventListener('click', () => switchView('list'));
        gridViewBtn.addEventListener('click', () => switchView('grid'));

        function switchView(view) {
            if (view === 'grid') {
                clusterContainer.classList.remove('space-y-4');
                clusterContainer.classList.add('grid', 'grid-cols-2', 'gap-4');
                gridViewBtn.classList.add('btn-active');
                listViewBtn.classList.remove('btn-active');
            } else {
                clusterContainer.classList.add('space-y-4');
                clusterContainer.classList.remove('grid', 'grid-cols-2', 'gap-4');
                listViewBtn.classList.add('btn-active');
                gridViewBtn.classList.remove('btn-active');
            }
        }
    });
</script>

<style>
    .cluster-item {
        @apply transition-all duration-300 ease-in-out;
    }

    .cluster-item:hover {
        @apply shadow-md;
    }

    /* Grid view styles */
    .grid .cluster-item>div {
        @apply flex-col items-start;
    }

    .grid .cluster-item .w-12 {
        @apply mb-4;
    }

    .grid .cluster-item .flex-1 {
        @apply mb-4 w-full;
    }

    .grid .cluster-item .flex.items-center.space-x-2 {
        @apply w-full justify-between;
    }
</style>
