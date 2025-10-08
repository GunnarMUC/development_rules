<?php
/**
 * Activity Logger
 * Handles logging of user activities to the database
 */

require_once __DIR__ . '/db.php';

/**
 * Log an activity to the database
 *
 * @param int $userId User ID who performed the action
 * @param string $action Action type (e.g., 'created', 'updated', 'completed', 'deleted')
 * @param string $targetType Type of target (e.g., 'task', 'team', 'user')
 * @param int|null $targetId ID of the target entity
 * @param string $description Human-readable description of the activity
 * @return bool Success status
 */
function logActivity($userId, $action, $targetType, $targetId, $description) {
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO activities (user_id, action, target_type, target_id, description, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");

        return $stmt->execute([$userId, $action, $targetType, $targetId, $description]);
    } catch (Exception $e) {
        error_log("Failed to log activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Log task-related activity
 *
 * @param int $userId User ID
 * @param string $action Action performed on task
 * @param array $taskData Task data array
 * @return bool Success status
 */
function logTaskActivity($userId, $action, $taskData) {
    $descriptions = [
        'created' => "created task \"{$taskData['title']}\"",
        'updated' => "updated task \"{$taskData['title']}\"",
        'completed' => "completed task \"{$taskData['title']}\"",
        'started' => "started working on task \"{$taskData['title']}\"",
        'assigned' => "assigned task \"{$taskData['title']}\" to {$taskData['assigned_name']}",
        'deleted' => "deleted task \"{$taskData['title']}\"",
        'commented' => "commented on task \"{$taskData['title']}\""
    ];

    $description = $descriptions[$action] ?? "performed action on task \"{$taskData['title']}\"";

    return logActivity($userId, $action, 'task', $taskData['id'] ?? null, $description);
}

/**
 * Log team-related activity
 *
 * @param int $userId User ID
 * @param string $action Action performed
 * @param array $teamData Team data array
 * @return bool Success status
 */
function logTeamActivity($userId, $action, $teamData) {
    $descriptions = [
        'created' => "created team \"{$teamData['name']}\"",
        'joined' => "joined team \"{$teamData['name']}\"",
        'left' => "left team \"{$teamData['name']}\"",
        'updated' => "updated team \"{$teamData['name']}\"",
        'deleted' => "deleted team \"{$teamData['name']}\""
    ];

    $description = $descriptions[$action] ?? "performed action on team \"{$teamData['name']}\"";

    return logActivity($userId, $action, 'team', $teamData['id'] ?? null, $description);
}

/**
 * Log user-related activity
 *
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $description Custom description
 * @return bool Success status
 */
function logUserActivity($userId, $action, $description) {
    return logActivity($userId, $action, 'user', $userId, $description);
}

/**
 * Get recent activities
 *
 * @param int $limit Number of activities to retrieve
 * @param int|null $userId Filter by user ID (optional)
 * @param int|null $teamId Filter by team ID (optional)
 * @return array Array of activity records
 */
function getRecentActivities($limit = 10, $userId = null, $teamId = null) {
    global $pdo;

    try {
        $sql = "
            SELECT
                a.*,
                u.first_name,
                u.last_name,
                u.email
            FROM activities a
            LEFT JOIN users u ON a.user_id = u.id
        ";

        $params = [];
        $conditions = [];

        if ($userId !== null) {
            // Get activities for a specific user or their teams
            $conditions[] = "(a.user_id = ? OR a.user_id IN (
                SELECT tm2.user_id
                FROM team_members tm1
                JOIN team_members tm2 ON tm1.team_id = tm2.team_id
                WHERE tm1.user_id = ?
            ))";
            $params[] = $userId;
            $params[] = $userId;
        }

        if ($teamId !== null) {
            $conditions[] = "a.user_id IN (SELECT user_id FROM team_members WHERE team_id = ?)";
            $params[] = $teamId;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY a.created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Failed to get recent activities: " . $e->getMessage());
        return [];
    }
}