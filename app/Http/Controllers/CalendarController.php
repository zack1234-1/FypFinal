<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\Todo;
use App\Models\User;
use App\Models\UserWorkspace;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth;


class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $workspaceId = session('workspace_id');
        $type = $request->query('type'); // get ?type= from URL

        $users = User::whereHas('workspaces', function($query) use ($workspaceId) {
                $query->where('workspaces.id', $workspaceId);
            })
            ->orWhere('id', auth()->id())
            ->get(['id', 'first_name', 'last_name', 'email']);

        $eventsQuery = Event::where('workspace_id', $workspaceId);

        if ($type) {
            $eventsQuery->where('type', $type);
        }

        $events = $eventsQuery->get();

        return view('calendar.index', [
            'events' => $events,
            'users' => $users,
            'currentUserId' => auth()->id()
        ]);
    }


    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required',
            'description' => 'nullable',
            'type' => 'required|in:meeting,todo',
            'start_date_time' => 'required|date',
            'end_date_time' => 'required|date|after_or_equal:start_date_time',
            'assigned_members' => 'required|array|min:1',
        ]);

        $validated['assigned_members'] = implode(',', $validated['assigned_members']);

        $event->update($validated);

        return redirect()->route('calendar.index')->with('success', 'Event updated successfully.');
    }

    public function destroy($id)
    {
        Event::findOrFail($id)->delete();
         return redirect()->route('calendar.index')->with('success', 'Event deleted successfully.');
    }


    public function store(Request $request)
    {
        $adminId = getAdminIdByUserRole();

        $formFields = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:meeting,todo',
            'start_date_time' => 'required|date',
            'end_date_time' => 'required|date|after_or_equal:start_date_time',
            'assigned_members' => 'required|array|min:1',
            'assigned_members.*' => 'exists:users,id',
            'workspace_id' => 'required|exists:workspaces,id'
        ]);


        $formFields['creator_id'] = auth()->id();
        $formFields['assigned_members'] = implode(',', $formFields['assigned_members']);

        $event = new Event($formFields);
        $event->save();
          
        return redirect()->route('calendar.index')->with('success', 'Event created successfully.');
    }
}
