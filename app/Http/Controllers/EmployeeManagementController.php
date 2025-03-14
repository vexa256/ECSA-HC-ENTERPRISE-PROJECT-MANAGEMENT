<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EmployeeManagementController extends Controller
{
    /**
     * Display a listing of employees.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Retrieve all employee records joined with corresponding user details if needed.
            $employees = DB::table('employees')
                ->join('users', 'employees.UserID', '=', 'users.id')
                ->select('employees.*', 'users.email', 'users.name as UserName')
                ->orderBy('employees.FirstName', 'asc')
                ->get();

            return view('scrn', [
                'Page'       => 'HR_MODULE.MgtEmp.MgtEmp',
                'viewAction' => 'list',
                'employees'  => $employees,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading employees: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to load employees. Please try again later.',
            ]);
        }
    }

    /**
     * Show the form for creating a new employee.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            // Retrieve positions from h_r__positions for dropdown
            $positions = DB::table('h_r__positions')
                ->orderBy('position_name', 'asc')
                ->get();

            // Retrieve clusters from clusters table (to get ClusterID and Cluster_Name)
            $clusters = DB::table('clusters')
                ->orderBy('Cluster_Name', 'asc')
                ->get();

            return view('scrn', [
                'Page'       => 'HR_MODULE.MgtEmp.MgtEmp',
                'viewAction' => 'create',
                'positions'  => $positions,
                'clusters'   => $clusters,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading employee creation form: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to load employee creation form. Please try again later.',
            ]);
        }
    }

    /**
     * Store a newly created employee and corresponding user account.
     *
     * By default, the employee's email is used as their password.
     * A notification is generated to inform the data entrant.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate incoming data. Note: Some fields are duplicated in both users and employees.
        $validator = Validator::make($request->all(), [
            // User-related fields
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            // For simplicity, password is not separately provided.
            // Employee-specific personal info:
            'FirstName'             => 'required|string|max:255',
            'LastName'              => 'required|string|max:255',
            'DateOfBirth'           => 'nullable|date',
            'Gender'                => 'required|in:Male,Female,Other',
            'MaritalStatus'         => 'required|in:Single,Married,Divorced,Widowed',
            'Nationality'           => 'nullable|string|max:100',
            // HR / Job details:
            'EmploymentStatus'      => 'required|in:Active,Inactive,Terminated,On Leave',
            'EmploymentType'        => 'required|in:International,Local',
            'HR_Position_ID'        => 'nullable|integer|exists:h_r__positions,id',
            'JobTitle'              => 'required|string|max:255',
            // Department will be derived from the selected cluster
            'ClusterID'             => 'required|string|exists:clusters,ClusterID',
            'DateJoined'            => 'required|date',
            'ContractStartDate'     => 'nullable|date',
            'ContractEndDate'       => 'nullable|date',
            'EmployeeCategory'      => 'required|in:Full-Time,Part-Time,Contractor,Intern',
            'ProbationPeriodMonths' => 'required|integer|min:0',
            // Financial details:
            'BasicSalaryPerMonth'   => 'nullable|numeric',
            'SalaryCurrency'        => 'required|in:USD,EURO,BRISTISH CURRENCY,TSH',
            // PaymentFrequency is only Monthly
            'BankAccountNumber'     => 'nullable|string|max:50',
            'BankName'              => 'nullable|string|max:255',
            'BranchCode'            => 'nullable|string|max:50',
            'TaxID'                 => 'nullable|string|max:100',
            'BenefitsEligibility'   => 'required|in:Yes,No',
            'PensionPlan'           => 'required|in:Yes,No',
            // Supervisor: optional
            'SupervisorEmployeeID'  => 'nullable|integer|exists:employees,EmployeeID',
            // Optionally, additional user/HR fields from users table:
            'HR_first_name'         => 'nullable|string|max:255',
            'HR_last_name'          => 'nullable|string|max:255',
            'HR_supervisor_id'      => 'nullable|integer|exists:users,id',
            'HR_role'               => 'required|in:Supervisor,Non-Supervisor,Admin,Cluster Head,HR-Account,Corporate Account',
            'HR_hire_date'          => 'nullable|date',
            'HR_department'         => 'nullable|string|max:255',
            // AccountRole for user account:
            'AccountRole'           => 'sometimes|required|in:Admin,User,Cluster Head,HR-Account,Corporate Account',
        ]);

        if ($validator->fails()) {
            $errorMessage = implode('<br>', $validator->errors()->all());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => $errorMessage,
                ])->withInput();
        }

        try {
            DB::beginTransaction();

            // Create the user account.
            // By default, set the password to the email (hashed).
            $userId = DB::table('users')->insertGetId([
                'name'             => $request->input('name'),
                'email'            => $request->input('email'),
                'password'         => bcrypt($request->input('email')),
                'HR_first_name'    => $request->input('HR_first_name') ?? $request->input('FirstName'),
                'HR_last_name'     => $request->input('HR_last_name') ?? $request->input('LastName'),
                'HR_position_id'   => $request->input('HR_Position_ID'),
                'HR_supervisor_id' => $request->input('HR_supervisor_id'),
                'HR_role'          => $request->input('HR_role'),
                'HR_hire_date'     => $request->input('HR_hire_date'),
                'HR_department'    => $request->input('HR_department'),
                // Set other fields as needed...
                'AccountRole'      => $request->input('AccountRole', 'User'),
                'UserType'         => 'ECSA-HC', // or ECSA-HR if required, based on business logic
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // For department in employees, we fetch the ClusterID from clusters.
            // Here, we assume the selected ClusterID (from input) is directly used.
            $department = $request->input('ClusterID');

            // Create the employee record.
            $employeeId = DB::table('employees')->insertGetId([
                'UserID'                => $userId,
                'FirstName'             => $request->input('FirstName'),
                'LastName'              => $request->input('LastName'),
                'DateOfBirth'           => $request->input('DateOfBirth'),
                'Gender'                => $request->input('Gender'),
                'MaritalStatus'         => $request->input('MaritalStatus'),
                'Nationality'           => $request->input('Nationality'),
                'EmploymentStatus'      => $request->input('EmploymentStatus'),
                'EmploymentType'        => $request->input('EmploymentType'),
                'HR_Position_ID'        => $request->input('HR_Position_ID'),
                'JobTitle'              => $request->input('JobTitle'),
                'Department'            => $department, // use the ClusterID as department
                'DateJoined'            => $request->input('DateJoined'),
                'ContractStartDate'     => $request->input('ContractStartDate'),
                'ContractEndDate'       => $request->input('ContractEndDate'),
                'EmployeeCategory'      => $request->input('EmployeeCategory'),
                'ProbationPeriodMonths' => $request->input('ProbationPeriodMonths'),
                'BasicSalaryPerMonth'   => $request->input('BasicSalaryPerMonth'),
                'SalaryCurrency'        => $request->input('SalaryCurrency'),
                'PaymentFrequency'      => $request->input('PaymentFrequency', 'Monthly'),
                'BankAccountNumber'     => $request->input('BankAccountNumber'),
                'BankName'              => $request->input('BankName'),
                'BranchCode'            => $request->input('BranchCode'),
                'TaxID'                 => $request->input('TaxID'),
                'BenefitsEligibility'   => $request->input('BenefitsEligibility'),
                'PensionPlan'           => $request->input('PensionPlan'),
                'SupervisorEmployeeID'  => $request->input('SupervisorEmployeeID'),
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            DB::commit();

            // Create a notification message to inform the data entrant.
            $notificationMessage = "Employee created successfully. Note: The employee's email has been set as their default password. Please advise them to change their password upon first login.";

            return redirect()->route('staff_management.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => $notificationMessage,
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating employee: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to create employee. Please try again.',
            ]);
        }
    }

    /**
     * Show the form for editing an existing employee.
     *
     * @param int $id EmployeeID from the employees table.
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        try {
            // Retrieve the employee record.
            $employee = DB::table('employees')->where('EmployeeID', $id)->first();
            if (! $employee) {
                return redirect()->route('staff_management.index')
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'Employee not found.',
                    ]);
            }

            // Retrieve the corresponding user record.
            $user = DB::table('users')->where('id', $employee->UserID)->first();

            // Retrieve positions and clusters for dropdowns.
            $positions = DB::table('h_r__positions')
                ->orderBy('position_name', 'asc')
                ->get();

            $clusters = DB::table('clusters')
                ->orderBy('Cluster_Name', 'asc')
                ->get();

            return view('scrn', [
                'Page'       => 'HR_MODULE.MgtEmp.MgtEmp',
                'viewAction' => 'edit',
                'employee'   => $employee,
                'user'       => $user,
                'positions'  => $positions,
                'clusters'   => $clusters,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading employee edit form: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to load employee edit form. Please try again later.',
            ]);
        }
    }

    /**
     * Update the specified employee and their corresponding user account.
     *
     * @param Request $request
     * @param int $id EmployeeID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate input data.
        $validator = Validator::make($request->all(), [
            // User data
            'name'                  => 'required|string|max:255',
            'email'                 => "required|email|unique:users,email,{$id},id", // careful with unique rule
                                                                                     // Employee data
            'FirstName'             => 'required|string|max:255',
            'LastName'              => 'required|string|max:255',
            'DateOfBirth'           => 'nullable|date',
            'Gender'                => 'required|in:Male,Female,Other',
            'MaritalStatus'         => 'required|in:Single,Married,Divorced,Widowed',
            'Nationality'           => 'nullable|string|max:100',
            'EmploymentStatus'      => 'required|in:Active,Inactive,Terminated,On Leave',
            'EmploymentType'        => 'required|in:International,Local',
            'HR_Position_ID'        => 'nullable|integer|exists:h_r__positions,id',
            'JobTitle'              => 'required|string|max:255',
            'ClusterID'             => 'required|string|exists:clusters,ClusterID',
            'DateJoined'            => 'required|date',
            'ContractStartDate'     => 'nullable|date',
            'ContractEndDate'       => 'nullable|date',
            'EmployeeCategory'      => 'required|in:Full-Time,Part-Time,Contractor,Intern',
            'ProbationPeriodMonths' => 'required|integer|min:0',
            'BasicSalaryPerMonth'   => 'nullable|numeric',
            'SalaryCurrency'        => 'required|in:USD,EURO,BRISTISH CURRENCY,TSH',
            'BankAccountNumber'     => 'nullable|string|max:50',
            'BankName'              => 'nullable|string|max:255',
            'BranchCode'            => 'nullable|string|max:50',
            'TaxID'                 => 'nullable|string|max:100',
            'BenefitsEligibility'   => 'required|in:Yes,No',
            'PensionPlan'           => 'required|in:Yes,No',
            'SupervisorEmployeeID'  => 'nullable|integer|exists:employees,EmployeeID',
            // Additional user fields:
            'HR_first_name'         => 'nullable|string|max:255',
            'HR_last_name'          => 'nullable|string|max:255',
            'HR_supervisor_id'      => 'nullable|integer|exists:users,id',
            'HR_role'               => 'required|in:Supervisor,Non-Supervisor,Admin,Cluster Head,HR-Account,Corporate Account',
            'HR_hire_date'          => 'nullable|date',
            'HR_department'         => 'nullable|string|max:255',
            'AccountRole'           => 'sometimes|required|in:Admin,User,Cluster Head,HR-Account,Corporate Account',
        ]);

        if ($validator->fails()) {
            $errorMessage = implode('<br>', $validator->errors()->all());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => $errorMessage,
            ])->withInput();
        }

        try {
            DB::beginTransaction();

            // Update user record.
            DB::table('users')
                ->where('id', function ($query) use ($id) {
                    $query->select('UserID')
                        ->from('employees')
                        ->where('EmployeeID', $id)
                        ->limit(1);
                })
                ->update([
                    'name'             => $request->input('name'),
                    'email'            => $request->input('email'),
                    // Do not update password automatically on update.
                    'HR_first_name'    => $request->input('HR_first_name') ?? $request->input('FirstName'),
                    'HR_last_name'     => $request->input('HR_last_name') ?? $request->input('LastName'),
                    'HR_position_id'   => $request->input('HR_Position_ID'),
                    'HR_supervisor_id' => $request->input('HR_supervisor_id'),
                    'HR_role'          => $request->input('HR_role'),
                    'HR_hire_date'     => $request->input('HR_hire_date'),
                    'HR_department'    => $request->input('HR_department'),
                    'AccountRole'      => $request->input('AccountRole', 'User'),
                    'updated_at'       => now(),
                ]);

            // Determine department from ClusterID
            $department = $request->input('ClusterID');

            // Update employee record.
            $updated = DB::table('employees')
                ->where('EmployeeID', $id)
                ->update([
                    'FirstName'             => $request->input('FirstName'),
                    'LastName'              => $request->input('LastName'),
                    'DateOfBirth'           => $request->input('DateOfBirth'),
                    'Gender'                => $request->input('Gender'),
                    'MaritalStatus'         => $request->input('MaritalStatus'),
                    'Nationality'           => $request->input('Nationality'),
                    'EmploymentStatus'      => $request->input('EmploymentStatus'),
                    'EmploymentType'        => $request->input('EmploymentType'),
                    'HR_Position_ID'        => $request->input('HR_Position_ID'),
                    'JobTitle'              => $request->input('JobTitle'),
                    'Department'            => $department,
                    'DateJoined'            => $request->input('DateJoined'),
                    'ContractStartDate'     => $request->input('ContractStartDate'),
                    'ContractEndDate'       => $request->input('ContractEndDate'),
                    'EmployeeCategory'      => $request->input('EmployeeCategory'),
                    'ProbationPeriodMonths' => $request->input('ProbationPeriodMonths'),
                    'BasicSalaryPerMonth'   => $request->input('BasicSalaryPerMonth'),
                    'SalaryCurrency'        => $request->input('SalaryCurrency'),
                    'PaymentFrequency'      => $request->input('PaymentFrequency', 'Monthly'),
                    'BankAccountNumber'     => $request->input('BankAccountNumber'),
                    'BankName'              => $request->input('BankName'),
                    'BranchCode'            => $request->input('BranchCode'),
                    'TaxID'                 => $request->input('TaxID'),
                    'BenefitsEligibility'   => $request->input('BenefitsEligibility'),
                    'PensionPlan'           => $request->input('PensionPlan'),
                    'SupervisorEmployeeID'  => $request->input('SupervisorEmployeeID'),
                    'updated_at'            => now(),
                ]);

            DB::commit();

            if ($updated === 0) {
                return redirect()->back()->with('notification', [
                    'type'    => 'error',
                    'message' => 'No changes made or employee not found.',
                ]);
            }

            return redirect()->route('staff_management.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Employee updated successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating employee: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to update employee. Please try again.',
            ]);
        }
    }

    /**
     * Remove the specified employee and their corresponding user account.
     *
     * @param int $id EmployeeID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Retrieve the employee record to get the corresponding UserID.
            $employee = DB::table('employees')->where('EmployeeID', $id)->first();
            if (! $employee) {
                return redirect()->back()->with('notification', [
                    'type'    => 'error',
                    'message' => 'Employee not found.',
                ]);
            }

            // Delete the employee record.
            DB::table('employees')->where('EmployeeID', $id)->delete();

            // Delete the corresponding user account.
            DB::table('users')->where('id', $employee->UserID)->delete();

            DB::commit();

            return redirect()->route('staff_management.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Employee and associated user account deleted successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting employee: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to delete employee. Please try again.',
            ]);
        }
    }
}