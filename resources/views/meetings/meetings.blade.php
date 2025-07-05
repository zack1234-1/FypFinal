@extends('layout')

@section('title')
    <?= get_label('meetings', 'Meetings') ?>
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
                        <?= get_label('meetings', 'Meetings') ?>
                    </li>
                </ol>
            </nav>
        </div>

        <div>
            @if (auth()->user()->hasRole('admin'))
                <button type="button" class="btn btn-sm btn-primary action_create_meetings"
                    data-bs-toggle="modal"
                    data-bs-target="#createMeetingModal"
                    data-bs-placement="left"
                    title="<?= get_label('create_meeting', 'Create meeting') ?>">
                    <i class='bx bx-plus'></i>
                </button>
            @endif
        </div>
    </div>

    {{-- Zoom Meeting List --}}
    @if ($meetings->isEmpty())
        <div class="alert alert-info mt-4">No meetings scheduled.</div>
    @else
        <div class="mt-5">
            <h5>Upcoming Zoom Meetings</h5>
            <ul class="list-group list-group-flush">
                @foreach ($meetings as $meeting)
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-1">{{ $meeting->topic }}</h6>
                            <small class="text-muted">
                                Starts at: {{ \Carbon\Carbon::parse($meeting->start_time)->format('d M Y, h:i A') }}<br>
                                Duration: {{ $meeting->duration }} minutes
                            </small>

                            @if ($meeting->zoom_join_url)
                                <div class="mt-2">
                                    <a href="{{ $meeting->zoom_join_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        Join
                                    </a>
                                    @if (auth()->id() == $meeting->created_by)
                                        <a href="{{ $meeting->zoom_start_url }}" target="_blank" class="btn btn-sm btn-success">
                                            Start
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <span class="badge bg-primary rounded-pill">
                            {{ \Carbon\Carbon::parse($meeting->start_time)->diffForHumans() }}
                        </span>
                        @if ($meeting->recording)
                            <div class="mt-2">
                                <a href="{{ $meeting->recording }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    View Recording
                                </a>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

{{-- Create Zoom Meeting Modal --}}
<div class="modal fade" id="createMeetingModal" tabindex="-1" aria-labelledby="createMeetingLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('meetings.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createMeetingLabel">Create Zoom Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="topic" class="form-label">Topic</label>
                        <input type="text" name="topic" id="topic" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="datetime-local" name="start_time" id="start_time" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration" id="duration" class="form-control" required min="1" value="30">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Meeting</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Labels & Scripts --}}
<script>
    var label_update = '<?= get_label('update', 'Update') ?>';
    var label_delete = '<?= get_label('delete', 'Delete') ?>';
    var label_duplicate = '<?= get_label('duplicate', 'Duplicate') ?>';
    var label_not_assigned = '<?= get_label('not_assigned', 'Not assigned') ?>';
</script>

<script src="{{ asset('assets/js/pages/meetings.js') }}"></script>
@endsection
