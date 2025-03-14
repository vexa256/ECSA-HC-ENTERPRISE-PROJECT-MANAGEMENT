{{-- resources/views/ClusterTargets/cluster-select.blade.php --}}
<!-- Required scripts -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

<div class="premium-container" x-data="{
    searchQuery: '',
    clusters: @js($clusters),
    filteredClusters: @js($clusters),
    activeCluster: null,
    filterClusters() {
        this.filteredClusters = this.clusters.filter(cluster =>
            cluster.Cluster_Name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
            cluster.Description.toLowerCase().includes(this.searchQuery.toLowerCase())
        );
    }
}" x-init="$nextTick(() => {
    document.querySelectorAll('.gradient-bg').forEach(el => {
        el.classList.add('gradient-animate');
    });
})">

    <!-- Premium header with animated gradient -->
    <div class="premium-header">
        <div class="gradient-bg"></div>
        <div class="premium-header-content">
            <div class="flex items-center">
                <iconify-icon icon="fluent:cube-32-regular" class="text-3xl mr-3 text-white"></iconify-icon>
                <h1 class="text-2xl font-medium text-white">Cluster Target Management</h1>
            </div>

            @if ($hasInvalidClusters)
                <div class="premium-badge" x-data="{ show: true }" x-show="show">
                    <button @click="show = false" class="flex items-center space-x-2 group">
                        <div class="badge-icon">
                            <iconify-icon icon="fluent:warning-16-regular" class="text-lg"></iconify-icon>
                        </div>
                        <span class="text-sm font-medium text-amber-100 group-hover:text-white transition-colors">Configuration issues detected</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Main content area -->
    <div class="premium-content">
        <!-- Search and filters section -->
        <div class="premium-search-section">
            <div class="premium-search-container">
                <iconify-icon icon="fluent:search-24-regular" class="text-base-content/50"></iconify-icon>
                <input
                    type="text"
                    placeholder="Search clusters by name or description..."
                    class="premium-search-input"
                    x-model="searchQuery"
                    x-on:input="filterClusters()"
                    autocomplete="off"
                >
                <button
                    x-show="searchQuery.length > 0"
                    x-on:click="searchQuery = ''; filterClusters()"
                    class="premium-clear-button"
                >
                    <iconify-icon icon="fluent:dismiss-circle-16-regular" class="text-base-content/50"></iconify-icon>
                </button>
            </div>

            <div class="premium-filters">
                <button class="premium-filter-btn active">
                    <span>All Clusters</span>
                </button>
                {{-- <button class="premium-filter-btn">
                    <span>Active</span>
                </button>
                <button class="premium-filter-btn">
                    <span>Archived</span>
                </button> --}}
            </div>
        </div>

        <!-- Warning message (if needed) -->
        @if ($hasInvalidClusters)
            <div class="premium-alert" x-data="{ show: true }" x-show="show" x-transition>
                <div class="flex items-center">
                    <iconify-icon icon="fluent:warning-16-filled" class="text-xl mr-3 text-amber-500"></iconify-icon>
                    <div>
                        <h4 class="font-medium text-gray-900">Configuration Warning</h4>
                        <p class="text-sm text-gray-600 mt-0.5">Some clusters have invalid configuration settings that need attention.</p>
                    </div>
                </div>
                <button x-on:click="show = false" class="premium-alert-close">
                    <iconify-icon icon="fluent:dismiss-16-regular" class="text-gray-400 hover:text-gray-600"></iconify-icon>
                </button>
            </div>
        @endif

        <!-- Empty state -->
        <div class="premium-empty-state" x-show="filteredClusters.length === 0" x-transition>
            <div class="empty-state-icon">
                <iconify-icon icon="fluent:search-24-regular" class="text-4xl text-gray-300"></iconify-icon>
            </div>
            <h3 class="text-xl font-medium text-gray-700 mt-4">No clusters found</h3>
            <p class="text-gray-500 mt-2 max-w-md text-center">We couldn't find any clusters matching your search criteria. Try adjusting your search or filters.</p>
        </div>

        <!-- Clusters grid -->
        <div class="premium-grid" x-show="filteredClusters.length > 0">
            <template x-for="(cluster, index) in filteredClusters" :key="cluster.ClusterID">
                <div class="premium-card"
                     :class="{'premium-card-active': activeCluster === cluster.ClusterID}"
                     @mouseenter="activeCluster = cluster.ClusterID"
                     @mouseleave="activeCluster = null"
                     :style="`animation-delay: ${index * 0.05}s`">
                    <div class="premium-card-gradient gradient-bg"></div>
                    <div class="premium-card-content">
                        <div class="premium-card-icon">
                            <iconify-icon icon="fluent:cube-24-regular" class="text-2xl"></iconify-icon>
                        </div>
                        <h3 class="premium-card-title" x-text="cluster.Cluster_Name"></h3>
                        <p class="premium-card-description" x-text="cluster.Description"></p>

                        <form method="GET" :action="'{{ route('targets.setup') }}'">
                            <input type="hidden" name="ClusterID" :value="cluster.ClusterID">
                            <button type="submit" class="premium-card-button">
                                <span>Manage Targets</span>
                                <iconify-icon icon="fluent:arrow-right-16-filled" class="ml-2 text-lg premium-card-button-icon"></iconify-icon>
                            </button>
                        </form>
                    </div>
                </div>
            </template>
        </div>
    </div>

