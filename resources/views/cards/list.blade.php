@extends('layout')
@section('title')
    <?= get_label('kanban_view', 'Kanban View') ?>
@endsection
@php
    $user = getAuthenticatedUser();
@endphp
@section('content')
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

<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home.index') }}">{{ get_label('home', 'Home') }}</a>
                    </li>
                    <li class="breadcrumb-item active">{{ get_label('card_table', 'Card Table') }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_note_modal">
                <button type="button" class="btn btn-sm btn-primary">
                    <i class='bx bx-plus'></i>
                </button>
            </a>
        </div>
    </div>

    <div class="d-flex justify-content-center mb-4">
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <a class="nav-link active px-5 py-3 fs-5 fw-bold" 
                href="{{ route('status.index') }}" 
                role="tab">
                <i class="bx bx-list-check me-2 fs-4"></i>
                {{ get_label('status', 'Status Management') }}
            </a>
            <a class="nav-link px-5 py-3 fs-5 fw-bold" 
                href="{{ route('cards.index') }}" 
                role="tab">
                <i class="bx bx-card-text me-2 fs-4"></i>
                {{ get_label('cards', 'Kanban Board') }}
            </a>
        </div>
    </div>

    @if ($notes->count() > 0)
        <div class="container">
            <div class="d-flex flex-nowrap overflow-auto p-3 gap-3 min-vh-75 overflow-y-auto">
                @foreach ($statuses as $status)
                    <div class="card flex-shrink-0" style="width: 300px; height: 600px;" data-status-id="{{ $status->id }}">
                        <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
                            <strong>{{ $status->title }}</strong>
                            <span class="badge bg-primary column-count">
                                {{ $notes->where('status_id', $status->id)->count() }}
                            </span>
                        </div>
                        <div class="card-body kanban-column-body">
                            @foreach ($notes->where('status_id', $status->id) as $note)
                             <div class="card mb-3 mt-3 border kanban-card min-vh-55" data-card-id="{{ $note->id }}">
                                    <div class="card-body min-vh-25 py-4">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0 text-truncate" style="max-width: 200px;">
                                                {{ Str::limit($note->title, 25) }}
                                            </h6>
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#edit_note_modal_{{ $note->id }}">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <form action="{{ route('cards.destroy', $note->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </div>
                                        </div>
                                        @if ($note->description)
                                            <p class="mb-1">
                                                <span class="badge bg-secondary">
                                                    {{ Str::limit($note->description, 40) }}
                                                </span>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <div class="text-center my-5">
            <h5 class="text-muted">No cards found.</h5>
        </div>
    @endif

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="update-status-url" content="{{ route('cards.update-status', ['note' => '__NOTE_ID__']) }}">
</div>

<!-- Create Note Modal -->
<div class="modal fade" id="create_note_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content form-submit-event" action="{{ route('cards.store') }}" method="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">
                    <?= get_label('create_card', 'Create Card') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col mb-3">
                        <label for="nameBasic" class="form-label">
                            <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                        </label>
                        <input type="text" id="nameBasic" class="form-control" name="title"
                            placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <label for="nameBasic" class="form-label">
                            <?= get_label('description', 'Description') ?>
                        </label>
                        <textarea class="form-control description" name="description"
                            placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>"></textarea>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="status">
                        <?= get_label('status', 'Status') ?> <span class="asterisk text-danger">*</span>
                    </label>

                    <div class="d-flex align-items-center">
                        <select class="form-control statusDropdown me-2" name="status_id" id="status">
                            @foreach ($statuses as $status)
                                <option value="{{ $status->id }}" {{ old('status') == $status->id ? 'selected' : '' }}>
                                    {{ $status->title }}
                                </option>
                            @endforeach
                        </select>

                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#create_status_modal" data-bs-toggle="tooltip" data-bs-placement="right"
                            data-bs-original-title="<?= get_label('create_status', 'Create status') ?>">
                            <i class="bx bx-plus"></i>
                        </button>
                    </div>

                    @error('status_id')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="submit_btn">
                    <?= get_label('create', 'Create') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Create Status Modal -->
<div class="modal fade" id="create_status_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content form-submit-event" action="{{ route('status.store') }}" method="POST">
            <input type="hidden" name="dnr">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">
                    <?= get_label('create_status', 'Create status') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col mb-3">
                        <label for="nameBasic" class="form-label">
                            <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                        </label>
                        <input type="text" id="nameBasic" class="form-control" name="title"
                            placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="submit_btn">
                    <?= get_label('create', 'Create') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Note Modals -->
@foreach ($notes as $note)
<div class="modal fade" id="edit_note_modal_{{ $note->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content form-submit-event" 
              action="{{ route('cards.update', $note->id) }}" 
              method="POST">
            @csrf

            <input type="hidden" name="id" value="{{ $note->id }}">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">
                    {{ get_label('update_card', 'Update Card') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col mb-3">
                        <label class="form-label">
                            {{ get_label('title', 'Title') }} <span class="asterisk">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               name="title"
                               value="{{ $note->title }}" 
                               placeholder="{{ get_label('please_enter_title', 'Please enter title') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <label class="form-label">
                            {{ get_label('description', 'Description') }}
                        </label>
                        <textarea class="form-control description" 
                                  name="description"
                                  placeholder="{{ get_label('please_enter_description', 'Please enter description') }}">{{ $note->description }}</textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    {{ get_label('close', 'Close') }}
                </button>
                <button type="submit" class="btn btn-primary">
                    {{ get_label('update', 'Update') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endforeach

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<script>
document.addEventListener('DOMContentLoaded', () => {
    const toastElList = document.querySelectorAll('.toast');
        toastElList.forEach(toastEl => {
            new bootstrap.Toast(toastEl, { delay: 4000 }).show();
        });
    const allowDrop = ev => ev.preventDefault();

    const dragNote = ev => {
        ev.dataTransfer.setData('note_id', ev.target.closest('.kanban-card').dataset.cardId);
        ev.target.classList.add('dragging');
    };

    const dragEnd = ev => ev.target.classList.remove('dragging');

    const getDragAfterElement = (container, y) => {
        const elements = [...container.querySelectorAll('.kanban-card:not(.dragging)')];
        return elements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            return (offset < 0 && offset > closest.offset) ? { offset, element: child } : closest;
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    };

    const dropNote = ev => {
        ev.preventDefault();
        const noteId = ev.dataTransfer.getData('note_id');
        const draggedCard = document.querySelector(`[data-card-id="${noteId}"]`);
        const columnBody = ev.currentTarget;
        const column = columnBody.closest('[data-status-id]');
        const newStatusId = column.dataset.statusId;
        const afterElement = getDragAfterElement(columnBody, ev.clientY);
        afterElement ? columnBody.insertBefore(draggedCard, afterElement) : columnBody.appendChild(draggedCard);
        updateColumnCounters();

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const updateUrl = document.querySelector('meta[name="update-status-url"]').content.replace('__NOTE_ID__', noteId);

        fetch(updateUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ status_id: newStatusId })
        });
    };

    const updateColumnCounters = () => {
        document.querySelectorAll('[data-status-id]').forEach(col => {
            const count = col.querySelectorAll('.kanban-card').length;
            const badge = col.querySelector('.column-count');
            if (badge) badge.textContent = count;
        });
    };

    document.querySelectorAll('.kanban-column-body').forEach(body => {
        body.addEventListener('dragover', allowDrop);
        body.addEventListener('drop', dropNote);
    });

    document.querySelectorAll('.kanban-card').forEach(card => {
        card.setAttribute('draggable', 'true');
        card.addEventListener('dragstart', dragNote);
        card.addEventListener('dragend', dragEnd);
    });

    document.querySelectorAll('.delete-note').forEach(btn => 
    {
        btn.addEventListener('click', function () {
            if (!confirm('Are you sure you want to delete this card?')) return;
            const noteId = this.dataset.id;
            const url = this.dataset.url;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(url, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.querySelector(`[data-card-id="${noteId}"]`)?.remove();
                    updateColumnCounters();
                } else {
                    alert(data.message || 'Delete failed.');
                }
            })
            .catch(() => alert('Error deleting note.'));
        });
    });
});
</script>


@endsection