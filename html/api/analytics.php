<?php
/**
 * Analytics API Endpoint
 * Provides data for dashboard charts and analytics
 */

require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../classes/Database.php';

// Require login
require_login_ajax();

// Get current user and team
$user = get_current_user_info();
$team_id = $_SESSION['current_team_id'] ?? null;

// Get request parameters
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Initialize database connection
$db = Database::getInstance();

// Set JSON header
header('Content-Type: application/json');

try {
    switch ($action) {
        case 'task_completion':
            // Get daily task completion data for line chart
            $sql = "SELECT
                        DATE(completed_at) as date,
                        COUNT(*) as completed_count
                    FROM tasks
                    WHERE status = 'completed'
                    AND completed_at BETWEEN :start_date AND :end_date";

            if ($team_id) {
                $sql .= " AND team_id = :team_id";
            }

            $sql .= " GROUP BY DATE(completed_at)
                     ORDER BY date ASC";

            $params = [
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ];

            if ($team_id) {
                $params[':team_id'] = $team_id;
            }

            $stmt = $db->query($sql, $params);
            $data = $stmt->fetchAll();

            // Fill in missing dates with zero completions
            $date_range = [];
            $current = strtotime($start_date);
            $end = strtotime($end_date);

            while ($current <= $end) {
                $date_range[date('Y-m-d', $current)] = 0;
                $current = strtotime('+1 day', $current);
            }

            foreach ($data as $row) {
                $date_range[$row['date']] = intval($row['completed_count']);
            }

            // Format for Chart.js
            $labels = array_keys($date_range);
            $values = array_values($date_range);

            echo json_encode([
                'success' => true,
                'labels' => array_map(function($date) {
                    return date('M j', strtotime($date));
                }, $labels),
                'datasets' => [
                    [
                        'label' => 'Tasks Completed',
                        'data' => $values,
                        'borderColor' => 'rgb(75, 192, 192)',
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                        'tension' => 0.4
                    ]
                ]
            ]);
            break;

        case 'status_distribution':
            // Get task status distribution for pie chart
            $sql = "SELECT
                        status,
                        COUNT(*) as count
                    FROM tasks
                    WHERE created_at BETWEEN :start_date AND :end_date";

            if ($team_id) {
                $sql .= " AND team_id = :team_id";
            }

            $sql .= " GROUP BY status";

            $params = [
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ];

            if ($team_id) {
                $params[':team_id'] = $team_id;
            }

            $stmt = $db->query($sql, $params);
            $data = $stmt->fetchAll();

            $labels = [];
            $values = [];
            $colors = [
                'pending' => 'rgb(255, 205, 86)',
                'in_progress' => 'rgb(54, 162, 235)',
                'completed' => 'rgb(75, 192, 192)',
                'cancelled' => 'rgb(255, 99, 132)',
                'on_hold' => 'rgb(201, 203, 207)'
            ];

            $backgroundColors = [];
            foreach ($data as $row) {
                $status = $row['status'];
                $labels[] = ucfirst(str_replace('_', ' ', $status));
                $values[] = intval($row['count']);
                $backgroundColors[] = $colors[$status] ?? 'rgb(201, 203, 207)';
            }

            echo json_encode([
                'success' => true,
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $values,
                        'backgroundColor' => $backgroundColors,
                        'borderWidth' => 2,
                        'borderColor' => '#fff'
                    ]
                ]
            ]);
            break;

        case 'team_productivity':
            // Get task count by team member for bar chart
            $sql = "SELECT
                        u.id,
                        CONCAT(u.first_name, ' ', u.last_name) as name,
                        COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed,
                        COUNT(CASE WHEN t.status = 'in_progress' THEN 1 END) as in_progress,
                        COUNT(CASE WHEN t.status = 'pending' THEN 1 END) as pending
                    FROM users u
                    LEFT JOIN task_assignees ta ON u.id = ta.user_id
                    LEFT JOIN tasks t ON ta.task_id = t.id
                        AND t.created_at BETWEEN :start_date AND :end_date";

            if ($team_id) {
                $sql .= " LEFT JOIN team_members tm ON u.id = tm.user_id
                         WHERE tm.team_id = :team_id";
            } else {
                $sql .= " WHERE 1=1";
            }

            $sql .= " GROUP BY u.id, u.first_name, u.last_name
                     ORDER BY (COUNT(CASE WHEN t.status = 'completed' THEN 1 END)) DESC
                     LIMIT 10";

            $params = [
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ];

            if ($team_id) {
                $params[':team_id'] = $team_id;
            }

            $stmt = $db->query($sql, $params);
            $data = $stmt->fetchAll();

            $labels = [];
            $completed = [];
            $in_progress = [];
            $pending = [];

            foreach ($data as $row) {
                $labels[] = $row['name'];
                $completed[] = intval($row['completed']);
                $in_progress[] = intval($row['in_progress']);
                $pending[] = intval($row['pending']);
            }

            echo json_encode([
                'success' => true,
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Completed',
                        'data' => $completed,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
                        'borderColor' => 'rgb(75, 192, 192)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'In Progress',
                        'data' => $in_progress,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                        'borderColor' => 'rgb(54, 162, 235)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Pending',
                        'data' => $pending,
                        'backgroundColor' => 'rgba(255, 205, 86, 0.8)',
                        'borderColor' => 'rgb(255, 205, 86)',
                        'borderWidth' => 1
                    ]
                ]
            ]);
            break;

        case 'priority_breakdown':
            // Get task priority distribution for doughnut chart
            $sql = "SELECT
                        priority,
                        COUNT(*) as count
                    FROM tasks
                    WHERE created_at BETWEEN :start_date AND :end_date";

            if ($team_id) {
                $sql .= " AND team_id = :team_id";
            }

            $sql .= " GROUP BY priority
                     ORDER BY FIELD(priority, 'critical', 'high', 'medium', 'low')";

            $params = [
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ];

            if ($team_id) {
                $params[':team_id'] = $team_id;
            }

            $stmt = $db->query($sql, $params);
            $data = $stmt->fetchAll();

            $labels = [];
            $values = [];
            $colors = [
                'low' => 'rgb(75, 192, 192)',
                'medium' => 'rgb(255, 205, 86)',
                'high' => 'rgb(255, 159, 64)',
                'critical' => 'rgb(255, 99, 132)'
            ];

            $backgroundColors = [];
            foreach ($data as $row) {
                $priority = $row['priority'];
                $labels[] = ucfirst($priority);
                $values[] = intval($row['count']);
                $backgroundColors[] = $colors[$priority] ?? 'rgb(201, 203, 207)';
            }

            echo json_encode([
                'success' => true,
                'labels' => $labels,
                'datasets' => [
                    [
                        'data' => $values,
                        'backgroundColor' => $backgroundColors,
                        'borderWidth' => 2,
                        'borderColor' => '#fff'
                    ]
                ]
            ]);
            break;

        case 'completion_rate':
            // Calculate overall completion rate and trends
            $sql = "SELECT
                        COUNT(*) as total,
                        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed,
                        COUNT(CASE WHEN status = 'in_progress' THEN 1 END) as in_progress,
                        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending
                    FROM tasks
                    WHERE created_at BETWEEN :start_date AND :end_date";

            if ($team_id) {
                $sql .= " AND team_id = :team_id";
            }

            $params = [
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ];

            if ($team_id) {
                $params[':team_id'] = $team_id;
            }

            $stmt = $db->query($sql, $params);
            $stats = $stmt->fetch();

            $completion_rate = $stats['total'] > 0
                ? round(($stats['completed'] / $stats['total']) * 100, 1)
                : 0;

            // Get previous period stats for comparison
            $prev_start = date('Y-m-d', strtotime($start_date . ' -30 days'));
            $prev_end = date('Y-m-d', strtotime($end_date . ' -30 days'));

            $sql = "SELECT
                        COUNT(*) as total,
                        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed
                    FROM tasks
                    WHERE created_at BETWEEN :start_date AND :end_date";

            if ($team_id) {
                $sql .= " AND team_id = :team_id";
            }

            $params = [
                ':start_date' => $prev_start,
                ':end_date' => $prev_end
            ];

            if ($team_id) {
                $params[':team_id'] = $team_id;
            }

            $stmt = $db->query($sql, $params);
            $prev_stats = $stmt->fetch();

            $prev_completion_rate = $prev_stats['total'] > 0
                ? round(($prev_stats['completed'] / $prev_stats['total']) * 100, 1)
                : 0;

            $trend = $completion_rate - $prev_completion_rate;

            echo json_encode([
                'success' => true,
                'current' => [
                    'total' => intval($stats['total']),
                    'completed' => intval($stats['completed']),
                    'in_progress' => intval($stats['in_progress']),
                    'pending' => intval($stats['pending']),
                    'completion_rate' => $completion_rate
                ],
                'previous' => [
                    'total' => intval($prev_stats['total']),
                    'completed' => intval($prev_stats['completed']),
                    'completion_rate' => $prev_completion_rate
                ],
                'trend' => [
                    'value' => $trend,
                    'direction' => $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'neutral'),
                    'percentage' => abs($trend)
                ]
            ]);
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
    }

} catch (Exception $e) {
    error_log("Analytics API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching analytics data'
    ]);
}