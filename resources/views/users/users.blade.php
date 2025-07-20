{{-- resources/views/users/index.blade.php --}}

@extends('layout')

@section('title')
    {{ get_label('users', 'Users') }}
@endsection

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
    <div class="d-flex justify-content-between mb-3 mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/home') }}">{{ get_label('home', 'Home') }}</a></li>
                <li class="breadcrumb-item active">{{ get_label('staffs', 'Staffs') }}</li>
            </ol>
        </nav>

        <button type="button"
                class="btn btn-sm btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#createStaffModal"
                title="{{ get_label('create_staff', 'Create staff') }}">
            <i class='bx bx-plus'></i>
        </button>
    </div>

    @if ($users->count())
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>{{ get_label('name', 'Name') }}</th>
                                <th>{{ get_label('email', 'Email') }}</th>
                                <th>{{ get_label('phone', 'Phone') }}</th>
                                <th>{{ get_label('role', 'Role') }}</th>
                                <th>{{ get_label('created_at', 'Created At') }}</th>
                                <th>{{ get_label('actions', 'Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ trim($user->first_name . ' ' . $user->last_name) ?: '-' }}</td>
                                    <td>{{ $user->email ?? '-' }}</td>
                                    <td>{{ $user->phone ?? '-' }}</td>
                                    <td>
                                        @foreach ($user->roles as $role)
                                            <span class="badge bg-info text-dark">{{ ucfirst($role->name) }}</span>
                                        @endforeach
                                    </td>
                                    <td>{{ $user->created_at ? $user->created_at->format('Y-m-d') : '-' }}</td>
                                    <td>
                                        <button type="button"
                                                class="btn btn-sm btn-warning me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editStaffModal-{{ $user->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form action="{{ route('users.delete_user', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-user-slash fa-2x mb-2 text-secondary"></i>
            <div class="fw-semibold fs-5">No staffs found</div>
            <small class="text-muted">There are currently no staffs in the system.</small>
        </div>
    @endif
</div>

<!-- Create Staff Modal -->
<div class="modal fade" id="createStaffModal" tabindex="-1" aria-labelledby="createStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('users.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="createStaffModalLabel"><i class="fas fa-user-plus me-2"></i>Create Staff</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">First Name</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Last Name</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Retype Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Create
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modals for Each User -->
@foreach ($users as $user)
    <div class="modal fade" id="editStaffModal-{{ $user->id }}" tabindex="-1" aria-labelledby="editStaffModalLabel-{{ $user->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('users.update', $user->id) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editStaffModalLabel-{{ $user->id }}">
                        <i class="fas fa-edit me-2"></i>Edit Staff
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ $user->first_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ $user->last_name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="{{ $user->phone }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="1" {{ $user->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $user->status == 0 ? 'selected' : '' }}>Deactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password <small class="text-muted">(leave blank if unchanged)</small></label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Retype New Password</label>
                        <input type="password" name="password_confirmation" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

    <!-- Bootstrap 5 Bundle JS (includes Popper) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
      const toastElList = document.querySelectorAll('.toast');
        toastElList.forEach(toastEl => {
            new bootstrap.Toast(toastEl, { delay: 4000 }).show();
        });
});
</script>
@endsection
