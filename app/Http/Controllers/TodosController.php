<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Client;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Services\DeletionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\UserWorkspace;

class TodosController extends Controller
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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {
    //     $workspaceId = session()->get('workspace_id');
        
    //     if (!$workspaceId) {
    //         abort(403, 'No workspace selected');
    //     }

    //     $currentUserId = $this->user->id;


    //     $todos = Todo::where('workspace_id', $workspaceId)  
    //                 ->whereJsonContains('user_id', (string) $currentUserId)
    //                 ->orderBy('is_completed', 'asc')   
    //                 ->orderBy('created_at', 'desc')
    //                 ->get();

    //     return view('todos.list', ['todos' => $todos]);
    // }

    // app/Http/Controllers/TodoController.php
    public function index()
    {
        $workspaceId = session('workspace_id');

        if (!$workspaceId) {
            abort(403, 'No workspace selected');
        }

        $currentUserId = auth()->id();
        $adminId = getAdminIdByUserRole();


        $todos = Todo::where('workspace_id', $workspaceId)
                    ->whereJsonContains('user_id', (string) $currentUserId)
                    ->orderBy('created_at', 'desc')
                    ->get();

        $allUserIds = collect($todos)
                    ->pluck('user_id')
                    ->flatten(1)
                    ->unique()
                    ->values();

        $usersById = User::whereIn('id', $allUserIds)->get()
                        ->keyBy('id');

        // Get team members under this admin
        $memberIds = TeamMember::where('admin_id', $adminId)->pluck('user_id')->toArray();

        // Ensure current user is included
        if (!in_array($currentUserId, $memberIds)) {
            $memberIds[] = $currentUserId;
        }

        // Fetch all users (team members + current user)
        $users = User::whereIn('id', $memberIds)->get();

        return view('todos.list', compact('todos', 'usersById', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('todos.create_todo');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $adminId = getAdminIdByUserRole();
        
        $formFields = $request->validate([
            'title' => ['required'],
            'priority' => ['required'],
            'description' => ['nullable'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required'],
            'user_id' => ['required', 'array'], 
        ]);

        $formFields['workspace_id'] = $this->workspace->id;
        $formFields['admin_id'] = $adminId;
        $formFields['status'] = 'pending'; 

        $todo = new Todo($formFields);
        $todo->creator()->associate(auth()->user()); 
        $todo->save();

        Session::flash('message', 'Todo created successfully.');
        return redirect()->route('todos.index')->with('message', 'Todo created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $todo = Todo::findOrFail($id);
        return view('todos.edit_todo', ['todo' => $todo]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $formFields = $request->validate([
            'id' => ['required'],
            'title' => ['required'],
            'priority' => ['required'],
            'description' => ['nullable'],
            'start_date' => ['required', 'before_or_equal:end_date'],
            'end_date' => ['required'],
            'user_id' => ['required', 'array'],
            'status' => ['required', 'in:pending,done'],
        ]);


        $todo = Todo::findOrFail($request->id);

        if ($todo->update($formFields)) 
        {
            Session::flash('message', 'Todo updated successfully.');
            return redirect()->route('todos.index')->with('message', 'Todo updated successfully.');
        } else {
            return redirect()->route('todos.index')->with('message', 'Todo cannot be updated successfully.');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {

        $response = DeletionService::delete(Todo::class, $id, 'Todo');
        return redirect()->route('todos.index')->with('message', 'Todo deleted successfully.');
    }

    public function update_status(Request $request)
    {
        $formFields = $request->validate([
            'id' => ['required'],
            'status' => ['required']

        ]);
        $id = $request->id;
        $status = $request->status;
        $todo = Todo::findOrFail($id);
        $todo->is_completed = $status;
        $statusText = $status == 1 ? 'Completed' : 'Pending';
        if ($todo->save()) {
            return response()->json(['error' => false, 'message' => 'Status updated successfully.', 'id' => $id, 'activity_message' => $this->user->first_name . ' ' . $this->user->last_name . ' marked todo ' . $todo->title . ' as ' . $statusText]);
        } else {
            return response()->json(['error' => true, 'message' => 'Status couldn\'t updated.']);
        }
    }

    public function get($id)
    {
        $todo = Todo::findOrFail($id);
        return response()->json(['todo' => $todo]);
    }

    public function destroy_multiple(Request $request)
    {
        $validatedData = $request->validate([
            'ids' => 'required|array', // Ensure 'ids' is present and an array
            'ids.*' => 'integer|exists:todos,id' // Ensure each ID in 'ids' is an integer and exists in the table
        ]);

        $ids = $validatedData['ids'];
        $deletedIds = [];
        $deletedTitles = [];

        // Perform deletion using validated IDs
        foreach ($ids as $id) {
            $todo = Todo::findOrFail($id);
            $deletedIds[] = $id;
            $deletedTitles[] = $todo->title;
            DeletionService::delete(Todo::class, $id, 'Todo');
        }
        Session::flash('message', 'Todo(s) deleted successfully.');
        return response()->json([
            'error' => false,
            'message' => 'Todo(s) deleted successfully.',
            'id' => $deletedIds,
            'titles' => $deletedTitles
        ]);
    }
}