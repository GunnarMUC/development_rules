<?php
/**
 * Database Configuration
 * Store sensitive database credentials
 */

define('DB_HOST', 'localhost');
define('DB_PORT', 3306); // MySQL port (3306 default, 8889 for MAMP)
define('DB_NAME', 'vibe_templates');
define('DB_USER', 'vibe_templates');
define('DB_PASS', '#Skilliks229!');
define('DB_CHARSET', 'utf8mb4');

/**
 * PDO Connection Function
 * Returns a PDO connection instance
 */
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Database connection error");
    }
}

/**
 * MySQLi Connection Function
 * Returns a MySQLi connection instance
 */
function getMySQLiConnection() {
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($connection->connect_error) {
        error_log("Database connection failed: " . $connection->connect_error);
        throw new Exception("Database connection error");
    }

    $connection->set_charset(DB_CHARSET);
    return $connection;
}