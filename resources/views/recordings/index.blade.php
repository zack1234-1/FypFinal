@extends('layout')

@section('title')
    <?= get_label('recordings', 'Recordings') ?>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4">
    <!-- Header Section -->
    <div class="row align-items-center justify-content-between mt-4 mb-4">
        <div class="col-12 col-md-auto mb-3 mb-md-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home.index') }}" class="text-decoration-none">
                            <i class='bx bx-home me-1'></i>Home
                        </a>
                    </li>
                    <li class="breadcrumb-item active">
                        <i class='bx bx-video me-1'></i>Recordings
                    </li>
                </ol>
            </nav>
        </div>

        <div class="col-12 col-md-auto">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-pill">{{ count($recordings) }} Recording{{ count($recordings) !== 1 ? 's' : '' }}</span>
                @if(count($recordings) > 0)
                    <span class="badge bg-secondary rounded-pill">Latest: {{ $recordings->first()->created_at->format('M d, Y') ?? 'N/A' }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-12">
            <h3 class="fw-bold">Available Recordings</h3>
            <p class="text-muted mb-0">Watch or download your meeting recordings</p>
        </div>
    </div>

    <!-- Recordings Section -->
    <div class="row">
        <div class="col-12">
            @forelse ($recordings as $recording)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row align-items-start">
                            <!-- Recording Info -->
                            <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-success bg-opacity-10 rounded-circle p-2">
                                            <i class='bx bx-play-circle text-success fs-5'></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1 text-truncate">{{ $recording->file_name }}</h6>
                                        <small class="text-muted">{{ $recording->created_at->format('M d, Y H:i') }}</small>
                                    </div>
                                </div>

                                @if ($recording->recording_blob)
                                    <div class="mt-3">
                                        <video width="100%" controls>
                                            <source src="{{ route('recordings.stream', $recording->id) }}" type="{{ $recording->mime_type ?? 'video/mp4' }}">
                                            Your browser does not support the video tag.
                                        </video>
                                    </div>
                                @else
                                    <p class="text-danger mt-3">No preview available for this recording.</p>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="col-12 col-lg-6 d-flex justify-content-lg-end align-items-start mt-3 mt-lg-0">
                                <a href="{{ route('recordings.download', $recording->id) }}" 
                                   class="btn btn-outline-primary d-flex align-items-center">
                                    <i class='bx bx-download me-2'></i>
                                    <span>Download</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class='bx bx-video-off display-1 text-muted opacity-50'></i>
                        </div>
                        <h4 class="text-muted mb-3">No recordings found</h4>
                        <p class="text-muted mb-4">
                            Meeting recordings will appear here once they're processed and available for viewing.
                        </p>
                        <a href="{{ route('meetings.index') }}" class="btn btn-outline-primary">
                            <i class='bx bx-video me-2'></i>Go to Meetings
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection