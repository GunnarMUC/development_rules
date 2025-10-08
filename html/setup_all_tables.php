<?php
require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo "✓ Connected to database\n\n";

    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        username VARCHAR(50) UNIQUE,
        role ENUM('user', 'admin', 'super_admin') DEFAULT 'user',
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        email_verified_at TIMESTAMP NULL DEFAULT NULL,
        remember_token VARCHAR(100) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_username (username),
        INDEX idx_role (role),
        INDEX idx_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "✓ Created users table\n";

    // Create sessions table
    $sql = "CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(128) PRIMARY KEY,
        user_id INT UNSIGNED,
        ip_address VARCHAR(45),
        user_agent TEXT,
        payload TEXT NOT NULL,
        last_activity INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_last_activity (last_activity),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "✓ Created sessions table\n";

    // Create password_resets table
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        used_at TIMESTAMP NULL DEFAULT NULL,
        INDEX idx_email (email),
        INDEX idx_token (token),
        INDEX idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
    echo "✓ Created password_resets table\n";

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
    echo "✓ Created tasks table\n";

    echo "\n--- Now creating team tables ---\n\n";

    // Create teams table (now users table exists, the foreign key will work)
    $sql = "CREATE TABLE IF NOT EXISTS teams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_by INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sql);
    echo "✓ Created teams table\n";

    // Create team_members table
    $sql = "CREATE TABLE IF NOT EXISTS team_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        team_id INT NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        role ENUM('member', 'admin') DEFAULT 'member',
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_team_member (team_id, user_id),
        FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sql);
    echo "✓ Created team_members table\n";

    // Add team_id column to tasks table if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM tasks LIKE 'team_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN team_id INT DEFAULT NULL AFTER user_id");
        $pdo->exec("ALTER TABLE tasks ADD FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE");
        echo "✓ Added team_id column to tasks table\n";
    } else {
        echo "⚠ team_id column already exists in tasks table\n";
    }

    // Add assigned_to column to tasks table if not exists
    $stmt = $pdo->query("SHOW COLUMNS FROM tasks LIKE 'assigned_to'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN assigned_to INT UNSIGNED DEFAULT NULL AFTER team_id");
        $pdo->exec("ALTER TABLE tasks ADD FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL");
        echo "✓ Added assigned_to column to tasks table\n";
    } else {
        echo "⚠ assigned_to column already exists in tasks table\n";
    }

    echo "\n✅ All tables created successfully!\n";

    // Show final table structure
    echo "\n--- Database structure ---\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  • $table\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}