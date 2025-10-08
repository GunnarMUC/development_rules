<?php
// Sidebar component with jQuery collapsible navigation menu
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Define menu structure
$menu_items = [
    [
        'id' => 'dashboard',
        'title' => 'Dashboard',
        'icon' => 'bi-speedometer2',
        'link' => 'dashboard.php',
        'badge' => null
    ],
    [
        'id' => 'tasks',
        'title' => 'Tasks',
        'icon' => 'bi-list-task',
        'link' => '#',
        'badge' => ['text' => '12', 'class' => 'bg-primary'],
        'submenu' => [
            ['id' => 'all-tasks', 'title' => 'All Tasks', 'link' => 'tasks.php'],
            ['id' => 'my-tasks', 'title' => 'My Tasks', 'link' => 'my-tasks.php'],
            ['id' => 'create-task', 'title' => 'Create Task', 'link' => 'create-task.php']
        ]
    ],
    [
        'id' => 'kanban',
        'title' => 'Kanban Board',
        'icon' => 'bi-kanban',
        'link' => 'kanban.php',
        'badge' => null
    ],
    [
        'id' => 'calendar',
        'title' => 'Calendar',
        'icon' => 'bi-calendar3',
        'link' => 'calendar.php',
        'badge' => null
    ],
    [
        'id' => 'teams',
        'title' => 'Teams',
        'icon' => 'bi-people',
        'link' => '#',
        'submenu' => [
            ['id' => 'my-teams', 'title' => 'My Teams', 'link' => 'my-teams.php'],
            ['id' => 'team-members', 'title' => 'Team Members', 'link' => 'team-members.php'],
            ['id' => 'manage-teams', 'title' => 'Manage Teams', 'link' => 'manage-teams.php']
        ]
    ],
    [
        'id' => 'reports',
        'title' => 'Reports',
        'icon' => 'bi-graph-up',
        'link' => 'reports.php',
        'badge' => null
    ]
];

// Check if user has admin role for admin menu
$is_admin = isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['admin', 'super_admin']);
$is_super_admin = isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'super_admin';
?>

<!-- Sidebar Column -->
<div class="col-lg-2">
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
    <div class="sidebar-header" id="sidebar-header">
        <h5 class="sidebar-title">Navigation</h5>
    </div>

    <ul class="sidebar-nav" id="sidebar-nav">
        <?php foreach($menu_items as $item): ?>
        <li class="nav-item<?php echo isset($item['submenu']) ? ' has-submenu' : ''; ?>" id="nav-<?php echo $item['id']; ?>">
            <a
                class="nav-link<?php echo $current_page === basename($item['link'], '.php') ? ' active' : ''; ?><?php echo isset($item['submenu']) ? ' collapsed' : ''; ?>"
                href="<?php echo htmlspecialchars($item['link']); ?>"
                <?php if(isset($item['submenu'])): ?>
                data-bs-toggle="collapse"
                data-bs-target="#submenu-<?php echo $item['id']; ?>"
                aria-expanded="false"
                <?php endif; ?>
                id="link-<?php echo $item['id']; ?>"
            >
                <i class="bi <?php echo $item['icon']; ?>"></i>
                <span class="nav-text"><?php echo htmlspecialchars($item['title']); ?></span>
                <?php if(isset($item['badge']) && $item['badge']): ?>
                <span class="badge <?php echo $item['badge']['class']; ?> ms-auto">
                    <?php echo htmlspecialchars($item['badge']['text']); ?>
                </span>
                <?php endif; ?>
                <?php if(isset($item['submenu'])): ?>
                <i class="bi bi-chevron-down ms-auto submenu-arrow"></i>
                <?php endif; ?>
            </a>

            <?php if(isset($item['submenu'])): ?>
            <ul class="collapse submenu" id="submenu-<?php echo $item['id']; ?>">
                <?php foreach($item['submenu'] as $subitem): ?>
                <li>
                    <a
                        class="submenu-link<?php echo $current_page === basename($subitem['link'], '.php') ? ' active' : ''; ?>"
                        href="<?php echo htmlspecialchars($subitem['link']); ?>"
                        id="sublink-<?php echo $subitem['id']; ?>"
                    >
                        <?php echo htmlspecialchars($subitem['title']); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>

        <?php if($is_admin): ?>
        <li class="nav-divider"></li>
        <li class="nav-header">Administration</li>
        <li class="nav-item" id="nav-users">
            <a class="nav-link" href="admin-users.php" id="link-admin-users">
                <i class="bi bi-person-gear"></i>
                <span class="nav-text">User Management</span>
            </a>
        </li>
        <li class="nav-item" id="nav-system">
            <a class="nav-link" href="admin-settings.php" id="link-admin-settings">
                <i class="bi bi-sliders"></i>
                <span class="nav-text">System Settings</span>
            </a>
        </li>
        <?php if($is_super_admin): ?>
        <li class="nav-item" id="nav-global-settings">
            <a class="nav-link" href="global-settings.php" id="link-global-settings">
                <i class="bi bi-gear-wide-connected"></i>
                <span class="nav-text">Global Settings</span>
            </a>
        </li>
        <?php endif; ?>
        <?php endif; ?>
    </ul>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer" id="sidebar-footer">
        <button class="btn btn-link sidebar-collapse-btn" id="sidebar-collapse-btn">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>
