<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class Appraisal_RatingScalesController
 *
 * This controller manages CRUD operations for rating scales in the h_r_rating_scales table.
 * It uses the "scrn" layout with the Page variable "RatingScales" to render all views.
 * The viewAction variable determines which UI segment to display: list, create, or edit.
 *
 * Table Structure:
 * - id
 * - scale_name
 * - scale_code
 * - scale_value
 * - scale_description
 * - created_at
 * - updated_at
 *
 * Responsibilities:
 * - Listing all rating scales.
 * - Showing the create form.
 * - Showing the edit form (requires a valid rating scale ID).
 * - Handling store, update, and delete operations with robust validation, transactions, and logging.
 *
 * @package App\Http\Controllers
 */
class Appraisal_RatingScalesController extends Controller
{
    /**
     * Handle all GET requests for rating scales.
     * Based on the "action" query parameter, this method returns:
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
            $action = $request->query('action', 'list');

            if ($action === 'edit') {
                $id = $request->query('id');
                if (! $id) {
                    return redirect()->route('appraisal_rating_scales.index')
                        ->with('notification', [
                            'type'    => 'error',
                            'message' => 'No rating scale ID provided for editing.',
                        ]);
                }
                $scale = DB::table('h_r_rating_scales')->where('id', $id)->first();
                if (! $scale) {
                    return redirect()->route('appraisal_rating_scales.index')
                        ->with('notification', [
                            'type'    => 'error',
                            'message' => 'Rating scale not found.',
                        ]);
                }
                return view('scrn', [
                    'Page'       => 'Appraisals.RatingScales.RatingScales',
                    'viewAction' => 'edit',
                    'scale'      => $scale,
                ]);
            } elseif ($action === 'create') {
                return view('scrn', [
                    'Page'       => 'Appraisals.RatingScales.RatingScales',
                    'viewAction' => 'create',
                ]);
            } else {
                $scales = DB::table('h_r_rating_scales')
                    ->orderBy('scale_name', 'asc')
                    ->get();
                return view('scrn', [
                    'Page'       => 'Appraisals.RatingScales.RatingScales',
                    'viewAction' => 'list',
                    'scales'     => $scales,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing rating scales request: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'An error occurred while processing your request. Please try again later.',
            ]);
        }
    }

    /**
     * Store a newly created rating scale.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the input data.
        $validator = Validator::make($request->all(), [
            'scale_name'        => 'required|string|max:255',
            'scale_code'        => 'required|string|max:255',
            'scale_value'       => 'nullable|string|max:255',
            'scale_description' => 'nullable|string',
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

            // Insert new rating scale into the table.
            DB::table('h_r_rating_scales')->insert([
                'scale_name'        => $request->input('scale_name'),
                'scale_code'        => $request->input('scale_code'),
                'scale_value'       => $request->input('scale_value'),
                'scale_description' => $request->input('scale_description'),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            DB::commit();

            return redirect()->route('appraisal_rating_scales.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Rating scale created successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing rating scale: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to create rating scale. Please try again.',
                ]);
        }
    }

    /**
     * Update the specified rating scale.
     *
     * @param Request $request
     * @param int $id Rating scale ID to update.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the input data.
        $validator = Validator::make($request->all(), [
            'scale_name'        => 'required|string|max:255',
            'scale_code'        => 'required|string|max:255',
            'scale_value'       => 'nullable|string|max:255',
            'scale_description' => 'nullable|string',
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

            // Update the rating scale record.
            $updated = DB::table('h_r_rating_scales')
                ->where('id', $id)
                ->update([
                    'scale_name'        => $request->input('scale_name'),
                    'scale_code'        => $request->input('scale_code'),
                    'scale_value'       => $request->input('scale_value'),
                    'scale_description' => $request->input('scale_description'),
                    'updated_at'        => now(),
                ]);

            DB::commit();

            if ($updated === 0) {
                return redirect()->back()
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'No changes made or rating scale not found.',
                    ]);
            }

            return redirect()->route('appraisal_rating_scales.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Rating scale updated successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating rating scale: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to update rating scale. Please try again.',
                ]);
        }
    }

    /**
     * Delete the specified rating scale.
     *
     * @param int $id Rating scale ID to delete.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Delete the rating scale.
            $deleted = DB::table('h_r_rating_scales')
                ->where('id', $id)
                ->delete();

            DB::commit();

            if ($deleted === 0) {
                return redirect()->back()
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'Rating scale not found or already deleted.',
                    ]);
            }

            return redirect()->route('appraisal_rating_scales.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Rating scale deleted successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting rating scale: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to delete rating scale. Please try again.',
                ]);
        }
    }
}