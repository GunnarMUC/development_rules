    </div><!-- End of page-wrapper -->

    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-light" id="main-footer">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col text-center">
                    <span class="text-muted" id="footer-copyright">Copyright <?php echo date('Y'); ?>, Kinetic Seas Incorporated - All Rights Reserved</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Core JavaScript -->
    <!-- jQuery (kept for Bootstrap compatibility only) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery UI for drag and drop functionality (will be migrated to Sortable.js) -->
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- jQuery UI CSS for datepicker -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

    <!-- Chart.js for dashboard charts (Alpine.js compatible) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Toastr for notifications (will be migrated to Alpine Toast) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- HTMX Configuration -->
    <script>
    // Configure HTMX globally
    document.body.addEventListener('htmx:configRequest', (event) => {
        // Add CSRF token to all HTMX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (csrfToken) {
            event.detail.headers['X-CSRF-Token'] = csrfToken.content;
        }
    });

    // Global HTMX error handling
    document.body.addEventListener('htmx:responseError', (event) => {
        console.error('HTMX Error:', event.detail);
        if (event.detail.xhr.status === 401) {
            window.location.href = 'login.php?session_expired=1';
        } else {
            showNotification('An error occurred. Please try again.', 'error');
        }
    });

    // Show success messages from server
    document.body.addEventListener('htmx:afterSwap', (event) => {
        const successMsg = event.detail.xhr.getResponseHeader('X-Success-Message');
        if (successMsg) {
            showNotification(successMsg, 'success');
        }
    });
    </script>

    <!-- Main Application JavaScript -->
    <script>
    $(document).ready(function() {
        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize Bootstrap popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // Sidebar toggle for mobile
        $('#sidebar-toggle-btn').on('click', function(e) {
            e.preventDefault();
            $('#sidebar').toggleClass('show');
            $('#sidebar-backdrop').toggleClass('show');
        });

        // Close sidebar when clicking backdrop
        $('#sidebar-backdrop').on('click', function() {
            $('#sidebar').removeClass('show');
            $(this).removeClass('show');
        });

        // Sidebar collapse/expand for desktop
        $('#sidebar-collapse-btn').on('click', function(e) {
            e.preventDefault();
            $('#sidebar').toggleClass('collapsed');

            // Save state to localStorage
            if ($('#sidebar').hasClass('collapsed')) {
                localStorage.setItem('sidebarCollapsed', 'true');
            } else {
                localStorage.removeItem('sidebarCollapsed');
            }

            // Add tooltips for collapsed sidebar items
            if ($('#sidebar').hasClass('collapsed')) {
                $('.sidebar .nav-link').each(function() {
                    var text = $(this).find('.nav-text').text().trim();
                    $(this).attr('data-tooltip', text);
                });
            } else {
                $('.sidebar .nav-link').removeAttr('data-tooltip');
            }
        });

        // Restore sidebar state from localStorage
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            $('#sidebar').addClass('collapsed');
            $('.sidebar .nav-link').each(function() {
                var text = $(this).find('.nav-text').text().trim();
                $(this).attr('data-tooltip', text);
            });
        }

        // Active menu highlighting
        var currentPath = window.location.pathname.split('/').pop();
        if (currentPath === '') currentPath = 'dashboard.php';

        // Remove all active classes first
        $('.sidebar .nav-link').removeClass('active');
        $('.sidebar .submenu-link').removeClass('active');

        // Find and activate current page link
        $('.sidebar .nav-link').each(function() {
            var href = $(this).attr('href');
            if (href === currentPath) {
                $(this).addClass('active');

                // If it's a submenu item, expand parent
                var parentSubmenu = $(this).closest('.submenu');
                if (parentSubmenu.length) {
                    parentSubmenu.addClass('show');
                    parentSubmenu.prev('.nav-link').attr('aria-expanded', 'true');
                    parentSubmenu.prev('.nav-link').removeClass('collapsed');
                }
            }
        });

        $('.sidebar .submenu-link').each(function() {
            var href = $(this).attr('href');
            if (href === currentPath) {
                $(this).addClass('active');

                // Expand parent submenu
                var parentSubmenu = $(this).closest('.submenu');
                parentSubmenu.addClass('show');
                parentSubmenu.prev('.nav-link').attr('aria-expanded', 'true');
                parentSubmenu.prev('.nav-link').removeClass('collapsed');
            }
        });

        // Handle submenu toggle
        $('.sidebar .nav-link[data-bs-toggle="collapse"]').on('click', function(e) {
            if (!$('#sidebar').hasClass('collapsed')) {
                e.preventDefault();
                var target = $(this).attr('data-bs-target');
                $(target).collapse('toggle');
            }
        });

        // Prevent navigation when clicking on parent menu items with submenus
        $('.has-submenu > .nav-link').on('click', function(e) {
            if ($(this).attr('href') === '#' && !$('#sidebar').hasClass('collapsed')) {
                e.preventDefault();
            }
        });

        // Handle window resize
        $(window).on('resize', function() {
            if ($(window).width() > 768) {
                $('#sidebar').removeClass('show');
                $('#sidebar-backdrop').removeClass('show');
            }
        });

        // AJAX error handler
        $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
            console.error('AJAX Error:', thrownError);
            if (jqxhr.status === 401) {
                // Redirect to login if session expired
                window.location.href = 'login.php?session_expired=1';
            }
        });

        // Global loading indicator
        $(document).ajaxStart(function() {
            // Can add a loading spinner here
        }).ajaxStop(function() {
            // Remove loading spinner
        });

        // Form validation enhancement
        $('.needs-validation').on('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            $(this).addClass('was-validated');
        });

        // Team Selector functionality removed per request
        // Team switching code has been disabled

        // Global Search with jQuery UI Autocomplete
        if ($('#global-search-input').length) {
            $('#global-search-input').autocomplete({
                minLength: 2,
                delay: 300,
                source: function(request, response) {
                    $.ajax({
                        url: '/api/search.php',
                        dataType: 'json',
                        data: {
                            q: request.term
                        },
                        success: function(data) {
                            response($.map(data.results, function(item) {
                                return {
                                    label: item.label,
                                    value: item.label,
                                    type: item.type,
                                    description: item.description,
                                    icon: item.icon,
                                    category: item.category,
                                    badge: item.badge,
                                    badge_class: item.badge_class,
                                    url: item.url
                                };
                            }));
                        }
                    });
                },
                select: function(event, ui) {
                    if (ui.item.url) {
                        window.location.href = ui.item.url;
                    }
                    return false;
                },
                focus: function(event, ui) {
                    event.preventDefault();
                }
            }).data('ui-autocomplete')._renderItem = function(ul, item) {
            var html = '<div class="ui-menu-item-wrapper">';
            html += '<i class="' + item.icon + ' search-result-icon"></i>';
            html += '<div class="search-result-content">';
            html += '<div class="search-result-category">' + item.category + '</div>';
            html += '<div class="search-result-title">' + item.label + '</div>';
            if (item.description) {
                html += '<div class="search-result-description">' + item.description + '</div>';
            }
            html += '</div>';
            if (item.badge) {
                html += '<span class="badge bg-' + item.badge_class + ' search-result-badge">' + item.badge + '</span>';
            }
            html += '</div>';

            return $('<li>').html(html).appendTo(ul);
        };
        }

        // Toastr Configuration
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };

        // Notifications System
        var notificationCount = 0;
        var notificationsOpen = false;

        // Load notification count
        function loadNotificationCount() {
            $.ajax({
                url: '/api/notifications.php',
                method: 'GET',
                data: { action: 'get_count' },
                dataType: 'json',
                success: function(response) {
                    if (response.count > 0) {
                        notificationCount = response.count;
                        $('#notification-count').text(response.count);
                        $('#notification-badge').show();
                    } else {
                        $('#notification-badge').hide();
                    }
                }
            });
        }

        // Load notifications list
        function loadNotifications() {
            $.ajax({
                url: '/api/notifications.php',
                method: 'GET',
                data: { action: 'get_list', limit: 10 },
                dataType: 'json',
                success: function(response) {
                    var html = '';
                    if (response.notifications && response.notifications.length > 0) {
                        response.notifications.forEach(function(notification) {
                            html += '<div class="notification-item ' + (notification.is_read ? '' : 'unread') + '" data-id="' + notification.id + '">';
                            html += '<div class="notification-title">' + notification.title + '</div>';
                            if (notification.message) {
                                html += '<div class="notification-message">' + notification.message + '</div>';
                            }
                            html += '<div class="notification-time">' + notification.time_ago + '</div>';
                            html += '</div>';
                        });
                    } else {
                        html = '<div class="p-3 text-center text-muted">No notifications</div>';
                    }
                    $('#notifications-list').html(html);
                }
            });
        }

        // Toggle notifications dropdown
        $('#notifications-btn').on('click', function(e) {
            e.stopPropagation();
            notificationsOpen = !notificationsOpen;
            if (notificationsOpen) {
                $('#notifications-dropdown').addClass('show');
                loadNotifications();
            } else {
                $('#notifications-dropdown').removeClass('show');
            }
        });

        // Close notifications when clicking outside
        $(document).on('click', function() {
            if (notificationsOpen) {
                $('#notifications-dropdown').removeClass('show');
                notificationsOpen = false;
            }
        });

        // Prevent dropdown from closing when clicking inside
        $('#notifications-dropdown').on('click', function(e) {
            e.stopPropagation();
        });

        // Mark notification as read
        $(document).on('click', '.notification-item.unread', function() {
            var notificationId = $(this).data('id');
            var $item = $(this);

            $.ajax({
                url: '/api/notifications.php',
                method: 'POST',
                data: {
                    action: 'mark_read',
                    notification_id: notificationId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $item.removeClass('unread');
                        loadNotificationCount();
                    }
                }
            });

            // Navigate to link if exists
            if ($(this).data('link')) {
                window.location.href = $(this).data('link');
            }
        });

        // Mark all as read
        $('#mark-all-read').on('click', function() {
            $.ajax({
                url: '/api/notifications.php',
                method: 'POST',
                data: { action: 'mark_all_read' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('.notification-item').removeClass('unread');
                        loadNotificationCount();
                    }
                }
            });
        });

        // Check for new notifications every 30 seconds
        setInterval(function() {
            loadNotificationCount();

            // Check for new notifications and show toastr if any
            $.ajax({
                url: '/api/notifications.php',
                method: 'GET',
                data: { action: 'check_new' },
                dataType: 'json',
                success: function(response) {
                    if (response.new_notification) {
                        toastr.info(response.new_notification.message, response.new_notification.title);
                    }
                }
            });
        }, 30000);

        // Initial load
        loadNotificationCount();

        // Fix: Manually initialize all Bootstrap dropdowns
        // Bootstrap 5 should auto-initialize, but for some reason it's not working on this page
        if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
            document.querySelectorAll('[data-bs-toggle="dropdown"]').forEach(function(dropdownToggle) {
                if (!bootstrap.Dropdown.getInstance(dropdownToggle)) {
                    new bootstrap.Dropdown(dropdownToggle);
                }
            });
        }
    });

    // Utility functions
    function showNotification(message, type = 'info') {
        // Use Toastr for notifications
        switch(type) {
            case 'success':
                toastr.success(message);
                break;
            case 'danger':
            case 'error':
                toastr.error(message);
                break;
            case 'warning':
                toastr.warning(message);
                break;
            default:
                toastr.info(message);
        }
    }

    // Session timeout warning
    var sessionTimeout;
    var warningTimeout;
    var sessionDuration = 30 * 60 * 1000; // 30 minutes
    var warningDuration = 5 * 60 * 1000; // 5 minutes before timeout

    function resetSessionTimers() {
        clearTimeout(sessionTimeout);
        clearTimeout(warningTimeout);

        warningTimeout = setTimeout(function() {
            showNotification('Your session will expire in 5 minutes. Please save your work.', 'warning');
        }, sessionDuration - warningDuration);

        sessionTimeout = setTimeout(function() {
            window.location.href = 'logout.php?timeout=1';
        }, sessionDuration);
    }

    // Reset timers on user activity
    $(document).on('click keypress scroll', resetSessionTimers);
    resetSessionTimers();
    </script>

    <?php if(isset($additional_js)): ?>
    <?php echo $additional_js; ?>
    <?php endif; ?>

    <style>
    .footer {
        margin-top: auto;
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
        font-size: 0.875rem;
    }

    /* Additional responsive styles */
    @media (max-width: 576px) {
        .footer .row {
            text-align: center;
        }

        .footer .col-md-6.text-md-end {
            text-align: center !important;
            margin-top: 0.5rem;
        }
    }

    /* Loading spinner */
    .spinner-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
    </style>
</body>
</html>