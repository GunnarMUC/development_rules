<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'config/database.php';

// Check if user is logged in and has super_admin role
require_login();

$current_user = get_current_user_info();
if (!$current_user || $current_user['role'] !== 'super_admin') {
    header('Location: dashboard.php');
    exit();
}

$page_title = "Global Settings";

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
                    error_log("Error saving setting $key: " . $e->getMessage());
                }
            }

            if ($settings_saved) {
                $message = 'Settings saved successfully!';
            } else {
                $error = 'Some settings could not be saved. Please try again.';
            }
        }
    }
}

// Load current settings
$settings = [];
try {
    $db = getDB();
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    // Table might not exist yet
    $settings = [];
}

// Set defaults if not set
$default_settings = [
    'site_name' => 'SaaS Template',
    'site_description' => 'A modern SaaS application template',
    'max_file_size' => '10485760', // 10MB in bytes
    'max_team_members' => '50',
    'max_teams_per_user' => '10',
    'allow_registration' => '1',
    'require_email_verification' => '0',
    'session_timeout' => '1800', // 30 minutes
    'password_min_length' => '8',
    'enable_notifications' => '1',
    'enable_api' => '1',
    'api_rate_limit' => '100', // requests per hour
    'maintenance_mode' => '0',
    'maintenance_message' => 'We are currently performing maintenance. Please check back later.',
    'smtp_host' => '',
    'smtp_port' => '587',
    'smtp_username' => '',
    'smtp_from_email' => '',
    'smtp_from_name' => '',
    'date_format' => 'Y-m-d',
    'time_format' => 'H:i:s',
    'timezone' => 'America/New_York',
    'navigation_layout' => 'sidenav' // sidenav or topnav
];

foreach ($default_settings as $key => $default_value) {
    if (!isset($settings[$key])) {
        $settings[$key] = $default_value;
    }
}

include_once 'includes/header.php';
?>

