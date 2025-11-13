<?php
// Tasks page with HTMX
require_once 'includes/session.php';
require_once 'includes/auth.php';

// Require login
require_login();

// Get current user
$user = get_current_user_info();

// Get database connection
require_once 'includes/db.php';

// Page-specific settings
$page_title = 'Tasks';

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get users for assignment dropdown
try {
    $users_stmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name, email FROM users ORDER BY first_name, last_name");
    $users = $users_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
}

// Include header
include_once 'includes/header.php';
?>

<!-- Additional CSS for Tasks -->
<style>
    .priority-low { color: #6c757d; }
    .priority-medium { color: #17a2b8; }
    .priority-high { color: #ffc107; }
    .priority-critical { color: #dc3545; }

    .status-badge {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }

    .task-actions button {
        padding: 0.25rem 0.5rem;
        margin: 0 0.125rem;
    }

    .task-completed {
        text-decoration: line-through;
        opacity: 0.7;
    }

    #tasksStats {
        margin-bottom: 2rem;
    }

    .stat-card {
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .htmx-indicator {
        display: none;
    }

    .htmx-request .htmx-indicator {
        display: inline-block;
    }

    .htmx-request.htmx-indicator {
        display: inline-block;
    }
</style>

<!-- Main Container -->
<div class="container-fluid" x-data="tasksPage()">
    <div class="row">
        <!-- Main Content -->
        <main class="col-lg-12 px-md-4 mt-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><i class="bi bi-list-task"></i> My Tasks</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-primary" @click="showAddModal = true">
                            <i class="bi bi-plus-lg"></i> New Task
                        </button>
                        <a href="create-task.php" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle"></i> Create Task
                        </a>
                    </div>
                </div>
            </div>

            <!-- Task Statistics -->
            <div id="tasksStats"
                 class="row"
                 hx-get="api/tasks-html.php?action=stats"
                 hx-trigger="load, taskUpdated from:body, taskDeleted from:body"
                 hx-swap="none"
                 @htmx:after-request="updateStats($event)">
                <div class="col-md-3 mb-3">
                    <div class="card stat-card border-primary">
                        <div class="card-body">
                            <h5 class="card-title text-primary">Total Tasks</h5>
                            <p class="card-text display-6" x-text="stats.total">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card border-success">
                        <div class="card-body">
                            <h5 class="card-title text-success">Completed</h5>
                            <p class="card-text display-6" x-text="stats.completed">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card border-warning">
                        <div class="card-body">
                            <h5 class="card-title text-warning">In Progress</h5>
                            <p class="card-text display-6" x-text="stats.in_progress">0</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stat-card border-danger">
                        <div class="card-body">
                            <h5 class="card-title text-danger">Overdue</h5>
                            <p class="card-text display-6" x-text="stats.overdue">0</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Task Filters -->
            <div class="card mb-3" id="task-filters-card">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="filter_status" class="form-label">Status</label>
                            <select name="filter_status" id="filter_status" class="form-select" x-model="filters.status">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_priority" class="form-label">Priority</label>
                            <select name="filter_priority" id="filter_priority" class="form-select" x-model="filters.priority">
                                <option value="">All Priorities</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter_assignee" class="form-label">Assignee</label>
                            <select name="filter_assignee" id="filter_assignee" class="form-select" x-model="filters.assignee">
                                <option value="">All Assignees</option>
                                <?php foreach ($users as $user_option): ?>
                                    <option value="<?php echo $user_option['id']; ?>">
                                        <?php echo htmlspecialchars($user_option['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div>
                                <button type="button"
                                        class="btn btn-primary"
                                        hx-get="api/tasks-html.php?action=list_html"
                                        hx-target="#tasks-tbody"
                                        hx-include="[name='filter_status'], [name='filter_priority'], [name='filter_assignee']">
                                    <i class="bi bi-funnel"></i> Apply Filters
                                </button>
                                <button type="button"
                                        class="btn btn-outline-secondary"
                                        @click="resetFilters()">
                                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tasksTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="5%">ID</th>
                                    <th width="25%">Title</th>
                                    <th width="15%">Assigned To</th>
                                    <th width="10%">Status</th>
                                    <th width="10%">Priority</th>
                                    <th width="10%">Due Date</th>
                                    <th width="10%">Created</th>
                                    <th width="15%">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tasks-tbody"
                                   hx-get="api/tasks-html.php?action=list_html"
                                   hx-trigger="load, taskUpdated from:body, taskDeleted from:body"
                                   hx-include="[name='filter_status'], [name='filter_priority'], [name='filter_assignee']"
                                   hx-indicator="#loading-indicator">
                                <!-- Tasks loaded via HTMX -->
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <div class="spinner-border text-primary" role="status" id="loading-indicator">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Task Modal -->
<div class="modal fade"
     :class="{ 'show': showAddModal }"
     :style="showAddModal ? 'display: block' : ''"
     tabindex="-1"
     @click.self="showAddModal = false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Task</h5>
                <button type="button" class="btn-close" @click="showAddModal = false"></button>
            </div>
            <form hx-post="api/tasks.php?action=create"
                  hx-target="#tasks-tbody"
                  hx-swap="afterbegin"
                  @htmx:after-request="handleTaskAdded($event)">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="taskTitle" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="taskTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="taskDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="taskDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="taskPriority" class="form-label">Priority</label>
                            <select class="form-select" id="taskPriority" name="priority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="taskStatus" class="form-label">Status</label>
                            <select class="form-select" id="taskStatus" name="status">
                                <option value="pending" selected>Pending</option>
                                <option value="in_progress">In Progress</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="taskAssignedTo" class="form-label">Assign To</label>
                        <select class="form-select" id="taskAssignedTo" name="assigned_to">
                            <option value="">-- Not Assigned --</option>
                            <?php foreach ($users as $user_option): ?>
                                <option value="<?php echo $user_option['id']; ?>">
                                    <?php echo htmlspecialchars($user_option['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="taskDueDate" class="form-label">Due Date</label>
                        <input type="date" class="form-control" id="taskDueDate" name="due_date">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="showAddModal = false">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Task</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal backdrop -->
<div class="modal-backdrop fade"
     :class="{ 'show': showAddModal }"
     x-show="showAddModal"
     x-cloak
     style="display: none;"
     @click="showAddModal = false"></div>

<script>
function tasksPage() {
    return {
        showAddModal: false,
        filters: {
            status: '',
            priority: '',
            assignee: ''
        },
        stats: {
            total: 0,
            completed: 0,
            in_progress: 0,
            overdue: 0
        },

        resetFilters() {
            this.filters = {
                status: '',
                priority: '',
                assignee: ''
            };
            // Reload tasks
            htmx.trigger('#tasks-tbody', 'taskUpdated');
        },

        handleTaskAdded(event) {
            const xhr = event.detail.xhr;
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    this.showAddModal = false;
                    // Reset form
                    document.getElementById('taskTitle').value = '';
                    document.getElementById('taskDescription').value = '';
                    // Trigger stats update
                    htmx.trigger(document.body, 'taskUpdated');
                }
            }
        },

        updateStats(event) {
            const xhr = event.detail.xhr;
            if (xhr.status === 200) {
                const stats = JSON.parse(xhr.responseText);
                this.stats = stats;
            }
        }
    }
}
</script>

<?php include_once 'includes/footer.php'; ?>
