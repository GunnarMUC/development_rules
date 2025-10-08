<?php
/**
 * Calendar API Endpoint
 * Handles calendar events and task integration
 */

session_start();
require_once '../config/database.php';
require_once '../includes/auth.php';

// Check authentication
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? 'list';

// Get current user and team
$user_id = $_SESSION['user_id'];
$team_id = $_SESSION['current_team_id'] ?? null;

// Ensure team_id is set
if (!$team_id) {
    // Try to get user's team from database
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT team_id FROM team_members WHERE user_id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        $membership = $stmt->fetch();

        if ($membership) {
            $team_id = $membership['team_id'];
            $_SESSION['current_team_id'] = $team_id;
        } else {
            // Create or get default team
            $stmt = $db->query("SELECT id FROM teams ORDER BY id ASC LIMIT 1");
            $team = $stmt->fetch();

            if ($team) {
                $team_id = $team['id'];
            } else {
                // Create a default team
                $stmt = $db->prepare("INSERT INTO teams (name, created_by) VALUES ('Default Team', ?)");
                $stmt->execute([$user_id]);
                $team_id = $db->lastInsertId();
            }

            // Add user to team
            $stmt = $db->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, 'member')
                                 ON DUPLICATE KEY UPDATE team_id=team_id");
            $stmt->execute([$team_id, $user_id]);

            $_SESSION['current_team_id'] = $team_id;
        }
    } catch (Exception $e) {
        // If still no team_id, return error
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unable to determine team context']);
        exit;
    }
}

// Initialize response
$response = ['success' => false];

