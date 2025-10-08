<?php
require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    echo "✓ Connected to database\n\n";

    // Show existing tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Existing tables:\n";
    if (empty($tables)) {
        echo "  (no tables found)\n";
    } else {
        foreach ($tables as $table) {
            echo "  - $table\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}