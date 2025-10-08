<?php
// Settings page
require_once 'includes/session.php';
require_once 'includes/auth.php';

// Require login
require_login();

// Get current user
$user = get_current_user_info();

// Get database connection
require_once 'includes/db.php';

// Handle settings update
$success_message = '';
$error_message = '';

// Get current user settings
$stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt->execute([$user['id']]);
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

// Initialize default settings if none exist
if (!$settings) {
    $stmt = $pdo->prepare("INSERT INTO user_settings (user_id, theme, language, timezone, date_format, time_format, notifications_email, notifications_push, notifications_sound, email_daily_summary, email_task_updates, email_mentions, display_density, sidebar_collapsed, show_task_numbers, default_view) VALUES (?, 'light', 'en', 'UTC', 'MM/DD/YYYY', '12h', 1, 1, 1, 1, 1, 1, 'comfortable', 0, 1, 'list')");
    $stmt->execute([$user['id']]);

    $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_appearance'])) {
        // Update appearance settings
        $theme = $_POST['theme'];
        $display_density = $_POST['display_density'];
        $sidebar_collapsed = isset($_POST['sidebar_collapsed']) ? 1 : 0;
        $show_task_numbers = isset($_POST['show_task_numbers']) ? 1 : 0;
        $default_view = $_POST['default_view'];

        try {
            $stmt = $pdo->prepare("UPDATE user_settings SET theme = ?, display_density = ?, sidebar_collapsed = ?, show_task_numbers = ?, default_view = ? WHERE user_id = ?");
            $stmt->execute([$theme, $display_density, $sidebar_collapsed, $show_task_numbers, $default_view, $user['id']]);

            $success_message = 'Appearance settings updated successfully!';

            // Refresh settings
            $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error_message = 'Error updating appearance settings.';
        }
    } elseif (isset($_POST['update_regional'])) {
        // Update regional settings
        $language = $_POST['language'];
        $timezone = $_POST['timezone'];
        $date_format = $_POST['date_format'];
        $time_format = $_POST['time_format'];

        try {
            $stmt = $pdo->prepare("UPDATE user_settings SET language = ?, timezone = ?, date_format = ?, time_format = ? WHERE user_id = ?");
            $stmt->execute([$language, $timezone, $date_format, $time_format, $user['id']]);

            $success_message = 'Regional settings updated successfully!';

            // Refresh settings
            $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error_message = 'Error updating regional settings.';
        }
    } elseif (isset($_POST['update_notifications'])) {
        // Update notification settings
        $notifications_email = isset($_POST['notifications_email']) ? 1 : 0;
        $notifications_push = isset($_POST['notifications_push']) ? 1 : 0;
        $notifications_sound = isset($_POST['notifications_sound']) ? 1 : 0;
        $email_daily_summary = isset($_POST['email_daily_summary']) ? 1 : 0;
        $email_task_updates = isset($_POST['email_task_updates']) ? 1 : 0;
        $email_mentions = isset($_POST['email_mentions']) ? 1 : 0;

        try {
            $stmt = $pdo->prepare("UPDATE user_settings SET notifications_email = ?, notifications_push = ?, notifications_sound = ?, email_daily_summary = ?, email_task_updates = ?, email_mentions = ? WHERE user_id = ?");
            $stmt->execute([$notifications_email, $notifications_push, $notifications_sound, $email_daily_summary, $email_task_updates, $email_mentions, $user['id']]);

            $success_message = 'Notification settings updated successfully!';

            // Refresh settings
            $stmt = $pdo->prepare("SELECT * FROM user_settings WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error_message = 'Error updating notification settings.';
        }
    }
}

