<div class="container mx-auto px-4 py-8 bg-gradient-to-br from-base-100 to-base-200">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 space-y-4 md:space-y-0">
        <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary">
            Strategic Objectives
        </h1>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text" id="search-input" placeholder="Search objectives..." class="input input-bordered input-sm pr-10 w-full max-w-xs" />
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <button class="btn btn-sm btn-neutral shadow-lg hover:shadow-xl transition-all duration-300"
                onclick="add_strategic_objective_modal.showModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add New Objective
            </button>
        </div>
    </div>

    <div id="objectives-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($strategicObjectives as $objective)
            <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300">
                <div class="card-body">
                    <h2 class="card-title text-primary">{{ $objective->SO_Number }}</h2>
                    <p class="text-base-content/70">{{ Str::limit($objective->Description, 100) }}</p>
                    <div class="card-actions justify-end mt-4">
                        <button class="btn btn-sm btn-outline shadow-md hover:shadow-lg transition-all duration-300"
                            onclick="view_more_dialog_{{ $objective->id }}.showModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View
                        </button>
                        <button class="btn btn-sm btn-outline shadow-md hover:shadow-lg transition-all duration-300"
                            onclick="edit_strategic_objective_modal_{{ $objective->id }}.showModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </button>
                        <button
                            class="btn btn-sm btn-outline btn-error shadow-md hover:shadow-lg transition-all duration-300"
                            onclick="confirmDelete('{{ $objective->id }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full flex items-center justify-center h-64 bg-base-200 rounded-box shadow-inner">
                <p class="text-xl text-base-content/50">No strategic objectives found</p>
            </div>
        @endforelse
    </div>
</div>

<!-- ... (previous modals remain unchanged) ... -->

<!-- View More Details Dialogs -->
@foreach ($strategicObjectives as $objective)
    <dialog id="view_more_dialog_{{ $objective->id }}" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-base-100 shadow-2xl">
            <h3 class="font-bold text-2xl mb-6 text-primary">Strategic Objective Details</h3>
            <div class="space-y-4">
                <div class="bg-base-200 p-4 rounded-lg">
                    <h4 class="font-semibold text-lg mb-2">SO Number</h4>
                    <p class="text-base-content/70">{{ $objective->SO_Number }}</p>
                </div>
                <div class="bg-base-200 p-4 rounded-lg">
                    <h4 class="font-semibold text-lg mb-2">SO Name</h4>
                    <p class="text-base-content/70">{{ $objective->SO_Name }}</p>
                </div>
                <div class="bg-base-200 p-4 rounded-lg">
                    <h4 class="font-semibold text-lg mb-2">Description</h4>
                    <p class="text-base-content/70">{{ $objective->Description }}</p>
                </div>
            </div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-sm btn-neutral shadow-md">Close</button>
                </form>
            </div>
        </div>
    </dialog>
@endforeach

<script>
    function confirmDelete(objectiveId) {
        const deleteForm = document.getElementById('delete-form');
        const deleteIdInput = document.getElementById('delete-id');
        deleteIdInput.value = objectiveId;
        delete_confirm_modal.showModal();
    }

    document.addEventListener('DOMContentLoaded', (event) => {
        @if (session('status'))
            document.getElementById('status-message').textContent = "{{ session('status') }}";
            status_dialog.showModal();
        @endif

        @if (session('error'))
            document.getElementById('error-message').textContent = "{{ session('error') }}";
            error_dialog.showModal();
        @endif

        // Search functionality
        const searchInput = document.getElementById('search-input');
        const objectivesGrid = document.getElementById('objectives-grid');
        const objectives = objectivesGrid.querySelectorAll('.card');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            objectives.forEach(objective => {
                const title = objective.querySelector('.card-title').textContent.toLowerCase();
                const description = objective.querySelector('p').textContent.toLowerCase();

                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    objective.style.display = '';
                    objective.style.opacity = '1';
                    objective.style.transform = 'scale(1)';
                } else {
                    objective.style.opacity = '0';
                    objective.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        objective.style.display = 'none';
                    }, 300);
                }
            });

            // Check if there are any visible objectives
            const visibleObjectives = objectivesGrid.querySelectorAll('.card[style="display: "";"]');
            const noResultsMessage = objectivesGrid.querySelector('.no-results-message');

            if (visibleObjectives.length === 0) {
                if (!noResultsMessage) {
                    const message = document.createElement('div');
                    message.className = 'col-span-full flex items-center justify-center h-64 bg-base-200 rounded-box shadow-inner no-results-message';
                    message.innerHTML = '<p class="text-xl text-base-content/50">No matching objectives found</p>';
                    objectivesGrid.appendChild(message);
                }
            } else if (noResultsMessage) {
                noResultsMessage.remove();
            }
        });
    });
</script>