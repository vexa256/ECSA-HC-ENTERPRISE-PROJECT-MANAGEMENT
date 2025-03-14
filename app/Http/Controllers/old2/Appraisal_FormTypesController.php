<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class Appraisal_FormTypesController
 *
 * This controller manages CRUD operations for appraisal form types in the h_r_form_types table.
 * It returns all views via the "scrn" layout, using the Page variable "FormTypes" to drive the UI.
 *
 * Responsibilities include:
 * - Listing all form types
 * - Showing the create form
 * - Showing the edit form (requires a valid form type ID)
 * - Handling store, update, and delete operations with proper validation, transactions, and logging.
 *
 * @package App\Http\Controllers
 */
class Appraisal_FormTypesController extends Controller
{
    /**
     * Handle all GET requests for form types.
     * Depending on the query parameter "action", this method returns:
     * - Listing view (default, action = "list")
     * - Create form (action = "create")
     * - Edit form (action = "edit", requires an "id" query parameter)
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        try {
            // Determine the requested action: list (default), create, or edit.
            $action = $request->query('action', 'list');

            // Edit action requires a valid "id".
            if ($action === 'edit') {
                $id = $request->query('id');
                if (! $id) {
                    return redirect()->route('appraisal_form_types.index')
                        ->with('notification', [
                            'type'    => 'error',
                            'message' => 'No form type ID provided for editing.',
                        ]);
                }
                $formType = DB::table('h_r_form_types')->where('id', $id)->first();
                if (! $formType) {
                    return redirect()->route('appraisal_form_types.index')
                        ->with('notification', [
                            'type'    => 'error',
                            'message' => 'Form type not found.',
                        ]);
                }
                // Return the edit view with the dynamic "viewAction" set to "edit".
                return view('scrn', [
                    'Page'       => 'Appraisals.Forms.FormTypes',
                    'viewAction' => 'edit',
                    'formType'   => $formType,
                ]);
            }
            // Create action: simply show the create form.
            elseif ($action === 'create') {
                return view('scrn', [
                    'Page'       => 'Appraisals.Forms.FormTypes',
                    'viewAction' => 'create',
                ]);
            }
            // Default: List all form types with "viewAction" set to "list".
            else {
                $formTypes = DB::table('h_r_form_types')
                    ->orderBy('form_name', 'asc')
                    ->get();
                return view('scrn', [
                    'Page'       => 'Appraisals.Forms.FormTypes',
                    'viewAction' => 'list',
                    'formTypes'  => $formTypes,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing form types request: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'An error occurred while processing your request. Please try again later.',
            ]);
        }
    }

    /**
     * Store a newly created form type.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the incoming data.
        $validator = Validator::make($request->all(), [
            'form_name'   => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $errorMessage = implode('<br>', $validator->errors()->all());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => $errorMessage,
                ])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Insert the new form type into the h_r_form_types table.
            DB::table('h_r_form_types')->insert([
                'form_name'   => $request->input('form_name'),
                'description' => $request->input('description'),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();

            return redirect()->route('appraisal_form_types.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Form type created successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing new form type: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to create form type. Please try again.',
                ]);
        }
    }

    /**
     * Update the specified form type.
     *
     * @param Request $request
     * @param int $id Form type ID to update.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming data.
        $validator = Validator::make($request->all(), [
            'form_name'   => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $errorMessage = implode('<br>', $validator->errors()->all());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => $errorMessage,
                ])
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update the form type record in the h_r_form_types table.
            $updated = DB::table('h_r_form_types')
                ->where('id', $id)
                ->update([
                    'form_name'   => $request->input('form_name'),
                    'description' => $request->input('description'),
                    'updated_at'  => now(),
                ]);

            DB::commit();

            if ($updated === 0) {
                return redirect()->back()
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'No changes made or form type not found.',
                    ]);
            }

            return redirect()->route('appraisal_form_types.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Form type updated successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating form type: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to update form type.',
                ]);
        }
    }

    /**
     * Delete the specified form type.
     *
     * @param int $id Form type ID to delete.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Delete the form type from the h_r_form_types table.
            $deleted = DB::table('h_r_form_types')
                ->where('id', $id)
                ->delete();

            DB::commit();

            if ($deleted === 0) {
                return redirect()->back()
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'Form type not found or already deleted.',
                    ]);
            }

            return redirect()->route('appraisal_form_types.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Form type deleted successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting form type: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to delete form type.',
                ]);
        }
    }
}