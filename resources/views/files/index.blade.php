@extends('layout')

@section('content')
<div class="container">
    <div class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="row g-4">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif
            @if(session('error'))
                <div class="col-12">
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            @endif

            <!-- Folder Card -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <a href="{{ route('files.display') }}" class="text-decoration-none text-dark">
                        <div class="card-body text-center p-5">
                            <div class="mb-3">
                                <i class="fas fa-folder fa-4x text-warning"></i>
                            </div>
                            <h3 class="card-title mb-2">Folders</h3>
                            <p class="text-muted">Browse files organized in folders</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Files Card -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <a href="{{ route('files.noFolder') }}" class="text-decoration-none text-dark">
                        <div class="card-body text-center p-5">
                            <div class="mb-3">
                                <i class="fas fa-file-alt fa-4x text-secondary"></i>
                            </div>
                            <h3 class="card-title mb-2">Files</h3>
                            <p class="text-muted">View all files in one place</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection