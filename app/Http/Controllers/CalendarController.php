<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Meeting;
use App\Models\Todo;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Auth;


class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::all([
            'id',
            'title',
            'start_date_time as start',
            'end_date_time as end',
            'user_id'
        ]);

        return view('calendar.index', ['events' => $events]);
    }

    public function fetchEvents()
    {
        $events = Event::orderBy('type')  // Sort by type
                        ->orderBy('start_date_time') // Then by start time
                        ->get()
                        ->map(function ($event) {
                            return [
                                'id'    => $event->id,
                                'title' => $event->title,
                                'start' => $event->start_date_time,
                                'end'   => $event->end_date_time,
                                'description' => $event->description,
                                'type'  => $event->type, // 'meeting' or 'todo'
                            ];
                        });

        return response()->json($events);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required',
            'description' => 'nullable|string|max:1000', 
            'type' => 'required|in:meeting,todo',
            'start_date_time' => 'required|date',
            'end_date_time' => 'required|date|after_or_equal:start_date_time',
            'workspace_id' => 'required|integer|exists:workspaces,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $event = Event::create($validated);

        if ($event->type === 'meeting') {
            Meeting::create([
                'admin_id' => getAdminIDByUserRole(),
                'workspace_id' => session('workspace_id'),
                'user_id' => auth()->user()->id,
                'title' => $event->title,
                'start_date_time' => $event->start_date_time,
                'end_date_time' => $event->end_date_time,
            ]);
        } else {
            Todo::create([
                'admin_id' => getAdminIDByUserRole(),
                'workspace_id' => session('workspace_id'),
                'user_id' => auth()->user()->id,
                'title' => $event->title,
                'description' => $request->description ?? '',
                'priority' => 'open',
                'is_completed' => false,
                'creator_id' => auth()->user()->id,
                'creator_type' => 2,
                'start_date' => $event->start_date_time,
                'end_date' => $event->end_date_time,
            ]);
        }

      return redirect()->back()->withInput()->with('success', 'Event created successfully!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date_time' => 'required|date',
            'end_date_time' => 'required|date|after_or_equal:start_date_time',
        ]);

        $event = Event::findOrFail($id);

        $event->title = $validated['title'];
        $event->description = $validated['description'] ?? '';
        $event->start_date_time = $validated['start_date_time'];
        $event->end_date_time = $validated['end_date_time'];
        $event->save();

        return redirect()->back()->with('success', 'Event updated successfully.');
    }

    public function destroy($id)
    {
        Event::findOrFail($id)->delete();
        return redirect()->back()->withInput()->with('success', 'Event deleted successfully!');
    }
}
