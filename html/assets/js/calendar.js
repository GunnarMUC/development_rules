/**
 * Calendar Module JavaScript
 * Handles FullCalendar initialization and event management
 */

let calendar;
let currentFilter = 'all';
let currentEvent = null;

$(document).ready(function() {
    // Initialize calendar
    initializeCalendar();

    // Initialize Select2 for attendees
    initializeSelect2();

    // Bind event handlers
    bindEventHandlers();

    // Set minimum date for event creation
    setMinimumDates();
});

/**
 * Initialize FullCalendar
 */
function initializeCalendar() {
    const calendarEl = document.getElementById('calendar');

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        height: 'auto',
        editable: true,
        droppable: true,
        selectable: true,
        selectMirror: true,
        eventMaxStack: 3,

        // Event sources - both tasks and events
        eventSources: [
            {
                url: 'api/calendar.php',
                method: 'GET',
                extraParams: {
                    action: 'list'
                },
                failure: function() {
                    Swal.fire('Error', 'Failed to load calendar events', 'error');
                }
            }
        ],

        // Date click - create new event
        dateClick: function(info) {
            $('#eventModal').modal('show');
            $('#event_id').val('');
            $('#eventForm')[0].reset();
            $('#start_date').val(info.dateStr);
            $('#end_date').val(info.dateStr);
            $('#eventModalLabel').text('New Event');
        },

        // Event click - view/edit
        eventClick: function(info) {
            currentEvent = info.event;

            if (info.event.extendedProps.type === 'task') {
                // For tasks, show read-only view
                showTaskDetails(info.event);
            } else {
                // For events, show full details with edit options
                showEventDetails(info.event);
            }
        },

        // Event drag/resize
        eventDrop: function(info) {
            updateEventDateTime(info.event, info.revert);
        },

        eventResize: function(info) {
            updateEventDateTime(info.event, info.revert);
        },

        // Customize event display
        eventDidMount: function(info) {
            // Add tooltips
            $(info.el).tooltip({
                title: info.event.title + (info.event.extendedProps.location ? '\n📍 ' + info.event.extendedProps.location : ''),
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });

            // Add custom classes based on type
            if (info.event.extendedProps.type === 'task') {
                info.el.classList.add('event-task');
                if (info.event.extendedProps.overdue) {
                    info.el.classList.add('overdue');
                }
            } else {
                info.el.classList.add('event-' + info.event.extendedProps.type);
            }
        }
    });

    calendar.render();
}

/**
 * Initialize Select2 for attendees dropdown
 */
function initializeSelect2() {
    $('#event_attendees').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select attendees',
        allowClear: true,
        dropdownParent: $('#eventModal'),
        ajax: {
            url: 'api/teams.php',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    action: 'get_members',
                    search: params.term
                };
            },
            processResults: function(data) {
                if (data.success && data.members) {
                    return {
                        results: data.members.map(member => ({
                            id: member.user_id,
                            text: member.first_name + ' ' + member.last_name + ' (' + member.email + ')'
                        }))
                    };
                }
                return { results: [] };
            }
        }
    });
}

/**
 * Bind event handlers
 */
function bindEventHandlers() {
    // All day checkbox handler
    $('#all_day').on('change', function() {
        if ($(this).is(':checked')) {
            $('.time-inputs').hide();
            $('#start_time, #end_time').prop('required', false);
        } else {
            $('.time-inputs').show();
            $('#start_time, #end_time').prop('required', true);
        }
    });

    // Event form submission
    $('#eventForm').on('submit', function(e) {
        e.preventDefault();
        saveEvent();
    });

    // Filter buttons
    $('.filter-buttons button').on('click', function() {
        $('.filter-buttons button').removeClass('active');
        $(this).addClass('active');

        const filterId = $(this).attr('id');
        currentFilter = filterId.replace('filter-', '');
        applyFilter();
    });

    // Edit event button
    $('#editEventBtn').on('click', function() {
        if (currentEvent) {
            loadEventForEdit(currentEvent);
        }
    });

    // Delete event button
    $('#deleteEventBtn').on('click', function() {
        if (currentEvent && currentEvent.extendedProps.type !== 'task') {
            deleteEvent(currentEvent.id);
        }
    });

    // End date validation
    $('#start_date').on('change', function() {
        $('#end_date').attr('min', $(this).val());
        if ($('#end_date').val() < $(this).val()) {
            $('#end_date').val($(this).val());
        }
    });
}

/**
 * Set minimum dates for date inputs
 */
function setMinimumDates() {
    const today = new Date().toISOString().split('T')[0];
    $('#start_date').attr('min', today);
    $('#end_date').attr('min', today);
}

/**
 * Save event (create or update)
 */
function saveEvent() {
    const formData = $('#eventForm').serializeArray();
    const eventData = {};

    // Convert form data to object
    formData.forEach(item => {
        if (item.name === 'attendees[]') {
            if (!eventData.attendees) eventData.attendees = [];
            eventData.attendees.push(item.value);
        } else {
            eventData[item.name] = item.value;
        }
    });

    // Set action based on whether we're editing or creating
    eventData.action = eventData.event_id ? 'update' : 'create';

    // Handle all day events
    if ($('#all_day').is(':checked')) {
        eventData.all_day = true;
        eventData.start_time = '00:00';
        eventData.end_time = '23:59';
    }

    $.ajax({
        url: 'api/calendar.php',
        type: 'POST',
        data: eventData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#eventModal').modal('hide');
                $('#eventForm')[0].reset();
                calendar.refetchEvents();

                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: eventData.event_id ? 'Event updated successfully' : 'Event created successfully',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', response.message || 'Failed to save event', 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'An error occurred while saving the event', 'error');
        }
    });
}

