<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'config/database.php';

// Check if user is logged in and has admin or super_admin role
require_login();

$current_user = get_current_user_info();
if (!$current_user || !in_array($current_user['role'], ['admin', 'super_admin'])) {
    header('Location: dashboard.php');
    exit();
}

$page_title = "Admin Settings";

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save_settings') {
        // Validate CSRF token
        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $error = 'Invalid security token. Please try again.';
        } else {
            $db = getDB();

            // Save each setting
            $settings_saved = true;
            foreach ($_POST as $key => $value) {
                if (in_array($key, ['action', 'csrf_token'])) continue;

                try {
                    // Check if setting exists
                    $stmt = $db->prepare("SELECT id FROM settings WHERE setting_key = ?");
                    $stmt->execute([$key]);
                    $exists = $stmt->fetch();

                    if ($exists) {
                        // Update existing setting
                        $stmt = $db->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                        $stmt->execute([$value, $key]);
                    } else {
                        // Insert new setting
                        $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
                        $stmt->execute([$key, $value]);
                    }
                } catch (PDOException $e) {
                    $settings_saved = false;
                    $error = 'Database error: ' . $e->getMessage();
                    break;
                }
            }

            if ($settings_saved) {
                $message = 'Settings saved successfully!';
            }
        }
    }
}

// Load current settings
$db = getDB();
$settings = [];

try {
    $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $error = 'Failed to load settings: ' . $e->getMessage();
}

