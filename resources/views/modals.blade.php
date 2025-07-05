@php
    $prefix = null;
    $currentRoute = Route::current();
    if ($currentRoute) {
        $uriSegments = explode('/', $currentRoute->uri());
        $prefix = count($uriSegments) > 1 ? $uriSegments[0] : '';
    }
    use App\Models\Workspace;
    $workspace = Workspace::find(session()->get('workspace_id'));
    $auth_user = getAuthenticatedUser();
    $toSelectProjectUsers = isset($workspace) ? $workspace->users : [];
    $toSelectProjectClients = isset($workspace) ? $workspace->clients : [];
    $roles = \Spatie\Permission\Models\Role::where([['name', '!=', 'admin'], ['name', '!=', 'superadmin']])->get();
    $adminId = getAdminIdByUserRole();
    $admin = App\Models\Admin::with('user', 'teamMembers.user')->find($adminId);
    $clients = App\Models\Client::where('admin_id', $adminId)->get();
@endphp
@if (Request::is($prefix . '/projects') ||
        Request::is($prefix . '/projects/*') ||
        Request::is($prefix . '/tasks') ||
        Request::is($prefix . '/tasks/*') ||
        Request::is($prefix . '/status/manage') ||
        Request::is($prefix . '/users') ||
        Request::is($prefix . '/clients'))
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
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('color', 'Color') ?> <span class="asterisk">*</span>
                            </label>
                            <select class="form-select" id="color" name="color">
                                <option class="badge bg-label-primary" value="primary"
                                    {{ old('color') == 'primary' ? 'selected' : '' }}>
                                    <?= get_label('primary', 'Primary') ?>
                                </option>
                                <option class="badge bg-label-secondary" value="secondary"
                                    {{ old('color') == 'secondary' ? 'selected' : '' }}>
                                    <?= get_label('secondary', 'Secondary') ?>
                                </option>
                                <option class="badge bg-label-success" value="success"
                                    {{ old('color') == 'success' ? 'selected' : '' }}>
                                    <?= get_label('success', 'Success') ?>
                                </option>
                                <option class="badge bg-label-danger" value="danger"
                                    {{ old('color') == 'danger' ? 'selected' : '' }}>
                                    <?= get_label('danger', 'Danger') ?>
                                </option>
                                <option class="badge bg-label-warning" value="warning"
                                    {{ old('color') == 'warning' ? 'selected' : '' }}>
                                    <?= get_label('warning', 'Warning') ?>
                                </option>
                                <option class="badge bg-label-info" value="info"
                                    {{ old('color') == 'info' ? 'selected' : '' }}>
                                    <?= get_label('info', 'Info') ?>
                                </option>
                                <option class="badge bg-label-dark" value="dark"
                                    {{ old('color') == 'dark' ? 'selected' : '' }}>
                                    <?= get_label('dark', 'Dark') ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?></label>
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit_btn">
                        <?= get_label('create', 'Create') ?></label>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_status_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('status.update') }}" class="modal-content form-submit-event" method="POST">
                <input type="hidden" name="id" id="status_id">
                <input type="hidden" name="dnr">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_status', 'Update status') ?>
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
                            <input type="text" id="status_title" class="form-control" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('color', 'Color') ?> <span class="asterisk">*</span>
                            </label>
                            <select class="form-select" id="status_color" name="color" required>
                                <option class="badge bg-label-primary" value="primary">
                                    <?= get_label('primary', 'Primary') ?>
                                </option>
                                <option class="badge bg-label-secondary" value="secondary">
                                    <?= get_label('secondary', 'Secondary') ?>
                                </option>
                                <option class="badge bg-label-success" value="success">
                                    <?= get_label('success', 'Success') ?>
                                </option>
                                <option class="badge bg-label-danger" value="danger">
                                    <?= get_label('danger', 'Danger') ?>
                                </option>
                                <option class="badge bg-label-warning" value="warning">
                                    <?= get_label('warning', 'Warning') ?>
                                </option>
                                <option class="badge bg-label-info" value="info">
                                    <?= get_label('info', 'Info') ?>
                                </option>
                                <option class="badge bg-label-dark" value="dark">
                                    <?= get_label('dark', 'Dark') ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?></label>
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit_btn">
                        <?= get_label('update', 'Update') ?></label>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
<div class="modal fade" id="confirmUpdateStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('confirm', 'Confirm!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('confirm_update_status', 'Do You Want to Update the Status?') ?>
                </p>
                <textarea class="form-control" id="statusNote" placeholder="<?= get_label('optional_note', 'Optional Note') ?>"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="declineUpdateStatus"
                    data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="confirmUpdateStatus">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="confirmUpdatePriorityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('confirm', 'Confirm!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('confirm_update_priority', 'Do You Want to Update the Priority?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" id="declineUpdatePriority"
                    data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="confirmUpdatePriority">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
@if (Request::is($prefix . '/projects') ||
        Request::is($prefix . '/projects/*') ||
        Request::is($prefix . '/tags/manage') ||
        Request::is($prefix . '/users') ||
        Request::is($prefix . '/clients'))
    <div class="modal fade" id="create_tag_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('tags.store') }}" method="POST">
                <input type="hidden" name="dnr">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_tag', 'Create tag') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
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
                                <?= get_label('color', 'Color') ?> <span class="asterisk">*</span>
                            </label>
                            <select class="form-select select-bg-label-primary" id="color" name="color">
                                <option class="badge bg-label-primary" value="primary"
                                    {{ old('color') == 'primary' ? 'selected' : '' }}>
                                    <?= get_label('primary', 'Primary') ?>
                                </option>
                                <option class="badge bg-label-secondary" value="secondary"
                                    {{ old('color') == 'secondary' ? 'selected' : '' }}>
                                    <?= get_label('secondary', 'Secondary') ?>
                                </option>
                                <option class="badge bg-label-success" value="success"
                                    {{ old('color') == 'success' ? 'selected' : '' }}>
                                    <?= get_label('success', 'Success') ?>
                                </option>
                                <option class="badge bg-label-danger" value="danger"
                                    {{ old('color') == 'danger' ? 'selected' : '' }}>
                                    <?= get_label('danger', 'Danger') ?>
                                </option>
                                <option class="badge bg-label-warning" value="warning"
                                    {{ old('color') == 'warning' ? 'selected' : '' }}>
                                    <?= get_label('warning', 'Warning') ?>
                                </option>
                                <option class="badge bg-label-info" value="info"
                                    {{ old('color') == 'info' ? 'selected' : '' }}>
                                    <?= get_label('info', 'Info') ?>
                                </option>
                                <option class="badge bg-label-dark" value="dark"
                                    {{ old('color') == 'dark' ? 'selected' : '' }}>
                                    <?= get_label('dark', 'Dark') ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?></label>
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit_btn">
                        <?= get_label('create', 'Create') ?></label>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/tags/manage'))
    <div class="modal fade" id="edit_tag_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('tags.update') }}" method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" name="id" id="tag_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_tag', 'Update tag') ?>
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
                            <input type="text" id="tag_title" class="form-control" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('color', 'Color') ?> <span class="asterisk">*</span>
                            </label>
                            <select class="form-select select-bg-label-primary" id="tag_color" name="color">
                                <option class="badge bg-label-primary" value="primary">
                                    <?= get_label('primary', 'Primary') ?>
                                </option>
                                <option class="badge bg-label-secondary" value="secondary">
                                    <?= get_label('secondary', 'Secondary') ?>
                                </option>
                                <option class="badge bg-label-success" value="success">
                                    <?= get_label('success', 'Success') ?>
                                </option>
                                <option class="badge bg-label-danger" value="danger">
                                    <?= get_label('danger', 'Danger') ?>
                                </option>
                                <option class="badge bg-label-warning" value="warning">
                                    <?= get_label('warning', 'Warning') ?>
                                </option>
                                <option class="badge bg-label-info" value="info">
                                    <?= get_label('info', 'Info') ?>
                                </option>
                                <option class="badge bg-label-dark" value="dark">
                                    <?= get_label('dark', 'Dark') ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?></label>
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit_btn">
                        <?= get_label('update', 'Update') ?></label>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/home') || Request::is($prefix . '/todos'))