try {
    $db = getDB();

    switch ($action) {
        case 'list':
            // Get calendar events and tasks
            $filter = $_GET['filter'] ?? 'all';
            $start = $_GET['start'] ?? null;
            $end = $_GET['end'] ?? null;

            $events = [];

            // Get tasks with due dates (if not filtering events only)
            if ($filter !== 'events') {
                $taskQuery = "SELECT
                    t.id,
                    t.title,
                    t.description,
                    t.due_date,
                    t.status,
                    t.priority,
                    u.first_name,
                    u.last_name
                FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE t.team_id = :team_id
                AND t.due_date IS NOT NULL";

                if ($filter === 'my-events') {
                    $taskQuery .= " AND (t.user_id = :user_id OR t.assigned_to = :user_id2)";
                }

                $stmt = $db->prepare($taskQuery);
                $stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);

                if ($filter === 'my-events') {
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(':user_id2', $user_id, PDO::PARAM_INT);
                }

                $stmt->execute();
                $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($tasks as $task) {
                    $isOverdue = strtotime($task['due_date']) < strtotime('today');
                    $assignee = $task['first_name'] ? $task['first_name'] . ' ' . $task['last_name'] : 'Unassigned';

                    $events[] = [
                        'id' => 'task_' . $task['id'],
                        'title' => '📋 ' . $task['title'] . ' (' . $assignee . ')',
                        'start' => $task['due_date'],
                        'allDay' => true,
                        'backgroundColor' => $isOverdue ? '#dc3545' : '#28a745',
                        'borderColor' => $isOverdue ? '#dc3545' : '#28a745',
                        'extendedProps' => [
                            'type' => 'task',
                            'status' => $task['status'],
                            'priority' => $task['priority'],
                            'description' => $task['description'],
                            'overdue' => $isOverdue
                        ]
                    ];
                }
            }

            // Get calendar events (if not filtering tasks only)
            if ($filter !== 'tasks') {
                $eventQuery = "SELECT
                    e.id,
                    e.title,
                    e.description,
                    e.location,
                    e.start_datetime,
                    e.end_datetime,
                    e.all_day,
                    e.color,
                    e.type,
                    e.status,
                    e.created_by
                FROM events e
                WHERE e.team_id = :team_id
                AND e.status != 'cancelled'";

                if ($filter === 'my-events') {
                    $eventQuery .= " AND (e.created_by = :user_id OR e.id IN (
                        SELECT event_id FROM event_attendees WHERE user_id = :user_id2
                    ))";
                }

                $stmt = $db->prepare($eventQuery);
                $stmt->bindParam(':team_id', $team_id, PDO::PARAM_INT);

                if ($filter === 'my-events') {
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(':user_id2', $user_id, PDO::PARAM_INT);
                }

                $stmt->execute();
                $calendarEvents = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($calendarEvents as $event) {
                    $eventIcon = '';
                    switch ($event['type']) {
                        case 'meeting': $eventIcon = '🤝 '; break;
                        case 'appointment': $eventIcon = '📅 '; break;
                        case 'reminder': $eventIcon = '🔔 '; break;
                        default: $eventIcon = '📌 '; break;
                    }

                    $events[] = [
                        'id' => $event['id'],
                        'title' => $eventIcon . $event['title'],
                        'start' => $event['start_datetime'],
                        'end' => $event['end_datetime'],
                        'allDay' => (bool)$event['all_day'],
                        'backgroundColor' => $event['color'],
                        'borderColor' => $event['color'],
                        'extendedProps' => [
                            'type' => $event['type'],
                            'location' => $event['location'],
                            'description' => $event['description'],
                            'status' => $event['status']
                        ]
                    ];
                }
            }

            // Return just the events array for FullCalendar compatibility
            $response = $events;
            break;

        case 'get':
            // Get single event details
            $event_id = $_GET['event_id'] ?? null;

            if (!$event_id) {
                throw new Exception('Event ID is required');
            }

            $stmt = $db->prepare("
                SELECT e.*, u.first_name as creator_first, u.last_name as creator_last
                FROM events e
                JOIN users u ON e.created_by = u.id
                WHERE e.id = :event_id AND e.team_id = :team_id
            ");
            $stmt->execute([':event_id' => $event_id, ':team_id' => $team_id]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event) {
                throw new Exception('Event not found');
            }

            // Get attendees
            $stmt = $db->prepare("
                SELECT ea.*, u.first_name, u.last_name, u.email
                FROM event_attendees ea
                JOIN users u ON ea.user_id = u.id
                WHERE ea.event_id = :event_id
            ");
            $stmt->execute([':event_id' => $event_id]);
            $attendees = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $event['attendees'] = array_map(function($a) {
                return [
                    'user_id' => $a['user_id'],
                    'name' => $a['first_name'] . ' ' . $a['last_name'],
                    'email' => $a['email'],
                    'response_status' => $a['response_status']
                ];
            }, $attendees);

            $response = ['success' => true, 'event' => $event];
            break;

        case 'create':
            // Create new event
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $title = $_POST['title'] ?? '';
            $type = $_POST['type'] ?? 'event';
            $description = $_POST['description'] ?? '';
            $location = $_POST['location'] ?? '';
            $start_date = $_POST['start_date'] ?? '';
            $start_time = $_POST['start_time'] ?? '09:00';
            $end_date = $_POST['end_date'] ?? '';
            $end_time = $_POST['end_time'] ?? '10:00';
            $all_day = isset($_POST['all_day']) && $_POST['all_day'] ? 1 : 0;
            $color = $_POST['color'] ?? '#007bff';
            $attendees = $_POST['attendees'] ?? [];

            if (empty($title) || empty($start_date) || empty($end_date)) {
                throw new Exception('Title, start date, and end date are required');
            }

            // Format datetime
            $start_datetime = $start_date . ' ' . ($all_day ? '00:00:00' : $start_time . ':00');
            $end_datetime = $end_date . ' ' . ($all_day ? '23:59:59' : $end_time . ':00');

            // Insert event
            $stmt = $db->prepare("
                INSERT INTO events (team_id, created_by, title, description, location,
                                  start_datetime, end_datetime, all_day, color, type)
                VALUES (:team_id, :user_id, :title, :description, :location,
                        :start_datetime, :end_datetime, :all_day, :color, :type)
            ");

            $stmt->execute([
                ':team_id' => $team_id,
                ':user_id' => $user_id,
                ':title' => $title,
                ':description' => $description,
                ':location' => $location,
                ':start_datetime' => $start_datetime,
                ':end_datetime' => $end_datetime,
                ':all_day' => $all_day,
                ':color' => $color,
                ':type' => $type
            ]);

            $event_id = $db->lastInsertId();

            // Add attendees
            if (!empty($attendees)) {
                foreach ($attendees as $attendee_id) {
                    $stmt = $db->prepare("
                        INSERT INTO event_attendees (event_id, user_id, is_organizer)
                        VALUES (:event_id, :user_id, :is_organizer)
                    ");
                    $stmt->execute([
                        ':event_id' => $event_id,
                        ':user_id' => $attendee_id,
                        ':is_organizer' => ($attendee_id == $user_id) ? 1 : 0
                    ]);
                }
            }

            // Always add creator as organizer if not in attendees
            if (!in_array($user_id, $attendees)) {
                $stmt = $db->prepare("
                    INSERT INTO event_attendees (event_id, user_id, is_organizer, response_status)
                    VALUES (:event_id, :user_id, 1, 'accepted')
                ");
                $stmt->execute([':event_id' => $event_id, ':user_id' => $user_id]);
            }

            $response = ['success' => true, 'message' => 'Event created successfully', 'event_id' => $event_id];
            break;

        case 'update':
            // Update existing event
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $event_id = $_POST['event_id'] ?? null;

            if (!$event_id) {
                throw new Exception('Event ID is required');
            }

            // Check if user can edit
            $stmt = $db->prepare("SELECT created_by FROM events WHERE id = :id AND team_id = :team_id");
            $stmt->execute([':id' => $event_id, ':team_id' => $team_id]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event || $event['created_by'] != $user_id) {
                throw new Exception('You do not have permission to edit this event');
            }

            // Update event
            $title = $_POST['title'] ?? '';
            $type = $_POST['type'] ?? 'event';
            $description = $_POST['description'] ?? '';
            $location = $_POST['location'] ?? '';
            $start_date = $_POST['start_date'] ?? '';
            $start_time = $_POST['start_time'] ?? '09:00';
            $end_date = $_POST['end_date'] ?? '';
            $end_time = $_POST['end_time'] ?? '10:00';
            $all_day = isset($_POST['all_day']) && $_POST['all_day'] ? 1 : 0;
            $color = $_POST['color'] ?? '#007bff';

            $start_datetime = $start_date . ' ' . ($all_day ? '00:00:00' : $start_time . ':00');
            $end_datetime = $end_date . ' ' . ($all_day ? '23:59:59' : $end_time . ':00');

            $stmt = $db->prepare("
                UPDATE events
                SET title = :title, description = :description, location = :location,
                    start_datetime = :start_datetime, end_datetime = :end_datetime,
                    all_day = :all_day, color = :color, type = :type
                WHERE id = :id
            ");

            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':location' => $location,
                ':start_datetime' => $start_datetime,
                ':end_datetime' => $end_datetime,
                ':all_day' => $all_day,
                ':color' => $color,
                ':type' => $type,
                ':id' => $event_id
            ]);

            // Update attendees
            if (isset($_POST['attendees'])) {
                // Remove existing attendees
                $stmt = $db->prepare("DELETE FROM event_attendees WHERE event_id = :event_id");
                $stmt->execute([':event_id' => $event_id]);

                // Add new attendees
                $attendees = $_POST['attendees'] ?? [];
                foreach ($attendees as $attendee_id) {
                    $stmt = $db->prepare("
                        INSERT INTO event_attendees (event_id, user_id, is_organizer)
                        VALUES (:event_id, :user_id, :is_organizer)
                    ");
                    $stmt->execute([
                        ':event_id' => $event_id,
                        ':user_id' => $attendee_id,
                        ':is_organizer' => ($attendee_id == $user_id) ? 1 : 0
                    ]);
                }

                // Always add creator as organizer
                if (!in_array($user_id, $attendees)) {
                    $stmt = $db->prepare("
                        INSERT INTO event_attendees (event_id, user_id, is_organizer, response_status)
                        VALUES (:event_id, :user_id, 1, 'accepted')
                    ");
                    $stmt->execute([':event_id' => $event_id, ':user_id' => $user_id]);
                }
            }

            $response = ['success' => true, 'message' => 'Event updated successfully'];
            break;

        case 'update_datetime':
            // Update event datetime (from drag/drop)
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $event_id = $_POST['event_id'] ?? null;
            $start = $_POST['start'] ?? null;
            $end = $_POST['end'] ?? null;
            $is_task = $_POST['is_task'] ?? false;

            if (!$event_id || !$start) {
                throw new Exception('Event ID and start date are required');
            }

            if ($is_task) {
                // Update task due date
                $task_id = str_replace('task_', '', $event_id);
                $due_date = date('Y-m-d', strtotime($start));

                $stmt = $db->prepare("
                    UPDATE tasks
                    SET due_date = :due_date
                    WHERE id = :id AND team_id = :team_id
                ");
                $stmt->execute([
                    ':due_date' => $due_date,
                    ':id' => $task_id,
                    ':team_id' => $team_id
                ]);
            } else {
                // Check permission
                $stmt = $db->prepare("SELECT created_by FROM events WHERE id = :id AND team_id = :team_id");
                $stmt->execute([':id' => $event_id, ':team_id' => $team_id]);
                $event = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$event || $event['created_by'] != $user_id) {
                    throw new Exception('You do not have permission to edit this event');
                }

                // Update event datetime
                $stmt = $db->prepare("
                    UPDATE events
                    SET start_datetime = :start_datetime, end_datetime = :end_datetime
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':start_datetime' => date('Y-m-d H:i:s', strtotime($start)),
                    ':end_datetime' => date('Y-m-d H:i:s', strtotime($end ?? $start)),
                    ':id' => $event_id
                ]);
            }

            $response = ['success' => true, 'message' => 'Updated successfully'];
            break;

        case 'delete':
            // Delete event
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $event_id = $_POST['event_id'] ?? null;

            if (!$event_id) {
                throw new Exception('Event ID is required');
            }

            // Check permission
            $stmt = $db->prepare("SELECT created_by FROM events WHERE id = :id AND team_id = :team_id");
            $stmt->execute([':id' => $event_id, ':team_id' => $team_id]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$event || $event['created_by'] != $user_id) {
                throw new Exception('You do not have permission to delete this event');
            }

            // Delete event (attendees will be deleted by cascade)
            $stmt = $db->prepare("DELETE FROM events WHERE id = :id");
            $stmt->execute([':id' => $event_id]);

            $response = ['success' => true, 'message' => 'Event deleted successfully'];
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
    error_log('Calendar API Error: ' . $e->getMessage());
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);