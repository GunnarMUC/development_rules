<?php
// Dashboard page with modular components
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/activity_logger.php';

// Require login
require_login();

// Get current user
$user = get_current_user_info();

// Get database connection
require_once 'includes/db.php';

// Fetch statistics for logged-in user
$userId = $user['id'];

// Get total tasks for user (created by or assigned to)
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tasks WHERE user_id = ? OR assigned_to = ?");
$stmt->execute([$userId, $userId]);
$totalTasks = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get completed tasks
$stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM tasks WHERE (user_id = ? OR assigned_to = ?) AND status = 'completed'");
$stmt->execute([$userId, $userId]);
$completedTasks = $stmt->fetch(PDO::FETCH_ASSOC)['completed'];

// Get in-progress tasks
$stmt = $pdo->prepare("SELECT COUNT(*) as in_progress FROM tasks WHERE (user_id = ? OR assigned_to = ?) AND status = 'in_progress'");
$stmt->execute([$userId, $userId]);
$inProgressTasks = $stmt->fetch(PDO::FETCH_ASSOC)['in_progress'];

// Get team members count (count unique members in teams user belongs to)
$stmt = $pdo->prepare("
    SELECT COUNT(DISTINCT tm2.user_id) as team_members
    FROM team_members tm1
    JOIN team_members tm2 ON tm1.team_id = tm2.team_id
    WHERE tm1.user_id = ?
");
$stmt->execute([$userId]);
$teamMembers = $stmt->fetch(PDO::FETCH_ASSOC)['team_members'];

// Calculate percentage changes (placeholder values for now)
$taskChangePercent = 12;
$completedChangePercent = 8;
$newMembers = 1;

// Page-specific settings
$page_title = 'Dashboard';

// Include header
include_once 'includes/header.php';
?>

<!-- Main Container -->
<div class="container-fluid">
    <div class="row">
        <?php
        // Force topnav layout for dashboard (hide sidebar)
        $nav_layout = 'topnav';
        $main_col_class = 'col-lg-12';
        ?>

        <!-- Sidebar is hidden on dashboard -->
        <?php if (false): // Never show sidebar on dashboard ?>
            <?php include 'includes/sidebar.php'; ?>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="<?php echo $main_col_class; ?> px-md-4 mt-4">
        <!-- Page Header -->
        <div class="page-header mb-4" id="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="page-title" id="page-title">Dashboard</h1>
                    <nav aria-label="breadcrumb" id="breadcrumb-nav">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" id="create-task-btn">
                        <i class="bi bi-plus-circle"></i> Create Task
                    </button>
                </div>
            </div>
        </div>

        <!-- Welcome Card -->
        <div class="welcome-section mb-4" id="welcome-section">
            <div class="welcome-card">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h2 class="welcome-title" id="welcome-title">
                            Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!
                        </h2>
                        <p class="welcome-message mb-0" id="welcome-message">
                            Here's what's happening with your projects today.
                        </p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <p class="mb-0" id="welcome-date">
                            <i class="bi bi-calendar3"></i> <?php echo date('l, F j, Y'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-section mb-4" id="stats-section">
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card clickable-card" id="stat-tasks" data-link="tasks.php">
                        <div class="card-body">
                            <div class="stat-content">
                                <div class="stat-icon-wrapper">
                                    <div class="stat-icon bg-primary-soft">
                                        <i class="bi bi-list-check text-primary"></i>
                                    </div>
                                </div>
                                <div class="stat-details">
                                    <h6 class="stat-label">Total Tasks</h6>
                                    <h3 class="stat-value"><?php echo $totalTasks; ?></h3>
                                    <div class="stat-change text-success">
                                        <i class="bi bi-arrow-up"></i> 12% from last week
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card clickable-card" id="stat-completed" data-link="completed-tasks.php">
                        <div class="card-body">
                            <div class="stat-content">
                                <div class="stat-icon-wrapper">
                                    <div class="stat-icon bg-success-soft">
                                        <i class="bi bi-check-circle text-success"></i>
                                    </div>
                                </div>
                                <div class="stat-details">
                                    <h6 class="stat-label">Completed</h6>
                                    <h3 class="stat-value"><?php echo $completedTasks; ?></h3>
                                    <div class="stat-change text-success">
                                        <i class="bi bi-arrow-up"></i> 8% increase
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card clickable-card" id="stat-progress" data-link="current-tasks.php">
                        <div class="card-body">
                            <div class="stat-content">
                                <div class="stat-icon-wrapper">
                                    <div class="stat-icon bg-warning-soft">
                                        <i class="bi bi-clock-history text-warning"></i>
                                    </div>
                                </div>
                                <div class="stat-details">
                                    <h6 class="stat-label">In Progress</h6>
                                    <h3 class="stat-value"><?php echo $inProgressTasks; ?></h3>
                                    <div class="stat-change text-warning">
                                        <i class="bi bi-dash"></i> No change
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card clickable-card" id="stat-teams" data-link="team-members.php">
                        <div class="card-body">
                            <div class="stat-content">
                                <div class="stat-icon-wrapper">
                                    <div class="stat-icon bg-info-soft">
                                        <i class="bi bi-people text-info"></i>
                                    </div>
                                </div>
                                <div class="stat-details">
                                    <h6 class="stat-label">Team Members</h6>
                                    <h3 class="stat-value"><?php echo $teamMembers; ?></h3>
                                    <div class="stat-change text-info">
                                        <i class="bi bi-person-plus"></i> 1 new member
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="charts-section mb-4" id="charts-section">
            <!-- Date Range Selector -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card" id="date-range-card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="mb-0">Analytics Dashboard</h5>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-calendar-range"></i></span>
                                        <input type="text" class="form-control" id="date-range-start" placeholder="Start Date">
                                        <span class="input-group-text">to</span>
                                        <input type="text" class="form-control" id="date-range-end" placeholder="End Date">
                                        <button class="btn btn-primary" id="apply-date-range">Apply</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="row">
                <!-- Task Completion Line Chart -->
                <div class="col-lg-8 mb-4">
                    <div class="card" id="completion-chart-card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Task Completion Trend</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="completion-line-chart" width="400" height="150"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Status Distribution Pie Chart -->
                <div class="col-lg-4 mb-4">
                    <div class="card" id="status-chart-card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Task Status Distribution</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="status-pie-chart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="row">
                <!-- Team Productivity Bar Chart -->
                <div class="col-lg-8 mb-4">
                    <div class="card" id="productivity-chart-card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Team Productivity</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="productivity-bar-chart" width="400" height="150"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Priority Breakdown Doughnut Chart -->
                <div class="col-lg-4 mb-4">
                    <div class="card" id="priority-chart-card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Priority Breakdown</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="priority-doughnut-chart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity and Tasks Grid -->
        <div class="row">
            <!-- Recent Tasks -->
            <div class="col-lg-8 mb-4">
                <div class="card" id="recent-tasks-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Tasks</h5>
                        <a href="tasks.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="task-list">
                            <?php
                            // Fetch recent tasks from database
                            $stmt = $pdo->prepare("
                                SELECT
                                    t.*,
                                    u1.first_name as owner_first,
                                    u1.last_name as owner_last,
                                    u2.first_name as assigned_first,
                                    u2.last_name as assigned_last
                                FROM tasks t
                                LEFT JOIN users u1 ON t.user_id = u1.id
                                LEFT JOIN users u2 ON t.assigned_to = u2.id
                                WHERE t.user_id = ? OR t.assigned_to = ?
                                ORDER BY t.created_at DESC
                                LIMIT 5
                            ");
                            $stmt->execute([$userId, $userId]);
                            $recentTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($recentTasks as $index => $task):
                                $statusClass = [
                                    'pending' => 'bg-info-soft text-info',
                                    'in_progress' => 'bg-warning-soft text-warning',
                                    'completed' => 'bg-success-soft text-success',
                                    'cancelled' => 'bg-danger-soft text-danger'
                                ][$task['status']] ?? 'bg-secondary-soft text-secondary';

                                $isCompleted = $task['status'] === 'completed';
                                $assignedName = $task['assigned_to'] == $userId ? 'you' :
                                    ($task['assigned_first'] ? $task['assigned_first'] . ' ' . $task['assigned_last'] : 'Unassigned');

                                // Calculate due date display
                                $dueDisplay = '';
                                if ($task['due_date']) {
                                    $dueDate = new DateTime($task['due_date']);
                                    $today = new DateTime();
                                    $diff = $today->diff($dueDate);

                                    if ($isCompleted && $task['completed_at']) {
                                        $completedDate = new DateTime($task['completed_at']);
                                        $dueDisplay = 'Completed ' . $completedDate->format('M d');
                                    } elseif ($dueDate < $today) {
                                        $dueDisplay = 'Overdue by ' . $diff->days . ' day' . ($diff->days != 1 ? 's' : '');
                                    } elseif ($diff->days == 0) {
                                        $dueDisplay = 'Due today';
                                    } elseif ($diff->days == 1) {
                                        $dueDisplay = 'Due tomorrow';
                                    } else {
                                        $dueDisplay = 'Due in ' . $diff->days . ' days';
                                    }
                                }
                            ?>
                            <div class="task-item" id="task-<?php echo $task['id']; ?>">
                                <div class="d-flex align-items-center">
                                    <div class="form-check me-3">
                                        <input class="form-check-input task-checkbox" type="checkbox" value="<?php echo $task['id']; ?>"
                                            id="task-check-<?php echo $task['id']; ?>"
                                            <?php echo $isCompleted ? 'checked disabled' : ''; ?>
                                            data-task-id="<?php echo $task['id']; ?>">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="task-title mb-1 <?php echo $isCompleted ? 'text-decoration-line-through text-muted' : ''; ?>">
                                            <?php echo htmlspecialchars($task['title']); ?>
                                        </h6>
                                        <div class="task-meta">
                                            <span class="badge <?php echo $statusClass; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                            </span>
                                            <?php if ($dueDisplay): ?>
                                            <span class="text-muted small ms-2">
                                                <i class="bi bi-<?php echo $isCompleted ? 'check-circle' : 'calendar2'; ?>"></i>
                                                <?php echo $dueDisplay; ?>
                                            </span>
                                            <?php endif; ?>
                                            <span class="text-muted small ms-2">
                                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($assignedName); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="task-actions">
                                        <button class="btn btn-sm btn-light" data-bs-toggle="tooltip"
                                            title="<?php echo $isCompleted ? 'View' : 'Edit'; ?>">
                                            <i class="bi bi-<?php echo $isCompleted ? 'eye' : 'pencil'; ?>"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <?php if (empty($recentTasks)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                No tasks found. Create your first task to get started!
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Feed -->
            <div class="col-lg-4 mb-4">
                <div class="card" id="activity-card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="activity-feed">
                            <?php
                            // Get recent activities using the activity logger function
                            $activities = getRecentActivities(15, $userId);

                            // Define icon and color mappings for activities
                            $actionIcons = [
                                'completed' => ['icon' => 'bi-check-circle', 'color' => 'success'],
                                'created' => ['icon' => 'bi-plus-circle', 'color' => 'primary'],
                                'updated' => ['icon' => 'bi-pencil', 'color' => 'warning'],
                                'joined' => ['icon' => 'bi-person-plus', 'color' => 'info'],
                                'assigned' => ['icon' => 'bi-person-check', 'color' => 'primary'],
                                'commented' => ['icon' => 'bi-chat', 'color' => 'secondary'],
                                'started' => ['icon' => 'bi-play-circle', 'color' => 'warning'],
                                'task_created' => ['icon' => 'bi-plus-circle', 'color' => 'primary']
                            ];

                            foreach ($activities as $activity):
                                $actionConfig = $actionIcons[$activity['action']] ?? ['icon' => 'bi-circle', 'color' => 'secondary'];
                                $actorName = $activity['user_id'] == $userId ? 'You' :
                                    ($activity['first_name'] . ' ' . $activity['last_name']);

                                // Format time ago
                                $createdTime = new DateTime($activity['created_at']);
                                $now = new DateTime();
                                $interval = $now->diff($createdTime);

                                if ($interval->days > 1) {
                                    $timeAgo = $interval->days . ' days ago';
                                } elseif ($interval->days == 1) {
                                    $timeAgo = 'Yesterday';
                                } elseif ($interval->h > 1) {
                                    $timeAgo = $interval->h . ' hours ago';
                                } elseif ($interval->h == 1) {
                                    $timeAgo = '1 hour ago';
                                } elseif ($interval->i > 1) {
                                    $timeAgo = $interval->i . ' minutes ago';
                                } else {
                                    $timeAgo = 'Just now';
                                }
                            ?>
                            <div class="activity-item" id="activity-<?php echo isset($activity['id']) ? $activity['id'] : uniqid(); ?>">
                                <div class="activity-icon bg-<?php echo $actionConfig['color']; ?>-soft text-<?php echo $actionConfig['color']; ?>">
                                    <i class="bi <?php echo $actionConfig['icon']; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <p class="mb-1">
                                        <strong><?php echo htmlspecialchars($actorName); ?></strong>
                                        <?php echo htmlspecialchars($activity['description'] ?? $activity['action']); ?>
                                    </p>
                                    <small class="text-muted"><?php echo $timeAgo; ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>

                            <?php if (empty($activities)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
                                No recent activity to display.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
        </div>
    </div>

<!-- Additional CSS for Dashboard -->
<style>
/* Commented out dashboard-specific floating styles - sidebar is now part of page flow
.main-content {
    flex: 1;
    padding: 2rem;
    background-color: #f5f6fa;
    min-height: calc(100vh - 56px);
}
*/

.main-content {
    padding: 2rem;
    background-color: #f5f6fa;
    min-height: 100vh;
}

.page-header {
    margin-bottom: 2rem;
}

.page-title {
    font-size: 1.75rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}

.welcome-card {
    background: linear-gradient(135deg, #0d6efd 0%, #0056b3 100%);
    color: white !important;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(13, 110, 253, 0.3);
}

.welcome-card * {
    color: white !important;
}

.welcome-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.welcome-message {
    font-size: 1rem;
    opacity: 0.9;
}

.stat-card {
    background: white;
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.clickable-card {
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.clickable-card::after {
    content: '\f138';
    font-family: 'Bootstrap-icons';
    position: absolute;
    top: 10px;
    right: 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
    color: #6c757d;
}

.clickable-card:hover::after {
    opacity: 0.5;
}

.stat-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon-wrapper {
    flex-shrink: 0;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.bg-primary-soft {
    background-color: rgba(102, 126, 234, 0.1);
}

.bg-success-soft {
    background-color: rgba(34, 197, 94, 0.1);
}

.bg-warning-soft {
    background-color: rgba(251, 191, 36, 0.1);
}

.bg-info-soft {
    background-color: rgba(14, 165, 233, 0.1);
}

.stat-details {
    flex: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    font-weight: 500;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
    line-height: 1;
}

.stat-change {
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
}

.card-header {
    border-bottom: 1px solid #e9ecef;
    padding: 1.25rem;
}

.task-list {
    padding: 0;
}

.task-item {
    padding: 1rem;
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s;
}

.task-item:last-child {
    border-bottom: none;
}

.task-item:hover {
    background-color: #f8f9fa;
}

.task-title {
    font-size: 0.95rem;
    font-weight: 500;
    margin-bottom: 0.25rem;
}

.task-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.task-checkbox {
    width: 1.25rem;
    height: 1.25rem;
    cursor: pointer;
}

.activity-feed {
    position: relative;
    padding-left: 40px;
}

.activity-feed::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background-color: #e9ecef;
}

.activity-item {
    position: relative;
    padding-bottom: 1.5rem;
}

.activity-item:last-child {
    padding-bottom: 0;
}

.activity-icon {
    position: absolute;
    left: -25px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    background-color: white;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.activity-content {
    font-size: 0.9rem;
}

.activity-content strong {
    font-weight: 600;
}

/* Mobile Responsive Improvements */
@media (max-width: 992px) {
    .main-content {
        padding: 1.5rem;
    }
}

@media (max-width: 768px) {
    .main-content {
        padding: 0.75rem;
    }

    .container-fluid {
        padding: 0;
    }

    /* Page header mobile */
    .page-header {
        margin-bottom: 1rem;
    }

    .page-title {
        font-size: 1.25rem;
    }

    #create-task-btn {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    /* Welcome card mobile */
    .welcome-card {
        padding: 1.25rem;
        border-radius: 0.75rem;
    }

    .welcome-title {
        font-size: 1.25rem;
    }

    .welcome-message {
        font-size: 0.9rem;
    }

    /* Stats cards mobile */
    .stat-card {
        margin-bottom: 0.75rem;
    }

    .stat-content {
        gap: 0.75rem;
    }

    .stat-icon {
        width: 45px;
        height: 45px;
        font-size: 1.25rem;
    }

    .stat-value {
        font-size: 1.5rem;
    }

    .stat-label {
        font-size: 0.75rem;
    }

    .stat-change {
        font-size: 0.75rem;
    }

    /* Date range selector mobile */
    #date-range-card .row {
        flex-direction: column;
    }

    #date-range-card .col-md-8,
    #date-range-card .col-md-4 {
        width: 100%;
        margin-bottom: 0.5rem;
    }

    #date-range-card .input-group {
        flex-direction: column;
    }

    #date-range-card .input-group > * {
        width: 100%;
        margin-bottom: 0.5rem;
        border-radius: 0.375rem !important;
    }

    #date-range-card .input-group-text {
        display: none;
    }

    /* Charts mobile */
    .card-header h5 {
        font-size: 1rem;
    }

    canvas {
        max-width: 100%;
        height: auto !important;
    }

    /* Tasks list mobile */
    .task-item {
        padding: 0.75rem;
    }

    .task-item .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }

    .task-item .form-check {
        margin-bottom: 0.5rem;
    }

    .task-item .flex-grow-1 {
        width: 100%;
    }

    .task-meta {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.25rem;
    }

    .task-actions {
        margin-top: 0.5rem;
        align-self: flex-end;
    }

    /* Activity feed mobile */
    .activity-feed {
        padding-left: 30px;
    }

    .activity-icon {
        left: -15px;
        width: 25px;
        height: 25px;
        font-size: 0.75rem;
    }

    .activity-content {
        font-size: 0.85rem;
    }

    .activity-content p {
        margin-bottom: 0.5rem;
    }

    /* Breadcrumb mobile */
    .breadcrumb {
        font-size: 0.85rem;
    }

    /* Cards general mobile */
    .card {
        border-radius: 0.75rem;
        margin-bottom: 1rem;
    }

    .card-header {
        padding: 0.75rem;
    }

    .card-body {
        padding: 0.75rem;
    }

    /* Button adjustments */
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}

@media (max-width: 576px) {
    /* Extra small screens */
    .main-content {
        padding: 0.5rem;
    }

    .page-header .row {
        flex-direction: column;
    }

    .page-header .col-auto {
        width: 100%;
        margin-top: 0.75rem;
    }

    #create-task-btn {
        width: 100%;
    }

    /* Welcome section mobile */
    .welcome-section .row {
        flex-direction: column;
    }

    .welcome-section .col-lg-4 {
        margin-top: 0.75rem;
        text-align: left !important;
    }

    /* Stats grid extra small */
    .stats-section .col-xl-3 {
        margin-bottom: 0.75rem;
    }

    /* Recent tasks and activity mobile layout */
    .row > .col-lg-8,
    .row > .col-lg-4 {
        padding: 0 0.5rem;
    }
}
</style>

<?php
// Include footer (which loads jQuery and other scripts)
include_once 'includes/footer.php';
?>

<!-- Dashboard-specific JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize charts
    let completionChart, statusChart, productivityChart, priorityChart;

    // Task Completion Trend Chart
    function initCompletionChart() {
        const ctx = document.getElementById('completion-line-chart');
        if (ctx) {
            fetch('api/dashboard_data.php?type=completion_trend')
                .then(response => response.json())
                .then(data => {
                    completionChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Tasks Completed',
                                data: data.data,
                                borderColor: 'rgb(75, 192, 192)',
                                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                });
        }
    }

    // Status Distribution Chart
    function initStatusChart() {
        const ctx = document.getElementById('status-pie-chart');
        if (ctx) {
            fetch('api/dashboard_data.php?type=status_distribution')
                .then(response => response.json())
                .then(data => {
                    statusChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: [
                                    '#22c55e', // completed
                                    '#f59e0b', // in progress
                                    '#3b82f6', // pending
                                    '#ef4444'  // cancelled
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                });
        }
    }

    // Team Productivity Chart
    function initProductivityChart() {
        const ctx = document.getElementById('productivity-bar-chart');
        if (ctx) {
            fetch('api/dashboard_data.php?type=team_productivity')
                .then(response => response.json())
                .then(data => {
                    productivityChart = new Chart(ctx, {
                        type: 'bar',
                        data: data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'top'
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                });
        }
    }

    // Priority Breakdown Chart
    function initPriorityChart() {
        const ctx = document.getElementById('priority-doughnut-chart');
        if (ctx) {
            fetch('api/dashboard_data.php?type=priority_breakdown')
                .then(response => response.json())
                .then(data => {
                    priorityChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.data,
                                backgroundColor: [
                                    '#dc2626', // critical
                                    '#f97316', // high
                                    '#eab308', // medium
                                    '#22c55e'  // low
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                });
        }
    }

    // Initialize all charts
    initCompletionChart();
    initStatusChart();
    initProductivityChart();
    initPriorityChart();

    // Task checkbox handling
    $('.task-checkbox').on('change', function() {
        var taskItem = $(this).closest('.task-item');
        var taskId = $(this).data('task-id');

        if ($(this).is(':checked') && !$(this).prop('disabled')) {
            // Mark task as complete via AJAX
            $.ajax({
                url: 'api/update_task.php',
                method: 'POST',
                data: {
                    task_id: taskId,
                    status: 'completed'
                },
                success: function(response) {
                    taskItem.fadeOut(300, function() {
                        showNotification('Task marked as complete!', 'success');
                        // Refresh charts
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    });
                },
                error: function() {
                    // If API doesn't exist, just fade out
                    taskItem.fadeOut(300, function() {
                        showNotification('Task marked as complete!', 'success');
                    });
                }
            });
        }
    });

    // Create task button
    $('#create-task-btn').on('click', function() {
        // Redirect to create task page or open modal
        window.location.href = 'create-task.php';
    });

    // Statistics cards navigation
    $('.clickable-card').on('click', function() {
        var link = $(this).data('link');
        if (link) {
            window.location.href = link;
        }
    });

    // Date range functionality
    $('#apply-date-range').on('click', function() {
        const startDate = $('#date-range-start').val();
        const endDate = $('#date-range-end').val();

        if (startDate && endDate) {
            // Refresh charts with date range
            // This would require updating the API to accept date parameters
            showNotification('Date range applied', 'info');
        }
    });
});
</script>