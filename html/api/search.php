<?php
/**
 * Global Search API Endpoint
 * Searches across tasks, teams, and users
 */

session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';
require_once '../classes/Database.php';

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

if (strlen($query) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();
    $userId = $_SESSION['user_id'];
    $currentTeamId = $_SESSION['current_team_id'] ?? null;
    $searchTerm = '%' . $query . '%';
    $results = [];

    // Search tasks (within current team if set)
    $taskSql = "SELECT
                    'task' as type,
                    t.id,
                    t.title as label,
                    t.description,
                    t.status,
                    t.priority,
                    CONCAT('/tasks.php?task=', t.id) as url
                FROM tasks t
                WHERE (t.title LIKE ? OR t.description LIKE ?)";

    $params = [$searchTerm, $searchTerm];

    if ($currentTeamId) {
        $taskSql .= " AND t.team_id = ?";
        $params[] = $currentTeamId;
    } else {
        $taskSql .= " AND t.user_id = ?";
        $params[] = $userId;
    }

    $taskSql .= " ORDER BY t.created_at DESC LIMIT ?";
    $params[] = $limit;

    $stmt = $db->prepare($taskSql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tasks as $task) {
        $task['icon'] = 'bi-check2-square';
        $task['category'] = 'Tasks';
        $task['badge'] = ucfirst($task['priority']);
        $task['badge_class'] = $task['priority'] === 'critical' ? 'danger' :
                               ($task['priority'] === 'high' ? 'warning' :
                               ($task['priority'] === 'medium' ? 'info' : 'secondary'));
        $results[] = $task;
    }

    // Search teams (user is member of)
    $teamSql = "SELECT
                    'team' as type,
                    t.id,
                    t.name as label,
                    t.description,
                    COUNT(tm.user_id) as member_count,
                    CONCAT('/teams.php?team=', t.id) as url
                FROM teams t
                JOIN team_members tm ON t.id = tm.team_id
                WHERE t.name LIKE ?
                    AND t.id IN (
                        SELECT team_id FROM team_members WHERE user_id = ?
                    )
                GROUP BY t.id
                ORDER BY t.name
                LIMIT ?";

    $stmt = $db->prepare($teamSql);
    $stmt->execute([$searchTerm, $userId, $limit]);
    $teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($teams as $team) {
        $team['icon'] = 'bi-people-fill';
        $team['category'] = 'Teams';
        $team['badge'] = $team['member_count'] . ' members';
        $team['badge_class'] = 'primary';
        $results[] = $team;
    }

    // Search users (in same teams)
    if ($currentTeamId) {
        $userSql = "SELECT DISTINCT
                        'user' as type,
                        u.id,
                        CONCAT(u.first_name, ' ', u.last_name) as label,
                        u.email as description,
                        tm.role,
                        CONCAT('/profile.php?user=', u.id) as url
                    FROM users u
                    JOIN team_members tm ON u.id = tm.user_id
                    WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)
                        AND tm.team_id = ?
                        AND u.id != ?
                    ORDER BY u.first_name, u.last_name
                    LIMIT ?";

        $stmt = $db->prepare($userSql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $currentTeamId, $userId, $limit]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($users as $user) {
            $user['icon'] = 'bi-person-circle';
            $user['category'] = 'Team Members';
            $user['badge'] = ucfirst($user['role']);
            $user['badge_class'] = $user['role'] === 'admin' ? 'success' : 'info';
            $results[] = $user;
        }
    }

    // Sort results by relevance (exact matches first)
    usort($results, function($a, $b) use ($query) {
        $aScore = stripos($a['label'], $query) === 0 ? 1 : 0;
        $bScore = stripos($b['label'], $query) === 0 ? 1 : 0;
        return $bScore - $aScore;
    });

    echo json_encode([
        'results' => array_slice($results, 0, $limit),
        'query' => $query,
        'total' => count($results)
    ]);

} catch (Exception $e) {
    error_log("Search error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Search failed']);
}
?>