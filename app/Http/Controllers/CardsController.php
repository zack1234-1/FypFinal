<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Status;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;
use App\Services\DeletionService;

class CardsController extends Controller
{

    public function index(Workspace $workspace)
    {
        $workspaceId = session()->get('workspace_id');

        $notes = Card::where('workspace_id', $workspaceId)
                    ->orderBy('created_at', 'desc')
                    ->get();

        $statuses = Status::where('workspace_id', $workspaceId)
                  ->get();

        return view('cards.list', [
            'notes' => $notes,
            'statuses' => $statuses
        ]);
    }

    public function store(Request $request)
    {
        $adminId = getAdminIdByUserRole();
        $workspaceId = session()->get('workspace_id');
        
        $formFields = $request->validate([
            'title' => ['required'],
            'description' => ['nullable'],
            'status_id' => ['required', 'exists:statuses,id'], 
        ]);

        try 
        {
            $formFields['workspace_id'] = $workspaceId;
            $formFields['admin_id'] = $adminId;
            $formFields['creator_id'] = auth()->id();

            $note = Card::create($formFields);
            
            return redirect()->back()->with('success', 'Card created successfully.');
            
        } catch (\Exception $e) {
             return redirect()->back()->with('error', 'Card created unsuccessfully.');
        }
    }

    public function update(Request $request)
    {
        $formFields = $request->validate([
            'id' => ['required'],
            'title' => ['required'],
            'description' => ['nullable']
        ]);

        $note = Card::findOrFail($request->id);

        if ($note->update($formFields)) {
             return redirect()->back()->with('success', 'Card updated successfully.');
        } else {
            return redirect()->back()->with('error', 'Card updated unsuccessfully.');
        }
    }

    public function destroy($id)
    {
        $note = Card::find($id);

        if (!$note) {
              return redirect()->back()->with('error', 'Card not found.');
        }

        try {
            $note->delete();
            return redirect()->back()->with('success', 'Card deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Card deleted unsuccessfully.');
        }
    }


    public function kanban_view()
    {
        $statuses = Status::all(); 
        $notes = Card::with('status')
                ->orderBy('updated_at', 'desc')
                ->get();

        return view('cards.kanban', [
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
            $note = Card::findOrFail($noteId);
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