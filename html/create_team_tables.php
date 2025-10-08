<?php
require_once 'config/database.php';

try {
    $db = getDB();

    // Read the team tables SQL file
    $sql = file_get_contents(__DIR__ . '/sql/team_tables.sql');

    // Split by semicolons to execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 60) . "...\n";
            } catch (PDOException $e) {
                // Check if it's a duplicate column error
                if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    echo "⚠ Column already exists, skipping...\n";
                } else {
                    throw $e;
                }
            }
        }
    }

    echo "\n✅ Team tables created/updated successfully!\n";

} catch (Exception $e) {
    echo "❌ Error creating team tables: " . $e->getMessage() . "\n";
}