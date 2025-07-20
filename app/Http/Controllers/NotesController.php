<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Services\DeletionService;

class NotesController extends Controller
{
    protected $workspace;
    protected $user;
    public function __construct()
    {

        $this->middleware(function ($request, $next) {
            // fetch session and use it in entire class with constructor
            $this->workspace = Workspace::find(session()->get('workspace_id'));
            $this->user = getAuthenticatedUser();
            return $next($request);
        });
    }

   public function index(Workspace $workspace)
   {
 
    $workspaceId = session()->get('workspace_id');
        
    if (!$workspaceId) {
        abort(403, 'No workspace selected');
    }

    $notes = Note::where('workspace_id', $workspaceId)  
                ->orderBy('created_at', 'desc')
                ->get();

    return view('notes.list', ['notes' => $notes]);

   }

    public function store(Request $request)
    {
        $adminId = getAdminIdByUserRole();
        
        $formFields = $request->validate([
            'title' => ['required'],
            'color' => ['required'],
            'description' => ['nullable'],
            'status_id' => ['required', 'exists:statuses,id'], 
        ]);

        try {
            $formFields['workspace_id'] = $this->workspace->id;
            $formFields['admin_id'] = $adminId;
            $formFields['creator_id'] = isClient() ? 'c_' . $this->user->id : 'u_' . $this->user->id;
            
            
           $note = Note::create($formFields);
           Session::flash('message', 'Card created successfully.');
           return redirect()->back()->with('success', 'Card created successfully.');
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => true, 
                'message' => 'Note couldn\'t be created: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $formFields = $request->validate([
            'id' => ['required'],
            'title' => ['required'],
            'color' => ['required'],
            'description' => ['nullable']
        ]);
        $note = Note::findOrFail($request->id);

        if ($note->update($formFields))
        {
         Session::flash('message', 'Card updated successfully.');
         return redirect()->back()->with('success', 'Card updated successfully.');

        } else {
            return response()->json(['error' => true, 'message' => 'Note couldn\'t updated.']);
        }
    }

    public function get($id)
    {
        $note = Note::findOrFail($id);
        return response()->json(['note' => $note]);
    }

    public function destroy($id)
    {
        $response = DeletionService::delete(Note::class, $id, 'Note');
        Session::flash('message', 'Card deleted successfully.');
        return back();
    }

    public function destroy_multiple(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:notes,id' // Ensure each ID in 'ids' is an integer and exists in the notes table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];

        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $note = Note::findOrFail($id);
            // Add any additional logic you need here, such as updating related data
            $deletedIds[] = $id;
            $deletedTitles[] = $note->title; // Assuming 'title' is a field in the notes table
            DeletionService::delete(Note::class, $id, 'Note');
        }
        Session::flash('message', 'Note(s) deleted successfully.');
        return response()->json([
            'error' => false,
            'message' => 'Note(s) deleted successfully.',
            'id' => $deletedIds,
            'titles' => $deletedTitles
        ]);
    }

    public function kanban_view()
    {
        $statuses = Status::all(); 
        $notes = Note::with('status')
                ->orderBy('updated_at', 'desc')
                ->get();

        return view('notes.kanban', [
            'statuses' => $statuses,
            'notes' => $notes
        ]);
    }

    public function updateNoteStatus(Request $request, $noteId)
    {
        $request->validate([
            'status_id' => ['required', 'exists:statuses,id'],
        ]);
    
        try {
            $note = Note::findOrFail($noteId);
            $note->status_id = $request->status_id;
            $note->save();
    
            return response()->json([
                'error' => false,
                'message' => 'Note status updated successfully.',
                'status_id' => $note->status_id,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => true,
                'message' => 'Note not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    
}