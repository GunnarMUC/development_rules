<?php
/**
 * Session Management
 * Handles secure session configuration and management
 */

// Set secure session configuration
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 3600); // 1 hour

// Check if HTTPS is enabled
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Regenerate session ID for security
 */
function regenerate_session() {
    session_regenerate_id(true);
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verify_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token field for forms
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generate_csrf_token()) . '">';
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function get_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Set user session data
 */
function set_user_session($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_role'] = $user['role'] ?? 'user';
    $_SESSION['login_time'] = time();

    // Store full user array for header.php
    $_SESSION['user'] = [
        'id' => $user['id'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'role' => $user['role'] ?? 'user'
    ];

    // Set default team for the user
    set_user_default_team($user['id']);

    regenerate_session();
}

/**
 * Clear user session
 */
function clear_user_session() {
    $_SESSION = array();

    // Destroy session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();
}

/**
 * Check session timeout
 */
function check_session_timeout() {
    $timeout = 3600; // 1 hour

    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > $timeout) {
            clear_user_session();
            return false;
        }
        $_SESSION['login_time'] = time(); // Reset timer on activity
    }

    return true;
}

/**
 * Set user's default team in session
 */
function set_user_default_team($user_id) {
    try {
        // Try to get user's team from database
        if (function_exists('getDB')) {
            $db = @getDB();
            if ($db) {
                // First try to get user's existing team membership
                $stmt = $db->prepare("SELECT team_id FROM team_members WHERE user_id = ? ORDER BY joined_at ASC LIMIT 1");
                $stmt->execute([$user_id]);
                $membership = $stmt->fetch();

                if ($membership) {
                    $_SESSION['current_team_id'] = $membership['team_id'];
                } else {
                    // If user has no team, get or create default team
                    $stmt = $db->query("SELECT id FROM teams WHERE name = 'Default Team' LIMIT 1");
                    $team = $stmt->fetch();

                    if (!$team) {
                        // Create default team
                        $stmt = $db->prepare("INSERT INTO teams (name, created_by) VALUES ('Default Team', ?)");
                        $stmt->execute([$user_id]);
                        $team_id = $db->lastInsertId();
                    } else {
                        $team_id = $team['id'];
                    }

                    // Add user to default team
                    $stmt = $db->prepare("INSERT INTO team_members (team_id, user_id, role) VALUES (?, ?, 'member')
                                         ON DUPLICATE KEY UPDATE team_id=team_id");
                    $stmt->execute([$team_id, $user_id]);

                    $_SESSION['current_team_id'] = $team_id;
                }
            }
        }
    } catch (Exception $e) {
        // If database fails, set a default team ID
        $_SESSION['current_team_id'] = 1;
        error_log("Failed to set user team: " . $e->getMessage());
    }

    // Fallback: ensure team_id is always set
    if (!isset($_SESSION['current_team_id'])) {
        $_SESSION['current_team_id'] = 1;
    }
}

/**
 * Redirect if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: /html/login.php');
        exit();
    }

    // Check session timeout
    if (!check_session_timeout()) {
        header('Location: /html/login.php?timeout=1');
        exit();
    }
}