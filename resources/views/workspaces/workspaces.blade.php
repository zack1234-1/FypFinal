@extends('layout')

@section('title')
    <?= get_label('projects', 'Projects') ?>
@endsection

@php
    $currentUserId = auth()->id();
@endphp
<head>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

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
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home.index') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <?= get_label('projects', 'Projects') ?>
                        </li>
                    </ol>
                </nav>
            </div>
            <div>
                <button 
                    type="button" 
                    class="btn btn-sm btn-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#createWorkspaceModal" 
                    data-bs-toggle="tooltip" 
                    data-bs-placement="left" 
                    title="{{ get_label('create_project', 'Create Project') }}">
                    <i class='bx bx-plus'></i> {{ get_label('create', 'Create') }}
                </button>
            </div>
        </div>
            @if ($workspaces->isNotEmpty())
             <div class="row">
                @foreach ($workspaces as $workspace)
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <h5 class="card-title mb-0">{{ $workspace->title }}</h5>
                                    <div class="d-flex gap-2">
                                        {{-- Switch Button (visible to all) --}}
                                        <a href="{{ route('workspaces.switch', ['id' => $workspace->id]) }}"
                                        class="btn d-flex align-items-center justify-content-center"
                                        style="background-color: #00C853; width: 40px; height: 40px; border-radius: 8px;"
                                        title="Switch to this project">
                                            <i class="bx bx-transfer text-white"></i>
                                        </a>

                                        {{-- Admin-only Buttons --}}
                                            {{-- Edit Button --}}
                                            <button 
                                                type="button"
                                                class="btn d-flex align-items-center justify-content-center"
                                                style="background-color: #FFC107; width: 40px; height: 40px; border-radius: 8px;"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editWorkspaceModal{{ $workspace->id }}"
                                                title="Edit project">
                                                <i class="fas fa-edit text-white"></i>
                                            </button>

                                            {{-- Delete Button --}}
                                            <form action="{{ route('workspaces.destroy', $workspace->id) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit"
                                                    class="btn d-flex align-items-center justify-content-center"
                                                    style="background-color: #FF3D00; width: 40px; height: 40px; border-radius: 8px;"
                                                    title="Delete project">
                                                    <i class="fas fa-trash text-white"></i>
                                                </button>
                                            </form>
                                    </div>
                                </div>

                                {{-- Users List --}}
                                <p class="card-text">
                                    <strong>{{ get_label('users', 'Users') }}:</strong><br>
                                    @foreach ($workspace->users ?? [] as $user)
                                        <span class="badge bg-primary mb-1">{{ $user->first_name }}</span>
                                    @endforeach
                                </p>

                                {{-- Created and Updated Info --}}
                                <p class="card-text">
                                    <small class="text-muted">{{ get_label('created_at', 'Created at') }}: {{ $workspace->created_at?->format('Y-m-d') }}</small><br>
                                    <small class="text-muted">{{ get_label('updated_at', 'Updated at') }}: {{ $workspace->updated_at?->format('Y-m-d') }}</small>
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        @else

        @endif
    </div>
@endsection


<!-- Create Workspace Modal -->
<div class="modal fade" id="createWorkspaceModal" tabindex="-1" aria-labelledby="createWorkspaceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('workspaces.store') }}" class="modal-content">
            @csrf

            {{-- Modal Header --}}
            <div class="modal-header">
                <h5 class="modal-title" id="createWorkspaceModalLabel">
                    <i class="fas fa-plus me-2"></i>{{ get_label('create_project', 'Create Project') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body">
                {{-- Title Input --}}
                <div class="mb-3">
                    <label class="form-label">{{ get_label('title', 'Title') }}</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                {{-- User Checkboxes --}}
                <div class="mb-3">
                    <label class="form-label">{{ get_label('assign_users', 'Assign Users') }}</label>
                    <div class="form-check-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; border-radius: 5px;">
                        @foreach ($users as $user)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="user_ids[]" id="userCheckbox{{ $user->id }}" value="{{ $user->id }}">
                                <label class="form-check-label" for="userCheckbox{{ $user->id }}">
                                    {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted">Tick to assign users.</small>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>{{ get_label('save', 'Save') }}
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ get_label('cancel', 'Cancel') }}
                </button>
            </div>
        </form>
    </div>
</div>

@foreach ($workspaces as $index => $workspace)
<div class="modal fade" id="editWorkspaceModal{{ $workspace->id }}" tabindex="-1" aria-labelledby="editWorkspaceModalLabel{{ $workspace->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('workspaces.update', $workspace->id) }}" class="modal-content">
            @csrf
            @method('PUT')

            {{-- Modal Header --}}
            <div class="modal-header">
                <h5 class="modal-title" id="editWorkspaceModalLabel{{ $workspace->id }}">
                    <i class="fas fa-edit me-2"></i>{{ get_label('edit_workspace', 'Edit Workspace') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body">
                {{-- Title Input --}}
                <div class="mb-3">
                    <label class="form-label">{{ get_label('title', 'Title') }}</label>
                    <input type="text" name="title" class="form-control" value="{{ $workspace->title }}" required>
                </div>

                {{-- User Checkboxes --}}
                <div class="mb-3">
                    <label class="form-label">{{ get_label('assign_users', 'Assign Users') }}</label>
                    <div class="form-check-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; border-radius: 5px;">
                        @foreach ($users as $user)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="user_ids[]"
                                    id="editUserCheckbox{{ $workspace->id }}_{{ $user->id }}"
                                    value="{{ $user->id }}"
                                    {{ $workspace->users->contains($user->id) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="editUserCheckbox{{ $workspace->id }}_{{ $user->id }}">
                                    {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                </label>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted">Tick to assign or unassign users.</small>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>{{ get_label('update', 'Update') }}
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ get_label('cancel', 'Cancel') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endforeach


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Initialize Select2 -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('.select2').select2({
            placeholder: "Select users",
            width: '100%'
        });
       const toastElList = document.querySelectorAll('.toast');
        toastElList.forEach(toastEl => {
            new bootstrap.Toast(toastEl, { delay: 4000 }).show();
        });
    });
    
</script>