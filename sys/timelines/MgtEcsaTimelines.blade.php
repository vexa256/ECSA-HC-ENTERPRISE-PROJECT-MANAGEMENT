<!-- resources/views/timelines.blade.php -->
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">MPA Timelines</h1>
        <!-- Updated button theme: btn-sm, btn-active, btn-neutral -->
        <button class="btn btn-sm btn-active btn-neutral"
            onclick="document.getElementById('add_timeline_modal').showModal()">
            <!-- Heroicon for "add" -->
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New Timeline
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr class="bg-gray-100 dark:bg-gray-800">
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report
                        Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Closing
                        Date</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status
                    </th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last
                        Bi-Annual</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($timelines as $timeline)
                    <tr class="bg-white dark:bg-gray-700">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $timeline->id }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $timeline->ReportName }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $timeline->Type }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $timeline->Year }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($timeline->ClosingDate)->format('Y-m-d') }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $timeline->status == 'Completed'
                                    ? 'bg-green-100 text-green-800'
                                    : ($timeline->status == 'In Progress'
                                        ? 'bg-yellow-100 text-yellow-800'
                                        : 'bg-red-100 text-red-800') }}">
                                {{ $timeline->status }}
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            @if ($timeline->Type == 'Bi-Annual Reports')
                                {{ $timeline->LastBiAnnual }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium">
                            <!-- Updated button theme: btn-sm, btn-active (for Edit and Delete) -->
                            <button class="btn btn-sm btn-active mr-2"
                                onclick="document.getElementById('edit_timeline_modal_{{ $timeline->id }}').showModal()">
                                Edit
                            </button>
                            <button class="btn btn-sm btn-error text-light"
                                onclick="confirmDelete('{{ $timeline->id }}')">
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-2 text-center text-gray-500 dark:text-gray-400">
                            No timelines found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add New Timeline Modal -->
<dialog id="add_timeline_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 class="font-bold text-lg mb-4">Add New Timeline</h3>
        <!-- Must match: Route::post('/MassInsert', ...) -->
        <form action="{{ route('MassInsert') }}" method="POST" id="addTimelineForm">
            @csrf
            <input type="hidden" name="TableName" value="ecsahc_timelines">
            <input type="hidden" name="ReportingID" value="{{ md5(uniqid() . now()) }}">

            <div class="form-control mb-4">
                <label class="label" for="ReportName">
                    <span class="label-text">Report Name</span>
                </label>
                <input type="text" id="ReportName" name="ReportName" class="input input-bordered" required>
            </div>

            <div class="form-control mb-4">
                <label class="label" for="Type">
                    <span class="label-text">Type</span>
                </label>
                <select id="Type" name="Type" class="select select-bordered type-select" required>
                    <option value="Quarterly Reports">Quarterly Reports</option>
                    <option value="Bi-Annual Reports">Bi-Annual Reports</option>
                    <option value="Annual Reports">Annual Reports</option>
                </select>
            </div>

            <div class="form-control mb-4">
                <label class="label" for="Description">
                    <span class="label-text">Description</span>
                </label>
                <textarea id="Description" name="Description" class="textarea textarea-bordered" rows="3"></textarea>
            </div>

            <div class="form-control mb-4">
                <label class="label" for="Year">
                    <span class="label-text">Year</span>
                </label>
                <input type="text" id="Year" name="Year" class="input input-bordered" maxlength="4"
                    pattern="\d{4}" required>
            </div>

            <div class="form-control mb-4">
                <label class="label" for="ClosingDate">
                    <span class="label-text">Closing Date</span>
                </label>
                <input type="date" id="ClosingDate" name="ClosingDate" class="input input-bordered" required>
            </div>

            <div class="form-control mb-4">
                <label class="label" for="status">
                    <span class="label-text">Status</span>
                </label>
                <select id="status" name="status" class="select select-bordered" required>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>

            <!-- Only show for Bi-Annual -->
            <div class="form-control mb-4 last-biannual-wrapper">
                <label class="label cursor-pointer">
                    <span class="label-text">Last Bi-Annual</span>
                    <input type="checkbox" id="LastBiAnnual" name="LastBiAnnual" class="checkbox" value="Yes">
                </label>
            </div>

            <div class="modal-action">
                <!-- Updated button themes -->
                <button type="button" class="btn btn-sm btn-outline"
                    onclick="document.getElementById('add_timeline_modal').close()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-sm btn-active">
                    Save
                </button>
            </div>
        </form>
    </div>
</dialog>

<!-- Edit Timeline Modals -->
@foreach ($timelines as $timeline)
    <dialog id="edit_timeline_modal_{{ $timeline->id }}" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-4">Edit Timeline</h3>
            <!-- Must match: Route::put('/MassUpdate', ...) -->
            <form action="{{ route('MassUpdate') }}" method="POST" id="editTimelineForm-{{ $timeline->id }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="TableName" value="ecsahc_timelines">
                <input type="hidden" name="id" value="{{ $timeline->id }}">

                <div class="form-control mb-4">
                    <label class="label" for="ReportName-{{ $timeline->id }}">
                        <span class="label-text">Report Name</span>
                    </label>
                    <input type="text" id="ReportName-{{ $timeline->id }}" name="ReportName"
                        class="input input-bordered" value="{{ $timeline->ReportName }}" required>
                </div>

                <div class="form-control mb-4">
                    <label class="label" for="editType-{{ $timeline->id }}">
                        <span class="label-text">Type</span>
                    </label>
                    <select id="editType-{{ $timeline->id }}" name="Type"
                        class="select select-bordered type-select" required>
                        <option value="Quarterly Reports"
                            {{ $timeline->Type == 'Quarterly Reports' ? 'selected' : '' }}>
                            Quarterly Reports
                        </option>
                        <option value="Bi-Annual Reports"
                            {{ $timeline->Type == 'Bi-Annual Reports' ? 'selected' : '' }}>
                            Bi-Annual Reports
                        </option>
                        <option value="Annual Reports" {{ $timeline->Type == 'Annual Reports' ? 'selected' : '' }}>
                            Annual Reports
                        </option>
                    </select>
                </div>

                <div class="form-control mb-4">
                    <label class="label" for="Description-{{ $timeline->id }}">
                        <span class="label-text">Description</span>
                    </label>
                    <textarea id="Description-{{ $timeline->id }}" name="Description" class="textarea textarea-bordered"
                        rows="3">{{ $timeline->Description }}</textarea>
                </div>

                <div class="form-control mb-4">
                    <label class="label" for="Year-{{ $timeline->id }}">
                        <span class="label-text">Year</span>
                    </label>
                    <input type="text" id="Year-{{ $timeline->id }}" name="Year" class="input input-bordered"
                        value="{{ $timeline->Year }}" maxlength="4" pattern="\d{4}" required>
                </div>

                <div class="form-control mb-4">
                    <label class="label" for="ClosingDate-{{ $timeline->id }}">
                        <span class="label-text">Closing Date</span>
                    </label>
                    <input type="date" id="ClosingDate-{{ $timeline->id }}" name="ClosingDate"
                        class="input input-bordered" value="{{ $timeline->ClosingDate }}" required>
                </div>

                <div class="form-control mb-4">
                    <label class="label" for="status-{{ $timeline->id }}">
                        <span class="label-text">Status</span>
                    </label>
                    <select id="status-{{ $timeline->id }}" name="status" class="select select-bordered" required>
                        <option value="Pending" {{ $timeline->status == 'Pending' ? 'selected' : '' }}>Pending
                        </option>
                        <option value="In Progress" {{ $timeline->status == 'In Progress' ? 'selected' : '' }}>
                            In Progress
                        </option>
                        <option value="Completed" {{ $timeline->status == 'Completed' ? 'selected' : '' }}>
                            Completed
                        </option>
                    </select>
                </div>

                <div class="form-control mb-4 last-biannual-wrapper">
                    <label class="label cursor-pointer">
                        <span class="label-text">Last Bi-Annual</span>
                        <input type="checkbox" id="editLastBiAnnual-{{ $timeline->id }}" name="LastBiAnnual"
                            class="checkbox" value="Yes" {{ $timeline->LastBiAnnual == 'Yes' ? 'checked' : '' }}>
                    </label>
                </div>

                <div class="modal-action">
                    <!-- Updated button themes -->
                    <button type="button" class="btn btn-sm btn-outline"
                        onclick="document.getElementById('edit_timeline_modal_{{ $timeline->id }}').close()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-sm btn-active">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </dialog>
@endforeach

<!-- Delete Confirmation Modal -->
<dialog id="confirm_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Are you sure?</h3>
        <p class="py-4">You won't be able to revert this!</p>
        <div class="modal-action">
            <!-- Must match: Route::delete('/MassDelete', ...) -->
            <form id="deleteForm" method="POST" action="{{ route('MassDelete') }}">
                @csrf
                @method('DELETE')
                <input type="hidden" name="TableName" value="ecsahc_timelines">
                <input type="hidden" id="deleteTimelineId" name="id" value="">
                <!-- Updated button themes -->
                <button type="button" class="btn btn-sm btn-outline mr-2"
                    onclick="document.getElementById('confirm_modal').close()">
                    Cancel
                </button>
                <button type="submit" class="btn btn-sm btn-active">
                    Delete
                </button>
            </form>
        </div>
    </div>
</dialog>

<!-- Show success if session('status') -->
@if (session('status'))
    <dialog id="success_modal" class="modal modal-bottom sm:modal-middle" open>
        <div class="modal-box">
            <h3 class="font-bold text-lg text-success">Success</h3>
            <p class="py-4">{{ session('status') }}</p>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-sm btn-active">Close</button>
                </form>
            </div>
        </div>
    </dialog>
@endif

<!-- Show error if session('error') -->
@if (session('error'))
    <dialog id="error_modal" class="modal modal-bottom sm:modal-middle" open>
        <div class="modal-box">
            <h3 class="font-bold text-lg text-error">Error</h3>
            <p class="py-4">{{ session('error') }}</p>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-sm btn-active">Close</button>
                </form>
            </div>
        </div>
    </dialog>
@endif

<script>
    // Toggle Last Bi-Annual checkbox visibility based on "Type" select
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll(".type-select").forEach((selectElem) => {
            var form = selectElem.closest("form");
            if (!form) return;
            var wrapper = form.querySelector(".last-biannual-wrapper");
            if (!wrapper) return;

            function toggleWrapper() {
                wrapper.style.display = (selectElem.value === "Bi-Annual Reports") ? "block" : "none";
                if (wrapper.style.display === "none") {
                    var checkbox = wrapper.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = false;
                }
            }

            toggleWrapper();
            selectElem.addEventListener("change", toggleWrapper);
        });
    });

    // Delete confirmation
    function confirmDelete(timelineId) {
        // Set the hidden field with the timeline ID
        document.getElementById('deleteTimelineId').value = timelineId;
        // Show the confirmation modal
        document.getElementById('confirm_modal').showModal();
    }
</script>
