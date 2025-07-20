@extends('layout')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

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

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Event Calendar</h2>
        <div class="d-flex gap-2">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                <i class="bi bi-plus-circle"></i> Add Event
            </button>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary" id="listViewBtn" onclick="switchView('list')">
                    <i class="bi bi-list"></i> List
                </button>
                <button type="button" class="btn btn-outline-secondary active" id="calendarViewBtn" onclick="switchView('calendar')">
                    <i class="bi bi-calendar"></i> Calendar
                </button>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <form method="GET" action="{{ route('calendar.index') }}" class="mb-3">
                <select name="type" id="filterType" class="form-select" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="meeting" {{ request('type') == 'meeting' ? 'selected' : '' }}>Meeting</option>
                    <option value="todo" {{ request('type') == 'todo' ? 'selected' : '' }}>To-do</option>
                </select>
            </form>
        </div>
    </div>

    <div id="listView" class="view-container" style="display: none;">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Events List</span>
                <small class="text-muted">Total: <span id="eventCount">{{ count($events) }}</span></small>
            </div>
            <div class="card-body" id="eventList">
                @forelse($events as $event)
                    <div class="card mb-3 shadow-sm event-card" 
                        data-type="{{ $event->type }}" 
                        data-title="{{ strtolower($event->title) }}" 
                        data-description="{{ strtolower($event->description) }}"
                        id="event-{{ $event->id }}">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 class="card-title">
                                        {{ $event->title }}
                                        <span class="badge bg-{{ $event->type == 'meeting' ? 'success' : 'warning' }} ms-2">
                                            {{ ucfirst($event->type) }}
                                        </span>
                                    </h5>
                                    <p class="card-text">{{ $event->description }}</p>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar-event"></i>
                                                <strong>Start:</strong> {{ \Carbon\Carbon::parse($event->start_date_time)->format('d M Y h:i A') }}
                                            </small>
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted">
                                                <i class="bi bi-calendar-check"></i>
                                                <strong>End:</strong> {{ \Carbon\Carbon::parse($event->end_date_time)->format('d M Y h:i A') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-2">
                                        <strong>Assigned Members:</strong>
                                        <div class="mt-1">
                                            @foreach(explode(',', $event->assigned_members) as $uid)
                                                @php $user = \App\Models\User::find($uid); @endphp
                                                @if($user)
                                                    <span class="badge bg-secondary me-1">{{ $user->first_name }} {{ $user->last_name }}</span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="btn-group w-100" role="group">
                                        <button class="btn btn-sm btn-outline-primary" onclick="viewEvent({{ $event->id }})">
                                            <i class="bi bi-eye"></i> View
                                        </button>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal{{ $event->id }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteEvent({{ $event->id }})">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle"></i> No events found.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Calendar View -->
    <div id="calendarView" class="view-container">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <button class="btn btn-outline-primary" onclick="changeMonth(-1)">
                        <i class="bi bi-chevron-left"></i> Previous
                    </button>
                    <h4 class="mb-0" id="currentMonthYear"></h4>
                    <button class="btn btn-outline-primary" onclick="changeMonth(1)">
                        Next <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center py-2">Sun</th>
                                <th class="text-center py-2">Mon</th>
                                <th class="text-center py-2">Tue</th>
                                <th class="text-center py-2">Wed</th>
                                <th class="text-center py-2">Thu</th>
                                <th class="text-center py-2">Fri</th>
                                <th class="text-center py-2">Sat</th>
                            </tr>
                        </thead>
                        <tbody id="calendarGrid">
                            <!-- Calendar days will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('events.store') }}" id="addForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="workspace_id" value="{{ session('workspace_id') }}">

                    <div class="mb-3">
                        <label class="form-label">Event Title</label>
                        <input class="form-control" name="title" placeholder="Enter event title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" placeholder="Enter event description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Event Type</label>
                        <select class="form-select" name="type" required>
                            <option value="">Select event type</option>
                            <option value="meeting">Meeting</option>
                            <option value="todo">To-Do</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Start Date & Time</label>
                            <input class="form-control" type="datetime-local" name="start_date_time" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date & Time</label>
                            <input class="form-control" type="datetime-local" name="end_date_time" required>
                        </div>
                    </div>

                    <div class="mb-3 mt-3">
                        <label class="form-label">Assign to Team Members:</label>
                        <div class="row">
                            @foreach($users as $u)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="assigned_members[]" value="{{ $u->id }}" id="user{{ $u->id }}">
                                        <label class="form-check-label" for="user{{ $u->id }}">
                                            {{ $u->first_name }} {{ $u->last_name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Event</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Event Modals -->
@foreach($events as $event)
    <div class="modal fade" id="editModal{{ $event->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('events.update', $event->id) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="id" value="{{ $event->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input class="form-control" name="title" value="{{ $event->title }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3">{{ $event->description }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type" required>
                                <option value="meeting" {{ $event->type == 'meeting' ? 'selected' : '' }}>Meeting</option>
                                <option value="todo" {{ $event->type == 'todo' ? 'selected' : '' }}>To-Do</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Start Date & Time</label>
                                <input class="form-control" type="datetime-local" name="start_date_time"
                                       value="{{ \Carbon\Carbon::parse($event->start_date_time)->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">End Date & Time</label>
                                <input class="form-control" type="datetime-local" name="end_date_time"
                                       value="{{ \Carbon\Carbon::parse($event->end_date_time)->format('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>
                        <div class="mb-3 mt-3">
                            <label class="form-label">Assign to:</label>
                            <div class="row">
                                @foreach($users as $u)
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="assigned_members[]"
                                                   value="{{ $u->id }}"
                                                   id="edit_user{{ $event->id }}_{{ $u->id }}"
                                                   {{ in_array($u->id, explode(',', $event->assigned_members)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="edit_user{{ $event->id }}_{{ $u->id }}">
                                                {{ $u->first_name }} {{ $u->last_name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Update Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Event Details Modal -->
<div class="modal fade" id="eventDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Event Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="eventDetailsBody">
                <!-- Event details will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="editEventBtn">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <button type="button" class="btn btn-danger" id="deleteEventBtn">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Event Context Menu -->
<div class="dropdown-menu position-absolute" id="eventContextMenu" style="display: none; z-index: 1050;">
    <a class="dropdown-item" href="#" onclick="viewEventFromMenu()">
        <i class="bi bi-eye"></i> View Details
    </a>
    <a class="dropdown-item" href="#" onclick="editEventFromMenu()">
        <i class="bi bi-pencil"></i> Edit Event
    </a>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item text-danger" href="#" onclick="deleteEventFromMenu()">
        <i class="bi bi-trash"></i> Delete Event
    </a>
</div>

@endsection

<script>
const events = @json($events);
const users = @json($users);
let currentDate = new Date();
let currentView = 'calendar';
let selectedEventId = null;

document.addEventListener('DOMContentLoaded', function() {
        const toastElList = document.querySelectorAll('.toast');
        toastElList.forEach(toastEl => {
            new bootstrap.Toast(toastEl, { delay: 4000 }).show();
    });
    initializeCalendar();
    updateEventCount();
    
    // Hide context menu on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#eventContextMenu')) {
            hideContextMenu();
        }
    });
});

function switchView(view) {
    currentView = view;
    
    // Update button states
    document.getElementById('listViewBtn').classList.remove('active');
    document.getElementById('calendarViewBtn').classList.remove('active');
    
    if (view === 'list') {
        document.getElementById('listViewBtn').classList.add('active');
        document.getElementById('listView').style.display = 'block';
        document.getElementById('calendarView').style.display = 'none';
    } else {
        document.getElementById('calendarViewBtn').classList.add('active');
        document.getElementById('listView').style.display = 'none';
        document.getElementById('calendarView').style.display = 'block';
        renderCalendar();
    }
}

function initializeCalendar() {
    renderCalendar();
}

function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];
    document.getElementById('currentMonthYear').textContent = `${monthNames[month]} ${year}`;
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();
    
    const calendarGrid = document.getElementById('calendarGrid');
    calendarGrid.innerHTML = '';
    
    // Calculate weeks needed
    const totalDays = Math.ceil((daysInMonth + startingDayOfWeek) / 7) * 7;
    
    let dayCount = 1;
    let nextMonthDay = 1;
    
    // Generate calendar rows
    for (let week = 0; week < Math.ceil(totalDays / 7); week++) {
        const row = document.createElement('tr');
        
        for (let day = 0; day < 7; day++) {
            const cell = document.createElement('td');
            cell.className = 'align-top position-relative';
            cell.style.height = '120px';
            cell.style.minWidth = '120px';
            
            const dayIndex = week * 7 + day;
            
            if (dayIndex < startingDayOfWeek) {
                // Previous month days
                const prevMonth = new Date(year, month - 1, 0);
                const prevDay = prevMonth.getDate() - startingDayOfWeek + dayIndex + 1;
                cell.className += ' table-secondary text-muted';
                cell.innerHTML = `<div class="fw-bold p-1">${prevDay}</div>`;
            } else if (dayCount <= daysInMonth) {
                // Current month days
                const today = new Date();
                const isToday = year === today.getFullYear() && 
                               month === today.getMonth() && 
                               dayCount === today.getDate();
                
                if (isToday) {
                    cell.className += ' table-info';
                }
                
                const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(dayCount).padStart(2, '0')}`;
                cell.setAttribute('data-date', dateStr);
                
                let cellContent = `<div class="fw-bold p-1">${dayCount}</div>`;
                
                // Add events for this day
                const dayEvents = getEventsForDay(year, month, dayCount);
                dayEvents.forEach(event => {
                    const eventBadge = createEventBadge(event);
                    cellContent += eventBadge;
                });
                
                cell.innerHTML = cellContent;
                dayCount++;
            } else {
                // Next month days
                cell.className += ' table-secondary text-muted';
                cell.innerHTML = `<div class="fw-bold p-1">${nextMonthDay}</div>`;
                nextMonthDay++;
            }
            
            row.appendChild(cell);
        }
        
        calendarGrid.appendChild(row);
    }
}

function createEventBadge(event) {
    const badgeClass = event.type === 'meeting' ? 'bg-success' : 'bg-warning text-dark';
    const truncatedTitle = event.title.length > 15 ? event.title.substring(0, 15) + '...' : event.title;
    
    return `
        <div class="badge ${badgeClass} d-block mb-1 text-start position-relative" 
             style="font-size: 0.7rem; cursor: pointer; user-select: none;"
             onclick="viewEvent(${event.id})"
             oncontextmenu="showEventContextMenu(event, ${event.id})"
             title="${event.title}">
            <i class="bi bi-${event.type === 'meeting' ? 'people' : 'check-circle'}"></i>
            ${truncatedTitle}
        </div>
    `;
}

function getEventsForDay(year, month, day) {
    return events.filter(event => {
        const eventStartDate = new Date(event.start_date_time);
        const eventEndDate = new Date(event.end_date_time);
        const checkDate = new Date(year, month, day);
        
        const eventStartDay = new Date(eventStartDate.getFullYear(), eventStartDate.getMonth(), eventStartDate.getDate());
        const eventEndDay = new Date(eventEndDate.getFullYear(), eventEndDate.getMonth(), eventEndDate.getDate());
        
        return checkDate >= eventStartDay && checkDate <= eventEndDay;
    });
}

function changeMonth(delta) {
    currentDate.setMonth(currentDate.getMonth() + delta);
    renderCalendar();
}

function viewEvent(eventId) {
    const event = events.find(e => e.id === eventId);
    if (!event) return;
    
    selectedEventId = eventId;
    
    const startDate = new Date(event.start_date_time);
    const endDate = new Date(event.end_date_time);
    
    const assignedUsers = event.assigned_members.split(',').map(uid => {
        const user = users.find(u => u.id == uid);
        return user ? `${user.first_name} ${user.last_name}` : '';
    }).filter(name => name).join(', ');
    
    const detailsHtml = `
        <div class="mb-3">
            <h6 class="text-primary">Title:</h6>
            <p class="mb-1">${event.title}</p>
        </div>
        <div class="mb-3">
            <h6 class="text-primary">Description:</h6>
            <p class="mb-1">${event.description || 'No description'}</p>
        </div>
        <div class="mb-3">
            <h6 class="text-primary">Type:</h6>
            <span class="badge bg-${event.type === 'meeting' ? 'success' : 'warning'}">
                ${event.type.charAt(0).toUpperCase() + event.type.slice(1)}
            </span>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <h6 class="text-primary">Start:</h6>
                <p class="mb-1">${startDate.toLocaleString()}</p>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary">End:</h6>
                <p class="mb-1">${endDate.toLocaleString()}</p>
            </div>
        </div>
        <div class="mb-3">
            <h6 class="text-primary">Assigned Members:</h6>
            <div>
                ${assignedUsers.split(', ').map(name => 
                    `<span class="badge bg-secondary me-1">${name}</span>`
                ).join('') || '<span class="text-muted">No members assigned</span>'}
            </div>
        </div>
    `;
    
    document.getElementById('eventDetailsBody').innerHTML = detailsHtml;
    
    // Update modal footer buttons
    document.getElementById('editEventBtn').onclick = () => editEventFromDetails(eventId);
    document.getElementById('deleteEventBtn').onclick = () => deleteEventFromDetails(eventId);
    
    new bootstrap.Modal(document.getElementById('eventDetailsModal')).show();
}

function editEventFromDetails(eventId) {
    // Close details modal
    const detailsModal = bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal'));
    detailsModal.hide();
    
    // Open edit modal
    setTimeout(() => {
        const editModal = new bootstrap.Modal(document.getElementById(`editModal${eventId}`));
        editModal.show();
    }, 300);
}

function deleteEventFromDetails(eventId) {
    if (confirm('Are you sure you want to delete this event?')) {
        deleteEvent(eventId);
        
        // Close details modal
        const detailsModal = bootstrap.Modal.getInstance(document.getElementById('eventDetailsModal'));
        detailsModal.hide();
    }
}

function deleteEvent(eventId) {
    // Create and submit a form for deletion
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/events/${eventId}`;
    form.style.display = 'none';
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken.getAttribute('content');
        form.appendChild(csrfInput);
    }
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    form.submit();
}

function showEventContextMenu(e, eventId) {
    e.preventDefault();
    e.stopPropagation();
    
    selectedEventId = eventId;
    const contextMenu = document.getElementById('eventContextMenu');
    
    contextMenu.style.display = 'block';
    contextMenu.style.left = e.pageX + 'px';
    contextMenu.style.top = e.pageY + 'px';
    
    // Adjust position if menu goes off screen
    setTimeout(() => {
        const rect = contextMenu.getBoundingClientRect();
        if (rect.right > window.innerWidth) {
            contextMenu.style.left = (e.pageX - rect.width) + 'px';
        }
        if (rect.bottom > window.innerHeight) {
            contextMenu.style.top = (e.pageY - rect.height) + 'px';
        }
    }, 0);
}

function hideContextMenu() {
    document.getElementById('eventContextMenu').style.display = 'none';
}

function viewEventFromMenu() {
    hideContextMenu();
    if (selectedEventId) {
        viewEvent(selectedEventId);
    }
}

function editEventFromMenu() {
    hideContextMenu();
    if (selectedEventId) {
        const editModal = new bootstrap.Modal(document.getElementById(`editModal${selectedEventId}`));
        editModal.show();
    }
}

function deleteEventFromMenu() {
    hideContextMenu();
    if (selectedEventId && confirm('Are you sure you want to delete this event?')) {
        deleteEvent(selectedEventId);
    }
}

function updateEventCount() {
    const totalEvents = document.querySelectorAll('.event-card').length;
    const countElement = document.getElementById('eventCount');
    if (countElement) {
        countElement.textContent = totalEvents;
    }
}

// Form validation
document.getElementById('addForm').addEventListener('submit', function(e) {
    const startDate = new Date(document.querySelector('input[name="start_date_time"]').value);
    const endDate = new Date(document.querySelector('input[name="end_date_time"]').value);
    
    if (endDate <= startDate) {
        e.preventDefault();
        alert('End date must be after start date');
        return false;
    }
});

// Auto-set end date when start date changes
document.querySelector('input[name="start_date_time"]').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDate = new Date(startDate.getTime() + 60 * 60 * 1000); // Add 1 hour
    const endInput = document.querySelector('input[name="end_date_time"]');
    
    if (!endInput.value) {
        endInput.value = endDate.toISOString().slice(0, 16);
    }
});

document.addEventListener('DOMContentLoaded', function() {


    switchView('calendar');
});
</script>