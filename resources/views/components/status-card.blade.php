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
@endsection
