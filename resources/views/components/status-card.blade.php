@extends('layout')
@section('title')
    <?= get_label('statuses', 'Statuses') ?>
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
                    <li class="breadcrumb-item active">
                        <?= get_label('statuses', 'Statuses') ?>
                    </li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#create_status_modal">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="right"
                    data-bs-original-title="<?= get_label('create_status', 'Create status') ?>">
                    <i class="bx bx-plus"></i>
                </button>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                @if (is_countable($statuses) && count($statuses) > 0)
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?= get_label('id', 'ID') ?></th>
                                <th><?= get_label('title', 'Title') ?></th>
                                <th><?= get_label('preview', 'Preview') ?></th>
                                <th><?= get_label('updated_at', 'Updated at') ?></th>
                                <th><?= get_label('actions', 'Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statuses as $index => $status)
                                <tr>
                                    <td><input type="checkbox" name="selected[]" value="{{ $status->id }}"></td>
                                    <td>{{ $status->id }}</td>
                                    <td>{{ $status->title }}</td>
                                    <td>
                                        <span class="badge bg-{{ $status->color ?? 'secondary' }}">
                                            {{ $status->title }}
                                        </span>
                                    </td>
                                    <td>{{ $status->updated_at->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="javascript:void(0);"
                                            class="btn btn-sm btn-outline-primary"
                                            data-id="{{ $status->id }}"
                                            title="{{ get_label('update', 'Update') }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#edit_status_modal">
                                            <i class="bx bx-edit"></i>
                                        </a>

                                        <form action="{{ route('status.destroy', $status->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <x-empty-state-card :type="'Status'" />
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="edit_status_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('status.update') }}" class="modal-content form-submit-event" method="POST">
            @csrf
            <input type="hidden" name="id" id="status_id">
            <div class="modal-header">
                <h5 class="modal-title"><?= get_label('update_status', 'Update status') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><?= get_label('title', 'Title') ?> <span class="asterisk">*</span></label>
                    <input type="text" class="form-control" name="title" id="status_title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label"><?= get_label('color', 'Color') ?> <span class="asterisk">*</span></label>
                    <select class="form-select" name="color" id="status_color" required>
                        <option value="primary"><?= get_label('primary', 'Primary') ?></option>
                        <option value="secondary"><?= get_label('secondary', 'Secondary') ?></option>
                        <option value="success"><?= get_label('success', 'Success') ?></option>
                        <option value="danger"><?= get_label('danger', 'Danger') ?></option>
                        <option value="warning"><?= get_label('warning', 'Warning') ?></option>
                        <option value="info"><?= get_label('info', 'Info') ?></option>
                        <option value="dark"><?= get_label('dark', 'Dark') ?></option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?= get_label('close', 'Close') ?>
                </button>
                <button type="submit" class="btn btn-primary">
                    <?= get_label('update', 'Update') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll('[data-bs-target="#edit_status_modal"]');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            const id = this.getAttribute('data-id');
            const title = row.querySelectorAll('td')[2].innerText.trim();
            const badge = row.querySelector('.badge');
            const badgeClasses = badge.className.split(' ');
            let color = 'secondary';

            badgeClasses.forEach(c => {
                if (c.startsWith('bg-')) {
                    color = c.replace('bg-', '');
                }
            });

            document.getElementById('status_id').value = id;
            document.getElementById('status_title').value = title;
            document.getElementById('status_color').value = color;
        });
    });
});
</script>
@endsection
