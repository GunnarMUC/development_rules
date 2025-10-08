<?php
// Reports page with modular components
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Require login
require_login();

// Get current user
$user = get_current_user_info();
$user_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? 0;
$username = $_SESSION['user']['first_name'] ?? 'User';

// Page-specific settings
$page_title = 'Reports';

// Initialize default values
$task_stats = [
    'total_tasks' => 0,
    'completed_tasks' => 0,
    'in_progress_tasks' => 0,
    'pending_tasks' => 0,
    'high_priority_tasks' => 0,
    'medium_priority_tasks' => 0,
    'low_priority_tasks' => 0
];

$team_stats = ['total_teams' => 0];
$activity_data = [];

// Fetch statistics for reports if database is connected
if (isDatabaseConnected() && $pdo) {
    try {
        // Task statistics
        $task_stats_query = "SELECT
            COUNT(*) as total_tasks,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_tasks,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_tasks,
            SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority_tasks,
            SUM(CASE WHEN priority = 'medium' THEN 1 ELSE 0 END) as medium_priority_tasks,
            SUM(CASE WHEN priority = 'low' THEN 1 ELSE 0 END) as low_priority_tasks
        FROM tasks WHERE assigned_to = :user_id";

        $stmt = $pdo->prepare($task_stats_query);
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $task_stats = $result;
        }

        // Team statistics
        $team_stats_query = "SELECT COUNT(DISTINCT tm.team_id) as total_teams
        FROM team_members tm WHERE tm.user_id = :user_id";

        $stmt = $pdo->prepare($team_stats_query);
        $stmt->execute(['user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $team_stats = $result;
        }

        // Recent activity - last 30 days
        $activity_query = "SELECT
            DATE(created_at) as activity_date,
            COUNT(*) as tasks_created
        FROM tasks
        WHERE assigned_to = :user_id AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(created_at)
        ORDER BY activity_date ASC";

        $stmt = $pdo->prepare($activity_query);
        $stmt->execute(['user_id' => $user_id]);
        $activity_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        // Log error but continue with default values
        error_log("Error fetching report data: " . $e->getMessage());
    }
}

// Additional CSS for this page
$additional_css = '
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
        .main-content {
            min-height: 100vh;
            background: #f8f9fa;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #495057;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }

        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .report-header h2,
        .report-header p {
            color: white !important;
        }
    </style>
';

// Include header
include_once 'includes/header.php';
?>

<!-- Main Container -->
<div class="container-fluid">
    <div class="row">
        <!-- Main Content Column - Full Width -->
        <div class="col-lg-12">
            <!-- Main Content Area -->
            <main class="main-content" id="main-content">
                <div class="container-fluid p-4">
                    <!-- Report Header -->
                    <div class="report-header" id="report-header">
                        <h2><i class="bi bi-graph-up me-2"></i>Reports Dashboard</h2>
                        <p class="mb-0">View your task statistics and performance metrics</p>
                    </div>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Statistics Cards -->
                    <div class="row g-4 mb-4" id="statistics-row">
                        <div class="col-md-3" id="total-tasks-col">
                            <div class="stat-card">
                                <div class="stat-number"><?php echo $task_stats['total_tasks'] ?? 0; ?></div>
                                <div class="stat-label">Total Tasks</div>
                            </div>
                        </div>
                        <div class="col-md-3" id="completed-tasks-col">
                            <div class="stat-card">
                                <div class="stat-number text-success"><?php echo $task_stats['completed_tasks'] ?? 0; ?></div>
                                <div class="stat-label">Completed</div>
                            </div>
                        </div>
                        <div class="col-md-3" id="in-progress-tasks-col">
                            <div class="stat-card">
                                <div class="stat-number text-warning"><?php echo $task_stats['in_progress_tasks'] ?? 0; ?></div>
                                <div class="stat-label">In Progress</div>
                            </div>
                        </div>
                        <div class="col-md-3" id="pending-tasks-col">
                            <div class="stat-card">
                                <div class="stat-number text-info"><?php echo $task_stats['pending_tasks'] ?? 0; ?></div>
                                <div class="stat-label">Pending</div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row g-4" id="charts-row">
                        <!-- Task Status Chart -->
                        <div class="col-md-6" id="status-chart-col">
                            <div class="chart-container">
                                <h5 class="mb-4">Task Status Distribution</h5>
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>

                        <!-- Priority Distribution Chart -->
                        <div class="col-md-6" id="priority-chart-col">
                            <div class="chart-container">
                                <h5 class="mb-4">Priority Distribution</h5>
                                <canvas id="priorityChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Chart -->
                    <div class="row mt-4" id="activity-chart-row">
                        <div class="col-12" id="activity-chart-col">
                            <div class="chart-container">
                                <h5 class="mb-4">Task Activity (Last 30 Days)</h5>
                                <canvas id="activityChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Section -->
                    <div class="row mt-4" id="summary-row">
                        <div class="col-12" id="summary-col">
                            <div class="stat-card">
                                <h5 class="mb-3">Summary</h5>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p><strong>Completion Rate:</strong>
                                            <?php
                                                $total = $task_stats['total_tasks'] ?? 0;
                                                $completed = $task_stats['completed_tasks'] ?? 0;
                                                $rate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
                                                echo $rate . '%';
                                            ?>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>High Priority Tasks:</strong> <?php echo $task_stats['high_priority_tasks'] ?? 0; ?></p>
                                    </div>
                                    <div class="col-md-4">
                                        <p><strong>Teams:</strong> <?php echo $team_stats['total_teams'] ?? 0; ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- jQuery (required for many components) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- jQuery UI for autocomplete -->
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Toastr for notifications -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Custom JavaScript -->
<script src="/assets/js/global-search.js"></script>
<script src="/assets/js/notifications.js"></script>
<script src="/assets/js/team-switcher.js"></script>

<script>
        // Task Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress', 'Pending'],
                datasets: [{
                    data: [
                        <?php echo $task_stats['completed_tasks'] ?? 0; ?>,
                        <?php echo $task_stats['in_progress_tasks'] ?? 0; ?>,
                        <?php echo $task_stats['pending_tasks'] ?? 0; ?>
                    ],
                    backgroundColor: ['#28a745', '#ffc107', '#17a2b8'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Priority Chart
        const priorityCtx = document.getElementById('priorityChart').getContext('2d');
        new Chart(priorityCtx, {
            type: 'bar',
            data: {
                labels: ['High', 'Medium', 'Low'],
                datasets: [{
                    label: 'Tasks by Priority',
                    data: [
                        <?php echo $task_stats['high_priority_tasks'] ?? 0; ?>,
                        <?php echo $task_stats['medium_priority_tasks'] ?? 0; ?>,
                        <?php echo $task_stats['low_priority_tasks'] ?? 0; ?>
                    ],
                    backgroundColor: ['#dc3545', '#ffc107', '#28a745'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        const activityData = <?php echo json_encode($activity_data); ?>;

        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: activityData.map(item => item.activity_date),
                datasets: [{
                    label: 'Tasks Created',
                    data: activityData.map(item => item.tasks_created),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

    </script>
</body>
</html>