/**
 * Update event date/time after drag or resize
 */
function updateEventDateTime(event, revertFunc) {
    const eventData = {
        action: 'update_datetime',
        event_id: event.id,
        start: event.start.toISOString(),
        end: event.end ? event.end.toISOString() : event.start.toISOString(),
        is_task: event.extendedProps.type === 'task'
    };

    $.ajax({
        url: 'api/calendar.php',
        type: 'POST',
        data: eventData,
        dataType: 'json',
        success: function(response) {
            if (!response.success) {
                revertFunc();
                Swal.fire('Error', response.message || 'Failed to update event', 'error');
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated',
                    text: 'Event rescheduled successfully',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        },
        error: function() {
            revertFunc();
            Swal.fire('Error', 'Failed to update event', 'error');
        }
    });
}

/**
 * Show task details (read-only)
 */
function showTaskDetails(task) {
    const details = `
        <div class="event-details">
            <div class="detail-row">
                <span class="detail-label">Type:</span>
                <span class="badge bg-success">Task</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Title:</span>
                <span>${task.title}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Due Date:</span>
                <span>${task.start.toLocaleDateString()}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span>${task.extendedProps.status || 'Pending'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Priority:</span>
                <span>${task.extendedProps.priority || 'Medium'}</span>
            </div>
            ${task.extendedProps.description ? `
            <div class="detail-row">
                <span class="detail-label">Description:</span>
                <span>${task.extendedProps.description}</span>
            </div>
            ` : ''}
        </div>
    `;

    $('#event-details-content').html(details);
    $('#editEventBtn, #deleteEventBtn').hide();
    $('#viewEventModal').modal('show');
}

/**
 * Show event details
 */
function showEventDetails(event) {
    $.ajax({
        url: 'api/calendar.php',
        type: 'GET',
        data: {
            action: 'get',
            event_id: event.id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.event) {
                const evt = response.event;
                const details = `
                    <div class="event-details">
                        <div class="detail-row">
                            <span class="detail-label">Type:</span>
                            <span class="badge bg-primary">${evt.type}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Title:</span>
                            <span>${evt.title}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Start:</span>
                            <span>${formatDateTime(evt.start_datetime)}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">End:</span>
                            <span>${formatDateTime(evt.end_datetime)}</span>
                        </div>
                        ${evt.location ? `
                        <div class="detail-row">
                            <span class="detail-label">Location:</span>
                            <span>${evt.location}</span>
                        </div>
                        ` : ''}
                        ${evt.description ? `
                        <div class="detail-row">
                            <span class="detail-label">Description:</span>
                            <span>${evt.description}</span>
                        </div>
                        ` : ''}
                        ${evt.attendees && evt.attendees.length > 0 ? `
                        <div class="detail-row">
                            <span class="detail-label">Attendees:</span>
                            <span>${evt.attendees.map(a => a.name).join(', ')}</span>
                        </div>
                        ` : ''}
                    </div>
                `;

                $('#event-details-content').html(details);
                $('#editEventBtn, #deleteEventBtn').show();
                $('#viewEventModal').modal('show');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to load event details', 'error');
        }
    });
}

/**
 * Load event for editing
 */
function loadEventForEdit(event) {
    $('#viewEventModal').modal('hide');

    $.ajax({
        url: 'api/calendar.php',
        type: 'GET',
        data: {
            action: 'get',
            event_id: event.id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.event) {
                const evt = response.event;

                // Populate form
                $('#event_id').val(evt.id);
                $('#event_title').val(evt.title);
                $('#event_type').val(evt.type);
                $('#event_location').val(evt.location);
                $('#event_description').val(evt.description);
                $('#event_color').val(evt.color);

                // Parse dates
                const startDate = new Date(evt.start_datetime);
                const endDate = new Date(evt.end_datetime);

                $('#start_date').val(startDate.toISOString().split('T')[0]);
                $('#end_date').val(endDate.toISOString().split('T')[0]);

                if (evt.all_day) {
                    $('#all_day').prop('checked', true);
                    $('.time-inputs').hide();
                } else {
                    $('#all_day').prop('checked', false);
                    $('.time-inputs').show();
                    $('#start_time').val(startDate.toTimeString().slice(0, 5));
                    $('#end_time').val(endDate.toTimeString().slice(0, 5));
                }

                // Set attendees
                if (evt.attendees && evt.attendees.length > 0) {
                    const attendeeIds = evt.attendees.map(a => a.user_id);
                    $('#event_attendees').val(attendeeIds).trigger('change');
                }

                $('#eventModalLabel').text('Edit Event');
                $('#eventModal').modal('show');
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to load event for editing', 'error');
        }
    });
}

/**
 * Delete event
 */
function deleteEvent(eventId) {
    Swal.fire({
        title: 'Delete Event?',
        text: 'Are you sure you want to delete this event?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'api/calendar.php',
                type: 'POST',
                data: {
                    action: 'delete',
                    event_id: eventId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#viewEventModal').modal('hide');
                        calendar.refetchEvents();

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Event has been deleted.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Failed to delete event', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to delete event', 'error');
                }
            });
        }
    });
}

/**
 * Apply calendar filter
 */
function applyFilter() {
    // Remove all event sources
    calendar.getEventSources().forEach(source => source.remove());

    // Add filtered event source
    calendar.addEventSource({
        url: 'api/calendar.php',
        method: 'GET',
        extraParams: {
            action: 'list',
            filter: currentFilter
        },
        failure: function() {
            Swal.fire('Error', 'Failed to load calendar events', 'error');
        }
    });
}

/**
 * Format date time for display
 */
function formatDateTime(datetime) {
    const date = new Date(datetime);
    const options = {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleString('en-US', options);
}