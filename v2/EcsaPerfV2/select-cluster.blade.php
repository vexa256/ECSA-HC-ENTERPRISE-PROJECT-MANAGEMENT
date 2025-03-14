<style>
    /* iOS-style card */
    .ios-card {
        background-color: #ffffff;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    /* iOS-style header */
    .ios-header {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    /* Search input */
    .ios-search {
        display: flex;
        align-items: center;
        background-color: #e5e5ea;
        border-radius: 10px;
        padding: 0.5rem 1rem;
        margin-bottom: 1.5rem;
    }

    .ios-search-input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 0.5rem;
        font-size: 1rem;
        outline: none;
        color: #000000;
    }

    .ios-search-input::placeholder {
        color: #8e8e93;
    }

    /* Grid container */
    .ios-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    @media (min-width: 480px) {
        .ios-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (min-width: 768px) {
        .ios-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (min-width: 1024px) {
        .ios-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }

    @media (min-width: 1280px) {
        .ios-grid {
            grid-template-columns: repeat(6, 1fr);
        }
    }

    /* Cluster grid item */
    .ios-grid-item {
        background-color: #ffffff;
        border-radius: 10px;
        padding: 0.75rem;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    /* Premium gradient borders and shadows */
    .ios-grid-item::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 10px;
        padding: 1px;
        background: linear-gradient(to bottom right, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.2));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }

    /* iOS gradient variations */
    .ios-gradient-blue {
        box-shadow: 0 4px 15px rgba(0, 122, 255, 0.15);
    }

    .ios-gradient-blue::before {
        background: linear-gradient(135deg, #007aff, #5ac8fa);
    }

    .ios-gradient-green {
        box-shadow: 0 4px 15px rgba(52, 199, 89, 0.15);
    }

    .ios-gradient-green::before {
        background: linear-gradient(135deg, #34c759, #30d158);
    }

    .ios-gradient-purple {
        box-shadow: 0 4px 15px rgba(175, 82, 222, 0.15);
    }

    .ios-gradient-purple::before {
        background: linear-gradient(135deg, #af52de, #bf5af2);
    }

    .ios-gradient-orange {
        box-shadow: 0 4px 15px rgba(255, 149, 0, 0.15);
    }

    .ios-gradient-orange::before {
        background: linear-gradient(135deg, #ff9500, #ff9f0a);
    }

    .ios-gradient-pink {
        box-shadow: 0 4px 15px rgba(255, 45, 85, 0.15);
    }

    .ios-gradient-pink::before {
        background: linear-gradient(135deg, #ff2d55, #ff375f);
    }

    .ios-gradient-teal {
        box-shadow: 0 4px 15px rgba(90, 200, 250, 0.15);
    }

    .ios-gradient-teal::before {
        background: linear-gradient(135deg, #5ac8fa, #64d2ff);
    }

    .ios-grid-item:active {
        transform: scale(0.98);
    }

    .ios-grid-item.selected {
        background-color: rgba(0, 122, 255, 0.05);
    }

    .ios-grid-item.selected::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 0;
        height: 0;
        border-style: solid;
        border-width: 0 20px 20px 0;
        border-color: transparent #007aff transparent transparent;
    }

    /* Radio button styling */
    .ios-radio {
        appearance: none;
        position: absolute;
        opacity: 0;
    }

    /* iOS-style button */
    .ios-button {
        background-color: #007aff;
        color: white;
        border-radius: 6px;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.2s ease;
        border: none;
        outline: none;
        cursor: pointer;
        text-align: center;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .ios-button:active {
        opacity: 0.8;
        transform: scale(0.98);
    }

    /* Info card */
    .ios-info-card {
        background-color: rgba(0, 122, 255, 0.1);
        border-radius: 10px;
        padding: 1rem;
        margin-top: 1.5rem;
    }

    .ios-info-title {
        color: #0056b3;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .ios-info-text {
        color: #0069d9;
        font-size: 0.875rem;
    }

    /* Pagination controls */
    .ios-pagination {
        display: flex;
        justify-content: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .ios-pagination-button {
        background-color: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
        margin: 0.25rem;
        font-size: 0.875rem;
        color: #007aff;
        transition: all 0.2s ease;
    }

    .ios-pagination-button:active {
        background-color: #e5e5ea;
    }

    .ios-pagination-button.active {
        background-color: #007aff;
        color: #ffffff;
        border-color: #007aff;
    }

    .ios-pagination-button.disabled {
        color: #8e8e93;
        pointer-events: none;
    }

    /* Cluster name and description */
    .ios-cluster-name {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
        line-height: 1.2;
        display: flex;
        align-items: center;
    }

    .ios-cluster-desc {
        font-size: 0.75rem;
        color: #8e8e93;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        flex-grow: 1;
    }

    /* Cluster icon */
    .ios-cluster-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 6px;
        margin-right: 8px;
        flex-shrink: 0;
        color: white;
        font-size: 16px;
    }

    .ios-icon-bg-blue {
        background-color: #007aff;
    }

    .ios-icon-bg-green {
        background-color: #34c759;
    }

    .ios-icon-bg-purple {
        background-color: #af52de;
    }

    .ios-icon-bg-orange {
        background-color: #ff9500;
    }

    .ios-icon-bg-pink {
        background-color: #ff2d55;
    }

    .ios-icon-bg-teal {
        background-color: #5ac8fa;
    }
</style>

<div class="ios-card p-4 mb-6">
    <h2 class="ios-header">Select Cluster</h2>
    <p class="text-gray-600 mb-4">
        Please select a cluster to view its performance indicators and metrics.
    </p>
</div>

@if(isset($Clusters) && count($Clusters) > 0)
<form action="{{ route('performance.cluster.process') }}" method="POST" id="clusterForm">
    @csrf

    <!-- Search input (hidden by default) -->
    <div class="ios-search" style="display: none">
        <i class="iconify text-gray-500 mr-2" data-icon="heroicons-solid:search"></i>
        <input type="text" id="clusterSearch" class="ios-search-input" placeholder="Search clusters...">
        <button type="button" id="clearSearch" class="hidden">
            <i class="iconify text-gray-500" data-icon="heroicons-solid:x-circle"></i>
        </button>
    </div>

    @php
        $itemsPerPage = 18; // 6x3 grid on large screens
        $filteredClusters = $Clusters->filter(function($cluster) {
            return $cluster->Cluster_Name !== 'All clusters/projects' && $cluster->ClusterID !== 'All clusters/projects';
        });
        $totalClusters = count($filteredClusters);
        $totalPages = ceil($totalClusters / $itemsPerPage);
        $currentPage = request()->query('page', 1);
        $currentPage = max(1, min($currentPage, $totalPages));
        $startIndex = ($currentPage - 1) * $itemsPerPage;
        $displayClusters = $filteredClusters->slice($startIndex, $itemsPerPage);

        // iOS-style icons for clusters
        $icons = [
            'heroicons-solid:chart-pie',
            'heroicons-solid:chart-bar',
            'heroicons-solid:chart-square-bar',
            'heroicons-solid:clipboard-list',
            'heroicons-solid:clipboard-check',
            'heroicons-solid:document-text',
            'heroicons-solid:document-report',
            'heroicons-solid:document-search',
            'heroicons-solid:collection',
            'heroicons-solid:template',
            'heroicons-solid:view-grid',
            'heroicons-solid:view-boards',
            'heroicons-solid:puzzle',
            'heroicons-solid:cube',
            'heroicons-solid:cube-transparent',
            'heroicons-solid:server',
            'heroicons-solid:terminal',
            'heroicons-solid:code',
            'heroicons-solid:chip',
            'heroicons-solid:database',
            'heroicons-solid:globe',
            'heroicons-solid:globe-alt',
            'heroicons-solid:desktop-computer',
            'heroicons-solid:device-mobile'
        ];

        // iOS gradient classes
        $gradients = [
            'ios-gradient-blue',
            'ios-gradient-green',
            'ios-gradient-purple',
            'ios-gradient-orange',
            'ios-gradient-pink',
            'ios-gradient-teal'
        ];

        // iOS icon background classes
        $iconBgs = [
            'ios-icon-bg-blue',
            'ios-icon-bg-green',
            'ios-icon-bg-purple',
            'ios-icon-bg-orange',
            'ios-icon-bg-pink',
            'ios-icon-bg-teal'
        ];
    @endphp

    <div class="ios-grid" id="clusterGrid">
        @foreach($displayClusters as $cluster)
            @php
                // Generate consistent icon and gradient for each cluster based on ID
                $iconIndex = crc32($cluster->ClusterID) % count($icons);
                $gradientIndex = crc32($cluster->ClusterID) % count($gradients);
                $iconBgIndex = crc32($cluster->ClusterID) % count($iconBgs);

                $icon = $icons[$iconIndex];
                $gradient = $gradients[$gradientIndex];
                $iconBg = $iconBgs[$iconBgIndex];
            @endphp

            <label class="ios-grid-item {{ $gradient }}" data-cluster-id="{{ $cluster->ClusterID }}" data-cluster-name="{{ strtolower($cluster->Cluster_Name) }}" data-cluster-desc="{{ strtolower($cluster->Description ?? '') }}">
                <input type="radio" name="cluster_id" value="{{ $cluster->ClusterID }}" class="ios-radio" required>
                <div class="ios-cluster-name">
                    <div class="ios-cluster-icon {{ $iconBg }}">
                        <i class="iconify" data-icon="{{ $icon }}"></i>
                    </div>
                    <span>{{ $cluster->Cluster_Name }}</span>
                </div>
                @if($cluster->Description)
                    <div class="ios-cluster-desc">{{ $cluster->Description }}</div>
                @endif
            </label>
        @endforeach
    </div>

    <!-- No results message (hidden by default) -->
    <div id="noResults" class="ios-card p-6 text-center hidden">
        <i class="iconify text-gray-400 text-4xl mb-2" data-icon="heroicons-solid:search-circle"></i>
        <h3 class="text-lg font-semibold mb-2">No Matching Clusters</h3>
        <p class="text-gray-600">
            No clusters match your search criteria. Try a different search term.
        </p>
    </div>

    <!-- Pagination controls if needed -->
    @if($totalPages > 1)
        <div class="ios-pagination" id="pagination">
            <a href="{{ route('performance.cluster.selection', ['page' => 1]) }}"
               class="ios-pagination-button {{ $currentPage == 1 ? 'disabled' : '' }}">
                <i class="iconify" data-icon="heroicons-solid:chevron-double-left"></i>
            </a>

            <a href="{{ route('performance.cluster.selection', ['page' => max(1, $currentPage - 1)]) }}"
               class="ios-pagination-button {{ $currentPage == 1 ? 'disabled' : '' }}">
                <i class="iconify" data-icon="heroicons-solid:chevron-left"></i>
            </a>

            @php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalPages, $startPage + 4);
                $startPage = max(1, $endPage - 4);
            @endphp

            @for($i = $startPage; $i <= $endPage; $i++)
                <a href="{{ route('performance.cluster.selection', ['page' => $i]) }}"
                   class="ios-pagination-button {{ $i == $currentPage ? 'active' : '' }}">
                    {{ $i }}
                </a>
            @endfor

            <a href="{{ route('performance.cluster.selection', ['page' => min($totalPages, $currentPage + 1)]) }}"
               class="ios-pagination-button {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                <i class="iconify" data-icon="heroicons-solid:chevron-right"></i>
            </a>

            <a href="{{ route('performance.cluster.selection', ['page' => $totalPages]) }}"
               class="ios-pagination-button {{ $currentPage == $totalPages ? 'disabled' : '' }}">
                <i class="iconify" data-icon="heroicons-solid:chevron-double-right"></i>
            </a>
        </div>
    @endif

    <button type="submit" class="ios-button">
        Continue
        <i class="iconify ml-1" data-icon="heroicons-solid:arrow-right"></i>
    </button>
</form>

@else
    <div class="ios-card p-6 text-center">
        <i class="iconify text-gray-400 text-6xl mb-4" data-icon="heroicons-solid:emoji-sad"></i>
        <h3 class="text-lg font-semibold mb-2">No Clusters Available</h3>
        <p class="text-gray-600">
            There are no clusters available in the system. Please contact the administrator.
        </p>
    </div>
@endif

<!-- iOS-style info card -->
<div class="ios-info-card" style="display: none">
    <div class="flex items-start">
        <i class="iconify text-blue-500 mr-3 text-xl flex-shrink-0" data-icon="heroicons-solid:information-circle"></i>
        <div>
            <h3 class="ios-info-title">What are Clusters?</h3>
            <p class="ios-info-text">
                Clusters are organizational units or projects within ECSA-HC. Each cluster has its own set of performance indicators and targets.
            </p>
        </div>
    </div>
</div>

<!-- Include Iconify library -->
<script src="https://code.iconify.design/2/2.2.1/iconify.min.js"></script>
<!-- JavaScript for iOS-like interactions and search functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get all necessary elements
        const clusterItems = document.querySelectorAll('.ios-grid-item');
        const radioInputs = document.querySelectorAll('.ios-radio');
        const clusterForm = document.getElementById('clusterForm');
        const searchInput = document.getElementById('clusterSearch');
        const clearButton = document.getElementById('clearSearch');
        const clusterGrid = document.getElementById('clusterGrid');
        const noResults = document.getElementById('noResults');
        const pagination = document.getElementById('pagination');

        // Function to handle cluster selection
        function handleClusterSelection(item) {
            // Remove selected class from all items
            clusterItems.forEach(i => i.classList.remove('selected'));

            // Add selected class to clicked item
            item.classList.add('selected');

            // Check the radio input
            const radio = item.querySelector('.ios-radio');
            if (radio) radio.checked = true;
        }

        // Add click event to all cluster items
        clusterItems.forEach(item => {
            item.addEventListener('click', function(e) {
                handleClusterSelection(this);

                // Prevent form submission on label click
                e.preventDefault();
            });
        });

        // Check if any radio is already checked (e.g., on page reload)
        radioInputs.forEach(radio => {
            if (radio.checked) {
                const item = radio.closest('.ios-grid-item');
                if (item) handleClusterSelection(item);
            }
        });

        // Add ripple effect to button
        const button = document.querySelector('.ios-button');
        if (button) {
            button.addEventListener('touchstart', function() {
                this.style.opacity = '0.8';
                this.style.transform = 'scale(0.98)';
            });

            button.addEventListener('touchend', function() {
                this.style.opacity = '1';
                this.style.transform = 'scale(1)';
            });
        }

        // Submit form when a cluster is double-clicked
        clusterItems.forEach(item => {
            item.addEventListener('dblclick', function() {
                if (clusterForm) clusterForm.submit();
            });
        });

        // Enhanced search functionality
        if (searchInput && clusterGrid) {
            // Initialize search state
            let searchActive = false;

            // Function to normalize text for searching (remove accents, lowercase, etc.)
            function normalizeText(text) {
                if (!text) return '';
                return text.toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '') // Remove accents
                    .replace(/[^\w\s]/g, ''); // Remove special characters
            }

            // Function to check if an item matches the search term
            function itemMatchesSearch(item, searchTerms) {
                if (!item) return false;

                const clusterName = normalizeText(item.getAttribute('data-cluster-name') || '');
                const clusterDesc = normalizeText(item.getAttribute('data-cluster-desc') || '');

                // Check if all search terms are found in either name or description
                return searchTerms.every(term =>
                    clusterName.includes(term) || clusterDesc.includes(term)
                );
            }

            // Function to perform the search
            function performSearch() {
                const searchValue = searchInput.value.trim();
                const searchTerms = normalizeText(searchValue).split(/\s+/).filter(Boolean);
                searchActive = searchTerms.length > 0;

                // Show/hide clear button
                if (searchActive) {
                    if (clearButton) clearButton.classList.remove('hidden');
                } else {
                    if (clearButton) clearButton.classList.add('hidden');
                }

                // Count visible items for no results message
                let visibleCount = 0;

                // Filter clusters
                clusterItems.forEach(item => {
                    if (!searchActive || itemMatchesSearch(item, searchTerms)) {
                        item.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        item.classList.add('hidden');
                    }
                });

                // Show/hide no results message
                if (visibleCount === 0) {
                    if (noResults) noResults.classList.remove('hidden');
                    if (pagination) pagination.classList.add('hidden');
                } else {
                    if (noResults) noResults.classList.add('hidden');
                    if (pagination) {
                        if (!searchActive) {
                            pagination.classList.remove('hidden');
                        } else {
                            pagination.classList.add('hidden');
                        }
                    }
                }

                // If there's only one visible item and Enter is pressed, select it
                if (visibleCount === 1 && event && event.key === 'Enter') {
                    const visibleItem = Array.from(clusterItems).find(item => !item.classList.contains('hidden'));
                    if (visibleItem) {
                        handleClusterSelection(visibleItem);
                        // Optional: submit the form
                        // if (clusterForm) clusterForm.submit();
                    }
                }
            }

            // Add input event listener with small delay to avoid performance issues during typing
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 100);
            });

            // Add keydown event for Enter key
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault(); // Prevent form submission
                    performSearch(); // Run search immediately
                }
            });

            // Clear search functionality
            if (clearButton) {
                clearButton.addEventListener('click', function() {
                    searchInput.value = '';
                    searchInput.focus();
                    performSearch();
                });
            }

            // Run search on page load (in case there's a value already)
            performSearch();
        }

        // Make sure pagination links preserve search query
        if (pagination) {
            const paginationLinks = pagination.querySelectorAll('a');
            paginationLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (searchInput && searchInput.value.trim()) {
                        e.preventDefault();
                        const url = new URL(this.href);
                        url.searchParams.set('search', searchInput.value.trim());
                        window.location.href = url.toString();
                    }
                });
            });
        }

        // Check URL for search parameter on page load
        function checkUrlForSearch() {
            if (searchInput) {
                const urlParams = new URLSearchParams(window.location.search);
                const searchParam = urlParams.get('search');
                if (searchParam) {
                    searchInput.value = searchParam;
                    // Trigger search after a short delay to ensure DOM is ready
                    setTimeout(() => {
                        if (typeof performSearch === 'function') {
                            performSearch();
                        }
                    }, 100);
                }
            }
        }

        // Run URL check on page load
        checkUrlForSearch();
    });
</script>