<div id="main-wrapper" class="container-fluid">
    <div id="main-row" class="row">
        <?php
        // Get navigation layout from session or default
        $nav_layout = $_SESSION['navigation_layout'] ?? 'sidenav';
        $main_col_class = ($nav_layout === 'topnav') ? 'col-lg-12' : 'col-md-9 ms-sm-auto col-lg-10';
        ?>

        <!-- Sidebar - only show if sidenav layout -->
        <?php if ($nav_layout === 'sidenav'): ?>
            <?php include_once 'includes/sidebar.php'; ?>
        <?php endif; ?>

        <!-- Main Content -->
        <main id="main-content-wrapper" class="<?php echo $main_col_class; ?> px-md-4">
            <!-- Page Header -->
            <div id="page-header" class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <div id="page-title-wrapper">
                    <h1 id="page-title" class="h2">Global Settings</h1>
                    <nav id="breadcrumb-nav" aria-label="breadcrumb">
                        <ol id="breadcrumb-list" class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Global Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?php if ($message): ?>
                <div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div id="error-alert" class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Settings Form -->
            <form id="settings-form" method="POST" action="">
                <input type="hidden" name="action" value="save_settings">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <!-- General Settings -->
                <div id="general-settings-card" class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-cog"></i> General Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="site_name" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="site_name" name="site_name"
                                       value="<?php echo htmlspecialchars($settings['site_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="site_description" class="form-label">Site Description</label>
                                <input type="text" class="form-control" id="site_description" name="site_description"
                                       value="<?php echo htmlspecialchars($settings['site_description']); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select" id="timezone" name="timezone">
                                    <option value="America/New_York" <?php echo $settings['timezone'] == 'America/New_York' ? 'selected' : ''; ?>>Eastern Time</option>
                                    <option value="America/Chicago" <?php echo $settings['timezone'] == 'America/Chicago' ? 'selected' : ''; ?>>Central Time</option>
                                    <option value="America/Denver" <?php echo $settings['timezone'] == 'America/Denver' ? 'selected' : ''; ?>>Mountain Time</option>
                                    <option value="America/Los_Angeles" <?php echo $settings['timezone'] == 'America/Los_Angeles' ? 'selected' : ''; ?>>Pacific Time</option>
                                    <option value="UTC" <?php echo $settings['timezone'] == 'UTC' ? 'selected' : ''; ?>>UTC</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="date_format" class="form-label">Date Format</label>
                                <select class="form-select" id="date_format" name="date_format">
                                    <option value="Y-m-d" <?php echo $settings['date_format'] == 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                    <option value="m/d/Y" <?php echo $settings['date_format'] == 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                    <option value="d/m/Y" <?php echo $settings['date_format'] == 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                    <option value="M d, Y" <?php echo $settings['date_format'] == 'M d, Y' ? 'selected' : ''; ?>>Mon DD, YYYY</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="time_format" class="form-label">Time Format</label>
                                <select class="form-select" id="time_format" name="time_format">
                                    <option value="H:i:s" <?php echo $settings['time_format'] == 'H:i:s' ? 'selected' : ''; ?>>24-hour (HH:MM:SS)</option>
                                    <option value="h:i:s A" <?php echo $settings['time_format'] == 'h:i:s A' ? 'selected' : ''; ?>>12-hour (HH:MM:SS AM/PM)</option>
                                    <option value="H:i" <?php echo $settings['time_format'] == 'H:i' ? 'selected' : ''; ?>>24-hour (HH:MM)</option>
                                    <option value="h:i A" <?php echo $settings['time_format'] == 'h:i A' ? 'selected' : ''; ?>>12-hour (HH:MM AM/PM)</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="navigation_layout" class="form-label">Navigation Layout</label>
                                <select class="form-select" id="navigation_layout" name="navigation_layout">
                                    <option value="sidenav" <?php echo $settings['navigation_layout'] == 'sidenav' ? 'selected' : ''; ?>>Side Navigation (Default)</option>
                                    <option value="topnav" <?php echo $settings['navigation_layout'] == 'topnav' ? 'selected' : ''; ?>>Top Navigation Only</option>
                                </select>
                                <small class="text-muted">When "Top Navigation Only" is selected, the sidebar will be hidden and the main content will use full width.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div id="security-settings-card" class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-shield-alt"></i> Security Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="session_timeout" class="form-label">Session Timeout (seconds)</label>
                                <input type="number" class="form-control" id="session_timeout" name="session_timeout"
                                       value="<?php echo htmlspecialchars($settings['session_timeout']); ?>" min="300" required>
                                <small class="text-muted">Default: 1800 (30 minutes)</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="password_min_length" class="form-label">Minimum Password Length</label>
                                <input type="number" class="form-control" id="password_min_length" name="password_min_length"
                                       value="<?php echo htmlspecialchars($settings['password_min_length']); ?>" min="6" max="32" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="api_rate_limit" class="form-label">API Rate Limit (per hour)</label>
                                <input type="number" class="form-control" id="api_rate_limit" name="api_rate_limit"
                                       value="<?php echo htmlspecialchars($settings['api_rate_limit']); ?>" min="0" required>
                                <small class="text-muted">0 = unlimited</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="allow_registration" name="allow_registration"
                                           value="1" <?php echo $settings['allow_registration'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="allow_registration">Allow User Registration</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="require_email_verification" name="require_email_verification"
                                           value="1" <?php echo $settings['require_email_verification'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="require_email_verification">Require Email Verification</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="enable_api" name="enable_api"
                                           value="1" <?php echo $settings['enable_api'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_api">Enable API Access</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="enable_notifications" name="enable_notifications"
                                           value="1" <?php echo $settings['enable_notifications'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="enable_notifications">Enable Notifications</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- System Limits -->
                <div id="limits-settings-card" class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-tachometer-alt"></i> System Limits</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="max_file_size" class="form-label">Max File Upload Size (bytes)</label>
                                <input type="number" class="form-control" id="max_file_size" name="max_file_size"
                                       value="<?php echo htmlspecialchars($settings['max_file_size']); ?>" min="1024" required>
                                <small class="text-muted">Default: 10485760 (10MB)</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="max_team_members" class="form-label">Max Members per Team</label>
                                <input type="number" class="form-control" id="max_team_members" name="max_team_members"
                                       value="<?php echo htmlspecialchars($settings['max_team_members']); ?>" min="1" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="max_teams_per_user" class="form-label">Max Teams per User</label>
                                <input type="number" class="form-control" id="max_teams_per_user" name="max_teams_per_user"
                                       value="<?php echo htmlspecialchars($settings['max_teams_per_user']); ?>" min="1" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email Settings -->
                <div id="email-settings-card" class="card mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-envelope"></i> Email Settings (SMTP)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="smtp_host" class="form-label">SMTP Host</label>
                                <input type="text" class="form-control" id="smtp_host" name="smtp_host"
                                       value="<?php echo htmlspecialchars($settings['smtp_host']); ?>"
                                       placeholder="smtp.gmail.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="smtp_port" class="form-label">SMTP Port</label>
                                <input type="number" class="form-control" id="smtp_port" name="smtp_port"
                                       value="<?php echo htmlspecialchars($settings['smtp_port']); ?>"
                                       placeholder="587">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="smtp_username" class="form-label">SMTP Username</label>
                                <input type="text" class="form-control" id="smtp_username" name="smtp_username"
                                       value="<?php echo htmlspecialchars($settings['smtp_username']); ?>"
                                       placeholder="your-email@domain.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="smtp_password" class="form-label">SMTP Password</label>
                                <input type="password" class="form-control" id="smtp_password" name="smtp_password"
                                       placeholder="Leave blank to keep current password">
                                <small class="text-muted">Password is encrypted in database</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="smtp_from_email" class="form-label">From Email</label>
                                <input type="email" class="form-control" id="smtp_from_email" name="smtp_from_email"
                                       value="<?php echo htmlspecialchars($settings['smtp_from_email']); ?>"
                                       placeholder="noreply@yourdomain.com">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="smtp_from_name" class="form-label">From Name</label>
                                <input type="text" class="form-control" id="smtp_from_name" name="smtp_from_name"
                                       value="<?php echo htmlspecialchars($settings['smtp_from_name']); ?>"
                                       placeholder="Your App Name">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Mode -->
                <div id="maintenance-settings-card" class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-tools"></i> Maintenance Mode</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode"
                                   value="1" <?php echo $settings['maintenance_mode'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="maintenance_mode">Enable Maintenance Mode</label>
                        </div>
                        <div class="mb-3">
                            <label for="maintenance_message" class="form-label">Maintenance Message</label>
                            <textarea class="form-control" id="maintenance_message" name="maintenance_message" rows="3"><?php echo htmlspecialchars($settings['maintenance_message']); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div id="form-actions" class="d-flex justify-content-end mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Save All Settings
                    </button>
                </div>
            </form>
        </main>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Handle form submission
    $('#settings-form').on('submit', function(e) {
        // Convert unchecked checkboxes to 0
        $('input[type="checkbox"]').each(function() {
            if (!$(this).is(':checked')) {
                $(this).after('<input type="hidden" name="' + $(this).attr('name') + '" value="0">');
            }
        });
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>