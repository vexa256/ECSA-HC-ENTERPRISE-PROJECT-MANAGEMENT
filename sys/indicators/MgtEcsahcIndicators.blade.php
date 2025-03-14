@php
    $isAdmin = auth()->check() && auth()->user()->AccountRole === 'Admin';
@endphp

<!-- Header Section -->
<div class="w-full p-4">
    <div class="flex flex-col gap-4 mb-2 md:flex-row md:justify-between">
        <!-- Premium Search Component -->
        <div class="relative w-full md:w-96">
            <div class="w-full form-control">
                <div class="w-full input-group">
                    <input type="text" id="premium-search" placeholder="Search indicators..." class="w-full input input-bordered focus:ring-2 focus:ring-primary/50" />
                    {{-- <button class="btn btn-square bg-primary hover:bg-primary-focus border-primary">
                        <span class="iconify" data-icon="mdi:magnify"></span>
                    </button> --}}
                </div>
            </div>
            <div id="search-filters" class="absolute left-0 right-0 z-10 hidden p-3 rounded-b-lg shadow-lg top-full bg-base-100">
                <div class="flex flex-wrap gap-2 mb-2">
                    <span class="cursor-pointer badge badge-primary search-filter" data-column="all">All</span>
                    <span class="cursor-pointer badge badge-outline search-filter" data-column="number">Number</span>
                    <span class="cursor-pointer badge badge-outline search-filter" data-column="name">Name</span>
                    <span class="cursor-pointer badge badge-outline search-filter" data-column="type">Response Type</span>
                    <span class="cursor-pointer badge badge-outline search-filter" data-column="cluster">Cluster</span>
                </div>
            </div>
        </div>

        @if ($isAdmin)
            <div class="flex justify-end">
                <label for="addIndicatorModal" class="hidden btn btn-neutral md:inline-flex">
                    <span class="mr-1 iconify" data-icon="mdi:plus"></span>
                    Add New Indicator
                </label>
                <label for="addIndicatorModal" class="btn btn-primary md:hidden btn-square" aria-label="Add New Indicator">
                    <span class="iconify" data-icon="mdi:plus"></span>
                </label>
            </div>
        @endif
    </div>
</div>

<div class="w-full px-4">
    <div class="w-full shadow-xl card bg-base-100">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table w-full" id="indicators-table">
                    <thead>
                        <tr>
                            <th class="cursor-pointer hover:bg-base-200" data-sort="number">
                                Number <span class="inline-block ml-1 iconify sort-icon" data-icon="mdi:sort"></span>
                            </th>
                            <th class="cursor-pointer hover:bg-base-200" data-sort="name">
                                Indicator <span class="inline-block ml-1 iconify sort-icon" data-icon="mdi:sort"></span>
                            </th>
                            <th class="cursor-pointer hover:bg-base-200" data-sort="type">
                                Response Type <span class="inline-block ml-1 iconify sort-icon" data-icon="mdi:sort"></span>
                            </th>
                            <th class="cursor-pointer hover:bg-base-200" data-sort="cluster">
                                Cluster(s) <span class="inline-block ml-1 iconify sort-icon" data-icon="mdi:sort"></span>
                            </th>
                            @if ($isAdmin)
                                <th class="w-1">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indicators as $indicator)
                            <tr class="transition-colors indicator-row hover:bg-base-200/50">
                                <td>{{ $indicator->Indicator_Number }}</td>
                                <td>{{ $indicator->Indicator_Name }}</td>
                                <td>{{ $indicator->ResponseType }}</td>
                                <td>{{ $indicator->Responsible_Cluster }}</td>
                                @if ($isAdmin)
                                    <td>
                                        <div class="flex flex-wrap gap-2">
                                            <label for="editIndicatorModal-{{ $indicator->id }}"
                                                class="btn btn-outline btn-sm">
                                                <span class="iconify" data-icon="mdi:pencil"></span>
                                            </label>
                                            <form id="delete-form-{{ $indicator->id }}"
                                                action="{{ route('DeleteEcsahcIndicators') }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="id" value="{{ $indicator->id }}">
                                                <button type="button" class="btn btn-outline btn-sm btn-error"
                                                    onclick="confirmDelete('{{ $indicator->id }}')">
                                                    <span class="iconify" data-icon="mdi:trash-can-outline"></span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr id="no-results-row" class="hidden">
                                <td colspan="{{ $isAdmin ? 5 : 4 }}" class="py-4 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <span class="text-3xl iconify text-base-300" data-icon="mdi:file-search-outline"></span>
                                        <p class="font-medium">No indicators found matching your search</p>
                                        <button id="reset-search" class="mt-2 btn btn-sm btn-outline">Reset Search</button>
                                    </div>
                                </td>
                            </tr>
                            <tr id="empty-row" class="{{ count($indicators) > 0 ? 'hidden' : '' }}">
                                <td colspan="{{ $isAdmin ? 5 : 4 }}" class="py-4 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <span class="text-3xl iconify text-base-300" data-icon="mdi:clipboard-text-outline"></span>
                                        <p class="font-medium">No indicators available</p>
                                        @if ($isAdmin)
                                            <label for="addIndicatorModal" class="mt-2 btn btn-sm btn-primary">
                                                Add Your First Indicator
                                            </label>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Search Results Summary -->
            <div id="search-summary" class="hidden mt-4 text-sm text-right">
                <span id="results-count"></span> results found
                <button id="clear-search" class="ml-2 btn btn-xs btn-ghost">
                    <span class="iconify" data-icon="mdi:close"></span> Clear
                </button>
            </div>
        </div>
    </div>
