<?php
/**
 * Alert Partial
 *
 * Displays a Bootstrap alert
 *
 * Variables:
 * - $type: Alert type (success, danger, warning, info, primary, secondary)
 * - $message: The alert message
 * - $dismissible: Whether the alert can be dismissed (default: true)
 */

$type = $type ?? 'info';
$message = $message ?? 'Alert message';
$dismissible = $dismissible ?? true;
?>

<div class="alert alert-<?php echo htmlspecialchars($type); ?> <?php echo $dismissible ? 'alert-dismissible fade show' : ''; ?>" role="alert">
    <?php echo htmlspecialchars($message); ?>
    <?php if ($dismissible): ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    <?php endif; ?>
</div>
