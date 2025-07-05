@extends('layout')

@section('title')
    <?= get_label('workspaces', 'Workspaces') ?>
@endsection

@section('content')
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
        <x-workspaces-card :workspaces="$workspaces" :users="$users"/>
    </div>
    @php
        $routePrefix = Route::getCurrentRoute()->getPrefix();
    @endphp

    <script>
        var label_update = '<?= get_label('update', 'Update') ?>';
        var label_delete = '<?= get_label('delete', 'Delete') ?>';
        var label_not_assigned = '<?= get_label('not_assigned', 'Not assigned') ?>';
        var label_duplicate = '<?= get_label('duplicate', 'Duplicate') ?>';
        var routePrefix = '{{ $routePrefix }}';
    </script>
    <script src="{{ asset('assets/js/pages/workspaces.js') }}"></script>
@endsection


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
                    <select name="user_ids[]" class="form-select select2" multiple required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">
                                {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                            </option>
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

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Initialize Select2 -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        $('.select2').select2({
            placeholder: "Select users",
            width: '100%'
        });
    });
</script>