@extends('layout')

@section('content')
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@emoji-picker-element@^1.0.0/dist/emoji-picker/emoji-picker.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/@emoji-picker-element@^1.0.0/dist/emoji-picker/emoji-picker.js" type="module"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>

<style>
    body { height: 100vh; overflow: hidden; }
    .chat-container { display: flex; height: 100%; }
    .user-list { width: 20%; background: #f8f9fa; border-right: 1px solid #ddd; overflow-y: auto; padding: 1rem; }
    .chat-box { width: 60%; padding: 1rem; overflow-y: auto; display: flex; flex-direction: column; }
    .attachment-profile { width: 20%; background: #f8f9fa; border-left: 1px solid #ddd; overflow-y: auto; padding: 1rem; }
    .message-wrapper { width: 100%; }
    .message { word-wrap: break-word; max-width: 70%; }
    .message .actions { font-size: 0.8rem; cursor: pointer; color: lightgray; }
    .message .actions span { margin-left: 5px; }
    #messageList::-webkit-scrollbar { width: 8px; }
    #messageList::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    #messageList::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
    #messageList::-webkit-scrollbar-thumb:hover { background: #555; }
    .action-btn { cursor: pointer; opacity: 0.7; transition: opacity 0.2s; }
    .action-btn:hover { opacity: 1; }
    simple-emoji-picker {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
    max-width: 300px;
    max-height: 200px;
    overflow-y: auto;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.emoji-category {
    margin-bottom: 10px;
}

.emoji-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
    gap: 8px;
    max-height: 250px;
    overflow-y: auto;
}

.emoji-item {
    padding: 5px;
    text-align: center;
    cursor: pointer;
    border-radius: 4px;
    font-size: 20px;
    line-height: 1;
}

#emojiPickerContainer {
    position: absolute;
    bottom: 70px;
    left: 0;
    right: 0;
    width: 100%;
    display: none;
    z-index: 1000;
    padding: 10px;
    background-color: #fff;
}

.emoji-item:hover {
    background-color: #f0f0f0;
}

.reply-box input[type="text"] {
    font-size: 0.9rem;
}
.reply-box .input-group {
    gap: 8px;
}



</style>

<form id="chatForm" method="GET" action="{{ route('chat.index') }}">
    <input type="hidden" name="to_id" id="to_id" value="{{ $toId ?? '' }}">
</form>

<div class="container-fluid h-100">
        <div class="row h-100">
            <div class="col-md-4 col-lg-3 p-0 border-end">
        <div class="d-flex flex-column h-100">
            <div class="p-3 bg-light border-bottom">
                <button type="button" class="btn btn-success w-100 py-2" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                    <i class="fas fa-users me-2"></i> Create New Group
                </button>
            </div>

            <div class="flex-grow-1 d-flex flex-column overflow-hidden">
                <div class="card rounded-0 border-0 h-100">
                    <div class="card-header p-0 bg-white">
                        <ul class="nav nav-tabs nav-justified h-100" id="contactsTab" role="tablist">
                            <li class="nav-item h-100" role="presentation">
                                <button class="nav-link h-100 d-flex align-items-center justify-content-center 
                                    {{ !request()->has('group_id') ? 'active' : '' }}" 
                                    id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button">
                                    <i class="fas fa-user me-2"></i>Users
                                </button>
                            </li>
                            <li class="nav-item h-100" role="presentation">
                                <button class="nav-link h-100 d-flex align-items-center justify-content-center 
                                    {{ request()->has('group_id') ? 'active' : '' }}" 
                                    id="groups-tab" data-bs-toggle="tab" data-bs-target="#groups" type="button">
                                    <i class="fas fa-users me-2"></i>My Groups
                                </button>
                            </li>
                        </ul>
                    </div>


                    <div class="card-body p-0 h-100">
                        <div class="tab-content h-100" id="contactsTabContent">
                            <div class="tab-pane fade h-100 
                                {{ !request()->has('group_id') ? 'show active' : '' }}" 
                                id="users" role="tabpanel" style="overflow-y: auto;">
                                <div class="list-group list-group-flush">
                                    @foreach ($users as $user)
                                        <a href="{{ route('chat.index', ['to_id' => $user->id]) }}" 
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-3 {{ (string) $toId === (string) $user->id ? 'active bg-primary text-white' : '' }}">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-3 bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <span class="text-white">{{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $user->first_name }} {{ $user->last_name }}</h6>
                                                </div>
                                            </div>
                                            @if((string) $toId === (string) $user->id)
                                                <i class="fas fa-comment-dots"></i>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <div class="tab-pane fade h-100 
                                {{ request()->has('group_id') ? 'show active' : '' }}" 
                                id="groups" role="tabpanel" style="overflow-y: auto;">
                                <div class="list-group list-group-flush">
                                    @foreach ($groups as $group)
                                        <a href="{{ route('chat.index', ['to_id' => $group->id, 'group_id' => $group->id]) }}" 
                                        class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 px-3 {{ (string) $toId === (string) $group->id ? 'active bg-primary text-white' : '' }}">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-3 bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-users text-white"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $group->name }}</h6>
                                                    <small class="text-muted {{ (string) $toId === (string) $group->id ? 'text-white-50' : '' }}">
                                                        {{ $group->members_count }} members
                                                    </small>
                                                </div>
                                            </div>
                                            @if((string) $toId === (string) $group->id)
                                                <i class="fas fa-comments"></i>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>

        <div class="col-md-8 col-lg-9 p-0 d-flex flex-column">
            @if ($toId)
                <div class="p-3 border-bottom d-flex align-items-center bg-light">
                    @if(isset($groupId))
                        <div class="avatar me-3 bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-users text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $group->name }}</h5>
                            <small class="text-muted">{{ $group->members_count }} members</small>
                        </div>
                    @else
                        @php $currentUser = $users->firstWhere('id', $toId); @endphp
                        <div class="avatar me-3 bg-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <span class="text-white">{{ substr($currentUser->first_name, 0, 1) }}{{ substr($currentUser->last_name, 0, 1) }}</span>
                        </div>
                        <div>
                            <h5 class="mb-0">{{ $currentUser->first_name }} {{ $currentUser->last_name }}</h5>
                        </div>
                    @endif
                </div>

                <!-- Messages List -->
                <div class="flex-grow-1 overflow-auto p-3" id="messageList" style="height: calc(100vh - 200px); margin-bottom: 100px;">
                    @foreach ($messages as $msg)
                        <div class="message-wrapper d-flex mb-2 {{ $msg->from_id == auth()->id() ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="message border p-2 rounded {{ $msg->from_id == auth()->id() ? 'bg-primary text-white' : 'bg-light' }}" data-id="{{ $msg->id }}" style="max-width: 80%;">
                            {{-- Actions: Reply/Edit/Delete --}}
                              <div class="actions text-end mt-1" style="font-size: 0.85rem;">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-sm btn-link text-muted p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-ellipsis-h"></i> 
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @php
                                                $mimeType = finfo_buffer(finfo_open(), $msg->attachment, FILEINFO_MIME_TYPE) ?? '';
                                                $isImage = str_starts_with($mimeType, 'image/');
                                                $hasOnlyAttachment = !$msg->body && $msg->attachment;
                                            @endphp
                                           <li>
                                                <a class="dropdown-item"
                                                href=""
                                                data-bs-toggle="modal"
                                                data-bs-target="#replyModal-{{ $msg->id }}"
                                                data-image="{{ $isImage ? route('chat.attachment', $msg->id) : '' }}"
                                                data-file="{{ !$isImage ? route('chat.attachment', $msg->id) : '' }}">
                                                    <i class="fas fa-reply me-2"></i> Reply
                                                </a>
                                            </li>

                                            <li>
                                                <a class="dropdown-item" href="" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#forwardModal"
                                                data-message-id="{{ $msg->id }}" 
                                                data-message-body="{{ e($msg->body) }}">
                                                    <i class="fas fa-share me-2"></i> Forward
                                                </a>
                                            </li>
                                           @if ($isImage && $hasOnlyAttachment)
                                                <li>
                                                    <a class="dropdown-item" href="javascript:void(0);" onclick="downloadStoredFile('{{ $msg->file_name }}')">
                                                        <i class="fas fa-download me-2"></i> Download Image
                                                    </a>
                                                </li>
                                             @endif

                                            @if ($msg->from_id == auth()->id())
                                               @if($msg->body)
                                                        <li>
                                                            <a class="dropdown-item" href="" onclick="editMessage(this, '{{ $msg->id }}', event)">
                                                                <i class="fas fa-edit me-2"></i> Edit
                                                            </a>
                                                        </li>
                                                @endif
                                            <li>
                                                <a class="dropdown-item text-danger" href="" onclick="deleteMessage(this, '{{ $msg->id }}', event)">
                                                    <i class="fas fa-trash me-2"></i> Delete
                                                </a>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>
                               </div>
                                {{-- Sender Name --}}
                                
                                @if (!empty($msg->forward))
                                    <div class="text-muted small fst-italic mb-1">
                                        <i class="fas fa-share"></i> Forwarded
                                    </div>
                                @endif

                                @if ($msg->from_id == auth()->id())
                                    <strong>You:</strong>
                                @else
                                    <strong></strong>
                                @endif

                               @php
                                    $isImageReply = false;
                                    $isFileReply = false;
                                    $mimeType = null;

                                    if ($msg->reply_attachment) {
                                        $finfo = new \finfo(FILEINFO_MIME_TYPE);
                                        $mimeType = $finfo->buffer($msg->reply_attachment);
                                        $isImageReply = str_starts_with($mimeType, 'image/');
                                        $isFileReply = !$isImageReply;
                                    }
                                @endphp

                                @if (!$msg->reply_attachment && $msg->reply_message)
                                    <div class="border-start border-3 ps-2 mb-2 small" style="background-color: #f0f0f0;">
                                        <strong class="text-muted">Replied to:</strong><br>
                                        <span class="text-muted fst-italic">{{ Str::limit($msg->reply_message, 100) }}</span>
                                    </div>
                                @endif

                                @if ($isImageReply)
                                    <div class="mb-2">
                                        <div class="small text-muted mb-1">Replied to the image:</div>
                                        <img src="{{ route('chat.reply.attachment', $msg->id) }}"
                                            alt="reply_image_{{ $msg->id }}"
                                            class="img-thumbnail"
                                            style="max-height: 150px;">
                                    </div>
                                @endif

                                @if ($isFileReply)
                                    <div class="mb-2">
                                        <div class="small text-muted mb-1">Replied to the attachment:</div>
                                        <button onclick="downloadStoredFile('{{ $msg->file_name }}')"
                                                class="btn btn-secondary btn-sm">
                                            <i class="fas fa-file-download me-1"></i> Download Attachment
                                        </button>
                                    </div>
                                @endif


                                {{-- Message Body --}}
                                <span class="msg-body">
                                    {{ $msg->body }}
                                </span>

                                 @if($msg->attachment)
                                    @php
                                        $mimeType = finfo_buffer(finfo_open(), $msg->attachment, FILEINFO_MIME_TYPE) ?? '';
                                        $isImage = str_starts_with($mimeType, 'image/');
                                    @endphp
                                    <div class="mb-3">
                                        @if($isImage)
                                           <a href="{{ route('chat.attachment', $msg->id) }}" target="_blank">
                                                <img src="{{ route('chat.attachment', $msg->id) }}"
                                                    alt="attachment_{{ $msg->id }}"
                                                    class="img-thumbnail"
                                                    style="max-height: 200px; object-fit: contain;">
                                            </a>
                                        @else
                                       <button
                                            onclick="downloadStoredFile('{{ $msg->file_name }}')"
                                            class="btn btn-secondary btn-sm"
                                            style="display: inline-block;">
                                            <i class="fas fa-file-download me-1"></i> Download Attachment
                                        </button>
                                        @endif
                                    </div>
                                @endif
                                {{-- Timestamp --}}
                                <div class="text-end small text-muted">
                                    {{ $msg->created_at->format('h:i A') }}
                                </div>
                            </div>
                        </div>

                        {{-- Reply Box --}}
                             <div class="modal fade" id="replyModal-{{ $msg->id }}" tabindex="-1" aria-labelledby="replyModalLabel-{{ $msg->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg"> 
                                    <div class="modal-content shadow-sm rounded-4">
                                        <div class="modal-header bg-light py-2 px-3">
                                            <h5 class="modal-title fw-semibold text-primary" id="replyModalLabel-{{ $msg->id }}">
                                                <i class="fas fa-reply me-2 text-success"></i>Reply to Message
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body p-3">
                                            <div id="reply-box-{{ $msg->id }}" class="reply-box">
                                                <form onsubmit="submitReply(event, '{{ $msg->id }}')" enctype="multipart/form-data">
                                                    <input type="hidden" name="to_id" value="{{ $toId ?? '' }}">
                                                    <input type="hidden" name="group_id" value="{{ $groupId ?? '' }}">
                                                    <input type="hidden" name="reply_to" value="{{ $msg->body ?: 'Attachment' }}">
                                                    <input type="hidden" name="reply_attachment" value="{{ $msg->attachment ? route('chat.attachment', $msg->id) : '' }}">
                                                    <input type="hidden" name="file_name" value="{{ $msg->file_name ?: 'Unknown' }}">
                                                    @if($msg->body)
                                                    <div class="mb-2 small text-muted">
                                                        Replying to: <span class="fst-italic text-dark">"{{ Str::limit($msg->body, 60) }}"</span>
                                                    </div>
                                                    @endif
                                                    @if($msg->attachment)
                                                    <div class="mb-3 text-center" id="reply-preview-{{ $msg->id }}">
                                                        <img src="" class="img-fluid rounded reply-image d-none" alt="reply-preview" style="max-height: 200px; object-fit: contain;">
                                                        <a href="" target="_blank" class="btn btn-outline-primary btn-sm d-none reply-file">
                                                            <i class="fas fa-file-download me-1"></i> View Attachment
                                                        </a>
                                                    </div>
                                                    @endif
                                                    <div class="row g-2 align-items-center">
                                                        <div class="col-12 col-md-8">
                                                            <input type="text" name="reply_message" class="form-control" placeholder="Type your reply..." required>
                                                        </div>

                                                        <div class="col-12 col-md-4">
                                                            <input type="file" name="attachment" class="form-control" accept="*/*">
                                                        </div>
                                                    </div>

                                                    <div class="mt-3 text-end">
                                                        <button type="submit" class="btn btn-success px-4">
                                                            <i class="fas fa-paper-plane me-1"></i>Send Reply
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    @endforeach
                </div>

                <!-- Message Input -->
                <div class="position-fixed bottom-0 " style="left: 620px; width: 54%; z-index: 100;">
                    <form id="sendForm" onsubmit="sendMessage(event)" enctype="multipart/form-data">
                        <div class="input-group mb-2">
                            <button class="btn btn-outline-secondary" type="button" id="emojiToggle">
                                <i class="far fa-smile"></i>
                            </button>
                            <label for="fileInput" class="btn btn-outline-secondary mb-0">
                                <i class="fas fa-paperclip"></i> {{-- File attachment icon --}}
                            </label>
                            <input type="file" id="fileInput" name="attachment" class="d-none" accept="*/*">
                            <input type="text" id="messageInput" name="message" class="form-control" placeholder="Type a message...">
                            <button class="btn btn-primary" type="submit">Send</button>
                        </div>
                        <input type="hidden" id="to_id" name="to_id" value="{{ $toId ?? ($users->first()->id ?? '') }}">
                        <input type="hidden" id="group_id" name="group_id" value="{{ $groupId }}">
                    </form>
                 <!-- Attachment Preview Modal -->
                    <div id="emojiPickerContainer" class="position-absolute" style="bottom: 70px; left: 40px; display: none; z-index: 1000;">
                        <div class="simple-emoji-picker">
                            <div class="emoji-category">
                                <div class="emoji-grid">
                                    <span class="emoji-item" data-emoji="😀">😀</span>
                                    <span class="emoji-item" data-emoji="😃">😃</span>
                                    <span class="emoji-item" data-emoji="😄">😄</span>
                                    <span class="emoji-item" data-emoji="😁">😁</span>
                                    <span class="emoji-item" data-emoji="😆">😆</span>
                                    <span class="emoji-item" data-emoji="😅">😅</span>
                                    <span class="emoji-item" data-emoji="😂">😂</span>
                                    <span class="emoji-item" data-emoji="🤣">🤣</span>
                                    <span class="emoji-item" data-emoji="😊">😊</span>
                                    <span class="emoji-item" data-emoji="😇">😇</span>
                                    <span class="emoji-item" data-emoji="🙂">🙂</span>
                                    <span class="emoji-item" data-emoji="🙃">🙃</span>
                                    <span class="emoji-item" data-emoji="😉">😉</span>
                                    <span class="emoji-item" data-emoji="😌">😌</span>
                                    <span class="emoji-item" data-emoji="😍">😍</span>
                                    <span class="emoji-item" data-emoji="🥰">🥰</span>
                                    <span class="emoji-item" data-emoji="😘">😘</span>
                                    <span class="emoji-item" data-emoji="😗">😗</span>
                                    <span class="emoji-item" data-emoji="😙">😙</span>
                                    <span class="emoji-item" data-emoji="😚">😚</span>
                                    <span class="emoji-item" data-emoji="😋">😋</span>
                                    <span class="emoji-item" data-emoji="😛">😛</span>
                                    <span class="emoji-item" data-emoji="😝">😝</span>
                                    <span class="emoji-item" data-emoji="😜">😜</span>
                                    <span class="emoji-item" data-emoji="🤪">🤪</span>
                                    <span class="emoji-item" data-emoji="🤨">🤨</span>
                                    <span class="emoji-item" data-emoji="🧐">🧐</span>
                                    <span class="emoji-item" data-emoji="🤓">🤓</span>
                                    <span class="emoji-item" data-emoji="😎">😎</span>
                                    <span class="emoji-item" data-emoji="🤩">🤩</span>
                                    <span class="emoji-item" data-emoji="🥳">🥳</span>
                                    <span class="emoji-item" data-emoji="😏">😏</span>
                                    <span class="emoji-item" data-emoji="😒">😒</span>
                                    <span class="emoji-item" data-emoji="😞">😞</span>
                                    <span class="emoji-item" data-emoji="😔">😔</span>
                                    <span class="emoji-item" data-emoji="😟">😟</span>
                                    <span class="emoji-item" data-emoji="😕">😕</span>
                                    <span class="emoji-item" data-emoji="🙁">🙁</span>
                                    <span class="emoji-item" data-emoji="☹️">☹️</span>
                                    <span class="emoji-item" data-emoji="😣">😣</span>
                                    <span class="emoji-item" data-emoji="😖">😖</span>
                                    <span class="emoji-item" data-emoji="😫">😫</span>
                                    <span class="emoji-item" data-emoji="😩">😩</span>
                                    <span class="emoji-item" data-emoji="🥺">🥺</span>
                                    <span class="emoji-item" data-emoji="😢">😢</span>
                                    <span class="emoji-item" data-emoji="😭">😭</span>
                                    <span class="emoji-item" data-emoji="😤">😤</span>
                                    <span class="emoji-item" data-emoji="😠">😠</span>
                                    <span class="emoji-item" data-emoji="😡">😡</span>
                                    <span class="emoji-item" data-emoji="🤬">🤬</span>
                                    <span class="emoji-item" data-emoji="🤯">🤯</span>
                                    <span class="emoji-item" data-emoji="😳">😳</span>
                                    <span class="emoji-item" data-emoji="🥵">🥵</span>
                                    <span class="emoji-item" data-emoji="🥶">🥶</span>
                                    <span class="emoji-item" data-emoji="😱">😱</span>
                                    <span class="emoji-item" data-emoji="😨">😨</span>
                                    <span class="emoji-item" data-emoji="😰">😰</span>
                                    <span class="emoji-item" data-emoji="😥">😥</span>
                                    <span class="emoji-item" data-emoji="😓">😓</span>
                                    <span class="emoji-item" data-emoji="🤗">🤗</span>
                                    <span class="emoji-item" data-emoji="🤔">🤔</span>
                                    <span class="emoji-item" data-emoji="🤭">🤭</span>
                                    <span class="emoji-item" data-emoji="🤫">🤫</span>
                                    <span class="emoji-item" data-emoji="🤥">🤥</span>
                                    <span class="emoji-item" data-emoji="😶">😶</span>
                                    <span class="emoji-item" data-emoji="😐">😐</span>
                                    <span class="emoji-item" data-emoji="😑">😑</span>
                                    <span class="emoji-item" data-emoji="😬">😬</span>
                                    <span class="emoji-item" data-emoji="🙄">🙄</span>
                                    <span class="emoji-item" data-emoji="😯">😯</span>
                                    <span class="emoji-item" data-emoji="😦">😦</span>
                                    <span class="emoji-item" data-emoji="😧">😧</span>
                                    <span class="emoji-item" data-emoji="😮">😮</span>
                                    <span class="emoji-item" data-emoji="😲">😲</span>
                                    <span class="emoji-item" data-emoji="🥱">🥱</span>
                                    <span class="emoji-item" data-emoji="😴">😴</span>
                                    <span class="emoji-item" data-emoji="🤤">🤤</span>
                                    <span class="emoji-item" data-emoji="😪">😪</span>
                                    <span class="emoji-item" data-emoji="😵">😵</span>
                                    <span class="emoji-item" data-emoji="🤐">🤐</span>
                                    <span class="emoji-item" data-emoji="🥴">🥴</span>
                                    <span class="emoji-item" data-emoji="🤢">🤢</span>
                                    <span class="emoji-item" data-emoji="🤮">🤮</span>
                                    <span class="emoji-item" data-emoji="🤧">🤧</span>
                                    <span class="emoji-item" data-emoji="😷">😷</span>
                                    <span class="emoji-item" data-emoji="🤒">🤒</span>
                                    <span class="emoji-item" data-emoji="🤕">🤕</span>
                                    <span class="emoji-item" data-emoji="🤑">🤑</span>
                                    <span class="emoji-item" data-emoji="🤠">🤠</span>
                                    <span class="emoji-item" data-emoji="👍">👍</span>
                                    <span class="emoji-item" data-emoji="👎">👎</span>
                                    <span class="emoji-item" data-emoji="👌">👌</span>
                                    <span class="emoji-item" data-emoji="✌️">✌️</span>
                                    <span class="emoji-item" data-emoji="🤞">🤞</span>
                                    <span class="emoji-item" data-emoji="🤟">🤟</span>
                                    <span class="emoji-item" data-emoji="🤘">🤘</span>
                                    <span class="emoji-item" data-emoji="🤙">🤙</span>
                                    <span class="emoji-item" data-emoji="👈">👈</span>
                                    <span class="emoji-item" data-emoji="👉">👉</span>
                                    <span class="emoji-item" data-emoji="👆">👆</span>
                                    <span class="emoji-item" data-emoji="🖕">🖕</span>
                                    <span class="emoji-item" data-emoji="👇">👇</span>
                                    <span class="emoji-item" data-emoji="☝️">☝️</span>
                                    <span class="emoji-item" data-emoji="👏">👏</span>
                                    <span class="emoji-item" data-emoji="🙌">🙌</span>
                                    <span class="emoji-item" data-emoji="👐">👐</span>
                                    <span class="emoji-item" data-emoji="🤲">🤲</span>
                                    <span class="emoji-item" data-emoji="🤝">🤝</span>
                                    <span class="emoji-item" data-emoji="🙏">🙏</span>
                                    <span class="emoji-item" data-emoji="❤️">❤️</span>
                                    <span class="emoji-item" data-emoji="🧡">🧡</span>
                                    <span class="emoji-item" data-emoji="💛">💛</span>
                                    <span class="emoji-item" data-emoji="💚">💚</span>
                                    <span class="emoji-item" data-emoji="💙">💙</span>
                                    <span class="emoji-item" data-emoji="💜">💜</span>
                                    <span class="emoji-item" data-emoji="🖤">🖤</span>
                                    <span class="emoji-item" data-emoji="🤍">🤍</span>
                                    <span class="emoji-item" data-emoji="🤎">🤎</span>
                                    <span class="emoji-item" data-emoji="💔">💔</span>
                                    <span class="emoji-item" data-emoji="❣️">❣️</span>
                                    <span class="emoji-item" data-emoji="💕">💕</span>
                                    <span class="emoji-item" data-emoji="💞">💞</span>
                                    <span class="emoji-item" data-emoji="💓">💓</span>
                                    <span class="emoji-item" data-emoji="💗">💗</span>
                                    <span class="emoji-item" data-emoji="💖">💖</span>
                                    <span class="emoji-item" data-emoji="💘">💘</span>
                                    <span class="emoji-item" data-emoji="💝">💝</span>
                                    <span class="emoji-item" data-emoji="💟">💟</span>
                                    <span class="emoji-item" data-emoji="🔥">🔥</span>
                                    <span class="emoji-item" data-emoji="💯">💯</span>
                                    <span class="emoji-item" data-emoji="⭐">⭐</span>
                                    <span class="emoji-item" data-emoji="🌟">🌟</span>
                                    <span class="emoji-item" data-emoji="✨">✨</span>
                                    <span class="emoji-item" data-emoji="🎉">🎉</span>
                                    <span class="emoji-item" data-emoji="🎊">🎊</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                    <div class="text-center p-4">
                        <i class="fas fa-comments fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Select a chat to start messaging</h4>
                        <p class="text-muted">Choose from your contacts or groups to begin the conversation</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="createGroupModal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form method="POST" action="{{ route('chat.group.store') }}">
        @csrf
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createGroupModalLabel">Create New Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="group_name" class="form-label">Group Name</label>
                    <input type="text" class="form-control" name="name" id="group_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Select Users</label>
                    <div class="form-check" style="max-height: 200px; overflow-y: auto;">
                        @foreach($users as $user)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="users[]" value="{{ $user->id }}" id="user{{ $user->id }}">
                                <label class="form-check-label" for="user{{ $user->id }}">
                                    {{ $user->first_name }} ({{ $user->email }})
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Group</button>
            </div>
        </div>
    </form>
  </div>
