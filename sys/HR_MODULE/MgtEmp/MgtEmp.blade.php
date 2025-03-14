<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">Employee Management</h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
                <a href="{{ route('staff_management.create') }}" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Add Employee
                </a>
            </div>
        </div>
    </div>

    @if (session('notification'))
        <div id="notification"
            class="alert alert-{{ session('notification')['type'] == 'success' ? 'success' : 'danger' }} alert-dismissible"
            role="alert">
            {{ session('notification')['message'] }}
            <a href="#" class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    @endif

    @if (isset($viewAction) && $viewAction == 'list')
        <div class="row row-cards">
            @foreach ($employees as $employee)
                <div class="col-md-6 col-lg-3">
                    <div class="card">
                        <div class="card-body p-4 text-center">
                            <h3 class="m-0 mb-1">{{ $employee->FirstName }} {{ $employee->LastName }}</h3>
                            <div class="text-muted">{{ $employee->JobTitle }}</div>
                            <div class="mt-3">
                                <span class="badge bg-blue-lt">{{ $employee->email }}</span>
                            </div>
                        </div>
                        <div class="d-flex">
                            <a href="{{ route('staff_management.edit', $employee->EmployeeID) }}" class="card-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24"
                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M9 7h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />
                                    <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />
                                    <line x1="16" y1="5" x2="19" y2="8" />
                                </svg>
                                Edit
                            </a>
                            <form action="{{ route('staff_management.destroy', $employee->EmployeeID) }}" method="POST"
                                class="card-btn">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-link"
                                    onclick="return confirm('Are you sure you want to delete this employee?')">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-danger" width="24"
                                        height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <line x1="4" y1="7" x2="20" y2="7" />
                                        <line x1="10" y1="11" x2="10" y2="17" />
                                        <line x1="14" y1="11" x2="14" y2="17" />
                                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                    </svg>
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif(isset($viewAction) && ($viewAction == 'create' || $viewAction == 'edit'))
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $viewAction == 'create' ? 'Add New Employee' : 'Edit Employee' }}</h3>
            </div>
            <div class="card-body">
                <form id="employeeForm"
                    action="{{ $viewAction == 'create' ? route('staff_management.store') : route('staff_management.update', $employee->EmployeeID ?? '') }}"
                    method="POST">
                    @csrf
                    @if ($viewAction == 'edit')
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" name="FirstName"
                                value="{{ old('FirstName', $employee->FirstName ?? '') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="LastName"
                                value="{{ old('LastName', $employee->LastName ?? '') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email"
                                value="{{ old('email', $user->email ?? '') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name="DateOfBirth"
                                value="{{ old('DateOfBirth', $employee->DateOfBirth ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="Gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male"
                                    {{ old('Gender', $employee->Gender ?? '') == 'Male' ? 'selected' : '' }}>Male
                                </option>
                                <option value="Female"
                                    {{ old('Gender', $employee->Gender ?? '') == 'Female' ? 'selected' : '' }}>Female
                                </option>
                                <option value="Other"
                                    {{ old('Gender', $employee->Gender ?? '') == 'Other' ? 'selected' : '' }}>Other
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Marital Status</label>
                            <select class="form-select" name="MaritalStatus" required>
                                <option value="">Select Marital Status</option>
                                <option value="Single"
                                    {{ old('MaritalStatus', $employee->MaritalStatus ?? '') == 'Single' ? 'selected' : '' }}>
                                    Single</option>
                                <option value="Married"
                                    {{ old('MaritalStatus', $employee->MaritalStatus ?? '') == 'Married' ? 'selected' : '' }}>
                                    Married</option>
                                <option value="Divorced"
                                    {{ old('MaritalStatus', $employee->MaritalStatus ?? '') == 'Divorced' ? 'selected' : '' }}>
                                    Divorced</option>
                                <option value="Widowed"
                                    {{ old('MaritalStatus', $employee->MaritalStatus ?? '') == 'Widowed' ? 'selected' : '' }}>
                                    Widowed</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Nationality</label>
                            <input type="text" class="form-control" name="Nationality"
                                value="{{ old('Nationality', $employee->Nationality ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Employment Status</label>
                            <select class="form-select" name="EmploymentStatus" required>
                                <option value="">Select Status</option>
                                <option value="Active"
                                    {{ old('EmploymentStatus', $employee->EmploymentStatus ?? '') == 'Active' ? 'selected' : '' }}>
                                    Active</option>
                                <option value="Inactive"
                                    {{ old('EmploymentStatus', $employee->EmploymentStatus ?? '') == 'Inactive' ? 'selected' : '' }}>
                                    Inactive</option>
                                <option value="Terminated"
                                    {{ old('EmploymentStatus', $employee->EmploymentStatus ?? '') == 'Terminated' ? 'selected' : '' }}>
                                    Terminated</option>
                                <option value="On Leave"
                                    {{ old('EmploymentStatus', $employee->EmploymentStatus ?? '') == 'On Leave' ? 'selected' : '' }}>
                                    On Leave</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Employment Type</label>
                            <select class="form-select" name="EmploymentType" required>
                                <option value="">Select Type</option>
                                <option value="International"
                                    {{ old('EmploymentType', $employee->EmploymentType ?? '') == 'International' ? 'selected' : '' }}>
                                    International</option>
                                <option value="Local"
                                    {{ old('EmploymentType', $employee->EmploymentType ?? '') == 'Local' ? 'selected' : '' }}>
                                    Local</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Position</label>
                            <select class="form-select" name="HR_Position_ID" required>
                                <option value="">Select Position</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}"
                                        {{ old('HR_Position_ID', $employee->HR_Position_ID ?? '') == $position->id ? 'selected' : '' }}>
                                        {{ $position->position_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" class="form-control" name="JobTitle"
                                value="{{ old('JobTitle', $employee->JobTitle ?? '') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Cluster</label>
                            <select class="form-select" name="ClusterID" required>
                                <option value="">Select Cluster</option>
                                @foreach ($clusters as $cluster)
                                    <option value="{{ $cluster->ClusterID }}"
                                        {{ old('ClusterID', $employee->Department ?? '') == $cluster->ClusterID ? 'selected' : '' }}>
                                        {{ $cluster->Cluster_Name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date Joined</label>
                            <input type="date" class="form-control" name="DateJoined"
                                value="{{ old('DateJoined', $employee->DateJoined ?? '') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Contract Start Date</label>
                            <input type="date" class="form-control" name="ContractStartDate"
                                value="{{ old('ContractStartDate', $employee->ContractStartDate ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Contract End Date</label>
                            <input type="date" class="form-control" name="ContractEndDate"
                                value="{{ old('ContractEndDate', $employee->ContractEndDate ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Employee Category</label>
                            <select class="form-select" name="EmployeeCategory" required>
                                <option value="">Select Category</option>
                                <option value="Full-Time"
                                    {{ old('EmployeeCategory', $employee->EmployeeCategory ?? '') == 'Full-Time' ? 'selected' : '' }}>
                                    Full-Time</option>
                                <option value="Part-Time"
                                    {{ old('EmployeeCategory', $employee->EmployeeCategory ?? '') == 'Part-Time' ? 'selected' : '' }}>
                                    Part-Time</option>
                                <option value="Contractor"
                                    {{ old('EmployeeCategory', $employee->EmployeeCategory ?? '') == 'Contractor' ? 'selected' : '' }}>
                                    Contractor</option>
                                <option value="Intern"
                                    {{ old('EmployeeCategory', $employee->EmployeeCategory ?? '') == 'Intern' ? 'selected' : '' }}>
                                    Intern</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Probation Period (Months)</label>
                            <input type="number" class="form-control" name="ProbationPeriodMonths"
                                value="{{ old('ProbationPeriodMonths', $employee->ProbationPeriodMonths ?? 0) }}"
                                min="0" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Basic Salary Per Month</label>
                            <input type="number" class="form-control" name="BasicSalaryPerMonth" step="0.01"
                                value="{{ old('BasicSalaryPerMonth', $employee->BasicSalaryPerMonth ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Salary Currency</label>
                            <select class="form-select" name="SalaryCurrency" required>
                                <option value="">Select Currency</option>
                                <option value="USD"
                                    {{ old('SalaryCurrency', $employee->SalaryCurrency ?? '') == 'USD' ? 'selected' : '' }}>
                                    USD</option>
                                <option value="EURO"
                                    {{ old('SalaryCurrency', $employee->SalaryCurrency ?? '') == 'EURO' ? 'selected' : '' }}>
                                    EURO</option>
                                <option value="BRISTISH CURRENCY"
                                    {{ old('SalaryCurrency', $employee->SalaryCurrency ?? '') == 'BRISTISH CURRENCY' ? 'selected' : '' }}>
                                    British Currency</option>
                                <option value="TSH"
                                    {{ old('SalaryCurrency', $employee->SalaryCurrency ?? '') == 'TSH' ? 'selected' : '' }}>
                                    TSH</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Bank Account Number</label>
                            <input type="text" class="form-control" name="BankAccountNumber"
                                value="{{ old('BankAccountNumber', $employee->BankAccountNumber ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text" class="form-control" name="BankName"
                                value="{{ old('BankName', $employee->BankName ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Branch Code</label>
                            <input type="text" class="form-control" name="BranchCode"
                                value="{{ old('BranchCode', $employee->BranchCode ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Tax ID</label>
                            <input type="text" class="form-control" name="TaxID"
                                value="{{ old('TaxID', $employee->TaxID ?? '') }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Benefits Eligibility</label>
                            <select class="form-select" name="BenefitsEligibility" required>
                                <option value="">Select Eligibility</option>
                                <option value="Yes"
                                    {{ old('BenefitsEligibility', $employee->BenefitsEligibility ?? '') == 'Yes' ? 'selected' : '' }}>
                                    Yes</option>
                                <option value="No"
                                    {{ old('BenefitsEligibility', $employee->BenefitsEligibility ?? '') == 'No' ? 'selected' : '' }}>
                                    No</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Pension Plan</label>
                            <select class="form-select" name="PensionPlan" required>
                                <option value="">Select Plan</option>
                                <option value="Yes"
                                    {{ old('PensionPlan', $employee->PensionPlan ?? '') == 'Yes' ? 'selected' : '' }}>
                                    Yes</option>
                                <option value="No"
                                    {{ old('PensionPlan', $employee->PensionPlan ?? '') == 'No' ? 'selected' : '' }}>No
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">HR Role</label>
                            <select class="form-select" name="HR_role" required>
                                <option value="">Select HR Role</option>
                                <option value="Supervisor"
                                    {{ old('HR_role', $user->HR_role ?? '') == 'Supervisor' ? 'selected' : '' }}>
                                    Supervisor</option>
                                <option value="Non-Supervisor"
                                    {{ old('HR_role', $user->HR_role ?? '') == 'Non-Supervisor' ? 'selected' : '' }}>
                                    Non-Supervisor</option>
                                <option value="Admin"
                                    {{ old('HR_role', $user->HR_role ?? '') == 'Admin' ? 'selected' : '' }}>Admin
                                </option>
                                <option value="Cluster Head"
                                    {{ old('HR_role', $user->HR_role ?? '') == 'Cluster Head' ? 'selected' : '' }}>
                                    Cluster Head</option>
                                <option value="HR-Account"
                                    {{ old('HR_role', $user->HR_role ?? '') == 'HR-Account' ? 'selected' : '' }}>
                                    HR-Account</option>
                                <option value="Corporate Account"
                                    {{ old('HR_role', $user->HR_role ?? '') == 'Corporate Account' ? 'selected' : '' }}>
                                    Corporate Account</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Account Role</label>
                            <select class="form-select" name="AccountRole" required>
                                <option value="">Select Account Role</option>
                                <option value="Admin"
                                    {{ old('AccountRole', $user->AccountRole ?? '') == 'Admin' ? 'selected' : '' }}>
                                    Admin</option>
                                <option value="User"
                                    {{ old('AccountRole', $user->AccountRole ?? '') == 'User' ? 'selected' : '' }}>User
                                </option>
                                <option value="Cluster Head"
                                    {{ old('AccountRole', $user->AccountRole ?? '') == 'Cluster Head' ? 'selected' : '' }}>
                                    Cluster Head</option>
                                <option value="HR-Account"
                                    {{ old('AccountRole', $user->AccountRole ?? '') == 'HR-Account' ? 'selected' : '' }}>
                                    HR-Account</option>
                                <option value="Corporate Account"
                                    {{ old('AccountRole', $user->AccountRole ?? '') == 'Corporate Account' ? 'selected' : '' }}>
                                    Corporate Account</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-footer">
                        <button type="submit"
                            class="btn btn-primary">{{ $viewAction == 'create' ? 'Create Employee' : 'Update Employee' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notification = document.getElementById('notification');
        if (notification) {
            setTimeout(() => {
                notification.style.display = 'none';
            }, 5000);
        }
    });
</script>
