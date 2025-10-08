<?php
/**
 * Task Row Partial
 *
 * Displays a single task row in the tasks table
 *
 * Variables:
 * - $task: Task data array with keys: id, title, status, priority, due_date, assigned_to, assigned_first_name, assigned_last_name, created_at
 */

// Priority badge classes
$priority_classes = [
    'low' => 'text-secondary',
    'medium' => 'text-info',
    'high' => 'text-warning',
    'critical' => 'text-danger'
];

// Status badge classes
$status_badges = [
    'pending' => ['class' => 'bg-secondary', 'text' => 'Pending'],
    'in_progress' => ['class' => 'bg-warning', 'text' => 'In Progress'],
    'completed' => ['class' => 'bg-success', 'text' => 'Completed'],
    'cancelled' => ['class' => 'bg-danger', 'text' => 'Cancelled']
];

$task_id = $task['id'];
$title_class = $task['status'] === 'completed' ? 'task-completed' : '';
$priority_class = $priority_classes[$task['priority']] ?? 'text-secondary';
$status_badge = $status_badges[$task['status']] ?? ['class' => 'bg-secondary', 'text' => ucfirst($task['status'])];

// Assigned to name
$assigned_name = '';
if (!empty($task['assigned_first_name']) || !empty($task['assigned_last_name'])) {
    $assigned_name = trim(($task['assigned_first_name'] ?? '') . ' ' . ($task['assigned_last_name'] ?? ''));
}

// Format dates
$due_date_formatted = !empty($task['due_date']) ? date('M d, Y', strtotime($task['due_date'])) : '-';
$created_at_formatted = !empty($task['created_at']) ? date('M d, Y', strtotime($task['created_at'])) : '-';
?>

<tr id="task-row-<?php echo $task_id; ?>"
    class="task-row"
    x-data="{ editing: false }">

    <td><?php echo htmlspecialchars($task_id); ?></td>

    <td>
        <span class="<?php echo $title_class; ?>">
            <?php echo htmlspecialchars($task['title']); ?>
        </span>
    </td>

    <td>
        <?php if ($assigned_name): ?>
            <span class="badge bg-info"><?php echo htmlspecialchars($assigned_name); ?></span>
        <?php else: ?>
            <span class="text-muted">Not assigned</span>
        <?php endif; ?>
    </td>

    <td>
        <span class="badge status-badge <?php echo $status_badge['class']; ?>">
            <?php echo $status_badge['text']; ?>
        </span>
    </td>

    <td>
        <span class="<?php echo $priority_class; ?>">
            <i class="bi bi-flag-fill"></i> <?php echo ucfirst($task['priority']); ?>
        </span>
    </td>

    <td><?php echo $due_date_formatted; ?></td>

    <td><?php echo $created_at_formatted; ?></td>

    <td class="task-actions">
        <button class="btn btn-sm btn-outline-primary"
                hx-get="api/tasks.php?action=get&id=<?php echo $task_id; ?>"
                hx-target="#editTaskModalContent"
                hx-swap="innerHTML"
                @click="$dispatch('open-edit-modal', { id: <?php echo $task_id; ?> })"
                title="Edit">
            <i class="bi bi-pencil"></i>
        </button>

        <button class="btn btn-sm btn-outline-danger"
                @click="if(confirm('Are you sure you want to delete this task?')) {
                    htmx.ajax('DELETE', 'api/tasks.php?action=delete&id=<?php echo $task_id; ?>', {
                        target: '#task-row-<?php echo $task_id; ?>',
                        swap: 'outerHTML swap:1s'
                    }).then(() => {
                        htmx.trigger('#tasksTable', 'taskDeleted');
                    });
                }"
                title="Delete">
            <i class="bi bi-trash"></i>
        </button>

        <button class="btn btn-sm btn-outline-success"
                hx-post="api/tasks.php?action=toggle-complete&id=<?php echo $task_id; ?>"
                hx-target="#task-row-<?php echo $task_id; ?>"
                hx-swap="outerHTML"
                title="Toggle Complete">
            <i class="bi bi-check-circle"></i>
        </button>
    </td>
</tr>
