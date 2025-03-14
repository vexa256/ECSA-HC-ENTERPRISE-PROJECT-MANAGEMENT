@php
    $isAdmin = auth()->check() && auth()->user()->AccountRole === 'Admin';
@endphp

<!-- Header Section -->
<div class="w-full p-4">
    @if ($isAdmin)
        <div class="flex justify-end mb-2">
            <label for="addIndicatorModal" class="btn btn-active hidden md:inline-flex">
                <span class="iconify mr-1" data-icon="mdi:plus"></span>
                Add New Indicator
            </label>
            <label for="addIndicatorModal" class="btn btn-active md:hidden btn-square" aria-label="Add New Indicator">
                <span class="iconify" data-icon="mdi:plus"></span>
            </label>
        </div>
    @endif
</div>

<div class="w-full px-4">
    <div class="card bg-base-100 shadow-xl w-full">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table class="table w-full">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Indicator</th>
                            <th>Response Type</th>
                            <th>Cluster(s)</th>
                            @if ($isAdmin)
                                <th class="w-1">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indicators as $indicator)
                            <tr>
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
                            <!-- No indicators found -->
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@if ($isAdmin)
    <!-- ADD INDICATOR MODAL -->
    <input type="checkbox" id="addIndicatorModal" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box relative w-full max-w-6xl">
            <label for="addIndicatorModal" class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
            <h5 class="text-lg font-bold mb-4">Add New Indicator ({{ $strategicObjectives->SO_Name }})</h5>
            <form action="{{ route('AddEcsahcIndicators') }}" method="POST" id="addIndicatorForm"
                class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <input type="hidden" name="StrategicObjectiveID" value="{{ $StrategicObjectiveID }}">
                <input type="hidden" name="IndicatorID"
                    value="{{ md5(md5(uniqid() . date('now') . $StrategicObjectiveID)) }}">
                <div class="form-control">
                    <label class="label font-semibold" for="Indicator_Number">Indicator Number</label>
                    <input type="text" class="input input-bordered w-full" id="Indicator_Number"
                        name="Indicator_Number" required>
                </div>
                <div class="form-control">
                    <label class="label font-semibold" for="Indicator_Name">Indicator Name</label>
                    <input type="text" class="input input-bordered w-full" id="Indicator_Name" name="Indicator_Name"
                        required>
                </div>
                <div class="form-control">
                    <label class="label font-semibold" for="ResponseType">Response Type</label>
                    <select class="select select-bordered w-full" id="ResponseType" name="ResponseType" required>
                        <option value="Number">Number</option>
                        <option value="Text">Text</option>
                        <option value="Boolean">Boolean</option>
                        <option value="Yes/No">Yes/No</option>
                    </select>
                </div>
                <div class="form-control md:col-span-2">
                    <label class="label font-semibold">Responsible Cluster(s)</label>
                    <select name="Responsible_Cluster[]" class="select select-bordered w-full tomselect-multiple"
                        id="select-states" multiple>
                        @foreach ($clusters as $cluster)
                            <option value="{{ $cluster->ClusterID }}">{{ $cluster->Cluster_Name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <div class="modal-action mt-4">
                <label for="addIndicatorModal" class="btn btn-neutral">Cancel</label>
                <button type="submit" form="addIndicatorForm" class="btn btn-active">
                    <span class="iconify mr-1" data-icon="mdi:content-save"></span>Save
                </button>
            </div>
        </div>
    </div>

    <!-- EDIT INDICATOR MODALS -->
    @foreach ($indicators as $indicator)
        <input type="checkbox" id="editIndicatorModal-{{ $indicator->id }}" class="modal-toggle edit-modal-toggle" />
        <div class="modal">
            <div class="modal-box relative w-full max-w-6xl">
                <label for="editIndicatorModal-{{ $indicator->id }}"
                    class="btn btn-sm btn-circle absolute right-2 top-2">✕</label>
                <h5 class="text-lg font-bold mb-4">Edit Indicator</h5>
                <form action="{{ route('UpdateEcsahcIndicators') }}" method="POST"
                    id="editIndicatorForm-{{ $indicator->id }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="id" value="{{ $indicator->id }}">
                    <input type="hidden" name="StrategicObjectiveID" value="{{ $StrategicObjectiveID }}">
                    <div class="form-control">
                        <label class="label font-semibold" for="Indicator_Number-{{ $indicator->id }}">Indicator
                            Number</label>
                        <input type="text" class="input input-bordered w-full"
                            id="Indicator_Number-{{ $indicator->id }}" name="Indicator_Number"
                            value="{{ $indicator->Indicator_Number }}" required>
                    </div>
                    <div class="form-control">
                        <label class="label font-semibold" for="Indicator_Name-{{ $indicator->id }}">Indicator
                            Name</label>
                        <input type="text" class="input input-bordered w-full"
                            id="Indicator_Name-{{ $indicator->id }}" name="Indicator_Name"
                            value="{{ $indicator->Indicator_Name }}" required>
                    </div>

                    <div class="form-control">
                        <label class="label font-semibold" for="ResponseType-{{ $indicator->id }}">Response
                            Type</label>
                        <select class="select select-bordered w-full" id="ResponseType-{{ $indicator->id }}"
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
                        <label class="label font-semibold">Responsible Cluster(s)</label>
                        <select name="Responsible_Cluster[]" class="select select-bordered w-full tomselect-multiple"
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
                <div class="modal-action mt-4">
                    <label for="editIndicatorModal-{{ $indicator->id }}" class="btn btn-neutral">Cancel</label>
                    <button type="submit" form="editIndicatorForm-{{ $indicator->id }}" class="btn btn-active">
                        <span class="iconify mr-1" data-icon="mdi:content-save"></span>Update
                    </button>
                </div>
            </div>
        </div>
    @endforeach
@endif

<!-- TomSelect Styles and Scripts -->
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js"></script>

<!-- Custom Styles for TomSelect Dropdown -->
<style>
    .ts-dropdown {
        z-index: 99999 !important;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        background-color: #fff;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            // Initialize TomSelect on all elements with the class
            document.querySelectorAll(".tomselect-multiple").forEach(function(selectEl) {
                new TomSelect(selectEl, {
                    copyClassesToDropdown: false,
                    dropdownParent: 'body',
                    controlInput: '<input>',
                    render: {
                        item: function(data, escape) {
                            return '<div>' + escape(data.text) + '</div>';
                        },
                        option: function(data, escape) {
                            return '<div>' + escape(data.text) + '</div>';
                        }
                    }
                });
            });
        }
    });

    // When an edit modal opens, set the TomSelect value from the underlying select element
    document.querySelectorAll('.edit-modal-toggle').forEach(function(modalToggle) {
        modalToggle.addEventListener('change', function() {
            if (this.checked) {
                setTimeout(() => {
                    const modalId = this.getAttribute('id').replace('editIndicatorModal-', '');
                    const select = document.getElementById('select-states-' + modalId);
                    if (select && select.tomselect) {
                        // Get the selected values from the original select element
                        let selectedValues = Array.from(select.options)
                            .filter(option => option.selected)
                            .map(option => option.value);
                        // Explicitly set the values on the TomSelect instance
                        select.tomselect.setValue(selectedValues);
                    }
                }, 300);
            }
        });
    });
</script>
