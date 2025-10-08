<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    die("Not logged in. Please login first.");
}

$userId = $_SESSION['user_id'];
$teamId = $_SESSION['current_team_id'] ?? null;

echo "<h2>Debug Information for Kanban Board</h2>";
echo "<pre>";
echo "User ID: $userId\n";
echo "Team ID: " . ($teamId ?? 'NULL') . "\n\n";

try {
    $conn = getDbConnection();

    // Check tasks for the user
    echo "=== Tasks in Database ===\n";
    $stmt = $conn->prepare("
        SELECT t.*,
               u1.first_name as created_by_name,
               u2.first_name as assigned_to_name
        FROM tasks t
        LEFT JOIN users u1 ON t.user_id = u1.id
        LEFT JOIN users u2 ON t.assigned_to = u2.id
        WHERE (t.user_id = ? OR t.assigned_to = ?)
        " . ($teamId ? "AND t.team_id = ?" : "") . "
        ORDER BY t.created_at DESC
        LIMIT 20
    ");

    if ($teamId) {
        $stmt->bind_param("iii", $userId, $userId, $teamId);
    } else {
        $stmt->bind_param("ii", $userId, $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    echo "Total tasks found: " . $result->num_rows . "\n\n";

    while ($task = $result->fetch_assoc()) {
        echo "Task ID: {$task['id']}\n";
        echo "  Title: {$task['title']}\n";
        echo "  Status: {$task['status']}\n";
        echo "  Priority: {$task['priority']}\n";
        echo "  Created by: {$task['created_by_name']} (ID: {$task['user_id']})\n";
        echo "  Assigned to: " . ($task['assigned_to_name'] ?? 'Unassigned') . " (ID: " . ($task['assigned_to'] ?? 'NULL') . ")\n";
        echo "  Team ID: " . ($task['team_id'] ?? 'NULL') . "\n";
        echo "  Created: {$task['created_at']}\n";
        echo "  ---\n";
    }

    // Test the kanban API endpoint
    echo "\n=== Testing Kanban API ===\n";
    $_REQUEST['action'] = 'kanban';
    $_SESSION['user_id'] = $userId;
    $_SESSION['current_team_id'] = $teamId;

    // Include and execute the API
    ob_start();
    include 'api/tasks.php';
    $apiResponse = ob_get_clean();

    $response = json_decode($apiResponse, true);
    if ($response) {
        echo "API Response Success: " . ($response['success'] ? 'Yes' : 'No') . "\n";
        echo "Tasks returned: " . count($response['tasks'] ?? []) . "\n\n";

        if (!empty($response['tasks'])) {
            echo "Tasks from API:\n";
            foreach ($response['tasks'] as $task) {
                echo "  - {$task['title']} (Status: {$task['status']})\n";
            }
        }
    } else {
        echo "Failed to decode API response\n";
        echo "Raw response: $apiResponse\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>