<?php
require_once 'config/database.php';

try {
    $db = getDB();

    // Read SQL file
    $sql = file_get_contents(__DIR__ . '/sql/create_tables.sql');

    // Remove the CREATE DATABASE and USE statements since we're already connected
    $sql = preg_replace('/CREATE DATABASE[^;]+;/i', '', $sql);
    $sql = preg_replace('/USE[^;]+;/i', '', $sql);

    // Split by semicolons to execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
            echo "Executed: " . substr($statement, 0, 50) . "...\n";
        }
    }

    echo "\nAll tables created successfully!\n";

} catch (Exception $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
}