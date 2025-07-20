@extends('layout')

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

<head>
    {{-- Other CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<div class="container-fluid px-3 px-lg-4 py-4">
  {{-- Header Section --}}
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
        <div>
          <h2 class="text-primary fw-bold mb-1">Todo Management</h2>
          <p class="text-muted mb-0">Organize and track your tasks efficiently</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <div class="btn-group" role="group" aria-label="View options">
            <button id="tableViewBtn" class="btn btn-outline-primary">
              <i class="bi bi-table me-1"></i>
              <span class="d-none d-sm-inline">Table</span>
            </button>
            <button id="cardViewBtn" class="btn btn-primary">
              <i class="bi bi-grid-3x3-gap me-1"></i>
              <span class="d-none d-sm-inline">Cards</span>
            </button>
          </div>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createTodoModal">
            <i class="bi bi-plus-lg me-1"></i>
            <span class="d-none d-sm-inline">Add Todo</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  {{-- Stats Cards --}}
  <div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
      <div class="card bg-primary text-white h-100">
        <div class="card-body text-center">
          <i class="bi bi-list-task fs-1 mb-2"></i>
          <h4 class="card-title">{{ count($todos) }}</h4>
          <p class="card-text small">Total Tasks</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card bg-success text-white h-100">
        <div class="card-body text-center">
          <i class="bi bi-check-circle fs-1 mb-2"></i>
          <h4 class="card-title">{{ count($todos->where('priority', 'high')) }}</h4>
          <p class="card-text small">High Priority</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card bg-warning text-white h-100">
        <div class="card-body text-center">
          <i class="bi bi-clock fs-1 mb-2"></i>
          <h4 class="card-title">{{ count($todos->where('priority', 'medium')) }}</h4>
          <p class="card-text small">Medium Priority</p>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
      <div class="card bg-info text-white h-100">
        <div class="card-body text-center">
          <i class="bi bi-exclamation-circle fs-1 mb-2"></i>
          <h4 class="card-title">{{ count($todos->where('priority', 'low')) }}</h4>
          <p class="card-text small">Low Priority</p>
        </div>
      </div>
    </div>
  </div>

  {{-- Table View --}}
  <div id="tableView" class="d-none">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-light border-0">
        <h5 class="mb-0 text-primary">
          <i class="bi bi-table me-2"></i>Table View
        </h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
              <tr>
                <th class="fw-semibold">Title</th>
                <th class="fw-semibold text-center">Priority</th>
                <th class="fw-semibold d-none d-md-table-cell">Start Date</th>
                <th class="fw-semibold d-none d-md-table-cell">End Date</th>
                <th class="fw-semibold d-none d-lg-table-cell">Description</th>
                <th class="fw-semibold d-none d-xl-table-cell">Assigned</th>
                <th class="fw-semibold text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($todos as $todo)
                <tr>
                  <td>
                    <div class="fw-semibold text-primary">{{ $todo->title }}</div>
                    <div class="d-md-none small text-muted mt-1">
                      {{ $todo->start_date ?? 'No start date' }} - {{ $todo->end_date ?? 'No end date' }}
                    </div>
                  </td>
                  <td class="text-center">
                    @php
                      $priorityColor = match(strtolower($todo->priority)) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'secondary'
                      };
                    @endphp
                    <span class="badge bg-{{ $priorityColor }} px-3 py-2">
                      {{ ucfirst($todo->priority) }}
                    </span>
                  </td>
                  <td class="d-none d-md-table-cell">
                    <span class="text-muted">{{ $todo->start_date ?? '-' }}</span>
                  </td>
                  <td class="d-none d-md-table-cell">
                    <span class="text-muted">{{ $todo->end_date ?? '-' }}</span>
                  </td>
                  <td class="d-none d-lg-table-cell">
                    <div class="text-truncate" style="max-width: 200px;" title="{{ $todo->description }}">
                      {{ $todo->description ?: 'No description' }}
                    </div>
                  </td>
                  <td class="d-none d-xl-table-cell">
                    <div class="d-flex flex-wrap gap-1">
                      @forelse ($todo->user_id as $uid)
                        @if (isset($usersById[$uid]))
                          <span class="badge bg-info text-dark">{{ $usersById[$uid]->first_name }}</span>
                        @endif
                      @empty
                        <span class="text-muted small">Unassigned</span>
                      @endforelse
                    </div>
                  </td>
                 <td>
                      <div class="d-flex gap-1 justify-content-center">
                          <form action="{{ route('todos.markAsDone', $todo->id) }}" method="POST" class="d-inline">
                              @csrf
                              @method('PUT')
                              <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as Done" {{ $todo->status === 'done' ? 'disabled' : '' }}>
                                  <i class="bi bi-check-lg"></i>
                              </button>
                          </form>
                        @if(auth()->user()->hasRole('admin') && auth()->id() === $todo->creator_id)
                          <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editTodoModal_{{ $todo->id }}" title="Edit">
                              <i class="bi bi-pencil"></i>
                          </button>

                          <form action="{{ route('todos.destroy', $todo->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this todo?');">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                  <i class="bi bi-trash"></i>
                              </button>
                          </form>
                        @endif
                      </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center py-5">
                    <div class="text-muted">
                      <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                      <h5>No todos found</h5>
                      <p>Create your first todo to get started!</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- Card View --}}
  <div id="cardView">
    @if(count($todos) > 0)
      <div class="row g-4">
        @foreach($todos as $todo)
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
            <div class="card h-100 shadow-sm border-0 todo-card">
              <div class="card-header bg-transparent border-0 pb-0">
                <div class="d-flex justify-content-between align-items-start">
                  <h6 class="card-title text-primary fw-bold mb-0">{{ $todo->title }}</h6>
                  @php
                    $priorityColor = match(strtolower($todo->priority)) {
                      'high' => 'danger',
                      'medium' => 'warning',
                      'low' => 'success',
                      default => 'secondary'
                    };
                  @endphp
                  <span class="badge bg-{{ $priorityColor }}">{{ ucfirst($todo->priority) }}</span>
                </div>
              </div>
              
              <div class="card-body pt-2">
                @if($todo->description)
                  <p class="card-text text-muted small mb-3">{{ Str::limit($todo->description, 100) }}</p>
                @endif
                
                <div class="row g-2 mb-3">
                  <div class="col-6">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-calendar-event text-success me-2"></i>
                      <div>
                        <div class="small text-muted">Start</div>
                        <div class="fw-semibold small">{{ $todo->start_date ?? 'Not set' }}</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-calendar-check text-danger me-2"></i>
                      <div>
                        <div class="small text-muted">End</div>
                        <div class="fw-semibold small">{{ $todo->end_date ?? 'Not set' }}</div>
                      </div>
                    </div>
                  </div>
                </div>

                @if(!empty($todo->user_id))
                  <div class="mb-3">
                    <div class="small text-muted mb-1">
                      <i class="bi bi-people me-1"></i>Assigned to:
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                      @foreach ($todo->user_id as $uid)
                        @if (isset($usersById[$uid]))
                          <span class="badge bg-secondary small">{{ $usersById[$uid]->first_name }}</span>
                        @endif
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>
              
             <div class="card-footer bg-transparent border-0 pt-0">
                <div class="d-flex gap-2">
                        <form action="{{ route('todos.markAsDone', $todo->id) }}" method="POST" class="flex-fill">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-outline-success btn-sm w-100" title="Mark as Done" {{ $todo->status === 'done' ? 'disabled' : '' }}>
                                <i class="bi bi-check-lg me-1"></i>Done
                            </button>
                        </form>
                  @if(auth()->user()->hasRole('admin') && auth()->id() === $todo->creator_id)
                        <button class="btn btn-outline-warning btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#editTodoModal_{{ $todo->id }}">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>

                        <form action="{{ route('todos.destroy', $todo->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('Are you sure you want to delete this todo?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                <i class="bi bi-trash me-1"></i>Delete
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="text-center py-5">
        <div class="card border-0 shadow-sm">
          <div class="card-body py-5">
            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
            <h3 class="text-muted mt-3">No Todos Yet</h3>
            <p class="text-muted mb-4">Start organizing your tasks by creating your first todo!</p>
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#createTodoModal">
              <i class="bi bi-plus-lg me-2"></i>Create Your First Todo
            </button>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>

