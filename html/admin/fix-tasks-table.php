<?php
// Allow CLI execution
if (php_sapi_name() === 'cli') {
    require_once dirname(__DIR__) . '/config/database.php';
} else {
    session_start();
    require_once '../config/database.php';

    // Check if user is admin for web access
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        die("Access denied. Admin privileges required.");
    }
}

// Get PDO connection
$pdo = getDB();

try {
    echo "<h2>Fixing Tasks Table Schema</h2>";
    echo "<pre>";

    // First, check current structure
    echo "Current table structure:\n";
    $stmt = $pdo->query("DESCRIBE tasks");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $existingColumns = array_column($columns, 'Field');

    foreach ($columns as $column) {
        echo "  - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }

    echo "\nChecking for missing columns...\n";

    // Check and add missing columns
    $columnsToAdd = [
        'category' => "ALTER TABLE tasks ADD COLUMN category VARCHAR(100) DEFAULT NULL COMMENT 'Task category'",
        'tags' => "ALTER TABLE tasks ADD COLUMN tags TEXT DEFAULT NULL COMMENT 'Comma-separated tags'",
        'created_by' => "ALTER TABLE tasks ADD COLUMN created_by INT UNSIGNED DEFAULT NULL COMMENT 'User ID who created the task'"
    ];

    $addedColumns = [];
    foreach ($columnsToAdd as $columnName => $sql) {
        if (!in_array($columnName, $existingColumns)) {
            echo "Adding column '$columnName'... ";
            $pdo->exec($sql);
            $addedColumns[] = $columnName;
            echo "✓\n";
        } else {
            echo "Column '$columnName' already exists ✓\n";
        }
    }

    // Add indexes if columns were added
    if (in_array('category', $addedColumns)) {
        echo "Adding index on 'category'... ";
        $pdo->exec("ALTER TABLE tasks ADD INDEX idx_category (category)");
        echo "✓\n";
    }

    if (in_array('created_by', $addedColumns)) {
        echo "Adding index on 'created_by'... ";
        $pdo->exec("ALTER TABLE tasks ADD INDEX idx_created_by (created_by)");
        echo "✓\n";

        // Add foreign key constraint
        echo "Adding foreign key constraint for 'created_by'... ";
        try {
            $pdo->exec("ALTER TABLE tasks ADD CONSTRAINT fk_tasks_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL");
            echo "✓\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key') !== false) {
                echo "Already exists ✓\n";
            } else {
                echo "Warning: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\nUpdated table structure:\n";
    $stmt = $pdo->query("DESCRIBE tasks");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "  - " . $column['Field'] . " (" . $column['Type'] . ")\n";
    }

    echo "\n<strong>✅ Tasks table schema fixed successfully!</strong>\n";
    echo "\nYou can now use the create-task.php page without errors.";
    echo "</pre>";

} catch (PDOException $e) {
    echo "<pre>";
    echo "❌ Error: " . $e->getMessage();
    echo "</pre>";
}
?>