<?php
/**
 * Task List Partial
 *
 * Displays a list of tasks with pagination
 *
 * Variables:
 * - $tasks: Array of task data
 * - $total: Total number of tasks
 * - $page: Current page number
 * - $limit: Tasks per page
 */

$page = $page ?? 1;
$limit = $limit ?? 25;
$total_pages = ceil($total / $limit);
?>

<?php if (empty($tasks)): ?>
    <tr>
        <td colspan="8" class="text-center py-5">
            <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">No tasks found. Create your first task to get started!</p>
        </td>
    </tr>
<?php else: ?>
    <?php foreach ($tasks as $task): ?>
        <?php include __DIR__ . '/task-row.php'; ?>
    <?php endforeach; ?>

    <?php if ($total_pages > 1): ?>
    <tr>
        <td colspan="8">
            <nav aria-label="Tasks pagination">
                <ul class="pagination justify-content-center mb-0">
                    <!-- Previous button -->
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <button class="page-link"
                                <?php if ($page > 1): ?>
                                hx-get="api/tasks.php?action=list_html&page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>"
                                hx-target="#tasks-tbody"
                                hx-swap="innerHTML"
                                hx-include="[name='filter_status'], [name='filter_priority'], [name='filter_assignee']"
                                <?php endif; ?>
                                <?php echo $page <= 1 ? 'disabled' : ''; ?>>
                            Previous
                        </button>
                    </li>

                    <!-- Page numbers -->
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);

                    for ($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <button class="page-link"
                                    hx-get="api/tasks.php?action=list_html&page=<?php echo $i; ?>&limit=<?php echo $limit; ?>"
                                    hx-target="#tasks-tbody"
                                    hx-swap="innerHTML"
                                    hx-include="[name='filter_status'], [name='filter_priority'], [name='filter_assignee']">
                                <?php echo $i; ?>
                            </button>
                        </li>
                    <?php endfor; ?>

                    <!-- Next button -->
                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                        <button class="page-link"
                                <?php if ($page < $total_pages): ?>
                                hx-get="api/tasks.php?action=list_html&page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>"
                                hx-target="#tasks-tbody"
                                hx-swap="innerHTML"
                                hx-include="[name='filter_status'], [name='filter_priority'], [name='filter_assignee']"
                                <?php endif; ?>
                                <?php echo $page >= $total_pages ? 'disabled' : ''; ?>>
                            Next
                        </button>
                    </li>
                </ul>
            </nav>
            <p class="text-center text-muted small mt-2">
                Showing <?php echo (($page - 1) * $limit) + 1; ?> to <?php echo min($page * $limit, $total); ?> of <?php echo $total; ?> tasks
            </p>
        </td>
    </tr>
    <?php endif; ?>
<?php endif; ?>
