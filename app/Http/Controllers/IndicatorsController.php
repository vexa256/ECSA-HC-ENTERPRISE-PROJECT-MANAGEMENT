<?php
namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;

class IndicatorsController extends Controller
{
    public function MgtSO()
    {
        $data = [

            "Desc"                => "Manage ECSA-HC Strategic Ojectives",
            "Page"                => "indicators.MgtSO",
            "strategicObjectives" => DB::table("strategic_objectives")->get(),

        ];

        return view('scrn', $data);
    }

    public function SelectSo()
    {
        $user    = auth()->user();
        $message = "";

        // If user is ECSA-HC and not an Admin, filter strategic objectives based on their cluster.
        if ($user->UserType === 'ECSA-HC' && $user->AccountRole !== 'Admin') {
                                               // Retrieve the user's ClusterID (from the users table)
            $userClusterID = $user->ClusterID; // e.g. "HEPRR-MPA"

            // Look up the cluster record to obtain its friendly name.
            $clusterRecord = DB::table('clusters')
                ->where('ClusterID', $userClusterID)
                ->first();
            $attachedCluster = $clusterRecord ? $clusterRecord->Cluster_Name : $userClusterID;

            // Retrieve performance indicators where the Responsible_Cluster JSON contains either the user's ClusterID
            // or the attached cluster name.
            $indicatorSOIDs = DB::table('performance_indicators')
                ->where(function ($query) use ($userClusterID, $attachedCluster) {
                    $query->whereRaw("JSON_CONTAINS(Responsible_Cluster, '\"$userClusterID\"')")
                        ->orWhereRaw("JSON_CONTAINS(Responsible_Cluster, '\"$attachedCluster\"')");
                })
                ->pluck('SO_ID')
                ->unique();

            // Retrieve strategic objectives using the SO_ID field (mapped to StrategicObjectiveID).
            $strategicObjectives = DB::table('strategic_objectives')
                ->whereIn('StrategicObjectiveID', $indicatorSOIDs)
                ->get();

            $message = "Hello!  Your attached cluster is '{$attachedCluster}', and you can see the Strategic Objectives linked to your indicators.";
        } else {
            // For Admin users, show all strategic objectives.
            $strategicObjectives = DB::table('strategic_objectives')->get();
            $message             = "Hello Admin! You can see all Strategic Objectives.";
        }

        $data = [
            "Desc"                => "Select Strategic Objective To Attach Indicators To",
            "Page"                => "indicators.SelectSO",
            "strategicObjectives" => $strategicObjectives,
            "message"             => $message,
        ];

        return view('scrn', $data);
    }

    public function MgtEcsaIndicators(Request $request)
    {
        $StrategicObjectiveID = $request->StrategicObjectiveID;

        // Get the specific strategic objective
        $SO = DB::table('strategic_objectives')
            ->where('StrategicObjectiveID', $StrategicObjectiveID)
            ->first();

        // All available clusters (useful for populating forms, etc.)
        $clusters = DB::table('clusters')->get();

        // Fetch indicators
        $indicators = DB::table('performance_indicators AS P')
            ->join('strategic_objectives AS S', 'S.StrategicObjectiveID', '=', 'P.SO_ID')
            ->where('P.SO_ID', $StrategicObjectiveID)
            ->select('S.StrategicObjectiveID', 'P.*')
            ->get();

        // Transform each indicator's Responsible_Cluster from JSON of IDs --> comma-separated names
        foreach ($indicators as $indicator) {
            // Decode the JSON to get an array of cluster IDs
            $clusterIDs = json_decode($indicator->Responsible_Cluster, true);

            if (is_array($clusterIDs) && count($clusterIDs) > 0) {
                // Fetch all cluster names whose ClusterID is in our JSON array
                $clusterNames = DB::table('clusters')
                    ->whereIn('ClusterID', $clusterIDs)
                    ->pluck('Cluster_Name')
                    ->toArray();

                // Convert the array of names to a comma-separated string
                $indicator->Responsible_Cluster = implode(', ', $clusterNames);
            } else {
                // If no clusters or invalid JSON, set to an empty string or handle accordingly
                $indicator->Responsible_Cluster = '';
            }
        }

        // Prepare data for the view
        $data = [
            'Desc'                 => 'Manage ECSA-HC performance indicators attached to ' . $SO->SO_Number,
            'Page'                 => 'indicators.MgtEcsahcIndicators',
            'strategicObjectives'  => $SO,
            'StrategicObjectiveID' => $StrategicObjectiveID,
            'clusters'             => $clusters,
            'indicators'           => $indicators,
        ];

        // Return the view
        return view('scrn', $data);
    }

