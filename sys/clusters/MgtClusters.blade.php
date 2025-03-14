@php
    use Illuminate\Support\Str;
@endphp

<div class="container mx-auto px-4 py-8 bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="flex flex-col space-y-4 lg:flex-row lg:justify-between lg:items-center mb-8">
        <h1 class="text-4xl font-bold tracking-tight text-gray-900">ECSA-HC Clusters</h1>
        <div class="flex flex-col sm:flex-row items-center space-y-4 sm:space-y-0 sm:space-x-4">
            <div class="relative w-full sm:w-auto">
                <input type="text" id="search" placeholder="Search clusters..."
                    class="input input-bordered w-full pr-10" oninput="filterClusters()">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <button class="btn btn-neutral text-white w-full sm:w-auto" onclick="add_entity_modal.showModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add Cluster
            </button>
        </div>
    </div>

    <div id="clusters-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse ($clusters as $cluster)
            <div
                class="cluster-card bg-white rounded-xl overflow-hidden transition-all duration-300 hover:-translate-y-1 border border-gray-200">
                <div class="p-6 space-y-4">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl font-semibold text-gray-800">{{ $cluster->Cluster_Name }}</h3>
                        <span class="animated-pill text-xs text-dark bg-default font-medium px-2 py-1 rounded-full">ID:
                            {{ $cluster->id }}</span>
                    </div>
                    <p class="text-gray-600 text-sm">{{ Str::limit($cluster->Description, 100) }}</p>
                    <div class="pt-4 border-t border-gray-200 flex justify-end space-x-2">
                        <button class="btn btn-outline btn-active btn-sm text-white"
                            onclick="edit_entity_modal_{{ $cluster->id }}.showModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Edit
                        </button>
                        <button class="btn btn-outline btn-error btn-sm text-white"
                            onclick="confirmDelete({{ $cluster->id }})">
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
            <div class="col-span-full text-center py-8 text-gray-500 bg-white rounded-xl shadow">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-xl font-medium">No clusters found</p>
                <p class="mt-2">Start by adding a new cluster</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Add New Entity Modal -->
<dialog id="add_entity_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-white">
        <h3 class="font-bold text-2xl mb-6 text-gray-800">Add New ECSA-HC Cluster</h3>
        <form action="{{ route('MassInsert') }}" method="POST">
            @csrf
            <input type="hidden" name="TableName" value="clusters">
            <div class="space-y-6">
                <div class="form-control">
                    <label class="label" for="Cluster_Name">
                        <span class="label-text">Cluster Name</span>
                    </label>
                    <input type="text" id="Cluster_Name" name="Cluster_Name" class="input input-bordered w-full"
                        required>
                </div>
                <div class="form-control">
                    <label class="label" for="ClusterID">
                        <span class="label-text">ClusterID</span>
                    </label>
                    <input readonly value="{{ md5(uniqid() . strtotime('now')) }}" type="text" id="ClusterID"
                        name="ClusterID" class="input input-bordered w-full bg-gray-100" required>
                </div>
                <div class="form-control">
                    <label class="label" for="Description">
                        <span class="label-text">Details</span>
                    </label>
                    <textarea id="Description" name="Description" class="textarea textarea-bordered h-24"></textarea>
                </div>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="add_entity_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-neutral text-white">Save</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Edit Entity Modals -->
@foreach ($clusters as $cluster)
    <dialog id="edit_entity_modal_{{ $cluster->id }}" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-white">
            <h3 class="font-bold text-2xl mb-6 text-gray-800">Edit Cluster</h3>
            <form action="{{ route('MassUpdate') }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $cluster->id }}">
                <input type="hidden" name="TableName" value="clusters">
                <div class="space-y-6">
                    <div class="form-control">
                        <label class="label" for="Cluster_Name_{{ $cluster->id }}">
                            <span class="label-text">Cluster Name</span>
                        </label>
                        <input type="text" id="Cluster_Name_{{ $cluster->id }}" name="Cluster_Name"
                            value="{{ $cluster->Cluster_Name }}" class="input input-bordered w-full" required>
                    </div>
                    <div class="form-control">
                        <label class="label" for="Description_{{ $cluster->id }}">
                            <span class="label-text">Details</span>
                        </label>
                        <textarea id="Description_{{ $cluster->id }}" name="Description" class="textarea textarea-bordered h-24">{{ $cluster->Description }}</textarea>
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-ghost"
                        onclick="edit_entity_modal_{{ $cluster->id }}.close()">Cancel</button>
                    <button type="submit" class="btn btn-neutral text-white">Update</button>
                </div>
            </form>
        </div>
    </dialog>
@endforeach

<!-- Delete Confirmation Modal -->
<dialog id="delete_confirm_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Confirm Deletion</h3>
        <p class="py-4">Are you sure you want to delete this cluster? This action cannot be undone.</p>
        <div class="modal-action">
            <form id="delete-form" action="{{ route('MassDelete') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" id="delete-id">
                <input type="hidden" name="TableName" value="clusters">
                <button type="button" class="btn btn-ghost" onclick="delete_confirm_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-error">Delete</button>
            </form>
        </div>
    </div>
</dialog>

<!-- Status Notification Dialog -->
<dialog id="status_dialog" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Success</h3>
        <p class="py-4" id="status-message"></p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-default">Close</button>
            </form>
        </div>
    </div>
</dialog>

<!-- Error Notification Dialog -->
<dialog id="error_dialog" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-error text-error-content">
        <h3 class="font-bold text-lg">Error</h3>
        <p class="py-4" id="error-message"></p>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-error">Close</button>
            </form>
        </div>
    </div>
</dialog>

<!-- Validation Errors Dialog -->
<dialog id="validation_errors_dialog" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-warning text-warning-content">
        <h3 class="font-bold text-lg">Validation Errors</h3>
        <ul class="py-4" id="validation-errors-list"></ul>
        <div class="modal-action">
            <form method="dialog">
                <button class="btn btn-warning">Close</button>
            </form>
        </div>
    </div>
</dialog>

<script>
    function filterClusters() {
        const searchTerm = document.getElementById('search').value.toLowerCase();
        const clusterCards = document.querySelectorAll('.cluster-card');

        clusterCards.forEach(card => {
            const clusterName = card.querySelector('h3').textContent.toLowerCase();
            const clusterDescription = card.querySelector('p').textContent.toLowerCase();

            if (clusterName.includes(searchTerm) || clusterDescription.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function confirmDelete(id) {
        const deleteForm = document.getElementById('delete-form');
        const deleteIdInput = document.getElementById('delete-id');
        deleteIdInput.value = id;
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

        @if ($errors->any())
            const validationErrorsList = document.getElementById('validation-errors-list');
            validationErrorsList.innerHTML = '';
            @foreach ($errors->all() as $error)
                const li = document.createElement('li');
                li.textContent = "{{ $error }}";
                validationErrorsList.appendChild(li);
            @endforeach
            validation_errors_dialog.showModal();
        @endif
    });
</script>

<style>
    .cluster-card {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        background: linear-gradient(135deg, #ffffff 0%, #f3f4f6 100%);
        transition: all 0.3s ease;
    }

    .cluster-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: translateY(-5px);
    }

    .animated-pill {
        /* background: linear-gradient(45deg, #4f46e5, #7c3aed); */
        animation: gradientShift 3s ease infinite;
        background-size: 200% 200%;
    }

    @keyframes gradientShift {
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

    .modal-box {
        max-height: 90vh;
        overflow-y: auto;
    }

    @media (max-width: 640px) {
        .modal-box {
            width: 90vw;
            max-width: none;
        }
    }
    }
</style>
