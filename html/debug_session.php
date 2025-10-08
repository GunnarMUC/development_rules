<?php
// Debug current session
session_start();
require_once 'includes/auth.php';

header('Content-Type: text/plain');

echo "Current Session Debug Info\n";
echo "==========================\n\n";

echo "Session ID: " . session_id() . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "User email: " . ($_SESSION['user']['email'] ?? 'NOT SET') . "\n";
echo "Current Team ID: " . ($_SESSION['current_team_id'] ?? 'NOT SET') . "\n";
echo "Is logged in: " . (is_logged_in() ? 'YES' : 'NO') . "\n\n";

if (isset($_SESSION['user'])) {
    echo "Full user data:\n";
    print_r($_SESSION['user']);
}

// Test API directly
echo "\n\nTesting API endpoint:\n";
echo "---------------------\n";

if (is_logged_in() && isset($_SESSION['user_id'])) {
    require_once 'classes/Task.php';
    $task = new Task();

    $request = [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'team_id' => $_SESSION['current_team_id'] ?? null
    ];

    $result = $task->getDataTableTasks($_SESSION['user_id'], $request);

    echo "Tasks returned for logged-in user:\n";
    echo "Total: " . $result['recordsTotal'] . "\n";
    echo "Filtered: " . $result['recordsFiltered'] . "\n";
    echo "Data count: " . count($result['data']) . "\n\n";

    if (count($result['data']) > 0) {
        echo "First 5 tasks:\n";
        $count = 0;
        foreach ($result['data'] as $task) {
            echo "  - ID " . $task['id'] . ": " . $task['title'] . "\n";
            $count++;
            if ($count >= 5) break;
        }
    }
} else {
    echo "Cannot test API - user not logged in\n";
}