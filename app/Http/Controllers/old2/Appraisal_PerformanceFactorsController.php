<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class Appraisal_PerformanceFactorsController
 *
 * This controller manages CRUD operations for performance factors in the h_r_performance_factors table.
 * It returns views via the "scrn" layout with the Page variable set to "PerformanceFactors" and
 * uses the viewAction variable to control the displayed UI segment.
 *
 * Responsibilities:
 * - Listing all performance factors.
 * - Displaying the create form.
 * - Displaying the edit form (requires a valid factor ID).
 * - Handling store, update, and delete operations with validation, transactions, and logging.
 *
 * @package App\Http\Controllers
 */
class Appraisal_PerformanceFactorsController extends Controller
{
    /**
     * Handle all GET requests for performance factors.
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
            // Determine the requested action: default is "list".
            $action = $request->query('action', 'list');

            // For the edit action, validate that an ID is provided.
            if ($action === 'edit') {
                $id = $request->query('id');
                if (! $id) {
                    return redirect()->route('appraisal_performance_factors.index')
                        ->with('notification', [
                            'type'    => 'error',
                            'message' => 'No performance factor ID provided for editing.',
                        ]);
                }
                $factor = DB::table('h_r_performance_factors')->where('id', $id)->first();
                if (! $factor) {
                    return redirect()->route('appraisal_performance_factors.index')
                        ->with('notification', [
                            'type'    => 'error',
                            'message' => 'Performance factor not found.',
                        ]);
                }
                return view('scrn', [
                    'Page'       => 'Appraisals.PerformanceFactors.PerformanceFactors',
                    'viewAction' => 'edit',
                    'factor'     => $factor,
                ]);
            }
            // If action is "create", simply show the create form.
            elseif ($action === 'create') {
                return view('scrn', [
                    'Page'       => 'Appraisals.PerformanceFactors.PerformanceFactors',
                    'viewAction' => 'create',
                ]);
            }
            // Default action: list all performance factors.
            else {
                $factors = DB::table('h_r_performance_factors')
                    ->orderBy('factor_category', 'asc')
                    ->get();
                return view('scrn', [
                    'Page'       => 'Appraisals.PerformanceFactors.PerformanceFactors',
                    'viewAction' => 'list',
                    'factors'    => $factors,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing performance factors request: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'An error occurred while processing your request. Please try again later.',
            ]);
        }
    }

    /**
     * Store a newly created performance factor.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the incoming request data.
        $validator = Validator::make($request->all(), [
            'factor_category'       => 'required|string|max:255',
            'factor_description'    => 'required|string',
            'is_supervisory_factor' => 'required|boolean',
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

            // Insert the new performance factor into the database.
            DB::table('h_r_performance_factors')->insert([
                'factor_category'       => $request->input('factor_category'),
                'factor_description'    => $request->input('factor_description'),
                'is_supervisory_factor' => $request->input('is_supervisory_factor'),
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);

            DB::commit();

            return redirect()->route('appraisal_performance_factors.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Performance factor created successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing performance factor: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to create performance factor. Please try again.',
                ]);
        }
    }

    /**
     * Update the specified performance factor.
     *
     * @param Request $request
     * @param int $id The ID of the performance factor to update.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate incoming data.
        $validator = Validator::make($request->all(), [
            'factor_category'       => 'required|string|max:255',
            'factor_description'    => 'required|string',
            'is_supervisory_factor' => 'required|boolean',
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

            // Update the performance factor record.
            $updated = DB::table('h_r_performance_factors')
                ->where('id', $id)
                ->update([
                    'factor_category'       => $request->input('factor_category'),
                    'factor_description'    => $request->input('factor_description'),
                    'is_supervisory_factor' => $request->input('is_supervisory_factor'),
                    'updated_at'            => now(),
                ]);

            DB::commit();

            if ($updated === 0) {
                return redirect()->back()
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'No changes made or performance factor not found.',
                    ]);
            }

            return redirect()->route('appraisal_performance_factors.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Performance factor updated successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating performance factor: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to update performance factor. Please try again.',
                ]);
        }
    }

    /**
     * Delete the specified performance factor.
     *
     * @param int $id The ID of the performance factor to delete.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Delete the performance factor from the database.
            $deleted = DB::table('h_r_performance_factors')
                ->where('id', $id)
                ->delete();

            DB::commit();

            if ($deleted === 0) {
                return redirect()->back()
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'Performance factor not found or already deleted.',
                    ]);
            }

            return redirect()->route('appraisal_performance_factors.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Performance factor deleted successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting performance factor: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to delete performance factor. Please try again.',
                ]);
        }
    }
}