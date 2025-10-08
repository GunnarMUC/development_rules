<?php
// Script to populate sample data
require_once 'includes/db.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/sql/create_activities_and_sample_data.sql');

    // Split by semicolon but be careful with delimiters inside strings
    $queries = array_filter(array_map('trim', preg_split('/;(?=([^\']*\'[^\']*\')*[^\']*$)/', $sql)));

    $success_count = 0;
    $error_count = 0;
    $errors = [];

    foreach ($queries as $query) {
        if (!empty($query) && !preg_match('/^\s*--/', $query)) {
            try {
                // Skip USE statements as we're already connected to the database
                if (stripos($query, 'USE ') === 0) {
                    continue;
                }

                $pdo->exec($query);
                $success_count++;
            } catch (PDOException $e) {
                $error_count++;
                $errors[] = "Query failed: " . substr($query, 0, 100) . "... Error: " . $e->getMessage();
            }
        }
    }

    echo "Sample data population completed!\n";
    echo "Successful queries: $success_count\n";
    echo "Failed queries: $error_count\n";

    if ($error_count > 0) {
        echo "\nErrors encountered:\n";
        foreach ($errors as $error) {
            echo "- $error\n";
        }
    }

    // Show some statistics
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch()['count'];
    echo "\nTotal users: $userCount\n";

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tasks");
    $taskCount = $stmt->fetch()['count'];
    echo "Total tasks: $taskCount\n";

    $stmt = $pdo->query("SELECT
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
        FROM tasks");
    $stats = $stmt->fetch();
    echo "- Completed: {$stats['completed']}\n";
    echo "- In Progress: {$stats['in_progress']}\n";
    echo "- Pending: {$stats['pending']}\n";

    // Check if activities table exists
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'activities'");
    if ($stmt->fetch()['count'] > 0) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM activities");
        $activityCount = $stmt->fetch()['count'];
        echo "Total activities: $activityCount\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}