include 'includes/header.php';
?>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card" id="settings-card">
                    <div class="card-header pb-0" id="settings-header">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0">Admin Settings</h5>
                            <button type="submit" form="settings-form" class="btn btn-primary btn-sm ms-auto">Save Changes</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form id="settings-form" method="POST">
                            <input type="hidden" name="action" value="save_settings">
                            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                            <!-- User Management Settings -->
                            <div class="settings-section mb-4" id="user-management-section">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">User Management</h6>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="max_users" class="form-label">Maximum Users</label>
                                        <input type="number" class="form-control" id="max_users" name="max_users"
                                               value="<?php echo htmlspecialchars($settings['max_users'] ?? '100'); ?>">
                                        <small class="form-text text-muted">Maximum number of users allowed in the system</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="default_user_role" class="form-label">Default User Role</label>
                                        <select class="form-control" id="default_user_role" name="default_user_role">
                                            <option value="user" <?php echo ($settings['default_user_role'] ?? 'user') === 'user' ? 'selected' : ''; ?>>User</option>
                                            <option value="team_leader" <?php echo ($settings['default_user_role'] ?? '') === 'team_leader' ? 'selected' : ''; ?>>Team Leader</option>
                                            <option value="admin" <?php echo ($settings['default_user_role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                        <small class="form-text text-muted">Role assigned to new users by default</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="user_registration" class="form-label">Allow User Registration</label>
                                        <select class="form-control" id="user_registration" name="user_registration">
                                            <option value="enabled" <?php echo ($settings['user_registration'] ?? 'enabled') === 'enabled' ? 'selected' : ''; ?>>Enabled</option>
                                            <option value="disabled" <?php echo ($settings['user_registration'] ?? '') === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                                        </select>
                                        <small class="form-text text-muted">Allow new users to register accounts</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="password_min_length" class="form-label">Minimum Password Length</label>
                                        <input type="number" class="form-control" id="password_min_length" name="password_min_length"
                                               value="<?php echo htmlspecialchars($settings['password_min_length'] ?? '8'); ?>" min="6" max="32">
                                        <small class="form-text text-muted">Minimum required password length</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Task Management Settings -->
                            <div class="settings-section mb-4" id="task-management-section">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Task Management</h6>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="task_auto_archive_days" class="form-label">Auto-Archive Completed Tasks (Days)</label>
                                        <input type="number" class="form-control" id="task_auto_archive_days" name="task_auto_archive_days"
                                               value="<?php echo htmlspecialchars($settings['task_auto_archive_days'] ?? '30'); ?>" min="0">
                                        <small class="form-text text-muted">Automatically archive completed tasks after this many days (0 to disable)</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="task_priority_levels" class="form-label">Priority Levels</label>
                                        <input type="text" class="form-control" id="task_priority_levels" name="task_priority_levels"
                                               value="<?php echo htmlspecialchars($settings['task_priority_levels'] ?? 'Low,Medium,High,Critical'); ?>">
                                        <small class="form-text text-muted">Comma-separated list of priority levels</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="task_status_options" class="form-label">Task Status Options</label>
                                        <input type="text" class="form-control" id="task_status_options" name="task_status_options"
                                               value="<?php echo htmlspecialchars($settings['task_status_options'] ?? 'Todo,In Progress,Review,Done'); ?>">
                                        <small class="form-text text-muted">Comma-separated list of task status options</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="default_task_priority" class="form-label">Default Task Priority</label>
                                        <select class="form-control" id="default_task_priority" name="default_task_priority">
                                            <option value="Low" <?php echo ($settings['default_task_priority'] ?? 'Medium') === 'Low' ? 'selected' : ''; ?>>Low</option>
                                            <option value="Medium" <?php echo ($settings['default_task_priority'] ?? 'Medium') === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                            <option value="High" <?php echo ($settings['default_task_priority'] ?? '') === 'High' ? 'selected' : ''; ?>>High</option>
                                            <option value="Critical" <?php echo ($settings['default_task_priority'] ?? '') === 'Critical' ? 'selected' : ''; ?>>Critical</option>
                                        </select>
                                        <small class="form-text text-muted">Default priority for new tasks</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification Settings -->
                            <div class="settings-section mb-4" id="notification-section">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Notifications</h6>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email_notifications" class="form-label">Email Notifications</label>
                                        <select class="form-control" id="email_notifications" name="email_notifications">
                                            <option value="enabled" <?php echo ($settings['email_notifications'] ?? 'enabled') === 'enabled' ? 'selected' : ''; ?>>Enabled</option>
                                            <option value="disabled" <?php echo ($settings['email_notifications'] ?? '') === 'disabled' ? 'selected' : ''; ?>>Disabled</option>
                                        </select>
                                        <small class="form-text text-muted">Enable/disable email notifications</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="notification_frequency" class="form-label">Notification Frequency</label>
                                        <select class="form-control" id="notification_frequency" name="notification_frequency">
                                            <option value="immediate" <?php echo ($settings['notification_frequency'] ?? 'immediate') === 'immediate' ? 'selected' : ''; ?>>Immediate</option>
                                            <option value="hourly" <?php echo ($settings['notification_frequency'] ?? '') === 'hourly' ? 'selected' : ''; ?>>Hourly</option>
                                            <option value="daily" <?php echo ($settings['notification_frequency'] ?? '') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                        </select>
                                        <small class="form-text text-muted">How often to send notification digests</small>
                                    </div>
                                </div>
                            </div>

                            <!-- System Settings -->
                            <div class="settings-section mb-4" id="system-section">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">System</h6>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="maintenance_mode" class="form-label">Maintenance Mode</label>
                                        <select class="form-control" id="maintenance_mode" name="maintenance_mode">
                                            <option value="off" <?php echo ($settings['maintenance_mode'] ?? 'off') === 'off' ? 'selected' : ''; ?>>Off</option>
                                            <option value="on" <?php echo ($settings['maintenance_mode'] ?? '') === 'on' ? 'selected' : ''; ?>>On</option>
                                        </select>
                                        <small class="form-text text-muted">Enable maintenance mode to prevent user access</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="session_timeout" class="form-label">Session Timeout (Minutes)</label>
                                        <input type="number" class="form-control" id="session_timeout" name="session_timeout"
                                               value="<?php echo htmlspecialchars($settings['session_timeout'] ?? '30'); ?>" min="5" max="1440">
                                        <small class="form-text text-muted">Automatically log out users after this many minutes of inactivity</small>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="timezone" class="form-label">System Timezone</label>
                                        <select class="form-control" id="timezone" name="timezone">
                                            <option value="UTC" <?php echo ($settings['timezone'] ?? 'UTC') === 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                            <option value="America/New_York" <?php echo ($settings['timezone'] ?? '') === 'America/New_York' ? 'selected' : ''; ?>>Eastern Time</option>
                                            <option value="America/Chicago" <?php echo ($settings['timezone'] ?? '') === 'America/Chicago' ? 'selected' : ''; ?>>Central Time</option>
                                            <option value="America/Denver" <?php echo ($settings['timezone'] ?? '') === 'America/Denver' ? 'selected' : ''; ?>>Mountain Time</option>
                                            <option value="America/Los_Angeles" <?php echo ($settings['timezone'] ?? '') === 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time</option>
                                        </select>
                                        <small class="form-text text-muted">Default timezone for the system</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="date_format" class="form-label">Date Format</label>
                                        <select class="form-control" id="date_format" name="date_format">
                                            <option value="m/d/Y" <?php echo ($settings['date_format'] ?? 'm/d/Y') === 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                            <option value="d/m/Y" <?php echo ($settings['date_format'] ?? '') === 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                            <option value="Y-m-d" <?php echo ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                        </select>
                                        <small class="form-text text-muted">Date display format</small>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-light me-2" onclick="window.location.href='dashboard.php'">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>