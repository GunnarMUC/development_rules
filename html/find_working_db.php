<?php
// Try different credential combinations
$attempts = [
    ['user' => 'root', 'pass' => ''],
    ['user' => 'root', 'pass' => 'root'],
    ['user' => 'vibe_template', 'pass' => '#Skilliks229!'],
    ['user' => 'vibe_templates', 'pass' => '#Skilliks229!'],
    ['user' => 'skilliks', 'pass' => ''],
    ['user' => 'skilliks', 'pass' => 'skilliks'],
];

$dbname = 'vibe_templates';

foreach ($attempts as $attempt) {
    echo "Trying user: {$attempt['user']} ... ";
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=$dbname", $attempt['user'], $attempt['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "✓ SUCCESS!\n";
        echo "Working credentials: username = '{$attempt['user']}', password = '{$attempt['pass']}'\n\n";

        // Show tables
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables found: " . implode(', ', $tables) . "\n";

        break;
    } catch (PDOException $e) {
        echo "✗ Failed\n";
    }
}