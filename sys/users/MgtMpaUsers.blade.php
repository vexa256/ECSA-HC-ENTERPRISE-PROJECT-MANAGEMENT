<div class="container mx-auto px-4 py-8 bg-gradient-to-br from-base-100 to-base-200">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 space-y-4 md:space-y-0">
        <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-primary to-secondary">
            User Management
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
            <button class="btn btn-sm btn-neutral shadow-lg hover:shadow-xl transition-all duration-300"
                onclick="add_user_modal.showModal()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Add New User
            </button>
        </div>
    </div>

    <div class="overflow-x-auto bg-base-100 shadow-xl rounded-box">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Entity</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="users-table-body">
                @foreach ($users as $user)
                    <tr class="hover">
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->Entity }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->Phone }}</td>
                        <td>
                            <div class="flex space-x-2">
                                <button
                                    class="btn btn-sm btn-outline shadow-md hover:shadow-lg transition-all duration-300"
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
                                <button
                                    class="btn btn-sm btn-outline shadow-md hover:shadow-lg transition-all duration-300"
                                    onclick="edit_user_modal_{{ $user->id }}.showModal()">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit
                                </button>
                                <button
                                    class="btn btn-sm btn-outline btn-error shadow-md hover:shadow-lg transition-all duration-300"
                                    onclick="confirmDelete('{{ $user->id }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Trash
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Add New User Modal -->
<dialog id="add_user_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-base-100 shadow-2xl">
        <h3 class="font-bold text-2xl mb-6 text-primary">Add New User</h3>
        <form action="{{ route('MassInsert') }}" method="POST" id="addUserForm" class="space-y-4">
            @csrf
            <input type="hidden" name="TableName" value="users">
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
                    <input type="email" id="email" name="email" class="input input-bordered w-full" required>
                </div>
                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text font-medium">Password</span>
                    </label>
                    <input type="password" id="password" name="password" class="input input-bordered w-full" required>
                </div>
                <div class="form-control">
                    <label class="label" for="EntityID">
                        <span class="label-text font-medium">Entity</span>
                    </label>
                    <select id="EntityID" name="EntityID" class="select select-bordered w-full">
                        <option value="">Select Entity</option>
                        @foreach ($entities as $entity)
                            <option value="{{ $entity->EntityID }}">{{ $entity->EntityID }}</option>
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
                        <option value="Cluster Head">Cluster Head</option>
                    </select>
                </div>
            </div>
            <div class="form-control">
                <label class="label" for="Address">
                    <span class="label-text font-medium">Address</span>
                </label>
                <textarea id="Address" name="Address" class="textarea textarea-bordered h-24"></textarea>
            </div>
            <input type="hidden" name="UserType" value="MPA">
            <input type="hidden" name="UserCode" value="{{ md5(uniqid() . date('now')) }}">
            <input type="hidden" name="UserID" value="{{ md5(uniqid() . date('now')) }}">
            <div class="modal-action">
                <button type="button" class="btn btn-sm btn-outline shadow-md"
                    onclick="add_user_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-sm btn-neutral shadow-md">Save</button>
            </div>
        </form>
    </div>
</dialog>

<!-- Edit User Modals -->
@foreach ($users as $user)
    <dialog id="edit_user_modal_{{ $user->id }}" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-base-100 shadow-2xl">
            <h3 class="font-bold text-2xl mb-6 text-primary">Edit User</h3>
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
                        <label class="label" for="EntityID-{{ $user->id }}">
                            <span class="label-text font-medium">Entity</span>
                        </label>
                        <select id="EntityID-{{ $user->id }}" name="EntityID"
                            class="select select-bordered w-full">
                            <option value="">Select Entity</option>
                            @foreach ($entities as $entity)
                                <option value="{{ $entity->EntityID }}"
                                    {{ $user->EntityID == $entity->EntityID ? 'selected' : '' }}>
                                    {{ $entity->Entity }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Add other fields similar to the add form, but with values from $user -->
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-sm btn-outline shadow-md"
                        onclick="edit_user_modal_{{ $user->id }}.close()">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-neutral shadow-md">Update</button>
                </div>
            </form>
        </div>
    </dialog>
@endforeach

<!-- View User Modals -->
@foreach ($users as $user)
    <dialog id="view_user_modal_{{ $user->id }}" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-base-100 shadow-2xl">
            <h3 class="font-bold text-2xl mb-6 text-primary">User Details</h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                    <div class="bg-base-200 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg mb-2">Name</h4>
                        <p class="text-base-content/70">{{ $user->name }}</p>
                    </div>
                    <div class="bg-base-200 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg mb-2">Email</h4>
                        <p class="text-base-content/70">{{ $user->email }}</p>
                    </div>
                    <div class="bg-base-200 p-4 rounded-lg">
                        <h4 class="font-semibold text-lg mb-2">Entity</h4>
                        <p class="text-base-content/70">{{ $user->Entity }}</p>
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
                    <button class="btn btn-sm btn-neutral shadow-md">Close</button>
                </form>
            </div>
        </div>
    </dialog>
@endforeach

<!-- Delete Confirmation Modal -->
<dialog id="delete_confirm_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box bg-base-100 shadow-2xl">
        <h3 class="font-bold text-2xl mb-4 text-error">Confirm Deletion</h3>
        <p class="py-4 text-base-content/70">Are you sure you want to delete this user? This action cannot be undone.
        </p>
        <div class="modal-action">
            <form id="delete-form" action="{{ route('MassDelete') }}" method="POST">
                @csrf
                @method('DELETE')
                <input type="hidden" name="id" id="delete-id">
                <input type="hidden" name="TableName" value="users">
                <button type="button" class="btn btn-sm btn-outline shadow-md mr-2"
                    onclick="delete_confirm_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-sm btn-outline btn-error shadow-md">Delete</button>
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
        const tableBody = document.getElementById('users-table-body');
        const rows = tableBody.querySelectorAll('tr');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            rows.forEach(row => {
                const id = row.cells[0].textContent.toLowerCase();
                const entity = row.cells[1].textContent.toLowerCase();
                const name = row.cells[2].textContent.toLowerCase();
                const email = row.cells[3].textContent.toLowerCase();
                const phone = row.cells[4].textContent.toLowerCase();

                if (id.includes(searchTerm) || entity.includes(searchTerm) || name.includes(
                        searchTerm) ||
                    email.includes(searchTerm) || phone.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // Check if there are any visible rows
            const visibleRows = tableBody.querySelectorAll('tr[style="display: "";"]');
            const noResultsRow = tableBody.querySelector('.no-results-row');

            if (visibleRows.length === 0) {
                if (!noResultsRow) {
                    const newRow = tableBody.insertRow();
                    newRow.className = 'no-results-row';
                    const cell = newRow.insertCell();
                    cell.colSpan = 6;
                    cell.className = 'text-center py-4';
                    cell.textContent = 'No matching users found';
                }
            } else if (noResultsRow) {
                noResultsRow.remove();
            }
        });
    });
</script>
