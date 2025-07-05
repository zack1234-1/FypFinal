<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use App\Models\Folder;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    public function index()
    {
        return view('files.index');
    }

    // public function upload(Request $request)
    // {
    //     $request->validate([
    //         'files.*' => 'required|file|max:2048',
    //         'folderName' => 'nullable|string|max:255',
    //     ]);


    //     if (!$request->hasFile('files')) {
    //         return back()->with('error', 'No files were uploaded.');
    //     }

    //     $folderName = $request->input('folderName');
    //     $uploadedFiles = [];
    //     $failedFiles = [];

    //     if ($folderName) 
    //     {
    //         $folder = Folder::firstOrCreate(['name' => $folderName]);
    //         $folderId = $folder->id;
    //     } else {
    //         $folderId = null;
    //     }

    //     foreach ($request->file('files') as $file) {
    //         try {
    //             $content = file_get_contents($file->getRealPath());
    //             if ($content === false) {
    //                 throw new \Exception("Could not read file contents");
    //             }

    //             File::create([
    //                 'filename' => $file->getClientOriginalName(),
    //                 'mime_type' => $file->getMimeType(),
    //                 'content' => $content,
    //                 'folder_id' => $folderId ?: null,
    //             ]);
    //             $uploadedFiles[] = $file->getClientOriginalName();

    //         } catch (\Exception $e) {
    //             $failedFiles[] = $file->getClientOriginalName();
    //             \Log::error("File upload failed: " . $e->getMessage());
    //         }
    //     }

    //     $message = '';
    //     if (!empty($uploadedFiles)) {
    //         $message = 'Successfully uploaded: ' . implode(', ', $uploadedFiles) . '. ';
    //     }
    //     if (!empty($failedFiles)) {
    //         $message .= 'Failed to upload: ' . implode(', ', $failedFiles);
    //     }

    //     return back()->with(empty($uploadedFiles) ? 'error' : 'success', $message);
    // }

    public function upload(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:2048',
            'folderName' => 'nullable|string|max:255',
        ]);

        if (!$request->hasFile('files')) {
            return back()->with('error', 'No files were uploaded.');
        }

        $folderName = $request->input('folderName');
        $uploadedFiles = [];
        $failedFiles = [];

        // Get or create folder record in DB
        $folderId = $folderName
            ? Folder::firstOrCreate(['name' => $folderName])->id
            : null;

        foreach ($request->file('files') as $file) {
            try {
                // Get file binary content
                $originalName = $file->getClientOriginalName();
                $content = file_get_contents($file->getRealPath());

                File::create([
                    'filename' => $originalName,
                    'mime_type' => $file->getMimeType(),
                    'content' => $content,
                    'size' => $file->getSize(),
                    'folder_id' => $folderId,
                ]);

                $destinationPath = public_path('uploads');
                $file->move($destinationPath, $originalName);

                $uploadedFiles[] = $file->getClientOriginalName();
            } catch (\Exception $e) {
                $failedFiles[] = $file->getClientOriginalName();
                \Log::error("File upload failed for {$file->getClientOriginalName()}: " . $e->getMessage());
            }
        }

        $message = '';
        if (!empty($uploadedFiles)) {
            $message = 'Successfully uploaded: ' . implode(', ', $uploadedFiles) . '. ';
        }
        if (!empty($failedFiles)) {
            $message .= 'Failed to upload: ' . implode(', ', $failedFiles);
        }

        return back()->with(empty($uploadedFiles) ? 'error' : 'success', $message);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'file' => 'nullable|file|max:10240',
            'folder' => 'nullable|string|max:255'
        ]);

        try {
            $file = File::findOrFail($id);

            $updateData = [];

            if ($request->has('folder')) {
                $updateData['folder'] = $request->folder;
            }

            if ($request->hasFile('file')) {
                $newFile = $request->file('file');
                $originalName = $newFile->getClientOriginalName();
                $cleanName = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $originalName);

                $mimeType = $newFile->getMimeType();
                if (empty($mimeType)) {
                    $mimeType = mime_content_type($newFile->getPathname());
                }

                $binaryContent = file_get_contents($newFile->getRealPath());

                $updateData = array_merge($updateData, [
                    'filename' => $cleanName,
                    'mime_type' => $mimeType,
                    'content' => $binaryContent,
                    'updated_at' => now(), 
                ]);
            }

            if (!empty($updateData)) {
                $file->update($updateData);
            }

            return back()->with('success', 'File updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return back()->with('error', 'File not found');
        } catch (\Exception $e) {
            return back()->with('error', 'Error updating file: ' . $e->getMessage());
        }
    }

    // public function download($id)
    // {
    //     try 
    //     {
    //         $file = File::findOrFail($id);

    //         // Authorization check
    //         if (!auth()->user()->can('view', $file)) {
    //             abort(403, 'Unauthorized access');
    //         }

    //         // Check if file content exists
    //         if (empty($file->content)) {
    //             abort(404, 'File content not found');
    //         }

    //         // Get MIME type
    //         $finfo = new \finfo(FILEINFO_MIME_TYPE);
    //         $mimeType = $finfo->buffer($file->content);
            
    //         // Get filename with proper extension
    //         $filename = $this->getDownloadFilename($file, $mimeType);

    //         // Return the file download response
    //         return response()->streamDownload(
    //             function () use ($file) {
    //                 echo $file->content;
    //             },
    //             $filename,
    //             [
    //                 'Content-Type' => $mimeType,
    //                 'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    //                 'Content-Length' => strlen($file->content),
    //                 'Pragma' => 'no-cache',
    //                 'Expires' => '0',
    //             ]
    //         );

    //     } catch (\Exception $e) {
    //         // Log the error for debugging
    //         \Log::error('File download failed: ' . $e->getMessage());
    //         abort(500, 'File download failed');
    //     }
    // }

    public function delete($id)
    {
        $file = File::findOrFail($id);
        
        $file->delete();

        return back()->with('success', 'File deleted successfully');
    }
    
    public function view($id)
    {
        $file = File::findOrFail($id);

        return Response::make($file->content, 200, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => 'inline; filename="' . $file->filename . '"'
        ]);
    }

    public function displayFolder()
    {
        $files = File::with('folder')->get()->groupBy('folder_id');
        $folders = Folder::all();

        return view('files.displayFolder', compact('files', 'folders'));
    }

    public function displayFile()
    {
        $files = File::where(function ($query) {
                        $query->whereNull('folder_id')
                            ->orWhere('folder_id', '');
                    })
                    ->get();

        return view('files.displayFiles', compact('files'));
    }

    public function updateFolder(Request $request, Folder $folder)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $folder->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'message' => 'Folder updated successfully',
            'folder' => $folder
        ]);
    }

    public function destroyFolder(Folder $folder)
    {
        // Optional: Check if folder is empty before deletion
        if ($folder->files()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Folder is not empty. Delete files first.'
            ], 422);
        }

        $folder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Folder deleted successfully'
        ]);
    }


}
