<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Plan;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = Plan::all();
        return view('superadmin.plans.list', ['plans' => $plans]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.plans.create');
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'             => 'required|string|max:255',
            'description'      => 'required|string',
            'max_team_members' => 'required|integer|min:1',
            'max_projects'     => 'required|integer|min:1',
            'modules'         => 'required|array',
        ], [
            'name.required'             => 'The plan name is required.',
            'name.string'               => 'The plan name must be a string.',
            'name.max'                  => 'The plan name may not be greater than 255 characters.',
            'description.required'      => 'The description is required.',
            'description.string'        => 'The description must be a string.',
            'max_team_members.required' => 'Maximum team members is required.',
            'max_team_members.integer'  => 'Maximum team members must be a number.',
            'max_team_members.min'      => 'Maximum team members must be at least 1.',
            'max_projects.required'     => 'Maximum projects is required.',
            'max_projects.integer'      => 'Maximum projects must be a number.',
            'max_projects.min'          => 'Maximum projects must be at least 1.',
            'modules.required'         => 'The features must be choose at least one.',
            'modules.array'            => 'The selected features must be an array.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try 
        {
            $plan = new Plan();
            $plan->name = $request->name;
            $plan->description = $request->description;
            $plan->max_team_members = $request->max_team_members;
            $plan->max_projects = $request->max_projects;

            $modules = $request->modules ?? [];
            if (in_array('cardTables', $modules) && !in_array('notes', $modules)) {
                $modules[] = 'notes';
            }
            
            $plan->modules = json_encode($modules);
            $plan->status = 'active';
            $plan->save();

            return redirect()->back()->with('success', 'Plan created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create plan: ' . $e->getMessage());
        }


    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $plan = Plan::findOrFail($id);
        $plan->modules = json_decode($plan->modules);
        return view('superadmin.plans.update', ['plan' => $plan]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'max_team_members' => 'required|integer|min:1',
                'max_projects' => 'required|integer|min:1',
                'modules' => 'nullable|array',
            ]);

            $plan = Plan::findOrFail($id);

            $plan->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'max_team_members' => $validated['max_team_members'],
                'max_projects' => $validated['max_projects'],
                'modules' => json_encode($validated['modules'] ?? []),
            ]);

            return redirect()->back()->with('success', 'Plan updated successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                            ->withErrors($e->validator)
                            ->withInput();
        } catch (\Exception $e) {
            \Log::error('Failed to update plan: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Something went wrong while updating the plan.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    
    public function destroy($id)
    {
        $plan = Plan::find($id);

        if (!$plan) {
            return back()->with('error', 'Plan not found.');
        }

        $plan->delete();

        return back()->with('success', 'Plan deleted successfully.');
    }


    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:activity_logs,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);
        $ids = $validatedData['ids'];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            DeletionService::delete(Plan::class, $id, 'Record');
        }
        return response()->json(['error' => false, 'message' => 'Record(s) deleted successfully.']);
    }
}
