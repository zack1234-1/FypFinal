<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\Session;

class StatusController extends Controller
{

    public function index()
    {
        $workspaceId = session()->get('workspace_id');

        $statuses = Status::where('workspace_id', $workspaceId)->get();

        $adminId = getAdminIdByUserRole();

        return view('status.list', compact('statuses','adminId'));
    }


    public function create()
    {
        return view('status.create');
    }


    public function store(Request $request)
    {
        $adminId = getAdminIdByUserRole();

        $validated = $request->validate([
            'title' => ['required']
        ]);

        $workspaceId = session()->get('workspace_id');

        if (!$workspaceId) {
            return response()->json(['error' => true, 'message' => 'No project selected.']);
        }

        $status = new Status();
        $status->title = $request->title;
        $status->slug = generateUniqueSlug($request->title, Status::class);
        $status->admin_id = $adminId;
        $status->workspace_id = $workspaceId;

        if ($status->save()) 
        {
            $roleIds = $request->input('role_ids');
            if ($roleIds) 
            {
                $status->roles()->attach($roleIds);
            }

         return redirect()->back()->with('success', 'Status Card created successfully.');

        } else {
            return response()->json(['error' => true, 'message' => 'Status couldn\'t be created.']);
        }
    } 


    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required'],
            'title' => ['required']
        ]);

        $status = Status::findOrFail($request->id);

        $status->title = $request->title;
        $status->slug = generateUniqueSlug($request->title, Status::class, $request->id);

        if ($status->save()) 
        {
            $roleIds = $request->input('role_ids');
            $status->roles()->sync($roleIds);

            return redirect()->back()->with('success', 'Status Card updated successfully..');
        } else {
            return response()->json([
                'error' => true,
                'message' => 'Status couldn\'t be updated.'
            ]);
        }
    }

    public function destroy($id)
    {
        $status = Status::find($id);

        if (!$status) 
        {
            return redirect()->back()->with('error', 'Status not found');
        }

        try {
            $status->delete();
            return redirect()->back()->with('success', 'Status Card deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Status Card deleted unsuccessfully.');
        }
    }

}