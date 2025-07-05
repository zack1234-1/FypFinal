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
    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $plans = Plan::orderBy($sort, $order);
        $status = request('status');
        if ($status) {
            $tags = $plans->where('status', $status);
        }
        $type = request('type');
        if ($type) {
            $tags = $plans->where('plan_type', $type);
        }
        if ($search) {
            $plans = $plans->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhere('plan_type', 'like', '%' . $search . '%')
                    ->orWhere('modules', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        $total = $plans->count();
        $plans = $plans->paginate(request("limit"));
        $plans = $plans->map(function ($plan) {
            $modules = json_decode($plan->modules);
            $moduleBadges = collect($modules)->map(function ($module) {
                return '<span class="badge bg-label-dark">' . $module . '</span>';
            })->implode(' ');
            $statusBadge = ($plan->status == 'active') ? '<span class="badge bg-success">
            ' . ucfirst($plan->status) . '
            </span>' : '<span class="badge bg-danger">' . ucfirst($plan->status) . '</span>';
            $planTypeBadge = ($plan->plan_type == 'free') ? '<span class="badge bg-success">' . ucfirst($plan->plan_type) . '</span>' : '<span class="badge bg-warning">' . ucfirst($plan->plan_type) . '</span>';
            return [
                'id' => $plan->id,
                'name' => ucfirst($plan->name),
                'description' => ucfirst($plan->description),
                'max_projects' => ($plan->max_projects == -1) ? get_label('unlimited', 'Unlimited') : $plan->max_projects,
                'max_clients' => ($plan->max_clients == -1) ? get_label('unlimited', 'Unlimited') : $plan->max_clients,
                'max_team_members' => ($plan->max_team_members == -1) ? get_label('unlimited', 'Unlimited') : $plan->max_team_members,
                'max_workspaces' => ($plan->max_worksapces == -1) ? get_label('unlimited', 'Unlimited') : $plan->max_worksapces,
                'plan_type' => $planTypeBadge,
                'monthly_price' => format_currency($plan->monthly_price),
                'monthly_discounted_price' => format_currency($plan->monthly_discounted_price),
                'yearly_price' => format_currency($plan->yearly_price),
                'yearly_discounted_price' => format_currency($plan->yearly_discounted_price),
                'lifetime_price' => format_currency($plan->lifetime_price),
                'lifetime_discounted_price' => format_currency($plan->lifetime_discounted_price),
                'status' => $statusBadge,
                'modules' => $moduleBadges, // Add the HTML badges for modules
            ];
        });
        return response()->json([
            "rows" => $plans,
            "total" => $total,
        ]);
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

        try {

            $plan = new Plan();
            $plan->name = $request->name;
            $plan->description = $request->description;
            $plan->max_team_members = $request->max_team_members;
            $plan->max_projects = $request->max_projects;
            $plan->modules = json_encode($request->modules ?? []);
            $plan->status = 'active';
            $plan->save();

            return redirect()->back()->with('success', 'Plan created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create the plan: ' . $e->getMessage());
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

            // Find the plan
            $plan = Plan::findOrFail($id);

            // Update fields including modules as JSON
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
    public function destroy(string $id)
    {
        $response = DeletionService::delete(Plan::class, $id, 'Record');
        return $response;
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
