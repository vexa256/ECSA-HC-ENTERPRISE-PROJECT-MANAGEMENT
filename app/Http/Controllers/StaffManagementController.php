<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StaffManagementController extends Controller
{
    /**
     * Display a listing of the staff members (users with UserType 'ECSA-HR').
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            // Retrieve all staff who are ECSA-HR type.
            $staff = DB::table('users')
                ->where('UserType', 'ECSA-HR')
                ->orderBy('name', 'asc')
                ->get();

            return view('scrn', [
                'Page'       => 'Appraisals.StaffManagement.StaffManagement',
                'viewAction' => 'list',
                'staff'      => $staff,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading staff list: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to load staff list. Please try again later.',
            ]);
        }
    }

    /**
     * Show the form for creating a new staff member.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        try {
            // Retrieve positions and clusters to populate the dropdowns in the create form.
            $positions = DB::table('h_r__positions')
                ->orderBy('position_name', 'asc')
                ->get();

            $clusters = DB::table('clusters')
                ->orderBy('Cluster_Name', 'asc')
                ->get();

            return view('scrn', [
                'Page'       => 'Appraisals.StaffManagement.StaffManagement',
                'viewAction' => 'create',
                'positions'  => $positions,
                'clusters'   => $clusters,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading staff creation form: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to load staff creation form. Please try again later.',
            ]);
        }
    }

    /**
     * Store a newly created staff member in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate incoming data.
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:6',
            'HR_first_name'    => 'nullable|string|max:255',
            'HR_last_name'     => 'nullable|string|max:255',
            'HR_position_id'   => 'required|integer|exists:h_r__positions,id',
            'JobTitle'         => 'required|string|max:255',          // JobTitle must match a position's title.
            'HR_supervisor_id' => 'nullable|integer|exists:users,id', // Optional; may assign later.
            'HR_role'          => 'required|in:Supervisor,Non-Supervisor,Admin,Cluster Head,HR-Account,Corporate Account',
            'HR_hire_date'     => 'nullable|date',
            'HR_department'    => 'nullable|string|max:255',
            'ClusterID'        => 'required|string|exists:clusters,ClusterID',
            'UserType'         => 'required|in:ECSA-HR', // Must be ECSA-HR for staff in this module.
                                                         // Optionally, you can validate AccountRole if it's being provided.
            'AccountRole'      => 'sometimes|required|in:Admin,User,Cluster Head,HR-Account,Corporate Account',
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

            // Create the new staff record. Password is hashed.
            $userId = DB::table('users')->insertGetId([
                'name'             => $request->input('name'),
                'email'            => $request->input('email'),
                'password'         => bcrypt($request->input('password')),
                'HR_first_name'    => $request->input('HR_first_name'),
                'HR_last_name'     => $request->input('HR_last_name'),
                'HR_position_id'   => $request->input('HR_position_id'),
                'JobTitle'         => $request->input('JobTitle'),
                'HR_supervisor_id' => $request->input('HR_supervisor_id'),
                'HR_role'          => $request->input('HR_role'),
                'HR_hire_date'     => $request->input('HR_hire_date'),
                'HR_department'    => $request->input('HR_department'),
                'ClusterID'        => $request->input('ClusterID'),
                'UserType'         => $request->input('UserType'),
                'AccountRole'      => $request->input('AccountRole', 'User'),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::commit();

            return redirect()->route('staff_management.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Staff account created successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating staff account: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to create staff account. Please try again.',
            ]);
        }
    }

    /**
     * Show the form for editing the specified staff member.
     *
     * @param int $id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        try {
            // Retrieve the staff member record.
            $staffMember = DB::table('users')->where('id', $id)->first();
            if (! $staffMember) {
                return redirect()->route('staff_management.index')
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'Staff member not found.',
                    ]);
            }

            // Retrieve positions and clusters for dropdown selections.
            $positions = DB::table('h_r__positions')
                ->orderBy('position_name', 'asc')
                ->get();

            $clusters = DB::table('clusters')
                ->orderBy('Cluster_Name', 'asc')
                ->get();

            return view('scrn', [
                'Page'        => 'Appraisals.StaffManagement.StaffManagement',
                'viewAction'  => 'edit',
                'staffMember' => $staffMember,
                'positions'   => $positions,
                'clusters'    => $clusters,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading staff edit form: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to load staff edit form. Please try again later.',
            ]);
        }
    }

    /**
     * Update the specified staff member in storage.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate input data.
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'email'            => "required|email|unique:users,email,{$id}",
            'HR_first_name'    => 'nullable|string|max:255',
            'HR_last_name'     => 'nullable|string|max:255',
            'HR_position_id'   => 'required|integer|exists:h_r__positions,id',
            'JobTitle'         => 'required|string|max:255',
            'HR_supervisor_id' => 'nullable|integer|exists:users,id',
            'HR_role'          => 'required|in:Supervisor,Non-Supervisor,Admin,Cluster Head,HR-Account,Corporate Account',
            'HR_hire_date'     => 'nullable|date',
            'HR_department'    => 'nullable|string|max:255',
            'ClusterID'        => 'required|string|exists:clusters,ClusterID',
            'AccountRole'      => 'sometimes|required|in:Admin,User,Cluster Head,HR-Account,Corporate Account',
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

            // Update the staff member's record.
            $updated = DB::table('users')
                ->where('id', $id)
                ->update([
                    'name'             => $request->input('name'),
                    'email'            => $request->input('email'),
                    'HR_first_name'    => $request->input('HR_first_name'),
                    'HR_last_name'     => $request->input('HR_last_name'),
                    'HR_position_id'   => $request->input('HR_position_id'),
                    'JobTitle'         => $request->input('JobTitle'),
                    'HR_supervisor_id' => $request->input('HR_supervisor_id'),
                    'HR_role'          => $request->input('HR_role'),
                    'HR_hire_date'     => $request->input('HR_hire_date'),
                    'HR_department'    => $request->input('HR_department'),
                    'ClusterID'        => $request->input('ClusterID'),
                    'AccountRole'      => $request->input('AccountRole', 'User'),
                    'updated_at'       => now(),
                ]);

            DB::commit();

            if ($updated === 0) {
                return redirect()->back()->with('notification', [
                    'type'    => 'error',
                    'message' => 'No changes made or staff member not found.',
                ]);
            }

            return redirect()->route('staff_management.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Staff account updated successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating staff account: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to update staff account. Please try again.',
            ]);
        }
    }

    /**
     * Remove the specified staff member from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $deleted = DB::table('users')
                ->where('id', $id)
                ->delete();

            DB::commit();

            if ($deleted === 0) {
                return redirect()->back()->with('notification', [
                    'type'    => 'error',
                    'message' => 'Staff member not found or already deleted.',
                ]);
            }

            return redirect()->route('staff_management.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Staff account deleted successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting staff account: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to delete staff account. Please try again.',
            ]);
        }
    }
}