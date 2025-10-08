<?php
// Create tasks table

try {
    // Try different authentication options
    $configs = [
        ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'db' => 'vibe_templates'],
        ['host' => 'localhost', 'user' => 'vibe_template', 'pass' => '#Skilliks229!', 'db' => 'vibe_templates'],
        ['host' => 'localhost', 'user' => 'root', 'pass' => 'Skilliks229!', 'db' => 'vibe_templates'],
    ];

    $pdo = null;
    foreach ($configs as $config) {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['db']};charset=utf8mb4";
            $pdo = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            echo "Connected successfully with user: {$config['user']}\n";
            break;
        } catch (PDOException $e) {
            echo "Failed with user {$config['user']}: " . $e->getMessage() . "\n";
        }
    }

    if (!$pdo) {
        die("Could not connect to database with any configuration.\n");
    }

    // Create tasks table
    $sql = "CREATE TABLE IF NOT EXISTS tasks (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
        priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
        due_date DATE DEFAULT NULL,
        completed_at TIMESTAMP NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_status (status),
        INDEX idx_priority (priority),
        INDEX idx_due_date (due_date),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "Tasks table created successfully!\n";

    // Check if table was created
    $tables = $pdo->query("SHOW TABLES LIKE 'tasks'")->fetchAll();
    if (count($tables) > 0) {
        echo "Verified: tasks table exists\n";

        // Show table structure
        $columns = $pdo->query("DESCRIBE tasks")->fetchAll();
        echo "\nTable structure:\n";
        foreach ($columns as $col) {
            echo "- {$col['Field']} ({$col['Type']})\n";
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}