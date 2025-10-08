<?php
/**
 * Task Model Class
 * Handles all task-related database operations
 */

class Task {
    private $db;

    public function __construct() {
        require_once __DIR__ . '/Database.php';
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new task
     * @param array $data Task data
     * @return int|false Task ID on success, false on failure
     */
    public function create($data) {
        try {
            // If team_id is not provided, get user's first team as default
            if (empty($data['team_id']) && !empty($data['user_id'])) {
                $teamStmt = $this->db->prepare("
                    SELECT team_id
                    FROM team_members
                    WHERE user_id = :user_id
                    ORDER BY joined_at ASC
                    LIMIT 1
                ");
                $teamStmt->execute([':user_id' => $data['user_id']]);
                $teamResult = $teamStmt->fetch(PDO::FETCH_ASSOC);
                if ($teamResult) {
                    $data['team_id'] = $teamResult['team_id'];
                }
            }

            $sql = "INSERT INTO tasks (user_id, team_id, assigned_to, title, description, status, priority, due_date)
                    VALUES (:user_id, :team_id, :assigned_to, :title, :description, :status, :priority, :due_date)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':team_id' => $data['team_id'] ?? null,
                ':assigned_to' => $data['assigned_to'] ?? null,
                ':title' => $data['title'],
                ':description' => $data['description'] ?? '',
                ':status' => $data['status'] ?? 'pending',
                ':priority' => $data['priority'] ?? 'medium',
                ':due_date' => $data['due_date'] ?? null
            ]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Task creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all tasks for a user
     * @param int $user_id User ID
     * @param array $filters Optional filters
     * @return array Array of tasks
     */
    public function getUserTasks($user_id, $filters = []) {
        try {
            $sql = "SELECT t.*, u.first_name as assigned_first_name, u.last_name as assigned_last_name, u.email as assigned_email
                    FROM tasks t
                    LEFT JOIN users u ON t.assigned_to = u.id
                    WHERE (t.user_id = :user_id OR t.assigned_to = :user_id2)";
            $params = [':user_id' => $user_id, ':user_id2' => $user_id];

            // Filter by team if provided
            if (!empty($filters['team_id'])) {
                $sql .= " AND t.team_id = :team_id";
                $params[':team_id'] = $filters['team_id'];
            }

            // Add filters if provided
            if (!empty($filters['status'])) {
                $sql .= " AND t.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['priority'])) {
                $sql .= " AND t.priority = :priority";
                $params[':priority'] = $filters['priority'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (t.title LIKE :search OR t.description LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            // Add sorting
            $sql .= " ORDER BY
                    CASE
                        WHEN t.status = 'completed' THEN 1
                        ELSE 0
                    END,
                    CASE t.priority
                        WHEN 'critical' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'medium' THEN 3
                        WHEN 'low' THEN 4
                    END,
                    t.due_date ASC,
                    t.created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to fetch user tasks: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single task by ID
     * @param int $id Task ID
     * @param int $user_id User ID (for security)
     * @param int $team_id Optional team ID
     * @return array|false Task data or false if not found
     */
    public function getById($id, $user_id, $team_id = null) {
        try {
            $sql = "SELECT t.*, u.first_name as assigned_first_name, u.last_name as assigned_last_name, u.email as assigned_email
                    FROM tasks t
                    LEFT JOIN users u ON t.assigned_to = u.id
                    WHERE t.id = :id AND (t.user_id = :user_id OR t.assigned_to = :user_id2)";
            $params = [':id' => $id, ':user_id' => $user_id, ':user_id2' => $user_id];

            if ($team_id !== null) {
                $sql .= " AND t.team_id = :team_id";
                $params[':team_id'] = $team_id;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to fetch task: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a task
     * @param int $id Task ID
     * @param array $data Updated task data
     * @param int $user_id User ID (for security)
     * @return bool Success status
     */
    public function update($id, $data, $user_id) {
        try {
            // Build dynamic update query
            $updateFields = [];
            $params = [
                ':id' => $id,
                ':user_id' => $user_id
            ];

            $allowedFields = ['title', 'description', 'status', 'priority', 'due_date', 'assigned_to', 'team_id'];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[":$field"] = $data[$field];
                }
            }

            // Handle status change to completed
            if (isset($data['status']) && $data['status'] === 'completed') {
                $updateFields[] = "completed_at = NOW()";
            } elseif (isset($data['status']) && $data['status'] !== 'completed') {
                $updateFields[] = "completed_at = NULL";
            }

            if (empty($updateFields)) {
                return false;
            }

            $sql = "UPDATE tasks SET " . implode(', ', $updateFields) .
                   " WHERE id = :id AND user_id = :user_id";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($params);

            return $result && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Task update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a task
     * @param int $id Task ID
     * @param int $user_id User ID (for security)
     * @return bool Success status
     */
    public function delete($id, $user_id) {
        try {
            $sql = "DELETE FROM tasks WHERE id = :id AND user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':id' => $id,
                ':user_id' => $user_id
            ]);

            return $result && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Task deletion failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Toggle task completion status
     * @param int $id Task ID
     * @param int $user_id User ID
     * @return bool Success status
     */
    public function toggleComplete($id, $user_id) {
        try {
            // First get current status
            $task = $this->getById($id, $user_id);
            if (!$task) {
                return false;
            }

            $newStatus = ($task['status'] === 'completed') ? 'pending' : 'completed';

            return $this->update($id, ['status' => $newStatus], $user_id);
        } catch (PDOException $e) {
            error_log("Task toggle failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get task statistics for a user
     * @param int $user_id User ID
     * @param int $team_id Optional team ID
     * @param bool $my_tasks_only Only count tasks assigned to user
     * @return array Statistics
     */
    public function getUserStatistics($user_id, $team_id = null, $my_tasks_only = false) {
        try {
            $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN due_date < CURDATE() AND status != 'completed' THEN 1 ELSE 0 END) as overdue
                    FROM tasks
                    WHERE ";

            if ($my_tasks_only) {
                $sql .= "assigned_to = :user_id";
                $params = [':user_id' => $user_id];
            } else {
                $sql .= "(user_id = :user_id OR assigned_to = :user_id2)";
                $params = [':user_id' => $user_id, ':user_id2' => $user_id];
            }
            if ($team_id !== null) {
                $sql .= " AND team_id = :team_id";
                $params[':team_id'] = $team_id;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to fetch statistics: " . $e->getMessage());
            return [
                'total' => 0,
                'completed' => 0,
                'pending' => 0,
                'in_progress' => 0,
                'overdue' => 0
            ];
        }
    }

    /**
     * Get team members for task assignment
     * @param int $team_id Team ID
     * @return array Team members
     */
    public function getTeamMembers($team_id) {
        try {
            $sql = "SELECT u.id, u.first_name, u.last_name, u.email
                    FROM users u
                    JOIN team_members tm ON u.id = tm.user_id
                    WHERE tm.team_id = :team_id
                    ORDER BY u.first_name, u.last_name";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':team_id' => $team_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Failed to fetch team members: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get tasks for DataTables server-side processing
     * @param int $user_id User ID
     * @param array $request DataTables request parameters
     * @return array DataTables response format
     */
    public function getDataTableTasks($user_id, $request) {
        try {
            $draw = intval($request['draw'] ?? 1);
            $start = intval($request['start'] ?? 0);
            $length = intval($request['length'] ?? 10);

            // Base query
            $myTasksOnly = isset($request['my_tasks_only']) && $request['my_tasks_only'];

            if ($myTasksOnly) {
                $baseQuery = "FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE (t.user_id = :user_id OR t.assigned_to = :user_id2)";
                $params = [':user_id' => $user_id, ':user_id2' => $user_id];
            } else {
                $baseQuery = "FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id WHERE (t.user_id = :user_id OR t.assigned_to = :user_id2)";
                $params = [':user_id' => $user_id, ':user_id2' => $user_id];
            }

            // Filter by team if provided
            if (!empty($request['team_id'])) {
                $baseQuery .= " AND t.team_id = :team_id";
                $params[':team_id'] = $request['team_id'];
            }

            // Filter by status
            if (!empty($request['filter_status'])) {
                $baseQuery .= " AND t.status = :filter_status";
                $params[':filter_status'] = $request['filter_status'];
            }

            // Filter by priority
            if (!empty($request['filter_priority'])) {
                $baseQuery .= " AND t.priority = :filter_priority";
                $params[':filter_priority'] = $request['filter_priority'];
            }

            // Filter by assignee
            if (!empty($request['filter_assignee'])) {
                $baseQuery .= " AND t.assigned_to = :filter_assignee";
                $params[':filter_assignee'] = $request['filter_assignee'];
            }

            // Apply quick filters
            if (!empty($request['quick_filter'])) {
                switch ($request['quick_filter']) {
                    case 'today':
                        $baseQuery .= " AND DATE(t.due_date) = CURDATE()";
                        break;
                    case 'week':
                        $baseQuery .= " AND t.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
                        break;
                    case 'overdue':
                        $baseQuery .= " AND t.due_date < CURDATE() AND t.status != 'completed'";
                        break;
                    case 'completed':
                        $baseQuery .= " AND t.status = 'completed'";
                        break;
                }
            }

            // Search functionality
            if (!empty($request['search']['value'])) {
                $search = $request['search']['value'];
                $baseQuery .= " AND (title LIKE :search OR description LIKE :search2)";
                $params[':search'] = '%' . $search . '%';
                $params[':search2'] = '%' . $search . '%';
            }

            // Get total count
            $countStmt = $this->db->prepare("SELECT COUNT(*) " . $baseQuery);
            $countStmt->execute($params);
            $totalFiltered = $countStmt->fetchColumn();

            // Get total without filter
            if ($myTasksOnly) {
                $countParams = [':user_id' => $user_id];
                $countQuery = "SELECT COUNT(*) FROM tasks WHERE assigned_to = :user_id";
            } else {
                $countParams = [':user_id' => $user_id, ':user_id2' => $user_id];
                $countQuery = "SELECT COUNT(*) FROM tasks WHERE (user_id = :user_id OR assigned_to = :user_id2)";
            }
            if (!empty($request['team_id'])) {
                $countQuery .= " AND team_id = :team_id";
                $countParams[':team_id'] = $request['team_id'];
            }
            $totalStmt = $this->db->prepare($countQuery);
            $totalStmt->execute($countParams);
            $total = $totalStmt->fetchColumn();

            // Main query with pagination
            $sql = "SELECT t.id, t.title, t.description, t.status, t.priority, t.due_date,
                    t.created_at, t.completed_at, t.user_id, t.assigned_to,
                    u.first_name as assigned_first_name, u.last_name as assigned_last_name " . $baseQuery;

            // Add ordering
            if (!empty($request['sort_by'])) {
                switch ($request['sort_by']) {
                    case 'due_date':
                        $sql .= " ORDER BY t.due_date ASC, t.created_at DESC";
                        break;
                    case 'priority':
                        $sql .= " ORDER BY FIELD(t.priority, 'critical', 'high', 'medium', 'low'), t.created_at DESC";
                        break;
                    case 'created':
                        $sql .= " ORDER BY t.created_at DESC";
                        break;
                    case 'title':
                        $sql .= " ORDER BY t.title ASC";
                        break;
                    default:
                        $sql .= " ORDER BY t.created_at DESC";
                }
            } elseif (isset($request['order'])) {
                $columns = ['id', 'title', 'status', 'priority', 'due_date', 'created_at'];
                $orderColumn = $columns[$request['order'][0]['column']] ?? 'created_at';
                $orderDir = $request['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';
                $sql .= " ORDER BY $orderColumn $orderDir";
            } else {
                $sql .= " ORDER BY t.created_at DESC";
            }

            // Add pagination
            $sql .= " LIMIT :start, :length";
            $params[':start'] = $start;
            $params[':length'] = $length;

            $stmt = $this->db->prepare($sql);

            // Bind parameters with proper types
            foreach ($params as $key => $value) {
                if ($key === ':start' || $key === ':length') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format data for DataTables
            foreach ($data as &$row) {
                $row['DT_RowId'] = 'row_' . $row['id'];
                $row['due_date_formatted'] = $row['due_date'] ? date('M j, Y', strtotime($row['due_date'])) : '-';
                $row['created_at_formatted'] = date('M j, Y', strtotime($row['created_at']));
            }

            return [
                'draw' => $draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $totalFiltered,
                'data' => $data
            ];

        } catch (PDOException $e) {
            error_log("DataTable query failed: " . $e->getMessage());
            return [
                'draw' => $request['draw'] ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ];
        }
    }

    /**
     * Get completed task statistics
     * @param int $user_id User ID
     * @param int|null $team_id Team ID
     * @return array Statistics array
     */
    public function getCompletedStatistics($user_id, $team_id = null) {
        try {
            // Base query for completed tasks
            $baseCondition = "(t.user_id = :user_id OR t.assigned_to = :user_id2) AND t.status = 'completed'";
            $params = [':user_id' => $user_id, ':user_id2' => $user_id];

            if ($team_id !== null) {
                $baseCondition .= " AND t.team_id = :team_id";
                $params[':team_id'] = $team_id;
            }

            // Total completed
            $sql = "SELECT COUNT(*) as total FROM tasks t WHERE $baseCondition";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $totalCompleted = $stmt->fetchColumn();

            // Completed this week
            $sql = "SELECT COUNT(*) as total FROM tasks t
                    WHERE $baseCondition
                    AND t.completed_at >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $weekCompleted = $stmt->fetchColumn();

            // Completed this month
            $sql = "SELECT COUNT(*) as total FROM tasks t
                    WHERE $baseCondition
                    AND MONTH(t.completed_at) = MONTH(CURRENT_DATE())
                    AND YEAR(t.completed_at) = YEAR(CURRENT_DATE())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $monthCompleted = $stmt->fetchColumn();

            // Calculate completion rate (completed vs total tasks)
            $sql = "SELECT COUNT(*) as total FROM tasks t
                    WHERE (t.user_id = :user_id OR t.assigned_to = :user_id2)";
            $totalParams = [':user_id' => $user_id, ':user_id2' => $user_id];
            if ($team_id !== null) {
                $sql .= " AND t.team_id = :team_id";
                $totalParams[':team_id'] = $team_id;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($totalParams);
            $totalTasks = $stmt->fetchColumn();

            $completionRate = $totalTasks > 0 ? ($totalCompleted / $totalTasks) * 100 : 0;

            return [
                'total_completed' => $totalCompleted,
                'week_completed' => $weekCompleted,
                'month_completed' => $monthCompleted,
                'completion_rate' => $completionRate
            ];

        } catch (PDOException $e) {
            error_log("Failed to get completed statistics: " . $e->getMessage());
            return [
                'total_completed' => 0,
                'week_completed' => 0,
                'month_completed' => 0,
                'completion_rate' => 0
            ];
        }
    }

    /**
     * Clear all completed tasks for a user
     * @param int $user_id User ID
     * @param int|null $team_id Team ID
     * @return int Number of deleted tasks
     */
    public function clearCompleted($user_id, $team_id = null) {
        try {
            $sql = "DELETE FROM tasks
                    WHERE (user_id = :user_id OR assigned_to = :user_id2)
                    AND status = 'completed'";
            $params = [':user_id' => $user_id, ':user_id2' => $user_id];

            if ($team_id !== null) {
                $sql .= " AND team_id = :team_id";
                $params[':team_id'] = $team_id;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->rowCount();

        } catch (PDOException $e) {
            error_log("Failed to clear completed tasks: " . $e->getMessage());
            return 0;
        }
    }
}