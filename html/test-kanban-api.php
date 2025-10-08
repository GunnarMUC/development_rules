<?php
session_start();
require_once 'includes/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    die("You must be logged in to test this.");
}

// Test direct API call
$userId = $_SESSION['user_id'];
$teamId = $_SESSION['current_team_id'] ?? null;

require_once 'classes/Task.php';
$task = new Task();

$filters = [];
if ($teamId !== null) {
    $filters['team_id'] = $teamId;
}

$tasks = $task->getUserTasks($userId, $filters);

// Map database statuses to kanban columns
$kanbanTasks = [];
foreach ($tasks as $t) {
    $kanbanStatus = $t['status'];
    if ($t['status'] === 'pending') {
        $kanbanStatus = 'todo';
    } elseif ($t['status'] === 'completed') {
        $kanbanStatus = 'done';
    }

    $kanbanTasks[] = [
        'id' => $t['id'],
        'title' => $t['title'],
        'description' => $t['description'],
        'status' => $kanbanStatus,
        'priority' => $t['priority'],
        'due_date' => $t['due_date'],
        'assigned_to' => $t['assigned_to'],
        'assigned_name' => $t['assigned_first_name'] ?
            $t['assigned_first_name'] . ' ' . $t['assigned_last_name'] : null,
        'tags' => []
    ];
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'tasks' => $kanbanTasks,
    'debug' => [
        'user_id' => $userId,
        'team_id' => $teamId,
        'raw_task_count' => count($tasks),
        'kanban_task_count' => count($kanbanTasks)
    ]
]);
?>