<!-- Create Todo Modal -->
<div class="modal fade" id="createTodoModal" tabindex="-1" aria-labelledby="createTodoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form action="{{ route('todos.store') }}" method="POST">
      @csrf
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white border-0">
          <h5 class="modal-title fw-bold" id="createTodoModalLabel">
            <i class="bi bi-plus-circle me-2"></i>Create New Todo
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <label for="title" class="form-label fw-semibold">
                Title <span class="text-danger">*</span>
              </label>
              <input type="text" name="title" class="form-control form-control-lg" placeholder="Enter todo title..." required>
            </div>

            <div class="col-md-6">
              <label for="priority" class="form-label fw-semibold">Priority Level</label>
              <select name="priority" class="form-select form-select-lg">
                <option value="low">🟢 Low Priority</option>
                <option value="medium" selected>🟡 Medium Priority</option>
                <option value="high">🔴 High Priority</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <div class="form-control form-control-lg d-flex align-items-center bg-light">
                <i class="bi bi-clock text-warning me-2"></i>
                <span>Pending</span>
              </div>
            </div>

            <div class="col-md-6">
              <label for="start_date" class="form-label fw-semibold">Start Date</label>
              <input type="date" name="start_date" class="form-control form-control-lg">
            </div>

            <div class="col-md-6">
              <label for="end_date" class="form-label fw-semibold">End Date</label>
              <input type="date" name="end_date" class="form-control form-control-lg">
            </div>

            <div class="col-12">
              <label for="description" class="form-label fw-semibold">Description</label>
              <textarea name="description" class="form-control" rows="4" placeholder="Describe your todo in detail..."></textarea>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Assign Team Members</label>
              <div class="border rounded p-3 bg-light">
                <div class="row g-2">
                  @foreach($users as $user)
                    <div class="col-md-6">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="user_id[]" value="{{ $user->id }}" id="member_{{ $user->id }}">
                        <label class="form-check-label d-flex align-items-center" for="member_{{ $user->id }}">
                          <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                            {{ strtoupper(substr($user->first_name, 0, 1)) }}
                          </div>
                          {{ $user->first_name }}
                        </label>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer border-0 p-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-success">
            <i class="bi bi-check-lg me-1"></i>Create Todo
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Todo Modals --}}
@foreach ($todos as $todo)
<div class="modal fade" id="editTodoModal_{{ $todo->id }}" tabindex="-1" aria-labelledby="editTodoModalLabel_{{ $todo->id }}" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form action="{{ route('todos.update') }}" method="POST">
      @csrf
      <input type="hidden" name="id" value="{{ $todo->id }}">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-warning text-dark border-0">
          <h5 class="modal-title fw-bold" id="editTodoModalLabel_{{ $todo->id }}">
            <i class="bi bi-pencil-square me-2"></i>Edit Todo
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
              <input type="text" name="title" value="{{ $todo->title }}" class="form-control form-control-lg" required>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Priority Level</label>
              <select name="priority" class="form-select form-select-lg">
                <option value="low" {{ strtolower($todo->priority) == 'low' ? 'selected' : '' }}>🟢 Low Priority</option>
                <option value="medium" {{ strtolower($todo->priority) == 'medium' ? 'selected' : '' }}>🟡 Medium Priority</option>
                <option value="high" {{ strtolower($todo->priority) == 'high' ? 'selected' : '' }}>🔴 High Priority</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Status</label>
              <div class="form-control form-control-lg d-flex align-items-center bg-light">
                <i class="bi bi-clock text-warning me-2"></i>
                <span>Pending</span>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Start Date</label>
              <input type="date" name="start_date" value="{{ $todo->start_date }}" class="form-control form-control-lg">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">End Date</label>
              <input type="date" name="end_date" value="{{ $todo->end_date }}" class="form-control form-control-lg">
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Description</label>
              <textarea name="description" class="form-control" rows="4">{{ $todo->description }}</textarea>
            </div>

            <div class="col-12">
              <label class="form-label fw-semibold">Assign Team Members</label>
              <div class="border rounded p-3 bg-light">
                <div class="row g-2">
                  @foreach($users as $user)
                    <div class="col-md-6">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="user_id[]" value="{{ $user->id }}"
                          id="edit_member_{{ $todo->id }}_{{ $user->id }}"
                          {{ in_array($user->id, $todo->user_id ?? []) ? 'checked' : '' }}>
                        <label class="form-check-label d-flex align-items-center" for="edit_member_{{ $todo->id }}_{{ $user->id }}">
                          <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px;">
                            {{ strtoupper(substr($user->first_name, 0, 1)) }}
                          </div>
                          {{ $user->first_name }}
                        </label>
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer border-0 p-4">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-lg me-1"></i>Cancel
          </button>
          <button type="submit" class="btn btn-warning">
            <i class="bi bi-check-lg me-1"></i>Update Todo
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endforeach

