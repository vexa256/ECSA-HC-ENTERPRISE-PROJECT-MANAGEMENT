<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">MPA Timelines</h1>
        <button class="btn btn-active" onclick="add_timeline_modal.showModal()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                    clip-rule="evenodd" />
            </svg>
            Add Timeline
        </button>
    </div>

    <div class="mb-4">
        <input type="text" id="search-input" placeholder="Search timelines..."
            class="input input-bordered w-full max-w-xs" />
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>Report Name</th>
                    <th>Type</th>
                    <th>Year</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="timelines-container">
                @forelse ($timelines as $timeline)
                    <tr>
                        <td>{{ $timeline->ReportName }}</td>
                        <td>{{ $timeline->Type }}</td>
                        <td>{{ $timeline->Year }}</td>
                        <td>
                            <span
                                class="badge {{ $timeline->status == 'Completed' ? 'badge-success' : ($timeline->status == 'In Progress' ? 'badge-warning' : 'badge-error') }}">
                                {{ $timeline->status }}
                            </span>
                        </td>
                        <td>
                            <div class="flex space-x-2">
                                <button class="btn btn-outline btn-sm"
                                    onclick="edit_timeline_modal_{{ $timeline->id }}.showModal()">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path
                                            d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </button>
                                <button class="btn btn-outline btn-error btn-sm"
                                    onclick="confirmDelete('{{ $timeline->id }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 dark:text-gray-400">No timelines found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add New Timeline Modal -->
<dialog id="add_timeline_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-white dark:bg-gray-800">
        <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-white">Add New MPA Timeline</h3>
        <form action="{{ route('MassInsert') }}" method="POST" id="addTimelineForm">
            @csrf
            <input type="hidden" name="TableName" value="mpa_timelines">
            <div class="space-y-4">
                <div class="form-control">
                    <label class="label" for="ReportName">
                        <span class="label-text">Report Name</span>
                    </label>
                    <input type="text" id="ReportName" name="ReportName" class="input input-bordered w-full"
                        required>
                </div>
                <div class="form-control">
                    <label class="label" for="Type">
                        <span class="label-text">Type</span>
                    </label>
                    <select id="Type" name="Type" class="select select-bordered w-full" required>
                        <option value="Quarterly">Quarterly</option>
                        <option value="Bi-Annual">Bi-Annual</option>
                        <option value="Annually Reported">Annually Reported</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label" for="Description">
                        <span class="label-text">Description</span>
                    </label>
                    <textarea id="Description" name="Description" class="textarea textarea-bordered h-24"></textarea>
                </div>
                <div class="form-control">
                    <label class="label" for="Year">
                        <span class="label-text">Year</span>
                    </label>
                    <input type="text" id="Year" name="Year" class="input input-bordered w-full"
                        maxlength="4" pattern="\d{4}" required>
                </div>
                <div class="form-control">
                    <label class="label" for="status">
                        <span class="label-text">Status</span>
                    </label>
                    <select id="status" name="status" class="select select-bordered w-full" required>
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>
                <div class="form-control" id="lastBiAnnualWrapper" style="display: none;">
                    <label class="label cursor-pointer">
                        <span class="label-text">Last Bi-Annual</span>
                        <input type="checkbox" name="LastBiAnnual" value="1" class="checkbox">
                    </label>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-outline" onclick="add_timeline_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-active">Save</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Edit Timeline Modals -->
