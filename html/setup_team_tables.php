<?php
require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo "✓ Connected to database\n";

    // Create teams table
    $sql = "CREATE TABLE IF NOT EXISTS teams (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_by INT NOT NULL,
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
        user_id INT NOT NULL,
        role ENUM('member', 'admin') DEFAULT 'member',
        joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_team_member (team_id, user_id),
        FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    $pdo->exec($sql);
    echo "✓ Created team_members table\n";

    // Check if team_id column exists in tasks table
    $stmt = $pdo->query("SHOW COLUMNS FROM tasks LIKE 'team_id'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN team_id INT DEFAULT NULL");

        // Try to add foreign key, but catch error if it already exists
        try {
            $pdo->exec("ALTER TABLE tasks ADD FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE");
        } catch (PDOException $e) {
            // Ignore if foreign key already exists
        }
        echo "✓ Added team_id column to tasks table\n";
    } else {
        echo "⚠ team_id column already exists in tasks table\n";
    }

    // Check if assigned_to column exists in tasks table
    $stmt = $pdo->query("SHOW COLUMNS FROM tasks LIKE 'assigned_to'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE tasks ADD COLUMN assigned_to INT DEFAULT NULL");

        // Try to add foreign key, but catch error if it already exists
        try {
            $pdo->exec("ALTER TABLE tasks ADD FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL");
        } catch (PDOException $e) {
            // Ignore if foreign key already exists
        }
        echo "✓ Added assigned_to column to tasks table\n";
    } else {
        echo "⚠ assigned_to column already exists in tasks table\n";
    }

    echo "\n✅ Team tables setup completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}