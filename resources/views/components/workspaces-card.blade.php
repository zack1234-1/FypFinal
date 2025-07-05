@extends('layout')

@section('title', get_label('workspaces', 'Workspaces'))

@php
    $visibleColumns = getUserPreferences('workspaces');
@endphp

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-2 mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}">{{ get_label('home', 'Home') }}</a></li>
                <li class="breadcrumb-item active">{{ get_label('projects', 'Projects') }}</li>
            </ol>
        </nav>
        <button 
            type="button" 
            class="btn btn-sm btn-primary" 
            data-bs-toggle="modal" 
            data-bs-target="#createWorkspaceModal" 
            title="{{ get_label('create_project', 'Create project') }}">
            <i class='bx bx-plus'></i> {{ get_label('create', 'Create') }}
        </button>
    </div>

    @if ($workspaces->isNotEmpty())
        <div class="row">
            @foreach ($workspaces as $workspace)
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <!-- Title on the left -->
                                <h5 class="card-title mb-0">{{ $workspace->title }}</h5>

                                <!-- Buttons on the right -->
                                <div class="d-flex gap-2">
                                    <!-- Edit Button -->
                                    <button 
                                        type="button"
                                        class="btn d-flex align-items-center justify-content-center"
                                        style="background-color: #FFC107; width: 40px; height: 40px; border-radius: 8px;"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editWorkspaceModal{{ $workspace->id }}">
                                        <i class="fas fa-edit text-white"></i>
                                    </button>

                                    <!-- Delete Button -->
                                    <form action="{{ route('workspaces.destroy', $workspace->id) }}" method="POST" onsubmit="return confirm('Delete this project?')">
                                        @csrf
                                        @method('DELETE')
                                        <button 
                                            type="submit"
                                            class="btn d-flex align-items-center justify-content-center"
                                            style="background-color: #FF3D00; width: 40px; height: 40px; border-radius: 8px;">
                                            <i class="fas fa-trash text-white"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <p class="card-text">
                                <strong>{{ get_label('users', 'Users') }}:</strong><br>
                                @foreach ($workspace->users ?? [] as $user)
                                    <span class="badge bg-primary mb-1">{{ $user->first_name }}</span>
                                @endforeach
                            </p>
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
        <x-empty-state-card :type="'Workspaces'" />
    @endif
</div>

@foreach ($workspaces as $index => $workspace)
  <div class="modal fade" id="editWorkspaceModal{{ $workspace->id }}" tabindex="-1" aria-labelledby="editWorkspaceModalLabel{{ $workspace->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <form method="POST" action="{{ route('workspaces.update', $workspace->id) }}" class="modal-content">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="editWorkspaceModalLabel{{ $workspace->id }}">
                                                <i class="fas fa-edit me-2"></i>{{ get_label('edit_workspace', 'Edit Workspace') }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">{{ get_label('title', 'Title') }}</label>
                                                <input type="text" name="title" class="form-control" value="{{ $workspace->title }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">{{ get_label('assign_users', 'Assign Users') }}</label>
                                                <select name="user_ids[]" class="form-select select2-edit" multiple required>
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->id }}" {{ $workspace->users->contains($user->id) ? 'selected' : '' }}>
                                                            {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i>{{ get_label('update', 'Update') }}
                                            </button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ get_label('cancel', 'Cancel') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
@endforeach
<!-- Create Workspace Modal -->
<div class="modal fade" id="createWorkspaceModal" tabindex="-1" aria-labelledby="createWorkspaceModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('workspaces.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="createWorkspaceModalLabel">
                    <i class="fas fa-plus me-2"></i>{{ get_label('create_project', 'Create Project') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">{{ get_label('title', 'Title') }}</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ get_label('assign_users', 'Assign Users') }}</label>
                    <select name="user_ids[]" class="form-select select2-create" multiple required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <small class="text-muted">You can select multiple users.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>{{ get_label('save', 'Save') }}
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ get_label('cancel', 'Cancel') }}</button>
            </div>
        </form>
    </div>
</div>

<!-- Select2 CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        $('.select2-create').select2({ width: '100%', placeholder: "Select users" });
        $('.select2-edit').select2({ width: '100%', placeholder: "Select users" });
    });
</script>
@endsection
