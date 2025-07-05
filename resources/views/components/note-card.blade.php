<!-- Kanban Board HTML -->
<div class="kanban-board d-flex bg-body gap-3 overflow-auto p-3">
    @foreach ($statuses as $status)
        <div class="kanban-column card" data-status-id="{{ $status->id }}">
            <div class="kanban-column-header card-header bg-label-{{ $status->color }} d-flex justify-content-between align-items-center p-3">
                <div class="fw-semibold">
                    {{ $status->title }}
                </div>
                <div class="column-count badge text-{{ $status->color }} bg-white">
                    {{ $notes->where('status_id', $status->id)->count() }}
                </div>
            </div>

            <div class="kanban-column-body card-body bg-body p-3"
                 ondrop="dropNote(event)"
                 ondragover="allowDrop(event)">
                @foreach ($notes->where('status_id', $status->id) as $note)
                    <div class="kanban-card card mb-3"
                         data-card-id="{{ $note->id }}"
                         draggable="true">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="card-title mb-0">
                                    {{ Str::limit($note->title, 25) }}
                                </h5>
                                <div class="note-actions">
                                   <a href="javascript:void(0);"
                                      class="edit-note"
                                      data-id="{{ $note->id }}"
                                      data-bs-toggle="modal"
                                      data-bs-target="#edit_note_modal_{{ $note->id }}">
                                        <i class='bx bx-edit text-primary'></i>
                                    </a>

                                    <a href="javascript:void(0);" class="delete-note" data-id="{{ $note->id }}" data-url="{{ route('notes.destroy', $note->id) }}">
                                        <i class="bx bx-trash-alt text-danger"></i>
                                    </a>
                                </div>
                            </div>
                            @if($note->description)
                                <div class="card-tags mb-2">
                                    <span class="badge bg-label-secondary">
                                        {{ Str::limit($note->description, 40) }}
                                    </span>
                                </div>
                            @endif
                            <small class="text-muted">
                                <i class='bx bx-time'></i>
                                {{ format_date($note->created_at, true) }}
                            </small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="update-status-url" content="{{ route('notes.update-status', ['note' => '__NOTE_ID__']) }}">

<style>
.kanban-column {
    min-width: 300px;
    max-width: 300px;
}
.kanban-card {
    cursor: move;
    transition: transform 0.2s;
    user-select: none;
}
.kanban-card.dragging {
    opacity: 0.5;
    transform: scale(0.98);
}
.kanban-column-body {
    min-height: 400px;
}
</style>

<script>
function allowDrop(ev) {
  ev.preventDefault();
  ev.dataTransfer.dropEffect = 'move';
}

function dragNote(ev) {
  const card = ev.target.closest('.kanban-card');
  ev.dataTransfer.setData('note_id', card.dataset.cardId);
  ev.dataTransfer.effectAllowed = 'move';
  card.classList.add('dragging');
}

function dragEnd(ev) {
  ev.target.classList.remove('dragging');
}

function getDragAfterElement(container, y) {
  const draggableElements = [...container.querySelectorAll('.kanban-card:not(.dragging)')];
  return draggableElements.reduce((closest, child) => {
    const box = child.getBoundingClientRect();
    const offset = y - box.top - box.height / 2;
    if (offset < 0 && offset > closest.offset) {
      return { offset: offset, element: child };
    } else {
      return closest;
    }
  }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function dropNote(ev) {
  ev.preventDefault();
  const noteId = ev.dataTransfer.getData('note_id');
  const draggedCard = document.querySelector(`[data-card-id="${noteId}"]`);
  if (!draggedCard) return;

  const columnBody = ev.currentTarget;
  const column = columnBody.closest('.kanban-column');
  const newStatusId = column.dataset.statusId;
  const afterElement = getDragAfterElement(columnBody, ev.clientY);

  if (afterElement == null) {
    columnBody.appendChild(draggedCard);
  } else {
    columnBody.insertBefore(draggedCard, afterElement);
  }

  updateColumnCounters();
  draggedCard.classList.remove('dragging');

  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  const updateUrlTemplate = document.querySelector('meta[name="update-status-url"]').content;
  const updateUrl = updateUrlTemplate.replace('__NOTE_ID__', noteId);

  fetch(updateUrl, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({ status_id: newStatusId })
  })
  .then(res => res.json())
  .then(data => {
    if (!data.success) {
    }
  })
  .catch(() => alert('Error updating status.'));
}

function updateColumnCounters() {
  document.querySelectorAll('.kanban-column').forEach(col => {
    const cnt = col.querySelectorAll('.kanban-card').length;
    col.querySelector('.column-count').textContent = cnt;
  });
}


document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.kanban-column-body').forEach(body => {
    body.addEventListener('dragover', allowDrop);
    body.addEventListener('drop', dropNote);
  });

  document.querySelectorAll('.kanban-card').forEach(card => {
    card.setAttribute('draggable', 'true');
    card.addEventListener('dragstart', dragNote);
    card.addEventListener('dragend', dragEnd);
  });

  document.querySelectorAll('.delete-note').forEach(btn => {
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
