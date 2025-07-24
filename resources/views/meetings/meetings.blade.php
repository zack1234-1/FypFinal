@extends('layout')

@section('title')
    <?= get_label('meetings', 'Meetings') ?>
@endsection

@section('content')
<div class="container-fluid px-3 px-md-4">
    <div class="row align-items-center justify-content-between mt-4 mb-4">
        <div class="col-12 col-lg-8 mb-3 mb-lg-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('home.index') }}" class="text-decoration-none">
                            <i class='bx bx-home me-1'></i><?= get_label('home', 'Home') ?>
                        </a>
                    </li>
                    <li class="breadcrumb-item active">
                        <i class='bx bx-video me-1'></i><?= get_label('meetings', 'Meetings') ?>
                    </li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row justify-content-center align-items-center min-vh-50">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 shadow-lg">
                <div class="card-body text-center p-5">

                    @if ($meeting && ($meeting->zoom_join_url || $meeting->zoom_start_url))
                        @php
                            $zoomUrl = auth()->user()->hasRole('admin') && $meeting->zoom_start_url
                                ? $meeting->zoom_start_url
                                : $meeting->zoom_join_url;
                            $isHost = auth()->user()->hasRole('admin') && $meeting->zoom_start_url;
                        @endphp

                        @if ($zoomUrl)
                            <div class="d-flex justify-content-center">
                                <a href="{{ $zoomUrl }}" target="_blank"
                                   class="btn btn-primary btn-lg px-5 py-3 d-flex align-items-center">
                                    <i class='bx bx-video me-2' style="font-size: 1.2rem;"></i>
                                    <span class="fw-bold">
                                        {{ $isHost ? 'Start Meeting' : 'Join Meeting' }}
                                    </span>
                                </a>
                            </div>
                        @else
                            <div class="mt-4">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-4 d-inline-block mb-3">
                                    <i class='bx bx-error text-warning' style="font-size: 3rem;"></i>
                                </div>
                                <h4 class="fw-bold mb-3 text-warning">Meeting Link Unavailable</h4>
                                <p class="text-muted mb-0">
                                    The meeting link is not available at this time. Please contact the meeting organizer.
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="mt-4">
                            <div class="bg-secondary bg-opacity-10 rounded-circle p-4 d-inline-block mb-3">
                                <i class='bx bx-video-off text-secondary' style="font-size: 3rem;"></i>
                            </div>
                            <h4 class="fw-bold mb-3 text-secondary">No Meeting Available</h4>
                            <p class="text-muted mb-4">
                                There is no active meeting at this time. Please check back later or contact the meeting organizer.
                            </p>
                        </div>

                        <div class="d-flex justify-content-center gap-2">
                            <a href="{{ route('home.index') }}" class="btn btn-outline-primary">
                                <i class='bx bx-home me-2'></i>Back to Home
                            </a>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const viewRecordingsBtn = document.getElementById('viewRecordingsBtn');

    if (viewRecordingsBtn) {
        viewRecordingsBtn.addEventListener('click', function (event) {
            event.preventDefault();

            fetch("{{ route('recordings.storeBlob') }}")
                .then(response => response.text())
                .then(message => 
                {
                    console.log('Recording stored:', message);

                    window.location.href = "{{ route('recordings.index') }}";
                })
                .catch(error => {
                    console.error('Error storing recording:', error);
                    window.location.href = "{{ route('recordings.index') }}";
                });
        });
    }
});
</script>
@endsection