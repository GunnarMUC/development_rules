<?php
/**
 * Setup Calendar Tables
 * Creates events and event_attendees tables for the calendar module
 */

require_once 'config/database.php';

try {
    // Get database instance
    $db = dbInstance();

    echo "Setting up calendar tables...\n\n";

    // Read SQL file
    $sqlFile = __DIR__ . '/sql/calendar_tables.sql';
    if (!file_exists($sqlFile)) {
        die("ERROR: SQL file not found at: {$sqlFile}\n");
    }

    $sql = file_get_contents($sqlFile);

    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement) && stripos($statement, 'USE') === false) {
            try {
                $db->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "✗ Failed: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n✅ Calendar tables setup complete!\n\n";

    // Verify tables were created
    echo "Verifying tables...\n";
    $tables = $db->query("SHOW TABLES LIKE '%event%'")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        echo "  - Table '{$table}' exists\n";
    }

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>