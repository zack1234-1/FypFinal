<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Todo;
use App\Models\Workspace;
use App\Models\User;
use App\Models\UserWorkspace;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Check authentication first
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // 2. Get project ID with fallback
        $projectId = session('workspace_id');
        if (!$projectId) {
            return redirect()->route('workspaces.index')
                ->with('error', 'Please select a project first');
        }

        $project = Workspace::findOrFail($projectId);

        $todos = Todo::where('workspace_id', $projectId)->get();
        $workspaces = Workspace::where('user_id', auth()->id())->get();

        $adminId = auth()->user()->role === 'admin' ? auth()->id() : null;

        $users = UserWorkspace::where('admin_id', $adminId)
                ->where('workspace_id', $projectId)
                ->distinct('user_id')
                ->get(['user_id']);

        $activities = $project->activity_logs()
                    ->latest()
                    ->limit(10)
                    ->get();

        return view('dashboard', compact(
            'project', 
            'todos',
            'users',
            'workspaces',
            'activities',
            'adminId'
        ));
    }
}
