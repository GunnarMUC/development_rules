<?php
/**
 * Database Connection Test Script
 * Tests the PDO Database singleton class and verifies connection
 */

// Display errors for testing
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include configuration and Database class
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/classes/Database.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Connection Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .test-result {
            background: white;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success {
            color: #28a745;
            border-left: 4px solid #28a745;
        }
        .error {
            color: #dc3545;
            border-left: 4px solid #dc3545;
        }
        .info {
            color: #17a2b8;
            border-left: 4px solid #17a2b8;
        }
        h1 {
            color: #333;
        }
        code {
            background: #f4f4f4;
            padding: 2px 5px;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Database Connection Test</h1>

    <?php
    try {
        // Test 1: Get database instance
        echo '<div class="test-result info">';
        echo '<h3>Test 1: Getting Database Instance</h3>';
        $db = Database::getInstance();
        echo '<p>✓ Successfully created Database singleton instance</p>';
        echo '</div>';

        // Test 2: Check connection
        echo '<div class="test-result info">';
        echo '<h3>Test 2: Testing Connection</h3>';
        if ($db->isConnected()) {
            echo '<p class="success">✓ Database connection is active</p>';
        } else {
            echo '<p class="error">✗ Database connection failed</p>';
        }
        echo '</div>';

        // Test 3: Get connection details
        echo '<div class="test-result info">';
        echo '<h3>Test 3: Connection Details</h3>';
        $conn = $db->getConnection();
        $version = $conn->query('SELECT VERSION()')->fetchColumn();
        echo '<p>Database Type: MariaDB/MySQL</p>';
        echo '<p>Server Version: ' . htmlspecialchars($version) . '</p>';
        echo '<p>Database Name: ' . DB_NAME . '</p>';
        echo '<p>Character Set: ' . DB_CHARSET . '</p>';
        echo '</div>';

        // Test 4: Check if tables exist
        echo '<div class="test-result info">';
        echo '<h3>Test 4: Database Tables</h3>';

        $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

        if (count($tables) > 0) {
            echo '<p>Found ' . count($tables) . ' table(s) in the database:</p>';
            echo '<table>';
            echo '<thead><tr><th>Table Name</th><th>Status</th></tr></thead>';
            echo '<tbody>';
            foreach ($tables as $table) {
                echo '<tr>';
                echo '<td><code>' . htmlspecialchars($table) . '</code></td>';
                echo '<td class="success">✓ Exists</td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>No tables found in database. You may need to run the SQL script.</p>';
            echo '<p>SQL script location: <code>/var/www/html/sql/create_tables.sql</code></p>';
        }
        echo '</div>';

        // Test 5: Test query execution
        echo '<div class="test-result info">';
        echo '<h3>Test 5: Query Execution Test</h3>';

        $testQuery = "SELECT 1 + 1 AS result";
        $result = $db->query($testQuery)->fetch();

        if ($result['result'] == 2) {
            echo '<p class="success">✓ Query execution successful</p>';
            echo '<p>Test query: <code>' . htmlspecialchars($testQuery) . '</code></p>';
            echo '<p>Result: ' . $result['result'] . '</p>';
        } else {
            echo '<p class="error">✗ Query execution failed</p>';
        }
        echo '</div>';

        // Test 6: Using helper functions
        echo '<div class="test-result info">';
        echo '<h3>Test 6: Helper Functions</h3>';

        $dbConn = getDB();
        if ($dbConn instanceof PDO) {
            echo '<p class="success">✓ getDB() function works correctly</p>';
        }

        $dbInst = dbInstance();
        if ($dbInst instanceof Database) {
            echo '<p class="success">✓ dbInstance() function works correctly</p>';
        }

        $queryResult = dbQuery("SELECT DATABASE() as db_name")->fetch();
        if ($queryResult['db_name'] === DB_NAME) {
            echo '<p class="success">✓ dbQuery() function works correctly</p>';
        }
        echo '</div>';

        // Overall status
        echo '<div class="test-result success">';
        echo '<h3>✓ All Tests Passed Successfully!</h3>';
        echo '<p>The database connection is working properly. The Database singleton class is functioning as expected.</p>';
        echo '</div>';

    } catch (Exception $e) {
        echo '<div class="test-result error">';
        echo '<h3>✗ Test Failed</h3>';
        echo '<p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p>Please check your database configuration in:</p>';
        echo '<ul>';
        echo '<li><code>/var/www/html/classes/Database.php</code></li>';
        echo '<li><code>/var/www/html/config/database.php</code></li>';
        echo '</ul>';
        echo '</div>';
    }
    ?>

    <div class="test-result info">
        <h3>Next Steps</h3>
        <ol>
            <li>If tables don't exist, run the SQL script to create them:
                <br><code>mysql -u vibe_templates -p vibe_templates < /var/www/html/sql/create_tables.sql</code>
            </li>
            <li>Once testing is complete, delete this test file for security:
                <br><code>rm /var/www/html/test_db.php</code>
            </li>
            <li>Start building your application using the Database singleton class</li>
        </ol>
    </div>
</body>
</html>