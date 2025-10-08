<?php
/**
 * Database Connection Test Script
 */

require_once __DIR__ . '/config/db_config.php';

echo "<h2>Testing MariaDB Connection</h2>";

// Test PDO Connection
echo "<h3>PDO Connection Test:</h3>";
try {
    $pdo = getDBConnection();
    echo "✓ PDO connection successful!<br>";

    // Get database version
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    echo "Database Version: " . $version['version'] . "<br><br>";

    // Test if database exists
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $db = $stmt->fetch();
    echo "Connected to database: " . $db['db'] . "<br><br>";

} catch (Exception $e) {
    echo "✗ PDO connection failed<br>";
    echo "Error: " . $e->getMessage() . "<br><br>";
}

// Test MySQLi Connection
echo "<h3>MySQLi Connection Test:</h3>";
try {
    $mysqli = getMySQLiConnection();
    echo "✓ MySQLi connection successful!<br>";
    echo "Server info: " . $mysqli->server_info . "<br>";
    echo "Character set: " . $mysqli->character_set_name() . "<br>";
    $mysqli->close();
} catch (Exception $e) {
    echo "✗ MySQLi connection failed<br>";
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "<br><hr>";
echo "<small>Note: Delete this test file after verification for security.</small>";