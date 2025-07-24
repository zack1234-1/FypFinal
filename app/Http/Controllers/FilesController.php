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

    public function upload(Request $request)
    {
        $workspaceId = session()->get('workspace_id');

        $request->validate([
            'files.*' => 'required|file|max:512000', 
            'folderName' => 'nullable|string|max:255',
        ]);

        if (!$request->hasFile('files')) {
            return back()->with('error', 'No files were uploaded.');
        }

        $folderName = $request->input('folderName');
        $uploadedFiles = [];
        $failedFiles = [];

        $folderId = null;

        if ($folderName) {
        $folder = Folder::firstOrCreate(
                ['name' => $folderName, 'workspace_id' => $workspaceId],
                [
                    'workspace_id' => $workspaceId,
                    'creator_id' => auth()->id(),
                ]
            );
            $folderId = $folder->id;
        }

        foreach ($request->file('files') as $file) {
            try {
                $originalName = $file->getClientOriginalName();
                $content = file_get_contents($file->getRealPath());

                File::create([
                    'filename'     => $originalName,
                    'mime_type'    => $file->getMimeType(),
                    'content'      => $content,
                    'size'         => $file->getSize(),
                    'folder_id'    => $folderId,
                    'workspace_id' => $workspaceId,
                    'creator_id' => auth()->id(),
                ]);

                $destinationPath = public_path('uploads');
                $file->move($destinationPath, $originalName);

                $uploadedFiles[] = $originalName;
            } catch (\Exception $e) {
                $failedFiles[] = $originalName;
                \Log::error("File upload failed for {$originalName}: " . $e->getMessage());
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
        $workspaceId = session()->get('workspace_id');

        $files = File::with('folder')
                    ->where('workspace_id', $workspaceId)
                    ->get()
                    ->groupBy('folder_id');

        $folders = Folder::where('workspace_id', $workspaceId)->get();

        return view('files.displayFolder', compact('files', 'folders'));
    }

    public function displayFile()
    {
        $workspaceId = session()->get('workspace_id');

        $files = File::where(function ($query) {
                        $query->whereNull('folder_id')
                            ->orWhere('folder_id', '');
                    })
                    ->where('workspace_id', $workspaceId)
                    ->get();

        return view('files.displayFiles', compact('files'));
    }


    public function updateFolder(Request $request, Folder $folder)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $folder->update(['name' => $request->name]);

        return back()->with('success', 'Folder updated successfully');
    }

    public function destroyFolder(Folder $folder)
    {
        if ($folder->files()->exists()) {
          return back()->with('error', 'Folder is not empty, please clean all the files in the folder.');
        }

        $folder->delete();

      return back()->with('success', 'Folder deleted successfully.');
    }


}
