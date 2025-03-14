<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Class Appraisal_AppraisalGenerationController
 *
 * This controller automates the generation of appraisal header records.
 * It displays available appraisal cycles and form types for selection, then, based on HR input,
 * generates appraisal records for each employee. For 360-degree reviews, multiple reviewer entries are created.
 *
 * Views are rendered using the "scrn" layout with the Page variable set to "AppraisalGeneration".
 *
 * @package App\Http\Controllers
 */
class Appraisal_AppraisalGenerationController extends Controller
{
    /**
     * Display the appraisal generation options.
     *
     * Retrieves available appraisal cycles and form types and renders a form
     * where HR can select a cycle and form type for which appraisal records should be generated.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index(Request $request)
    {
        try {
            // Retrieve all appraisal cycles (you might filter by status if needed)
            $cycles = DB::table('h_r_appraisal_cycles')
                ->orderBy('start_date', 'desc')
                ->get();

            // Retrieve all appraisal form types
            $formTypes = DB::table('h_r_form_types')
                ->orderBy('form_name', 'asc')
                ->get();

            return view('scrn', [
                'Page'       => 'Appraisals.AppraisalGeneration.AppraisalGeneration',
                'viewAction' => 'create',
                'cycles'     => $cycles,
                'formTypes'  => $formTypes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading appraisal generation options: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to load appraisal generation options. Please try again later.',
            ]);
        }
    }

    /**
     * Generate appraisal records based on selected cycle and form type.
     *
     * Validates HR input and creates an appraisal header record in h_r_appraisals
     * for each employee in the system. For 360-degree form types (determined by form name containing "360"),
     * additional reviewer entries are created in h_r_appraisal_reviewers.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateAppraisals(Request $request)
    {
        // Validate HR input: cycle_id and form_type_id are required.
        $validator = Validator::make($request->all(), [
            'cycle_id'     => 'required|integer|exists:h_r_appraisal_cycles,id',
            'form_type_id' => 'required|integer|exists:h_r_form_types,id',
        ]);

        if ($validator->fails()) {
            $errorMessage = implode('<br>', $validator->errors()->all());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => $errorMessage,
            ])->withInput();
        }

        $cycle_id     = $request->input('cycle_id');
        $form_type_id = $request->input('form_type_id');

        // Retrieve the selected form type to determine if it's a 360 review.
        $formType = DB::table('h_r_form_types')->where('id', $form_type_id)->first();
        if (! $formType) {
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Invalid form type selected.',
            ]);
        }

        try {
            DB::beginTransaction();

            // Retrieve all employees (assuming they exist in the "users" table)
            $employees = DB::table('users')->get();

            foreach ($employees as $employee) {
                // Create a new appraisal record for each employee.
                // Here, reviewer_id is set from the employee's HR_supervisor_id, if available.
                $appraisal_id = DB::table('h_r_appraisals')->insertGetId([
                    'user_id'      => $employee->id,
                    'reviewer_id'  => $employee->HR_supervisor_id ?? null,
                    'cycle_id'     => $cycle_id,
                    'form_type_id' => $form_type_id,
                    'status'       => 'Draft',
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                // If the form type is for a 360 review, add reviewer entries.
                if (stripos($formType->form_name, '360') !== false) {
                    // For 360 reviews, assume we assign the supervisor plus additional peers.
                    $reviewers = [];
                    if ($employee->HR_supervisor_id) {
                        $reviewers[] = $employee->HR_supervisor_id;
                    }
                    // For demonstration, select two random peers (excluding the employee).
                    $peers = DB::table('users')
                        ->where('id', '!=', $employee->id)
                        ->limit(2)
                        ->pluck('id')
                        ->toArray();

                    $reviewers = array_merge($reviewers, $peers);

                    foreach ($reviewers as $reviewer_id) {
                        DB::table('h_r_appraisal_reviewers')->insert([
                            'appraisal_id' => $appraisal_id,
                            'reviewer_id'  => $reviewer_id,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('appraisal_appraisal_generation.index')
                ->with('notification', [
                    'type'    => 'success',
                    'message' => 'Appraisal records generated successfully.',
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating appraisal records: ' . $e->getMessage());
            return redirect()->back()->with('notification', [
                'type'    => 'error',
                'message' => 'Failed to generate appraisal records. Please try again.',
            ]);
        }
    }
}