<?php
/**
 * Notifications API Endpoint
 * Handles all notification operations
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

$userId = $_SESSION['user_id'];
$teamId = $_SESSION['current_team_id'] ?? null;

// Get request action
$action = $_REQUEST['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = Database::getInstance()->getConnection();

    switch ($action) {
        case 'get_count':
            // Get unread notification count
            $sql = "SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode(['count' => intval($result['count'])]);
            break;

        case 'get_list':
            // Get notification list
            $limit = intval($_GET['limit'] ?? 20);
            $offset = intval($_GET['offset'] ?? 0);

            $sql = "SELECT n.*,
                    u.first_name as creator_first_name,
                    u.last_name as creator_last_name,
                    CASE
                        WHEN TIMESTAMPDIFF(MINUTE, n.created_at, NOW()) < 60 THEN CONCAT(TIMESTAMPDIFF(MINUTE, n.created_at, NOW()), ' minutes ago')
                        WHEN TIMESTAMPDIFF(HOUR, n.created_at, NOW()) < 24 THEN CONCAT(TIMESTAMPDIFF(HOUR, n.created_at, NOW()), ' hours ago')
                        WHEN TIMESTAMPDIFF(DAY, n.created_at, NOW()) < 7 THEN CONCAT(TIMESTAMPDIFF(DAY, n.created_at, NOW()), ' days ago')
                        ELSE DATE_FORMAT(n.created_at, '%b %d, %Y')
                    END as time_ago
                    FROM notifications n
                    LEFT JOIN users u ON n.created_by = u.id
                    WHERE n.user_id = ?
                    ORDER BY n.created_at DESC
                    LIMIT ? OFFSET ?";

            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $limit, $offset]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'notifications' => $notifications
            ]);
            break;

        case 'check_new':
            // Check for new notifications (for real-time updates)
            $lastCheck = $_SESSION['last_notification_check'] ?? date('Y-m-d H:i:s', strtotime('-1 minute'));

            $sql = "SELECT n.*,
                    CASE
                        WHEN TIMESTAMPDIFF(SECOND, n.created_at, NOW()) < 60 THEN 'just now'
                        ELSE CONCAT(TIMESTAMPDIFF(MINUTE, n.created_at, NOW()), ' minutes ago')
                    END as time_ago
                    FROM notifications n
                    WHERE n.user_id = ?
                    AND n.created_at > ?
                    AND n.is_read = 0
                    ORDER BY n.created_at DESC
                    LIMIT 1";

            $stmt = $db->prepare($sql);
            $stmt->execute([$userId, $lastCheck]);
            $newNotification = $stmt->fetch(PDO::FETCH_ASSOC);

            $_SESSION['last_notification_check'] = date('Y-m-d H:i:s');

            if ($newNotification) {
                echo json_encode([
                    'new_notification' => $newNotification
                ]);
            } else {
                echo json_encode(['new_notification' => null]);
            }
            break;

        case 'mark_read':
            // Mark a notification as read
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $notificationId = intval($_POST['notification_id'] ?? 0);
            if (!$notificationId) {
                throw new Exception('Invalid notification ID');
            }

            $sql = "UPDATE notifications SET is_read = 1, read_at = NOW()
                    WHERE id = ? AND user_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$notificationId, $userId]);

            echo json_encode([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
            break;

        case 'mark_all_read':
            // Mark all notifications as read
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $sql = "UPDATE notifications SET is_read = 1, read_at = NOW()
                    WHERE user_id = ? AND is_read = 0";
            $stmt = $db->prepare($sql);
            $stmt->execute([$userId]);

            echo json_encode([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);
            break;

        case 'create':
            // Create a notification (called from other APIs)
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $type = $_POST['type'] ?? 'system';
            $title = $_POST['title'] ?? '';
            $message = $_POST['message'] ?? '';
            $link = $_POST['link'] ?? null;
            $targetUserId = intval($_POST['target_user_id'] ?? 0);

            if (!$targetUserId || !$title) {
                throw new Exception('Invalid notification data');
            }

            $sql = "INSERT INTO notifications (user_id, team_id, type, title, message, link, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $targetUserId,
                $teamId,
                $type,
                $title,
                $message,
                $link,
                $userId
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Notification created',
                'id' => $db->lastInsertId()
            ]);
            break;

        case 'delete':
            // Delete a notification
            if ($method !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $notificationId = intval($_POST['notification_id'] ?? 0);
            if (!$notificationId) {
                throw new Exception('Invalid notification ID');
            }

            $sql = "DELETE FROM notifications WHERE id = ? AND user_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$notificationId, $userId]);

            echo json_encode([
                'success' => true,
                'message' => 'Notification deleted'
            ]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    error_log("Notification API error: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Helper function to create a notification (can be called from other parts of the application)
 */
function createNotification($targetUserId, $type, $title, $message = '', $link = null, $createdBy = null, $teamId = null) {
    try {
        $db = Database::getInstance()->getConnection();

        $sql = "INSERT INTO notifications (user_id, team_id, type, title, message, link, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        return $stmt->execute([
            $targetUserId,
            $teamId,
            $type,
            $title,
            $message,
            $link,
            $createdBy
        ]);
    } catch (Exception $e) {
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}
?>