    public function AddEcsahcIndicators(Request $request)
    {
        // Validate the incoming data
        $validated = $request->validate([
            'StrategicObjectiveID'  => 'required|exists:strategic_objectives,StrategicObjectiveID',
            'Indicator_Number'      => 'required|string|max:10',
            'Indicator_Name'        => 'required|string|max:255',
                                                          // 'Baseline_2023_2024'    => 'nullable|integer',
                                                          // 'Target_Year1'          => 'nullable|integer',
                                                          // 'Target_Year2'          => 'nullable|integer',
                                                          // 'Target_Year3'          => 'nullable|integer',
            'Responsible_Cluster'   => 'required|array',  // Must be an array
            'Responsible_Cluster.*' => 'required|string', // Each element must be a string
            'ResponseType'          => 'required|string', // Each element must be a string
        ]);

        // Insert the data into the performance_indicators table
        // Note that 'Responsible_Cluster' is stored as JSON
        $insertedId = DB::table('performance_indicators')->insertGetId([
            'SO_ID'               => $validated['StrategicObjectiveID'],
            'Indicator_Number'    => $validated['Indicator_Number'],
            'Indicator_Name'      => $validated['Indicator_Name'],
            // 'Baseline_2023_2024'  => $validated['Baseline_2023_2024'] ?? null,
            // 'Target_Year1'        => $validated['Target_Year1'] ?? null,
            // 'Target_Year2'        => $validated['Target_Year2'] ?? null,
            // 'Target_Year3'        => $validated['Target_Year3'] ?? null,
            'ResponseType'        => $validated['ResponseType'] ?? null,
            'Responsible_Cluster' => json_encode($validated['Responsible_Cluster']),
            'created_at'          => now(),
            'updated_at'          => now(),
        ]);

        // Check if insertion was successful
        if ($insertedId) {
            return redirect()
                ->back()
                ->with('success', 'Indicator created successfully!');
        } else {
            return redirect()
                ->back()
                ->with('error', 'Failed to create the indicator. Please try again.');
        }
    }

    public function UpdateEcsahcIndicators(Request $request)
    {

        // dd($request->ResponseType);

        // Validate incoming data
        $validated = $request->validate([
            'id'                    => 'required|exists:performance_indicators,id',
            'StrategicObjectiveID'  => 'required|exists:strategic_objectives,StrategicObjectiveID',
            'Indicator_Number'      => 'required|string|max:10',
            'Indicator_Name'        => 'required|string|max:255',
            'Baseline_2023_2024'    => 'nullable|integer',
            'Target_Year1'          => 'nullable|integer',
            'Target_Year2'          => 'nullable|integer',
            'Target_Year3'          => 'nullable|integer',
            'Responsible_Cluster'   => 'sometimes|array', // Might be posted as an array
            'Responsible_Cluster.*' => 'required|string', // Each element must be a string
        ]);

        // Build the update data array
        $updateData = [
            'SO_ID'            => $validated['StrategicObjectiveID'],
            'Indicator_Number' => $validated['Indicator_Number'],
            'Indicator_Name'   => $validated['Indicator_Name'],
            // 'Baseline_2023_2024' => $validated['Baseline_2023_2024'] ?? null,
            // 'Target_Year1'       => $validated['Target_Year1'] ?? null,
            // 'Target_Year2'       => $validated['Target_Year2'] ?? null,
            // 'Target_Year3'       => $validated['Target_Year3'] ?? null,
            'ResponseType'     => $validated['ResponseType'] ?? null,
            'updated_at'       => now(),
        ];

        // Only update the JSON field if Responsible_Cluster was included in the request
        if ($request->has('Responsible_Cluster')) {
            $updateData['Responsible_Cluster'] = json_encode($validated['Responsible_Cluster']);
        }

        // Perform the update
        $affected = DB::table('performance_indicators')
            ->where('id', $validated['id'])
            ->update($updateData);

        if ($request->has('ResponseType')) {
            DB::table('performance_indicators')
                ->where('id', $validated['id'])
                ->update([
                    'ResponseType' => $request->ResponseType,
                ]);
        }
        // Check if the update succeeded
        if ($affected) {
            return redirect()
                ->back()
                ->with('success', 'Indicator updated successfully!');
        } else {
            return redirect()
                ->back()
                ->with('error', 'Failed to update the indicator. No changes detected or error occurred.');
        }
    }

    public function DeleteEcsahcIndicators(Request $request)
    {
        // Validate the ID
        $request->validate([
            'id' => 'required|exists:performance_indicators,id',
        ]);

        // Attempt deletion
        $deleted = DB::table('performance_indicators')
            ->where('id', $request->id)
            ->delete();

        // Check if deletion was successful
        if ($deleted) {
            return redirect()
                ->back()
                ->with('success', 'Indicator deleted successfully!');
        } else {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete the indicator. Please try again.');
        }
    }

}