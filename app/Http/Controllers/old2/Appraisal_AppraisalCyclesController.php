<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class Appraisal_AppraisalCyclesController
 *
 * This controller manages appraisal cycles in the h_r_appraisal_cycles table.
 * It supports:
 * - Listing all appraisal cycles.
 * - Displaying the create form.
 * - Displaying the edit form (requires a valid cycle ID).
 * - Handling store, update, and delete operations.
 *
 * All views are rendered using the "scrn" layout with the Page variable set to "AppraisalCycles".
 * The viewAction variable (with values "list", "create", or "edit") indicates which UI segment to display.
 *
 * @package App\Http\Controllers
 */
class Appraisal_AppraisalCyclesController extends Controller
{
    /**
     * Handle GET requests for appraisal cycles.
     * The method returns:
     * - A list of appraisal cycles (default, action = "list").
     * - The create form (action = "create").
     * - The edit form (action = "edit", requires a valid "id").
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
                    return redirect()->route('appraisal_appraisal_cycles.index')
                        ->with('notification', [
                            'type'    => 'error',
                            'message' => 'No appraisal cycle ID provided for editing.',
                        ]);
                }
                $cycle = DB::table('h_r_appraisal_cycles')->where('id', $id)->first();
                if (! $cycle) {
                    return redirect()->route('appraisal_appraisal_cycles.index')
                        ->with('notification', [
                            'type'    => 'error',
                            'message' => 'Appraisal cycle not found.',
                        ]);
                }
                return view('scrn', [
                    'Page'       => 'Appraisals.AppraisalCycles.AppraisalCycles',
                    'viewAction' => 'edit',
                    'cycle'      => $cycle,
                ]);
            } elseif ($action === 'create') {
                return view('scrn', [
                    'Page'       => 'Appraisals.AppraisalCycles.AppraisalCycles',
                    'viewAction' => 'create',
                ]);
            } else {
                $cycles = DB::table('h_r_appraisal_cycles')
                    ->orderBy('start_date', 'desc')
                    ->get();
                return view('scrn', [
                    'Page'       => 'Appraisals.AppraisalCycles.AppraisalCycles',
                    'viewAction' => 'list',
                    'cycles'     => $cycles,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing appraisal cycles request: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'An error occurred while processing your request. Please try again later.',
            ]);
        }
    }

    /**
     * Store a newly created appraisal cycle.
     *
     * Validates the input and creates a new appraisal cycle record.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the incoming data.
        $validator = Validator::make($request->all(), [
            'cycle_name'  => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'status'      => 'required|in:Open,Closed,Draft,Archived',
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

            DB::table('h_r_appraisal_cycles')->insert([
                'cycle_name'  => $request->input('cycle_name'),
                'start_date'  => $request->input('start_date'),
                'end_date'    => $request->input('end_date'),
                'status'      => $request->input('status'),
                'description' => $request->input('description'),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            DB::commit();

            return redirect()->route('appraisal_appraisal_cycles.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Appraisal cycle created successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing appraisal cycle: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to create appraisal cycle. Please try again.',
                ]);
        }
    }

    /**
     * Update the specified appraisal cycle.
     *
     * Validates and updates the cycle record.
     *
     * @param Request $request
     * @param int $id The appraisal cycle ID.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validate the incoming data.
        $validator = Validator::make($request->all(), [
            'cycle_name'  => 'required|string|max:255',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'status'      => 'required|in:Open,Closed,Draft,Archived',
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

            $updated = DB::table('h_r_appraisal_cycles')
                ->where('id', $id)
                ->update([
                    'cycle_name'  => $request->input('cycle_name'),
                    'start_date'  => $request->input('start_date'),
                    'end_date'    => $request->input('end_date'),
                    'status'      => $request->input('status'),
                    'description' => $request->input('description'),
                    'updated_at'  => now(),
                ]);

            DB::commit();

            if ($updated === 0) {
                return redirect()->back()
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'No changes made or appraisal cycle not found.',
                    ]);
            }

            return redirect()->route('appraisal_appraisal_cycles.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Appraisal cycle updated successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating appraisal cycle: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to update appraisal cycle. Please try again.',
                ]);
        }
    }

    /**
     * Delete the specified appraisal cycle.
     *
     * @param int $id The appraisal cycle ID.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $deleted = DB::table('h_r_appraisal_cycles')
                ->where('id', $id)
                ->delete();

            DB::commit();

            if ($deleted === 0) {
                return redirect()->back()
                    ->with('notification', [
                        'type'    => 'error',
                        'message' => 'Appraisal cycle not found or already deleted.',
                    ]);
            }

            return redirect()->route('appraisal_appraisal_cycles.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Appraisal cycle deleted successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting appraisal cycle: ' . $e->getMessage());
            return redirect()->back()
                ->with('notification', [
                    'type'    => 'error',
                    'message' => 'Failed to delete appraisal cycle. Please try again.',
                ]);
        }
    }
}