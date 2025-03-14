<div class="container mx-auto px-4 py-8 bg-gradient-to-br from-base-100 to-base-200">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 space-y-4 md:space-y-0">
        <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary">
            Entities
        </h1>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text" id="search-input" placeholder="Search entities..."
                    class="input input-bordered input-sm pr-10 w-full max-w-xs" />
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <button class="btn btn-sm btn-neutral shadow-lg hover:shadow-xl transition-all duration-300"
                onclick="add_entity_modal.showModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add New Entity
            </button>
        </div>
    </div>

    <div id="entities-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($entities as $entity)
            <div class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all duration-300">
                <div class="card-body">
                    <h2 class="card-title text-primary">{{ $entity->Entity }}</h2>
                    <p class="text-base-content/70">{{ Str::limit($entity->EntityProjectDetails, 100) }}</p>
                    <div class="card-actions justify-end mt-4">
                        <button class="btn btn-sm btn-outline shadow-md hover:shadow-lg transition-all duration-300"
                            onclick="edit_entity_modal_{{ $entity->id }}.showModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </button>
                        <button
                            class="btn btn-sm btn-outline btn-error shadow-md hover:shadow-lg transition-all duration-300"
                            onclick="confirmDelete('{{ $entity->id }}')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Trash
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full flex items-center justify-center h-64 bg-base-200 rounded-box shadow-inner">
                <p class="text-xl text-base-content/50">No entities found</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Add New Entity Modal -->
<dialog id="add_entity_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-base-100 shadow-2xl">
        <h3 class="font-bold text-2xl mb-6 text-primary">Add New Entity</h3>
        <form action="{{ route('MassInsert') }}" method="POST" id="addEntityForm" class="space-y-4">
            @csrf
            <input type="hidden" name="TableName" value="mpa_entities">
            <div class="form-control">
                <label class="label" for="Entity">
                    <span class="label-text font-medium">Entity Name</span>
                </label>
                <input type="text" id="Entity" name="Entity" class="input input-bordered w-full" required>
            </div>
            <div class="form-control">
                <label class="label" for="EntityID">
                    <span class="label-text font-medium">Entity ID</span>
                </label>
                <input readonly value="{{ md5(uniqid() . strtotime('now')) }}" type="text" id="EntityID"
                    name="EntityID" class="input input-bordered w-full bg-gray-100" required>
            </div>
            <div class="form-control">
                <label class="label" for="EntityProjectDetails">
                    <span class="label-text font-medium">Details</span>
                </label>
                <textarea id="EntityProjectDetails" name="EntityProjectDetails" class="textarea textarea-bordered h-24"></textarea>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-sm btn-outline shadow-md"
                    onclick="add_entity_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-sm btn-neutral shadow-md">Save</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Edit Entity Modals -->
@foreach ($entities as $entity)
    <dialog id="edit_entity_modal_{{ $entity->id }}" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-base-100 shadow-2xl">
            <h3 class="font-bold text-2xl mb-6 text-primary">Edit Entity</h3>
            <form action="{{ route('MassUpdate') }}" method="POST" id="editEntityForm-{{ $entity->id }}"
                class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $entity->id }}">
                <input type="hidden" name="TableName" value="mpa_entities">
                <div class="form-control">
                    <label class="label" for="Entity_{{ $entity->id }}">
                        <span class="label-text font-medium">Entity Name</span>
                    </label>
                    <input type="text" id="Entity_{{ $entity->id }}" name="Entity"
                        value="{{ $entity->Entity }}" class="input input-bordered w-full" required>
                </div>
                <div class="form-control">
                    <label class="label" for="EntityProjectDetails_{{ $entity->id }}">
                        <span class="label-text font-medium">Details</span>
                    </label>
                    <textarea id="EntityProjectDetails_{{ $entity->id }}" name="EntityProjectDetails"
                        class="textarea textarea-bordered h-24">{{ $entity->EntityProjectDetails }}</textarea>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-sm btn-outline shadow-md"
                        onclick="edit_entity_modal_{{ $entity->id }}.close()">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-neutral shadow-md">Update</button>
                </div>
            </form>
        </div>
    </dialog>
@endforeach

<!-- Delete Confirmation Modal -->
<dialog id="delete_confirm_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-base-100 shadow-2xl">
        <h3 class="font-bold text-2xl mb-4 text-error">Confirm Deletion</h3>
        <p class="py-4 text-base-content/70">Are you sure you want to delete this entity? This action cannot be undone.
        </p>
        <div class="modal-action">
            <form id="delete-form" action="{{ route('MassDelete') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" id="delete-id">
                <input type="hidden" name="TableName" value="mpa_entities">
                <button type="button" class="btn btn-sm btn-outline shadow-md mr-2"
                    onclick="delete_confirm_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-sm btn-outline btn-error shadow-md">Delete</button>
            </form>
        </div>
    </div>
</dialog>

<script>
    function confirmDelete(entityId) {
        const deleteForm = document.getElementById('delete-form');
        const deleteIdInput = document.getElementById('delete-id');
        deleteIdInput.value = entityId;
        delete_confirm_modal.showModal();
    }

    document.addEventListener('DOMContentLoaded', (event) => {
        const searchInput = document.getElementById('search-input');
        const entitiesGrid = document.getElementById('entities-grid');
        const entities = entitiesGrid.querySelectorAll('.card');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            entities.forEach(entity => {
                const title = entity.querySelector('.card-title').textContent.toLowerCase();
                const description = entity.querySelector('p').textContent.toLowerCase();

                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    entity.style.display = '';
                    entity.style.opacity = '1';
                    entity.style.transform = 'scale(1)';
                } else {
                    entity.style.opacity = '0';
                    entity.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        entity.style.display = 'none';
                    }, 300);
                }
            });

            const visibleEntities = entitiesGrid.querySelectorAll('.card[style="display: "";"]');
            const noResultsMessage = entitiesGrid.querySelector('.no-results-message');

            if (visibleEntities.length === 0) {
                if (!noResultsMessage) {
                    const message = document.createElement('div');
                    message.className =
                        'col-span-full flex items-center justify-center h-64 bg-base-200 rounded-box shadow-inner no-results-message';
                    message.innerHTML =
                        '<p class="text-xl text-base-content/50">No matching entities found</p>';
                    entitiesGrid.appendChild(message);
                }
            } else if (noResultsMessage) {
                noResultsMessage.remove();
            }
        });
    });
</script>
