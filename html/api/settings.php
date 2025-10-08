<?php
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../config/database.php';
require_once '../classes/Database.php';
require_once '../classes/Settings.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$current_user = get_current_user_info();

// Only super_admin can access settings API
if ($current_user['role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden - Super admin access required']);
    exit();
}

// Get action from request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

$settings = new Settings();

switch ($action) {
    case 'get':
        $key = $_GET['key'] ?? '';
        if ($key) {
            $value = $settings->get($key);
            echo json_encode(['success' => true, 'value' => $value]);
        } else {
            echo json_encode(['error' => 'Setting key required']);
        }
        break;

    case 'get_all':
        $category = $_GET['category'] ?? null;
        $all_settings = $settings->getAll($category);
        echo json_encode(['success' => true, 'settings' => $all_settings]);
        break;

    case 'set':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'POST method required']);
            break;
        }

        $key = $_POST['key'] ?? '';
        $value = $_POST['value'] ?? '';
        $type = $_POST['type'] ?? 'text';

        if (!$key) {
            echo json_encode(['error' => 'Setting key required']);
            break;
        }

        if ($settings->set($key, $value, $type)) {
            echo json_encode(['success' => true, 'message' => 'Setting updated']);
        } else {
            echo json_encode(['error' => 'Failed to update setting']);
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'POST method required']);
            break;
        }

        $key = $_POST['key'] ?? '';
        if (!$key) {
            echo json_encode(['error' => 'Setting key required']);
            break;
        }

        if ($settings->delete($key)) {
            echo json_encode(['success' => true, 'message' => 'Setting deleted']);
        } else {
            echo json_encode(['error' => 'Failed to delete setting']);
        }
        break;

    case 'export':
        $includeEncrypted = ($_GET['include_encrypted'] ?? '0') === '1';
        $exported = $settings->export($includeEncrypted);

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="settings_export_' . date('Y-m-d_His') . '.json"');
        echo json_encode($exported, JSON_PRETTY_PRINT);
        break;

    case 'import':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'POST method required']);
            break;
        }

        $import_data = $_POST['settings'] ?? '';
        if (!$import_data) {
            echo json_encode(['error' => 'Settings data required']);
            break;
        }

        $settings_array = json_decode($import_data, true);
        if (!$settings_array) {
            echo json_encode(['error' => 'Invalid JSON data']);
            break;
        }

        if ($settings->import($settings_array)) {
            echo json_encode(['success' => true, 'message' => 'Settings imported successfully']);
        } else {
            echo json_encode(['error' => 'Failed to import settings']);
        }
        break;

    case 'clear_cache':
        $settings->clearCache();
        echo json_encode(['success' => true, 'message' => 'Cache cleared']);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}