<script>
  document.addEventListener("DOMContentLoaded", function () {

    const toastElList = document.querySelectorAll('.toast');
        toastElList.forEach(toastEl => {
            new bootstrap.Toast(toastEl, { delay: 4000 }).show();
        });
      
    const tableView = document.getElementById('tableView');
    const cardView = document.getElementById('cardView');
    const tableBtn = document.getElementById('tableViewBtn');
    const cardBtn = document.getElementById('cardViewBtn');

    // Set initial active state
    cardBtn.classList.add('active');
    
    tableBtn.addEventListener('click', function () {
      tableView.classList.remove('d-none');
      cardView.classList.add('d-none');
      
      // Update button states
      tableBtn.classList.remove('btn-outline-primary');
      tableBtn.classList.add('btn-primary');
      cardBtn.classList.remove('btn-primary');
      cardBtn.classList.add('btn-outline-primary');
    });

    cardBtn.addEventListener('click', function () {
      cardView.classList.remove('d-none');
      tableView.classList.add('d-none');
      
      // Update button states
      cardBtn.classList.remove('btn-outline-primary');
      cardBtn.classList.add('btn-primary');
      tableBtn.classList.remove('btn-primary');
      tableBtn.classList.add('btn-outline-primary');
    });

    // Add hover effects for cards
    const todoCards = document.querySelectorAll('.todo-card');
    todoCards.forEach(card => {
      card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.transition = 'transform 0.3s ease';
      });
      
      card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
      });
    });

    // Auto-update end date when start date changes
    const startDateInputs = document.querySelectorAll('input[name="start_date"]');
    const endDateInputs = document.querySelectorAll('input[name="end_date"]');
    
    startDateInputs.forEach((input, index) => {
      input.addEventListener('change', function() {
        const endDateInput = endDateInputs[index];
        if (endDateInput && this.value && (!endDateInput.value || endDateInput.value < this.value)) {
          endDateInput.value = this.value;
        }
      });
    });
  });
</script>

@endsection