@extends('layout')

@section('title')
    <?= get_label('create_plan', 'Create Plan') ?>
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
                            <?= get_label('create_project', 'Create Project') ?>
                        </li>

                    </ol>
                </nav>
            </div>

            <div>

                <a href="{{ route('plans.index') }}"><button type="button" class="btn btn-sm btn-primary"
                        data-bs-toggle="tooltip" data-bs-placement="left"
                        data-bs-original-title="<?= get_label('projects', 'Projects') ?>"><i
                            class='bx bx-list-ul'></i></button></a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('plans.store') }}" id="plan-create-form"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="col-md-12">
                        <h2 class="mb-4">{{ get_label('create_new_project', 'Create a New Project') }}</h2>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="planName" class="form-label bold"><?= get_label('name', 'Name:') ?><span
                                        class="asterisk">*</span></label>
                                <input type="text" class="form-control" id="planName" name="name"
                                    placeholder="Enter a descriptive name" >
                            </div>
                            <div class="col-md-6">
                                <label for="planDescription"
                                    class="form-label bold"><?= get_label('description', 'Description:') ?><span
                                        class="asterisk">*</span></label>
                                <textarea class="form-control" id="planDescription" rows="3" placeholder="Provide a clear and concise overview" name= "description"
                                    ></textarea>
                            </div>
                        </div>

                        <div class="row mb-3">

                            <div class="col-md-3">
                                <label for="maxTeamMembers"
                                    class="form-label bold"><?= get_label('max_team_members', 'Maximum Team Members:') ?><span
                                        class="asterisk">*</span></label>
                                <input type="number" class="form-control" id="maxTeamMembers" min="-1" name = "max_team_members"
                                    placeholder="Enter a number" >
                            </div>
                            <div class="col-md-3">
                                <label for="maxWorkshops"
                                    class="form-label bold"><?= get_label('max_workspaces', 'Maximum Workspaces:') ?><span
                                        class="asterisk">*</span></label>
                                <input type="number" class="form-control" id="maxWorkshops" min="-1" name= "max_workspaces"
                                    placeholder="Enter a number" >
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12 ">
                                <label for="modules" class="form-label"><?= get_label('module_selection', 'Module Selection:') ?></label>  <span
                                    class="asterisk">*</span>
                            </div>
                            <div class="col-md-12">
                                <!-- Select All Checkbox -->
                               <!-- Select All Checkbox -->
                                <div class="form-check form-check-inline mb-3">
                                    <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                                    <label class="form-check-label fw-bold" for="select-all-checkbox">
                                        <?= get_label('select_all', 'Select All') ?>
                                    </label>
                                </div>

                                <!-- Module Checkboxes -->
                                <div class="row" id="moduleCheckboxes">
                                    <?php foreach (config('taskify.modules') as $module => $data): ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100">
                                                <div class="card-header alert alert-dark bg-transparent border rounded-top">
                                                    <i class="<?= $data['icon'] ?>"></i> <?= get_label($module, ucfirst($module)) ?>
                                                </div>
                                                <div class="card-body">
                                                    <p class="card-text">
                                                        <?= get_label(strtolower(str_replace(['-', ' '], '_', $data['description'])), $data['description']) ?>
                                                    </p>
                                                    <div class="form-check form-switch">
                                                    <input class="form-check-input module-checkbox" type="checkbox"  id="module<?= ucfirst($module) ?>" value="<?= $module?>">
                                                        <label class="form-check-label fw-bold" for="module<?= ucfirst($module) ?>">Enabled</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <button type="submit" class="btn btn-primary mb-3 mt-3"
                                    id="createPlanButton"><?= get_label('create_plan_button', 'Create Plan') ?></button>
                            </div>
                </form>
            </div>
        </div>

    </div>

<script>
document.getElementById('select-all-checkbox').addEventListener('change', function () {
    const checked = this.checked;
    document.querySelectorAll('.module-checkbox').forEach(function (checkbox) {
        checkbox.checked = checked;
    });
});

document.getElementById('plan-create-form').addEventListener('submit', function (e) {
    const modules = Array.from(document.querySelectorAll('.module-checkbox:checked')).map(cb => cb.value);
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'modules';
    input.value = JSON.stringify(modules);

    this.appendChild(input);
});
</script>


@endsection