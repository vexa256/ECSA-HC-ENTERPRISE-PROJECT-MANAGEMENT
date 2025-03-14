<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class Appraisal_PositionsController
 *
 * This controller manages CRUD operations for HR job positions in the h_r__positions table.
 * It uses a single layout view ("scrn") with a dynamic "Page" variable set to
 * "Appraisals.Positions.MgtPositions" and a "viewAction" variable to control the UI portion.
 *
 * Responsibilities include:
 * - Listing all positions
 * - Showing the create form
 * - Showing the edit form (requires a valid position ID)
 * - Handling store, update, and delete operations with proper validation,
 *   transactions, and logging.
 *
 * @package App\Http\Controllers
 */
class Appraisal_PositionsController extends Controller
{
    /**
     * Handle all GET requests for positions.
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
            // Determine the desired action, defaulting to "list"
            $action = $request->query('action', 'list');

            // If action is "edit", ensure an ID is provided and load the corresponding position.
            if ($action === 'edit') {
                $id = $request->query('id');
                if (! $id) {
                    return redirect()->route('appraisal_positions.index')
                        ->with('notification', [
                            'type'    => 'error',
                            'message' => 'No position ID provided for editing.',
                        ]);
                }
                $position = DB::table('h_r__positions')->where('id', $id)->first();
                if (! $position) {
                    return redirect()->route('appraisal_positions.index')
                        ->with('notification', [
                            'type'    => 'error',
                            'message' => 'Position not found.',
                        ]);
                }
                // Return the main layout "scrn" with Page set to Appraisals.Positions.MgtPositions and viewAction "edit"
                return view('scrn', [
                    'Page'       => 'Appraisals.Positions.MgtPositions',
                    'viewAction' => 'edit',
                    'position'   => $position,
                ]);
            }
            // If action is "create", simply load the create form.
            elseif ($action === 'create') {
                return view('scrn', [
                    'Page'       => 'Appraisals.Positions.MgtPositions',
                    'viewAction' => 'create',
                ]);
            }
            // Default: list all positions with viewAction "list"
            else {
                $positions = DB::table('h_r__positions')
                    ->orderBy('position_name', 'asc')
                    ->get();
                return view('scrn', [
                    'Page'       => 'Appraisals.Positions.MgtPositions',
                    'viewAction' => 'list',
                    'positions'  => $positions,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing positions request: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'An error occurred while processing your request. Please try again later.',
            ]);
        }
    }

    /**
     * Store a newly created position.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the incoming data.
        $validator = Validator::make($request->all(), [
            'position_name'  => 'required|string|max:255',
            'is_supervisory' => 'required|boolean',
            'description'    => 'nullable|string',
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

            // Insert the new position into the h_r__positions table.
            DB::table('h_r__positions')->insert([
                'position_name'  => $request->input('position_name'),
                'is_supervisory' => $request->input('is_supervisory'),
                'description'    => $request->input('description'),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            DB::commit();

            return redirect()->route('appraisal_positions.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Position created successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing new position: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to create position. Please try again.',
                ]);
        }
    }

    /**
     * Update the specified position.
     *
     * @param Request $request
     * @param int $id Position ID to update.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming data.
        $validator = Validator::make($request->all(), [
            'position_name'  => 'required|string|max:255',
            'is_supervisory' => 'required|boolean',
            'description'    => 'nullable|string',
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

            // Update the position with the provided ID.
            $updated = DB::table('h_r__positions')
                ->where('id', $id)
                ->update([
                    'position_name'  => $request->input('position_name'),
                    'is_supervisory' => $request->input('is_supervisory'),
                    'description'    => $request->input('description'),
                    'updated_at'     => now(),
                ]);

            DB::commit();

            if ($updated === 0) {
                return redirect()->back()
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'No changes made or position not found.',
                    ]);
            }

            return redirect()->route('appraisal_positions.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Position updated successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating position: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to update position.',
                ]);
        }
    }

    /**
     * Delete the specified position.
     *
     * @param int $id Position ID to delete.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            // Delete the position from the h_r__positions table.
            $deleted = DB::table('h_r__positions')
                ->where('id', $id)
                ->delete();

            DB::commit();

            if ($deleted === 0) {
                return redirect()->back()
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'Position not found or already deleted.',
                    ]);
            }

            return redirect()->route('appraisal_positions.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Position deleted successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting position: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to delete position.',
                ]);
        }
    }
}