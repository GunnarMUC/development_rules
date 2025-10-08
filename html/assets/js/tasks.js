/**
 * Tasks Management JavaScript
 * Handles all task CRUD operations with jQuery and DataTables
 */

$(document).ready(function() {
    // Initialize Select2 for assign to dropdowns
    $('#taskAssignedTo, #editTaskAssignedTo').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select a team member',
        allowClear: true,
        width: '100%'
    });

    // Initialize Select2 for filter dropdowns
    $('#filter-status, #filter-priority, #filter-assignee').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Load team members for assignment dropdown
    function loadTeamMembers() {
        $.ajax({
            url: 'api/tasks.php',
            type: 'GET',
            data: { action: 'team_members' },
            success: function(response) {
                if (response.success) {
                    var options = '<option value="">-- Not Assigned --</option>';
                    var filterOptions = '<option value="">All Assignees</option>';
                    response.data.forEach(function(member) {
                        var name = member.first_name + ' ' + member.last_name;
                        if (name.trim() === '') {
                            name = member.email;
                        }
                        options += '<option value="' + member.id + '">' + name + '</option>';
                        filterOptions += '<option value="' + member.id + '">' + name + '</option>';
                    });
                    $('#taskAssignedTo, #editTaskAssignedTo').html(options).trigger('change');
                    $('#filter-assignee').html(filterOptions).trigger('change');
                }
            }
        });
    }

    // Load members on page load
    loadTeamMembers();

    // Apply filters button
    $('#apply-filters').on('click', function() {
        tasksTable.ajax.reload();
    });

    // Reset filters button
    $('#reset-filters').on('click', function() {
        $('#filter-status').val('').trigger('change');
        $('#filter-priority').val('').trigger('change');
        $('#filter-assignee').val('').trigger('change');
        tasksTable.ajax.reload();
    });

    // Auto-apply filters on change (optional)
    $('.filter-select').on('change', function() {
        tasksTable.ajax.reload();
    });

    // Initialize DataTable
    var tasksTable = $('#tasksTable').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: 'api/tasks.php',
            type: 'GET',
            data: function(d) {
                d.action = 'list';
                // Add filter parameters
                d.filter_status = $('#filter-status').val();
                d.filter_priority = $('#filter-priority').val();
                d.filter_assignee = $('#filter-assignee').val();
                console.log('DataTables request:', d);
            },
            dataSrc: function(json) {
                console.log('DataTables response:', json);
                console.log('Number of tasks received:', json.data ? json.data.length : 0);
                if (json.data && json.data.length > 0) {
                    console.log('First task:', json.data[0]);
                }
                return json.data;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTables AJAX error:', error, thrown);
                console.error('Response:', xhr.responseText);
            }
        },
        columns: [
            { data: 'id' },
            {
                data: 'title',
                render: function(data, type, row) {
                    var titleClass = row.status === 'completed' ? 'task-completed' : '';
                    return '<span class="' + titleClass + '">' + data + '</span>';
                }
            },
            {
                data: 'assigned_to',
                render: function(data, type, row) {
                    if (row.assigned_first_name || row.assigned_last_name) {
                        var name = (row.assigned_first_name || '') + ' ' + (row.assigned_last_name || '');
                        return '<span class="badge bg-info">' + name.trim() + '</span>';
                    }
                    return '<span class="text-muted">Not assigned</span>';
                }
            },
            {
                data: 'status',
                render: function(data, type, row) {
                    var badgeClass = '';
                    var badgeText = '';

                    switch(data) {
                        case 'pending':
                            badgeClass = 'bg-secondary';
                            badgeText = 'Pending';
                            break;
                        case 'in_progress':
                            badgeClass = 'bg-warning';
                            badgeText = 'In Progress';
                            break;
                        case 'completed':
                            badgeClass = 'bg-success';
                            badgeText = 'Completed';
                            break;
                        case 'cancelled':
                            badgeClass = 'bg-danger';
                            badgeText = 'Cancelled';
                            break;
                    }

                    return '<span class="badge status-badge ' + badgeClass + '">' + badgeText + '</span>';
                }
            },
            {
                data: 'priority',
                render: function(data, type, row) {
                    var priorityClass = 'priority-' + data;
                    var priorityText = data.charAt(0).toUpperCase() + data.slice(1);
                    return '<span class="' + priorityClass + '"><i class="bi bi-flag-fill"></i> ' + priorityText + '</span>';
                }
            },
            {
                data: 'due_date',
                render: function(data, type, row) {
                    if (!data) return '-';

                    var dueDate = new Date(data);
                    var today = new Date();
                    today.setHours(0, 0, 0, 0);

                    var dateStr = row.due_date_formatted || data;

                    if (dueDate < today && row.status !== 'completed') {
                        return '<span class="text-danger"><i class="bi bi-exclamation-circle"></i> ' + dateStr + '</span>';
                    } else {
                        return dateStr;
                    }
                }
            },
            {
                data: 'created_at',
                render: function(data, type, row) {
                    return row.created_at_formatted || data;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    var actions = '<div class="task-actions">';

                    // Complete/Uncomplete button
                    if (row.status === 'completed') {
                        actions += '<button class="btn btn-sm btn-outline-warning btn-uncomplete" data-id="' + row.id + '" title="Mark as Incomplete">' +
                                  '<i class="bi bi-arrow-counterclockwise"></i></button>';
                    } else {
                        actions += '<button class="btn btn-sm btn-outline-success btn-complete" data-id="' + row.id + '" title="Mark as Complete">' +
                                  '<i class="bi bi-check-lg"></i></button>';
                    }

                    // Edit button
                    actions += '<button class="btn btn-sm btn-outline-primary btn-edit" data-id="' + row.id + '" title="Edit">' +
                              '<i class="bi bi-pencil"></i></button>';

                    // Delete button
                    actions += '<button class="btn btn-sm btn-outline-danger btn-delete" data-id="' + row.id + '" title="Delete">' +
                              '<i class="bi bi-trash"></i></button>';

                    actions += '</div>';
                    return actions;
                }
            }
        ],
        order: [[5, 'desc']], // Sort by created date by default
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
        language: {
            emptyTable: "No tasks found. Create your first task to get started!"
        },
        drawCallback: function() {
            // Update statistics after table draw
            updateStatistics();
        }
    });

    // Load statistics on page load
    updateStatistics();

    // Add Task Form Submit
    $('#addTaskForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            url: 'api/tasks.php',
            type: 'POST',
            data: formData + '&action=create',
            dataType: 'json',
            beforeSend: function() {
                $('#addTaskForm button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Adding...');
            },
            success: function(response) {
                if (response.success) {
                    $('#addTaskModal').modal('hide');
                    $('#addTaskForm')[0].reset();
                    tasksTable.ajax.reload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Task added successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to add task'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while adding the task'
                });
            },
            complete: function() {
                $('#addTaskForm button[type="submit"]').prop('disabled', false).html('Add Task');
            }
        });
    });

    // Edit Task - Load Data
    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var taskId = $(this).data('id');

        if (!taskId) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Invalid task ID'
            });
            return;
        }

        $.ajax({
            url: 'api/tasks.php',
            type: 'GET',
            data: {
                action: 'get',
                id: taskId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Handle both possible response structures
                    var task = response.data || response.task;

                    if (!task) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'No task data received'
                        });
                        return;
                    }

                    // Populate the edit form
                    $('#editTaskId').val(task.id);
                    $('#editTaskTitle').val(task.title || '');
                    $('#editTaskDescription').val(task.description || '');
                    $('#editTaskPriority').val(task.priority || 'medium');
                    $('#editTaskStatus').val(task.status || 'pending');
                    $('#editTaskAssignedTo').val(task.assigned_to || '').trigger('change');
                    $('#editTaskDueDate').val(task.due_date || '');

                    // Show the modal
                    $('#editTaskModal').modal('show');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to load task'
                    });
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Failed to load task data';
                if (xhr.status === 401) {
                    errorMsg = 'Session expired. Please login again.';
                    window.location.href = 'login.php';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMsg
                });
            }
        });
    });

    // Edit Task Form Submit
    $('#editTaskForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();

        $.ajax({
            url: 'api/tasks.php',
            type: 'POST',
            data: formData + '&action=update',
            dataType: 'json',
            beforeSend: function() {
                $('#editTaskForm button[type="submit"]').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
            },
            success: function(response) {
                if (response.success) {
                    $('#editTaskModal').modal('hide');
                    tasksTable.ajax.reload();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Task updated successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to update task'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while updating the task'
                });
            },
            complete: function() {
                $('#editTaskForm button[type="submit"]').prop('disabled', false).html('Save Changes');
            }
        });
    });

    // Delete Task
    $(document).on('click', '.btn-delete', function() {
        var taskId = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'api/tasks.php',
                    type: 'POST',
                    data: {
                        action: 'delete',
                        id: taskId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            tasksTable.ajax.reload();

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: 'Task has been deleted.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: response.message || 'Failed to delete task'
                            });
                        }
                    }
                });
            }
        });
    });

    // Complete Task
    $(document).on('click', '.btn-complete', function() {
        var taskId = $(this).data('id');
        toggleTaskComplete(taskId);
    });

    // Uncomplete Task
    $(document).on('click', '.btn-uncomplete', function() {
        var taskId = $(this).data('id');
        toggleTaskComplete(taskId);
    });

    // Toggle Task Complete Status
    function toggleTaskComplete(taskId) {
        $.ajax({
            url: 'api/tasks.php',
            type: 'POST',
            data: {
                action: 'toggle',
                id: taskId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    tasksTable.ajax.reload();

                    var message = response.data.status === 'completed' ?
                                'Task marked as complete!' :
                                'Task marked as incomplete!';

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: message,
                        timer: 1000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Failed to update task'
                    });
                }
            }
        });
    }

    // Update Statistics
    function updateStatistics() {
        $.ajax({
            url: 'api/tasks.php',
            type: 'GET',
            data: {
                action: 'statistics'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var stats = response.data || response.statistics;
                    $('#stat-total').text(stats.total_tasks || 0);
                    $('#stat-completed').text(stats.completed_tasks || 0);
                    $('#stat-progress').text(stats.in_progress_tasks || 0);
                    $('#stat-overdue').text(stats.overdue_tasks || 0);
                }
            }
        });
    }

    // Set minimum date for due date to today
    var today = new Date().toISOString().split('T')[0];
    $('#taskDueDate').attr('min', today);
    $('#editTaskDueDate').attr('min', today);

    // Auto-refresh statistics every 30 seconds
    setInterval(updateStatistics, 30000);
});