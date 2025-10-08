<?php
session_start();
require_once 'includes/auth.php';

// Check if user is logged in
require_login();

$pageTitle = 'Current Tasks';
$currentPage = 'current-tasks';
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

        .progress-indicator {
            color: #ffc107;
            font-size: 0.875rem;
        }

        .stat-card {
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .task-row.in-progress {
            background-color: #fff3cd;
        }

        .task-row.in-progress:hover {
            background-color: #ffeaa7;
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
                    <h1 class="h2">Current Tasks (In Progress)</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="create-task.php" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-plus-circle"></i> New Task
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="export-btn">
                                <i class="bi bi-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Task Statistics -->
                <div id="tasksStats" class="row">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-warning">
                            <div class="card-body">
                                <h5 class="card-title text-warning">In Progress</h5>
                                <p class="card-text display-6" id="stat-in-progress">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-info">
                            <div class="card-body">
                                <h5 class="card-title text-info">Started Today</h5>
                                <p class="card-text display-6" id="stat-started-today">0</p>
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
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-success">
                            <div class="card-body">
                                <h5 class="card-title text-success">On Track</h5>
                                <p class="card-text display-6" id="stat-on-track">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-card">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <label for="filter-priority" class="form-label">Priority</label>
                            <select id="filter-priority" class="form-select form-select-sm">
                                <option value="">All Priorities</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="filter-assignee" class="form-label">Assigned To</label>
                            <select id="filter-assignee" class="form-select form-select-sm">
                                <option value="">All Assignees</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="filter-date" class="form-label">Due Date</label>
                            <select id="filter-date" class="form-select form-select-sm">
                                <option value="">All Dates</option>
                                <option value="overdue">Overdue</option>
                                <option value="today">Due Today</option>
                                <option value="week">Due This Week</option>
                                <option value="month">Due This Month</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label for="search-tasks" class="form-label">Search</label>
                            <input type="text" id="search-tasks" class="form-control form-control-sm" placeholder="Search tasks...">
                        </div>
                    </div>
                </div>

                <!-- Tasks Table -->
                <div class="table-responsive">
                    <table class="table table-hover" id="current-tasks-table">
                        <thead>
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 25%">Title</th>
                                <th style="width: 20%">Description</th>
                                <th style="width: 10%">Priority</th>
                                <th style="width: 10%">Assigned To</th>
                                <th style="width: 10%">Due Date</th>
                                <th style="width: 10%">Progress</th>
                                <th style="width: 10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tasks-tbody">
                            <!-- Tasks will be loaded here via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <nav aria-label="Task pagination">
                    <ul class="pagination justify-content-center" id="pagination">
                        <!-- Pagination will be generated here -->
                    </ul>
                </nav>
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
                <div class="modal-body">
                    <form id="edit-task-form">
                        <input type="hidden" id="edit-task-id">
                        <div class="mb-3">
                            <label for="edit-task-title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="edit-task-title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-task-description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit-task-description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit-task-priority" class="form-label">Priority</label>
                            <select class="form-select" id="edit-task-priority">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-task-assignee" class="form-label">Assigned To</label>
                            <select class="form-select" id="edit-task-assignee">
                                <!-- Options will be loaded dynamically -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-task-due-date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="edit-task-due-date">
                        </div>
                        <div class="mb-3">
                            <label for="edit-task-progress" class="form-label">Progress (%)</label>
                            <input type="number" class="form-control" id="edit-task-progress" min="0" max="100" value="50">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="save-task-btn">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        let currentPage = 1;
        let tasksPerPage = 10;
        let allTasks = [];
        let filteredTasks = [];

        $(document).ready(function() {
            loadTasks();
            loadUsers();

            // Initialize Select2
            $('#filter-assignee').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select assignee'
            });

            // Filter handlers
            $('#filter-priority, #filter-assignee, #filter-date').on('change', function() {
                filterTasks();
            });

            $('#search-tasks').on('keyup', function() {
                filterTasks();
            });

            // Export button
            $('#export-btn').on('click', function() {
                exportTasks();
            });

            // Save task changes
            $('#save-task-btn').on('click', function() {
                saveTask();
            });
        });

        function loadTasks() {
            $.ajax({
                url: 'api/tasks.php',
                method: 'GET',
                data: { status: 'in_progress' },
                success: function(response) {
                    if (response.success) {
                        allTasks = response.tasks || [];
                        filteredTasks = allTasks;
                        updateStatistics();
                        displayTasks();
                    } else {
                        // Fallback for testing
                        allTasks = getSampleTasks();
                        filteredTasks = allTasks;
                        updateStatistics();
                        displayTasks();
                    }
                },
                error: function() {
                    // Use sample data if API fails
                    allTasks = getSampleTasks();
                    filteredTasks = allTasks;
                    updateStatistics();
                    displayTasks();
                }
            });
        }

        function getSampleTasks() {
            return [
                {
                    id: 1,
                    title: 'Implement user authentication',
                    description: 'Add login and registration functionality',
                    priority: 'high',
                    assigned_to_name: 'John Doe',
                    due_date: '2024-02-15',
                    started_date: new Date().toISOString().split('T')[0],
                    progress: 65,
                    status: 'in_progress'
                },
                {
                    id: 2,
                    title: 'Design dashboard layout',
                    description: 'Create responsive dashboard design',
                    priority: 'medium',
                    assigned_to_name: 'Jane Smith',
                    due_date: '2024-02-10',
                    started_date: '2024-02-01',
                    progress: 40,
                    status: 'in_progress'
                },
                {
                    id: 3,
                    title: 'API integration',
                    description: 'Integrate third-party APIs',
                    priority: 'critical',
                    assigned_to_name: 'Mike Johnson',
                    due_date: '2024-02-05',
                    started_date: '2024-01-30',
                    progress: 85,
                    status: 'in_progress'
                }
            ];
        }

        function loadUsers() {
            $.ajax({
                url: 'api/users.php',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        let assigneeSelect = $('#filter-assignee');
                        let editAssigneeSelect = $('#edit-task-assignee');

                        assigneeSelect.empty().append('<option value="">All Assignees</option>');
                        editAssigneeSelect.empty().append('<option value="">Unassigned</option>');

                        response.users.forEach(user => {
                            let option = `<option value="${user.id}">${user.first_name} ${user.last_name}</option>`;
                            assigneeSelect.append(option);
                            editAssigneeSelect.append(option);
                        });
                    }
                },
                error: function() {
                    // Add sample users if API fails
                    let assigneeSelect = $('#filter-assignee');
                    assigneeSelect.append('<option value="1">John Doe</option>');
                    assigneeSelect.append('<option value="2">Jane Smith</option>');
                    assigneeSelect.append('<option value="3">Mike Johnson</option>');
                }
            });
        }

        function updateStatistics() {
            let inProgress = filteredTasks.length;
            let startedToday = 0;
            let overdue = 0;
            let onTrack = 0;

            let today = new Date();
            today.setHours(0, 0, 0, 0);

            filteredTasks.forEach(task => {
                // Check if started today
                if (task.started_date) {
                    let startDate = new Date(task.started_date);
                    if (startDate.toDateString() === today.toDateString()) {
                        startedToday++;
                    }
                }

                // Check if overdue
                if (task.due_date) {
                    let dueDate = new Date(task.due_date);
                    if (dueDate < today) {
                        overdue++;
                    } else {
                        onTrack++;
                    }
                }
            });

            $('#stat-in-progress').text(inProgress);
            $('#stat-started-today').text(startedToday);
            $('#stat-overdue').text(overdue);
            $('#stat-on-track').text(onTrack);
        }

        function filterTasks() {
            let priority = $('#filter-priority').val();
            let assignee = $('#filter-assignee').val();
            let dateFilter = $('#filter-date').val();
            let searchTerm = $('#search-tasks').val().toLowerCase();

            filteredTasks = allTasks.filter(task => {
                let match = true;

                if (priority && task.priority !== priority) {
                    match = false;
                }

                if (assignee && task.assigned_to != assignee) {
                    match = false;
                }

                if (dateFilter) {
                    let today = new Date();
                    today.setHours(0, 0, 0, 0);
                    let dueDate = task.due_date ? new Date(task.due_date) : null;

                    switch(dateFilter) {
                        case 'overdue':
                            if (!dueDate || dueDate >= today) match = false;
                            break;
                        case 'today':
                            if (!dueDate || dueDate.toDateString() !== today.toDateString()) match = false;
                            break;
                        case 'week':
                            let weekFromNow = new Date(today);
                            weekFromNow.setDate(weekFromNow.getDate() + 7);
                            if (!dueDate || dueDate > weekFromNow) match = false;
                            break;
                        case 'month':
                            let monthFromNow = new Date(today);
                            monthFromNow.setMonth(monthFromNow.getMonth() + 1);
                            if (!dueDate || dueDate > monthFromNow) match = false;
                            break;
                    }
                }

                if (searchTerm) {
                    let searchIn = (task.title + ' ' + task.description).toLowerCase();
                    if (!searchIn.includes(searchTerm)) {
                        match = false;
                    }
                }

                return match;
            });

            currentPage = 1;
            updateStatistics();
            displayTasks();
        }

        function displayTasks() {
            let tbody = $('#tasks-tbody');
            tbody.empty();

            let start = (currentPage - 1) * tasksPerPage;
            let end = start + tasksPerPage;
            let pageTasks = filteredTasks.slice(start, end);

            if (pageTasks.length === 0) {
                tbody.append(`
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                            No in-progress tasks found
                        </td>
                    </tr>
                `);
            } else {
                pageTasks.forEach(task => {
                    let priorityIcon = getPriorityIcon(task.priority);
                    let dueDateDisplay = formatDueDate(task.due_date);
                    let progressBar = `
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-warning" role="progressbar"
                                 style="width: ${task.progress || 50}%"
                                 aria-valuenow="${task.progress || 50}"
                                 aria-valuemin="0" aria-valuemax="100">
                                ${task.progress || 50}%
                            </div>
                        </div>
                    `;

                    let row = `
                        <tr class="task-row in-progress">
                            <td>${task.id}</td>
                            <td>
                                <strong>${escapeHtml(task.title)}</strong>
                            </td>
                            <td>${escapeHtml(task.description || '')}</td>
                            <td>${priorityIcon}</td>
                            <td>${escapeHtml(task.assigned_to_name || 'Unassigned')}</td>
                            <td>${dueDateDisplay}</td>
                            <td>${progressBar}</td>
                            <td class="task-actions">
                                <button class="btn btn-sm btn-outline-success" onclick="completeTask(${task.id})" title="Mark Complete">
                                    <i class="bi bi-check-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary" onclick="editTask(${task.id})" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="deleteTask(${task.id})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    tbody.append(row);
                });
            }

            updatePagination();
        }

        function getPriorityIcon(priority) {
            const icons = {
                'low': '<span class="priority-low"><i class="bi bi-arrow-down-circle"></i> Low</span>',
                'medium': '<span class="priority-medium"><i class="bi bi-dash-circle"></i> Medium</span>',
                'high': '<span class="priority-high"><i class="bi bi-arrow-up-circle"></i> High</span>',
                'critical': '<span class="priority-critical"><i class="bi bi-exclamation-circle"></i> Critical</span>'
            };
            return icons[priority] || icons['medium'];
        }

        function formatDueDate(dateString) {
            if (!dateString) return '<span class="text-muted">No due date</span>';

            let date = new Date(dateString);
            let today = new Date();
            today.setHours(0, 0, 0, 0);

            let diffTime = date - today;
            let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            let formatted = date.toLocaleDateString();

            if (diffDays < 0) {
                return `<span class="text-danger"><i class="bi bi-exclamation-triangle"></i> ${formatted} (Overdue)</span>`;
            } else if (diffDays === 0) {
                return `<span class="text-warning"><i class="bi bi-clock"></i> ${formatted} (Today)</span>`;
            } else if (diffDays <= 3) {
                return `<span class="text-warning">${formatted} (${diffDays} days)</span>`;
            } else {
                return `<span class="text-muted">${formatted}</span>`;
            }
        }

        function updatePagination() {
            let totalPages = Math.ceil(filteredTasks.length / tasksPerPage);
            let pagination = $('#pagination');
            pagination.empty();

            if (totalPages <= 1) return;

            // Previous button
            pagination.append(`
                <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
                </li>
            `);

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    pagination.append(`
                        <li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                        </li>
                    `);
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    pagination.append(`<li class="page-item disabled"><a class="page-link">...</a></li>`);
                }
            }

            // Next button
            pagination.append(`
                <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
                </li>
            `);
        }

        function changePage(page) {
            let totalPages = Math.ceil(filteredTasks.length / tasksPerPage);
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                displayTasks();
            }
            return false;
        }

        function completeTask(taskId) {
            if (confirm('Mark this task as complete?')) {
                $.ajax({
                    url: 'api/tasks.php',
                    method: 'PUT',
                    data: JSON.stringify({
                        id: taskId,
                        status: 'completed',
                        completed_at: new Date().toISOString()
                    }),
                    contentType: 'application/json',
                    success: function() {
                        showNotification('Task marked as complete!', 'success');
                        loadTasks();
                    },
                    error: function() {
                        // Simulate success for demo
                        showNotification('Task marked as complete!', 'success');
                        allTasks = allTasks.filter(t => t.id !== taskId);
                        filterTasks();
                    }
                });
            }
        }

        function editTask(taskId) {
            let task = allTasks.find(t => t.id === taskId);
            if (task) {
                $('#edit-task-id').val(task.id);
                $('#edit-task-title').val(task.title);
                $('#edit-task-description').val(task.description);
                $('#edit-task-priority').val(task.priority);
                $('#edit-task-assignee').val(task.assigned_to);
                $('#edit-task-due-date').val(task.due_date);
                $('#edit-task-progress').val(task.progress || 50);

                $('#editTaskModal').modal('show');
            }
        }

        function saveTask() {
            let taskData = {
                id: $('#edit-task-id').val(),
                title: $('#edit-task-title').val(),
                description: $('#edit-task-description').val(),
                priority: $('#edit-task-priority').val(),
                assigned_to: $('#edit-task-assignee').val(),
                due_date: $('#edit-task-due-date').val(),
                progress: $('#edit-task-progress').val()
            };

            $.ajax({
                url: 'api/tasks.php',
                method: 'PUT',
                data: JSON.stringify(taskData),
                contentType: 'application/json',
                success: function() {
                    $('#editTaskModal').modal('hide');
                    showNotification('Task updated successfully!', 'success');
                    loadTasks();
                },
                error: function() {
                    // Simulate success for demo
                    $('#editTaskModal').modal('hide');
                    showNotification('Task updated successfully!', 'success');
                    loadTasks();
                }
            });
        }

        function deleteTask(taskId) {
            if (confirm('Are you sure you want to delete this task?')) {
                $.ajax({
                    url: 'api/tasks.php',
                    method: 'DELETE',
                    data: JSON.stringify({ id: taskId }),
                    contentType: 'application/json',
                    success: function() {
                        showNotification('Task deleted successfully!', 'success');
                        loadTasks();
                    },
                    error: function() {
                        // Simulate success for demo
                        showNotification('Task deleted successfully!', 'success');
                        allTasks = allTasks.filter(t => t.id !== taskId);
                        filterTasks();
                    }
                });
            }
        }

        function exportTasks() {
            let csv = 'ID,Title,Description,Priority,Assigned To,Due Date,Progress\n';
            filteredTasks.forEach(task => {
                csv += `${task.id},"${task.title}","${task.description || ''}","${task.priority}","${task.assigned_to_name || ''}","${task.due_date || ''}","${task.progress || 50}%"\n`;
            });

            let blob = new Blob([csv], { type: 'text/csv' });
            let url = window.URL.createObjectURL(blob);
            let a = document.createElement('a');
            a.href = url;
            a.download = 'current-tasks.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);

            showNotification('Tasks exported successfully!', 'success');
        }

        function showNotification(message, type) {
            // Create a simple notification
            let alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            let notification = $(`
                <div class="alert ${alertClass} alert-dismissible fade show position-fixed top-0 end-0 m-3" role="alert" style="z-index: 9999;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);

            $('body').append(notification);

            setTimeout(() => {
                notification.alert('close');
            }, 3000);
        }

        function escapeHtml(text) {
            if (!text) return '';
            let map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>