@extends('layouts.app')

@section('content')
<div class="calendar-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div class="calendar-nav" style="display: flex; gap: 10px; align-items: center;">
        <button class="btn btn-outline-secondary" onclick="navigateCalendar('prev')">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="btn btn-outline-secondary" onclick="navigateCalendar('today')">
            Today
        </button>
        <button class="btn btn-outline-secondary" onclick="navigateCalendar('next')">
            <i class="fas fa-chevron-right"></i>
        </button>
        <h4 id="calendar-title" style="margin: 0 20px;"></h4>
    </div>
    
    <div class="calendar-controls" style="display: flex; gap: 10px;">
        <div class="btn-group" role="group">
            <button class="btn btn-outline-primary" onclick="changeView('month')">Month</button>
            <button class="btn btn-outline-primary" onclick="changeView('week')">Week</button>
            <button class="btn btn-outline-primary" onclick="changeView('day')">Day</button>
        </div>
        <button class="btn btn-success" onclick="openEventModal()">
            <i class="fas fa-plus"></i> Add Event
        </button>
    </div>
</div>

<div id="calendar" style="background: var(--bg-secondary); border-radius: 5px; padding: 20px;"></div>

<!-- Event Modal -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalTitle">Add Event</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="eventForm">
                <div class="modal-body">
                    <input type="hidden" id="event-id" name="event_id">
                    
                    <div class="form-group">
                        <label for="event-title">Title *</label>
                        <input type="text" class="form-control" id="event-title" name="title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="event-description">Description</label>
                        <textarea class="form-control" id="event-description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="event-start">Start Date/Time *</label>
                                <input type="datetime-local" class="form-control" id="event-start" name="start_datetime" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="event-end">End Date/Time</label>
                                <input type="datetime-local" class="form-control" id="event-end" name="end_datetime">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="event-comment">Comment</label>
                        <textarea class="form-control" id="event-comment" name="comment" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="delete-event-btn" onclick="deleteEvent()" style="display: none; margin-right: auto;">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Event</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />
<style>
.fc-event {
    cursor: pointer;
    border: none !important;
    background: #6f42c1 !important;
}

.fc-event:hover {
    background: #5a2d91 !important;
}

.fc-daygrid-day:hover {
    background-color: rgba(111, 66, 193, 0.05);
}

.fc-timegrid-slot:hover {
    background-color: rgba(111, 66, 193, 0.05);
}

.fc-highlight {
    background-color: rgba(111, 66, 193, 0.1) !important;
}

.theme-dark .fc {
    color: var(--text-primary);
}

.theme-dark .fc-theme-standard td,
.theme-dark .fc-theme-standard th {
    border-color: #333;
}

.theme-dark .fc-theme-standard .fc-scrollgrid {
    border-color: #333;
}
</style>
@endsection

@section('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.js'></script>
<script>
let calendar;
let currentEventId = null;

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: false, // We're using our custom header
        height: 'auto',
        editable: true,
        droppable: true,
        eventResizableFromStart: true,
        
        events: function(fetchInfo, successCallback, failureCallback) {
            fetch(`/calendar/events?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
        },
        
        eventClick: function(info) {
            editEvent(info.event);
        },
        
        dateClick: function(info) {
            openEventModal(info.dateStr);
        },
        
        eventDrop: function(info) {
            updateEventTime(info.event.id, info.event.start);
        },
        
        eventResize: function(info) {
            updateEventTime(info.event.id, info.event.start, info.event.end);
        },
        
        datesSet: function(dateInfo) {
            updateCalendarTitle();
        }
    });

    calendar.render();
    updateCalendarTitle();
});

function updateCalendarTitle() {
    const view = calendar.view;
    const date = view.currentStart;
    let title = '';
    
    if (view.type === 'dayGridMonth') {
        title = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
    } else if (view.type === 'timeGridWeek') {
        const endDate = new Date(date);
        endDate.setDate(endDate.getDate() + 6);
        title = `${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
    } else if (view.type === 'timeGridDay') {
        title = date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
    }
    
    document.getElementById('calendar-title').textContent = title;
}

function navigateCalendar(action) {
    switch(action) {
        case 'prev':
            calendar.prev();
            break;
        case 'next':
            calendar.next();
            break;
        case 'today':
            calendar.today();
            break;
    }
    updateCalendarTitle();
}

function changeView(view) {
    const viewMap = {
        'month': 'dayGridMonth',
        'week': 'timeGridWeek',
        'day': 'timeGridDay'
    };
    calendar.changeView(viewMap[view]);
    updateCalendarTitle();
}

function openEventModal(date = null) {
    currentEventId = null;
    document.getElementById('eventModalTitle').textContent = 'Add Event';
    document.getElementById('eventForm').reset();
    document.getElementById('event-id').value = '';
    document.getElementById('delete-event-btn').style.display = 'none';
    
    if (date) {
        const eventDate = new Date(date);
        const localDate = new Date(eventDate.getTime() - eventDate.getTimezoneOffset() * 60000);
        document.getElementById('event-start').value = localDate.toISOString().slice(0, 16);
    }
    
    $('#eventModal').modal('show');
}

function editEvent(event) {
    currentEventId = event.id;
    document.getElementById('eventModalTitle').textContent = 'Edit Event';
    document.getElementById('event-id').value = event.id;
    document.getElementById('event-title').value = event.title;
    document.getElementById('event-description').value = event.extendedProps.description || '';
    document.getElementById('event-comment').value = event.extendedProps.comment || '';
    document.getElementById('delete-event-btn').style.display = 'inline-block';
    
    const startDate = new Date(event.start);
    const localStartDate = new Date(startDate.getTime() - startDate.getTimezoneOffset() * 60000);
    document.getElementById('event-start').value = localStartDate.toISOString().slice(0, 16);
    
    if (event.end) {
        const endDate = new Date(event.end);
        const localEndDate = new Date(endDate.getTime() - endDate.getTimezoneOffset() * 60000);
        document.getElementById('event-end').value = localEndDate.toISOString().slice(0, 16);
    }
    
    $('#eventModal').modal('show');
}

function updateEventTime(eventId, start, end = null) {
    const data = {
        _token: '{{ csrf_token() }}',
        start_datetime: start.toISOString()
    };
    
    if (end) {
        data.end_datetime = end.toISOString();
    }
    
    fetch(`/calendar/events/${eventId}/move`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to update event');
            calendar.refetchEvents();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update event');
        calendar.refetchEvents();
    });
}

function deleteEvent() {
    if (!currentEventId) return;
    
    if (confirm('Are you sure you want to delete this event?')) {
        fetch(`/calendar/events/${currentEventId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#eventModal').modal('hide');
                calendar.refetchEvents();
            } else {
                alert('Failed to delete event');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete event');
        });
    }
}

// Handle form submission
document.getElementById('eventForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    data._token = '{{ csrf_token() }}';
    
    const url = currentEventId 
        ? `/calendar/events/${currentEventId}`
        : '/calendar/events';
    
    const method = currentEventId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#eventModal').modal('hide');
            calendar.refetchEvents();
            
            // Handle redirect preference
            if (data.redirect && data.redirect.date) {
                calendar.gotoDate(data.redirect.date);
            }
        } else {
            alert('Failed to save event');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save event');
    });
});
</script>
@endsection