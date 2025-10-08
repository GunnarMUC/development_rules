<?php
session_start();
require_once 'includes/auth.php';

// Check if user is logged in
require_login();

$pageTitle = 'Kanban Board';
$currentPage = 'kanban';
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
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        .kanban-board {
            padding: 1rem 0;
        }

        .kanban-column {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1rem;
            min-height: 500px;
            margin-bottom: 1rem;
        }

        .kanban-column-header {
            font-weight: bold;
            margin-bottom: 1rem;
            padding: 0.5rem;
            background: white;
            border-radius: 0.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .kanban-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
            cursor: move;
            transition: transform 0.2s;
        }

        .kanban-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .kanban-card-title {
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }

        .kanban-card-meta {
            font-size: 0.8rem;
            color: #6c757d;
        }

        .kanban-card.dragging {
            opacity: 0.5;
        }

        .kanban-column.drag-over {
            background: #e9ecef;
        }

        .priority-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.25rem;
        }

        .priority-low { background-color: #6c757d; }
        .priority-medium { background-color: #17a2b8; }
        .priority-high { background-color: #ffc107; }
        .priority-critical { background-color: #dc3545; }

        .task-tag {
            display: inline-block;
            padding: 0.125rem 0.375rem;
            background: #e9ecef;
            border-radius: 0.125rem;
            font-size: 0.75rem;
            margin-right: 0.25rem;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <main class="col-12 px-md-4" style="margin-top: 20px;">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2" id="page-title"><?php echo $pageTitle; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0" id="page-actions">
                        <button class="btn btn-sm btn-outline-secondary me-2" onclick="filterTasks()">
                            <i class="bi bi-funnel"></i> Filter
                        </button>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                            <i class="bi bi-plus-circle"></i> New Task
                        </button>
                    </div>
                </div>

                <div class="kanban-board row" id="kanban-board">
                    <!-- To Do Column -->
                    <div class="col-lg-3" id="todo-column">
                        <div class="kanban-column" data-status="todo">
                            <div class="kanban-column-header">
                                <span><i class="bi bi-circle"></i> To Do</span>
                                <span class="badge bg-secondary" id="todo-count">0</span>
                            </div>
                            <div class="kanban-cards" id="todo-cards"></div>
                        </div>
                    </div>

                    <!-- In Progress Column -->
                    <div class="col-lg-3" id="progress-column">
                        <div class="kanban-column" data-status="in_progress">
                            <div class="kanban-column-header">
                                <span><i class="bi bi-arrow-clockwise"></i> In Progress</span>
                                <span class="badge bg-primary" id="progress-count">0</span>
                            </div>
                            <div class="kanban-cards" id="progress-cards"></div>
                        </div>
                    </div>

                    <!-- Review Column -->
                    <div class="col-lg-3" id="review-column">
                        <div class="kanban-column" data-status="review">
                            <div class="kanban-column-header">
                                <span><i class="bi bi-eye"></i> Review</span>
                                <span class="badge bg-warning" id="review-count">0</span>
                            </div>
                            <div class="kanban-cards" id="review-cards"></div>
                        </div>
                    </div>

                    <!-- Done Column -->
                    <div class="col-lg-3" id="done-column">
                        <div class="kanban-column" data-status="done">
                            <div class="kanban-column-header">
                                <span><i class="bi bi-check-circle"></i> Done</span>
                                <span class="badge bg-success" id="done-count">0</span>
                            </div>
                            <div class="kanban-cards" id="done-cards"></div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addTaskForm">
                        <div class="mb-3">
                            <label for="taskTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="taskTitle" required>
                        </div>
                        <div class="mb-3">
                            <label for="taskDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="taskDescription" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="taskPriority" class="form-label">Priority</label>
                            <select class="form-select" id="taskPriority">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="taskDueDate" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="taskDueDate">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="addTask()">Add Task</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        let tasks = [];

        // Initialize the Kanban board
        $(document).ready(function() {
            loadTasks();
            initDragAndDrop();
        });

        // Load tasks from database
        function loadTasks() {
            console.log('Loading tasks from API...');
            $.ajax({
                url: 'api/tasks.php',
                method: 'GET',
                data: { action: 'kanban' },
                dataType: 'json',
                success: function(response) {
                    console.log('API Response received:', response);
                    if (response && response.success) {
                        tasks = response.tasks || [];
                        console.log('Number of tasks loaded:', tasks.length);
                        if (tasks.length > 0) {
                            console.log('First task sample:', tasks[0]);
                        }
                        renderTasks();
                    } else {
                        console.error('API returned failure:', response ? response.message : 'Unknown error');
                        tasks = [];
                        renderTasks();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX request failed:');
                    console.error('  Status:', status);
                    console.error('  Error:', error);
                    console.error('  HTTP Status Code:', xhr.status);
                    console.error('  Response Text:', xhr.responseText);

                    // Try to parse error response
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        console.error('  Parsed error:', errorResponse);
                    } catch (e) {
                        console.error('  Could not parse error response as JSON');
                    }

                    // Show empty board if loading fails
                    tasks = [];
                    renderTasks();
                }
            });
        }

        // Render tasks on the Kanban board
        function renderTasks() {
            console.log('Rendering tasks, total count:', tasks.length);
            console.log('Tasks array:', JSON.stringify(tasks, null, 2));

            // Clear all columns
            $('.kanban-cards').empty();

            // Count tasks per status
            let statusCounts = {
                todo: 0,
                in_progress: 0,
                review: 0,
                done: 0
            };

            // Check if we have any tasks
            if (!tasks || tasks.length === 0) {
                console.log('No tasks to render');
                // Update counts to 0
                $('#todo-count').text(0);
                $('#progress-count').text(0);
                $('#review-count').text(0);
                $('#done-count').text(0);
                return;
            }

            // Render each task
            tasks.forEach(task => {
                console.log('Processing task:', task);
                const card = createTaskCard(task);
                console.log('Created card HTML for task', task.id);

                switch(task.status) {
                    case 'todo':
                        $('#todo-cards').append(card);
                        statusCounts.todo++;
                        break;
                    case 'in_progress':
                        $('#progress-cards').append(card);
                        statusCounts.in_progress++;
                        break;
                    case 'review':
                        $('#review-cards').append(card);
                        statusCounts.review++;
                        break;
                    case 'done':
                        $('#done-cards').append(card);
                        statusCounts.done++;
                        break;
                    default:
                        console.warn('Unknown task status:', task.status, 'defaulting to todo');
                        // Default to todo column
                        $('#todo-cards').append(card);
                        statusCounts.todo++;
                }
            });

            console.log('Status counts:', statusCounts);

            // Update column counts
            $('#todo-count').text(statusCounts.todo);
            $('#progress-count').text(statusCounts.in_progress);
            $('#review-count').text(statusCounts.review);
            $('#done-count').text(statusCounts.done);
        }

        // Create a task card element
        function createTaskCard(task) {
            const tags = task.tags && task.tags.length > 0 ? task.tags.map(tag => `<span class="task-tag">${tag}</span>`).join('') : '';

            return `
                <div class="kanban-card" draggable="true" data-task-id="${task.id}">
                    <div class="kanban-card-title">
                        <span class="priority-dot priority-${task.priority}"></span>
                        ${task.title}
                    </div>
                    <div class="kanban-card-meta">
                        ${tags}
                        ${task.due_date ? `<div class="mt-1"><i class="bi bi-calendar"></i> ${formatDate(task.due_date)}</div>` : ''}
                        ${task.assigned_name ? `<div class="mt-1"><i class="bi bi-person"></i> ${task.assigned_name}</div>` : ''}
                    </div>
                </div>
            `;
        }

        // Initialize drag and drop functionality
        function initDragAndDrop() {
            let draggedCard = null;

            // Add event listeners to cards
            $(document).on('dragstart', '.kanban-card', function(e) {
                draggedCard = this;
                $(this).addClass('dragging');
                e.originalEvent.dataTransfer.effectAllowed = 'move';
            });

            $(document).on('dragend', '.kanban-card', function() {
                $(this).removeClass('dragging');
                draggedCard = null;
            });

            // Add event listeners to columns
            $('.kanban-column').on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('drag-over');
            });

            $('.kanban-column').on('dragleave', function() {
                $(this).removeClass('drag-over');
            });

            $('.kanban-column').on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('drag-over');

                if (draggedCard) {
                    const newStatus = $(this).data('status');
                    const taskId = $(draggedCard).data('task-id');

                    // Update task status
                    const task = tasks.find(t => t.id === taskId);
                    if (task) {
                        // Update task status in database
                        $.ajax({
                            url: 'api/tasks.php',
                            method: 'POST',
                            data: {
                                action: 'update_status',
                                id: taskId,
                                status: newStatus
                            },
                            success: function(response) {
                                if (response.success) {
                                    task.status = newStatus;
                                    renderTasks();
                                    console.log(`Task ${taskId} moved to ${newStatus}`);
                                } else {
                                    console.error('Failed to update task status:', response.message);
                                    // Reload tasks to reset UI
                                    loadTasks();
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Failed to update task status:', error);
                                // Reload tasks to reset UI
                                loadTasks();
                            }
                        });
                    }
                }
            });
        }

        // Add new task
        function addTask() {
            const title = $('#taskTitle').val();
            const description = $('#taskDescription').val();
            const priority = $('#taskPriority').val();
            const dueDate = $('#taskDueDate').val();

            if (title) {
                $.ajax({
                    url: 'api/tasks.php',
                    method: 'POST',
                    data: {
                        action: 'create',
                        title: title,
                        description: description,
                        status: 'pending',  // Will be mapped to 'todo' in kanban
                        priority: priority,
                        due_date: dueDate
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Task creation response:', response);
                        if (response && response.success) {
                            console.log('Task created successfully, reloading tasks...');
                            // Reload tasks to show the new one
                            loadTasks();

                            // Close modal and reset form
                            $('#addTaskModal').modal('hide');
                            $('#addTaskForm')[0].reset();
                        } else {
                            console.error('Failed to add task:', response);
                            alert('Failed to add task: ' + (response ? response.message : 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Failed to add task: ' + error);
                    }
                });
            }
        }

        // Format date for display
        function formatDate(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const options = { month: 'short', day: 'numeric', year: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }

        // Filter tasks (placeholder)
        function filterTasks() {
            alert('Filter functionality coming soon!');
        }
    </script>
</body>
</html>