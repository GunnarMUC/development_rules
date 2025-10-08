<?php
/**
 * Database Singleton Class
 * Provides a single PDO database connection instance throughout the application
 */

class Database {
    private static $instance = null;
    private $connection;
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $charset;

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        // Include database config if not already defined
        if (!defined('DB_HOST')) {
            $configFile = __DIR__ . '/../config/database.php';
            if (file_exists($configFile)) {
                require_once $configFile;
            }
        }

        // Use config constants if already defined
        $this->host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $this->port = defined('DB_PORT') ? DB_PORT : 3306;
        $this->dbname = defined('DB_NAME') ? DB_NAME : 'saas_template';
        $this->username = defined('DB_USER') ? DB_USER : 'root';
        $this->password = defined('DB_PASS') ? DB_PASS : '';
        $this->charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

        $this->connect();
    }

    /**
     * Establish database connection
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);

            // Set additional connection attributes
            $this->connection->exec("SET NAMES {$this->charset}");
            $this->connection->exec("SET CHARACTER SET {$this->charset}");
            $this->connection->exec("SET CHARACTER_SET_CONNECTION={$this->charset}");
            $this->connection->exec("SET SQL_MODE = ''");

        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection error: Unable to connect to database");
        }
    }

    /**
     * Get the singleton instance
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection
     * @return PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Prepare and execute a query with parameters
     * @param string $sql SQL query
     * @param array $params Parameters to bind
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw new Exception("Query execution error");
        }
    }

    /**
     * Get last insert ID
     * @return string
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->connection->rollBack();
    }

    /**
     * Check if connected
     * @return boolean
     */
    public function isConnected() {
        try {
            $this->connection->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}

    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}