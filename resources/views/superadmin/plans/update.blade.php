@extends('layout')

@section('title')
    <?= get_label('edit_plan', 'Edit Plan') ?>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between mt-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-style1">
                        <li class="breadcrumb-item">
                            <a href="{{ route('superadmin.panel') }}"><?= get_label('home', 'Home') ?></a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('plans.index') }}"><?= get_label('projects', 'Projects') ?></a>
                        </li>
                        <li class="breadcrumb-item active">
                            <?= get_label('edit_project', 'Edit Project') ?>
                        </li>
                    </ol>
                </nav>
            </div>

            <div>
                <a href="{{ route('plans.index') }}">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                        data-bs-placement="left" data-bs-original-title="<?= get_label('projects', 'Projects') ?>">
                        <i class='bx bx-list-ul'></i>
                    </button>
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('plans.update', $plan->id) }}" id="plan-update-form" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="col-md-12">
                        <h2 class="mb-4"><?= get_label('edit_project', 'Edit Project') ?></h2>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="planName" class="form-label bold"><?= get_label('name', 'Name:') ?></label> <span class="asterisk">*</span>
                                <input type="text" class="form-control" id="planName" name="name"
                                    placeholder="Enter a descriptive name" value="{{ old('name', $plan->name) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="planDescription" class="form-label bold"><?= get_label('description', 'Description:') ?></label> <span class="asterisk">*</span>
                                <textarea class="form-control" id="planDescription" name="description" rows="3"
                                    placeholder="Provide a clear and concise overview" required>{{ old('description', $plan->description) }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="maxTeamMembers" class="form-label bold"><?= get_label('max_team_members', 'Maximum Team Members:') ?></label> <span class="asterisk">*</span>
                                <input type="number" class="form-control" id="maxTeamMembers" name="max_team_members" min="-1"
                                    value="{{ old('max_team_members', $plan->max_team_members) }}" >
                            </div>
                            <div class="col-md-3">
                                <label for="maxWorkshops" class="form-label bold"><?= get_label('max_workspaces', 'Maximum Workspaces:') ?></label> <span class="asterisk">*</span>
                                <input type="number" class="form-control" id="maxWorkshops" name="max_workspaces" min="-1"
                                    value="{{ old('max_workspaces', $plan->max_workspaces) }}" >
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label bold"><?= get_label('module_selection', 'Module Selection:') ?></label> <span class="asterisk">*</span>
                                <div class="form-check form-check-inline mb-2">
                                    <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                                    <label class="form-check-label bold" for="select-all-checkbox"><?= get_label('select_all', 'Select All') ?></label>
                                </div>

                                <div class="row" id="moduleCheckboxes">
                                    @foreach(config('taskify.modules') as $module => $data)
                                        <div class="col-md-4 mt-3 mb-3">
                                        <div class="card mb-3">
                                            <h5 class="card-header bg-transparent border border-secondary-subtle rounded-2 p-3 h-100 alert alert-dark mb-0">
                                                <i class="{{ $data['icon'] }}"></i> {{ get_label($module, ucfirst($module)) }}
                                            </h5>
                                            <div class="card-body">
                                                <p class="card-text">{{ $data['description'] }}</p>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input module-checkbox"
                                                        type="checkbox"
                                                        id="module{{ ucfirst($module) }}"
                                                        name="modules[]"
                                                        value="{{ $module }}">
                                                    <label class="form-check-label bold" for="module{{ ucfirst($module) }}">Enabled</label>
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-3 mb-3" id="updatePlanButton">
                            {{ get_label('update_project_button', 'Update Project') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('select-all-checkbox').addEventListener('change', function () {
            const isChecked = this.checked;
            document.querySelectorAll('.module-checkbox').forEach(checkbox => {
                checkbox.checked = isChecked;
            });
        });
    </script>
@endsection
