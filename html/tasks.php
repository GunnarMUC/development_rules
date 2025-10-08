<?php
// Tasks page with modular components
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

// Additional CSS for this page
$additional_css = '
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
';

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
    </style>

<!-- Main Container -->
<div class="container-fluid">
    <div class="row">
        <?php
        // Force topnav layout for tasks page (hide sidebar)
        $nav_layout = 'topnav';
        $main_col_class = 'col-lg-12';
        ?>

        <!-- Sidebar is hidden on tasks page -->
        <?php if (false): // Never show sidebar on tasks page ?>
            <?php include 'includes/sidebar.php'; ?>
        <?php endif; ?>

        <!-- Main Content -->
        <main class="<?php echo $main_col_class; ?> px-md-4 mt-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">My Tasks</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                                <i class="bi bi-plus-lg"></i> New Task
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Task Statistics -->
                <div id="tasksStats" class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-primary">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Total Tasks</h5>
                                <p class="card-text display-6" id="stat-total">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-success">
                            <div class="card-body">
                                <h5 class="card-title text-success">Completed</h5>
                                <p class="card-text display-6" id="stat-completed">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-warning">
                            <div class="card-body">
                                <h5 class="card-title text-warning">In Progress</h5>
                                <p class="card-text display-6" id="stat-progress">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-danger">
                            <div class="card-body">
                                <h5 class="card-title text-danger">Overdue</h5>
                                <p class="card-text display-6" id="stat-overdue">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Filters -->
                <div class="card mb-3" id="task-filters-card">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="filter-status" class="form-label">Status</label>
                                <select id="filter-status" class="form-select filter-select">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter-priority" class="form-label">Priority</label>
                                <select id="filter-priority" class="form-select filter-select">
                                    <option value="">All Priorities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter-assignee" class="form-label">Assignee</label>
                                <select id="filter-assignee" class="form-select filter-select">
                                    <option value="">All Assignees</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="button" class="btn btn-primary" id="apply-filters">
                                        <i class="bi bi-funnel"></i> Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="reset-filters">
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
                                <tbody>
                                    <!-- Data will be loaded via DataTables AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
        </main>
    </div>
</div>

<!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addTaskModalLabel">Add New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addTaskForm">
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
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="taskAssignedTo" class="form-label">Assign To</label>
                            <select class="form-select" id="taskAssignedTo" name="assigned_to">
                                <option value="">-- Not Assigned --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="taskDueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="taskDueDate" name="due_date">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Task Modal -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTaskForm">
                    <input type="hidden" id="editTaskId" name="id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editTaskTitle" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editTaskTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editTaskDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editTaskPriority" class="form-label">Priority</label>
                                <select class="form-select" id="editTaskPriority" name="priority">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editTaskStatus" class="form-label">Status</label>
                                <select class="form-select" id="editTaskStatus" name="status">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskAssignedTo" class="form-label">Assign To</label>
                            <select class="form-select" id="editTaskAssignedTo" name="assigned_to">
                                <option value="">-- Not Assigned --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskDueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="editTaskDueDate" name="due_date">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php
// Include footer (which loads jQuery and other scripts)
include_once 'includes/footer.php';
?>

<!-- Tasks-specific JavaScript -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/tasks.js"></script>