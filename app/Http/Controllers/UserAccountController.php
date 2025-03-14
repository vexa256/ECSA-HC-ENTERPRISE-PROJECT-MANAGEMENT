<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserAccountController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    /**
     * Update the user's account information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAccount(Request $request)
    {
        $user = Auth::user();

        // Validate the request
        $validator = Validator::make($request->all(), [
            'name'                  => 'required|string|max:255',
            'email'                 => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'current_password'      => 'required_with:password',
            'password'              => 'nullable|min:6',
            'password_confirmation' => 'nullable|same:password',
            'Phone'                 => 'nullable|string|max:20',
            'PhoneNumber'           => 'nullable|string|max:20',
            'Nationality'           => 'nullable|string|max:100',
            'Sex'                   => 'nullable|in:Male,Female',
            'Address'               => 'nullable|string',
            'JobTitle'              => 'nullable|string|max:255',
            'ParentOrganization'    => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify current password if changing password
        if ($request->filled('password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()
                    ->with('error', 'Current password is incorrect')
                    ->withInput();
            }
        }

        // Prepare data for update
        $updateData = [
            'name'               => $request->name,
            'email'              => $request->email,
            'Phone'              => $request->Phone,
            'PhoneNumber'        => $request->PhoneNumber,
            'Nationality'        => $request->Nationality,
            'Sex'                => $request->Sex,
            'Address'            => $request->Address,
            'JobTitle'           => $request->JobTitle,
            'ParentOrganization' => $request->ParentOrganization,
        ];

        // Add password if it's being changed
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Remove null values
        $updateData = array_filter($updateData, function ($value) {
            return $value !== null;
        });

        // Update user using DB facade
        try {
            DB::table('users')->where('id', $user->id)->update($updateData);

            // Refresh the user session data
            $updatedUser = DB::table('users')->where('id', $user->id)->first();
            $request->session()->put('_old_input', []); // Clear old input

            return back()->with('status', 'Account updated successfully');
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Failed to update account: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Process the user logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}