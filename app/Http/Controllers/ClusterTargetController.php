<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClusterTargetController extends Controller
{
    /**
     * Render the common view.
     */
    private function renderView($page, $data = [])
    {
        return view('scrn', array_merge($data, [
            'Page'          => $page,
            'notifications' => session('notifications', [])
        ]));
    }

    /**
     * Cluster Selection
     * Excludes clusters such as "All clusters/projects".
     */
    public function index()
    {
        // Check for clusters with missing data.
        $invalidClusters = DB::table('clusters')
            ->whereNull('ClusterID')
            ->orWhereNull('Cluster_Name')
            ->exists();

        // Get clusters that have a valid ClusterID and are not "All clusters/projects"
        $clusters = DB::table('clusters')
            ->whereNotNull('ClusterID')
            ->where('Cluster_Name', '!=', 'All clusters/projects')
            ->orderBy('Cluster_Name')
            ->get();

        return $this->renderView('ClusterTargets.cluster-select', [
            'clusters'           => $clusters,
            'hasInvalidClusters' => $invalidClusters,
        ]);
    }

    /**
     * Show Target Form for a selected cluster.
     * Pulls indicators from performance_indicators (via Responsible_Cluster JSON)
     * and groups them by strategic objective (SO_ID).
     */
    public function showTargetForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ClusterID' => 'required|exists:clusters,ClusterID',
        ]);

        if ($validator->fails()) {
            return redirect()->route('targets.indextargets')
                ->with('notifications', [
                    'type'    => 'error',
                    'message' => 'Invalid cluster selection',
                ]);
        }

        $clusterId = $request->ClusterID;
        $cluster   = DB::table('clusters')->where('ClusterID', $clusterId)->first();

        // Check if the cluster is "All clusters/projects"
        if ($cluster->ClusterID === 'All clusters/projects') {
            return redirect()->route('targets.indextargets')
                ->with('notifications', [
                    'type'    => 'error',
                    'message' => 'Invalid cluster selection',
                ]);
        }

        // Retrieve indicators assigned to this cluster, excluding those also assigned to "All clusters/projects"
        $indicatorsCollection = DB::table('performance_indicators')
            ->whereJsonContains('Responsible_Cluster', $clusterId)
            ->where(function ($query) {
                $query->whereJsonDoesntContain('Responsible_Cluster', 'All clusters/projects');
            })
            ->select('id', 'Indicator_Number', 'Indicator_Name', 'SO_ID', 'ResponseType')
            ->get();

        // Group the indicators by strategic objective (SO_ID)
        $indicators = $indicatorsCollection->groupBy('SO_ID');

        $existingTargets = DB::table('cluster_indicator_targets')
            ->where('ClusterID', $clusterId)
            ->get()
            ->groupBy('IndicatorID');

        // Compute valid two-year ranges starting at 2024 up to (currentYear + 5)
        $validStartYear = 2024;
        $maxStartYear   = now()->year + 5;
        $ranges         = [];
        for ($year = $validStartYear; $year <= $maxStartYear; $year += 2) {
            $ranges[] = $year . '-' . ($year + 1);
        }

        // dd($existingTargets);
        return $this->renderView('ClusterTargets.target-setup', [
            'cluster'             => $cluster,
            'strategicObjectives' => $indicators->keys(),
            'indicators'          => $indicators,
            'existingTargets'     => $existingTargets,
            'validRanges'         => $ranges,
            'currentYear'         => now()->year,
            'validStartYear'      => $validStartYear,
        ]);
    }

    /**
     * Save a new target for an indicator.
     */
    public function saveTarget(Request $request)
    {
        $rules = [
            'ClusterID'    => 'required|exists:clusters,ClusterID',
            'IndicatorID'  => [
                'required',
                Rule::exists('performance_indicators', 'id')->where(function ($query) use ($request) {
                    $query->whereJsonContains('Responsible_Cluster', $request->ClusterID);
                }),
            ],
            // Expect a two-year range formatted as "YYYY-YYYY"
            'Target_Year'  => [
                'required',
                'regex:/^\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    $years = explode('-', $value);
                    if (count($years) !== 2) {
                        $fail("The $attribute must be in the format YYYY-YYYY (e.g. 2024-2025).");
                        return;
                    }
                    $start = intval($years[0]);
                    $end   = intval($years[1]);
                    if (($end - $start) !== 1) {
                        $fail("The $attribute must represent a two-year consecutive range (e.g. 2024-2025).");
                    }
                    if ($start < 2024 || $start > (now()->year + 5)) {
                        $fail("The start year of the $attribute must be between 2024 and " . (now()->year + 5) . ".");
                    }
                },
            ],
            'ResponseType' => ['required', Rule::in(['Text', 'Number', 'Boolean', 'Yes/No'])],
        ];

        // Custom validation for Target_Value based on the ResponseType.
        $rules['Target_Value'] = function ($attribute, $value, $fail) use ($request) {
            switch ($request->ResponseType) {
                case 'Number':
                    if (! is_numeric($value) || intval($value) != $value) {
                        $fail('The ' . $attribute . ' must be an integer.');
                    }
                    break;
                case 'Boolean':
                    if (! in_array(strtolower($value), ['0', '1', 'true', 'false'], true)) {
                        $fail('The ' . $attribute . ' must be a boolean value.');
                    }
                    break;
                case 'Yes/No':
                    if (! in_array($value, ['Yes', 'No'])) {
                        $fail('The ' . $attribute . ' must be either Yes or No.');
                    }
                    break;
                case 'Text':
                default:
                    if (! is_string($value)) {
                        $fail('The ' . $attribute . ' must be valid text.');
                    }
                    break;
            }
        };

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Ensure that a target for this indicator and range does not already exist.
        $exists = DB::table('cluster_indicator_targets')
            ->where('ClusterID', $request->ClusterID)
            ->where('IndicatorID', $request->IndicatorID)
            ->where('Target_Year', $request->Target_Year)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('notifications', [
                    'type'    => 'error',
                    'message' => 'Target already exists for this indicator and range',
                ])
                ->withInput();
        }

        try {
            $clusterTargetID = uniqid('target_', true);
            DB::table('cluster_indicator_targets')->insert([
                'ClusterTargetID' => $clusterTargetID,
                'ClusterID'       => $request->ClusterID,
                'IndicatorID'     => $request->IndicatorID,
                'Target_Year'     => $request->Target_Year, // e.g. "2024-2025"
                'Target_Value'    => $request->Target_Value,
                'ResponseType'    => $request->ResponseType,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            return redirect()->back()
                ->with('notifications', [
                    'type'    => 'success',
                    'message' => 'Target successfully saved',
                ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('notifications', [
                    'type'    => 'error',
                    'message' => 'Error saving target: ' . $e->getMessage(),
                ])
                ->withInput();
        }
    }

    /**
     * Update an existing target.
     */
    public function updateTarget(Request $request, $id)
    {
        // Retrieve the target first to get its existing ResponseType.
        $target = DB::table('cluster_indicator_targets')->find($id);
        if (! $target) {
            return redirect()->back()
                ->with('notifications', [
                    'type'    => 'error',
                    'message' => 'Target not found',
                ]);
        }

        $rules = [
            'Target_Year' => [
                'required',
                'regex:/^\d{4}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    $years = explode('-', $value);
                    if (count($years) !== 2) {
                        $fail("The $attribute must be in the format YYYY-YYYY (e.g. 2024-2025).");
                        return;
                    }
                    $start = intval($years[0]);
                    $end   = intval($years[1]);
                    if (($end - $start) !== 1) {
                        $fail("The $attribute must represent a two-year consecutive range (e.g. 2024-2025).");
                    }
                    if ($start < 2024 || $start > (now()->year + 5)) {
                        $fail("The start year of the $attribute must be between 2024 and " . (now()->year + 5) . ".");
                    }
                },
            ],
        ];

        // Custom validation for Target_Value based on the target's ResponseType.
        $rules['Target_Value'] = function ($attribute, $value, $fail) use ($target) {
            switch ($target->ResponseType) {
                case 'Number':
                    if (! is_numeric($value) || intval($value) != $value) {
                        $fail('The ' . $attribute . ' must be an integer.');
                    }
                    break;
                case 'Boolean':
                    if (! in_array(strtolower($value), ['0', '1', 'true', 'false'], true)) {
                        $fail('The ' . $attribute . ' must be a boolean value.');
                    }
                    break;
                case 'Yes/No':
                    if (! in_array($value, ['Yes', 'No'])) {
                        $fail('The ' . $attribute . ' must be either Yes or No.');
                    }
                    break;
                case 'Text':
                default:
                    if (! is_string($value)) {
                        $fail('The ' . $attribute . ' must be valid text.');
                    }
                    break;
            }
        };

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Ensure no other target exists for the same cluster, indicator, and range.
        $exists = DB::table('cluster_indicator_targets')
            ->where('ClusterID', $target->ClusterID)
            ->where('IndicatorID', $target->IndicatorID)
            ->where('Target_Year', $request->Target_Year)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->with('notifications', [
                    'type'    => 'error',
                    'message' => 'Target already exists for this indicator and range',
                ])
                ->withInput();
        }

        try {
            DB::table('cluster_indicator_targets')
                ->where('id', $id)
                ->update([
                    'Target_Year'  => $request->Target_Year,
                    'Target_Value' => $request->Target_Value,
                    'updated_at'   => now(),
                ]);

            return redirect()->back()
                ->with('notifications', [
                    'type'    => 'success',
                    'message' => 'Target updated successfully',
                ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('notifications', [
                    'type'    => 'error',
                    'message' => 'Error updating target: ' . $e->getMessage(),
                ]);
        }
    }

    /**
     * Delete a target.
     */
    public function delete($id)
    {
        try {
            DB::table('cluster_indicator_targets')->where('id', $id)->delete();
            return back()->with('notifications', [
                'type'    => 'success',
                'message' => 'Target successfully deleted',
            ]);
        } catch (\Exception $e) {
            return back()->with('notifications', [
                'type'    => 'error',
                'message' => 'Error deleting target: ' . $e->getMessage(),
            ]);
        }
    }
}