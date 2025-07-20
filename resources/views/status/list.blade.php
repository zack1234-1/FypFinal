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
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="tooltip"
                        data-bs-placement="right"
                        data-bs-original-title="<?= get_label('create_status', 'Create status') ?>">
                        <i class="bx bx-plus"></i>
                    </button>
                </a>
            </div>
        </div>

        <!-- Navigation Tabs -->
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

      @if (session('success') || session('error'))
        <div class="position-fixed top-0 end-0 p-4" style="z-index: 2000;">
            <div id="toastMessage"
                class="toast show text-white {{ session('success') ? 'bg-success' : 'bg-danger' }} border-0 shadow-lg"
                role="alert" aria-live="assertive" aria-atomic="true"
                style="min-width: 300px; font-size: 1.1rem; padding: 1rem;">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('success') ?? session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>
      @endif

        <div class="container-fluid">
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
                         <div class="text-center my-5">
                            <h5 class="text-muted">No statues found.</h5>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    
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

    const toastEl = document.getElementById('toastMessage');
        if (toastEl) {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    
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