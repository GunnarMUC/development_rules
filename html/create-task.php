<?php
session_start();
require_once 'includes/auth.php';
require_once 'includes/db.php';

// Check if user is logged in
require_login();

$pageTitle = 'Create Task';
$currentPage = 'create-task';
$page_title = 'Create Task';

// Get users for assignment dropdown
try {
    $users_stmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, email FROM users ORDER BY first_name, last_name");
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<!-- Main Content -->
<div class="col-md-9 col-lg-10 ms-sm-auto px-md-4" id="main-content">
    <div class="container-fluid" id="create-task-content" x-data="createTaskForm()">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><i class="bi bi-plus-circle"></i> Create New Task</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="tasks.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Tasks
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-lg-8">
                <div class="card" id="create-task-form-card">
                    <div class="card-header">
                        <h5 class="mb-0">Task Details</h5>
                    </div>
                    <div class="card-body">
                        <div id="alert-container"></div>

                        <form hx-post="api/tasks.php?action=create"
                              hx-target="#alert-container"
                              hx-indicator="#submitBtn .spinner-border"
                              @submit="handleSubmit"
                              @htmx:after-request="handleResponse($event)">

                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                            <div class="mb-3">
                                <label for="title" class="form-label">Task Title *</label>
                                <input type="text"
                                       class="form-control"
                                       :class="{ 'is-invalid': errors.title }"
                                       id="title"
                                       name="title"
                                       x-model="formData.title"
                                       @blur="validateTitle"
                                       required
                                       placeholder="Enter a clear, descriptive title">
                                <div class="invalid-feedback" x-show="errors.title" x-text="errors.title"></div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control"
                                          id="description"
                                          name="description"
                                          x-model="formData.description"
                                          rows="4"
                                          placeholder="Provide details about the task..."></textarea>
                                <small class="text-muted">Include all necessary information for completing this task</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">Priority</label>
                                    <select class="form-select"
                                            id="priority"
                                            name="priority"
                                            x-model="formData.priority">
                                        <option value="low">🟢 Low</option>
                                        <option value="medium" selected>🟡 Medium</option>
                                        <option value="high">🟠 High</option>
                                        <option value="critical">🔴 Critical</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="datetime-local"
                                           class="form-control"
                                           id="due_date"
                                           name="due_date"
                                           x-model="formData.due_date">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="assigned_to" class="form-label">Assign To</label>
                                    <select class="form-select"
                                            id="assigned_to"
                                            name="assigned_to"
                                            x-model="formData.assigned_to">
                                        <option value="">Unassigned</option>
                                        <?php foreach ($users as $user): ?>
                                            <option value="<?php echo $user['id']; ?>">
                                                <?php echo htmlspecialchars($user['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <input type="text"
                                           class="form-control"
                                           id="category"
                                           name="category"
                                           x-model="formData.category"
                                           placeholder="e.g., Development, Design, Marketing">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="tags" class="form-label">Tags</label>
                                <input type="text"
                                       class="form-control"
                                       id="tags"
                                       name="tags"
                                       x-model="formData.tags"
                                       placeholder="Comma-separated tags (e.g., urgent, bug, feature)">
                                <small class="text-muted">Add tags to help categorize and filter tasks</small>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="tasks.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit"
                                        class="btn btn-primary"
                                        id="submitBtn"
                                        :disabled="!isValid || loading">
                                    <span class="spinner-border spinner-border-sm htmx-indicator" role="status"></span>
                                    <i class="bi bi-plus-circle" x-show="!loading"></i>
                                    <span x-show="!loading">Create Task</span>
                                    <span x-show="loading">Creating...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card" id="create-task-help">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-lightbulb"></i> Quick Tips</h5>
                    </div>
                    <div class="card-body">
                        <h6>Priority Levels:</h6>
                        <ul class="small">
                            <li><span class="text-secondary">🟢 Low:</span> Can be done when time permits</li>
                            <li><span class="text-info">🟡 Medium:</span> Should be completed soon</li>
                            <li><span class="text-warning">🟠 High:</span> Needs immediate attention</li>
                            <li><span class="text-danger">🔴 Critical:</span> Must be done ASAP</li>
                        </ul>

                        <h6 class="mt-3">Best Practices:</h6>
                        <ul class="small">
                            <li>Use clear, descriptive titles</li>
                            <li>Set realistic due dates</li>
                            <li>Add relevant tags for easy filtering</li>
                            <li>Assign tasks to appropriate team members</li>
                            <li>Include all necessary details in description</li>
                        </ul>

                        <div class="alert alert-info alert-sm mt-3">
                            <i class="bi bi-info-circle"></i> <strong>Tip:</strong> You can assign tasks to yourself or leave them unassigned.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function createTaskForm() {
    return {
        formData: {
            title: '',
            description: '',
            priority: 'medium',
            due_date: '',
            assigned_to: '',
            category: '',
            tags: ''
        },
        errors: {
            title: ''
        },
        loading: false,

        validateTitle() {
            if (!this.formData.title || this.formData.title.trim() === '') {
                this.errors.title = 'Task title is required';
            } else if (this.formData.title.trim().length < 3) {
                this.errors.title = 'Task title must be at least 3 characters';
            } else {
                this.errors.title = '';
            }
        },

        handleSubmit(event) {
            this.validateTitle();

            if (this.errors.title) {
                event.preventDefault();
                return false;
            }

            this.loading = true;
        },

        handleResponse(event) {
            this.loading = false;

            try {
                const xhr = event.detail.xhr;
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);

                    if (response.success) {
                        // Show success message
                        document.getElementById('alert-container').innerHTML =
                            '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                            '<i class="bi bi-check-circle me-2"></i>' + response.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                            '</div>';

                        // Reset form
                        this.formData = {
                            title: '',
                            description: '',
                            priority: 'medium',
                            due_date: '',
                            assigned_to: '',
                            category: '',
                            tags: ''
                        };

                        // Redirect to tasks page after a short delay
                        setTimeout(() => {
                            window.location.href = 'tasks.php';
                        }, 1500);
                    } else {
                        // Show error message
                        document.getElementById('alert-container').innerHTML =
                            '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                            '<i class="bi bi-exclamation-triangle me-2"></i>' + response.message +
                            '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                            '</div>';
                    }
                }
            } catch (e) {
                console.error('Response parsing error:', e);
                document.getElementById('alert-container').innerHTML =
                    '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<i class="bi bi-exclamation-triangle me-2"></i>An error occurred. Please try again.' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
            }
        },

        get isValid() {
            return this.formData.title &&
                   this.formData.title.trim().length >= 3 &&
                   !this.errors.title;
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>
