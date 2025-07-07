<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\Session;

class StatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $workspaceId = session()->get('workspace_id');

        $statuses = Status::where('workspace_id', $workspaceId)->get();

        return view('status.list', compact('statuses'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('status.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $adminId = getAdminIdByUserRole();

        // Validate input
        $validated = $request->validate([
            'title' => ['required'],
            'color' => ['required']
        ]);

        $workspaceId = session()->get('workspace_id');
        if (!$workspaceId) {
            return response()->json(['error' => true, 'message' => 'No workspace selected.']);
        }

        // Create a new Status instance manually
        $status = new Status();
        $status->title = $request->title;
        $status->color = $request->color;
        $status->slug = generateUniqueSlug($request->title, Status::class);
        $status->admin_id = $adminId;
        $status->workspace_id = $workspaceId;

        // Save to DB
        if ($status->save()) {
            // Attach roles if any
            $roleIds = $request->input('role_ids');
            if ($roleIds) {
                $status->roles()->attach($roleIds);
            }

          Session::flash('message', 'Status Card created successfully.');
          return back();
        } else {
            return response()->json(['error' => true, 'message' => 'Status couldn\'t be created.']);
        }
    }


    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $status = Status::orderBy($sort, $order);
        $adminId = getAdminIdByUserRole();
        $status->where('admin_id', $adminId);
        if ($search) {
            $status = $status->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%');
            });
        }
        $total = $status->count();
        $status = $status
            ->paginate(request("limit"))
            ->through(function ($status) {
                $roles = $status->roles->pluck('name')->map(function ($roleName) {
                    return ucfirst($roleName);
                })->implode(', ');
                return [
                    'id' => $status->id,
                    'title' => $status->title,
                'roles_has_access' => $roles ?: ' - ',
                    'color' => '<span class="badge bg-' . $status->color . '">' . $status->title . '</span>',
                    'created_at' => format_date($status->created_at, true),
                    'updated_at' => format_date($status->updated_at, true),
                ];
            });


        return response()->json([
            "rows" => $status->items(),
            "total" => $total,
        ]);
    }

    public function get($id)
    {
        $status = Status::findOrFail($id);
        $roles = $status->roles()->pluck('id')->toArray();
        return response()->json(['status' => $status, 'roles' => $roles]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'id' => ['required'],
            'title' => ['required'],
            'color' => ['required']
        ]);

        // Find the status or fail
        $status = Status::findOrFail($request->id);

        // Assign values manually
        $status->title = $request->title;
        $status->color = $request->color;
        $status->slug = generateUniqueSlug($request->title, Status::class, $request->id);

        // Save updated status
        if ($status->save()) {
            // Sync roles if provided
            $roleIds = $request->input('role_ids');
            $status->roles()->sync($roleIds);

            Session::flash('message', 'Status Card updated successfully.');
            return back();
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Status couldn\'t be updated.'
            ]);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $status = Status::findOrFail($id);
        if ($status->projects()->count() > 0 ||  $status->tasks()->count() > 0) {
             
            return response()->json(['error' => true, 'message' => 'Status can\'t be deleted.It is associated with a project or task.']);
        } else {

            $response = DeletionService::delete(Status::class, $id, 'Status');
            Session::flash('message', 'Status Card deleted successfully.');
            return back();
        }
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:statuses,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $status = Status::findOrFail($id);
            if ($status->projects()->count() > 0 ||  $status->tasks()->count() > 0) {
                return response()->json(['error' => true, 'message' => 'Status can\'t be deleted.It is associated with a project']);
            } else {
                $deletedIds[] = $id;
                $deletedTitles[] = $status->title;
                DeletionService::delete(Status::class, $id, 'Status');
            }
        }
        return response()->json(['error' => false, 'message' => 'Status(es) deleted successfully.', 'id' => $deletedIds, 'titles' => $deletedTitles]);
    }
    public function search(Request $request)
    {
        $query = $request->input('q');
        $page = $request->input('page', 1);
        $perPage = 10;

        // If there is no query, return the first set of statuses
        $statuses = Status::where('admin_id', getAdminIDByUserRole())
            ->orWhere(function ($query) {
                $query->whereNull('admin_id')
                    ->where('is_default', 1);
            })
            ->when($query, function ($queryBuilder) use ($query) {
                $queryBuilder->where('title', 'like', '%' . $query . '%');
            })
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get(['id', 'title']);

        $statuses = $statuses->unique('id');
        // Prepare response for Select2
        $results = $statuses->map(function ($status) {
            return ['id' => $status->id, 'text' => $status->title];
        });

        // Flag for more results
        $pagination = ['more' => $statuses->count() === $perPage];

        return response()->json([
            'items' => $results,
            'pagination' => $pagination
        ]);
    }
}