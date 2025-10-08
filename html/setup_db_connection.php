<?php
// Setup script to create database and user with proper permissions

$host = 'localhost';
$root_user = 'root';

// Try to connect as root without password (common in development)
$configs = [
    ['user' => 'root', 'pass' => ''],
    ['user' => 'root', 'pass' => 'root'],
];

$pdo = null;
foreach ($configs as $config) {
    try {
        $pdo = new PDO("mysql:host=$host", $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connected as {$config['user']}\n";
        break;
    } catch (PDOException $e) {
        continue;
    }
}

if (!$pdo) {
    // If root doesn't work, let's just create the database objects assuming we have a working user
    echo "Cannot connect as root. Please run these SQL commands manually:\n\n";

    $sql = <<<SQL
-- Create database if not exists
CREATE DATABASE IF NOT EXISTS vibe_templates CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use the database
USE vibe_templates;

-- Create tasks table
CREATE TABLE IF NOT EXISTS tasks (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SQL;

    echo $sql;
    echo "\n\nSave this to a file and run: mysql -u [your_user] -p < create_tasks.sql\n";
    exit;
}

// We have a connection, let's set everything up
try {
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS vibe_templates CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database created or exists\n";

    // Use the database
    $pdo->exec("USE vibe_templates");

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

    // Verify table exists
    $result = $pdo->query("SHOW TABLES LIKE 'tasks'")->fetchAll();
    if (count($result) > 0) {
        echo "Verified: tasks table exists\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}