</div>

@if ($isAdmin)
    <!-- ADD INDICATOR MODAL -->
    <input type="checkbox" id="addIndicatorModal" class="modal-toggle" />
    <div class="modal">
        <div class="relative w-full max-w-6xl modal-box">
            <label for="addIndicatorModal" class="absolute btn btn-sm btn-circle right-2 top-2">✕</label>
            <h5 class="mb-4 text-lg font-bold">Add New Indicator ({{ $strategicObjectives->SO_Name }})</h5>
            <form action="{{ route('AddEcsahcIndicators') }}" method="POST" id="addIndicatorForm"
                class="grid grid-cols-1 gap-4 md:grid-cols-3">
                @csrf
                <input type="hidden" name="StrategicObjectiveID" value="{{ $StrategicObjectiveID }}">
                <input type="hidden" name="IndicatorID"
                    value="{{ md5(md5(uniqid() . date('now') . $StrategicObjectiveID)) }}">
                <div class="form-control">
                    <label class="font-semibold label" for="Indicator_Number">Indicator Number</label>
                    <input type="text" class="w-full input input-bordered" id="Indicator_Number"
                        name="Indicator_Number" required>
                </div>
                <div class="form-control">
                    <label class="font-semibold label" for="Indicator_Name">Indicator Name</label>
                    <input type="text" class="w-full input input-bordered" id="Indicator_Name" name="Indicator_Name"
                        required>
                </div>
                <div class="form-control">
                    <label class="font-semibold label" for="ResponseType">Response Type</label>
                    <select class="w-full select select-bordered" id="ResponseType" name="ResponseType" required>
                        <option value="Number">Number</option>
                        <option value="Text">Text</option>
                        <option value="Boolean">Boolean</option>
                        <option value="Yes/No">Yes/No</option>
                    </select>
                </div>
                <div class="form-control md:col-span-2">
                    <label class="font-semibold label">Responsible Cluster(s)</label>
                    <select name="Responsible_Cluster[]" class="w-full select select-bordered tomselect-multiple"
                        id="select-states" multiple>
                        @foreach ($clusters as $cluster)
                            <option value="{{ $cluster->ClusterID }}">{{ $cluster->Cluster_Name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <div class="mt-4 modal-action">
                <label for="addIndicatorModal" class="btn btn-neutral">Cancel</label>
                <button type="submit" form="addIndicatorForm" class="btn btn-primary">
                    <span class="mr-1 iconify" data-icon="mdi:content-save"></span>Save
                </button>
            </div>
        </div>
    </div>

    <!-- EDIT INDICATOR MODALS -->
    @foreach ($indicators as $indicator)
        <input type="checkbox" id="editIndicatorModal-{{ $indicator->id }}" class="modal-toggle edit-modal-toggle" />
        <div class="modal">
            <div class="relative w-full max-w-6xl modal-box">
                <label for="editIndicatorModal-{{ $indicator->id }}"
                    class="absolute btn btn-sm btn-circle right-2 top-2">✕</label>
                <h5 class="mb-4 text-lg font-bold">Edit Indicator</h5>
                <form action="{{ route('UpdateEcsahcIndicators') }}" method="POST"
                    id="editIndicatorForm-{{ $indicator->id }}" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $indicator->id }}">
                    <input type="hidden" name="StrategicObjectiveID" value="{{ $StrategicObjectiveID }}">
                    <div class="form-control">
                        <label class="font-semibold label" for="Indicator_Number-{{ $indicator->id }}">Indicator
                            Number</label>
                        <input type="text" class="w-full input input-bordered"
                            id="Indicator_Number-{{ $indicator->id }}" name="Indicator_Number"
                            value="{{ $indicator->Indicator_Number }}" required>
                    </div>
                    <div class="form-control">
                        <label class="font-semibold label" for="Indicator_Name-{{ $indicator->id }}">Indicator
                            Name</label>
                        <input type="text" class="w-full input input-bordered"
                            id="Indicator_Name-{{ $indicator->id }}" name="Indicator_Name"
                            value="{{ $indicator->Indicator_Name }}" required>
                    </div>

                    <div class="form-control">
                        <label class="font-semibold label" for="ResponseType-{{ $indicator->id }}">Response
                            Type</label>
                        <select class="w-full select select-bordered" id="ResponseType-{{ $indicator->id }}"
                            name="ResponseType" required>
                            <option value="Number" @if ($indicator->ResponseType === 'Number') selected @endif>Number</option>
                            <option value="Text" @if ($indicator->ResponseType === 'Text') selected @endif>Text</option>
                            <option value="Boolean" @if ($indicator->ResponseType === 'Boolean') selected @endif>Boolean</option>
                            <option value="Yes/No" @if ($indicator->ResponseType === 'Yes/No') selected @endif>Yes/No</option>
                        </select>
                    </div>
                    @php
                        // Decode the clusters stored in the database
                        $existingClusters = json_decode($indicator->Responsible_Cluster, true) ?? [];
                    @endphp
                    <div class="form-control md:col-span-2">
                        <label class="font-semibold label">Responsible Cluster(s)</label>
                        <select name="Responsible_Cluster[]" class="w-full select select-bordered tomselect-multiple"
                            id="select-states-{{ $indicator->id }}" multiple>
                            @foreach ($clusters as $cluster)
                                <option value="{{ $cluster->ClusterID }}"
                                    @if (in_array($cluster->ClusterID, $existingClusters)) selected @endif>
                                    {{ $cluster->Cluster_Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
                <div class="mt-4 modal-action">
                    <label for="editIndicatorModal-{{ $indicator->id }}" class="btn btn-neutral">Cancel</label>
                    <button type="submit" form="editIndicatorForm-{{ $indicator->id }}" class="btn btn-primary">
                        <span class="mr-1 iconify" data-icon="mdi:content-save"></span>Update
                    </button>
                </div>
            </div>
        </div>
    @endforeach
@endif

<!-- TomSelect Styles and Scripts -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>

<!-- Custom Styles for TomSelect Dropdown and Premium Search -->
<style>
    .ts-dropdown {
        z-index: 99999 !important;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        background-color: #fff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        background-color: wheat !important;
        height: 400px !important;
        overflow-y: scroll !important;
    }

    .ts-dropdown .ts-dropdown-item:hover {
        background-color: #f3f4f6;
    }

    .ts-control {
        font-family: 'Inter', sans-serif;
        font-size: 0.875rem;
        border-color: #d1d5db;
    }

    .ts-control:focus-within {
        border-color: #2563eb;
        box-shadow: 0 0 0 1px #2563eb;
    }

    /* Premium Search Styles */
    #premium-search:focus {
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
        transition: all 0.2s ease;
    }

    .search-filter {
        transition: all 0.2s ease;
    }

    .search-filter:hover {
        transform: translateY(-1px);
    }

    .highlight {
        background-color: rgba(59, 130, 246, 0.15);
        border-radius: 2px;
        padding: 0 2px;
    }

    .sort-active {
        color: #2563eb;
    }

    /* Animated search results */
    .search-animation {
        animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Table hover effects */
    .indicator-row {
        transition: all 0.2s ease;
    }

    .indicator-row:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }
</style>

<!-- SweetAlert2 for Delete Confirmation -->
<script>
    function confirmDelete(indicatorId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + indicatorId).submit();
            }
        });
    }
