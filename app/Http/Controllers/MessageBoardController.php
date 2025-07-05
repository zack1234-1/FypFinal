<?php

namespace App\Http\Controllers;

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
                'user_id' => auth()->id(),
                'workspace_id' => $workspaceId,
            ]);

            return back()->with('success', 'Comment added.');
        }

        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
        ]);

        Message::create([
            'title' => $request->title,
            'content' => $request->content,
            'user_id' => auth()->id(),
            'workspace_id' => $workspaceId,
        ]);

        return redirect()->route('message-board.index')->with('success', 'Message posted.');
    }


    public function destroy($type, $id)
    {
        if ($type === 'message') {
            $message = Message::findOrFail($id);
            $message->delete();
            return back()->with('success', 'Message deleted.');
        }

        if ($type === 'comment') {
            $comment = Comment::findOrFail($id);
            $comment->delete();
            return back()->with('success', 'Comment deleted.');
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
        return redirect()->route('message-board.index')->with('success', 'Message updated.');
    }
}