<style>
    /* Premium Web App Styling */
    .premium-container {
        background-color: #f8fafc;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    /* Header styling */
    .premium-header {
        position: relative;
        padding: 2rem;
        overflow: hidden;
        height: 140px;
        display: flex;
        align-items: center;
    }

    .gradient-bg {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #0369a1, #0284c7, #0ea5e9, #38bdf8);
        background-size: 400% 400%;
        opacity: 0;
        transition: opacity 0.5s ease;
    }

    .gradient-animate {
        opacity: 1;
        animation: gradientAnimation 15s ease infinite;
    }

    .premium-header-content {
        position: relative;
        z-index: 10;
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .premium-badge {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        border-radius: 50px;
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: all 0.3s ease;
    }

    .premium-badge:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .badge-icon {
        background: rgba(251, 191, 36, 0.3);
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fbbf24;
        margin-right: 8px;
    }

    /* Content area styling */
    .premium-content {
        padding: 2rem;
    }

    /* Search section */
    .premium-search-section {
        margin-bottom: 2rem;
    }

    .premium-search-container {
        background-color: white;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.05);
        margin-bottom: 1rem;
        transition: all 0.2s ease;
    }

    .premium-search-container:focus-within {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        border-color: rgba(14, 165, 233, 0.3);
    }

    .premium-search-input {
        background: transparent;
        border: none;
        flex: 1;
        margin: 0 0.75rem;
        font-size: 0.95rem;
        color: #334155;
        outline: none;
    }

    .premium-clear-button {
        background: transparent;
        border: none;
        padding: 0;
    }

    .premium-filters {
        display: flex;
        gap: 0.75rem;
        margin-top: 1rem;
    }

    .premium-filter-btn {
        background-color: white;
        border: 1px solid rgba(0, 0, 0, 0.05);
        border-radius: 8px;
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        color: #64748b;
        transition: all 0.2s ease;
    }

    .premium-filter-btn:hover {
        background-color: #f8fafc;
        color: #334155;
    }

    .premium-filter-btn.active {
        background-color: #0ea5e9;
        color: white;
        border-color: #0ea5e9;
    }

    /* Alert styling */
    .premium-alert {
        background-color: #fff9ed;
        border: 1px solid #fef3c7;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .premium-alert-close {
        background: transparent;
        border: none;
        padding: 0.25rem;
        margin-left: 1rem;
    }

    /* Empty state */
    .premium-empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 2rem;
        text-align: center;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background-color: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Grid layout */
    .premium-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    /* Card styling */
    .premium-card {
        position: relative;
        background-color: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        height: 100%;
        opacity: 0;
        transform: translateY(10px);
        animation: cardAppear 0.5s ease forwards;
    }

    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 30px rgba(0, 0, 0, 0.1);
    }

    .premium-card-active {
        transform: translateY(-5px);
        box-shadow: 0 20px 30px rgba(0, 0, 0, 0.1);
    }

    .premium-card-gradient {
        position: absolute;
        top: 0;
        left: 0;
        height: 8px;
        width: 100%;
        background: linear-gradient(90deg, #0ea5e9, #38bdf8);
        background-size: 200% 200%;
        transition: height 0.3s ease;
    }

    .premium-card:hover .premium-card-gradient {
        height: 100%;
        opacity: 0.05;
    }

    .premium-card-content {
        padding: 1.5rem;
        position: relative;
        z-index: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .premium-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background-color: #f0f9ff;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0ea5e9;
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .premium-card:hover .premium-card-icon {
        background-color: #0ea5e9;
        color: white;
        transform: scale(1.05);
    }

    .premium-card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .premium-card-description {
        font-size: 0.95rem;
        color: #64748b;
        line-height: 1.5;
        margin-bottom: 1.5rem;
        flex-grow: 1;
    }

    .premium-card-button {
        background-color: #f8fafc;
        color: #0ea5e9;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 0.75rem 1.25rem;
        font-size: 0.95rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        width: 100%;
    }

    .premium-card-button:hover {
        background-color: #0ea5e9;
        color: white;
        border-color: #0ea5e9;
    }

    .premium-card-button-icon {
        transition: transform 0.3s ease;
    }

    .premium-card-button:hover .premium-card-button-icon {
        transform: translateX(4px);
    }

    /* Animations */
    @keyframes gradientAnimation {
        0% {
            background-position: 0% 50%;
        }
        50% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0% 50%;
        }
    }

    @keyframes cardAppear {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .premium-header {
            height: auto;
            padding: 1.5rem;
        }

        .premium-header-content {
            flex-direction: column;
            align-items: flex-start;
        }

        .premium-badge {
            margin-top: 1rem;
        }

        .premium-content {
            padding: 1.5rem;
        }

        .premium-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
</div>
