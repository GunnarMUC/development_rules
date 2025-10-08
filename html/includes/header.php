<?php
// Header component with Bootstrap 5 navbar and user dropdown
// Ensure session is started if not already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get navigation layout setting
$navigation_layout = 'sidenav'; // default
try {
    require_once __DIR__ . '/../classes/Database.php';
    require_once __DIR__ . '/../classes/Settings.php';
    $settingsObj = new Settings();
    $navigation_layout = $settingsObj->get('navigation_layout', 'sidenav');
} catch (Exception $e) {
    // If settings can't be loaded, use default
    $navigation_layout = 'sidenav';
}

// Store in session for use in other pages
$_SESSION['navigation_layout'] = $navigation_layout;

// Get current user info if available
$current_user = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// If user is logged in but $_SESSION['user'] is not set (old session format), fix it
if (!$current_user && isset($_SESSION['user_id'])) {
    // Try to get user info and update session
    require_once __DIR__ . '/auth.php';
    $user_info = get_current_user_info();
    if ($user_info) {
        $_SESSION['user'] = [
            'id' => $user_info['id'],
            'email' => $user_info['email'],
            'first_name' => $user_info['first_name'],
            'last_name' => $user_info['last_name'],
            'role' => $user_info['role'] ?? 'user'
        ];
        $current_user = $_SESSION['user'];
    }
}

// Get teams for current user if logged in
$userTeams = [];
$currentTeamId = null;
$currentTeamName = null;

