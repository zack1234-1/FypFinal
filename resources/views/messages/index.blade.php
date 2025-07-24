@extends('layout')

@section('content')
<head>
<!-- Bootstrap CSS & JS (in layout.blade.php) -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" 
          integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        @if (session('success'))
            <div class="toast align-items-center text-white bg-success border-0 show"
                role="alert" aria-live="assertive" aria-atomic="true"
                style="min-width: 350px; font-size: 1.1rem; padding: 1rem 1.5rem;">
                <div class="d-flex">
                    <div class="toast-body fw-semibold">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="toast align-items-center text-white bg-danger border-0 show"
                role="alert" aria-live="assertive" aria-atomic="true"
                style="min-width: 350px; font-size: 1.1rem; padding: 1rem 1.5rem;">
                <div class="d-flex">
                    <div class="toast-body fw-semibold">
                        {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto"
                            data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
</div>

<div class="container mt-4">
    @if(auth()->user()->hasRole('admin'))
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-comments me-2"></i>Message Board</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postMessageModal">
            <i class="fas fa-plus-circle me-1"></i> Post New Message
        </button>
    </div>
    @endif

    @if($messages->count())
        @foreach($messages as $message)
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    {{-- Message Title + Edit/Delete --}}
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title mb-2">
                            <i class="fas fa-sticky-note me-2 text-primary"></i>{{ $message->title }}
                        </h5>
                        @if ($message->creator_id == auth()->id() && auth()->user()->hasRole('admin'))
                            <div class="d-flex gap-2">
                                <a href="{{ route('message-board.edit', $message) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('message-board.destroy', ['type' => 'message', 'id' => $message->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this message?')" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    {{-- Message Body --}}
                    <p class="card-text mt-2">{{ $message->content }}</p>
                    <small class="text-muted">Posted {{ $message->created_at->diffForHumans() }}</small>

                    {{-- Comments --}}
                    <hr>
                    <h6 class="text-muted"><i class="fas fa-reply-all me-2"></i>Comments</h6>

                    @if ($message->comments->count())
                        <ul class="list-group mb-3">
                            @foreach ($message->comments as $comment)
                                <li class="list-group-item">
                                    <div class="small text-muted mb-1">
                                        <i class="fas fa-user me-1"></i>{{ $comment->user->last_name ?? 'Unknown User' }}
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>{{ $comment->comment }}</span>
                                        @if ($comment->creator_id == auth()->id())
                                        <div class="d-flex gap-2">
                                            <!-- Edit Icon -->
                                            <button class="btn btn-sm btn-outline-warning"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editCommentModal-{{ $comment->id }}"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <!-- Delete -->
                                            <form action="{{ route('comments.destroy', $comment->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @endif
                                    </div>
                                </li>

                                <!-- Edit Comment Modal -->
                                <div class="modal fade" id="editCommentModal-{{ $comment->id }}" tabindex="-1" aria-labelledby="editCommentModalLabel-{{ $comment->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <form action="{{ route('comments.update', $comment->id) }}" method="POST" class="modal-content">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editCommentModalLabel-{{ $comment->id }}">
                                                    <i class="fas fa-edit me-2"></i>Edit Comment
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="text" name="comment" class="form-control" value="{{ $comment->comment }}" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save me-1"></i> Save Changes
                                                </button>
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No comments yet.</p>
                    @endif

                    {{-- Comment Form --}}
                    <form action="{{ route('comments.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="message_id" value="{{ $message->id }}">
                        <div class="input-group">
                            <input type="text" name="comment" class="form-control" placeholder="Write a comment..." required>
                            <button class="btn btn-outline-primary">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>No messages found.
        </div>
    @endif
</div>

<!-- Post Message Modal -->
<div class="modal fade" id="postMessageModal" tabindex="-1" aria-labelledby="postMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="{{ route('message-board.store') }}" method="POST" class="modal-content">
        @csrf
        <div class="modal-header">
            <h5 class="modal-title" id="postMessageModalLabel"><i class="fas fa-plus me-1"></i>New Message</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="messageTitle" class="form-label">Title</label>
                <input type="text" name="title" id="messageTitle" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="messageContent" class="form-label">Content</label>
                <textarea name="content" id="messageContent" class="form-control" rows="4" required></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-check-circle me-1"></i>Post
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
    </form>
  </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
       const toastElList = document.querySelectorAll('.toast');
        toastElList.forEach(toastEl => {
            new bootstrap.Toast(toastEl, { delay: 4000 }).show();
        });
    });

</script>
@endsection
