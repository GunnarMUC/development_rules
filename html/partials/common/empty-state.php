<?php
/**
 * Empty State Partial
 *
 * Displays an empty state message
 *
 * Variables:
 * - $icon: Bootstrap icon class - default: 'bi-inbox'
 * - $title: Empty state title
 * - $message: Empty state message
 * - $action_text: Optional action button text
 * - $action_url: Optional action button URL/HTMX endpoint
 */

$icon = $icon ?? 'bi-inbox';
$title = $title ?? 'Nothing here yet';
$message = $message ?? 'There are no items to display';
?>

<div class="text-center py-5">
    <i class="bi <?php echo htmlspecialchars($icon); ?> text-muted" style="font-size: 4rem;"></i>
    <h4 class="mt-3 text-muted"><?php echo htmlspecialchars($title); ?></h4>
    <p class="text-muted"><?php echo htmlspecialchars($message); ?></p>

    <?php if (isset($action_text) && isset($action_url)): ?>
    <a href="<?php echo htmlspecialchars($action_url); ?>" class="btn btn-primary mt-2">
        <?php echo htmlspecialchars($action_text); ?>
    </a>
    <?php endif; ?>
</div>
