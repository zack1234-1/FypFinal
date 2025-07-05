<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ZoomService;
use App\Models\Meeting;
use App\Models\User;

class MeetingsController extends Controller
{
    protected $zoomService;

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    public function index()
    {
        $meetings = Meeting::orderBy('start_time', 'desc')->get();
        $users = User::all(); 

        return view('meetings.meetings', compact('meetings', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'duration' => 'required|integer',
        ]);

        try {
            $zoomMeeting = $this->zoomService->createMeeting(
                $request->topic,
                $request->start_time,
                $request->duration
            );

            // Optional: store in DB
            Meeting::create([
                'topic' => $request->topic,
                'start_time' => $request->start_time,
                'duration' => $request->duration,
                'zoom_join_url' => $zoomMeeting['join_url'],
                'zoom_start_url' => $zoomMeeting['start_url'],
                'created_by' => auth()->id(),
            ]);

            return redirect()->back()->with('success', 'Zoom meeting created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function saveRecording(Request $request, $meetingId)
    {
        $meeting = Meeting::findOrFail($meetingId);

        $meeting->recording = $request->input('recording_url');
        $meeting->save();

        return back()->with('success', 'Recording saved.');
    }

}
