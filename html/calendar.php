<?php
session_start();
require_once 'includes/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$page_title = 'Calendar';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - SaaS Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <style>
        /* Calendar Container */
        #calendar-container {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
        }

        /* FullCalendar Customization */
        .fc {
            font-family: inherit;
        }

        .fc-event {
            cursor: pointer;
            padding: 2px 4px;
            margin-bottom: 1px;
            border: none;
        }

        /* Task Events (from todo list) */
        .fc-event.event-task {
            background-color: #28a745;
            border-color: #28a745;
        }

        .fc-event.event-task.overdue {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        /* Calendar Events */
        .fc-event.event-meeting {
            background-color: #007bff;
            border-color: #007bff;
        }

        .fc-event.event-appointment {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .fc-event.event-reminder {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }

        /* Today highlight */
        .fc-day-today {
            background-color: rgba(255, 193, 7, 0.1);
        }

        /* Toolbar customization */
        .fc-toolbar-title {
            font-size: 1.5rem;
            font-weight: 500;
        }

        .fc-button-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .fc-button-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .fc-button-primary:disabled {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        /* Event time display */
        .fc-event-time {
            font-weight: 500;
            font-size: 0.85em;
        }

        /* Legend */
        .calendar-legend {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 3px;
        }

        /* Filter buttons */
        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        /* Event details in popover */
        .event-details {
            padding: 0.5rem;
        }

        .event-details .detail-row {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .event-details .detail-label {
            font-weight: 500;
            width: 80px;
        }

        /* Quick add button */
        .quick-add-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Main Content Column - Full Width -->
            <div class="col-12" id="main-content">
                <div class="py-4 mt-4">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Calendar</li>
                    </ol>
                </nav>

                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4" id="page-header">
                    <h1 class="h3 mb-0">Calendar</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#eventModal">
                        <i class="bi bi-plus-circle"></i> New Event
                    </button>
                </div>

                <!-- Calendar Legend and Filters -->
                <div class="mb-3" id="calendar-controls">
                    <div class="calendar-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #28a745;"></div>
                            <span>Tasks</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #007bff;"></div>
                            <span>Meetings</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #17a2b8;"></div>
                            <span>Appointments</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #ffc107;"></div>
                            <span>Reminders</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background-color: #dc3545;"></div>
                            <span>Overdue Tasks</span>
                        </div>
                    </div>

                    <div class="filter-buttons">
                        <button class="btn btn-sm btn-outline-secondary active" id="filter-all">
                            <i class="bi bi-funnel"></i> All
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="filter-tasks">
                            <i class="bi bi-check-square"></i> Tasks Only
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="filter-events">
                            <i class="bi bi-calendar-event"></i> Events Only
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" id="filter-my-events">
                            <i class="bi bi-person"></i> My Events
                        </button>
                    </div>
                </div>

                <!-- Calendar Container -->
                <div id="calendar-container">
                    <div id="calendar"></div>
                </div>

                <!-- Quick Add Button -->
                <button class="btn btn-primary quick-add-btn" data-bs-toggle="modal" data-bs-target="#eventModal">
                    <i class="bi bi-plus-lg fs-4"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="eventForm">
                    <div class="modal-body">
                        <input type="hidden" id="event_id" name="event_id">

                        <div class="mb-3">
                            <label for="event_title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="event_title" name="title" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="event_type" class="form-label">Type</label>
                                <select class="form-select" id="event_type" name="type">
                                    <option value="event">Event</option>
                                    <option value="meeting">Meeting</option>
                                    <option value="appointment">Appointment</option>
                                    <option value="reminder">Reminder</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="event_location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="event_location" name="location" placeholder="Optional">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="all_day" name="all_day">
                                <label class="form-check-label" for="all_day">
                                    All day event
                                </label>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="start_date" class="form-label">Start Date *</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="col-md-6 time-inputs">
                                <label for="start_time" class="form-label">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="end_date" class="form-label">End Date *</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                            <div class="col-md-6 time-inputs">
                                <label for="end_time" class="form-label">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="event_attendees" class="form-label">Attendees</label>
                            <select class="form-select" id="event_attendees" name="attendees[]" multiple>
                                <!-- Will be populated via AJAX -->
                            </select>
                            <small class="text-muted">Select team members to invite</small>
                        </div>

                        <div class="mb-3">
                            <label for="event_description" class="form-label">Description</label>
                            <textarea class="form-control" id="event_description" name="description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="event_color" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color" id="event_color" name="color" value="#007bff">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Event Modal -->
    <div class="modal fade" id="viewEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="event-details-content">
                    <!-- Content will be populated dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="editEventBtn">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button type="button" class="btn btn-danger" id="deleteEventBtn">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Calendar JavaScript -->
    <script src="assets/js/calendar.js"></script>
</body>
</html>