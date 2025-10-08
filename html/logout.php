<?php
/**
 * Logout Page
 * Handles user logout and redirects to login page
 */

require_once 'includes/auth.php';

// Perform logout
logout_user();

// Redirect to login page
header('Location: login.php');
exit();