</script>

<!-- Initialize TomSelect for all multiple select elements and reapply selected values for edit modals -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
    if (window.TomSelect) {
        // Initialize enhanced TomSelect on all elements with the class
        document.querySelectorAll(".tomselect-multiple").forEach(function(selectEl) {
            const tomInstance = new TomSelect(selectEl, {
                // Core Enhancements - using only built-in plugins
                plugins: ['remove_button', 'clear_button'],
                copyClassesToDropdown: true,
                dropdownParent: 'body',
                controlInput: '<input>',
                closeAfterSelect: false, // Keep dropdown open for multiple selections

                // Rapid Data Entry Features
                createOnBlur: true,
                createFilter: function(input) {
                    return input.length >= 2; // Minimum 2 characters to create new item
                },
                create: true, // Allow creating new items

                // Performance Optimizations
                loadThrottle: 100,

                // Keyboard Navigation Enhancements
                selectOnTab: true,

                // Custom Rendering
                render: {
                    // Enhanced item display
                    item: function(data, escape) {
                        return `<div class="flex items-center gap-1 px-2 py-1 rounded ts-item bg-primary/10 text-primary-focus">
                            <span>${escape(data.text)}</span>
                        </div>`;
                    },

                    // Enhanced option display
                    option: function(data, escape) {
                        return `<div class="flex items-center gap-2 p-2 transition-colors cursor-pointer ts-option hover:bg-primary/10">
                            <div class="flex-1">
                                <div class="font-medium">${escape(data.text)}</div>
                                ${data.description ? `<div class="text-xs text-gray-500">${escape(data.description)}</div>` : ''}
                            </div>
                        </div>`;
                    },

                    // No results message
                    no_results: function(data, escape) {
                        return `<div class="p-3 text-center no-results">
                            <div class="text-gray-500">No results found</div>
                            <div class="mt-1 text-xs">Press <kbd class="px-2 py-1 bg-gray-100 rounded">Enter</kbd> to create</div>
                        </div>`;
                    }
                },

                // Rapid Data Entry: Keyboard Shortcuts
                onInitialize: function() {
                    const self = this;

                    // Add batch selection button for rapid entry
                    if (this.isMultiple) {
                        const batchBtn = document.createElement('button');
                        batchBtn.className = 'ts-batch-btn absolute right-0 top-0 p-2 text-gray-400 hover:text-primary';
                        batchBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>';
                        batchBtn.title = "Batch Selection";
                        batchBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            showBatchSelectionDialog(self);
                        });

                        this.wrapper.appendChild(batchBtn);
                    }

                    // Add clipboard paste support
                    this.control_input.addEventListener('paste', function(e) {
                        setTimeout(function() {
                            const pastedText = self.control_input.value;
                            if (pastedText.includes(',') || pastedText.includes('\n')) {
                                e.preventDefault();
                                const items = pastedText.split(/[,\n]/).map(item => item.trim()).filter(item => item);
                                items.forEach(item => {
                                    self.createItem(item);
                                });
                                self.control_input.value = '';
                            }
                        }, 0);
                    });

                    // Add keyboard shortcuts
                    document.addEventListener('keydown', function(e) {
                        // Only process if this TomSelect instance is focused
                        if (!self.isFocused) return;

                        // Ctrl+A: Select all visible options
                        if (e.ctrlKey && e.key === 'a' && self.isOpen) {
                            e.preventDefault();
                            const visibleOptions = self.dropdown_content.querySelectorAll('.option');
                            visibleOptions.forEach(option => {
                                const value = option.dataset.value;
                                if (!self.items.includes(value)) {
                                    self.addItem(value);
                                }
                            });
                        }
                    });
                }
            });

            // Store the instance for later reference
            selectEl.tomInstance = tomInstance;

            // Add custom classes for better styling
            tomInstance.wrapper.classList.add('ts-enhanced', 'focus-within:ring-2', 'focus-within:ring-primary/30', 'transition-shadow');
        });

        // Add styles for batch selection dialog
        const batchSelectionStyles = document.createElement('style');
        batchSelectionStyles.textContent = `
            .ts-batch-selection {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                border-radius: 0.5rem;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
                z-index: 9999;
                width: 480px;
                max-width: 90vw;
            }

            .ts-batch-selection-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem;
                border-bottom: 1px solid #e5e7eb;
            }

            .ts-batch-selection-body {
                padding: 1rem;
            }

            .ts-batch-selection-footer {
                display: flex;
                justify-content: flex-end;
                gap: 0.5rem;
                padding: 1rem;
                border-top: 1px solid #e5e7eb;
            }

            /* Enhanced TomSelect Styling */
            .ts-enhanced.ts-wrapper {
                border-radius: 0.375rem;
                border: 1px solid #d1d5db;
                padding: 0.25rem;
                transition: all 0.2s ease;
            }

            .ts-enhanced.ts-wrapper.focus {
                border-color: #2563eb;
                box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
            }

            .ts-enhanced .ts-control {
                min-height: 38px;
                padding: 0.25rem;
            }

            .ts-enhanced .ts-dropdown {
                border-radius: 0.375rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                border: 1px solid #e5e7eb;
                padding: 0.5rem 0;
            }

            .ts-enhanced .ts-dropdown .option {
                padding: 0.5rem 1rem;
            }

            .ts-enhanced .ts-dropdown .option.active {
                background-color: rgba(37, 99, 235, 0.1);
                color: #1e40af;
            }

            .ts-enhanced .ts-dropdown .option:hover {
                background-color: rgba(37, 99, 235, 0.05);
            }

            .ts-enhanced .ts-dropdown .optgroup-header {
                padding: 0.5rem 1rem;
                font-weight: 600;
                color: #4b5563;
                background-color: #f9fafb;
            }

            .ts-enhanced .item {
                background-color: rgba(37, 99, 235, 0.1);
                color: #1e40af;
                border-radius: 0.25rem;
                padding: 0.25rem 0.5rem;
                margin: 0.125rem;
            }

            .ts-enhanced .item.active {
                background-color: #2563eb;
                color: white;
            }
        `;
        document.head.appendChild(batchSelectionStyles);
    }

    initPremiumSearch();
});

