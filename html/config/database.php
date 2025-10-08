<?php
/**
 * Database Configuration File
 * Central configuration for database connection settings
 */

// Database connection constants
define('DB_HOST', 'localhost');
define('DB_PORT', 3306); // MySQL port (3306 default, 8889 for MAMP)
define('DB_NAME', 'vibe_templates');
define('DB_USER', 'vibe_templates');
define('DB_PASS', '#Skilliks229!');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATION', 'utf8mb4_unicode_ci');

// Database options
define('DB_PERSISTENT', true);
define('DB_ERROR_MODE', PDO::ERRMODE_EXCEPTION);
define('DB_FETCH_MODE', PDO::FETCH_ASSOC);

/**
 * Get PDO connection using singleton Database class
 * @return PDO
 */
function getDB() {
    require_once __DIR__ . '/../classes/Database.php';
    return Database::getInstance()->getConnection();
}

/**
 * Quick query helper function
 * @param string $sql SQL query
 * @param array $params Parameters to bind
 * @return PDOStatement
 */
function dbQuery($sql, $params = []) {
    require_once __DIR__ . '/../classes/Database.php';
    return Database::getInstance()->query($sql, $params);
}

/**
 * Get database instance
 * @return Database
 */
function dbInstance() {
    require_once __DIR__ . '/../classes/Database.php';
    return Database::getInstance();
}