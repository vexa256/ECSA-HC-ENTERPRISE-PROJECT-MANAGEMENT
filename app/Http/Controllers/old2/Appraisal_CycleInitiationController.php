<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class Appraisal_CycleInitiationController
 *
 * This controller allows HR to initiate appraisal cycles.
 * It provides:
 * - index(): Lists all appraisal cycles that are in "Draft" status.
 * - initiateCycle(): Updates a selected cycle's status to "Open".
 *
 * The views are rendered using the "scrn" layout with the Page variable set to "CycleInitiation".
 *
 * View: cycle_initiation.blade.php
 *
 * @package App\Http\Controllers
 */
class Appraisal_CycleInitiationController extends Controller
{
    /**
     * Display a list of appraisal cycles in "Draft" status.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        try {
            // Retrieve cycles that are in Draft status (ready to be initiated)
            $cycles = DB::table('h_r_appraisal_cycles')
                ->where('status', 'Draft')
                ->orderBy('start_date', 'desc')
                ->get();

            return view('scrn', [
                'Page'       => 'Appraisals.CycleInitiation.CycleInitiation',
                'viewAction' => 'list',
                'cycles'     => $cycles,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading cycle initiation dashboard: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to load appraisal cycles. Please try again later.',
            ]);
        }
    }

    /**
     * Initiate an appraisal cycle by updating its status to "Open".
     *
     * Expects a POST request with a valid "cycle_id" parameter.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiateCycle(Request $request)
    {
        // Validate the input: cycle_id must be provided and exist.
        $validator = Validator::make($request->all(), [
            'cycle_id' => 'required|integer|exists:h_r_appraisal_cycles,id',
        ]);

        if ($validator->fails()) {
            $errorMessage = implode('<br>', $validator->errors()->all());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => $errorMessage,
            ]);
        }

        try {
            DB::beginTransaction();

            // Only allow initiating cycles currently in "Draft" status.
            $updated = DB::table('h_r_appraisal_cycles')
                ->where('id', $request->input('cycle_id'))
                ->where('status', 'Draft')
                ->update([
                    'status'     => 'Open',
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                DB::rollBack();
                return redirect()->back()->with('notification', [
                    'type'    => 'error',
                    'message' => 'Cycle not found or cannot be initiated.',
                ]);
            }

            DB::commit();

            return redirect()->route('appraisal_cycle_initiation.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Appraisal cycle initiated successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error initiating appraisal cycle: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to initiate appraisal cycle. Please try again.',
            ]);
        }
    }
}