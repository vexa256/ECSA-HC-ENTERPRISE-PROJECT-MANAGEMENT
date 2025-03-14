<!-- Keep the header exactly as provided -->
<header class="sticky top-0 z-30 w-full bg-white shadow-sm">
    <div class="flex h-16 items-center justify-end px-4 md:px-6">
        <!-- User Menu -->
        <div class="relative">
            <button class="flex items-center space-x-2 p-2 rounded-full hover:bg-gray-100" id="userButton">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=John" alt="User"
                    class="w-8 h-8 rounded-full" />
                <span class="iconify w-4 h-4 text-gray-600" data-icon="lucide:chevron-down"></span>
            </button>
            <div id="userDropdown"
                class="hidden absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg py-1 z-10 border border-gray-200">
                <a href="#"
                    onclick="event.preventDefault(); document.getElementById('profile-modal').showModal();"
                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <span class="iconify w-5 h-5 mr-3" data-icon="lucide:user"></span>
                    Profile
                </a>
                <a href="#"
                    onclick="event.preventDefault(); document.getElementById('update-account-modal').showModal();"
                    class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <span class="iconify w-5 h-5 mr-3" data-icon="lucide:settings"></span>
                    Update Account
                </a>

                <div class="border-t border-gray-200 my-1"></div>
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-modal').showModal();"
                    class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                    <span class="iconify w-5 h-5 mr-3" data-icon="lucide:log-out"></span>
                    Logout
                </a>
            </div>
        </div>
    </div>
</header>

<!-- All modals and forms outside the header structure -->
@php
    // Get current authenticated user
    $user = auth()->user();
    $isECSAHC = $user && $user->UserType === 'ECSA-HC';
    $isMPA = $user && $user->UserType === 'MPA';

    // Get any flash messages or errors
    $errors = session('errors') ? session('errors')->getBag('default') : collect();
    $status = session('status');
    $errorMessage = session('error');
@endphp

{{-- Logout Form (Hidden) --}}
<form id="logout-form-hidden" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>