@foreach ($timelines as $timeline)
    <dialog id="edit_timeline_modal_{{ $timeline->id }}" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-white dark:bg-gray-800">
            <h3 class="font-bold text-lg mb-4 text-gray-800 dark:text-white">Edit MPA Timeline</h3>
            <form action="{{ route('MassUpdate', $timeline->id) }}" method="POST"
                id="editTimelineForm-{{ $timeline->id }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="TableName" value="mpa_timelines">
                <input type="hidden" name="id" value="{{ $timeline->id }}">
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label" for="ReportName-{{ $timeline->id }}">
                            <span class="label-text">Report Name</span>
                        </label>
                        <input type="text" id="ReportName-{{ $timeline->id }}" name="ReportName"
                            value="{{ $timeline->ReportName }}" class="input input-bordered w-full" required>
                    </div>
                    <div class="form-control">
                        <label class="label" for="Type-{{ $timeline->id }}">
                            <span class="label-text">Type</span>
                        </label>
                        <select id="Type-{{ $timeline->id }}" name="Type" class="select select-bordered w-full"
                            required>
                            <option value="Quarterly" {{ $timeline->Type == 'Quarterly' ? 'selected' : '' }}>Quarterly
                            </option>
                            <option value="Bi-Annual" {{ $timeline->Type == 'Bi-Annual' ? 'selected' : '' }}>Bi-Annual
                            </option>
                            <option value="Annually Reported"
                                {{ $timeline->Type == 'Annually Reported' ? 'selected' : '' }}>Annually Reported
                            </option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label" for="Description-{{ $timeline->id }}">
                            <span class="label-text">Description</span>
                        </label>
                        <textarea id="Description-{{ $timeline->id }}" name="Description" class="textarea textarea-bordered h-24">{{ $timeline->Description }}</textarea>
                    </div>
                    <div class="form-control">
                        <label class="label" for="Year-{{ $timeline->id }}">
                            <span class="label-text">Year</span>
                        </label>
                        <input type="text" id="Year-{{ $timeline->id }}" name="Year"
                            value="{{ $timeline->Year }}" class="input input-bordered w-full" maxlength="4"
                            pattern="\d{4}" required>
                    </div>
                    <div class="form-control">
                        <label class="label" for="status-{{ $timeline->id }}">
                            <span class="label-text">Status</span>
                        </label>
                        <select id="status-{{ $timeline->id }}" name="status" class="select select-bordered w-full"
                            required>
                            <option value="Pending" {{ $timeline->status == 'Pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="In Progress" {{ $timeline->status == 'In Progress' ? 'selected' : '' }}>In
                                Progress</option>
                            <option value="Completed" {{ $timeline->status == 'Completed' ? 'selected' : '' }}>
                                Completed</option>
                        </select>
                    </div>
                    <div class="form-control" id="lastBiAnnualWrapper-{{ $timeline->id }}"
                        style="{{ $timeline->Type == 'Bi-Annual' ? '' : 'display: none;' }}">
                        <label class="label cursor-pointer">
                            <span class="label-text">Last Bi-Annual</span>
                            <input type="checkbox" name="LastBiAnnual" value="1" class="checkbox"
                                {{ $timeline->LastBiAnnual ? 'checked' : '' }}>
                        </label>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-outline"
                        onclick="edit_timeline_modal_{{ $timeline->id }}.close()">Cancel</button>
                    <button type="submit" class="btn btn-active">Update</button>
                </div>
            </form>
        </div>
    </dialog>
@endforeach

<!-- Delete Confirmation Modal -->
<dialog id="delete_confirm_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-white dark:bg-gray-800">
        <h3 class="font-bold text-lg text-error">Confirm Deletion</h3>
        <p class="py-4 text-gray-700 dark:text-gray-300">Are you sure you want to delete this timeline? This action
            cannot be undone.</p>
        <div class="modal-action">
            <form id="delete-form" action="{{ route('MassDelete') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" id="delete-id">
                <input type="hidden" name="TableName" value="mpa_timelines">
                <button type="button" class="btn btn-outline mr-2"
                    onclick="delete_confirm_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-outline btn-error">Delete</button>
            </form>
        </div>
    </div>
</dialog>

<script>
    function confirmDelete(timelineId) {
        const deleteForm = document.getElementById('delete-form');
        const deleteIdInput = document.getElementById('delete-id');
        deleteIdInput.value = timelineId;
        delete_confirm_modal.showModal();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-input');
        const timelinesContainer = document.getElementById('timelines-container');
        const timelineRows = timelinesContainer.querySelectorAll('tr');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            timelineRows.forEach(row => {
                const reportName = row.querySelector('td:first-child').textContent
                    .toLowerCase();
                const type = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                const year = row.querySelector('td:nth-child(3)').textContent;

                if (reportName.includes(searchTerm) || type.includes(searchTerm) || year
                    .includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            const visibleRows = timelinesContainer.querySelectorAll('tr[style="display: "";"]');
            if (visibleRows.length === 0) {
                const noResults = document.createElement('tr');
                noResults.innerHTML =
                    '<td colspan="5" class="text-center text-gray-500 dark:text-gray-400">No matching timelines found</td>';
                timelinesContainer.appendChild(noResults);
            } else {
                const existingNoResults = timelinesContainer.querySelector('tr td[colspan="5"]');
                if (existingNoResults) {
                    existingNoResults.closest('tr').remove();
                }
            }
        });

        // Toggle Last Bi-Annual checkbox visibility based on Type selection
        const typeSelects = document.querySelectorAll('select[name="Type"]');
        typeSelects.forEach(select => {
            select.addEventListener('change', function() {
                const formId = this.closest('form').id;
                const lastBiAnnualWrapper = document.getElementById(
                    `lastBiAnnualWrapper${formId.includes('edit') ? '-' + formId.split('-')[1] : ''}`
                );
                if (this.value === 'Bi-Annual') {
                    lastBiAnnualWrapper.style.display = 'block';
                } else {
                    lastBiAnnualWrapper.style.display = 'none';
                    lastBiAnnualWrapper.querySelector('input[type="checkbox"]').checked = false;
                }
            });
        });
    });
</script>

<style>
    /* iOS-inspired styles */
    .btn {
        @apply font-semibold text-sm px-4 py-2 rounded-full transition-all duration-300 ease-in-out;
    }

    .btn-outline {
        @apply border border-gray-300 text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700;
    }

    .btn-active {
        @apply bg-blue-500 text-white hover:bg-blue-600;
    }

    .btn-outline.btn-error {
        @apply border-red-500 text-red-500 hover:bg-red-100 dark:hover:bg-red-900;
    }

    .modal {
        @apply backdrop-blur-sm bg-black bg-opacity-30;
    }

    .modal-box {
        @apply rounded-2xl shadow-lg;
    }

    .table {
        @apply rounded-lg overflow-hidden;
    }

    .table th {
        @apply bg-gray-100 text-gray-700 font-semibold uppercase text-xs tracking-wider dark:bg-gray-700 dark:text-gray-300;
    }

    .table td {
        @apply text-gray-700 dark:text-gray-300;
    }

    .table tr:hover {
        @apply bg-gray-50 dark:bg-gray-600;
    }
</style>
