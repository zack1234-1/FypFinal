<?php
namespace App\Http\Controllers;

use App\Models\ChMessage;
use App\Models\ChatGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $toId = $request->to_id;
        $groupId = $request->group_id;
        $workspaceId = session()->get('workspace_id');

        $messages = collect();
        if ($toId && $groupId) {
            $messages = ChMessage::where('group_id', $groupId)
            ->whereNotNull('group_id')
            ->where('to_id', $groupId)
            ->orderBy('created_at')
            ->get();
        }
        else
        {

            $messages = ChMessage::where(function ($query) use ($userId, $toId) {
            $query->where('from_id', $userId)
                        ->where('to_id', $toId);
                })
                ->orWhere(function ($query) use ($userId, $toId) {
                    $query->where('from_id', $toId)
                        ->where('to_id', $userId);
                })
                ->orderBy('created_at')
                ->get();
        }

        $users = User::all();

        $groups = ChatGroup::where('workspace_id', $workspaceId)
            ->whereJsonContains('user_ids', (string) $userId)
            ->get();

        return view('chat.index', compact(
            'messages',
            'users',
            'toId',
            'groups',
            'groupId'
        ));
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'to_id' => 'required|integer',
            'message' => 'nullable|string',
            'attachment' => 'nullable|file|max:51200', 
        ]);

        $message = new ChMessage();
        $message->workspace_id = session()->get('workspace_id');
        $message->from_id = auth()->user()->id;
        $message->to_id = $validated['to_id'];
        $message->body = $validated['message'] ?? null;
        $message->seen = 0;

        if ($request->hasFile('attachment')) 
        {
            $file = $request->file('attachment');
            $message->attachment = file_get_contents($file->getRealPath());
            $originalName = $file->getClientOriginalName();
            $message->file_name = $originalName;
            $destinationPath = public_path('upload');
            $file->move($destinationPath, $originalName);
        }

        $message->save();

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'from_id' => $message->from_id,
                'to_id' => $message->to_id,
                'has_attachment' => $message->attachment !== null,
                'file_name' => $message->file_name,
                'created_at' => $message->created_at->toDateTimeString(),
            ]
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $message = ChMessage::findOrFail($id);

        if ($message->from_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->update([
            'body' => $request->message,
        ]);

        return response()->json(['message' => $message]);
    }

    public function delete($id)
    {
        $message = ChMessage::findOrFail($id);

        if ($message->from_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }

    public function downloadAttachment(ChMessage $message)
    {
        if (!in_array(auth()->id(), [$message->from_id, $message->to_id])) {
            abort(403);
        }

        if (!$message->attachment) {
            abort(404);
        }

        // Detect MIME type
        $mimeType = finfo_buffer(finfo_open(), $message->attachment, FILEINFO_MIME_TYPE);
        $extension = explode('/', $mimeType)[1] ?? 'bin';
        $filename = "attachment_{$message->id}." . $extension;

        // Auto: display if image/pdf, else download
        $disposition = str_starts_with($mimeType, 'image/') || $mimeType === 'application/pdf'
            ? 'inline'
            : 'attachment';

        return response($message->attachment)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', $disposition . '; filename="' . $filename . '"');
    }

    public function downloadReplyAttachment(ChMessage $message)
    {
        if (!in_array(auth()->id(), [$message->from_id, $message->to_id])) {
            abort(403);
        }

        if (!$message->reply_attachment) {
            abort(404);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($message->reply_attachment);

        $extension = match($mimeType) {
            'application/pdf' => 'pdf',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'text/plain' => 'txt',
            'application/zip' => 'zip',
            default => 'bin',
        };

        $filename = "reply_attachment_{$message->id}.{$extension}";

        return response()->streamDownload(
            function () use ($message) {
                // Clean output buffer if any
                if (ob_get_length()) ob_end_clean();
                echo $message->reply_attachment;
                flush(); // ensure output is sent
            },
            $filename,
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($message->reply_attachment),
            ]
        );
    }


    public function storeReply(Request $request, $messageId)
    {
        $validated = $request->validate([
            'to_id' => 'required|integer',
            'group_id' => 'nullable|integer|exists:chat_groups,id',
            'body' => 'nullable|string',
            'reply_message' => 'nullable|string',
            'attachment' => 'nullable|file|max:51200', 
            'reply_attachment' => 'nullable|file|max:51200',
            'file_name' => 'nullable|string|max:255',
        ]);

        $message = new ChMessage();
        $message->workspace_id = session('workspace_id');
        $message->from_id = auth()->id();
        $message->to_id = $validated['to_id'];
        $message->body = $validated['body'] ?? null;
        $message->reply_message = $validated['reply_message'] ?? null;
        $message->group_id = $request->group_id;
        $message->seen = 0;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $message->attachment = file_get_contents($file->getRealPath());
        }

        if ($request->hasFile('reply_attachment')) {
            $replyFile = $request->file('reply_attachment');
            $message->reply_attachment = file_get_contents($replyFile->getRealPath());
        }

        $message->file_name = $request->file_name ?? null;

        $message->save();

        return response()->json([
            'message' => [
                'id' => $message->id,
                'body' => $message->body,
                'reply_message' => $message->reply_message,
                'from_id' => $message->from_id,
                'to_id' => $message->to_id,
                'file_name' => $message->file_name,
                'created_at' => $message->created_at->toDateTimeString(),
            ]
        ]);
    }


    public function showReplyAttachment($id)
    {
        $message = ChMessage::findOrFail($id);

        if (!$message->reply_attachment) {
            abort(404);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($message->reply_attachment);

        return response($message->reply_attachment)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="reply_attachment_' . $id . '"');
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'users' => 'required|array',
            'users.*' => 'exists:users,id'
        ]);

        $group = ChatGroup::create([
            'name' => $request->name,
            'created_by' => auth()->id(),
            'user_ids' => $request->users,
        ]);

        return redirect()->route('chat.index')->with('success', 'Group created successfully.');
    }

    public function forward(Request $request)
    {
        $request->validate([
            'original_message_id' => 'required|exists:ch_messages,id',
            'body' => 'required|string',
            'to_id' => 'nullable|exists:users,id',
            'group_id' => 'nullable|exists:chat_groups,id',
        ]);

        if (!$request->to_id && !$request->group_id) {
            return back()->withErrors('Please select a user or group to forward the message to.');
        }

        $to_id = $request->to_id ?: $request->group_id;
        $message = new ChMessage();
        $message->workspace_id = session('workspace_id');
        $message->from_id = auth()->id();
        $message->to_id = $to_id;
        $message->group_id = $request->group_id;
        $message->body = $request->body;
        $message->forward = true;
        $message->seen = 0;
        $message->save();

       $params = [];

        if ($request->group_id) 
        {
            $params['to_id'] = $to_id; 
            $params['group_id'] = $request->group_id;
        } elseif ($request->to_id) {
            $params['to_id'] = $request->to_id;
        }

        // Redirect with both if group_id exists
        return redirect()->route('chat.index', $params);
    }

}
