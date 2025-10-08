<?php
/**
 * Tasks API Endpoint for HTMX (Returns HTML)
 * Handles requests that need HTML partial responses
 */

session_start();

// Include required files
require_once '../includes/auth.php';
require_once '../classes/Task.php';
require_once '../includes/db.php';

// Check if user is logged in
if (!is_logged_in()) {
    http_response_code(401);
    echo '<tr><td colspan="8" class="text-center text-danger">Unauthorized. Please login.</td></tr>';
    exit;
}

$userId = $_SESSION['user_id'];
$teamId = $_SESSION['current_team_id'] ?? null;
$task = new Task();

// Get request parameters
$action = $_REQUEST['action'] ?? '';
$page = intval($_REQUEST['page'] ?? 1);
$limit = intval($_REQUEST['limit'] ?? 25);
$filter_status = $_REQUEST['filter_status'] ?? '';
$filter_priority = $_REQUEST['filter_priority'] ?? '';
$filter_assignee = $_REQUEST['filter_assignee'] ?? '';

try {
    switch ($action) {
        case 'list_html':
            // Get tasks with filters
            $offset = ($page - 1) * $limit;

            $where_conditions = ["(t.user_id = :user_id OR t.assigned_to = :user_id2)"];
            $params = [
                ':user_id' => $userId,
                ':user_id2' => $userId
            ];

            if ($filter_status) {
                $where_conditions[] = "t.status = :status";
                $params[':status'] = $filter_status;
            }

            if ($filter_priority) {
                $where_conditions[] = "t.priority = :priority";
                $params[':priority'] = $filter_priority;
            }

            if ($filter_assignee) {
                $where_conditions[] = "t.assigned_to = :assigned_to";
                $params[':assigned_to'] = $filter_assignee;
            }

            $where_sql = implode(' AND ', $where_conditions);

            // Get total count
            $count_sql = "SELECT COUNT(*) as total
                         FROM tasks t
                         WHERE $where_sql";
            $count_stmt = $pdo->prepare($count_sql);
            $count_stmt->execute($params);
            $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Get tasks
            $sql = "SELECT t.*,
                           u.first_name as assigned_first_name,
                           u.last_name as assigned_last_name,
                           creator.first_name as creator_first_name,
                           creator.last_name as creator_last_name
                    FROM tasks t
                    LEFT JOIN users u ON t.assigned_to = u.id
                    LEFT JOIN users creator ON t.created_by = creator.id
                    WHERE $where_sql
                    ORDER BY t.created_at DESC
                    LIMIT :limit OFFSET :offset";

            $stmt = $pdo->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Render partial
            include '../partials/tasks/task-list.php';
            break;

        case 'stats':
            // Get task statistics
            $stats_sql = "SELECT
                            COUNT(*) as total,
                            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                            SUM(CASE WHEN status = 'pending' AND due_date < NOW() THEN 1 ELSE 0 END) as overdue
                         FROM tasks
                         WHERE (user_id = :user_id OR assigned_to = :user_id2)";

            $stats_stmt = $pdo->prepare($stats_sql);
            $stats_stmt->execute([
                ':user_id' => $userId,
                ':user_id2' => $userId
            ]);
            $stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

            // Return JSON for stats
            header('Content-Type: application/json');
            echo json_encode($stats);
            break;

        default:
            echo '<tr><td colspan="8" class="text-center text-danger">Invalid action</td></tr>';
            break;
    }
} catch (Exception $e) {
    error_log('Tasks HTML API Error: ' . $e->getMessage());
    echo '<tr><td colspan="8" class="text-center text-danger">Error loading tasks: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
}
