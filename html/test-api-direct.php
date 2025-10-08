<?php
session_start();
require_once 'includes/auth.php';
require_once 'classes/Task.php';

// Check if user is logged in
if (!is_logged_in()) {
    die("Not logged in. Please login first.");
}

$userId = $_SESSION['user_id'];
$teamId = $_SESSION['current_team_id'] ?? null;

echo "<h2>Direct API Test for Kanban Tasks</h2>";
echo "<pre>";
echo "User ID: $userId\n";
echo "Team ID: " . ($teamId !== null ? $teamId : 'NULL') . "\n";
echo "Session data:\n";
print_r($_SESSION);
echo "\n";

$task = new Task();

// Test 1: Get tasks without team filter
echo "=== Test 1: Get tasks WITHOUT team filter ===\n";
$tasksNoTeam = $task->getUserTasks($userId, []);
echo "Tasks found (no team filter): " . count($tasksNoTeam) . "\n";
foreach ($tasksNoTeam as $t) {
    echo "  - {$t['title']} (Team: " . ($t['team_id'] ?? 'NULL') . ", Status: {$t['status']})\n";
}

// Test 2: Get tasks with team filter
echo "\n=== Test 2: Get tasks WITH team filter ===\n";
if ($teamId !== null) {
    $tasksWithTeam = $task->getUserTasks($userId, ['team_id' => $teamId]);
    echo "Tasks found (team_id = $teamId): " . count($tasksWithTeam) . "\n";
    foreach ($tasksWithTeam as $t) {
        echo "  - {$t['title']} (Team: {$t['team_id']}, Status: {$t['status']})\n";
    }
} else {
    echo "No team_id in session, skipping team filter test\n";
}

// Test 3: Simulate kanban API call
echo "\n=== Test 3: Simulating Kanban API Call ===\n";
$filters = [];
if ($teamId !== null) {
    $filters['team_id'] = $teamId;
}

$tasks = $task->getUserTasks($userId, $filters);
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
        'status' => $kanbanStatus,
        'priority' => $t['priority'],
        'team_id' => $t['team_id']
    ];
}

echo "Kanban tasks prepared: " . count($kanbanTasks) . "\n";
foreach ($kanbanTasks as $kt) {
    echo "  - {$kt['title']} (Status: {$kt['status']}, Team: " . ($kt['team_id'] ?? 'NULL') . ")\n";
}

echo "\n=== Final JSON Response (as API would return) ===\n";
$response = [
    'success' => true,
    'tasks' => $kanbanTasks
];
echo json_encode($response, JSON_PRETTY_PRINT);

echo "</pre>";
?>