@endif
<div class="modal fade" id="default_language_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('confirm', 'Confirm!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('set_primary_lang_alert', 'Are you want to set as your primary language?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="confirm">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="leaveWorkspaceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('warning', 'Warning!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?= get_label('confirm_leave_workspace', 'Are you sure you want leave this workspace?') ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-danger" id="confirm">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="create_language_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content form-submit-event" action="{{ route('languages.store') }}" method="POST">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">
                    <?= get_label('create_language', 'Create language') ?>
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
                        <input type="text" class="form-control" name="name"
                            placeholder="For Example: English" />
                        @error('name')
                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <label for="nameBasic" class="form-label">
                            <?= get_label('code', 'Code') ?> <span class="asterisk">*</span>
                        </label>
                        <input type="text" class="form-control" name="code" placeholder="For Example: en" />
                        @error('code')
                            <p class="text-danger mt-1 text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" id="submit_btn" class="btn btn-primary">
                    <?= get_label('create', 'Create') ?>
                </button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="edit_language_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <form class="modal-content form-submit-event" action="{{ route('languages.update') }}" method="POST">
            <input type="hidden" name="id" id="language_id">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">
                    <?= get_label('update_language', 'Update language') ?>
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
                        <input type="text" class="form-control" name="name" id="language_title"
                            placeholder="For Example: English" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" id="submit_btn" class="btn btn-primary">
                    <?= get_label('update', 'Update') ?>
                </button>
            </div>
        </form>
    </div>
</div>
@if (Request::is($prefix . '/leave-requests') || Request::is($prefix . '/leave-requests/*'))
    <div class="modal fade" id="create_leave_request_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content form-submit-event" action="{{ route('leave_requests.store') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" name="table" value="lr_table">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_leave_requet', 'Create leave request') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        @if (is_admin_or_leave_editor())
                            <div class="col-12 mb-3">
                                <label class="form-label" for="user_id">
                                    <?= get_label('select_user', 'Select user') ?> <span class="asterisk">*</span>
                                </label>
                                <select class="form-select selectLruser"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>"
                                    name="user_id">
                                    @isset($users)
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                <?= $user->id == getAuthenticatedUser()->id ? 'selected' : '' ?>>
                                                {{ $user->first_name . ' ' . $user->last_name }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        @endif
                        <div class="col-12 mb-3">
                            <div class="form-check form-switch">
                                <label class="form-check-label" for="partialLeave">
                                    <input class="form-check-input" type="checkbox" name="partialLeave"
                                        id="partialLeave">
                                    <?= get_label('partial_leave', 'Partial Leave') ?>?
                                </label>
                            </div>
                        </div>
                        <div class="col-5 leave-from-date-div mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('from_date', 'From date') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="start_date" name="from_date" class="form-control"
                                placeholder="" autocomplete="off">
                        </div>
                        <div class="col-5 leave-to-date-div mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('to_date', 'To date') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="lr_end_date" name="to_date" class="form-control"
                                placeholder="" autocomplete="off">
                            <span class="form-text d-none"
                                id="guide_text">{{ get_label(
                                    'for_partial_leave_start_date_and_end_must_be_same',
                                    'For partial leave start date
                                                                                                                                                                                                                            and end date must be same',
                                ) }}</span>
                        </div>
                        <div class="col-2 leave-from-time-div d-none mb-3">
                            <label class="form-label" for="">
                                <?= get_label('from_time', 'From Time') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="time" name="from_time" class="form-control"
                                value="{{ old('from_time') }}">
                        </div>
                        <div class="col-2 leave-to-time-div d-none mb-3">
                            <label class="form-label" for="">
                                <?= get_label('to_time', 'To Time') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="time" name="to_time" class="form-control" value="{{ old('to_time') }}">
                        </div>
                        <div class="col-2 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('days', 'Days') ?>
                            </label>
                            <input type="text" id="total_days" class="form-control" value="1"
                                placeholder="" disabled>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input leaveVisibleToAll" type="checkbox"
                                    name="leaveVisibleToAll" id="leaveVisibleToAll">
                                <label class="form-check-label" for="leaveVisibleToAll">
                                    <?= get_label('visible_to_all', 'Visible To All') ?>? <i
                                        class='bx bx-info-circle text-primary' data-bs-toggle="tooltip"
                                        data-bs-offset="0,4" data-bs-placement="top" title=""
                                        data-bs-html="true"
                                        data-bs-original-title="{{ get_label('leave_visible_to_info', 'Disabled: Requestee, Admin, and Leave Editors, along with selected users, will be able to know when the requestee is on leave. Enabled: All team members will be able to know when the requestee is on leave.') }}"></i>
                                </label>
                            </div>
                        </div>
                        <div class="col-12 leaveVisibleToDiv mb-3">
                            <select class="form-control js-example-basic-multiple" name="visible_to_ids[]"
                                multiple="multiple"
                                data-placeholder="<?= get_label('select_users_leave_visible_to', 'Select Users Leave Visible To') ?>">
                                @isset($users)
                                    @foreach ($users as $user)
                                        @if (!is_admin_or_leave_editor($user) && $user->id != $auth_user->id)
                                            <option value="{{ $user->id }}">
                                                {{ $user->first_name . ' ' . $user->last_name }}</option>
                                        @endif
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                    </div>
                    <label for="description" class="form-label">
                        <?= get_label('reason', 'Reason') ?> <span class="asterisk">*</span>
                    </label>
                    <textarea class="form-control" name="reason"
                        placeholder="<?= get_label('please_enter_leave_reason', 'Please enter leave reason') ?>"></textarea>
                    @if (is_admin_or_leave_editor())
                        <div class="row mt-4">
                            <div class="col-12 d-flex justify-content-center">
                                <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                    <input type="radio" class="btn-check" name="status" id="create_lr_pending"
                                        value="pending" checked>
                                    <label class="btn btn-outline-primary" for="create_lr_pending">
                                        <?= get_label('pending', 'Pending') ?>
                                    </label>
                                    <input type="radio" class="btn-check" name="status" id="create_lr_approved"
                                        value="approved">
                                    <label class="btn btn-outline-primary" for="create_lr_approved">
                                        <?= get_label('approved', 'Approved') ?>
                                    </label>
                                    <input type="radio" class="btn-check" name="status" id="create_lr_rejected"
                                        value="rejected">
                                    <label class="btn btn-outline-primary" for="create_lr_rejected">
                                        <?= get_label('rejected', 'Rejected') ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('create', 'Create') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_leave_request_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content form-submit-event" action="{{ route('leave_requests.update') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" name="table" value="lr_table">
                <input type="hidden" name="id" id="lr_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_leave_request', 'Update leave request') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        @if (is_admin_or_leave_editor())
                            <div class="col-12 mb-3">
                                <label class="form-label">
                                    <?= get_label('user', 'User') ?> <span class="asterisk">*</span>
                                </label>
                                <input type="text" id="leaveUser" class="form-control" disabled>
                            </div>
                        @endif
                        <div class="col-12">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="updatePartialLeave"
                                    name="partialLeave">
                                <label class="form-check-label" for="updatePartialLeave">
                                    <?= get_label('partial_leave', 'Partial Leave') ?>?
                                </label>
                            </div>
                        </div>
                        <div class="col-5 leave-from-date-div mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('from_date', 'From date') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="update_start_date" name="from_date" class="form-control"
                                placeholder="" autocomplete="off">
                        </div>
                        <div class="col-5 leave-to-date-div mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('to_date', 'To date') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="update_end_date" name="to_date" class="form-control"
                                placeholder="" autocomplete="off">
                        </div>
                        <div class="col-2 leave-from-time-div d-none mb-3">
                            <label class="form-label" for="">
                                <?= get_label('from_time', 'From Time') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="time" name="from_time" class="form-control">
                        </div>
                        <div class="col-2 leave-to-time-div d-none mb-3">
                            <label class="form-label" for="">
                                <?= get_label('to_time', 'To Time') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="time" name="to_time" class="form-control">
                        </div>
                        <div class="col-2 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('days', 'Days') ?>
                            </label>
                            <input type="text" id="update_total_days" class="form-control" value="1"
                                placeholder="" disabled>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input leaveVisibleToAll" type="checkbox"
                                    name="leaveVisibleToAll" id="updateLeaveVisibleToAll">
                                <label class="form-check-label" for="updateLeaveVisibleToAll">
                                    <?= get_label('visible_to_all', 'Visible To All') ?>?
                                    <i class='bx bx-info-circle text-primary' data-bs-toggle="tooltip"
                                        data-bs-offset="0,4" data-bs-placement="top" title=""
                                        data-bs-html="true"
                                        data-bs-original-title="{{ get_label('leave_visible_to_info', 'Disabled: Requestee, Admin, and Leave Editors, along with selected users, will be able to know when the requestee is on leave. Enabled: All team members will be able to know when the requestee is on leave.') }}"></i>
                                </label>
                            </div>
                        </div>
                        <div class="col-12 leaveVisibleToDiv mb-3">
                            <select class="form-control js-example-basic-multiple" name="visible_to_ids[]"
                                multiple="multiple"
                                data-placeholder="<?= get_label('select_users_leave_visible_to', 'Select Users Leave Visible To') ?>">
                                @isset($users)
                                    @foreach ($users as $user)
                                        @if (!is_admin_or_leave_editor($user) && $user->id != $auth_user->id)
                                            <option value="{{ $user->id }}">
                                                {{ $user->first_name . ' ' . $user->last_name }}</option>
                                        @endif
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">
                                <?= get_label('reason', 'Reason') ?> <span class="asterisk">*</span>
                            </label>
                            <textarea class="form-control" name="reason"
                                placeholder="<?= get_label('please_enter_leave_reason', 'Please enter leave reason') ?>"></textarea>
                        </div>
                        @php
                            $isAdminOrLr = is_admin_or_leave_editor();
                            $disabled = !$isAdminOrLr ? 'disabled' : '';
                        @endphp
                        <div class="col-12 d-flex justify-content-center">
                            <div class="btn-group" role="group" aria-label="Basic radio toggle button group">
                                <input type="radio" class="btn-check" name="status" id="update_lr_pending"
                                    value="pending" {{ $disabled }}>
                                <label class="btn btn-outline-primary" for="update_lr_pending">
                                    <?= get_label('pending', 'Pending') ?>
                                </label>
                                <input type="radio" class="btn-check" name="status" id="update_lr_approved"
                                    value="approved" {{ $disabled }}>
                                <label class="btn btn-outline-primary" for="update_lr_approved">
                                    <?= get_label('approved', 'Approved') ?>
                                </label>
                                <input type="radio" class="btn-check" name="status" id="update_lr_rejected"
                                    value="rejected" {{ $disabled }}>
                                <label class="btn btn-outline-primary" for="update_lr_rejected">
                                    <?= get_label('rejected', 'Rejected') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit_btn">
                        <?= get_label('update', 'Update') ?></label>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
{{-- Create and Edit Contract Type Modals --}}
<div class="modal fade" id="create_contract_type_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <form class="modal-content form-submit-event" action="{{ route('contracts.store_contract_type') }}"
            method="POST">
            <input type="hidden" name="dnr">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">
                    <?= get_label('create_contract_type', 'Create contract type') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col mb-3">
                        <label for="nameBasic" class="form-label">
                            <?= get_label('type', 'Type') ?> <span class="asterisk">*</span>
                        </label>
                        <input type="text" class="form-control" name="type"
                            placeholder="<?= get_label('please_enter_contract_type', 'Please enter contract type') ?>" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" id="submit_btn" class="btn btn-primary">
                    <?= get_label('create', 'Create') ?>
                </button>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="edit_contract_type_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <form class="modal-content form-submit-event" action="{{ route('contracts.update_contract_type') }}"
            method="POST">
            <input type="hidden" name="dnr">
            <input type="hidden" id="update_contract_type_id" name="id">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">
                    <?= get_label('update_contract_type', 'Update contract type') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col mb-3">
                        <label for="nameBasic" class="form-label">
                            <?= get_label('type', 'Type') ?> <span class="asterisk">*</span>
                        </label>
                        <input type="text" class="form-control" name="type" id="contract_type"
                            placeholder="<?= get_label('please_enter_contract_type', 'Please enter contract type') ?>" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" id="submit_btn" class="btn btn-primary">
                    <?= get_label('update', 'Update') ?>
                </button>
            </div>
        </form>
    </div>
</div>
@if (Request::is($prefix . '/contracts'))
    <div class="modal fade" id="create_contract_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content form-submit-event" action="{{ route('contracts.store') }}" method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" name="table" value="contracts_table">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_contract', 'Create contract') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" name="title" class="form-control"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('value', 'Value') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input type="text" name="value" class="form-control"
                                    placeholder="<?= get_label('please_enter_value', 'Please enter value') ?>">
                            </div>
                            <span class="text-danger error-message"></span>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('starts_at', 'Starts at') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="start_date" name="start_date" class="form-control"
                                placeholder="" autocomplete="off">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('ends_at', 'Ends at') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="end_date" name="end_date" class="form-control"
                                placeholder="" autocomplete="off">
                        </div>
                        @if (!isClient())
                            <label class="form-label" for="">
                                <?= get_label('select_client', 'Select client') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="col-12 mb-3">
                                <select class="form-select" name="client_id">
                                    <option value="">
                                        <?= get_label('please_select', 'Please select') ?>
                                    </option>
                                    @isset($clients)
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}">
                                                {{ $client->first_name . ' ' . $client->last_name }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        @endif
                        <label class="form-label" for="">
                            <?= get_label('select_project', 'Select project') ?>
                            <span class="asterisk">*</span>
                        </label>
                        <div class="col-12 mb-3">
                            <select class="form-select" name="project_id">
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                @isset($projects)
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->title }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <label class="form-label" for="">
                            <?= get_label('select_contract_type', 'Select contract type') ?> <span
                                class="asterisk">*</span>
                        </label>
                        <div class="col-12 mb-3">
                            <select class="form-select" name="contract_type_id">
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                @isset($contract_types)
                                    @foreach ($contract_types as $type)
                                        <option value="{{ $type->id }}">{{ $type->type }}</option>
                                    @endforeach
                                @endisset
                            </select>
                            <div class="mt-2">
                                <a href="javascript:void(0);" class="openCreateContractTypeModal"><button
                                        type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title=" <?= get_label('create_contract_type', 'Create contract type') ?>"><i
                                            class="bx bx-plus"></i></button></a>
                                <a href="{{ route('contracts.contract_types') }}"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('manage_contract_types', 'Manage contract types') ?>"><i
                                            class="bx bx-list-ul"></i></button></a>
                            </div>
                        </div>
                    </div>
                    <label for="description" class="form-label">
                        <?= get_label('description', 'Description') ?>
                    </label>
                    <textarea class="form-control description" name="description" id="contract_description"
                        placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('create', 'Create') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_contract_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content form-submit-event" action="{{ route('contracts.update') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" name="table" value="contracts_table">
                <input type="hidden" id="contract_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_contract', 'Update contract') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="title" name="title" class="form-control"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('value', 'Value') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input type="text" id="value" name="value" class="form-control"
                                    placeholder="<?= get_label('please_enter_value', 'Please enter value') ?>">
                            </div>
                            <span class="text-danger error-message"></span>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('starts_at', 'Starts at') ?>
                                <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="update_start_date" name="start_date" class="form-control"
                                placeholder="" autocomplete="off">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('ends_at', 'Ends at') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="update_end_date" name="end_date" class="form-control"
                                placeholder="" autocomplete="off">
                        </div>
                        <label class="form-label" for="">
                            <?= get_label('select_client', 'Select client') ?>
                            <span class="asterisk">*</span>
                        </label>
                        <div class="col-12 mb-3">
                            <select class="form-select" id="client_id" name="client_id">
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                @isset($clients)
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">
                                            {{ $client->first_name . ' ' . $client->last_name }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <label class="form-label" for="">
                            <?= get_label('select_project', 'Select project') ?> <span class="asterisk">*</span>
                        </label>
                        <div class="col-12 mb-3">
                            <select class="form-select" id="project_id" name="project_id">
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                @isset($projects)
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}">{{ $project->title }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <label class="form-label" for="">
                            <?= get_label('select_contract_type', 'Select contract type') ?> <span
                                class="asterisk">*</span>
                        </label>
                        <div class="col-12 mb-3">
                            <select class="form-select" id="contract_type_id" name="contract_type_id">
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                @isset($contract_types)
                                    @foreach ($contract_types as $type)
                                        <option value="{{ $type->id }}">{{ $type->type }}</option>
                                    @endforeach
                                @endisset
                            </select>
                            <div class="mt-2">
                                <a href="javascript:void(0);" class="openCreateContractTypeModal"><button
                                        type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title=" <?= get_label('create_contract_type', 'Create contract type') ?>"><i
                                            class="bx bx-plus"></i></button></a>
                                <a href="{{ route('contracts.contract_types') }}"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('manage_contract_types', 'Manage contract types') ?>"><i
                                            class="bx bx-list-ul"></i></button></a>
                            </div>
                        </div>
                    </div>
                    <label for="description" class="form-label">
                        <?= get_label('description', 'Description') ?>
                    </label>
                    <textarea class="form-control description" name="description" id="update_contract_description"
                        placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('update', 'Update') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/payslips/create') || Request::is($prefix . '/payment-methods'))
    <div class="modal fade" id="create_pm_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <form class="modal-content form-submit-event" action="{{ route('paymentMethods.store') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_payment_method', 'Create payment method') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" name="title"
                                placeholder="Please enter title" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('create', 'Create') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_pm_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <form class="modal-content form-submit-event" action="{{ route('paymentMethods.update') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" id="pm_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_payment_method', 'Update payment method') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" name="title" id="pm_title"
                                placeholder="Please enter title" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('update', 'Update') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/payslips/*') || Request::is($prefix . '/allowances'))
    <div class="modal fade" id="create_allowance_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('allowances.store') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_allowance', 'Create allowance') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" name="title"
                                placeholder="Please enter title" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="">
                                <?= get_label('amount', 'Amount') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input class="form-control min_0" min="0" type="number" name="amount"
                                    step="0.01" placeholder="Please enter amount">
                            </div>
                            <p class="text-danger error-message mt-1 text-xs"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('create', 'Create') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_allowance_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('allowances.update') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" name="id" id="allowance_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_allowance', 'Update allowance') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" id="allowance_title" name="title"
                                placeholder="Please enter title" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="">
                                <?= get_label('amount', 'Amount') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input class="form-control" type="number" id="allowance_amount" name="amount"
                                    step="0.01" placeholder="Please enter amount">
                            </div>
                            <p class="text-danger error-message mt-1 text-xs"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('update', 'Update') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/payslips/*') || Request::is($prefix . '/deductions'))
    <div class="modal fade" id="create_deduction_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('deductions.store') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_deduction', 'Create deduction') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" name="title"
                                placeholder="Please enter title" />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('type', 'Type') ?> <span class="asterisk">*</span>
                            </label>
                            <select id="deduction_type" name="type" class="form-select">
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                <option value="amount">
                                    <?= get_label('amount', 'Amount') ?>
                                </option>
                                <option value="percentage">
                                    <?= get_label('percentage', 'Percentage') ?>
                                </option>
                            </select>
                        </div>
                        <div class="col-md-12 d-none mb-3" id="amount_div">
                            <label class="form-label" for="">
                                <?= get_label('amount', 'Amount') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input class="form-control min_0" min="0" type="number" name="amount"
                                    step="0.01" placeholder="Please enter amount">
                            </div>
                            <p class="text-danger error-message mt-1 text-xs"></p>
                        </div>
                        <div class="col-md-12 d-none mb-3" id="percentage_div">
                            <label class="form-label" for="">
                                <?= get_label('percentage', 'Percentage') ?>
                                <span class="asterisk">*</span>
                            </label>
                            <input class="form-control max_100" max="100" type="number" name="percentage"
                                placeholder="Please enter percentage">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('create', 'Create') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_deduction_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('deductions.update') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" id="deduction_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_deduction', 'Update deduction') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" id="deduction_title" name="title"
                                placeholder="Please enter title" />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('type', 'Type') ?> <span class="asterisk">*</span>
                            </label>
                            <select id="update_deduction_type" name="type" class="form-control">
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                <option value="amount">
                                    <?= get_label('amount', 'Amount') ?>
                                </option>
                                <option value="percentage">
                                    <?= get_label('percentage', 'Percentage') ?>
                                </option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3" id="update_amount_div">
                            <label class="form-label" for="">
                                <?= get_label('amount', 'Amount') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input class="form-control" type="number" id="deduction_amount" name="amount"
                                    step="0.01" placeholder="Please enter amount">
                            </div>
                            <p class="text-danger error-message mt-1 text-xs"></p>
                        </div>
                        <div class="col-md-12 mb-3" id="update_percentage_div">
                            <label class="form-label" for="">
                                <?= get_label('percentage', 'Percentage') ?>
                                <span class="asterisk">*</span>
                            </label>
                            <input class="form-control" type="number" id="deduction_percentage"
                                name="percentage" placeholder="Please enter percentage">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('update', 'Update') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/estimates-invoices/create') ||
        Request::is($prefix . '/taxes') ||
        Request::is($prefix . '/units') ||
        Request::is($prefix . '/items'))
    <div class="modal fade" id="create_tax_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('taxes.store') }}" method="POST">
                <input type="hidden" name="dnr">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_tax', 'Create tax') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('type', 'Type') ?> <span class="asterisk">*</span>
                            </label>
                            <select id="deduction_type" name="type" class="form-select">
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                <option value="amount">
                                    <?= get_label('amount', 'Amount') ?>
                                </option>
                                <option value="percentage">
                                    <?= get_label('percentage', 'Percentage') ?>
                                </option>
                            </select>
                        </div>
                        <div class="col-md-12 d-none mb-3" id="amount_div">
                            <label class="form-label" for="">
                                <?= get_label('amount', 'Amount') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input class="form-control" type="number" min="0" name="amount"
                                    placeholder="<?= get_label('please_enter_amount', 'Please enter amount') ?>">
                            </div>
                            <p class="text-danger error-message mt-1 text-xs"></p>
                        </div>
                        <div class="col-md-12 d-none mb-3" id="percentage_div">
                            <label class="form-label" for="">
                                <?= get_label('percentage', 'Percentage') ?>
                                <span class="asterisk">*</span>
                            </label>
                            <input class="form-control" type="number" name="percentage" min="1"
                                max="100"
                                placeholder="<?= get_label('please_enter_percentage', 'Please enter percentage') ?>">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('create', 'Create') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_tax_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('taxes.update') }}" method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" id="tax_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_tax', 'Update tax') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" id="tax_title" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('type', 'Type') ?> <span class="asterisk">*</span>
                            </label>
                            <select id="update_tax_type" name="type" class="form-select" disabled>
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                <option value="amount">
                                    <?= get_label('amount', 'Amount') ?>
                                </option>
                                <option value="percentage">
                                    <?= get_label('percentage', 'Percentage') ?>
                                </option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3" id="update_amount_div">
                            <label class="form-label" for="">
                                <?= get_label('amount', 'Amount') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input class="form-control" type="text" id="tax_amount" name="amount"
                                    placeholder="<?= get_label('please_enter_amount', 'Please enter amount') ?>"
                                    disabled>
                            </div>
                            <p class="text-danger error-message mt-1 text-xs"></p>
                        </div>
                        <div class="col-md-12 mb-3" id="update_percentage_div">
                            <label class="form-label" for="">
                                <?= get_label('percentage', 'Percentage') ?>
                                <span class="asterisk">*</span>
                            </label>
                            <input class="form-control" type="number" id="tax_percentage" name="percentage"
                                placeholder="<?= get_label('please_enter_percentage', 'Please enter percentage') ?>"
                                disabled>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('update', 'Update') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="create_unit_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('units.store') }}" method="POST">
                <input type="hidden" name="dnr">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_unit', 'Create unit') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('description', 'Description') ?>
                            </label>
                            <textarea class="form-control" name="description"
                                placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('create', 'Create') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_unit_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('units.update') }}" method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" id="unit_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_unit', 'Update unit') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" id="unit_title" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('description', 'Description') ?>
                            </label>
                            <textarea class="form-control" id="unit_description" name="description"
                                placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('update', 'Update') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="create_item_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('items.store') }}" method="POST">
                @if (Request::is('items'))
                    <input type="hidden" name="dnr">
                @else
                    <input type="hidden" name="reload">
                @endif
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_item', 'Create item') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('price', 'Price') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" name="price"
                                placeholder="<?= get_label('please_enter_price', 'Please enter price') ?>" />
                        </div>
                        @if (isset($units) && is_iterable($units))
                            <div class="col-md-6 mb-3">
                                <label for="nameBasic" class="form-label">
                                    <?= get_label('unit', 'Unit') ?>
                                </label>
                                <select class="form-select" name="unit_id">
                                    <option value="">
                                        <?= get_label('please_select', 'Please select') ?>
                                    </option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('description', 'Description') ?>
                            </label>
                            <textarea class="form-control" name="description"
                                placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('create', 'Create') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_item_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form class="modal-content form-submit-event" action="{{ route('items.update') }}" method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" id="item_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_item', 'Update item') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" id="item_title" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('price', 'Price') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" id="item_price" name="price"
                                placeholder="<?= get_label('please_enter_price', 'Please enter price') ?>" />
                        </div>
                        @if (isset($units) && is_iterable($units))
                            <div class="col-md-6 mb-3">
                                <label for="nameBasic" class="form-label">
                                    <?= get_label('unit', 'Unit') ?>
                                </label>
                                <select class="form-select" id="item_unit_id" name="unit_id">
                                    <option value="">
                                        <?= get_label('please_select', 'Please select') ?>
                                    </option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('description', 'Description') ?>
                            </label>
                            <textarea class="form-control" id="item_description" name="description"
                                placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('update', 'Update') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/notes'))
    <div class="modal fade" id="create_note_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content form-submit-event" action="{{ route('notes.store') }}" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_note', 'Create note') ?>
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
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('color', 'Color') ?> <span class="asterisk">*</span>
                            </label>
                            <select class="form-select select-bg-label-success" name="color">
                                <option class="badge bg-label-success" value="success"
                                    {{ old('color') == 'success' ? 'selected' : '' }}>
                                    <?= get_label('green', 'Green') ?>
                                </option>
                                <option class="badge bg-label-warning" value="warning"
                                    {{ old('color') == 'warning' ? 'selected' : '' }}>
                                    <?= get_label('yellow', 'Yellow') ?>
                                </option>
                                <option class="badge bg-label-danger" value="danger"
                                    {{ old('color') == 'danger' ? 'selected' : '' }}>
                                    <?= get_label('red', 'Red') ?>
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                            <label class="form-label" for="status">
                                <?= get_label('status', 'Status') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group">
                                <select class="form-control statusDropdown" name="status_id">
                                        @foreach ($statuses as $status)
                                                <option value="{{ $status->id }}"
                                                    data-color="{{ $status->color }}"
                                                    {{ old('status') == $status->id ? 'selected' : '' }}>
                                                    {{ $status->title }} ({{ $status->color }})</option>
                                        @endforeach
                                </select>
                            </div>
                            @error('status_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?></label>
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit_btn">
                        <?= get_label('create', 'Create') ?></label>
                    </button>
                </div>
            </form>
        </div>
    </div>
@foreach ($notes as $note)
<div class="modal fade" id="edit_note_modal_{{ $note->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content form-submit-event" 
              action="{{ route('notes.update', $note->id) }}" 
              method="POST">
            @csrf

            <input type="hidden" name="id" value="{{ $note->id }}">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">
                    {{ get_label('update_note', 'Update note') }}
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
                <div class="row">
                    <div class="col mb-3">
                        <label class="form-label">
                            {{ get_label('color', 'Color') }} <span class="asterisk">*</span>
                        </label>
                        <select class="form-select select-bg-label-success" name="color">
                            @foreach(['success' => 'Green', 'warning' => 'Yellow', 'danger' => 'Red'] as $value => $label)
                            <option class="badge bg-label-{{ $value }}" 
                                    value="{{ $value }}"
                                    {{ $note->color == $value ? 'selected' : '' }}>
                                {{ get_label(strtolower($label), $label) }}
                            </option>
                            @endforeach
                        </select>
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
@endif
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('warning', 'Warning!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('delete_account_alert', 'Are you sure you want to delete your account?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <form id="formAccountDeactivation"
                    action="{{ route('profile.destroy', ['user' => getAuthenticatedUser()->id]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <?= get_label('yes', 'Yes') ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('warning', 'Warning!') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> '</button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('delete_alert', 'Are you sure you want to delete?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-danger" id="confirmDelete">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="confirmDeleteSelectedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('warning', 'Warning!') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> '</button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('delete_selected_alert', 'Are you sure you want to delete selected record(s)?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-danger" id="confirmDeleteSelections">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="duplicateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('warning', 'Warning!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('duplicate_warning', 'Are you sure you want to duplicate?') ?>
                </p>
                <div id="titleDiv" class="d-none"><label class="form-label">
                        <?= get_label('update_title', 'Update Title') ?>
                    </label><input type="text" class="form-control" id="updateTitle"
                        placeholder="<?= get_label('enter_title_duplicate', 'Enter Title For Item Being Duplicated') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="confirmDuplicate">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="timerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('time_tracker', 'Time tracker') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-body">
                    <div class="stopwatch">
                        <div class="stopwatch_time">
                            <input type="text" name="hour" id="hour" value="00"
                                class="form-control stopwatch_time_input" readonly>
                            <div class="stopwatch_time_lable">
                                <?= get_label('hours', 'Hours') ?>
                            </div>
                        </div>
                        <div class="stopwatch_time">
                            <input type="text" name="minute" id="minute" value="00"
                                class="form-control stopwatch_time_input" readonly>
                            <div class="stopwatch_time_lable">
                                <?= get_label('minutes', 'Minutes') ?>
                            </div>
                        </div>
                        <div class="stopwatch_time">
                            <input type="text" name="second" id="second" value="00"
                                class="form-control stopwatch_time_input" readonly>
                            <div class="stopwatch_time_lable">
                                <?= get_label('second', 'Second') ?>
                            </div>
                        </div>
                    </div>
                    <div class="selectgroup selectgroup-pills d-flex justify-content-around mt-3">
                        <label class="selectgroup-item">
                            <span class="selectgroup-button selectgroup-button-icon" data-bs-toggle="tooltip"
                                data-bs-placement="left" data-bs-original-title="<?= get_label('start', 'Start') ?>"
                                id="start" onclick="startTimer()"><i class="bx bx-play"></i></span>
                        </label>
                        <label class="selectgroup-item">
                            <span class="selectgroup-button selectgroup-button-icon" data-bs-toggle="tooltip"
                                data-bs-placement="left" data-bs-original-title="<?= get_label('stop', 'Stop') ?>"
                                id="end" onclick="stopTimer()"><i class="bx bx-stop"></i></span>
                        </label>
                        <label class="selectgroup-item">
                            <span class="selectgroup-button selectgroup-button-icon" data-bs-toggle="tooltip"
                                data-bs-placement="left" data-bs-original-title="<?= get_label('pause', 'Pause') ?>"
                                id="pause" onclick="pauseTimer()"><i class="bx bx-pause"></i></span>
                        </label>
                    </div>
                    <div class="form-group mb-0 mt-3">
                        <label class="label">
                            <?= get_label('message', 'Message') ?>:
                        </label>
                        <textarea class="form-control" id="time_tracker_message" placeholder="Please Enter Your Message" name="message"></textarea>
                    </div>
                </div>
                @if (getAuthenticatedUser()->can('manage_timesheet'))
                    <div class="modal-footer justify-content-center">
                        <a href="{{ route('time_tracker.index') }}" class="btn btn-primary"><i
                                class="bx bxs-time"></i>
                            <?= get_label('view_timesheet', 'View timesheet') ?>
                        </a>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="stopTimerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('warning', 'Warning!') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> '</button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('stop_timer_alert', 'Are you sure you want to stop the timer?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-danger" id="confirmStop">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
@if (Request::is($prefix . '/estimates-invoices/create') ||
        preg_match('/^estimates-invoices\/edit\/\d+$/', Request::path()))
    <div class="modal fade" id="edit-billing-address" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_billing_details', 'Update billing details') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        '</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('name', 'Name') ?> <span class="asterisk">*</span>
                            </label>
                            <input name="update_name" id="update_name" class="form-control"
                                placeholder="<?= get_label('please_enter_name', 'Please enter name') ?>"
                                value="{{ $estimate_invoice->name ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('contact', 'Contact') ?> <span class="asterisk">*</span>
                            </label>
                            <input name="update_contact" id="update_contact" class="form-control"
                                placeholder="<?= get_label('please_enter_contact', 'Please enter contact') ?>"
                                value="{{ $estimate_invoice->phone ?? '' }}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('address', 'Address') ?> <span class="asterisk">*</span>
                            </label>
                            <textarea class="form-control" placeholder="<?= get_label('please_enter_address', 'Please enter address') ?>"
                                name="update_address" id="update_address">{{ $estimate_invoice->address ?? '' }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('city', 'City') ?> <span class="asterisk">*</span>
                            </label>
                            <input name="update_city" id="update_city" class="form-control"
                                placeholder="<?= get_label('please_enter_city', 'Please enter city') ?>"
                                value="{{ $estimate_invoice->city ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('state', 'State') ?> <span class="asterisk">*</span>
                            </label>
                            <input name="update_contact" id="update_state" class="form-control"
                                placeholder="<?= get_label('please_enter_state', 'Please enter state') ?>"
                                value="{{ $estimate_invoice->city ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('country', 'Country') ?> <span class="asterisk">*</span>
                            </label>
                            <input name="update_country" id="update_country" class="form-control"
                                placeholder="<?= get_label('please_enter_country', 'Please enter country') ?>"
                                value="{{ $estimate_invoice->country ?? '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('zip_code', 'Zip code') ?> <span class="asterisk">*</span>
                            </label>
                            <input name="update_zip_code" id="update_zip_code" class="form-control"
                                placeholder="<?= get_label('please_enter_zip_code', 'Please enter zip code') ?>"
                                value="{{ $estimate_invoice->zip_code ?? '' }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="button" class="btn btn-primary" id="apply_billing_details">
                        <?= get_label('apply', 'Apply') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/expenses') || Request::is($prefix . '/expenses/*'))
    <div class="modal fade" id="create_expense_type_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content form-submit-event" action="{{ route('expenses-type.store') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_expense_type', 'Create expense type') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                        </div>
                        <div class="col mb-3">
                            <label for="description" class="form-label">
                                <?= get_label('description', 'Description') ?>
                            </label>
                            <textarea class="form-control" name="description"
                                placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('create', 'Create') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_expense_type_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content form-submit-event" action="{{ route('expenses-type.update') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" id="update_expense_type_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_expense_type', 'Update expense type') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" class="form-control" name="title" id="expense_type_title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                        </div>
                        <div class="col mb-3">
                            <label for="description" class="form-label">
                                <?= get_label('description', 'Description') ?>
                            </label>
                            <textarea class="form-control" name="description" id="expense_type_description"
                                placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('update', 'Update') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @if (Request::is($prefix . '/expenses'))
        <div class="modal fade" id="create_expense_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form class="modal-content form-submit-event" action="{{ route('expenses.store') }}"
                    method="POST">
                    <input type="hidden" name="dnr">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">
                            <?= get_label('create_expense', 'Create expense') ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">
                                    <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                                </label>
                                <input type="text" class="form-control" name="title"
                                    placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                            </div>
                            <div class="col mb-3">
                                <label class="form-label">
                                    <?= get_label('expense_type', 'Expense type') ?> <span class="asterisk">*</span>
                                </label>
                                <select class="form-select" name="expense_type_id">
                                    <option value="">
                                        <?= get_label('please_select', 'Please select') ?>
                                    </option>
                                    @isset($expense_types)
                                        @foreach ($expense_types as $type)
                                            <option value="{{ $type->id }}">{{ $type->title }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label class="form-label">
                                    <?= get_label('user', 'User') ?> <span class="asterisk">*</span>
                                </label>
                                <select class="form-select" name="user_id">
                                    <option value="">
                                        <?= get_label('please_select', 'Please select') ?>
                                    </option>
                                    @isset($users)
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->first_name . ' ' . $user->last_name }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="col mb-3">
                                <label class="form-label" for="">
                                    <?= get_label('amount', 'Amount') ?> <span class="asterisk">*</span>
                                </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                    <input class="form-control" type="number" min="0" name="amount"
                                        placeholder="<?= get_label('please_enter_amount', 'Please enter amount') ?>">
                                </div>
                                <span class="text-danger error-message"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">
                                    <?= get_label('expense_date', 'Expense date') ?> <span class="asterisk">*</span>
                                </label>
                                <input type="text" id="expense_date" name="expense_date" class="form-control"
                                    placeholder="" autocomplete="off">
                            </div>
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">
                                    <?= get_label('note', 'Note') ?>
                                </label>
                                <textarea class="form-control" name="note"
                                    placeholder="<?= get_label('please_enter_note_if_any', 'Please enter note if any') ?>"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <?= get_label('close', 'Close') ?>
                        </button>
                        <button type="submit" id="submit_btn" class="btn btn-primary">
                            <?= get_label('create', 'Create') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="modal fade" id="edit_expense_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <form class="modal-content form-submit-event" action="{{ route('expenses.update') }}"
                    method="POST">
                    <input type="hidden" name="dnr">
                    <input type="hidden" id="update_expense_id" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">
                            <?= get_label('update_expense', 'Update expense') ?>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">
                                    <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                                </label>
                                <input type="text" class="form-control" id="expense_title" name="title"
                                    placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" />
                            </div>
                            <div class="col mb-3">
                                <label class="form-label">
                                    <?= get_label('expense_type', 'Expense type') ?> <span class="asterisk">*</span>
                                </label>
                                <select class="form-select" id="expense_type_id" name="expense_type_id">
                                    <option value="">
                                        <?= get_label('please_select', 'Please select') ?>
                                    </option>
                                    @isset($expense_types)
                                        @foreach ($expense_types as $type)
                                            <option value="{{ $type->id }}">{{ $type->title }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label class="form-label">
                                    <?= get_label('user', 'User') ?> <span class="asterisk">*</span>
                                </label>
                                <select class="form-select" id="expense_user_id" name="user_id">
                                    <option value="">
                                        <?= get_label('please_select', 'Please select') ?>
                                    </option>
                                    @isset($users)
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">
                                                {{ $user->first_name . ' ' . $user->last_name }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="col mb-3">
                                <label class="form-label" for="">
                                    <?= get_label('amount', 'Amount') ?> <span class="asterisk">*</span>
                                </label>
                                <div class="input-group input-group-merge">
                                    <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                    <input class="form-control" type="number" min="0"
                                        id="expense_amount" name="amount"
                                        placeholder="<?= get_label('please_enter_amount', 'Please enter amount') ?>">
                                </div>
                                <span class="text-danger error-message"></span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">
                                    <?= get_label('expense_date', 'Expense date') ?> <span class="asterisk">*</span>
                                </label>
                                <input type="text" id="update_expense_date" name="expense_date"
                                    class="form-control" placeholder="" autocomplete="off">
                            </div>
                            <div class="col mb-3">
                                <label for="nameBasic" class="form-label">
                                    <?= get_label('note', 'Note') ?>
                                </label>
                                <textarea class="form-control" id="expense_note" name="note"
                                    placeholder="<?= get_label('please_enter_note_if_any', 'Please enter note if any') ?>"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <?= get_label('close', 'Close') ?>
                        </button>
                        <button type="submit" id="submit_btn" class="btn btn-primary">
                            <?= get_label('update', 'Update') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endif
@if (Request::is($prefix . '/payments'))
    <div class="modal fade" id="create_payment_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content form-submit-event" action="{{ route('payments.store') }}" method="POST">
                <input type="hidden" name="dnr">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_payment', 'Create payment') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">
                                <?= get_label('user', 'User') ?>
                            </label>
                            <select class="form-select" name="user_id" id="select_user">
                            </select>
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">
                                <?= get_label('invoice', 'Invoice') ?>
                            </label>
                            <select class="form-select" name="invoice_id" id="select_invoice">
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">
                                <?= get_label('payment_method', 'Payment method') ?>
                            </label>
                            <select class="form-select" name="payment_method_id" id="select_payment_method">
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                @isset($payment_methods)
                                    @foreach ($payment_methods as $pm)
                                        <option value="{{ $pm->id }}">{{ $pm->title }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="col mb-3">
                            <label class="form-label" for="">
                                <?= get_label('amount', 'Amount') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input class="form-control" type="number" min="0" name="amount"
                                    placeholder="<?= get_label('please_enter_amount', 'Please enter amount') ?>">
                            </div>
                            <span class="text-danger error-message"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('payment_date', 'Payment date') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="payment_date" name="payment_date" class="form-control"
                                placeholder="{{ get_label('please_select', 'Please Select') }}"
                                autocomplete="off">
                        </div>
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('note', 'Note') ?>
                            </label>
                            <textarea class="form-control" name="note"
                                placeholder="<?= get_label('please_enter_note_if_any', 'Please enter note if any') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('create', 'Create') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="edit_payment_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form class="modal-content form-submit-event" action="{{ route('payments.update') }}"
                method="POST">
                <input type="hidden" name="dnr">
                <input type="hidden" id="update_payment_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_payment', 'Update payment') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">
                                <?= get_label('user', 'User') ?>
                            </label>
                            <select class="form-select" name="user_id" id="payment_user_id">
                            </select>
                        </div>
                        <div class="col mb-3">
                            <label class="form-label">
                                <?= get_label('invoice', 'Invoice') ?>
                            </label>
                            <select class="form-select" name="invoice_id" id="payment_invoice_id">
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                @isset($invoices)
                                    @foreach ($invoices as $invoice)
                                        <option value="{{ $invoice->id }}">
                                            {{ get_label('invoice_id_prefix', 'INVC-') . $invoice->id }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label class="form-label">
                                <?= get_label('payment_method', 'Payment method') ?>
                            </label>
                            <select class="form-select" name="payment_method_id" id="payment_pm_id">
                                <option value="">
                                    <?= get_label('please_select', 'Please select') ?>
                                </option>
                                @isset($payment_methods)
                                    @foreach ($payment_methods as $pm)
                                        <option value="{{ $pm->id }}">{{ $pm->title }}</option>
                                    @endforeach
                                @endisset
                            </select>
                        </div>
                        <div class="col mb-3">
                            <label class="form-label" for="">
                                <?= get_label('amount', 'Amount') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input class="form-control" type="number" min="0" name="amount"
                                    id="payment_amount"
                                    placeholder="<?= get_label('please_enter_amount', 'Please enter amount') ?>">
                            </div>
                            <span class="text-danger error-message"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('payment_date', 'Payment date') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" name="payment_date" class="form-control"
                                id="update_payment_date" placeholder="" autocomplete="off">
                        </div>
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('note', 'Note') ?>
                            </label>
                            <textarea class="form-control" name="note" id="payment_note"
                                placeholder="<?= get_label('please_enter_note_if_any', 'Please enter note if any') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('update', 'Update') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
<div class="modal fade" id="mark_all_notifications_as_read_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('confirm', 'Confirm!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('mark_all_notifications_as_read_alert', 'Are you sure you want to mark all notifications as read?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="confirmMarkAllAsRead">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="update_notification_status_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('confirm', 'Confirm!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('update_notifications_status_alert', 'Are you sure you want to update notification status?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="confirmNotificationStatus">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="mark_all_announcements_as_read_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('confirm', 'Confirm!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('mark_all_announcements_as_read_alert', 'Are you sure you want to mark all announcements as read?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="confirmAnnouncementMarkAllAsRead">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="update_announcement_status_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('confirm', 'Confirm!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('update_announcement_status_alert', 'Are you sure you want to update announcement status?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="confirmAnnouncementStatus">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="restore_default_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('confirm', 'Confirm!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('confirm_restore_default_template', 'Are you sure you want to restore default template?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="confirmRestoreDefault">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="sms_instuction_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel1">Sms Gateway Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="modal-body">
                    <ul>
                        <li class="my-4">Read and follow instructions carefully while configuration sms gateway
                            setting </li>
                        <li class="my-4">Firstly open your sms gateway account . You can find api keys in your
                            account -> API keys & credentials -> create api key </li>
                        <li class="my-4">After create key you can see here Account sid and auth token </li>
                        <div class="simplelightbox-gallery">
                            <a href="{{ asset('storage/images/base_url_and_params.png') }}" target="_blank">
                                <img src="{{ asset('storage/images/base_url_and_params.png') }}" class="w-100">
                            </a>
                        </div>
                        <li class="my-4">For Base url Messaging -> Send an SMS</li>
                        <div class="simplelightbox-gallery">
                            <a href="{{ asset('storage/images/api_key_and_token.png') }}" target="_blank">
                                <img src="{{ asset('storage/images/api_key_and_token.png') }}" class="w-100">
                            </a>
                        </div>
                        <li class="my-4">check this for admin panel settings</li>
                        <div class="simplelightbox-gallery">
                            <a href="{{ asset('storage/images/sms_gateway_1.png') }}" target="_blank">
                                <img src="{{ asset('storage/images/sms_gateway_1.png') }}" class="w-100">
                            </a>
                        </div>
                        <div class="simplelightbox-gallery">
                            <a href="{{ asset('storage/images/sms_gateway_2.png') }}" target="_blank">
                                <img src="{{ asset('storage/images/sms_gateway_2.png') }}" class="w-100">
                            </a>
                        </div>
                        <li class="my-4"><b>Make sure you entered valid data as per instructions before proceed</b>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
            </div>
        </div>
    </div>
</div>
@if (Request::is($prefix . '/projects') ||
        Request::is($prefix . '/projects/*') ||
        Request::is($prefix . '/tasks') ||
        Request::is($prefix . '/tasks/*') ||
        Request::is($prefix . '/priority/manage'))
    <div class="modal fade" id="create_priority_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content form-submit-event" action="{{ route('priority.store') }}" method="POST">
                @if (Request::is('priority/manage'))
                    <input type="hidden" name="dnr">
                @endif
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_priority', 'Create Priority') ?>
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
                                <?= get_label('color', 'Color') ?> <span class="asterisk">*</span>
                            </label>
                            <select class="form-select" id="color" name="color">
                                <option class="badge bg-label-primary" value="primary"
                                    {{ old('color') == 'primary' ? 'selected' : '' }}>
                                    <?= get_label('primary', 'Primary') ?>
                                </option>
                                <option class="badge bg-label-secondary" value="secondary"
                                    {{ old('color') == 'secondary' ? 'selected' : '' }}>
                                    <?= get_label('secondary', 'Secondary') ?>
                                </option>
                                <option class="badge bg-label-success" value="success"
                                    {{ old('color') == 'success' ? 'selected' : '' }}>
                                    <?= get_label('success', 'Success') ?>
                                </option>
                                <option class="badge bg-label-danger" value="danger"
                                    {{ old('color') == 'danger' ? 'selected' : '' }}>
                                    <?= get_label('danger', 'Danger') ?>
                                </option>
                                <option class="badge bg-label-warning" value="warning"
                                    {{ old('color') == 'warning' ? 'selected' : '' }}>
                                    <?= get_label('warning', 'Warning') ?>
                                </option>
                                <option class="badge bg-label-info" value="info"
                                    {{ old('color') == 'info' ? 'selected' : '' }}>
                                    <?= get_label('info', 'Info') ?>
                                </option>
                                <option class="badge bg-label-dark" value="dark"
                                    {{ old('color') == 'dark' ? 'selected' : '' }}>
                                    <?= get_label('dark', 'Dark') ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?></label>
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit_btn">
                        <?= get_label('create', 'Create') ?></label>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/priority/manage'))
    <div class="modal fade" id="edit_priority_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form action="{{ route('priority.update') }}" class="modal-content form-submit-event"
                method="POST">
                <input type="hidden" name="id" id="priority_id">
                <input type="hidden" name="dnr">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_priority', 'Update Priority') ?>
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
                            <input type="text" id="priority_title" class="form-control" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>" required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <label for="nameBasic" class="form-label">
                                <?= get_label('color', 'Color') ?> <span class="asterisk">*</span>
                            </label>
                            <select class="form-select" id="priority_color" name="color" required>
                                <option class="badge bg-label-primary" value="primary">
                                    <?= get_label('primary', 'Primary') ?>
                                </option>
                                <option class="badge bg-label-secondary" value="secondary">
                                    <?= get_label('secondary', 'Secondary') ?>
                                </option>
                                <option class="badge bg-label-success" value="success">
                                    <?= get_label('success', 'Success') ?>
                                </option>
                                <option class="badge bg-label-danger" value="danger">
                                    <?= get_label('danger', 'Danger') ?>
                                </option>
                                <option class="badge bg-label-warning" value="warning">
                                    <?= get_label('warning', 'Warning') ?>
                                </option>
                                <option class="badge bg-label-info" value="info">
                                    <?= get_label('info', 'Info') ?>
                                </option>
                                <option class="badge bg-label-dark" value="dark">
                                    <?= get_label('dark', 'Dark') ?>
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?></label>
                    </button>
                    <button type="submit" class="btn btn-primary" id="submit_btn">
                        <?= get_label('update', 'Update') ?></label>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/projects') ||
        Request::is($prefix . '/home') ||
        Request::is($prefix . '/projects/list') ||
        Request::is($prefix . '/users/profile/*') ||
        Request::is($prefix . '/clients/profile/*') ||
        Request::is($prefix . '/projects/kanban-view'))
    <div class="modal fade" id="create_project_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('projects.store') }}" class="form-submit-event modal-content" method="POST">
                @if (!Request::is($prefix . '/projects') && !Request::is($prefix . '/projects/kanban-view'))
                    <input type="hidden" name="dnr">
                    <input type="hidden" name="table" value="projects_table">
                @endif
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_project', 'Create Project') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input class="form-control" type="text" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>"
                                value="{{ old('title') }}">
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="status">
                                <?= get_label('status', 'Status') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group">
                                <select class="form-control statusDropdown" name="status_id">
                                    @isset($statuses)
                                        @foreach ($statuses as $status)
                                            @if (canSetStatus($status))
                                                <option value="{{ $status->id }}"
                                                    data-color="{{ $status->color }}"
                                                    {{ old('status') == $status->id ? 'selected' : '' }}>
                                                    {{ $status->title }} ({{ $status->color }})</option>
                                            @endif
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="mt-2">
                                <a href="javascript:void(0);" class="openCreateStatusModal"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title=" <?= get_label('create_status', 'Create status') ?>"><i
                                            class="bx bx-plus"></i></button></a>
                                <a href="{{ route('status.index') }}" target="_blank"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('manage_statuses', 'Manage statuses') ?>"><i
                                            class="bx bx-list-ul"></i></button></a>
                            </div>
                            @error('status_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="start_date">
                                <?= get_label('starts_at', 'Starts at') ?>
                                <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="start_date" name="start_date" class="form-control"
                                value="" autocomplete="off">
                            @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="due_date">
                                <?= get_label('ends_at', 'Ends at') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="end_date" name="end_date" class="form-control"
                                value="" autocomplete="off">
                            @error('due_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="">
                                <?= get_label('task_accessibility', 'Task Accessibility') ?>
                                <i class='bx bx-info-circle text-primary' data-bs-toggle="tooltip"
                                    data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true"
                                    title=""
                                    data-bs-original-title="<b>{{ get_label('assigned_users', 'Assigned Users') }}:</b> {{ get_label('assigned_users_info', 'You Will Need to Manually Select Task Users When Creating Tasks Under This Project.') }} <br><b>{{ get_label('project_users', 'Project Users') }}:</b> {{ get_label('project_users_info', 'When Creating Tasks Under This Project, the Task Users Selection Will Be Automatically Filled With Project Users.') }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top"></i>
                            </label>
                            <div class="input-group">
                                <select class="form-select" name="task_accessibility">
                                    <option value="assigned_users">
                                        <?= get_label('assigned_users', 'Assigned Users') ?>
                                    </option>
                                    <option value="project_users">
                                        <?= get_label('project_users', 'Project Users') ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <!-- <div class="col-md-6 mb-3">
                            <label class="form-label" for="tasksTimeEntriesSwitch">
                                <?= get_label('tasks_time_entries', 'Tasks Time Entries') ?>
                                <i class="bx bx-info-circle text-primary" data-bs-toggle="tooltip"
                                    data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true"
                                    title=""
                                    data-bs-original-title="<b>{{ get_label('tasks_time_entries', 'Tasks Time Entries') }}:</b> {{ get_label('tasks_time_entries_info', 'To use Time Entries in tasks, you need to enable this option. It allows time tracking and entry management for tasks under this project.') }}">
                                </i>
                            </label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="enable_tasks_time_entries" value="0">
                                <input class="form-check-input" type="checkbox" name="enable_tasks_time_entries"
                                    id="tasks_time_entries" value="1"
                                    {{ old('tasks_time_entries') ? 'checked' : '' }}>
                                <label class="form-check-label" for="tasksTimeEntriesSwitch">
                                    {{ get_label('enable', 'Enable') }}
                                </label>
                            </div>
                        </div> -->
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label" for="user_id">
                                <?= get_label('select_users', 'Select users') ?>
                            </label>
                            <div class="input-group">
                                <select class="form-control js-example-basic-multiple" id="project_users"
                                    name="user_id[]" multiple="multiple"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    @isset($toSelectProjectUsers)
                                        @foreach ($toSelectProjectUsers as $user)
                                            <?php $selected = $user->id == getAuthenticatedUser()->id ? 'selected' : ''; ?>
                                            <option value="{{ $user->id }}"
                                                {{ collect(old('user_id'))->contains($user->id) ? 'selected' : '' }}
                                                <?= $selected ?>>{{ $user->first_name }} {{ $user->last_name }}
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="row">
                        <div class="mb-3">
                            <label class="form-label" for="client_id">
                                <?= get_label('select_clients', 'Select clients') ?>
                            </label>
                            <div class="input-group">
                                <select class="form-control js-example-basic-multiple" name="client_id[]"
                                    multiple="multiple" id="project_clients"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    @isset($toSelectProjectClients)
                                        @foreach ($toSelectProjectClients as $client)
                                            <?php $selected = $client->id == getAuthenticatedUser()->id && $auth_user->hasRole('client') ? 'selected' : ''; ?>
                                            <option value="{{ $client->id }}"
                                                {{ collect(old('client_id'))->contains($client->id) ? 'selected' : '' }}
                                                <?= $selected ?>>{{ $client->first_name }} {{ $client->last_name }}
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        </div>
                    </div> -->

                    <div class="row">
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <?= get_label('description', 'Description') ?>
                            </label>
                            <textarea class="form-control description" rows="5" name="description"
                                placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label">
                                <?= get_label('note', 'Note') ?>
                            </label>
                            <textarea class="form-control" name="note" rows="3"
                                placeholder="<?= get_label('optional_note', 'Optional Note') ?>"></textarea>
                        </div>
                    </div>
                    <div class="alert alert-primary" role="alert">
                        <?= get_label('you_will_be_project_participant_automatically', 'You will be project participant automatically.') ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('create', 'Create') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/projects') ||
        Request::is($prefix . '/projects/favorite') ||
        Request::is($prefix . '/projects/list') ||
        Request::is($prefix . '/projects/information/*') ||
        Request::is($prefix . '/home') ||
        Request::is($prefix . '/users/profile/*') ||
        Request::is($prefix . '/clients/profile/*') ||
        Request::is($prefix . '/users') ||
        Request::is($prefix . '/clients') ||
        Request::is($prefix . '/projects/kanban-view'))
    <div class="modal fade" id="edit_project_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('projects.update') }}" class="form-submit-event modal-content"
                method="POST">
                @method('PUT')
                <input type="hidden" name="id" id="project_id">
                @if (
                    !Request::is($prefix . '/projects') &&
                        !Request::is($prefix . '/projects/information/*') &&
                        !Request::is($prefix . '/projects/kanban-view'))
                    <input type="hidden" name="dnr">
                    <input type="hidden" name="table" value="projects_table">
                @endif
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_project', 'Update Project') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input class="form-control" type="text" name="title" id="project_title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>"
                                value="{{ old('title') }}">
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="status">
                                <?= get_label('status', 'Status') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group">
                                <select class="form-control statusDropdown" name="status_id"
                                    id="project_status_id">
                                    @isset($statuses)
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}" data-color="{{ $status->color }}"
                                                {{ old('status') == $status->id ? 'selected' : '' }}>
                                                {{ $status->title }} ({{ $status->color }})</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            @error('status_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                            <div class="mt-2">
                                <a href="javascript:void(0);" class="openCreateStatusModal"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title=" <?= get_label('create_status', 'Create status') ?>"><i
                                            class="bx bx-plus"></i></button></a>
                                <a href="{{ route('status.index') }}" target="_blank"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('manage_statuses', 'Manage statuses') ?>"><i
                                            class="bx bx-list-ul"></i></button></a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <?= get_label('priority', 'Priority') ?>
                            </label>
                            <div class="input-group">
                                <select class="form-select priorityDropdown" name="priority_id"
                                    id="project_priority_id"
                                    data-placeholder="<?= get_label('please_select', 'Please select') ?>">
                                    <option></option>
                                    @isset($priorities)
                                        @foreach ($priorities as $priority)
                                            <option value="{{ $priority->id }}" data-color="{{ $priority->color }}">
                                                {{ $priority->title }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="mt-2">
                                <a href="javascript:void(0);" class="openCreatePriorityModal"><button
                                        type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title=" <?= get_label('create_priority', 'Create Priority') ?>"><i
                                            class="bx bx-plus"></i></button></a>
                                <a href="{{ route('priority.manage') }}" target="_blank"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('manage_priorities', 'Manage Priorities') ?>"><i
                                            class="bx bx-list-ul"></i></button></a>
                            </div>
                            @error('priority_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="budget" class="form-label">
                                <?= get_label('budget', 'Budget') ?>
                            </label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text">{{ $general_settings['currency_symbol'] }}</span>
                                <input class="form-control min-0" min="0" type="number"
                                    id="project_budget" name="budget"
                                    placeholder="<?= get_label('please_enter_budget', 'Please enter budget') ?>"
                                    value="{{ old('budget') }}">
                            </div>
                            <span class="text-danger error-message"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="start_date">
                                <?= get_label('starts_at', 'Starts at') ?>
                                <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="update_start_date" name="start_date" class="form-control"
                                value="" autocomplete="off">
                            @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="due_date">
                                <?= get_label('ends_at', 'Ends at') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="update_end_date" name="end_date" class="form-control"
                                value="" autocomplete="off">
                            @error('due_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="">
                                <?= get_label('task_accessibility', 'Task Accessibility') ?>
                                <i class='bx bx-info-circle text-primary' data-bs-toggle="tooltip"
                                    data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true"
                                    title=""
                                    data-bs-original-title="<b>{{ get_label('assigned_users', 'Assigned Users') }}:</b> {{ get_label('assigned_users_info', 'You Will Need to Manually Select Task Users When Creating Tasks Under This Project.') }}<br><b>{{ get_label('project_users', 'Project Users') }}:</b> {{ get_label('project_users_info', 'When Creating Tasks Under This Project, the Task Users Selection Will Be Automatically Filled With Project Users.') }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top"></i>
                            </label>
                            <div class="input-group">
                                <select class="form-select" name="task_accessibility" id="task_accessibility">
                                    <option value="assigned_users">
                                        <?= get_label('assigned_users', 'Assigned Users') ?>
                                    </option>
                                    <option value="project_users">
                                        <?= get_label('project_users', 'Project Users') ?>
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tasksTimeEntriesSwitch">
                                <?= get_label('tasks_time_entries', 'Tasks Time Entries') ?>
                                <i class="bx bx-info-circle text-primary" data-bs-toggle="tooltip"
                                    data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true"
                                    title=""
                                    data-bs-original-title="<b>{{ get_label('tasks_time_entries', 'Tasks Time Entries') }}:</b> {{ get_label('tasks_time_entries_info', 'To use Time Entries in tasks, you need to enable this option. It allows time tracking and entry management for tasks under this project.') }}">
                                </i>
                            </label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="enable_tasks_time_entries" value="0">
                                <input class="form-check-input" type="checkbox" name="enable_tasks_time_entries"
                                    id="edit_tasks_time_entries" value="1">
                                <label class="form-check-label" for="tasksTimeEntriesSwitch">
                                    <?= get_label('enable', 'Enable') ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label" for="user_id">
                                <?= get_label('select_users', 'Select users') ?>
                            </label>
                            <div class="input-group">
                                <select class="form-control js-example-basic-multiple" name="user_id[]"
                                    multiple="multiple"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    @isset($toSelectProjectUsers)
                                        @foreach ($toSelectProjectUsers as $user)
                                            <?php $selected = $user->id == getAuthenticatedUser()->id ? 'selected' : ''; ?>
                                            <option value="{{ $user->id }}"
                                                {{ collect(old('user_id'))->contains($user->id) ? 'selected' : '' }}
                                                <?= $selected ?>>{{ $user->first_name }} {{ $user->last_name }}
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label" for="client_id">
                                <?= get_label('select_clients', 'Select clients') ?>
                            </label>
                            <div class="input-group">
                                <select class="form-control js-example-basic-multiple" name="client_id[]"
                                    multiple="multiple"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    @isset($toSelectProjectClients)
                                        @foreach ($toSelectProjectClients as $client)
                                            <?php $selected = $client->id == getAuthenticatedUser()->id && $auth_user->hasRole('client') ? 'selected' : ''; ?>
                                            <option value="{{ $client->id }}"
                                                {{ collect(old('client_id'))->contains($client->id) ? 'selected' : '' }}
                                                <?= $selected ?>>{{ $client->first_name }} {{ $client->last_name }}
                                            </option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label" for="">
                                <?= get_label('select_tags', 'Select tags') ?>
                            </label>
                            <div class="input-group">
                                <select class="form-control tagsDropdown" name="tag_ids[]" multiple="multiple"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    @isset($tags)
                                        @foreach ($tags as $tag)
                                            <option value="{{ $tag->id }}" data-color="{{ $tag->color }}"
                                                {{ collect(old('tag_ids'))->contains($tag->id) ? 'selected' : '' }}>
                                                {{ $tag->title }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="mt-2">
                                <a href="javascript:void(0);" class="openCreateTagModal"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title=" <?= get_label('create_tag', 'Create tag') ?>"><i
                                            class="bx bx-plus"></i></button></a>
                                <a href="{{ route('tags.index') }}"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('manage_tags', 'Manage tags') ?>"><i
                                            class="bx bx-list-ul"></i></button></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <?= get_label('description', 'Description') ?>
                            </label>
                            <textarea class="form-control description" rows="5" name="description" id="project_description"
                                placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label">
                                <?= get_label('note', 'Note') ?>
                            </label>
                            <textarea class="form-control" name="note" id="projectNote" rows="3"
                                placeholder="<?= get_label('optional_note', 'Optional Note') ?>"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('update', 'Update') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
<div class="modal fade" id="set_default_view_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('confirm', 'Confirm!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('set_default_view_alert', 'Are You Want to Set as Default View?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="confirm">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>
@if (Request::is($prefix . '/tasks') ||
        Request::is($prefix . '/home') ||
        Request::is($prefix . '/tasks/draggable') ||
        Request::is($prefix . '/projects/information/*') ||
        Request::is($prefix . '/projects/tasks/draggable/*') ||
        Request::is($prefix . '/projects/tasks/list/*') ||
        Request::is($prefix . '/users/profile/*') ||
        Request::is($prefix . '/clients/profile/*') ||
        Request::is($prefix . '/tasks/calendar-view') ||
        Request::is($prefix . '/tasks/group-by-task-list'))
    <div class="modal fade" id="create_task_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('tasks.store') }}" class="form-submit-event modal-content" method="POST">
                @if (
                    !Request::is($prefix . '/projects/tasks/draggable/*') &&
                        !Request::is($prefix . '/tasks/draggable') &&
                        !Request::is($prefix . '/projects/information/*') &&
                        !Request::is($prefix . '/tasks/calendar-view') &&
                        !Request::is($prefix . '/tasks/group-by-task-list'))
                    <input type="hidden" name="dnr">
                    <input type="hidden" name="table" value="task_table">
                @endif
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('create_task', 'Create Task') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="title" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input class="form-control" type="text" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>"
                                value="{{ old('title') }}">
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="status">
                                <?= get_label('status', 'Status') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group">
                                <select class="form-select statusDropdown" name="status_id">
                                    @isset($statuses)
                                        @foreach ($statuses as $status)
                                            @if (canSetStatus($status))
                                                <option value="{{ $status->id }}"
                                                    data-color="{{ $status->color }}"
                                                    {{ old('status') == $status->id ? 'selected' : '' }}>
                                                    {{ $status->title }} ({{ $status->color }})</option>
                                            @endif
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="mt-2">
                                <a href="javascript:void(0);" class="openCreateStatusModal"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title=" <?= get_label('create_status', 'Create status') ?>"><i
                                            class="bx bx-plus"></i></button></a>
                                <a href="{{ route('status.index') }}" target="_blank"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('manage_statuses', 'Manage statuses') ?>"><i
                                            class="bx bx-list-ul"></i></button></a>
                            </div>
                            @error('status_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <?= get_label('priority', 'Priority') ?>
                            </label>
                            <div class="input-group">
                                <select class="form-select priorityDropdown" name="priority_id"
                                    data-placeholder="<?= get_label('please_select', 'Please select') ?>">
                                    <option></option>
                                    @isset($priorities)
                                        @foreach ($priorities as $priority)
                                            <option value="{{ $priority->id }}" data-color="{{ $priority->color }}">
                                                {{ $priority->title }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="mt-2">
                                <a href="javascript:void(0);" class="openCreatePriorityModal"><button
                                        type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title=" <?= get_label('create_priority', 'Create Priority') ?>"><i
                                            class="bx bx-plus"></i></button></a>
                                <a href="{{ route('priority.manage') }}" target="_blank"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('manage_priorities', 'Manage Priorities') ?>"><i
                                            class="bx bx-list-ul"></i></button></a>
                            </div>
                            @error('priority_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="start_date">
                                <?= get_label('starts_at', 'Starts at') ?>
                                <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="task_start_date" name="start_date" class="form-control"
                                value="">
                            @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="due_date">
                                <?= get_label('ends_at', 'Ends at') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="task_end_date" name="due_date" class="form-control"
                                value="">
                            @error('due_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <?php $project_id = 0;
                                                                                            if (!isset($project->id)) {
                                                                                            ?>
                        <div class="mb-3">
                            <label class="form-label" for="user_id">
                                <?= get_label('select_project', 'Select project') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group">
                                <select class="form-control selectTaskProject" name="project"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    <option value=""></option>
                                    @isset($projects)
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}"
                                                {{ old('project') == $project->id ? 'selected' : '' }}>
                                                {{ $project->title }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                        </div>
                        <?php } else {
                                                                                                $project_id = $project->id ?>
                        <input type="hidden" name="project" value="{{ $project_id }}">
                        <div class="mb-3">
                            <label for="project_title" class="form-label">
                                <?= get_label('project', 'Project') ?>
                                <span class="asterisk">*</span>
                            </label>
                            <input class="form-control" type="text" value="{{ $project->title }}" readonly>
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <?php } ?>
                    </div>
                    <div class="row" id="selectTaskUsers">
                        <div class="mb-3">
                            <label class="form-label" for="user_id">
                                <?= get_label('select_users', 'Select users') ?> <span
                                    id="users_associated_with_project"></span>
                                <?php if (!empty($project_id)) { ?>
                                (
                                <?= get_label('users_associated_with_project', 'Users associated with project') ?>
                                <b>{{ $project->title }}</b>)
                                <?php } ?>
                            </label>
                            <div class="input-group">
                                <select class="form-control js-example-basic-multiple" name="users_id[]"
                                    multiple="multiple"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                    <?php if (isset($project_id) && !empty($project_id)) { ?>
                                    @foreach ($toSelectTaskUsers as $user)
                                        <?php
                                        $selected = '';
                                        // Check if task_accessibility is 'project_users' or if the user is the authenticated user
                                        if ($project->task_accessibility == 'project_users' || $user->id == getAuthenticatedUser()->id) {
                                            $selected = 'selected';
                                        }
                                        ?>
                                        <option value="{{ $user->id }}"
                                            {{ collect(old('user_id'))->contains($user->id) ? 'selected' : '' }}
                                            <?= $selected ?>>{{ $user->first_name }} {{ $user->last_name }}
                                        </option>
                                    @endforeach
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label for="task_list" class="form-label">
                                {{ get_label('task_list', 'Task List') }}
                            </label>
                            <select class="form-select" name="task_list_id" id="task_list"
                                data-placeholder="{{ get_label('select_task_list', 'Select Task List') }} ">
                                <option value="">{{ get_label('select_task_list', 'Select Task List') }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <?= get_label('description', 'Description') ?>
                            </label>
                            <textarea class="form-control description" rows="5" name="description"
                                placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"
                                for="billing_type">{{ get_label('billing_type', 'Billing Type') }}</label>
                            <select class="form-select" name="billing_type" id="billing_type">
                                <option value="none">{{ get_label('none', 'None') }}</option>
                                <option value="billable">{{ get_label('billable', 'Billable') }}</option>
                                <option value="non-billable">{{ get_label('non_billable', 'Non Billable') }}
                                </option>
                            </select>
                            @error('billing_type')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"
                                for="completion_percentage">{{ get_label('completion_percentage', 'Completion Percentage (%)') }}</label>
                            <select class="form-select" name="completion_percentage" id="completion_percentage">
                                @foreach (range(0, 100, 10) as $percentage)
                                    <option value="{{ $percentage }}">{{ $percentage }}%</option>
                                @endforeach
                            </select>
                            @error('completion_percentage')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label">{{ get_label('note', 'Note') }}</label>
                            <textarea class="form-control" name="note" rows="3"
                                placeholder="{{ get_label('optional_note', 'Optional Note') }} "></textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="reminder-switch"
                                    class="form-label">{{ get_label('enable_reminder', 'Enable Reminder') }}</label>
                                <i class="bx bx-info-circle text-primary"
                                    data-bs-toggle="tooltip"data-bs-offset="0,4" data-bs-placement="top"
                                    data-bs-html="true"title=""
                                    data-bs-original-title="<b>{{ get_label('task_reminder', 'Task Reminder') }}:</b> {{ get_label('task_reminder_info', 'Enable this option to set reminders for tasks. You can configure reminder frequencies (daily, weekly, or monthly), specific times, and customize alerts to ensure you stay on track with task deadlines.') }}"></i>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="reminder-switch"
                                        name="enable_reminder">
                                    <label class="form-check-label"
                                        for="reminder-switch">{{ get_label('enable_task_reminder', 'Enable Task Reminder') }}</label>
                                </div>
                            </div>
                            <div id="reminder-settings" class="d-none">
                                <!-- Frequency Type -->
                                <div class="mb-3">
                                    <label for="frequency-type"
                                        class="form-label">{{ get_label('frequency_type', 'Frequency Type') }}</label>
                                    <select class="form-select" id="frequency-type" name="frequency_type"
                                        required>
                                        <option value="daily">{{ get_label('daily', 'Daily') }}</option>
                                        <option value="weekly">{{ get_label('weekly', 'Weekly') }}</option>
                                        <option value="monthly">{{ get_label('monthly', 'Monthly') }}</option>
                                    </select>
                                </div>
                                <!-- Day of Week (Weekly Only) -->
                                <div class="d-none mb-3" id="day-of-week-group">
                                    <label
                                        for="day-of-week"class="form-label">{{ get_label('day_of_the_week', 'Day of the Week') }}</label>
                                    <select class="form-select" id="day-of-week" name="day_of_week">
                                        <option value="">{{ get_label('any_day', 'Any Day') }}</option>
                                        <option value="1">{{ get_label('monday', 'Monday') }}</option>
                                        <option value="2">{{ get_label('tuesday', 'Tuesday') }}</option>
                                        <option value="3">{{ get_label('wednesday', 'Wednesday') }}</option>
                                        <option value="4">{{ get_label('thursday', 'Thursday') }}</option>
                                        <option value="5">{{ get_label('friday', 'Friday') }}</option>
                                        <option value="6">{{ get_label('saturday', 'Saturday') }}</option>
                                        <option value="7">{{ get_label('sunday', 'Sunday') }}</option>
                                    </select>
                                </div>
                                <!-- Day of Month (Monthly Only) -->
                                <div class="d-none mb-3" id="day-of-month-group">
                                    <label for="day-of-month"
                                        class="form-label">{{ get_label('day_of_the_month', 'Day of the Month') }}</label>
                                    <select class="form-select" id="day-of-month" name="day_of_month">
                                        <option value="">{{ get_label('any_day', 'Any Day') }}</option>
                                        @foreach (range(1, 31) as $day)
                                            <option value="{{ $day }}">{{ $day }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Time of Day -->
                                <div class="mb-3">
                                    <label for="time-of-day"
                                        class="form-label">{{ get_label('time_of_day', 'Time of Day') }}</label>
                                    <input type="time" class="form-control" id="time-of-day"
                                        name="time_of_day">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="recurring-task-switch" class="form-label">
                                    {{ get_label('enable_recurring_task', 'Enable Recurring Task') }}
                                </label>
                                <i class="bx bx-info-circle text-primary" data-bs-toggle="tooltip"
                                    data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true"
                                    title=""
                                    data-bs-original-title="<b>{{ get_label('recurring_tasks', 'Recurring Tasks') }}:</b> {{ get_label('recurring_tasks_info', 'This option enables the creation of recurring tasks. You can set the frequency (daily, weekly, monthly, yearly), specific days, and manage the recurrence schedule efficiently.') }}">
                                </i>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="recurring-task-switch"
                                        name="enable_recurring_task">
                                    <label class="form-check-label" for="recurring-task-switch">
                                        {{ get_label('enable_recurring_task', 'Enable Recurring Task') }}
                                    </label>
                                </div>
                            </div>
                            <div id="recurring-task-settings" class="d-none">
                                <!-- Frequency Type -->
                                <div class="mb-3">
                                    <label for="recurrence-frequency" class="form-label">
                                        {{ get_label('recurrence_frequency', 'Recurrence Frequency') }}
                                    </label>
                                    <select class="form-select" id="recurrence-frequency"
                                        name="recurrence_frequency" required>
                                        <option value="daily">{{ get_label('daily', 'Daily') }}</option>
                                        <option value="weekly">{{ get_label('weekly', 'Weekly') }}</option>
                                        <option value="monthly">{{ get_label('monthly', 'Monthly') }}</option>
                                        <option value="yearly">{{ get_label('yearly', 'Yearly') }}</option>
                                    </select>
                                </div>
                                <!-- Day of Week (Weekly Only) -->
                                <div class="d-none mb-3" id="recurrence-day-of-week-group">
                                    <label for="recurrence-day-of-week" class="form-label">
                                        {{ get_label('day_of_the_week', 'Day of the Week') }}
                                    </label>
                                    <select class="form-select" id="recurrence-day-of-week"
                                        name="recurrence_day_of_week">
                                        <option value="">{{ get_label('any_day', 'Any Day') }}</option>
                                        <option value="1">{{ get_label('monday', 'Monday') }}</option>
                                        <option value="2">{{ get_label('tuesday', 'Tuesday') }}</option>
                                        <option value="3">{{ get_label('wednesday', 'Wednesday') }}</option>
                                        <option value="4">{{ get_label('thursday', 'Thursday') }}</option>
                                        <option value="5">{{ get_label('friday', 'Friday') }}</option>
                                        <option value="6">{{ get_label('saturday', 'Saturday') }}</option>
                                        <option value="7">{{ get_label('sunday', 'Sunday') }}</option>
                                    </select>
                                </div>
                                <!-- Day of Month (Monthly and Yearly Only) -->
                                <div class="d-none mb-3" id="recurrence-day-of-month-group">
                                    <label for="recurrence-day-of-month" class="form-label">
                                        {{ get_label('day_of_the_month', 'Day of the Month') }}
                                    </label>
                                    <select class="form-select" id="recurrence-day-of-month"
                                        name="recurrence_day_of_month">
                                        <option value="">{{ get_label('any_day', 'Any Day') }}</option>
                                        @foreach (range(1, 31) as $day)
                                            <option value="{{ $day }}">{{ $day }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Month of Year (Yearly Only) -->
                                <div class="d-none mb-3" id="recurrence-month-of-year-group">
                                    <label for="recurrence-month-of-year" class="form-label">
                                        {{ get_label('month_of_the_year', 'Month of the Year') }}
                                    </label>
                                    <select class="form-select" id="recurrence-month-of-year"
                                        name="recurrence_month_of_year">
                                        <option value="">{{ get_label('any_month', 'Any Month') }}</option>
                                        @foreach (range(1, 12) as $month)
                                            <option value="{{ $month }}">
                                                {{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Starts From -->
                                <div class="mb-3">
                                    <label for="recurrence-starts-from" class="form-label">
                                        {{ get_label('starts_from', 'Starts From') }}
                                    </label>
                                    <input type="date" class="form-control" id="recurrence-starts-from"
                                        name="recurrence_starts_from">
                                </div>
                                <!-- Number of Occurrences -->
                                <div class="mb-3">
                                    <label for="recurrence-occurrences" class="form-label">
                                        {{ get_label('number_of_occurrences', 'Number of Occurrences') }}
                                    </label>
                                    <input type="number" class="form-control" id="recurrence-occurrences"
                                        name="recurrence_occurrences" min="1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ get_label('close', 'Close') }}
                    </button>
                    <button type="submit" id="submit_btn"
                        class="btn btn-primary">{{ get_label('create', 'Create') }}</button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/tasks') ||
        Request::is($prefix . '/tasks/draggable') ||
        Request::is($prefix . '/projects/tasks/draggable/*') ||
        Request::is($prefix . '/projects/tasks/list/*') ||
        Request::is($prefix . '/tasks/information/*') ||
        Request::is($prefix . '/home') ||
        Request::is($prefix . '/users/profile/*') ||
        Request::is($prefix . '/clients/profile/*') ||
        Request::is($prefix . '/projects/information/*') ||
        Request::is($prefix . '/users') ||
        Request::is($prefix . '/clients')||
        Request::is($prefix . '/tasks/group-by-task-list'))
    <div class="modal fade" id="edit_task_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <form action="{{ route('tasks.update') }}" class="form-submit-event modal-content" method="POST">
                @method('PUT')
                <input type="hidden" name="id" id="id">
                @if (
                    !Request::is($prefix . '/projects/tasks/draggable/*') &&
                        !Request::is($prefix . '/tasks/draggable') &&
                        !Request::is($prefix . '/tasks/information/*')
                        && !Request::is($prefix . '/tasks/group-by-task-list'))
                    <input type="hidden" name="dnr">
                    <input type="hidden" name="table" value="task_table">
                @endif
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1">
                        <?= get_label('update_task', 'Update Task') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="title" class="form-label">
                                <?= get_label('title', 'Title') ?> <span class="asterisk">*</span>
                            </label>
                            <input class="form-control" type="text" id="title" name="title"
                                placeholder="<?= get_label('please_enter_title', 'Please enter title') ?>"
                                value="{{ old('title') }}">
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="status">
                                <?= get_label('status', 'Status') ?> <span class="asterisk">*</span>
                            </label>
                            <div class="input-group">
                                <select class="form-select statusDropdown" name="status_id" id="task_status_id">
                                    @isset($statuses)
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id }}" data-color="{{ $status->color }}"
                                                {{ old('status') == $status->id ? 'selected' : '' }}>
                                                {{ $status->title }} ({{ $status->color }})</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="mt-2">
                                <a href="javascript:void(0);" class="openCreateStatusModal"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title=" <?= get_label('create_status', 'Create status') ?>"><i
                                            class="bx bx-plus"></i></button></a>
                                <a href="{{ route('status.index') }}" target="_blank"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('manage_statuses', 'Manage statuses') ?>"><i
                                            class="bx bx-list-ul"></i></button></a>
                            </div>
                            @error('status_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <?= get_label('priority', 'Priority') ?>
                            </label>
                            <div class="input-group">
                                <select class="form-select priorityDropdown" name="priority_id" id="priority_id"
                                    data-placeholder="<?= get_label('please_select', 'Please select') ?>">
                                    <option></option>
                                    @isset($priorities)
                                        @foreach ($priorities as $priority)
                                            <option value="{{ $priority->id }}" data-color="{{ $priority->color }}">
                                                {{ $priority->title }}</option>
                                        @endforeach
                                    @endisset
                                </select>
                            </div>
                            <div class="mt-2">
                                <a href="javascript:void(0);" class="openCreatePriorityModal"><button
                                        type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title=" <?= get_label('create_priority', 'Create Priority') ?>"><i
                                            class="bx bx-plus"></i></button></a>
                                <a href="{{ route('priority.manage') }}" target="_blank"><button type="button"
                                        class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                                        data-bs-placement="right"
                                        data-bs-original-title="<?= get_label('manage_priorities', 'Manage Priorities') ?>"><i
                                            class="bx bx-list-ul"></i></button></a>
                            </div>
                            @error('priority_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="start_date">
                                <?= get_label('starts_at', 'Starts at') ?>
                                <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="update_start_date" name="start_date" class="form-control"
                                value="">
                            @error('start_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="due_date">
                                <?= get_label('ends_at', 'Ends at') ?> <span class="asterisk">*</span>
                            </label>
                            <input type="text" id="update_end_date" name="due_date" class="form-control"
                                value="">
                            @error('due_date')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="project_title" class="form-label">
                                <?= get_label('project', 'Project') ?>
                                <span class="asterisk">*</span>
                            </label>
                            <input class="form-control" type="text" id="update_project_title" readonly>
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label class="form-label" for="user_id">
                                <?= get_label('select_users', 'Select users') ?> <span
                                    id="task_update_users_associated_with_project"></span>
                            </label>
                            <div class="input-group">
                                <select class="form-control js-example-basic-multiple" name="user_id[]"
                                    multiple="multiple"
                                    data-placeholder="<?= get_label('type_to_search', 'Type to search') ?>">
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <?= get_label('description', 'Description') ?>
                            </label>
                            <textarea class="form-control description" id="task_description" rows="5" name="description"
                                placeholder="<?= get_label('please_enter_description', 'Please enter description') ?>">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label"
                                    for="billing_type">{{ get_label('billing_type', 'Billing Type') }}</label>
                                <select class="form-select" name="billing_type" id="edit_billing_type">
                                    <option value="none">{{ get_label('none', 'None') }}</option>
                                    <option value="billable">{{ get_label('billable', 'Billable') }}</option>
                                    <option value="non-billable">{{ get_label('non_billable', 'Non Billable') }}
                                    </option>
                                </select>
                                @error('billing_type')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label"
                                    for="completion_percentage">{{ get_label('completion_percentage', 'Completion Percentage (%)') }}</label>
                                <select class="form-select" name="completion_percentage"
                                    id="edit_completion_percentage">
                                    @foreach (range(0, 100, 10) as $percentage)
                                        <option value="{{ $percentage }}">{{ $percentage }}%</option>
                                    @endforeach
                                </select>
                                @error('completion_percentage')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">
                                <?= get_label('note', 'Note') ?>
                            </label>
                            <textarea class="form-control" name="note" rows="3" id="taskNote"
                                placeholder="<?= get_label('optional_note', 'Optional Note') ?>"></textarea>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="reminder-switch"
                                    class="form-label">{{ get_label('enable_reminder', 'Enable Reminder') }}</label>
                                <i class="bx bx-info-circle text-primary"
                                    data-bs-toggle="tooltip"data-bs-offset="0,4" data-bs-placement="top"
                                    data-bs-html="true"title=""
                                    data-bs-original-title="<b>{{ get_label('task_reminder', 'Task Reminder') }}:</b> {{ get_label('task_reminder_info', 'Enable this option to set reminders for tasks. You can configure reminder frequencies (daily, weekly, or monthly), specific times, and customize alerts to ensure you stay on track with task deadlines.') }}"></i>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="edit-reminder-switch"
                                        name="enable_reminder">
                                    <label class="form-check-label"
                                        for="reminder-switch">{{ get_label('enable_task_reminder', 'Enable Task Reminder') }}</label>
                                </div>
                            </div>
                            <div id="edit-reminder-settings" class="d-none">
                                <!-- Frequency Type -->
                                <div class="mb-3">
                                    <label for="frequency-type"
                                        class="form-label">{{ get_label('frequency_type', 'Frequency Type') }}</label>
                                    <select class="form-select" id="edit-frequency-type" name="frequency_type"
                                        required>
                                        <option value="daily">{{ get_label('daily', 'Daily') }}</option>
                                        <option value="weekly">{{ get_label('weekly', 'Weekly') }}</option>
                                        <option value="monthly">{{ get_label('monthly', 'Monthly') }}</option>
                                    </select>
                                </div>
                                <!-- Day of Week (Weekly Only) -->
                                <div class="d-none mb-3" id="edit-day-of-week-group">
                                    <label for="day-of-week"
                                        class="form-label">{{ get_label('day_of_the_week', 'Day of the Week') }}</label>
                                    <select class="form-select" id="edit-day-of-week" name="day_of_week">
                                        <option value="">{{ get_label('any_day', 'Any Day') }}</option>
                                        <option value="1">{{ get_label('monday', 'Monday') }}</option>
                                        <option value="2">{{ get_label('tuesday', 'Tuesday') }}</option>
                                        <option value="3">{{ get_label('wednesday', 'Wednesday') }}</option>
                                        <option value="4">{{ get_label('thursday', 'Thursday') }}</option>
                                        <option value="5">{{ get_label('friday', 'Friday') }}</option>
                                        <option value="6">{{ get_label('saturday', 'Saturday') }}</option>
                                        <option value="7">{{ get_label('sunday', 'Sunday') }}</option>
                                    </select>
                                </div>
                                <!-- Day of Month (Monthly Only) -->
                                <div class="d-none mb-3" id="edit-day-of-month-group">
                                    <label for="day-of-month"
                                        class="form-label">{{ get_label('day_of_the_month', 'Day of the Month') }}</label>
                                    <select class="form-select" id="edit-day-of-month" name="day_of_month">
                                        <option value="">{{ get_label('any_day', 'Any Day') }}</option>
                                        @foreach (range(1, 31) as $day)
                                            <option value="{{ $day }}">{{ $day }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Time of Day -->
                                <div class="mb-3">
                                    <label for="time-of-day"
                                        class="form-label">{{ get_label('time_of_day', 'Time of Day') }}</label>
                                    <input type="time" class="form-control" id="edit-time-of-day"
                                        name="time_of_day">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="recurring-task-switch" class="form-label">
                                    {{ get_label('enable_recurring_task', 'Enable Recurring Task') }}
                                </label>
                                <i class="bx bx-info-circle text-primary" data-bs-toggle="tooltip"
                                    data-bs-offset="0,4" data-bs-placement="top" data-bs-html="true"
                                    title=""
                                    data-bs-original-title="<b>{{ get_label('recurring_tasks', 'Recurring Tasks') }}:</b> {{ get_label('recurring_tasks_info', 'This option enables the creation of recurring tasks. You can set the frequency (daily, weekly, monthly, yearly), specific days, and manage the recurrence schedule efficiently.') }}">
                                </i>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input"
                                        id="edit-recurring-task-switch" name="enable_recurring_task">
                                    <label class="form-check-label" for="recurring-task-switch">
                                        {{ get_label('enable_recurring_task_toggle', 'Enable Recurring Task') }}
                                    </label>
                                </div>
                            </div>
                            <div id="edit-recurring-task-settings" class="d-none">
                                <!-- Frequency Type -->
                                <div class="mb-3">
                                    <label for="recurrence-frequency" class="form-label">
                                        {{ get_label('recurrence_frequency', 'Recurrence Frequency') }}
                                    </label>
                                    <select class="form-select" id="edit-recurrence-frequency"
                                        name="recurrence_frequency" required>
                                        <option value="daily">{{ get_label('daily', 'Daily') }}</option>
                                        <option value="weekly">{{ get_label('weekly', 'Weekly') }}</option>
                                        <option value="monthly">{{ get_label('monthly', 'Monthly') }}</option>
                                        <option value="yearly">{{ get_label('yearly', 'Yearly') }}</option>
                                    </select>
                                </div>
                                <!-- Day of Week (Weekly Only) -->
                                <div class="d-none mb-3" id="edit-recurrence-day-of-week-group">
                                    <label for="recurrence-day-of-week" class="form-label">
                                        {{ get_label('day_of_the_week', 'Day of the Week') }}
                                    </label>
                                    <select class="form-select" id="edit-recurrence-day-of-week"
                                        name="recurrence_day_of_week">
                                        <option value="">{{ get_label('any_day', 'Any Day') }}</option>
                                        <option value="1">{{ get_label('monday', 'Monday') }}</option>
                                        <option value="2">{{ get_label('tuesday', 'Tuesday') }}</option>
                                        <option value="3">{{ get_label('wednesday', 'Wednesday') }}</option>
                                        <option value="4">{{ get_label('thursday', 'Thursday') }}</option>
                                        <option value="5">{{ get_label('friday', 'Friday') }}</option>
                                        <option value="6">{{ get_label('saturday', 'Saturday') }}</option>
                                        <option value="7">{{ get_label('sunday', 'Sunday') }}</option>
                                    </select>
                                </div>
                                <!-- Day of Month (Monthly and Yearly Only) -->
                                <div class="d-none mb-3" id="edit-recurrence-day-of-month-group">
                                    <label for="recurrence-day-of-month" class="form-label">
                                        {{ get_label('day_of_the_month', 'Day of the Month') }}
                                    </label>
                                    <select class="form-select" id="edit-recurrence-day-of-month"
                                        name="recurrence_day_of_month">
                                        <option value="">{{ get_label('any_day', 'Any Day') }}</option>
                                        @foreach (range(1, 31) as $day)
                                            <option value="{{ $day }}">{{ $day }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Month of Year (Yearly Only) -->
                                <div class="d-none mb-3" id="edit-recurrence-month-of-year-group">
                                    <label for="recurrence-month-of-year" class="form-label">
                                        {{ get_label('month_of_the_year', 'Month of the Year') }}
                                    </label>
                                    <select class="form-select" id="edit-recurrence-month-of-year"
                                        name="recurrence_month_of_year">
                                        <option value="">{{ get_label('any_month', 'Any Month') }}</option>
                                        @foreach (range(1, 12) as $month)
                                            <option value="{{ $month }}">
                                                {{ \Carbon\Carbon::create()->month($month)->format('F') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Starts From -->
                                <div class="mb-3">
                                    <label for="recurrence-starts-from" class="form-label">
                                        {{ get_label('starts_from', 'Starts From') }}
                                    </label>
                                    <input type="date" class="form-control" id="edit-recurrence-starts-from"
                                        name="recurrence_starts_from">
                                </div>
                                <!-- Number of Occurrences -->
                                <div class="mb-3">
                                    <label for="recurrence-occurrences" class="form-label">
                                        {{ get_label('number_of_occurrences', 'Number of Occurrences') }}
                                    </label>
                                    <input type="number" class="form-control" id="edit-recurrence-occurrences"
                                        name="recurrence_occurrences" min="1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        <?= get_label('update', 'Update') ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
@if (Request::is($prefix . '/projects/list') ||
        Request::is($prefix . '/home') ||
        Request::is($prefix . '/projects') ||
        Request::is($prefix . '/users/profile/*') ||
        Request::is($prefix . '/clients/profile/*') ||
        Request::is($prefix . '/tasks') ||
        Request::is($prefix . '/tasks/draggable') ||
        Request::is($prefix . '/projects/information/*') ||
        Request::is($prefix . '/projects/kanban-view')||
        Request::is($prefix . '/tasks/group-by-task-list'))
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel1"><span id="typePlaceholder"></span>
                        <?= get_label('quick_view', 'Quick View') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5 id="quickViewTitlePlaceholder" class="text-muted"></h5>
                    <div class="nav-align-top">
                        <ul class="nav nav-tabs" role="tablist">
                            @if ($auth_user->can('manage_users'))
                                <li class="nav-item">
                                    <button type="button" class="nav-link active" role="tab"
                                        data-bs-toggle="tab" data-bs-target="#navs-top-quick-view-users"
                                        aria-controls="navs-top-quick-view-users">
                                        <i class="menu-icon tf-icons bx bx-group text-primary"></i>
                                        <?= get_label('users', 'Users') ?>
                                    </button>
                                </li>
                            @endif
                            @if ($auth_user->can('manage_clients'))
                                <li class="nav-item">
                                    <button type="button"
                                        class="nav-link {{ !$auth_user->can('manage_users') ? 'active' : '' }}"
                                        role="tab" data-bs-toggle="tab"
                                        data-bs-target="#navs-top-quick-view-clients"
                                        aria-controls="navs-top-quick-view-clients">
                                        <i class="menu-icon tf-icons bx bx-group text-warning"></i>
                                        <?= get_label('clients', 'Clients') ?>
                                    </button>
                                </li>
                            @endif
                            <li class="nav-item">
                                <button type="button"
                                    class="nav-link {{ !$auth_user->can('manage_users') && !$auth_user->can('manage_clients') ? 'active' : '' }}"
                                    role="tab" data-bs-toggle="tab"
                                    data-bs-target="#navs-top-quick-view-description"
                                    aria-controls="navs-top-quick-view-description">
                                    <i class="menu-icon tf-icons bx bx-notepad text-success"></i>
                                    <?= get_label('description', 'Description') ?>
                                </button>
                            </li>
                        </ul>
                        <input type="hidden" id="type">
                        <input type="hidden" id="typeId">
                        <div class="tab-content">
                            @if ($auth_user->can('manage_users'))
                                <div class="tab-pane fade active show" id="navs-top-quick-view-users"
                                    role="tabpanel">
                                    <div class="table-responsive text-nowrap">
                                        <!-- <input type="hidden" id="data_type" value="users">
                                <input type="hidden" id="data_table" value="usersTable"> -->
                                        <table id="usersTable" data-toggle="table"
                                            data-loading-template="loadingTemplate"
                                            data-url="{{ route('users.list') }}" data-icons-prefix="bx"
                                            data-icons="icons" data-show-refresh="true" data-total-field="total"
                                            data-trim-on-search="false" data-data-field="rows"
                                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                            data-side-pagination="server" data-show-columns="true"
                                            data-pagination="true" data-sort-name="id" data-sort-order="desc"
                                            data-mobile-responsive="true"
                                            data-query-params="queryParamsUsersClients">
                                            <thead>
                                                <tr>
                                                    <th data-checkbox="true"></th>
                                                    <th data-sortable="true" data-field="id">
                                                        <?= get_label('id', 'ID') ?>
                                                    </th>
                                                    <th data-field="profile">
                                                        <?= get_label('users', 'Users') ?>
                                                    </th>
                                                    <th data-field="role">
                                                        <?= get_label('role', 'Role') ?>
                                                    </th>
                                                    <th data-field="phone" data-sortable="true"
                                                        data-visible="false">
                                                        <?= get_label('phone_number', 'Phone number') ?>
                                                    </th>
                                                    <th data-sortable="true" data-field="created_at"
                                                        data-visible="false">
                                                        <?= get_label('created_at', 'Created at') ?>
                                                    </th>
                                                    <th data-sortable="true" data-field="updated_at"
                                                        data-visible="false">
                                                        <?= get_label('updated_at', 'Updated at') ?>
                                                    </th>
                                                    {{-- <th data-formatter="actionFormatterUsers">
                                                <?= get_label('actions', 'Actions') ?>
                                            </th> --}}
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            @endif
                            @if ($auth_user->can('manage_clients'))
                                <div class="tab-pane fade {{ !$auth_user->can('manage_users') ? 'active show' : '' }}"
                                    id="navs-top-quick-view-clients" role="tabpanel">
                                    <div class="table-responsive text-nowrap">
                                        <!-- <input type="hidden" id="data_type" value="clients">
                            <input type="hidden" id="data_table" value="clientsTable"> -->
                                        <table id="clientsTable" data-toggle="table"
                                            data-loading-template="loadingTemplate"
                                            data-url="{{ route('clients.list') }}" data-icons-prefix="bx"
                                            data-icons="icons" data-show-refresh="true" data-total-field="total"
                                            data-trim-on-search="false" data-data-field="rows"
                                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                            data-side-pagination="server" data-show-columns="true"
                                            data-pagination="true" data-sort-name="id" data-sort-order="desc"
                                            data-mobile-responsive="true"
                                            data-query-params="queryParamsUsersClients">
                                            <thead>
                                                <tr>
                                                    <th data-checkbox="true"></th>
                                                    <th data-sortable="true" data-field="id">
                                                        <?= get_label('id', 'ID') ?>
                                                    </th>
                                                    <th data-field="profile">
                                                        <?= get_label('client', 'Client') ?>
                                                    </th>
                                                    <th data-field="company" data-sortable="true"
                                                        data-visible="false">
                                                        <?= get_label('company', 'Company') ?>
                                                    </th>
                                                    <th data-field="phone" data-sortable="true"
                                                        data-visible="false">
                                                        <?= get_label('phone_number', 'Phone number') ?>
                                                    </th>
                                                    <th data-sortable="true" data-field="created_at"
                                                        data-visible="false">
                                                        <?= get_label('created_at', 'Created at') ?>
                                                    </th>
                                                    <th data-sortable="true" data-field="updated_at"
                                                        data-visible="false">
                                                        <?= get_label('updated_at', 'Updated at') ?>
                                                    </th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            @endif
                            <div class="tab-pane fade {{ !$auth_user->can('manage_users') && !$auth_user->can('manage_clients') ? 'active show' : '' }}"
                                id="navs-top-quick-view-description" role="tabpanel">
                                <p class="pt-3" id="quickViewDescPlaceholder"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <?= get_label('close', 'Close') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
<div class="modal fade" id="confirmSaveColumnVisibility" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="exampleModalLabel2">
                    <?= get_label('confirm', 'Confirm!') ?>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <?= get_label('save_column_visibility_alert', 'Are You Want to Save Column Visibility?') ?>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary" id="confirm">
                    <?= get_label('yes', 'Yes') ?>
                </button>
            </div>
        </div>
    </div>
</div>


{{-- Task Lists Modals --}}
<div class="modal fade" id="edit_task_list_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ get_label('update_task_list', 'Update Task List') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('task_lists.update') }}" class="form-submit-event" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="dnr">
                <input type="hidden" id="task_list_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="project" class="form-label">{{get_label('project','Project')}}</label>
                            <input class="form-control" type="text" id="task_list_project" name="project" disabled>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="name" class="form-label">{{ get_label('name', 'Name') }}
                                <span class="asterisk">*</span>
                            </label>
                            <input class="form-control" type="text" id="task_list_name" name="name"
                                placeholder="{{ get_label('please_enter_name', 'Please Enter Name') }}">
                            @error('title')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ get_label('close', 'Close') }}
                    </button>
                    <button type="submit" id="submit_btn" class="btn btn-primary">
                        {{ get_label('create', 'Create') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="create_task_list_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form class="modal-content form-submit-event" action="{{ route('task_lists.store') }}" method="POST">
            <input type="hidden" name="dnr">
            <div class="modal-header">
                <h5 class="modal-title">{{ get_label('create_task_list', 'Create Task List') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="name" class="form-label">{{ get_label('name', 'Name') }}
                            <span class="asterisk">*</span>
                        </label>
                        <input class="form-control" type="text" name="name"
                            placeholder="{{ get_label('please_enter_name', 'Please Enter Name') }}">
                        @error('title')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="project"
                            class="form-label">{{ get_label('select_project', 'Select Project') }}</label> <span
                            class="asterisk">*</span>
                        <select class="form-select project-select" name="project_id">
                            <option value="">{{ get_label('select_project', 'Select Project') }}</option>
                        </select>
                        @error('project')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    {{ get_label('close', 'Close') }}
                </button>
                <button type="submit" id="submit_btn" class="btn btn-primary">
                    {{ get_label('create', 'Create') }}
                </button>
            </div>
        </form>
    </div>
</div>



