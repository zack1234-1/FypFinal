<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\Workspace;
use App\Models\User;
use App\Models\UserWorkspace;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->user()->id;
        $user = auth()->user();
        $workspaceId = session('workspace_id');
        $workspace = Workspace::find($workspaceId);
        $todos = Todo::where('workspace_id', $workspaceId)->get();
        $workspaces = Workspace::where('user_id', $userId)->get();
        $adminId =  getAdminIDByUserRole();
        $users = UserWorkspace::where('admin_id', $adminId)
                ->where('workspace_id', $workspaceId)
                ->distinct('user_id')
                ->get(['user_id']);

         if ($workspace) {
            $activities = $workspace->activity_logs()->orderBy('id', 'desc')->limit(10)->get();
        } else {
            $activities = collect();
        }

        $planId = session('subscription_plan_id');

        return view('dashboard', compact('todos', 'workspaces','users','user','activities'));
    }
}
