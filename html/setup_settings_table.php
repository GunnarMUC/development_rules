<?php
require_once 'config/database.php';

try {
    $db = getDB();

    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/sql/settings_table.sql');

    // Execute the SQL
    $db->exec($sql);

    echo "Settings table created successfully!\n";

    // Verify table creation
    $stmt = $db->query("SELECT COUNT(*) as count FROM settings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "Total settings in database: " . $result['count'] . "\n";

} catch (PDOException $e) {
    echo "Error creating settings table: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Settings table setup complete!\n";