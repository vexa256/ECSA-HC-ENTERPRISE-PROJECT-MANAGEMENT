<div class="page">
    <div class="page-wrapper">
        <div class="container-xl">
            <!-- Page title -->
            <div class="page-header d-print-none">
                <div class="row align-items-center">
                    <div class="col">
                        <h2 class="page-title">
                            Staff Management
                        </h2>
                    </div>
                    <div class="col-auto ms-auto d-print-none">
                        <div class="btn-list">
                            <a href="{{ route('staff_management.create') }}"
                                class="btn btn-primary d-none d-sm-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                                Add New Staff
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page body -->
            <div class="page-body">
                <div class="card">
                    <div class="card-body">
                        @if (session('notification'))
                            <div class="alert alert-{{ session('notification')['type'] }} alert-dismissible"
                                role="alert">
                                {{ session('notification')['message'] }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if ($viewAction === 'list')
                            <div class="table-responsive">
                                <table class="table table-vcenter card-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Position</th>
                                            <th>Role</th>
                                            <th class="w-1"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($staff as $member)
                                            <tr>
                                                <td>{{ $member->name }}</td>
                                                <td class="text-muted">{{ $member->email }}</td>
                                                <td class="text-muted">{{ $member->JobTitle }}</td>
                                                <td class="text-muted">{{ $member->HR_role }}</td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="{{ route('staff_management.edit', $member->id) }}"
                                                            class="btn btn-sm btn-outline-secondary">Edit</a>
                                                        <form
                                                            action="{{ route('staff_management.destroy', $member->id) }}"
                                                            method="POST"
                                                            onsubmit="return confirm('Are you sure you want to delete this staff member?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="btn btn-sm btn-outline-danger">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($viewAction === 'create' || $viewAction === 'edit')
                            <form
                                action="{{ $viewAction === 'create' ? route('staff_management.store') : route('staff_management.update', $staffMember->id ?? '') }}"
                                method="POST">
                                @csrf
                                @if ($viewAction === 'edit')
                                    @method('PUT')
                                @endif
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ old('name', $staffMember->name ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email"
                                            value="{{ old('email', $staffMember->email ?? '') }}" required>
                                    </div>
                                    @if ($viewAction === 'create')
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" class="form-control" name="password" required>
                                        </div>
                                    @endif
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="HR_first_name"
                                            value="{{ old('HR_first_name', $staffMember->HR_first_name ?? '') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="HR_last_name"
                                            value="{{ old('HR_last_name', $staffMember->HR_last_name ?? '') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Position</label>
                                        <select class="form-select" name="HR_position_id" required>
                                            <option value="">Select a position</option>
                                            @foreach ($positions as $position)
                                                <option value="{{ $position->id }}"
                                                    {{ old('HR_position_id', $staffMember->HR_position_id ?? '') == $position->id ? 'selected' : '' }}>
                                                    {{ $position->position_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Job Title</label>
                                        <input type="text" class="form-control" name="JobTitle"
                                            value="{{ old('JobTitle', $staffMember->JobTitle ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Role</label>
                                        <select class="form-select" name="HR_role" required>
                                            <option value="">Select a role</option>
                                            @foreach (['Supervisor', 'Non-Supervisor', 'Admin', 'Cluster Head', 'HR-Account', 'Corporate Account'] as $role)
                                                <option value="{{ $role }}"
                                                    {{ old('HR_role', $staffMember->HR_role ?? '') == $role ? 'selected' : '' }}>
                                                    {{ $role }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Hire Date</label>
                                        <input type="date" class="form-control" name="HR_hire_date"
                                            value="{{ old('HR_hire_date', $staffMember->HR_hire_date ?? '') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Department</label>
                                        <input type="text" class="form-control" name="HR_department"
                                            value="{{ old('HR_department', $staffMember->HR_department ?? '') }}">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Cluster</label>
                                        <select class="form-select" name="ClusterID" required>
                                            <option value="">Select a cluster</option>
                                            @foreach ($clusters as $cluster)
                                                <option value="{{ $cluster->ClusterID }}"
                                                    {{ old('ClusterID', $staffMember->ClusterID ?? '') == $cluster->ClusterID ? 'selected' : '' }}>
                                                    {{ $cluster->Cluster_Name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Account Role</label>
                                        <select class="form-select" name="AccountRole">
                                            <option value="">Select an account role</option>
                                            @foreach (['Admin', 'User', 'Cluster Head', 'HR-Account', 'Corporate Account'] as $role)
                                                <option value="{{ $role }}"
                                                    {{ old('AccountRole', $staffMember->AccountRole ?? '') == $role ? 'selected' : '' }}>
                                                    {{ $role }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <input type="hidden" name="UserType" value="ECSA-HR">
                                <div class="form-footer">
                                    <button type="submit"
                                        class="btn btn-primary">{{ $viewAction === 'create' ? 'Create Staff' : 'Update Staff' }}</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (this.checkValidity()) {
                    this.submit();
                } else {
                    e.stopPropagation();
                    this.classList.add('was-validated');
                }
            });
        }

        // Handle notifications
        const notification = @json(session('notification'));
        if (notification) {
            const alertElement = document.querySelector('.alert');
            if (alertElement) {
                setTimeout(() => {
                    alertElement.classList.add('fade');
                    setTimeout(() => {
                        alertElement.remove();
                    }, 300);
                }, 5000);
            }
        }
    });
</script>

<style>
    .btn-group {
        display: flex;
    }

    .btn-group form {
        margin-left: 5px;
    }
</style>
