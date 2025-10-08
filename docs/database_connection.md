# Database Connection Guide

## Configuration Location
Database credentials are stored in: `/var/www/html/config/db_config.php`

## Connection Details
- **Host:** localhost
- **Database:** vibe_templates
- **User:** vibe_template
- **Password:** [Stored securely in config file]

## How to Connect

### Using PDO (Recommended)
```php
<?php
require_once __DIR__ . '/config/db_config.php';

try {
    $pdo = getDBConnection();
    // Your database operations here
    $stmt = $pdo->query("SELECT * FROM your_table");
    $results = $stmt->fetchAll();
} catch (Exception $e) {
    // Handle error
    echo "Connection failed";
}
?>
```

### Using MySQLi
```php
<?php
require_once __DIR__ . '/config/db_config.php';

try {
    $connection = getMySQLiConnection();
    // Your database operations here
    $result = $connection->query("SELECT * FROM your_table");
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $connection->close();
} catch (Exception $e) {
    // Handle error
    echo "Connection failed";
}
?>
```

## Security Notes
- Configuration file is protected by .htaccess rules
- Never expose credentials in front-end code
- Always use prepared statements to prevent SQL injection
- Error messages are logged, not displayed to users