// Function to show batch selection dialog
function showBatchSelectionDialog(tomInstance) {
    // Remove any existing dialog
    const existingDialog = document.querySelector('.ts-batch-selection');
    if (existingDialog) existingDialog.remove();

    // Create dialog
    const dialog = document.createElement('div');
    dialog.className = 'ts-batch-selection';
    dialog.innerHTML = `
        <div class="ts-batch-selection-header">
            <h2 class="text-lg font-semibold">Batch Selection</h2>
            <button class="p-2 text-gray-400 ts-close-btn hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="ts-batch-selection-body">
            <p class="mb-2 text-sm text-gray-500">Enter multiple items separated by commas or new lines:</p>
            <textarea class="w-full h-32 p-2 border border-gray-300 rounded-md ts-batch-input focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"></textarea>

            <div class="mt-4">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" class="w-4 h-4 ts-batch-create-new form-checkbox text-primary focus:ring-primary/30" checked>
                    Create new items if they don't exist
                </label>
            </div>

            <div class="mt-4">
                <h3 class="mb-2 text-sm font-medium text-gray-700">Saved Selections</h3>
                <div class="flex flex-wrap gap-2 ts-saved-selections">
                    <!-- Saved selections will be populated here -->
                </div>
            </div>
        </div>
        <div class="ts-batch-selection-footer">
            <button class="px-4 py-2 text-sm text-gray-700 rounded-md ts-batch-cancel hover:bg-gray-100">Cancel</button>
            <button class="px-4 py-2 text-sm bg-gray-200 rounded-md ts-batch-save hover:bg-gray-300">Save as Preset</button>
            <button class="px-4 py-2 text-sm text-white rounded-md ts-batch-apply bg-primary hover:bg-primary-focus">Apply</button>
        </div>
    `;

    document.body.appendChild(dialog);

    // Close button functionality
    dialog.querySelector('.ts-close-btn').addEventListener('click', function() {
        dialog.remove();
    });

    // Cancel button functionality
    dialog.querySelector('.ts-batch-cancel').addEventListener('click', function() {
        dialog.remove();
    });

    // Apply button functionality
    dialog.querySelector('.ts-batch-apply').addEventListener('click', function() {
        const batchInput = dialog.querySelector('.ts-batch-input').value;
        const createNew = dialog.querySelector('.ts-batch-create-new').checked;

        if (batchInput.trim()) {
            const items = batchInput.split(/[,\n]/).map(item => item.trim()).filter(item => item);

            items.forEach(item => {
                // Check if item exists in options
                const exists = tomInstance.options.hasOwnProperty(item) ||
                               Array.from(Object.values(tomInstance.options)).some(option =>
                                   option.toLowerCase() === item.toLowerCase());

                if (exists) {
                    // Add existing item
                    const value = Object.keys(tomInstance.options).find(key =>
                        tomInstance.options[key].toLowerCase() === item.toLowerCase()) || item;
                    tomInstance.addItem(value);
                } else if (createNew) {
                    // Create and add new item
                    tomInstance.createItem(item);
                }
            });
        }

        dialog.remove();
    });

    // Save as preset functionality
    dialog.querySelector('.ts-batch-save').addEventListener('click', function() {
        const batchInput = dialog.querySelector('.ts-batch-input').value;
        if (batchInput.trim()) {
            const presetName = prompt('Enter a name for this selection preset:');
            if (presetName) {
                // Save to localStorage
                const savedSelections = JSON.parse(localStorage.getItem('tomSelectPresets') || '{}');
                savedSelections[presetName] = batchInput;
                localStorage.setItem('tomSelectPresets', JSON.stringify(savedSelections));

                // Add to UI
                const savedSelectionsContainer = dialog.querySelector('.ts-saved-selections');
                const newPresetBtn = document.createElement('button');
                newPresetBtn.className = 'ts-saved-selection px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-full';
                newPresetBtn.textContent = presetName;
                newPresetBtn.addEventListener('click', function() {
                    dialog.querySelector('.ts-batch-input').value = batchInput;
                });
                savedSelectionsContainer.appendChild(newPresetBtn);
            }
        }
    });

    // Load saved presets
    const savedSelections = JSON.parse(localStorage.getItem('tomSelectPresets') || '{}');
    const savedSelectionsContainer = dialog.querySelector('.ts-saved-selections');

    // Clear default buttons
    savedSelectionsContainer.innerHTML = '';

    // Add saved presets
    Object.entries(savedSelections).forEach(([name, value]) => {
        const presetBtn = document.createElement('button');
        presetBtn.className = 'ts-saved-selection px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-full';
        presetBtn.textContent = name;
        presetBtn.addEventListener('click', function() {
            dialog.querySelector('.ts-batch-input').value = value;
        });
        savedSelectionsContainer.appendChild(presetBtn);
    });

    // If no saved presets, show message
    if (Object.keys(savedSelections).length === 0) {
        savedSelectionsContainer.innerHTML = '<span class="text-xs text-gray-500">No saved selections yet</span>';
    }

    // Close on click outside
    document.addEventListener('click', function closeDialog(e) {
        if (!dialog.contains(e.target) && !e.target.closest('.ts-batch-btn')) {
            dialog.remove();
            document.removeEventListener('click', closeDialog);
        }
    });

    // Close on ESC key
    document.addEventListener('keydown', function closeDialogOnEsc(e) {
        if (e.key === 'Escape') {
            dialog.remove();
            document.removeEventListener('keydown', closeDialogOnEsc);
        }
    });

    // Focus the textarea
    setTimeout(() => {
        dialog.querySelector('.ts-batch-input').focus();
    }, 100);
}

    // Premium Search Functionality
  // Premium Search Functionality