{{-- Profile Modal --}}
<dialog id="profile-modal" class="modal">
    <form method="dialog" class="modal-box w-11/12 max-w-3xl">
        <h3 class="font-bold text-lg mb-4">User Profile</h3>
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="card bg-base-200 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Personal Information</h2>

                    <div class="flex flex-col space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold">Name:</span>
                            <span>{{ $user->name ?? 'Not set' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="font-semibold">Email:</span>
                            <span>{{ $user->email ?? 'Not set' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="font-semibold">Phone:</span>
                            <span>{{ $user->Phone ?? ($user->PhoneNumber ?? 'Not set') }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="font-semibold">Nationality:</span>
                            <span>{{ $user->Nationality ?? 'Not set' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="font-semibold">Gender:</span>
                            <span>{{ $user->Sex ?? 'Not set' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="font-semibold">Address:</span>
                            <span>{{ $user->Address ?? 'Not set' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-200 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">Account Information</h2>

                    <div class="flex flex-col space-y-2">
                        <div class="flex justify-between">
                            <span class="font-semibold">User Type:</span>
                            <span
                                class="badge {{ $isECSAHC ? 'badge-primary' : ($isMPA ? 'badge-secondary' : 'badge-ghost') }}">
                                {{ $user->UserType ?? 'Not set' }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="font-semibold">Account Role:</span>
                            <span>{{ $user->AccountRole ?? 'Not set' }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="font-semibold">Job Title:</span>
                            <span>{{ $user->JobTitle ?? 'Not set' }}</span>
                        </div>

                        @if ($user->ClusterID)
                            <div class="flex justify-between">
                                <span class="font-semibold">Cluster ID:</span>
                                <span>{{ $user->ClusterID }}</span>
                            </div>
                        @endif

                        @if ($user->EntityID)
                            <div class="flex justify-between">
                                <span class="font-semibold">Entity ID:</span>
                                <span>{{ $user->EntityID }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between">
                            <span class="font-semibold">Parent Organization:</span>
                            <span>{{ $user->ParentOrganization ?? 'Not set' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-action">
            <button class="btn btn-primary"
                onclick="document.getElementById('update-account-modal').showModal(); document.getElementById('profile-modal').close()">
                Update Account
            </button>
            <button class="btn">Close</button>
        </div>
    </form>
</dialog>

{{-- Update Account Modal --}}
<dialog id="update-account-modal" class="modal">
    <div class="modal-box w-11/12 max-w-3xl">
        <h3 class="font-bold text-lg mb-4">Update Account</h3>
        <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
            onclick="document.getElementById('update-account-modal').close()">✕</button>

        @if ($status)
            <div class="alert alert-success mb-4">
                {{ $status }}
            </div>
        @endif

        @if ($errorMessage)
            <div class="alert alert-error mb-4">
                {{ $errorMessage }}
            </div>
        @endif

        {{-- Form that submits to the controller --}}
        <form action="{{ route('user.update-account') }}" method="POST" class="space-y-4" id="update-account-form">
            @csrf
            {{-- Include the current URL as a hidden field to redirect back after processing --}}
            <input type="hidden" name="redirect_url" value="{{ url()->current() }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Personal Information --}}
                <div class="space-y-4">
                    <h4 class="font-semibold text-md">Personal Information</h4>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Full Name</span>
                        </label>
                        <input type="text" name="name"
                            class="input input-bordered @if ($errors->has('name')) input-error @endif"
                            value="{{ old('name', $user->name) }}" required>
                        @if ($errors->has('name'))
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $errors->first('name') }}</span>
                            </label>
                        @endif
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input type="email" name="email"
                            class="input input-bordered @if ($errors->has('email')) input-error @endif"
                            value="{{ old('email', $user->email) }}" required>
                        @if ($errors->has('email'))
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $errors->first('email') }}</span>
                            </label>
                        @endif
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Phone Number</span>
                        </label>
                        <input type="text" name="Phone" class="input input-bordered"
                            value="{{ old('Phone', $user->Phone ?? '') }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Nationality</span>
                        </label>
                        <input type="text" name="Nationality" class="input input-bordered"
                            value="{{ old('Nationality', $user->Nationality ?? '') }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Gender</span>
                        </label>
                        <select name="Sex" class="select select-bordered w-full">
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('Sex', $user->Sex) === 'Male' ? 'selected' : '' }}>Male
                            </option>
                            <option value="Female" {{ old('Sex', $user->Sex) === 'Female' ? 'selected' : '' }}>Female
                            </option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Address</span>
                        </label>
                        <textarea name="Address" class="textarea textarea-bordered" rows="3">{{ old('Address', $user->Address ?? '') }}</textarea>
                    </div>
                </div>

                {{-- Account Information --}}
                <div class="space-y-4">
                    <h4 class="font-semibold text-md">Account Information</h4>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Job Title</span>
                        </label>
                        <input type="text" name="JobTitle" class="input input-bordered"
                            value="{{ old('JobTitle', $user->JobTitle ?? '') }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Parent Organization</span>
                        </label>
                        <input type="text" name="ParentOrganization" class="input input-bordered"
                            value="{{ old('ParentOrganization', $user->ParentOrganization ?? '') }}">
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Current Password</span>
                            <span class="label-text-alt">Required to change password</span>
                        </label>
                        <input type="password" name="current_password" id="current_password"
                            class="input input-bordered @if ($errors->has('current_password')) input-error @endif">
                        @if ($errors->has('current_password'))
                            <label class="label">
                                <span
                                    class="label-text-alt text-error">{{ $errors->first('current_password') }}</span>
                            </label>
                        @endif
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">New Password</span>
                            <span class="label-text-alt">Leave blank to keep current password</span>
                        </label>
                        <input type="password" name="password" id="new_password"
                            class="input input-bordered @if ($errors->has('password')) input-error @endif">
                        @if ($errors->has('password'))
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $errors->first('password') }}</span>
                            </label>
                        @endif
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Confirm New Password</span>
                        </label>
                        <input type="password" name="password_confirmation" id="confirm_password"
                            class="input input-bordered @if ($errors->has('password_confirmation')) input-error @endif">
                        @if ($errors->has('password_confirmation'))
                            <label class="label">
                                <span
                                    class="label-text-alt text-error">{{ $errors->first('password_confirmation') }}</span>
                            </label>
                        @endif
                    </div>

                    <div id="password-error" class="text-error hidden">
                        Passwords do not match
                    </div>
                </div>
            </div>

            <div class="modal-action">
                <button type="submit" class="btn btn-primary" id="update-btn">Save Changes</button>
                <button type="button" class="btn"
                    onclick="document.getElementById('update-account-modal').close()">Cancel</button>
            </div>
        </form>
    </div>
</dialog>

{{-- Logout Modal --}}
<dialog id="logout-modal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Confirm Logout</h3>
        <p class="py-4">Are you sure you want to logout?</p>
        <div class="modal-action">
            <button class="btn btn-error"
                onclick="document.getElementById('logout-form-hidden').submit();">Logout</button>
            <button class="btn" onclick="document.getElementById('logout-modal').close()">Cancel</button>
        </div>
    </div>
</dialog>

{{-- Inline script to toggle dropdown menu - using the existing structure --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const passwordError = document.getElementById('password-error');
        const updateBtn = document.getElementById('update-btn');

        if (newPasswordInput && confirmPasswordInput && passwordError && updateBtn) {
            function validatePasswords() {
                if (newPasswordInput.value && newPasswordInput.value !== confirmPasswordInput.value) {
                    passwordError.classList.remove('hidden');
                    updateBtn.disabled = true;
                    return false;
                } else {
                    passwordError.classList.add('hidden');
                    updateBtn.disabled = false;
                    return true;
                }
            }

            newPasswordInput.addEventListener('input', validatePasswords);
            confirmPasswordInput.addEventListener('input', validatePasswords);

            // Form submission validation
            const updateForm = document.getElementById('update-account-form');
            if (updateForm) {
                updateForm.addEventListener('submit', function(e) {
                    if (newPasswordInput.value && !validatePasswords()) {
                        e.preventDefault();
                        return false;
                    }

                    // If current password is empty but new password is provided
                    const currentPassword = document.getElementById('current_password');
                    if (newPasswordInput.value && currentPassword && !currentPassword.value) {
                        e.preventDefault();
                        alert('Please enter your current password to change your password');
                        return false;
                    }

                    return true;
                });
            }
        }

        // // Show modals if there are errors after form submission
        // @if ($errors->count() > 0 || $errorMessage)
        //     const updateModal = document.getElementById('update-account-modal');
        //     if (updateModal) {
        //         updateModal.showModal();
        //     }
        // @endif

        @if ($status)
            // If update was successful, show success message and close modal after delay
            setTimeout(function() {
                const updateModal = document.getElementById('update-account-modal');
                if (updateModal) {
                    updateModal.close();
                }
            }, 2000);
        @endif
    });
</script>
