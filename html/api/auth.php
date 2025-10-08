<?php
/**
 * Authentication API Endpoint
 * Handles login, registration, and logout requests
 */

require_once '../includes/auth.php';
require_once '../includes/session.php';

// Set JSON response header
header('Content-Type: application/json');

// Get action from request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Handle different actions
switch ($action) {
    case 'login':
        handleLogin();
        break;

    case 'register':
        handleRegister();
        break;

    case 'logout':
        handleLogout();
        break;

    case 'check':
        handleCheckAuth();
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

/**
 * Handle login request
 */
function handleLogin() {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        return;
    }

    // Get form data
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] === 'on';

    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }

    // Attempt login
    $result = login_user($email, $password, $remember);
    echo json_encode($result);
}

/**
 * Handle registration request
 */
function handleRegister() {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        return;
    }

    // Get form data
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $first_name = filter_var($_POST['first_name'] ?? '', FILTER_SANITIZE_STRING);
    $last_name = filter_var($_POST['last_name'] ?? '', FILTER_SANITIZE_STRING);

    // Validate input
    if (empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match']);
        return;
    }

    // Validate password strength
    $password_validation = validate_password($password);
    if ($password_validation !== true) {
        echo json_encode(['success' => false, 'message' => $password_validation]);
        return;
    }

    // Attempt registration
    $result = register_user($email, $password, $first_name, $last_name);
    echo json_encode($result);
}

/**
 * Handle logout request
 */
function handleLogout() {
    $result = logout_user();
    echo json_encode($result);
}

/**
 * Check authentication status
 */
function handleCheckAuth() {
    if (is_logged_in()) {
        $user = get_current_user_info();
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['first_name'] . ' ' . $user['last_name'],
                'role' => $user['role']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'authenticated' => false
        ]);
    }
}