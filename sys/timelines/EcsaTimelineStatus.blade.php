<div class="container mx-auto px-4 py-8">
    <!-- Top Bar: Search -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-2">
        <!-- Search Input -->
        <div>
            <input type="text" id="searchInput" placeholder="Search Timelines..."
                class="input input-bordered input-sm w-full md:w-64" />
        </div>
        <!-- (No "Add" functionality) -->
    </div>

    <!-- Timeline Cards (Grid) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="timelineCards">
        @forelse ($timelines as $timeline)
            <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow duration-300">
                <div class="card-body">
                    <h2 class="card-title text-lg font-semibold mb-2">
                        {{ $timeline->ReportName }}
                    </h2>
                    <div class="grid grid-cols-2 gap-2 text-sm mb-4">
                        <div>
                            <span class="font-medium">Type:</span> {{ $timeline->Type }}
                        </div>
                        <div>
                            <span class="font-medium">Year:</span> {{ $timeline->Year }}
                        </div>
                        <div>
                            <span class="font-medium">Status:</span>
                            <span
                                class="badge 
                                    @if ($timeline->status == 'Completed') badge-success 
                                    @elseif($timeline->status == 'In Progress') 
                                        badge-warning 
                                    @else 
                                        badge-error @endif
                                    ml-1
                                ">
                                {{ $timeline->status }}
                            </span>
                        </div>
                        <div>
                            <span class="font-medium">Last Bi-Annual:</span>
                            @if ($timeline->Type === 'Bi-Annual')
                                {{ $timeline->LastBiAnnual ? 'Yes' : 'No' }}
                            @else
                                -
                            @endif
                        </div>
                    </div>
                    <div class="card-actions justify-end">
                        <!-- Edit Button -->
                        <label for="editTimelineModal-{{ $timeline->id }}" class="btn btn-outline btn-sm">
                            <span class="iconify inline-block mr-1" data-icon="mdi:pencil"></span>
                            Edit
                        </label>
                        <!-- Delete Button -->
                        <button class="btn btn-outline btn-sm" onclick="confirmDelete({{ $timeline->id }})">
                            <span class="iconify inline-block mr-1" data-icon="mdi:trash-can-outline"></span>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500">No timelines found.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- ====================== -->
<!-- Edit Timeline Modals -->
<!-- ====================== -->
@foreach ($timelines as $timeline)
    <input type="checkbox" id="editTimelineModal-{{ $timeline->id }}" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box relative max-w-xl">
            <label for="editTimelineModal-{{ $timeline->id }}" class="btn btn-sm btn-circle absolute right-2 top-2">
                ✕
            </label>

            <h3 class="text-lg font-bold mb-4">
                Edit Reporting Status: {{ $timeline->ReportName }}
            </h3>

            <form action="{{ route('MassUpdate', $timeline->id) }}" method="POST"
                id="editTimelineForm-{{ $timeline->id }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="TableName" value="ecsahc_timelines">
                <input type="hidden" name="id" value="{{ $timeline->id }}">

                <div class="form-control mb-4">
                    <label class="label font-semibold" for="status-{{ $timeline->id }}">
                        Status
                    </label>
                    <select id="status-{{ $timeline->id }}" name="status" class="select select-bordered w-full"
                        required>
                        <option value="Pending" @if ($timeline->status === 'Pending') selected @endif>
                            Pending
                        </option>
                        <option value="In Progress" @if ($timeline->status === 'In Progress') selected @endif>
                            In Progress
                        </option>
                        <option value="Completed" @if ($timeline->status === 'Completed') selected @endif>
                            Completed
                        </option>
                    </select>
                </div>
            </form>

            <div class="modal-action">
                <!-- Cancel Button -->
                <label for="editTimelineModal-{{ $timeline->id }}" class="btn btn-outline btn-sm">
                    <span class="iconify inline-block mr-1" data-icon="mdi:close-circle-outline"></span>
                    Cancel
                </label>
                <!-- Update Button -->
                <button type="submit" form="editTimelineForm-{{ $timeline->id }}"
                    class="btn btn-active btn-neutral btn-sm">
                    <span class="iconify inline-block mr-1" data-icon="mdi:check-circle-outline"></span>
                    Update
                </button>
            </div>
        </div>
    </div>
@endforeach

<!-- ===================== -->
<!-- Delete Timeline Forms -->
<!-- ===================== -->
@foreach ($timelines as $timeline)
    <form id="delete-form-{{ $timeline->id }}" action="{{ route('MassDelete', $timeline->id) }}" method="POST"
        style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach

<!-- ===================== -->
<!-- Page Scripts -->
<!-- ===================== -->
<script>
    // SweetAlert2 Delete Confirmation
    function confirmDelete(timelineId) {
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
                document.getElementById('delete-form-' + timelineId).submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // DOM-based Search: filters cards by text
        const searchInput = document.getElementById('searchInput');
        const cardSelector = '#timelineCards .card';

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                const cards = document.querySelectorAll(cardSelector);

                cards.forEach((card) => {
                    const cardText = card.textContent.toLowerCase();
                    if (cardText.includes(query)) {
                        card.parentElement.style.display = '';
                    } else {
                        card.parentElement.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
