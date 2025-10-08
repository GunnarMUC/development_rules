<?php
session_start();
require_once 'includes/auth.php';

// Check if user is logged in
require_login();

$pageTitle = 'My Tasks';
$currentPage = 'my-tasks';
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
    <!-- Removed DataTables CSS - using standard Bootstrap 5 tables -->
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- Custom CSS -->
    <link href="assets/css/custom.css" rel="stylesheet">

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

        .filter-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .overdue-task {
            color: #dc3545;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Main Content (Full Width - No Sidebar) -->
            <main class="col-12 px-md-4 mt-3">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">My Tasks</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-primary" id="new-task-btn">
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
                                <p class="card-text display-6" id="stat-in-progress">0</p>
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

                <!-- Quick Filter Pills -->
                <div class="filter-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="btn-group" role="group" aria-label="Quick filters">
                                <input type="radio" class="btn-check" name="quickFilter" id="filter-all" value="all" checked>
                                <label class="btn btn-outline-primary" for="filter-all">All</label>

                                <input type="radio" class="btn-check" name="quickFilter" id="filter-today" value="today">
                                <label class="btn btn-outline-primary" for="filter-today">Due Today</label>

                                <input type="radio" class="btn-check" name="quickFilter" id="filter-week" value="week">
                                <label class="btn btn-outline-primary" for="filter-week">This Week</label>

                                <input type="radio" class="btn-check" name="quickFilter" id="filter-overdue" value="overdue">
                                <label class="btn btn-outline-danger" for="filter-overdue">Overdue</label>

                                <input type="radio" class="btn-check" name="quickFilter" id="filter-completed" value="completed">
                                <label class="btn btn-outline-success" for="filter-completed">Completed</label>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <select id="sortBy" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="due_date">Sort by Due Date</option>
                                <option value="priority">Sort by Priority</option>
                                <option value="created">Sort by Created</option>
                                <option value="title">Sort by Title</option>
                            </select>
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
                                        <th width="30"><input type="checkbox" id="selectAll"></th>
                                        <th>Task</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="tasksTableBody">
                                    <!-- Table rows will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
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
                                <label for="editTaskStatus" class="form-label">Status</label>
                                <select class="form-select" id="editTaskStatus" name="status">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editTaskPriority" class="form-label">Priority</label>
                                <select class="form-select" id="editTaskPriority" name="priority">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editTaskDueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="editTaskDueDate" name="due_date">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            Update Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        let allTasks = [];
        let currentPage = 1;
        let itemsPerPage = 10;

        // Load tasks from API
        function loadTasks() {
            let quickFilter = $('input[name="quickFilter"]:checked').val();
            let sortBy = $('#sortBy').val();

            $.ajax({
                url: 'api/tasks.php',
                method: 'POST',
                data: {
                    action: 'list',
                    my_tasks_only: true,
                    quick_filter: quickFilter !== 'all' ? quickFilter : null,
                    sort_by: sortBy,
                    draw: 1,
                    start: 0,
                    length: 1000 // Get all tasks for client-side pagination
                },
                success: function(response) {
                    if (response.data) {
                        allTasks = response.data;
                        renderTable();
                    }
                },
                error: function() {
                    $('#tasksTableBody').html('<tr><td colspan="6" class="text-center">Error loading tasks</td></tr>');
                }
            });
        }

        // Render table with tasks
        function renderTable() {
            let tbody = $('#tasksTableBody');
            tbody.empty();

            if (allTasks.length === 0) {
                tbody.html('<tr><td colspan="6" class="text-center">You have no tasks assigned to you</td></tr>');
                return;
            }

            // Calculate pagination
            let startIndex = (currentPage - 1) * itemsPerPage;
            let endIndex = Math.min(startIndex + itemsPerPage, allTasks.length);
            let paginatedTasks = allTasks.slice(startIndex, endIndex);

            // Render tasks
            paginatedTasks.forEach(function(task) {
                let row = '<tr>';

                // Checkbox
                row += '<td><input type="checkbox" class="task-select" value="' + task.id + '"></td>';

                // Title
                let title = task.status === 'completed' ? '<span class="task-completed">' + task.title + '</span>' : task.title;
                if (task.description) {
                    title += '<br><small class="text-muted">' + task.description.substring(0, 50) + '...</small>';
                }
                row += '<td>' + title + '</td>';

                // Priority
                let icon = '<i class="bi bi-circle-fill priority-' + task.priority + ' me-1"></i>';
                row += '<td>' + icon + task.priority.charAt(0).toUpperCase() + task.priority.slice(1) + '</td>';

                // Status
                let badgeClass = '';
                switch(task.status) {
                    case 'pending': badgeClass = 'bg-secondary'; break;
                    case 'in_progress': badgeClass = 'bg-warning'; break;
                    case 'completed': badgeClass = 'bg-success'; break;
                    case 'cancelled': badgeClass = 'bg-danger'; break;
                }
                row += '<td><span class="badge status-badge ' + badgeClass + '">' +
                       task.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) + '</span></td>';

                // Due Date
                if (!task.due_date) {
                    row += '<td><span class="text-muted">No due date</span></td>';
                } else {
                    let dueDate = new Date(task.due_date);
                    let today = new Date();
                    today.setHours(0, 0, 0, 0);
                    dueDate.setHours(0, 0, 0, 0);

                    let isOverdue = dueDate < today && task.status !== 'completed';
                    let dateStr = new Date(task.due_date).toLocaleDateString();

                    row += '<td>' + (isOverdue ? '<span class="overdue-task">' + dateStr + '</span>' : dateStr) + '</td>';
                }

                // Actions
                let actions = '<div class="dropdown">';
                actions += '<button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
                actions += '<i class="bi bi-three-dots-vertical"></i>';
                actions += '</button>';
                actions += '<ul class="dropdown-menu">';

                if (task.status !== 'completed') {
                    actions += '<li><a class="dropdown-item complete-task" href="#" data-id="' + task.id +
                              '"><i class="bi bi-check-lg me-2"></i>Mark Complete</a></li>';
                } else {
                    actions += '<li><a class="dropdown-item incomplete-task" href="#" data-id="' + task.id +
                              '"><i class="bi bi-arrow-clockwise me-2"></i>Mark Incomplete</a></li>';
                }

                actions += '<li><a class="dropdown-item edit-task" href="#" data-id="' + task.id +
                          '"><i class="bi bi-pencil me-2"></i>Edit Task</a></li>';
                actions += '<li><hr class="dropdown-divider"></li>';
                actions += '<li><a class="dropdown-item text-danger delete-task" href="#" data-id="' + task.id +
                          '"><i class="bi bi-trash me-2"></i>Delete Task</a></li>';
                actions += '</ul>';
                actions += '</div>';
                row += '<td>' + actions + '</td>';

                row += '</tr>';
                tbody.append(row);
            });

            // Add pagination controls
            renderPagination();
        }

        // Render pagination controls
        function renderPagination() {
            let totalPages = Math.ceil(allTasks.length / itemsPerPage);

            if (totalPages <= 1) {
                $('#paginationControls').remove();
                return;
            }

            // Remove existing pagination if any
            $('#paginationControls').remove();

            let paginationHtml = '<nav id="paginationControls" aria-label="Task pagination">';
            paginationHtml += '<ul class="pagination justify-content-center mt-3">';

            // Previous button
            paginationHtml += '<li class="page-item ' + (currentPage === 1 ? 'disabled' : '') + '">';
            paginationHtml += '<a class="page-link" href="#" data-page="' + (currentPage - 1) + '">Previous</a></li>';

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                paginationHtml += '<li class="page-item ' + (currentPage === i ? 'active' : '') + '">';
                paginationHtml += '<a class="page-link" href="#" data-page="' + i + '">' + i + '</a></li>';
            }

            // Next button
            paginationHtml += '<li class="page-item ' + (currentPage === totalPages ? 'disabled' : '') + '">';
            paginationHtml += '<a class="page-link" href="#" data-page="' + (currentPage + 1) + '">Next</a></li>';

            paginationHtml += '</ul></nav>';

            $('.card-body').append(paginationHtml);

            // Add click handlers for pagination
            $('#paginationControls .page-link').on('click', function(e) {
                e.preventDefault();
                let page = parseInt($(this).data('page'));
                if (page && page !== currentPage && page > 0 && page <= totalPages) {
                    currentPage = page;
                    renderTable();
                }
            });
        }

        // Initial load
        loadTasks();
        loadStatistics();

        // Quick filter change
        $('input[name="quickFilter"]').on('change', function() {
            currentPage = 1;
            loadTasks();
        });

        // Sort change
        $('#sortBy').on('change', function() {
            currentPage = 1;
            loadTasks();
        });

        // Select all checkbox
        $('#selectAll').on('change', function() {
            $('.task-select').prop('checked', $(this).prop('checked'));
        });

        // New Task button click handler
        $('#new-task-btn').on('click', function() {
            // Redirect to create task page
            window.location.href = 'create-task.php';
        });

        // Edit Task
        $(document).on('click', '.edit-task', function(e) {
            e.preventDefault();
            let taskId = $(this).data('id');

            $.ajax({
                url: 'api/tasks.php',
                method: 'POST',
                data: { action: 'get', id: taskId },
                success: function(response) {
                    if (response.success && response.task) {
                        $('#editTaskId').val(response.task.id);
                        $('#editTaskTitle').val(response.task.title);
                        $('#editTaskDescription').val(response.task.description);
                        $('#editTaskStatus').val(response.task.status);
                        $('#editTaskPriority').val(response.task.priority);
                        $('#editTaskDueDate').val(response.task.due_date);

                        $('#editTaskModal').modal('show');
                    }
                }
            });
        });

        // Update Task Form Submit
        $('#editTaskForm').on('submit', function(e) {
            e.preventDefault();

            let submitBtn = $(this).find('button[type="submit"]');
            let spinner = submitBtn.find('.spinner-border');

            submitBtn.prop('disabled', true);
            spinner.removeClass('d-none');

            $.ajax({
                url: 'api/tasks.php',
                method: 'POST',
                data: $(this).serialize() + '&action=update',
                success: function(response) {
                    if (response.success) {
                        $('#editTaskModal').modal('hide');
                        loadTasks();
                        loadStatistics();

                        Swal.fire({
                            icon: 'success',
                            title: 'Task Updated',
                            text: 'Your task has been updated successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to update task'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An error occurred while updating the task'
                    });
                },
                complete: function() {
                    submitBtn.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            });
        });

        // Complete/Incomplete Task
        $(document).on('click', '.complete-task, .incomplete-task', function(e) {
            e.preventDefault();
            let taskId = $(this).data('id');

            $.ajax({
                url: 'api/tasks.php',
                method: 'POST',
                data: { action: 'toggle', id: taskId },
                success: function(response) {
                    if (response.success) {
                        loadTasks();
                        loadStatistics();
                    }
                }
            });
        });

        // Delete Task
        $(document).on('click', '.delete-task', function(e) {
            e.preventDefault();
            let taskId = $(this).data('id');

            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/tasks.php',
                        method: 'POST',
                        data: { action: 'delete', id: taskId },
                        success: function(response) {
                            if (response.success) {
                                loadTasks();
                                loadStatistics();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'Your task has been deleted.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        }
                    });
                }
            });
        });

        // Load Statistics
        function loadStatistics() {
            $.ajax({
                url: 'api/tasks.php',
                method: 'POST',
                data: { action: 'statistics', my_tasks_only: true },
                success: function(response) {
                    if (response.success) {
                        $('#stat-total').text(response.statistics.total || 0);
                        $('#stat-completed').text(response.statistics.completed || 0);
                        $('#stat-in-progress').text(response.statistics.in_progress || 0);
                        $('#stat-overdue').text(response.statistics.overdue || 0);
                    }
                }
            });
        }

        // Set minimum date for due date inputs
        let today = new Date().toISOString().split('T')[0];
        $('#taskDueDate, #editTaskDueDate').attr('min', today);

        // Auto-refresh statistics every 30 seconds
        setInterval(loadStatistics, 30000);
    });
    </script>
</body>
</html>