function initPremiumSearch() {
    const searchInput = document.getElementById('premium-search');
    const searchFilters = document.getElementById('search-filters');
    const filterButtons = document.querySelectorAll('.search-filter');
    const table = document.getElementById('indicators-table');

    // Exit early if required elements don't exist
    if (!searchInput || !table) {
        console.log('Premium search elements not found, skipping initialization');
        return;
    }

    const rows = table.querySelectorAll('tbody tr.indicator-row');
    const noResultsRow = document.getElementById('no-results-row');
    const emptyRow = document.getElementById('empty-row');
    const searchSummary = document.getElementById('search-summary');
    const resultsCount = document.getElementById('results-count');
    const clearSearchBtn = document.getElementById('clear-search');
    const resetSearchBtn = document.getElementById('reset-search');
    const sortHeaders = document.querySelectorAll('th[data-sort]');

    let activeFilter = 'all';
    let currentSort = { column: null, direction: 'asc' };
    let debounceTimer;

    // Toggle search filters visibility (with null check)
    if (searchInput && searchFilters) {
        searchInput.addEventListener('focus', () => {
            searchFilters.classList.remove('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchFilters.contains(e.target)) {
                searchFilters.classList.add('hidden');
            }
        });
    }

    // Set active filter (with null check)
    if (filterButtons.length > 0) {
        filterButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                filterButtons.forEach(b => b.classList.remove('badge-primary'));
                filterButtons.forEach(b => b.classList.add('badge-outline'));
                btn.classList.remove('badge-outline');
                btn.classList.add('badge-primary');
                activeFilter = btn.dataset.column;
                performSearch(searchInput.value);
            });
        });
    }

    // Search functionality with debounce (with null check)
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSearch(searchInput.value);
            }, 300);
        });
    }

    // Clear search (with null check)
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', () => {
            if (searchInput) {
                searchInput.value = '';
                performSearch('');
            }
        });
    }

    // Reset search from no results view (with null check)
    if (resetSearchBtn) {
        resetSearchBtn.addEventListener('click', () => {
            if (searchInput) {
                searchInput.value = '';
                performSearch('');
            }
        });
    }

    // Sorting functionality (with null check)
    if (sortHeaders.length > 0) {
        sortHeaders.forEach(header => {
            header.addEventListener('click', () => {
                const column = header.dataset.sort;

                // Update sort direction
                if (currentSort.column === column) {
                    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    currentSort.column = column;
                    currentSort.direction = 'asc';
                }

                // Update sort icons
                sortHeaders.forEach(h => {
                    const icon = h.querySelector('.sort-icon');
                    if (icon) {
                        if (h.dataset.sort === currentSort.column) {
                            icon.classList.add('sort-active');
                            icon.setAttribute('data-icon', currentSort.direction === 'asc' ? 'mdi:sort-ascending' : 'mdi:sort-descending');
                        } else {
                            icon.classList.remove('sort-active');
                            icon.setAttribute('data-icon', 'mdi:sort');
                        }
                    }
                });

                sortTable();
            });
        });
    }

    // Perform search based on input and active filter
    function performSearch(query) {
        if (!rows.length) return;

        query = query.trim().toLowerCase();
        let matchCount = 0;

        // Clear previous highlights
        table.querySelectorAll('.highlight').forEach(el => {
            el.outerHTML = el.innerHTML;
        });

        // Show/hide rows based on search
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            let match = false;

            if (query === '') {
                match = true;
            } else {
                if (activeFilter === 'all') {
                    // Search all columns
                    for (let i = 0; i < cells.length - 1; i++) {
                        const cellText = cells[i].textContent.toLowerCase();
                        if (cellText.includes(query)) {
                            match = true;
                            highlightText(cells[i], query);
                        }
                    }
                } else {
                    // Search specific column
                    let columnIndex;
                    switch (activeFilter) {
                        case 'number': columnIndex = 0; break;
                        case 'name': columnIndex = 1; break;
                        case 'type': columnIndex = 2; break;
                        case 'cluster': columnIndex = 3; break;
                        default: columnIndex = 0;
                    }

                    if (cells[columnIndex]) {
                        const cellText = cells[columnIndex].textContent.toLowerCase();
                        if (cellText.includes(query)) {
                            match = true;
                            highlightText(cells[columnIndex], query);
                        }
                    }
                }
            }

            if (match) {
                row.classList.remove('hidden');
                row.classList.add('search-animation');
                matchCount++;
            } else {
                row.classList.add('hidden');
                row.classList.remove('search-animation');
            }
        });

        // Show/hide no results message
        if (noResultsRow) {
            if (matchCount === 0 && rows.length > 0) {
                noResultsRow.classList.remove('hidden');
                if (emptyRow) emptyRow.classList.add('hidden');
            } else {
                noResultsRow.classList.add('hidden');
                if (emptyRow && rows.length === 0) {
                    emptyRow.classList.remove('hidden');
                }
            }
        }

        // Update search summary
        if (searchSummary && resultsCount) {
            if (query !== '') {
                searchSummary.classList.remove('hidden');
                resultsCount.textContent = matchCount;
            } else {
                searchSummary.classList.add('hidden');
            }
        }

        // Re-sort the visible rows
        if (currentSort.column) {
            sortTable();
        }
    }

    // Highlight matching text
    function highlightText(cell, query) {
        if (!cell) return;

        const content = cell.innerHTML;
        const regex = new RegExp(`(${escapeRegExp(query)})`, 'gi');
        cell.innerHTML = content.replace(regex, '<span class="highlight">$1</span>');
    }

    // Sort table based on current sort settings
    function sortTable() {
        if (!table) return;

        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const visibleRows = Array.from(rows).filter(row => !row.classList.contains('hidden'));

        visibleRows.sort((a, b) => {
            const columnIndex = getColumnIndex(currentSort.column);

            if (!a.cells[columnIndex] || !b.cells[columnIndex]) return 0;

            const aValue = a.cells[columnIndex].textContent.trim();
            const bValue = b.cells[columnIndex].textContent.trim();

            // Check if values are numbers
            const aNum = parseFloat(aValue);
            const bNum = parseFloat(bValue);

            let comparison;
            if (!isNaN(aNum) && !isNaN(bNum)) {
                comparison = aNum - bNum;
            } else {
                comparison = aValue.localeCompare(bValue);
            }

            return currentSort.direction === 'asc' ? comparison : -comparison;
        });

        // Reorder rows
        visibleRows.forEach(row => {
            tbody.appendChild(row);
        });

        // Keep special rows at the end
        if (noResultsRow) tbody.appendChild(noResultsRow);
        if (emptyRow) tbody.appendChild(emptyRow);
    }

    // Helper function to get column index from sort key
    function getColumnIndex(column) {
        switch (column) {
            case 'number': return 0;
            case 'name': return 1;
            case 'type': return 2;
            case 'cluster': return 3;
            default: return 0;
        }
    }

    // Helper function to escape special characters in regex
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }
}
</script>
