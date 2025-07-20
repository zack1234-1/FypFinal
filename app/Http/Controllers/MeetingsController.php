<?php

namespace App\Http\Controllers;
use App\Models\Recording;
use Illuminate\Http\Request;
use App\Services\ZoomService;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Support\Facades\File;

class MeetingsController extends Controller
{
    protected $zoomService;

    public function __construct(ZoomService $zoomService)
    {
        $this->zoomService = $zoomService;
    }

    public function index()
    {
        $meeting = Meeting::latest()->first(); 
        $users = User::all(); 

        return view('meetings.meetings', compact('meeting', 'users'));
    }

    public function recordingIndex()
    {
        $workspaceId = session()->get('workspace_id');

        $recordings = Recording::where('workspace_id', $workspaceId)
            ->where('mime_type', 'video/mp4') 
            ->latest()
            ->get();

        return view('recordings.index', compact('recordings'));
    }

    public function moveAndStoreZoomRecordings()
    {
        $sourceDir = 'C:\Users\USER\Documents\Zoom';
        $destinationDir = public_path('recordings');
        $workspaceId = session()->get('workspace_id');

        if (!is_dir($sourceDir)) {
            return 'Zoom recordings folder not found.';
        }

        if (!is_dir($destinationDir)) {
            mkdir($destinationDir, 0775, true);
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourceDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $stored = 0;

        foreach ($files as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();
                $sourcePath = $file->getPathname();
                $destinationPath = $destinationDir . DIRECTORY_SEPARATOR . $filename;

                $alreadyExists = Recording::where('file_name', $filename)->exists();
                if ($alreadyExists) {
                    continue;
                }

                if (!file_exists($destinationPath)) {
                    copy($sourcePath, $destinationPath);
                }

                $binaryData = file_get_contents($sourcePath);

                Recording::create([
                    'file_name' => $filename,
                    'mime_type' => File::mimeType($sourcePath),
                    'recording_blob' => $binaryData,
                    'user_id' => auth()->id() ?? null,
                    'workspace_id' =>  $workspaceId  ?? null,
                ]);

                $stored++;
            }
        }

        return "$stored recording file(s) moved and stored in the database.";
    }

    public function download($id)
    {
        $recording = Recording::findOrFail($id);

        return response($recording->recording_blob)
            ->header('Content-Type', $recording->mime_type)
            ->header('Content-Disposition', 'attachment; filename="' . $recording->file_name . '"');
    }

    public function stream($id)
    {
        $recording = Recording::findOrFail($id);
        $content = $recording->recording_blob;
        $size = strlen($content);
        $mime = $recording->mime_type ?? 'video/mp4';
        $fileName = $recording->file_name ?? 'video.mp4';

        $start = 0;
        $end = $size - 1;
        $status = 200;

        $headers = [
            'Content-Type' => $mime,
            'Content-Length' => $size,
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"'
        ];

        if (request()->headers->has('Range')) {
            $range = request()->header('Range');
            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = intval($matches[1]);
                if (!empty($matches[2])) {
                    $end = intval($matches[2]);
                }

                $length = $end - $start + 1;
                $status = 206;
                $headers['Content-Range'] = "bytes $start-$end/$size";
                $headers['Content-Length'] = $length;
                $content = substr($content, $start, $length);
            }
        }

        return response($content, $status, $headers);
    }


}
