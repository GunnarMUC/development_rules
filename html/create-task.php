<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/activity_logger.php';

// Check if user is logged in
require_login();

$pageTitle = 'Create Task';
$currentPage = 'create-task';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $due_date = $_POST['due_date'] ?? null;
    $category = $_POST['category'] ?? null;
    $tags = $_POST['tags'] ?? null;
    $assigned_to = $_POST['assigned_to'] ?? null;
    // Convert empty string to null for database foreign key constraint
    if ($assigned_to === '') {
        $assigned_to = null;
    }

    if (!empty($title)) {
        try {
            // Get the current user ID from session
            $user_id = $_SESSION['user_id'] ?? null;

            // If no user_id in session, try to get from user array
            if (!$user_id && isset($_SESSION['user']['id'])) {
                $user_id = $_SESSION['user']['id'];
            }

            if (!$user_id) {
                throw new Exception("User not logged in properly. Please log out and log in again.");
            }

            // Get user's first team as default team_id
            $team_id = null;
            $team_stmt = $pdo->prepare("
                SELECT team_id
                FROM team_members
                WHERE user_id = ?
                ORDER BY joined_at ASC
                LIMIT 1
            ");
            $team_stmt->execute([$user_id]);
            $team_result = $team_stmt->fetch(PDO::FETCH_ASSOC);
            if ($team_result) {
                $team_id = $team_result['team_id'];
            }

            $stmt = $pdo->prepare("
                INSERT INTO tasks (user_id, team_id, title, description, priority, due_date, status, category, tags, created_by, assigned_to, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $user_id,
                $team_id,
                $title,
                $description,
                $priority,
                $due_date,
                $category,
                $tags,
                $user_id, // created_by is the same as user_id
                $assigned_to
            ]);

            // Get the ID of the newly created task
            $task_id = $pdo->lastInsertId();

            // Log the activity
            $taskData = [
                'id' => $task_id,
                'title' => $title
            ];
            logTaskActivity($user_id, 'created', $taskData);

            // If task was assigned to someone, log that too
            if ($assigned_to && $assigned_to != $user_id) {
                $assignee_stmt = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM users WHERE id = ?");
                $assignee_stmt->execute([$assigned_to]);
                $assignee = $assignee_stmt->fetch(PDO::FETCH_ASSOC);
                if ($assignee) {
                    $taskData['assigned_name'] = $assignee['name'];
                    logTaskActivity($user_id, 'assigned', $taskData);
                }
            }

            $_SESSION['success_message'] = 'Task created successfully!';
            header('Location: tasks.php');
            exit();
        } catch (PDOException $e) {
            $error_message = 'Error creating task: ' . $e->getMessage();
        }
    } else {
        $error_message = 'Task title is required.';
    }
}

// Get users for assignment dropdown
try {
    $users_stmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, email FROM users ORDER BY first_name, last_name");
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Task Management</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Main Content (Full Width) -->
            <main class="col-12 px-md-4">
                <!-- Header -->
                <?php include 'includes/header.php'; ?>

                <!-- Page Content -->
                <div class="container-fluid" id="create-task-content">
                    <div class="row">
                        <div class="col-12 col-lg-8">
                            <div class="card" id="create-task-form-card">
                                <div class="card-header">
                                    <h5 class="mb-0">Create New Task</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($error_message)): ?>
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <?php echo htmlspecialchars($error_message); ?>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endif; ?>

                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Task Title *</label>
                                            <input type="text" class="form-control" id="title" name="title" required
                                                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="priority" class="form-label">Priority</label>
                                                <select class="form-select" id="priority" name="priority">
                                                    <option value="low">Low</option>
                                                    <option value="medium" selected>Medium</option>
                                                    <option value="high">High</option>
                                                    <option value="critical">Critical</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="due_date" class="form-label">Due Date</label>
                                                <input type="datetime-local" class="form-control" id="due_date" name="due_date"
                                                       value="<?php echo isset($_POST['due_date']) ? htmlspecialchars($_POST['due_date']) : ''; ?>">
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="assigned_to" class="form-label">Assign To</label>
                                                <select class="form-select" id="assigned_to" name="assigned_to">
                                                    <option value="">Unassigned</option>
                                                    <?php foreach ($users as $user): ?>
                                                        <option value="<?php echo $user['id']; ?>"
                                                                <?php echo isset($_POST['assigned_to']) && $_POST['assigned_to'] == $user['id'] ? 'selected' : ''; ?>>
                                                            <?php echo htmlspecialchars($user['name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6 mb-3">
                                                <label for="category" class="form-label">Category</label>
                                                <input type="text" class="form-control" id="category" name="category"
                                                       placeholder="e.g., Development, Design, Marketing"
                                                       value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>">
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="tags" class="form-label">Tags</label>
                                            <input type="text" class="form-control" id="tags" name="tags"
                                                   placeholder="Comma-separated tags (e.g., urgent, bug, feature)"
                                                   value="<?php echo isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : ''; ?>">
                                        </div>

                                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                            <a href="tasks.php" class="btn btn-secondary">Cancel</a>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-plus-circle"></i> Create Task
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4">
                            <div class="card" id="create-task-help">
                                <div class="card-header">
                                    <h5 class="mb-0">Quick Tips</h5>
                                </div>
                                <div class="card-body">
                                    <h6>Priority Levels:</h6>
                                    <ul class="small">
                                        <li><span class="text-secondary">Low:</span> Can be done when time permits</li>
                                        <li><span class="text-info">Medium:</span> Should be completed soon</li>
                                        <li><span class="text-warning">High:</span> Needs immediate attention</li>
                                        <li><span class="text-danger">Critical:</span> Must be done ASAP</li>
                                    </ul>

                                    <h6 class="mt-3">Best Practices:</h6>
                                    <ul class="small">
                                        <li>Use clear, descriptive titles</li>
                                        <li>Set realistic due dates</li>
                                        <li>Add relevant tags for easy filtering</li>
                                        <li>Assign tasks to appropriate team members</li>
                                        <li>Include all necessary details in description</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2 for better dropdown experience
            $('#assigned_to').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select a user'
            });
        });
    </script>
</body>
</html>