<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Services\ZoomService;
use App\Models\Meeting;

class ZoomController extends Controller
{
    protected $zoomService;

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    public function store(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'start_time' => 'required|date',
            'duration' => 'required|integer',
        ]);

        try 
        {
            $zoomMeeting = $this->zoomService->createMeeting(
                $request->topic,
                $request->start_time,
                $request->duration
            );

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

    public function fetchRecordings()
    {
        $clientId = env('ZOOM_CLIENT_ID');
        $clientSecret = env('ZOOM_CLIENT_SECRET');
        $accountId = env('ZOOM_ACCOUNT_ID');

        $tokenResponse = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id' => $accountId,
            ]);

        if ($tokenResponse->failed()) {
            return response()->json(['error' => 'Failed to get Zoom access token'], 500);
        }

        $accessToken = $tokenResponse->json()['access_token'];

        $response = Http::withToken($accessToken)->get('https://api.zoom.us/v2/users/me/recordings', [
            'page_size' => 30,
            'from' => now()->subDays(30)->format('Y-m-d'),
            'to' => now()->format('Y-m-d'),
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch recordings'], 500);
        }

        $recordings = $response->json()['meetings'];

        foreach ($recordings as $recording) 
        {
            $recordingFiles = $recording['recording_files'] ?? [];

            $meeting = Meeting::where('start_time', $recording['start_time'])
                ->where('topic', $recording['topic'])
                ->first();

            if ($meeting) {
                $meeting->recording = json_encode($recordingFiles);
                $meeting->save();
            }
        }

        return response()->json(['message' => 'Zoom recordings saved successfully.']);
    }
}
