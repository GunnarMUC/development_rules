<?php
session_start();
require_once 'includes/auth.php';

// Check if user is logged in
require_login();

$pageTitle = 'Completed Tasks';
$currentPage = 'completed-tasks';
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

        .filter-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .completion-date {
            color: #28a745;
            font-size: 0.875rem;
        }

        .stat-card {
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
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
                    <h1 class="h2">Completed Tasks</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-danger" id="clear-completed-btn">
                                <i class="bi bi-trash"></i> Clear All Completed
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Task Statistics -->
                <div id="tasksStats" class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-success">
                            <div class="card-body">
                                <h5 class="card-title text-success">Total Completed</h5>
                                <p class="card-text display-6" id="stat-total-completed">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-info">
                            <div class="card-body">
                                <h5 class="card-title text-info">This Week</h5>
                                <p class="card-text display-6" id="stat-week-completed">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-warning">
                            <div class="card-body">
                                <h5 class="card-title text-warning">This Month</h5>
                                <p class="card-text display-6" id="stat-month-completed">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-primary">
                            <div class="card-body">
                                <h5 class="card-title text-primary">Completion Rate</h5>
                                <p class="card-text display-6" id="stat-completion-rate">0%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Options -->
                <div class="filter-card">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="btn-group" role="group" aria-label="Time filters">
                                <input type="radio" class="btn-check" name="timeFilter" id="filter-all" value="all" checked>
                                <label class="btn btn-outline-primary" for="filter-all">All Time</label>

                                <input type="radio" class="btn-check" name="timeFilter" id="filter-today" value="today">
                                <label class="btn btn-outline-primary" for="filter-today">Today</label>

                                <input type="radio" class="btn-check" name="timeFilter" id="filter-week" value="week">
                                <label class="btn btn-outline-primary" for="filter-week">This Week</label>

                                <input type="radio" class="btn-check" name="timeFilter" id="filter-month" value="month">
                                <label class="btn btn-outline-primary" for="filter-month">This Month</label>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <select id="sortBy" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="completed_date">Sort by Completion Date</option>
                                <option value="priority">Sort by Priority</option>
                                <option value="title">Sort by Title</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Tasks Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="completedTasksTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="30"><input type="checkbox" id="selectAll"></th>
                                        <th>Task</th>
                                        <th>Priority</th>
                                        <th>Completed Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="completedTasksTableBody">
                                    <!-- Table rows will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
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

        // Load completed tasks from API
        function loadCompletedTasks() {
            let timeFilter = $('input[name="timeFilter"]:checked').val();
            let sortBy = $('#sortBy').val();

            $.ajax({
                url: 'api/tasks.php',
                method: 'POST',
                data: {
                    action: 'list',
                    filter_status: 'completed',
                    time_filter: timeFilter !== 'all' ? timeFilter : null,
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
                    $('#completedTasksTableBody').html('<tr><td colspan="5" class="text-center">Error loading completed tasks</td></tr>');
                }
            });
        }

        // Render table with tasks
        function renderTable() {
            let tbody = $('#completedTasksTableBody');
            tbody.empty();

            if (allTasks.length === 0) {
                tbody.html('<tr><td colspan="5" class="text-center">No completed tasks found</td></tr>');
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
                let title = task.title;
                if (task.description) {
                    title += '<br><small class="text-muted">' + task.description.substring(0, 50) + '...</small>';
                }
                // Add assigned user if available
                if (task.assigned_to_name) {
                    title += '<br><small class="text-info"><i class="bi bi-person-fill"></i> ' + task.assigned_to_name + '</small>';
                }
                row += '<td>' + title + '</td>';

                // Priority
                let icon = '<i class="bi bi-circle-fill priority-' + task.priority + ' me-1"></i>';
                row += '<td>' + icon + task.priority.charAt(0).toUpperCase() + task.priority.slice(1) + '</td>';

                // Completed Date
                let completedDate = task.updated_at ? new Date(task.updated_at).toLocaleDateString() : 'N/A';
                row += '<td><span class="completion-date">' + completedDate + '</span></td>';

                // Actions
                let actions = '<div class="dropdown">';
                actions += '<button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
                actions += '<i class="bi bi-three-dots-vertical"></i>';
                actions += '</button>';
                actions += '<ul class="dropdown-menu">';
                actions += '<li><a class="dropdown-item reopen-task" href="#" data-id="' + task.id +
                          '"><i class="bi bi-arrow-clockwise me-2"></i>Reopen Task</a></li>';
                actions += '<li><hr class="dropdown-divider"></li>';
                actions += '<li><a class="dropdown-item text-danger delete-task" href="#" data-id="' + task.id +
                          '"><i class="bi bi-trash me-2"></i>Delete Permanently</a></li>';
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
        loadCompletedTasks();
        loadStatistics();

        // Time filter change
        $('input[name="timeFilter"]').on('change', function() {
            currentPage = 1;
            loadCompletedTasks();
        });

        // Sort change
        $('#sortBy').on('change', function() {
            currentPage = 1;
            loadCompletedTasks();
        });

        // Select all checkbox
        $('#selectAll').on('change', function() {
            $('.task-select').prop('checked', $(this).prop('checked'));
        });

        // Reopen Task
        $(document).on('click', '.reopen-task', function(e) {
            e.preventDefault();
            let taskId = $(this).data('id');

            $.ajax({
                url: 'api/tasks.php',
                method: 'POST',
                data: {
                    action: 'update',
                    id: taskId,
                    status: 'pending'
                },
                success: function(response) {
                    if (response.success) {
                        loadCompletedTasks();
                        loadStatistics();

                        Swal.fire({
                            icon: 'success',
                            title: 'Task Reopened',
                            text: 'The task has been moved back to pending status.',
                            timer: 2000,
                            showConfirmButton: false
                        });
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
                text: "This will permanently delete the task. You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it permanently!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/tasks.php',
                        method: 'POST',
                        data: { action: 'delete', id: taskId },
                        success: function(response) {
                            if (response.success) {
                                loadCompletedTasks();
                                loadStatistics();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: 'The task has been permanently deleted.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        }
                    });
                }
            });
        });

        // Clear All Completed
        $('#clear-completed-btn').on('click', function() {
            Swal.fire({
                title: 'Clear All Completed Tasks?',
                text: "This will permanently delete all completed tasks. You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, clear all!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'api/tasks.php',
                        method: 'POST',
                        data: { action: 'clear_completed' },
                        success: function(response) {
                            if (response.success) {
                                loadCompletedTasks();
                                loadStatistics();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Cleared!',
                                    text: 'All completed tasks have been deleted.',
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
                data: { action: 'completed_statistics' },
                success: function(response) {
                    if (response.success && response.statistics) {
                        $('#stat-total-completed').text(response.statistics.total_completed || 0);
                        $('#stat-week-completed').text(response.statistics.week_completed || 0);
                        $('#stat-month-completed').text(response.statistics.month_completed || 0);

                        let rate = response.statistics.completion_rate || 0;
                        $('#stat-completion-rate').text(rate.toFixed(1) + '%');
                    }
                }
            });
        }

        // Auto-refresh statistics every 30 seconds
        setInterval(loadStatistics, 30000);
    });
    </script>
</body>
</html>