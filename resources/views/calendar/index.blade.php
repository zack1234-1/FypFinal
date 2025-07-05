@extends('layout')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<div class="container mt-4">
    <h2>Calendar</h2>

    <div class="mb-3">
        <label for="filterType" class="form-label">Filter by Type:</label>
        <select id="filterType" class="form-select w-auto d-inline-block">
            <option value="">All</option>
            <option value="meeting">Meeting</option>
            <option value="todo">To-do</option>
        </select>
    </div>

    <div id="upcomingBirthdaysCalendar"></div>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addEventModal" onclick="clearEventForm()">Add Event</button>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addEventForm" action="{{ route('calendar.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addEventModalLabel">Add New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                    <input type="hidden" name="workspace_id" value="{{ session('workspace_id') }}">

                    <label for="event_title">Title:</label>
                    <input type="text" name="title" id="event_title" class="form-control mb-2" required>

                    <label for="event_description">Description:</label>
                    <textarea name="description" id="event_description" class="form-control mb-2" required></textarea>

                    <label for="event_type">Type:</label>
                    <select name="type" id="event_type" class="form-control mb-2" required>
                        <option value="">Select Type</option>
                        <option value="meeting">Meeting</option>
                        <option value="todo">To-do</option>
                    </select>

                    <label for="new_event_start">Start Date & Time:</label>
                    <input type="datetime-local" name="start_date_time" id="new_event_start" class="form-control mb-2" required>

                    <label for="new_event_end">End Date & Time:</label>
                    <input type="datetime-local" name="end_date_time" id="new_event_end" class="form-control mb-2" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Save Event</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Event Detail Modal -->
<div class="modal fade" id="eventDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="eventForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Event Detail</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="event_id">
                    <input type="text" name="title" id="event_detail_title" class="form-control mb-2">
                    <textarea name="description" id="event_detail_description" class="form-control mb-2"></textarea>
                    <input type="datetime-local" name="start_date_time" id="event_start" class="form-control mb-2">
                    <input type="datetime-local" name="end_date_time" id="event_end" class="form-control mb-2">
                </div>
                <div class="modal-footer">
                    <button type="button" id="updateBtn" class="btn btn-warning w-100 mb-1">Update</button>
                    <button type="button" id="deleteBtn" class="btn btn-danger w-100">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let calendar;

document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();

    document.getElementById('updateBtn').addEventListener('click', function() {
        const form = document.getElementById('eventForm');
        const eventId = document.getElementById('event_id').value;
        form.action = "{{ route('calendar.update', '') }}/" + eventId;
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        form.appendChild(methodInput);
        form.submit();
    });

    document.getElementById('deleteBtn').addEventListener('click', function() {
        if (confirm('Are you sure to delete this event?')) {
            const form = document.getElementById('eventForm');
            const eventId = document.getElementById('event_id').value;
            form.action = "{{ route('calendar.delete', '') }}/" + eventId;
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            form.submit();
        }
    });

    document.getElementById('filterType').addEventListener('change', function () {
        const selectedType = this.value;
        calendar.getEventSources().forEach(source => source.remove());
        calendar.addEventSource(function(fetchInfo, successCallback, failureCallback) {
            fetch("{{ route('calendar.events') }}")
                .then(response => response.json())
                .then(events => {
                    const filtered = selectedType ? events.filter(e => e.type === selectedType) : events;
                    successCallback(filtered);
                });
        });
    });
});

function initializeCalendar() {
    const calendarEl = document.getElementById("upcomingBirthdaysCalendar");
    if (!calendarEl) return;

    calendar = new FullCalendar.Calendar(calendarEl, {
        plugins: ["interaction", "dayGrid", "list"],
        headerToolbar: {
            center: "title",
            right: "dayGridMonth,listYear",
        },
        events: {
            url: "{{ route('calendar.events') }}",
            failure: () => alert('There was an error fetching events!')
        },
        eventDisplay: 'block',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },
        eventDidMount: function(info) {
            const type = info.event.extendedProps.type;
            if (type === 'meeting') {
                info.el.style.backgroundColor = '#28a745';
            } else if (type === 'todo') {
                info.el.style.backgroundColor = '#007bff';
            }
        },
        eventClick: function(info) {
            document.getElementById("event_id").value = info.event.id;
            document.getElementById("event_detail_title").value = info.event.title;
            document.getElementById("event_detail_description").value = info.event.extendedProps.description || '';
            document.getElementById("event_start").value = info.event.start ? info.event.start.toISOString().slice(0, 16) : '';
            document.getElementById("event_end").value = info.event.end ? info.event.end.toISOString().slice(0, 16) : '';
            const modal = new bootstrap.Modal(document.getElementById("eventDetailModal"));
            modal.show();
        },
    });

    calendar.render();
}
</script>
@endsection
