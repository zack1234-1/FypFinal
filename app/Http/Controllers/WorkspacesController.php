<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\TeamMember;
use App\Models\UserWorkspace;
use App\Models\Workspace;
use App\Models\ChMessage;
use App\Models\Message;
use App\Models\Event;
use App\Models\Folder;
use App\Models\File;
use App\Models\Recording;
use App\Models\Status;
use App\Models\Todo;
use App\Models\Card;
use App\Models\ChatGroup;
use App\Models\ActivityLog;
use App\Models\Plan;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class WorkspacesController extends Controller
{
    public function index()
    {
        $adminId = getAdminIdByUserRole();
        $members = TeamMember::where('admin_id', $adminId)->get();
        $memberIds = $members->pluck('user_id')->toArray();

        $currentUserId = auth()->id();
        if (!in_array($currentUserId, $memberIds)) {
            $memberIds[] = $currentUserId;
        }

        $adminUserId =Admin::where('id', $adminId)->value('user_id');

        if (!in_array($adminUserId, $memberIds)) {
            $memberIds[] = $adminUserId;
        }
        
        $memberIds = array_unique($memberIds);

        $users = User::whereIn('id', $memberIds)->get();
        $workspaces = Workspace::where('admin_id', $adminId)->get();

        return view('workspaces.workspaces', compact('workspaces', 'users'));
    }

    public function store(Request $request)
    {
        
        if (empty($request->user_ids)) 
        {
        return redirect()->back()->with('error', 'Please assign at least one user to the project.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $adminId = getAdminIdByUserRole();

        $project = new Workspace();
        $project->title = $request->title;
        $project->admin_id = $adminId;
        $project->user_id = auth()->id();
        $project->save();
        
 
        foreach ($request->user_ids as $userId) 
        {
                UserWorkspace::create([
                    'user_id' => $userId,
                    'workspace_id' => $project->id,
                    'admin_id' => $adminId,
                ]);
        }

        return redirect()->back()->with('success', 'Project created and users assigned.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        if (!in_array(auth()->id(), $request->user_ids)) {
            return redirect()->back()->with('error', 'Project must be assigned to yourself.');
        }

        $project = Workspace::findOrFail($id);
        $project->title = $request->title;
        $project->save();

        $project->users()->sync($request->user_ids);

        return redirect()->back()->with('success', 'Project updated successfully.');
    }


    public function destroy($id)
    {
        $workspaceId = $id;
        $adminId = getAdminIdByUserRole();

        $workspace = Workspace::find($workspaceId);

        if (!$workspace) {
            return redirect()->back()->with('error', 'Workspace not found.');
        }

        try 
        {
            Todo::where('workspace_id', $workspaceId)->delete();
            TeamMember::where('admin_id', $adminId)->delete();
            Status::where('workspace_id', $workspaceId)->delete();
            ChMessage::where('workspace_id', $workspaceId)->delete();
            ChatGroup::where('workspace_id', $workspaceId)->delete();
            Recording::where('workspace_id', $workspaceId)->delete();
            Message::where('workspace_id', $workspaceId)->delete();
            Event::where('workspace_id', $workspaceId)->delete();
            Card::where('workspace_id', $workspaceId)->delete();

            $folders = Folder::where('workspace_id', $workspaceId)->get();
            foreach ($folders as $folder) {
                File::where('folder_id', $folder->id)->delete();
                $folder->delete();
            }

            DB::table('user_workspace')->where('workspace_id', $workspaceId)->delete();
            $workspace->delete();

            return redirect()->back()->with('success', 'Workspace and all related data deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete workspace: ' . $e->getMessage());
        }
    }


    public function switch($id)
    {
        if (Workspace::findOrFail($id)) {
            session()->put('workspace_id', $id);
            return back()->with('success', 'Project changed successfully.');
        } else {
            return back()->with('error', 'Project not found.');
        }
    }

}