</nav>
</div>

<style>
/* Sidebar integrated into page flow - no longer floating */
.sidebar {
    width: var(--sidebar-width);
    background: #f8f9fa;
    border-right: 1px solid #dee2e6;
    transition: width 0.3s ease;
    position: relative;
    overflow-x: hidden;
    /* Remove fixed height to allow natural scrolling with content */
    min-height: 100vh;
}

.sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
}

.sidebar-header {
    padding: 1.5rem 1rem 1rem;
    border-bottom: 1px solid #dee2e6;
}

.sidebar-title {
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6c757d;
    margin: 0;
    transition: opacity 0.3s ease;
}

.sidebar.collapsed .sidebar-title {
    opacity: 0;
}

.sidebar-nav {
    list-style: none;
    padding: 1rem 0;
    margin: 0;
}

.nav-item {
    margin-bottom: 2px;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #495057;
    text-decoration: none;
    transition: all 0.2s ease;
    position: relative;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}

.nav-link:hover {
    background-color: #e9ecef;
    color: #212529;
}

.nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.nav-link.active:hover {
    color: white;
}

.nav-link i {
    font-size: 1.2rem;
    width: 24px;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.nav-text {
    flex: 1;
    transition: opacity 0.3s ease;
}

.sidebar.collapsed .nav-text {
    opacity: 0;
    width: 0;
    overflow: hidden;
}

.submenu-arrow {
    transition: transform 0.3s ease;
    margin-left: auto !important;
    margin-right: 0 !important;
}

.nav-link[aria-expanded="true"] .submenu-arrow {
    transform: rotate(180deg);
}

.sidebar.collapsed .submenu-arrow {
    display: none;
}

.submenu {
    list-style: none;
    padding: 0;
    margin: 0;
    background: rgba(0, 0, 0, 0.03);
}

.submenu-link {
    display: block;
    padding: 0.5rem 1rem 0.5rem 3.5rem;
    color: #6c757d;
    text-decoration: none;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.submenu-link:hover {
    color: #495057;
    background-color: rgba(0, 0, 0, 0.03);
}

.submenu-link.active {
    color: #667eea;
    font-weight: 500;
}

.sidebar.collapsed .submenu {
    display: none;
}

.nav-divider {
    height: 1px;
    background-color: #dee2e6;
    margin: 1rem 0;
}

.nav-header {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6c757d;
}

.sidebar.collapsed .nav-header {
    opacity: 0;
}

.sidebar-footer {
    /* Changed from absolute to relative positioning for page flow */
    position: relative;
    margin-top: auto;
    padding: 1rem;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
}

.sidebar-collapse-btn {
    width: 100%;
    text-align: center;
    color: #6c757d;
    padding: 0.5rem;
}

.sidebar-collapse-btn:hover {
    color: #495057;
}

.sidebar.collapsed .sidebar-collapse-btn i {
    transform: rotate(180deg);
}

.badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.sidebar.collapsed .badge {
    display: none;
}

/* Mobile Overlay */
@media (max-width: 768px) {
    .sidebar {
        /* Keep mobile as overlay for better UX on small screens */
        position: fixed;
        top: 56px;
        left: -100%;
        z-index: 1030;
        height: calc(100vh - 56px);
        transition: left 0.3s ease;
    }

    .sidebar.show {
        left: 0;
    }

    .sidebar-backdrop {
        display: none;
        position: fixed;
        top: 56px;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1029;
    }

    .sidebar-backdrop.show {
        display: block;
    }
}

/* Tooltips for collapsed sidebar */
.sidebar.collapsed .nav-link {
    position: relative;
}

.sidebar.collapsed .nav-link:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    left: calc(var(--sidebar-collapsed-width) + 10px);
    top: 50%;
    transform: translateY(-50%);
    background: #212529;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    white-space: nowrap;
    z-index: 1050;
}
</style>