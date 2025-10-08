<?php
/**
 * Database connection include file
 * Provides database connection through the Database singleton class
 */

// Include the database configuration
require_once __DIR__ . '/../config/database.php';

// Include the Database class
require_once __DIR__ . '/../classes/Database.php';

// Create a global PDO connection variable for backward compatibility
try {
    $pdo = Database::getInstance()->getConnection();
} catch (Exception $e) {
    // Log error but don't expose database details
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}

/**
 * Check if database is connected
 * @return bool
 */
function isDatabaseConnected() {
    global $pdo;
    return $pdo !== null;
}