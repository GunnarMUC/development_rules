<?php
/**
 * Spinner Partial
 *
 * Displays a loading spinner
 *
 * Variables:
 * - $size: Spinner size (sm, md, lg) - default: md
 * - $text: Loading text - default: "Loading..."
 * - $centered: Center the spinner - default: false
 */

$size = $size ?? 'md';
$text = $text ?? 'Loading...';
$centered = $centered ?? false;
$spinnerClass = $size === 'sm' ? 'spinner-border-sm' : '';
?>

<div class="<?php echo $centered ? 'text-center py-4' : ''; ?>">
    <div class="spinner-border <?php echo $spinnerClass; ?>" role="status">
        <span class="visually-hidden"><?php echo htmlspecialchars($text); ?></span>
    </div>
    <?php if ($text && $size !== 'sm'): ?>
    <div class="mt-2 text-muted"><?php echo htmlspecialchars($text); ?></div>
    <?php endif; ?>
</div>