if (isset($_SESSION['user_id'])) {
    try {
        require_once __DIR__ . '/../classes/Team.php';
        $teamModel = new Team();
        $userTeams = $teamModel->getUserTeams($_SESSION['user_id']);

        // Check if a team is selected in session
        if (isset($_SESSION['current_team_id'])) {
            $currentTeamId = $_SESSION['current_team_id'];
            // Find the team name
            foreach ($userTeams as $team) {
                if ($team['id'] == $currentTeamId) {
                    $currentTeamName = $team['name'];
                    break;
                }
            }
        }
    } catch (Exception $e) {
        // If database connection fails, continue without team functionality
        // This allows the page to load even without database access
        error_log("Team functionality unavailable: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - ' : ''; ?>SaaS Template</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/custom.css">

    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.13.5/dist/cdn.min.js"></script>

    <!-- jQuery UI CSS for autocomplete (will be migrated) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <!-- Toastr CSS for notifications (will be migrated to Alpine Toast) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- HTMX CSRF Token -->
    <meta name="htmx-config" content='{"getCacheBusterParam":true}'>
    <?php if(isset($_SESSION['csrf_token'])): ?>
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <?php endif; ?>

    <!-- Page-specific CSS -->
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 60px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
            background-color: #fff;
            z-index: 1040;
        }

        .navbar-brand {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .navbar-brand i {
            font-size: 1.5rem;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .sidebar-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.25rem 0.5rem;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block;
            }
        }

        .dropdown-menu {
            box-shadow: 0 10px 40px rgba(0,0,0,.1);
            border: none;
            padding: 0.5rem;
        }

        .dropdown-item {
            border-radius: 4px;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .dropdown-item i {
            width: 20px;
            text-align: center;
        }


        /* jQuery UI Autocomplete Customization */
        .ui-autocomplete {
            max-height: 400px;
            overflow-y: auto;
            border: none;
            box-shadow: 0 10px 40px rgba(0,0,0,.1);
            border-radius: 8px;
            padding: 0.5rem;
        }

        .ui-menu-item {
            list-style: none;
            margin: 0.25rem 0;
        }

        .ui-menu-item-wrapper {
            padding: 0.75rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .ui-menu-item-wrapper:hover,
        .ui-state-active {
            background-color: #f0f6ff !important;
            border: none !important;
        }

        .search-result-icon {
            font-size: 1.2rem;
            width: 30px;
            text-align: center;
            color: #6c757d;
        }

        .search-result-content {
            flex: 1;
        }

        .search-result-title {
            font-weight: 500;
            color: #212529;
            margin-bottom: 2px;
        }

        .search-result-description {
            font-size: 0.875rem;
            color: #6c757d;
        }

        .search-result-category {
            font-size: 0.75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .search-result-badge {
            font-size: 0.75rem;
        }

        /* Notification Dropdown Styles */
        .notifications-dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 350px;
            max-height: 400px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0,0,0,.1);
            display: none;
            z-index: 1050;
        }

        .notifications-dropdown.show {
            display: block;
        }

        .notifications-header {
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .notifications-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item.unread {
            background-color: #f0f6ff;
        }

        .notification-title {
            font-weight: 500;
            margin-bottom: 4px;
        }

        .notification-message {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 4px;
        }

        .notification-time {
            font-size: 0.75rem;
            color: #adb5bd;
        }

        .notifications-footer {
            padding: 0.75rem;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }

    </style>

    <?php if(isset($additional_css)): ?>
    <?php echo $additional_css; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top" id="main-navbar">
        <div class="container-fluid">
            <!-- Mobile Sidebar Toggle -->
            <button class="sidebar-toggle" id="sidebar-toggle-btn" type="button">
                <i class="bi bi-list"></i>
            </button>

            <!-- Brand -->
            <a class="navbar-brand" href="dashboard.php" id="navbar-brand">
                <i class="bi bi-box-seam text-primary"></i>
                <span>SaaS Template</span>
            </a>

            <!-- Mobile toggle for main nav -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Main Navigation Items -->
            <div class="navbar-collapse collapse" id="navbarNav">
                <ul class="navbar-nav ms-3">
                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php" id="topnav-dashboard">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>

                    <!-- Tasks Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="topnav-tasks" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-list-task"></i> Tasks
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="tasks.php">All Tasks</a></li>
                            <li><a class="dropdown-item" href="my-tasks.php">My Tasks</a></li>
                            <li><a class="dropdown-item" href="create-task.php">Create Task</a></li>
                        </ul>
                    </li>

                    <!-- Kanban Board -->
                    <li class="nav-item">
                        <a class="nav-link" href="kanban.php" id="topnav-kanban">
                            <i class="bi bi-kanban"></i> Kanban Board
                        </a>
                    </li>

                    <!-- Calendar -->
                    <li class="nav-item">
                        <a class="nav-link" href="calendar.php" id="topnav-calendar">
                            <i class="bi bi-calendar3"></i> Calendar
                        </a>
                    </li>

                    <!-- Teams Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="topnav-teams" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-people"></i> Teams
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="my-teams.php">My Teams</a></li>
                            <li><a class="dropdown-item" href="team-members.php">Team Members</a></li>
                            <li><a class="dropdown-item" href="manage-teams.php">Manage Teams</a></li>
                        </ul>
                    </li>

                    <!-- Reports -->
                    <li class="nav-item">
                        <a class="nav-link" href="reports.php" id="topnav-reports">
                            <i class="bi bi-graph-up"></i> Reports
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Team Selector removed per request -->

            <!-- Right Side Items -->
            <div class="ms-auto d-flex align-items-center gap-3">
                <!-- Admin Dropdown (for admin and super_admin users) -->
                <?php
                $is_admin = isset($_SESSION['user']['role']) &&
                           ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['role'] === 'super_admin');
                if($is_admin):
                ?>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="admin-dropdown-btn">
                        <i class="bi bi-shield-lock"></i> Admin
                    </button>
                    <ul class="dropdown-menu" id="admin-dropdown-menu">
                        <li><h6 class="dropdown-header">Administration</h6></li>
                        <li><a class="dropdown-item" href="admin-users.php" id="admin-link-users">
                            <i class="bi bi-person-gear"></i> User Management
                        </a></li>
                        <li><a class="dropdown-item" href="admin-settings.php" id="admin-link-settings">
                            <i class="bi bi-sliders"></i> System Settings
                        </a></li>
                        <?php if ($current_user['role'] === 'super_admin'): ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="global-settings.php" id="admin-link-global-settings">
                            <i class="bi bi-gear-wide-connected"></i> Global Settings
                        </a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Notifications -->
                <div class="position-relative">
                    <button class="btn btn-link text-dark position-relative p-1" id="notifications-btn">
                        <i class="bi bi-bell" style="font-size: 1.2rem;"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                              id="notification-badge"
                              style="display: none;">
                            <span id="notification-count">0</span>
                            <span class="visually-hidden">unread notifications</span>
                        </span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div class="notifications-dropdown" id="notifications-dropdown">
                        <div class="notifications-header">
                            <h6 class="mb-0">Notifications</h6>
                            <button class="btn btn-sm btn-link text-decoration-none" id="mark-all-read">
                                Mark all as read
                            </button>
                        </div>
                        <div class="notifications-list" id="notifications-list">
                            <!-- Notifications will be loaded here -->
                        </div>
                        <div class="notifications-footer">
                            <a href="notifications.php" class="text-decoration-none">View all notifications</a>
                        </div>
                    </div>
                </div>

                <!-- User Dropdown -->
                <?php if($current_user): ?>
                <div class="dropdown">
                    <div class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false" id="user-dropdown-trigger">
                        <div class="user-avatar" id="user-avatar">
                            <?php
                            $initials = strtoupper(substr($current_user['first_name'], 0, 1) . substr($current_user['last_name'], 0, 1));
                            echo htmlspecialchars($initials);
                            ?>
                        </div>
                        <span class="d-none d-md-block text-white" id="user-name">
                            <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?>
                        </span>
                        <i class="bi bi-chevron-down small"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end" id="user-dropdown-menu">
                        <li>
                            <div class="dropdown-item-text px-3 py-2" id="user-info">
                                <div class="fw-bold"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($current_user['email']); ?></div>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="profile.php" id="profile-link">
                            <i class="bi bi-person"></i> My Profile
                        </a></li>
                        <li><a class="dropdown-item" href="settings.php" id="settings-link">
                            <i class="bi bi-gear"></i> Settings
                        </a></li>
                        <li><a class="dropdown-item" href="help.php" id="help-link">
                            <i class="bi bi-question-circle"></i> Help & Support
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php" id="logout-link">
                            <i class="bi bi-box-arrow-right"></i> Sign Out
                        </a></li>
                    </ul>
                </div>
                <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-sm" id="login-btn">Sign In</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Page Container - Changed from flex to grid layout for integrated sidebar -->
    <div class="row g-0" id="page-wrapper">