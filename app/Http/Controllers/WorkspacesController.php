<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\TeamMember;
use App\Models\UserWorkspace;
use App\Models\Client;
use App\Models\Workspace;
use App\Models\ActivityLog;
use App\Models\Plan;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class WorkspacesController extends Controller
{
    protected $workspace;
    protected $user;
    public function __construct()
    {

        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor
            $this->workspace = Workspace::find(session()->get('workspace_id'));
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }

    public function index()
    {
        $adminId = getAdminIdByUserRole();
        $members = TeamMember::where('admin_id', $adminId)->get();
        $memberIds = $members->pluck('user_id')->toArray();

        $currentUserId = auth()->id();
        if (!in_array($currentUserId, $memberIds)) {
            $memberIds[] = $currentUserId;
        }

        $users = User::whereIn('id', $memberIds)->get();

        $workspaces = Workspace::where('admin_id', $adminId)->get();

        return view('workspaces.workspaces', compact('workspaces', 'users'));
    }


    public function create()
    {
        $adminId = getAdminIdByUserRole();
        $admin = Admin::with('user', 'teamMembers.user')->find($adminId);

        $users = User::all();
        $clients = Client::where('admin_id', $adminId)->get();
        $auth_user = $this->user;

        return view('workspaces.create_workspace', compact('users', 'clients', 'auth_user', 'admin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $adminId = getAdminIdByUserRole();

        $workspace = new Workspace();
        $workspace->title = $request->title;
        $workspace->admin_id = $adminId;
        $workspace->user_id = auth()->id();
        $workspace->save();
        
 
        foreach ($request->user_ids as $userId) 
        {
                UserWorkspace::create([
                    'user_id' => $userId,
                    'workspace_id' => $workspace->id,
                    'admin_id' => $adminId,
                ]);
        }

        return redirect()->back()->with('success', 'Workspace created and users assigned.');
    }


    public function list()
    {
        $search = request('search');
        $sort = (request('sort')) ? request('sort') : "id";
        $order = (request('order')) ? request('order') : "DESC";
        $user_id = (request('user_id')) ? request('user_id') : "";
        $client_id = (request('client_id')) ? request('client_id') : "";

        $workspaces = isAdminOrHasAllDataAccess() ? $this->workspace : $this->user->workspaces();
        // dd(getAdminIDByUserRole());


        if ($user_id) {
            $user = User::find($user_id);
            $workspaces = $user->workspaces();
        }
        if ($client_id) {
            $client = Client::find($client_id);
            $workspaces = $client->workspaces();
        }
        $workspaces = $workspaces->when($search, function ($query) use ($search) {
            return $query->where('title', 'like', '%' . $search . '%')
                ->orWhere('id', 'like', '%' . $search . '%');
        });
        $workspaces->where('workspaces.admin_id', getAdminIDByUserRole());
        $totalworkspaces = $workspaces->count();

        $canCreate = checkPermission('create_workspaces');
        $canEdit = checkPermission('edit_workspaces');
        $canDelete = checkPermission('delete_workspaces');

        $workspaces = $workspaces->orderBy($sort, $order)
            ->paginate(request("limit"))
            ->through(function ($workspace) use ($canEdit, $canDelete, $canCreate) {

                $actions = '';

                if ($canEdit) {
                    $actions .= '<a href="javascript:void(0);" class="edit-workspace" data-id="' . $workspace->id . '" title="' . get_label('update', 'Update') . '">' .
                        '<i class="bx bx-edit mx-1"></i>' .
                        '</a>';
                }

                if ($canDelete) {
                    $actions .= '<button title="' . get_label('delete', 'Delete') . '" type="button" class="btn delete" data-id="' . $workspace->id . '" data-type="workspaces">' .
                '<i class="bx bx-trash text-danger mx-1"></i>' .
                '</button>';
                }

                if ($canCreate) {
                    $actions .= '<a href="javascript:void(0);" class="duplicate" data-id="' . $workspace->id . '" data-title="' . $workspace->title . '" data-type="workspaces" title="' . get_label('duplicate', 'Duplicate') . '">' .
                        '<i class="bx bx-copy text-warning mx-2"></i>' .
                        '</a>';
                }

                $actions = $actions ?: '-';

                $userHtml = '';
                if (!empty($workspace->users) && count($workspace->users) > 0) {
                    $userHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                    foreach ($workspace->users as $user) {
                        $userHtml .= "<li class='avatar avatar-sm pull-up'><a href='/users/profile/{$user->id}' target='_blank' title='{$user->first_name} {$user->last_name}'><img src='" . ($user->photo ? asset('storage/' . $user->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                    }
                    if ($canEdit) {
                        $userHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-workspace update-users-clients" data-id="' . $workspace->id . '"><span class="bx bx-edit"></span></a></li>';
                    }
                    $userHtml .= '</ul>';
                } else {
                    $userHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                    if ($canEdit) {
                        $userHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-workspace update-users-clients" data-id="' . $workspace->id . '">' .
                            '<span class="bx bx-edit"></span>' .
                            '</a>';
                    }
                }

                $clientHtml = '';
                if (!empty($workspace->clients) && count($workspace->clients) > 0) {
                    $clientHtml .= '<ul class="list-unstyled users-list m-0 avatar-group d-flex align-items-center">';
                    foreach ($workspace->clients as $client) {
                        $clientHtml .= "<li class='avatar avatar-sm pull-up'><a href='/clients/profile/{$client->id}' target='_blank' title='{$client->first_name} {$client->last_name}'><img src='" . ($client->photo ? asset('storage/' . $client->photo) : asset('storage/photos/no-image.jpg')) . "' alt='Avatar' class='rounded-circle' /></a></li>";
                    }
                    if ($canEdit) {
                        $clientHtml .= '<li title=' . get_label('update', 'Update') . '><a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-workspace update-users-clients" data-id="' . $workspace->id . '"><span class="bx bx-edit"></span></a></li>';
                    }
                    $clientHtml .= '</ul>';
                } else {
                    $clientHtml = '<span class="badge bg-primary">' . get_label('not_assigned', 'Not Assigned') . '</span>';
                    if ($canEdit) {
                        $clientHtml .= '<a href="javascript:void(0)" class="btn btn-icon btn-sm btn-outline-primary btn-sm rounded-circle edit-workspace update-users-clients" data-id="' . $workspace->id . '">' .
                            '<span class="bx bx-edit"></span>' .
                            '</a>';
                    }
                }
                return [
                    'id' => $workspace->id,
                'title' => '<a href="workspaces/switch/' . $workspace->id . '">' . $workspace->title . '</a>' . ($workspace->is_primary ? ' <span class="badge bg-success">' . get_label('primary', 'Primary') . '</span>' : ''),
                'users' => $userHtml,
                'clients' => $clientHtml,
                'created_at' => format_date($workspace->created_at, true),
                'updated_at' => format_date($workspace->updated_at, true),
                'actions' => $actions
                ];
            });

        return response()->json([
            "rows" => $workspaces->items(),
            "total" => $totalworkspaces,
        ]);
    }


    public function edit($id)
    {
        $workspace = Workspace::findOrFail($id);
        $admin = Admin::with('user', 'teamMembers.user')->find(getAdminIdByUserRole());
        $clients = Client::where('admin_id', getAdminIdByUserRole())->get();
        return view('workspaces.update_workspace', compact('workspace', 'clients', 'admin'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $workspace = Workspace::findOrFail($id);
        $workspace->title = $request->title;
        $workspace->save();

        $workspace->users()->sync($request->user_ids);

        return redirect()->back()->with('success', 'Workspace updated successfully.');
    }


    public function destroy($id)
    {
        // dd($id);

        if ($this->workspace->id != $id) {
            $response = DeletionService::delete(Workspace::class, $id, 'Workspace');
           return redirect()->back()->with('success', 'Workspace deleted successfully.');
        } else {
            return response()->json(['error' => true, 'message' => 'Current workspace couldn\'t deleted.']);
        }
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:workspaces,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedWorkspaces = [];
        $deletedWorkspaceTitles = [];
        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $workspace = Workspace::find($id);
            if ($workspace) {
                $deletedWorkspaces[] = $id;
                $deletedWorkspaceTitles[] = $workspace->title;
                DeletionService::delete(Workspace::class, $id, 'Workspace');
            }
        }

        return response()->json(['error' => false, 'message' => 'Workspace(s) deleted successfully.', 'id' => $deletedWorkspaces, 'titles' => $deletedWorkspaceTitles]);
    }

    public function switch($id)
    {
        if (Workspace::findOrFail($id)) {
            session()->put('workspace_id', $id);
            return back()->with('message', 'Workspace changed successfully.');
        } else {
            return back()->with('error', 'Workspace not found.');
        }
    }

    public function remove_participant()
    {
        $workspace = Workspace::findOrFail(session()->get('workspace_id'));
        if ($this->user->hasRole('client')) {
            $workspace->clients()->detach($this->user->id);
        } else {
            $workspace->users()->detach($this->user->id);
        }
        $workspace_id = isset($this->user->workspaces[0]['id']) && !empty($this->user->workspaces[0]['id']) ? $this->user->workspaces[0]['id'] : 0;
        $data = ['workspace_id' => $workspace_id];
        session()->put($data);
        Session::flash('message', 'Removed from workspace successfully.');
        return response()->json(['error' => false]);
    }

    public function duplicate($id)
    {
        // Define the related tables for this workspace
        $relatedTables = ['users', 'clients']; // Include related tables as needed

        // Use the general duplicateRecord function
        $title = (request()->has('title') && !empty(trim(request()->title))) ? request()->title : '';
        $duplicate = duplicateRecord(Workspace::class, $id, $relatedTables, $title);
        $workspace = Workspace::find($duplicate->id);
        $workspace->update(['is_primary' => 0]);
        if (!$duplicate) {
            return response()->json(['error' => true, 'message' => 'Workspace duplication failed.']);
        }
        if (request()->has('reload') && request()->input('reload') === 'true') {
            Session::flash('message', 'Workspace duplicated successfully.');
        }
        return response()->json(['error' => false, 'message' => 'Workspace duplicated successfully.', 'id' => $id]);
    }
    public function get($id)
    {
        $workspace = Workspace::with('users', 'clients')->findOrFail($id);

        return response()->json(['error' => false, 'workspace' => $workspace]);
    }
}
