<?php
/**
 * Test Authentication System
 * This file tests the authentication without database
 */

// Include the auth files
require_once 'includes/session.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Authentication System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h1>Authentication System Test</h1>

                <div class="alert alert-info">
                    <h4>Authentication System Status</h4>
                    <p>This page tests the authentication system components.</p>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Session Configuration</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        echo "<p><strong>Session Status:</strong> " . (session_status() == PHP_SESSION_ACTIVE ? "Active" : "Not Active") . "</p>";
                        echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
                        echo "<p><strong>CSRF Token:</strong> " . generate_csrf_token() . "</p>";
                        ?>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Authentication Pages</h5>
                    </div>
                    <div class="card-body">
                        <p>The following pages have been created:</p>
                        <ul>
                            <li><a href="login.php" target="_blank">Login Page</a> - User login with jQuery validation</li>
                            <li><a href="register.php" target="_blank">Registration Page</a> - New user registration</li>
                            <li><a href="dashboard.php" target="_blank">Dashboard</a> - Protected page (requires login)</li>
                            <li><a href="logout.php">Logout</a> - Ends user session</li>
                        </ul>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h5>File Structure</h5>
                    </div>
                    <div class="card-body">
                        <pre>
/var/www/html/
├── includes/
│   ├── session.php     - Session management
│   └── auth.php        - Authentication functions
├── api/
│   └── auth.php        - AJAX authentication endpoint
├── login.php           - Login page
├── register.php        - Registration page
├── dashboard.php       - Dashboard (protected)
└── logout.php          - Logout handler
                        </pre>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Features Implemented</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            <li class="list-group-item">✅ Secure session management with httponly cookies</li>
                            <li class="list-group-item">✅ CSRF token generation and validation</li>
                            <li class="list-group-item">✅ Password hashing with password_hash()</li>
                            <li class="list-group-item">✅ Password verification with password_verify()</li>
                            <li class="list-group-item">✅ jQuery form validation on login/register</li>
                            <li class="list-group-item">✅ AJAX form submission</li>
                            <li class="list-group-item">✅ Bootstrap 5 responsive design</li>
                            <li class="list-group-item">✅ Remember me functionality</li>
                            <li class="list-group-item">✅ Session timeout handling</li>
                            <li class="list-group-item">✅ Password strength indicator</li>
                        </ul>
                    </div>
                </div>

                <div class="alert alert-warning">
                    <h5>Database Setup Required</h5>
                    <p>To fully test the authentication system, you need to create the database tables using the SQL script at <code>/var/www/html/sql/create_tables.sql</code></p>
                    <p>Run this command in MySQL:</p>
                    <code>mysql -u root vibe_templates < /var/www/html/sql/create_tables.sql</code>
                </div>
            </div>
        </div>
    </div>
</body>
</html>