<?php
// Dashboard data API endpoint
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';

// Require login
require_login();

// Get current user
$user = get_current_user_info();
$userId = $user['id'];

// Set JSON header
header('Content-Type: application/json');

// Get the data type requested
$type = $_GET['type'] ?? 'all';

$response = [];

try {
    switch($type) {
        case 'completion_trend':
            // Get task completion data for the last 30 days
            $stmt = $pdo->prepare("
                SELECT
                    DATE(completed_at) as date,
                    COUNT(*) as completed_count
                FROM tasks
                WHERE status = 'completed'
                    AND completed_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    AND (user_id = ? OR assigned_to = ?)
                GROUP BY DATE(completed_at)
                ORDER BY date
            ");
            $stmt->execute([$userId, $userId]);
            $completionData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Fill in missing dates with 0
            $endDate = new DateTime();
            $startDate = (new DateTime())->sub(new DateInterval('P30D'));
            $interval = new DateInterval('P1D');
            $period = new DatePeriod($startDate, $interval, $endDate);

            $dataByDate = [];
            foreach ($completionData as $row) {
                $dataByDate[$row['date']] = $row['completed_count'];
            }

            $labels = [];
            $data = [];
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $labels[] = $date->format('M d');
                $data[] = $dataByDate[$dateStr] ?? 0;
            }

            $response = [
                'labels' => $labels,
                'data' => $data
            ];
            break;

        case 'status_distribution':
            // Get current task status distribution
            $stmt = $pdo->prepare("
                SELECT
                    status,
                    COUNT(*) as count
                FROM tasks
                WHERE user_id = ? OR assigned_to = ?
                GROUP BY status
            ");
            $stmt->execute([$userId, $userId]);
            $statusData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $data = [];
            $colors = [
                'completed' => '#22c55e',
                'in_progress' => '#f59e0b',
                'pending' => '#3b82f6',
                'cancelled' => '#ef4444'
            ];

            foreach ($statusData as $row) {
                $labels[] = ucfirst(str_replace('_', ' ', $row['status']));
                $data[] = $row['count'];
            }

            $response = [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => array_values($colors)
            ];
            break;

        case 'team_productivity':
            // Get team members' task completion this week
            $stmt = $pdo->prepare("
                SELECT
                    u.first_name,
                    u.last_name,
                    COUNT(CASE WHEN t.status = 'completed' AND t.completed_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as completed_this_week,
                    COUNT(CASE WHEN t.status = 'in_progress' THEN 1 END) as in_progress,
                    COUNT(CASE WHEN t.status = 'pending' THEN 1 END) as pending
                FROM users u
                LEFT JOIN tasks t ON (t.user_id = u.id OR t.assigned_to = u.id)
                WHERE u.id IN (
                    SELECT DISTINCT user_id
                    FROM team_members
                    WHERE team_id IN (
                        SELECT team_id FROM team_members WHERE user_id = ?
                    )
                )
                GROUP BY u.id, u.first_name, u.last_name
                ORDER BY completed_this_week DESC
                LIMIT 5
            ");
            $stmt->execute([$userId]);
            $teamData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($teamData)) {
                // Fallback: show top users by task count
                $stmt = $pdo->prepare("
                    SELECT
                        u.first_name,
                        u.last_name,
                        COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_this_week,
                        COUNT(CASE WHEN t.status = 'in_progress' THEN 1 END) as in_progress,
                        COUNT(CASE WHEN t.status = 'pending' THEN 1 END) as pending
                    FROM users u
                    LEFT JOIN tasks t ON (t.user_id = u.id OR t.assigned_to = u.id)
                    GROUP BY u.id, u.first_name, u.last_name
                    ORDER BY completed_this_week DESC
                    LIMIT 5
                ");
                $stmt->execute();
                $teamData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $labels = [];
            $completed = [];
            $inProgress = [];
            $pending = [];

            foreach ($teamData as $row) {
                $labels[] = $row['first_name'] . ' ' . substr($row['last_name'], 0, 1) . '.';
                $completed[] = $row['completed_this_week'];
                $inProgress[] = $row['in_progress'];
                $pending[] = $row['pending'];
            }

            $response = [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Completed',
                        'data' => $completed,
                        'backgroundColor' => '#22c55e'
                    ],
                    [
                        'label' => 'In Progress',
                        'data' => $inProgress,
                        'backgroundColor' => '#f59e0b'
                    ],
                    [
                        'label' => 'Pending',
                        'data' => $pending,
                        'backgroundColor' => '#3b82f6'
                    ]
                ]
            ];
            break;

        case 'priority_breakdown':
            // Get priority distribution
            $stmt = $pdo->prepare("
                SELECT
                    priority,
                    COUNT(*) as count
                FROM tasks
                WHERE (user_id = ? OR assigned_to = ?)
                    AND status != 'completed'
                GROUP BY priority
                ORDER BY
                    CASE priority
                        WHEN 'critical' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                    END
            ");
            $stmt->execute([$userId, $userId]);
            $priorityData = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $data = [];
            $colors = [
                'critical' => '#dc2626',
                'high' => '#f97316',
                'medium' => '#eab308',
                'low' => '#22c55e'
            ];

            foreach ($priorityData as $row) {
                $labels[] = ucfirst($row['priority']);
                $data[] = $row['count'];
            }

            $response = [
                'labels' => $labels,
                'data' => $data,
                'backgroundColor' => array_values($colors)
            ];
            break;

        case 'recent_tasks':
            // Get recent tasks
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
            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        case 'recent_activities':
            // Get recent activities
            // First check if activities table exists
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'activities'");
            if ($stmt->fetch()['count'] > 0) {
                $stmt = $pdo->prepare("
                    SELECT
                        a.*,
                        u.first_name,
                        u.last_name
                    FROM activities a
                    LEFT JOIN users u ON a.user_id = u.id
                    ORDER BY a.created_at DESC
                    LIMIT 10
                ");
                $stmt->execute();
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                // Fallback: create activities from tasks
                $stmt = $pdo->prepare("
                    SELECT
                        'task_update' as action,
                        t.title as description,
                        t.created_at,
                        u.first_name,
                        u.last_name
                    FROM tasks t
                    LEFT JOIN users u ON t.user_id = u.id
                    WHERE t.user_id = ? OR t.assigned_to = ?
                    ORDER BY t.created_at DESC
                    LIMIT 10
                ");
                $stmt->execute([$userId, $userId]);
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            break;

        default:
            $response = ['error' => 'Invalid type specified'];
    }

} catch (PDOException $e) {
    $response = ['error' => 'Database error occurred'];
    error_log('Dashboard data error: ' . $e->getMessage());
}

echo json_encode($response);