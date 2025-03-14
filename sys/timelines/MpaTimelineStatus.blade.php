<div class="bg-gray-100 p-4">
    <div class="container mx-auto">
        <h4 class="text-xl font-semibold text-center my-6 text-gray-800">MPA Reporting Timelines Status Managment</h4>
        <div class="mb-4 text-center">
            <input type="text" id="searchInput" placeholder="Search timelines..."
                class="input input-bordered w-full max-w-xs bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
        </div>



        <div id="timelineGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach ($timelines as $timeline)
                <div class="bg-white rounded-lg shadow-md p-4 timeline-card">
                    <h2 class="text-lg font-semibold mb-2 text-gray-800">{{ $timeline->ReportName }}</h2>
                    <p class="text-sm text-gray-600">Type: {{ $timeline->Type }}</p>
                    <p class="text-sm text-gray-600">Year: {{ $timeline->Year }}</p>
                    <div class="mt-2">
                        <span
                            class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $timeline->status == 'Completed'
                                ? 'bg-green-100 text-green-800'
                                : ($timeline->status == 'In Progress'
                                    ? 'bg-yellow-100 text-yellow-800'
                                    : 'bg-red-100 text-red-800') }}">
                            {{ $timeline->status }}
                        </span>
                    </div>
                    @if ($timeline->Type == 'Bi-Annual')
                        <p class="text-sm text-gray-600 mt-2">Last Bi-Annual:
                            {{ $timeline->LastBiAnnual ? 'Yes' : 'No' }}</p>
                    @endif
                    <div class="mt-4 text-right">
                        <label for="edit-modal-{{ $timeline->id }}"
                            class="btn btn-sm bg-blue-500 hover:bg-blue-600 text-white">Edit</label>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex justify-center">
            <div class="btn-group" id="pagination">
                <!-- Pagination buttons will be dynamically inserted here -->
            </div>
        </div>
    </div>
</div>

@foreach ($timelines as $timeline)
    <input type="checkbox" id="edit-modal-{{ $timeline->id }}" class="modal-toggle" />
    <div class="modal">
        <div class="modal-box bg-white rounded-lg shadow-lg">
            <h3 class="font-semibold text-xl mb-4 text-gray-800">
                Edit MPA Reporting Timeline Status: {{ $timeline->ReportName }}
            </h3>
            <form action="{{ route('MassUpdate', $timeline->id) }}" method="POST"
                id="editTimelineForm-{{ $timeline->id }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="TableName" value="mpa_timelines">
                <input type="hidden" name="id" value="{{ $timeline->id }}">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="status-{{ $timeline->id }}">
                        Status
                    </label>
                    <select
                        class="select select-bordered w-full bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        id="status-{{ $timeline->id }}" name="status" required>
                        <option value="Pending" {{ $timeline->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="In Progress" {{ $timeline->status == 'In Progress' ? 'selected' : '' }}>In
                            Progress</option>
                        <option value="Completed" {{ $timeline->status == 'Completed' ? 'selected' : '' }}>Completed
                        </option>
                    </select>
                </div>

                <div class="flex justify-end mt-6">
                    <label for="edit-modal-{{ $timeline->id }}"
                        class="btn btn-sm bg-gray-200 hover:bg-gray-300 text-gray-800 mr-2">Cancel</label>
                    <button type="submit" class="btn btn-sm bg-blue-500 hover:bg-blue-600 text-white">Update</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

@if (session('status'))
    <div id="success-alert"
        class="alert alert-success fixed bottom-5 right-5 w-96 z-50 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md shadow-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">{{ session('status') }}</p>
            </div>
        </div>
    </div>
@endif

@if (session('error'))
    <div id="error-alert"
        class="alert alert-error fixed bottom-5 right-5 w-96 z-50 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

```
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const itemsPerPage = 20;
        let currentPage = 1;
        const timelineCards = document.querySelectorAll('.timeline-card');
        const totalPages = Math.ceil(timelineCards.length / itemsPerPage);

        function showPage(page) {
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;

            timelineCards.forEach((card, index) => {
                card.style.display = (index >= start && index < end) ? '' : 'none';
            });
        }

        function setupPagination() {
            const paginationElement = document.getElementById('pagination');
            paginationElement.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const button = document.createElement('button');
                button.innerText = i;
                button.classList.add('btn', 'btn-sm');
                if (i === currentPage) {
                    button.classList.add('btn-active');
                }
                button.addEventListener('click', () => {
                    currentPage = i;
                    showPage(currentPage);
                    setupPagination();
                });
                paginationElement.appendChild(button);
            }
        }

        showPage(currentPage);
        setupPagination();

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            timelineCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        document.querySelectorAll('.type-select').forEach(function(selectElem) {
            var form = selectElem.closest('form');
            if (!form) return;
            var wrapper = form.querySelector('.last-biannual-wrapper');
            if (!wrapper) return;

            function toggleWrapper() {
                if (selectElem.value === 'Bi-Annual') {
                    wrapper.style.display = 'block';
                } else {
                    wrapper.style.display = 'none';
                    var checkbox = wrapper.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = false;
                }
            }

            toggleWrapper();
            selectElem.addEventListener('change', toggleWrapper);
        });

        // Auto-hide alerts after 3 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.display = 'none';
            });
        }, 3000);
    });

    function confirmDelete(timelineId) {
        if (confirm("Are you sure you want to delete this timeline?")) {
            document.getElementById('delete-form-' + timelineId).submit();
        }
    }
</script>