// Get timezones list
$timezones = timezone_identifiers_list();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Task Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div id="settings-header" class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Settings</h1>
                </div>

                <?php if ($success_message): ?>
                    <div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div id="error-alert" class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div id="settings-tabs">
                    <ul class="nav nav-tabs mb-4" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance" type="button">
                                <i class="bi bi-palette me-2"></i>Appearance
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="regional-tab" data-bs-toggle="tab" data-bs-target="#regional" type="button">
                                <i class="bi bi-globe me-2"></i>Regional
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications" type="button">
                                <i class="bi bi-bell me-2"></i>Notifications
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" type="button">
                                <i class="bi bi-shield-lock me-2"></i>Privacy
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="integrations-tab" data-bs-toggle="tab" data-bs-target="#integrations" type="button">
                                <i class="bi bi-plug me-2"></i>Integrations
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Appearance Tab -->
                        <div class="tab-pane fade show active" id="appearance" role="tabpanel">
                            <div id="appearance-card" class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Appearance Settings</h5>
                                    <form method="POST">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="theme" class="form-label">Theme</label>
                                                <select class="form-select" id="theme" name="theme">
                                                    <option value="light" <?php echo $settings['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                                                    <option value="dark" <?php echo $settings['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                                                    <option value="auto" <?php echo $settings['theme'] === 'auto' ? 'selected' : ''; ?>>Auto (Follow System)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="display_density" class="form-label">Display Density</label>
                                                <select class="form-select" id="display_density" name="display_density">
                                                    <option value="compact" <?php echo $settings['display_density'] === 'compact' ? 'selected' : ''; ?>>Compact</option>
                                                    <option value="comfortable" <?php echo $settings['display_density'] === 'comfortable' ? 'selected' : ''; ?>>Comfortable</option>
                                                    <option value="spacious" <?php echo $settings['display_density'] === 'spacious' ? 'selected' : ''; ?>>Spacious</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="default_view" class="form-label">Default Task View</label>
                                                <select class="form-select" id="default_view" name="default_view">
                                                    <option value="list" <?php echo $settings['default_view'] === 'list' ? 'selected' : ''; ?>>List View</option>
                                                    <option value="board" <?php echo $settings['default_view'] === 'board' ? 'selected' : ''; ?>>Board View</option>
                                                    <option value="calendar" <?php echo $settings['default_view'] === 'calendar' ? 'selected' : ''; ?>>Calendar View</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="sidebar_collapsed" name="sidebar_collapsed" <?php echo $settings['sidebar_collapsed'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="sidebar_collapsed">
                                                    Collapse sidebar by default
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="show_task_numbers" name="show_task_numbers" <?php echo $settings['show_task_numbers'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="show_task_numbers">
                                                    Show task ID numbers
                                                </label>
                                            </div>
                                        </div>

                                        <button type="submit" name="update_appearance" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-2"></i>Save Appearance Settings
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Regional Tab -->
                        <div class="tab-pane fade" id="regional" role="tabpanel">
                            <div id="regional-card" class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Regional Settings</h5>
                                    <form method="POST">
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="language" class="form-label">Language</label>
                                                <select class="form-select" id="language" name="language">
                                                    <option value="en" <?php echo $settings['language'] === 'en' ? 'selected' : ''; ?>>English</option>
                                                    <option value="es" <?php echo $settings['language'] === 'es' ? 'selected' : ''; ?>>Spanish</option>
                                                    <option value="fr" <?php echo $settings['language'] === 'fr' ? 'selected' : ''; ?>>French</option>
                                                    <option value="de" <?php echo $settings['language'] === 'de' ? 'selected' : ''; ?>>German</option>
                                                    <option value="it" <?php echo $settings['language'] === 'it' ? 'selected' : ''; ?>>Italian</option>
                                                    <option value="pt" <?php echo $settings['language'] === 'pt' ? 'selected' : ''; ?>>Portuguese</option>
                                                    <option value="zh" <?php echo $settings['language'] === 'zh' ? 'selected' : ''; ?>>Chinese</option>
                                                    <option value="ja" <?php echo $settings['language'] === 'ja' ? 'selected' : ''; ?>>Japanese</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="timezone" class="form-label">Timezone</label>
                                                <select class="form-select" id="timezone" name="timezone">
                                                    <?php foreach($timezones as $tz): ?>
                                                        <option value="<?php echo $tz; ?>" <?php echo $settings['timezone'] === $tz ? 'selected' : ''; ?>><?php echo $tz; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="date_format" class="form-label">Date Format</label>
                                                <select class="form-select" id="date_format" name="date_format">
                                                    <option value="MM/DD/YYYY" <?php echo $settings['date_format'] === 'MM/DD/YYYY' ? 'selected' : ''; ?>>MM/DD/YYYY</option>
                                                    <option value="DD/MM/YYYY" <?php echo $settings['date_format'] === 'DD/MM/YYYY' ? 'selected' : ''; ?>>DD/MM/YYYY</option>
                                                    <option value="YYYY-MM-DD" <?php echo $settings['date_format'] === 'YYYY-MM-DD' ? 'selected' : ''; ?>>YYYY-MM-DD</option>
                                                    <option value="DD.MM.YYYY" <?php echo $settings['date_format'] === 'DD.MM.YYYY' ? 'selected' : ''; ?>>DD.MM.YYYY</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="time_format" class="form-label">Time Format</label>
                                                <select class="form-select" id="time_format" name="time_format">
                                                    <option value="12h" <?php echo $settings['time_format'] === '12h' ? 'selected' : ''; ?>>12-hour (AM/PM)</option>
                                                    <option value="24h" <?php echo $settings['time_format'] === '24h' ? 'selected' : ''; ?>>24-hour</option>
                                                </select>
                                            </div>
                                        </div>

                                        <button type="submit" name="update_regional" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-2"></i>Save Regional Settings
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications Tab -->
                        <div class="tab-pane fade" id="notifications" role="tabpanel">
                            <div id="notifications-card" class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Notification Settings</h5>
                                    <form method="POST">
                                        <h6 class="mb-3">General Notifications</h6>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="notifications_email" name="notifications_email" <?php echo $settings['notifications_email'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="notifications_email">
                                                    Email notifications
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="notifications_push" name="notifications_push" <?php echo $settings['notifications_push'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="notifications_push">
                                                    Push notifications
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="notifications_sound" name="notifications_sound" <?php echo $settings['notifications_sound'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="notifications_sound">
                                                    Sound notifications
                                                </label>
                                            </div>
                                        </div>

                                        <h6 class="mb-3">Email Preferences</h6>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="email_daily_summary" name="email_daily_summary" <?php echo $settings['email_daily_summary'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="email_daily_summary">
                                                    Daily task summary
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="email_task_updates" name="email_task_updates" <?php echo $settings['email_task_updates'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="email_task_updates">
                                                    Task updates and changes
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="email_mentions" name="email_mentions" <?php echo $settings['email_mentions'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="email_mentions">
                                                    When someone mentions me
                                                </label>
                                            </div>
                                        </div>

                                        <button type="submit" name="update_notifications" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-2"></i>Save Notification Settings
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Privacy Tab -->
                        <div class="tab-pane fade" id="privacy" role="tabpanel">
                            <div id="privacy-card" class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Privacy & Security</h5>

                                    <div id="privacy-options" class="mb-4">
                                        <h6 class="mb-3">Profile Visibility</h6>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="profile_visibility" id="visibility_public" value="public">
                                            <label class="form-check-label" for="visibility_public">
                                                Public - Anyone can see your profile
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="profile_visibility" id="visibility_team" value="team" checked>
                                            <label class="form-check-label" for="visibility_team">
                                                Team Only - Only team members can see your profile
                                            </label>
                                        </div>
                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="radio" name="profile_visibility" id="visibility_private" value="private">
                                            <label class="form-check-label" for="visibility_private">
                                                Private - Only you can see your profile
                                            </label>
                                        </div>

                                        <h6 class="mb-3">Security Options</h6>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="two_factor" disabled>
                                            <label class="form-check-label" for="two_factor">
                                                Enable two-factor authentication <span class="badge bg-secondary">Coming Soon</span>
                                            </label>
                                        </div>
                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="session_timeout" checked>
                                            <label class="form-check-label" for="session_timeout">
                                                Automatically log out after 30 minutes of inactivity
                                            </label>
                                        </div>

                                        <h6 class="mb-3">Data & Privacy</h6>
                                        <div class="d-grid gap-2 d-md-block">
                                            <button class="btn btn-outline-primary" disabled>
                                                <i class="bi bi-download me-2"></i>Download My Data
                                            </button>
                                            <button class="btn btn-outline-danger" disabled>
                                                <i class="bi bi-trash me-2"></i>Delete My Account
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Integrations Tab -->
                        <div class="tab-pane fade" id="integrations" role="tabpanel">
                            <div id="integrations-card" class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-4">Integrations</h5>

                                    <div id="integrations-list" class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <i class="bi bi-slack fs-3 me-3"></i>
                                                        <div>
                                                            <h6 class="mb-0">Slack</h6>
                                                            <small class="text-muted">Team communication</small>
                                                        </div>
                                                        <div class="ms-auto">
                                                            <span class="badge bg-secondary">Not Connected</span>
                                                        </div>
                                                    </div>
                                                    <button class="btn btn-sm btn-outline-primary" disabled>
                                                        <i class="bi bi-link-45deg me-1"></i>Connect
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <i class="bi bi-github fs-3 me-3"></i>
                                                        <div>
                                                            <h6 class="mb-0">GitHub</h6>
                                                            <small class="text-muted">Code repository</small>
                                                        </div>
                                                        <div class="ms-auto">
                                                            <span class="badge bg-secondary">Not Connected</span>
                                                        </div>
                                                    </div>
                                                    <button class="btn btn-sm btn-outline-primary" disabled>
                                                        <i class="bi bi-link-45deg me-1"></i>Connect
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <i class="bi bi-google fs-3 me-3"></i>
                                                        <div>
                                                            <h6 class="mb-0">Google Calendar</h6>
                                                            <small class="text-muted">Calendar sync</small>
                                                        </div>
                                                        <div class="ms-auto">
                                                            <span class="badge bg-secondary">Not Connected</span>
                                                        </div>
                                                    </div>
                                                    <button class="btn btn-sm btn-outline-primary" disabled>
                                                        <i class="bi bi-link-45deg me-1"></i>Connect
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center mb-3">
                                                        <i class="bi bi-microsoft-teams fs-3 me-3"></i>
                                                        <div>
                                                            <h6 class="mb-0">Microsoft Teams</h6>
                                                            <small class="text-muted">Team collaboration</small>
                                                        </div>
                                                        <div class="ms-auto">
                                                            <span class="badge bg-secondary">Not Connected</span>
                                                        </div>
                                                    </div>
                                                    <button class="btn btn-sm btn-outline-primary" disabled>
                                                        <i class="bi bi-link-45deg me-1"></i>Connect
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="api-section" class="mt-4 pt-4 border-top">
                                        <h6 class="mb-3">API Access</h6>
                                        <p class="text-muted">Generate API keys to integrate with external applications.</p>
                                        <button class="btn btn-outline-primary" disabled>
                                            <i class="bi bi-key me-2"></i>Generate API Key
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script>
        // Apply theme if set to dark
        document.addEventListener('DOMContentLoaded', function() {
            <?php if ($settings['theme'] === 'dark'): ?>
                document.body.classList.add('dark-mode');
            <?php elseif ($settings['theme'] === 'auto'): ?>
                // Check system preference
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.body.classList.add('dark-mode');
                }
            <?php endif; ?>
        });
    </script>
</body>
</html>