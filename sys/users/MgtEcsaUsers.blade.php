<div class="container mx-auto px-4 py-8 bg-base-200">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 space-y-4 md:space-y-0">
        <h1 class="text-4xl font-bold text-primary">
            ECSA-HC User Management
        </h1>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text" id="search-input" placeholder="Search users..."
                    class="input input-bordered input-sm pr-10 w-full max-w-xs" />
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-5 w-5 absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <button class="btn btn-outline btn-sm" onclick="add_user_modal.showModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add New User
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="users-grid">
        @foreach ($users as $user)
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title text-primary">{{ $user->name }}</h2>
                    <p class="text-secondary">{{ $user->Cluster_Name }}</p>
                    <p>{{ $user->email }}</p>
                    <p>{{ $user->Phone }}</p>
                    <div class="card-actions justify-end mt-4">
                        <button class="btn btn-outline btn-sm"
                            onclick="view_user_modal_{{ $user->id }}.showModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View
                        </button>
                        <button class="btn btn-outline btn-sm"
                            onclick="edit_user_modal_{{ $user->id }}.showModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit
                        </button>
                        <button class="btn btn-outline btn-error btn-sm" onclick="confirmDelete('{{ $user->id }}')">
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
        @endforeach
    </div>
</div>

<!-- Add New User Modal -->
<dialog id="add_user_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-base-100">
        <h3 class="font-bold text-2xl mb-6 text-primary">Add New ECSA-HC User</h3>
        <form action="{{ route('MassInsert') }}" method="POST" id="addUserForm" class="space-y-4">
            @csrf
            <input type="hidden" name="TableName" value="users">
            <input type="hidden" name="UserType" value="ECSA-HC">
            <input type="hidden" name="UserCode" value="{{ md5(uniqid() . date('now')) }}">
            <input type="hidden" name="UserID" value="{{ md5(uniqid() . date('now')) }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label" for="name">
                        <span class="label-text font-medium">Name</span>
                    </label>
                    <input type="text" id="name" name="name" class="input input-bordered w-full" required>
                </div>
                <div class="form-control">
                    <label class="label" for="email">
                        <span class="label-text font-medium">Email</span>
                    </label>
                    <input type="email" id="email" name="email" class="input input-bordered w-full"
                        required>
                </div>
                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text font-medium">Password</span>
                    </label>
                    <input type="password" id="password" name="password" class="input input-bordered w-full"
                        required>
                </div>
                <div class="form-control">
                    <label class="label" for="ClusterID">
                        <span class="label-text font-medium">Cluster</span>
                    </label>
                    <select id="ClusterID" name="ClusterID" class="select select-bordered w-full">
                        <option value="">Select Cluster</option>
                        @foreach ($clusters as $cluster)
                            <option value="{{ $cluster->ClusterID }}">{{ $cluster->Cluster_Name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label" for="Phone">
                        <span class="label-text font-medium">Phone</span>
                    </label>
                    <input type="text" id="Phone" name="Phone" class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <label class="label" for="Nationality">
                        <span class="label-text font-medium">Nationality</span>
                    </label>
                    <input type="text" id="Nationality" name="Nationality" class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <label class="label" for="Sex">
                        <span class="label-text font-medium">Sex</span>
                    </label>
                    <select id="Sex" name="Sex" class="select select-bordered w-full">
                        <option value="">Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-control">
                    <label class="label" for="JobTitle">
                        <span class="label-text font-medium">Job Title</span>
                    </label>
                    <input type="text" id="JobTitle" name="JobTitle" class="input input-bordered w-full">
                </div>
                <div class="form-control">
                    <label class="label" for="AccountRole">
                        <span class="label-text font-medium">Account Role</span>
                    </label>
                    <select id="AccountRole" name="AccountRole" class="select select-bordered w-full">
                        <option value="Admin">Admin</option>
                        <option value="User" selected>User</option>
                        <option value="Viewer">Viewer</option>
                    </select>
                </div>
            </div>
            <div class="form-control">
                <label class="label" for="Address">
                    <span class="label-text font-medium">Address</span>
                </label>
                <textarea id="Address" name="Address" class="textarea textarea-bordered h-24"></textarea>
            </div>
            <div class="modal-action">
                <button type="button" class="btn btn-outline btn-sm"
                    onclick="add_user_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-active btn-sm">Save</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Edit User Modals -->
@foreach ($users as $user)
    <dialog id="edit_user_modal_{{ $user->id }}" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-base-100">
            <h3 class="font-bold text-2xl mb-6 text-primary">Edit ECSA-HC User</h3>
            <form action="{{ route('MassUpdate') }}" method="POST" id="editUserForm-{{ $user->id }}"
                class="space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" value="{{ $user->id }}">
                <input type="hidden" name="TableName" value="users">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label" for="name-{{ $user->id }}">
                            <span class="label-text font-medium">Name</span>
                        </label>
                        <input type="text" id="name-{{ $user->id }}" name="name"
                            value="{{ $user->name }}" class="input input-bordered w-full" required>
                    </div>
                    <div class="form-control">
                        <label class="label" for="email-{{ $user->id }}">
                            <span class="label-text font-medium">Email</span>
                        </label>
                        <input type="email" id="email-{{ $user->id }}" name="email"
                            value="{{ $user->email }}" class="input input-bordered w-full" required>
                    </div>
                    <div class="form-control">
                        <label class="label" for="password-{{ $user->id }}">
                            <span class="label-text font-medium">Password (leave blank to keep current)</span>
                        </label>
                        <input type="password" id="password-{{ $user->id }}" name="password"
                            class="input input-bordered w-full">
                    </div>
                    <div class="form-control">
                        <label class="label" for="ClusterID-{{ $user->id }}">
                            <span class="label-text font-medium">Cluster</span>
                        </label>
                        <select id="ClusterID-{{ $user->id }}" name="ClusterID"
                            class="select select-bordered w-full">
                            <option value="">Select Cluster</option>
                            @foreach ($clusters as $cluster)
                                <option value="{{ $cluster->ClusterID }}"
                                    {{ $user->ClusterID == $cluster->ClusterID ? 'selected' : '' }}>
                                    {{ $cluster->Cluster_Name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label" for="Phone-{{ $user->id }}">
                            <span class="label-text font-medium">Phone</span>
                        </label>
                        <input type="text" id="Phone-{{ $user->id }}" name="Phone"
                            value="{{ $user->Phone }}" class="input input-bordered w-full">
                    </div>
                    <div class="form-control">
                        <label class="label" for="Nationality-{{ $user->id }}">
                            <span class="label-text font-medium">Nationality</span>
                        </label>
                        <input type="text" id="Nationality-{{ $user->id }}" name="Nationality"
                            value="{{ $user->Nationality }}" class="input input-bordered w-full">
                    </div>
                    <div class="form-control">
                        <label class="label" for="Sex-{{ $user->id }}">
                            <span class="label-text font-medium">Sex</span>
                        </label>
                        <select id="Sex-{{ $user->id }}" name="Sex" class="select select-bordered w-full">
                            <option value="">Select Sex</option>
                            <option value="Male" {{ $user->Sex == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ $user->Sex == 'Female' ? 'selected' : '' }}>Female</option>
                        </select>
                    </div>
                    <div class="form-control">
                        <label class="label" for="JobTitle-{{ $user->id }}">
                            <span class="label-text font-medium">Job Title</span>
                        </label>
                        <input type="text" id="JobTitle-{{ $user->id }}" name="JobTitle"
                            value="{{ $user->JobTitle }}" class="input input-bordered w-full">
                    </div>
                    <div class="form-control">
                        <label class="label" for="AccountRole-{{ $user->id }}">
                            <span class="label-text font-medium">Account Role</span>
                        </label>
                        <select id="AccountRole-{{ $user->id }}" name="AccountRole"
                            class="select select-bordered w-full">
                            <option value="Admin" {{ $user->AccountRole == 'Admin' ? 'selected' : '' }}>Admin
                            </option>
                            <option value="User" {{ $user->AccountRole == 'User' ? 'selected' : '' }}>User</option>
                            <option value="Viewer" {{ $user->AccountRole == 'Viewer' ? 'selected' : '' }}>Viewer
                            </option>
                        </select>
                    </div>
                </div>
                <div class="form-control">
                    <label class="label" for="Address-{{ $user->id }}">
                        <span class="label-text font-medium">Address</span>
                    </label>
                    <textarea id="Address-{{ $user->id }}" name="Address" class="textarea textarea-bordered h-24">{{ $user->Address }}</textarea>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-outline btn-sm"
                        onclick="edit_user_modal_{{ $user->id }}.close()">Cancel</button>
                    <button type="submit" class="btn btn-active btn-sm">Update</button>
                </div>
            </form>
        </div>
    </dialog>
@endforeach

<!-- View User Modals -->
@foreach ($users as $user)
    <dialog id="view_user_modal_{{ $user->id }}" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-base-100">
            <h3 class="font-bold text-2xl mb-6 text-primary">User Details</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-base-200 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg mb-2">Name</h4>
                        <p class="text-base-content/70">{{ $user->name }}</p>
                    </div>
                    <div class="bg-base-200 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg mb-2">Email</h4>
                        <p class="text-base-content/70">{{ $user->email }}</p>
                    </div>
                    <div class="bg-base-200 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg mb-2">Cluster</h4>
                        <p class="text-base-content/70">{{ $user->Cluster_Name }}</p>
                    </div>
                    <div class="bg-base-200 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg mb-2">Phone</h4>
                        <p class="text-base-content/70">{{ $user->Phone }}</p>
                    </div>
                    <div class="bg-base-200 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg mb-2">Job Title</h4>
                        <p class="text-base-content/70">{{ $user->JobTitle }}</p>
                    </div>
                    <div class="bg-base-200 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg mb-2">Account Role</h4>
                        <p class="text-base-content/70">{{ $user->AccountRole }}</p>
                    </div>
                </div>
                <div class="bg-base-200 p-4 rounded-lg">
                    <h4 class="font-semibold text-lg mb-2">Address</h4>
                    <p class="text-base-content/70">{{ $user->Address }}</p>
                </div>
            </div>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-outline btn-sm">Close</button>
                </form>
            </div>
        </div>
    </dialog>
@endforeach

<!-- Delete Confirmation Modal -->
<dialog id="delete_confirm_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-base-100">
        <h3 class="font-bold text-2xl mb-4 text-error">Confirm Deletion</h3>
        <p class="py-4 text-base-content/70">Are you sure you want to delete this user? This action cannot be undone.
        </p>
        <div class="modal-action">
            <form id="delete-form" action="{{ route('MassDelete') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" id="delete-id">
                <input type="hidden" name="TableName" value="users">
                <button type="button" class="btn btn-outline btn-sm mr-2"
                    onclick="delete_confirm_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-outline btn-error btn-sm">Delete</button>
            </form>
        </div>
    </div>
</dialog>

<script>
    function confirmDelete(userId) {
        const deleteForm = document.getElementById('delete-form');
        const deleteIdInput = document.getElementById('delete-id');
        deleteIdInput.value = userId;
        delete_confirm_modal.showModal();
    }

    document.addEventListener('DOMContentLoaded', (event) => {
        const searchInput = document.getElementById('search-input');
        const usersGrid = document.getElementById('users-grid');
        const userCards = usersGrid.querySelectorAll('.card');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            userCards.forEach(card => {
                const name = card.querySelector('.card-title').textContent.toLowerCase();
                const cluster = card.querySelector('.text-secondary').textContent.toLowerCase();
                const email = card.querySelector('p:nth-of-type(1)').textContent.toLowerCase();
                const phone = card.querySelector('p:nth-of-type(2)').textContent.toLowerCase();

                if (name.includes(searchTerm) || cluster.includes(searchTerm) ||
                    email.includes(searchTerm) || phone.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });

            // Check if there are any visible cards
            const visibleCards = usersGrid.querySelectorAll('.card[style="display: "";"]');
            const noResultsMessage = usersGrid.querySelector('.no-results-message');

            if (visibleCards.length === 0) {
                if (!noResultsMessage) {
                    const message = document.createElement('p');
                    message.className = 'no-results-message text-center py-4 text-base-content/70';
                    message.textContent = 'No matching users found';
                    usersGrid.appendChild(message);
                }
            } else if (noResultsMessage) {
                noResultsMessage.remove();
            }
        });
    });
</script>
