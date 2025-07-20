<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Session;
use App\Models\Message;
use App\Models\Comment;
use Illuminate\Http\Request;

class MessageBoardController extends Controller
{
    public function index()
    {
        $workspaceId = session('workspace_id');

        $messages = Message::where('workspace_id', $workspaceId)
                            ->latest()
                            ->get();

        return view('messages.index', compact('messages'));
    }

    public function store(Request $request)
    {
        $workspaceId = session('workspace_id');

        if ($request->has('comment') && $request->has('message_id')) {
            $request->validate([
                'message_id' => 'required|exists:messages,id',
                'comment' => 'required|string',
            ]);

            Comment::create([
                'message_id' => $request->message_id,
                'comment' => $request->comment,
                'creator_id' => auth()->id()
            ]);

            Session::flash('success', 'Comment added successfully.');
            return back()->with('success', 'Comment added.');
        }

        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        Message::create([
            'title' => $request->title,
            'content' => $request->content,
            'workspace_id' => $workspaceId,
            'creator_id'=> auth()->id(),
        ]);

        Session::flash('success', 'Message posted successfully.');
        return redirect()->route('message-board.index')->with('success', 'Message posted successfully.');
    }


    public function destroy($type, $id)
    {
        if ($type === 'message') {
            $message = Message::findOrFail($id);
            $message->delete();
            Session::flash('success', 'Message deleted successfully.');
            return back()->with('success', 'Message deleted successfully.');
        }

        if ($type === 'comment') {
            $comment = Comment::findOrFail($id);
            $comment->delete();
            Session::flash('success', 'Comment deleted successfully.');
            return back()->with('success', 'Comment deleted successfully.');
        }

        return back()->with('error', 'Invalid delete action.');
    }

    public function edit(Message $message)
    {
        return view('messages.edit', compact('message'));
    }

    public function update(Request $request, Message $message)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        $message->update($request->only('title', 'content'));
        Session::flash('success', 'Message updated successfully.');
        return redirect()->route('message-board.index')->with('success', 'Message updated successfully.');
    }
}