</div>

<div class="modal fade" id="forwardModal" tabindex="-1" aria-labelledby="forwardModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="forwardForm" method="POST" action="{{ route('chat.forward') }}">
      @csrf
      <input type="hidden" name="original_message_id" id="forwardMessageId">
      <input type="hidden" name="body" id="forwardMessageBody">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="forwardModalLabel">Forward Message</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="forward_to_id" class="form-label">Select User</label>
            <select class="form-select" name="to_id" id="forward_to_id">
            <option value="">-- Select User --</option>
              @foreach ($users as $user)
                @if($user->id != auth()->id())
                  <option value="{{ $user->id }}">{{ $user->first_name }}</option>
                @endif
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="forward_group_id" class="form-label">Or Select Group</label>
            <select class="form-select" name="group_id" id="forward_group_id">
              <option value="">-- Select Group --</option>
              @foreach ($groups as $group)
                <option value="{{ $group->id }}">{{ $group->name }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Forward</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="attachmentPreviewModal" tabindex="-1" aria-labelledby="attachmentPreviewLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="max-height: 90vh;">
      <div class="modal-header">
        <h5 class="modal-title" id="attachmentPreviewLabel">Attachment Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body overflow-auto" id="filePreviewContent" style="max-height: 70vh;">
        <!-- JavaScript will inject preview content here -->
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="sendForm" class="btn btn-primary">Send</button>
      </div>
    </div>
  </div>
</div>


<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    document.querySelectorAll('#userTab button[data-bs-toggle="tab"]').forEach(button => {
        button.addEventListener('click', function () {
            const selectedUserId = this.getAttribute('data-bs-target').replace('#user-', '');
            document.getElementById('to_id').value = selectedUserId;
            document.getElementById('chatForm').submit();
        });
    });

    //     document.querySelectorAll('.forward-icon').forEach(icon => {
    //     icon.addEventListener('click', function () {
    //         document.getElementById('forwardMessageId').value = this.dataset.messageId;
    //         document.getElementById('forwardMessageBody').value = this.dataset.messageBody;
            
    //         document.getElementById('forwardMessageId').value = messageId;
    //         document.getElementById('forwardMessageBody').value = messageBody;
    //     });
    // });

    document.addEventListener('DOMContentLoaded', function() {
        const messageList = document.getElementById('messageList');
        messageList.scrollTop = messageList.scrollHeight;

        const observer = new MutationObserver(() => {
            messageList.scrollTop = messageList.scrollHeight;
        });

        observer.observe(messageList, { childList: true });

        const emojiToggle = document.getElementById('emojiToggle');
        const emojiPickerContainer = document.getElementById('emojiPickerContainer');
        const messageInput = document.getElementById('messageInput');

        document.querySelector('label[for="fileInput"]').addEventListener('click', function () {
            const input = document.getElementById('fileInput');
            input.value = ''; 
        });

        const previewModal = new bootstrap.Modal(document.getElementById('attachmentPreviewModal'));

        document.getElementById('fileInput').addEventListener('change', function (event) {
            const file = event.target.files[0];
            const previewContent = document.getElementById('filePreviewContent');
            previewContent.innerHTML = '';

            if (file) {
                const fileName = document.createElement('p');
                fileName.textContent = `File: ${file.name}`;
                fileName.style.textAlign = 'center';
                fileName.style.fontWeight = 'bold';
                previewContent.appendChild(fileName);

                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    img.style.maxHeight = '500px';
                    img.style.maxWidth = '100%';
                    img.style.display = 'block';
                    img.style.margin = '10px auto';
                    img.onload = () => URL.revokeObjectURL(img.src);
                    previewContent.appendChild(img);
                } else {
                    const icon = document.createElement('i');
                    icon.className = 'fas fa-file-alt fa-3x d-block text-center mt-4';
                    previewContent.appendChild(icon);
                }

                previewModal.show();
            }
});

        emojiToggle.addEventListener('click', function(e) {
            e.preventDefault();
            const isVisible = emojiPickerContainer.style.display !== 'none';
            emojiPickerContainer.style.display = isVisible ? 'none' : 'block';
        });

        document.addEventListener('click', function(e) {
            if (!emojiToggle.contains(e.target) && !emojiPickerContainer.contains(e.target)) {
                emojiPickerContainer.style.display = 'none';
            }
        });

        document.querySelectorAll('.emoji-item').forEach(item => {
            item.addEventListener('click', function () {
                const emoji = this.getAttribute('data-emoji');
                const messageInput = document.getElementById('messageInput');
                const cursorPosition = messageInput.selectionStart;

                const currentValue = messageInput.value;
                const newValue = currentValue.slice(0, cursorPosition) + emoji + currentValue.slice(cursorPosition);
                messageInput.value = newValue;

                messageInput.focus();
                messageInput.setSelectionRange(cursorPosition + emoji.length, cursorPosition + emoji.length);

                document.getElementById('emojiPickerContainer').style.display = 'none';
            });
        });

    });

    async function sendMessage(e) 
    {
        e.preventDefault();

        const messageInput = document.getElementById('messageInput');
        const toId = document.getElementById('to_id').value;
        const fileInput = document.getElementById('fileInput');

        const body = messageInput.value.trim();
        const file = fileInput.files[0];

        if (!body && !file) return;

        const formData = new FormData();
        formData.append('to_id', toId);
        if (body) formData.append('message', body);
        if (file) formData.append('attachment', file);

        const res = await fetch("{{ route('chat.send') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        if (res.ok) 
        {
            location.reload();
        } else {
            alert('Failed to send message.');
        }
    }


     async function deleteMessage(el, id) 
     {
        if (!confirm('Delete this message?')) return;
        const url = "{{ route('chat.delete', ':id') }}".replace(':id', id);

        try {
            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (res.ok) {
                el.closest('.message-wrapper').remove();
            } else {
                const data = await res.json();
                alert(data.error || 'Failed to delete the message.');
            }
        } catch (err) {
            console.error('Delete failed:', err);
            alert('An error occurred.');
        }
    }
    
    async function editMessage(el, id) 
    {
        const messageEl = el.closest('.message');
        const currentText = messageEl.querySelector('.msg-body').innerText;
        const newText = prompt('Edit your message:', currentText);
        if (newText === null) return;

        const res = await fetch("{{ route('chat.update', ':id') }}".replace(':id', id), {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message: newText })
        });

        const updated = await res.json();
        messageEl.querySelector('.msg-body').innerText = updated.message.body;
    }

        function downloadStoredFile(filename) 
        {
            const fileUrl = `/upload/${filename}`;

            console.log(fileUrl);
            
            const link = document.createElement('a');
            link.href = fileUrl;
            link.download = filename;
            link.style.display = 'none';

            document.body.appendChild(link);
            link.click();
            
            // Clean up
            setTimeout(() => {
                document.body.removeChild(link);
            }, 100);
        }

        function showReplyInput(messageId, originalMsg) 
        {
            document.querySelectorAll('.reply-box').forEach(box => box.style.display = 'none');
            const replyBox = document.getElementById('reply-box-' + messageId);
            if (replyBox) {
                replyBox.style.display = 'block';
                replyBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        async function submitReply(event, messageId) 
        {
            event.preventDefault();

            const replyBox = document.querySelector(`#reply-box-${messageId}`);
            const input = replyBox.querySelector(`input[name="reply_message"]`);
            const fileInput = replyBox.querySelector(`input[name="attachment"]`);
            const groupIdInput = replyBox.querySelector(`input[name="group_id"]`);
            const replyAttachmentInput = replyBox.querySelector(`input[name="reply_attachment"]`);
            const fileNameInput = replyBox.querySelector(`input[name="file_name"]`);

            const replyText = input.value.trim();
            if (!replyText && !fileInput.files.length && !replyAttachmentInput?.value) return;

            const originalMessage = document.querySelector(`.message[data-id="${messageId}"] .msg-body`)?.innerText || '';

            const formData = new FormData();
            formData.append('body', replyText); 
            formData.append('reply_message', originalMessage); 
            formData.append('to_id', document.getElementById('to_id').value);

            if (groupIdInput) {
                formData.append('group_id', groupIdInput.value);
            }

            if (fileInput.files.length > 0) {
                formData.append('attachment', fileInput.files[0]);
            }

            if (replyAttachmentInput?.value) {
                try {
                    const response = await fetch(replyAttachmentInput.value);
                    const blob = await response.blob();
                    formData.append('reply_attachment', blob, 'reply_attachment');
                } catch (err) {
                    console.warn('Failed to load reply_attachment:', err);
                }
            }

            if (fileNameInput) {
                formData.append('file_name', fileNameInput.value);
            }

            const url = "{{ route('chat.reply', ':message') }}".replace(':message', messageId);

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await res.json();

                if (res.ok) {
                    input.value = '';
                    fileInput.value = '';
                    replyBox.style.display = 'none';
                    location.reload();
                } else {
                    alert(data.message || 'Failed to send reply.');
                }
            } catch (err) {
                console.error('Reply failed:', err);
                alert('An error occurred.');
            }
        }
        
        const forwardModal = document.getElementById('forwardModal');

        forwardModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const messageId = button.getAttribute('data-message-id');
            const messageBody = button.getAttribute('data-message-body');

            document.getElementById('forwardMessageId').value = messageId;
            document.getElementById('forwardMessageBody').value = messageBody;
        });

      document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-bs-target^="#replyModal-"]').forEach(el => {
            el.addEventListener('click', function () {
                const imageUrl = this.getAttribute('data-image');
                const fileUrl = this.getAttribute('data-file');
                const targetModalId = this.getAttribute('data-bs-target').replace('#', '');
                const modal = document.getElementById(targetModalId);

                const imgPreview = modal.querySelector('.reply-image');
                const fileLink = modal.querySelector('.reply-file');

                if (imageUrl) {
                    imgPreview.src = imageUrl;
                    imgPreview.classList.remove('d-none');
                    fileLink.classList.add('d-none');
                    fileLink.href = '#';
                } else if (fileUrl) {
                    fileLink.href = fileUrl;
                    fileLink.classList.remove('d-none');
                    imgPreview.classList.add('d-none');
                    imgPreview.src = '';
                } else {
                    imgPreview.classList.add('d-none');
                    imgPreview.src = '';
                    fileLink.classList.add('d-none');
                    fileLink.href = '#';
                }
            });
        });

        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('hidden.bs.modal', () => {
                const img = modal.querySelector('.reply-image');
                const file = modal.querySelector('.reply-file');
                if (img) {
                    img.src = '';
                    img.classList.add('d-none');
                }
                if (file) {
                    file.href = '#';
                    file.classList.add('d-none');
                }
            });
        });
    });

</script>
@endsection
