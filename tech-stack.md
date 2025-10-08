# Technology Stack Documentation - HTMX & Alpine.js Migration

## Overview

This document outlines the modernized technology stack for the SaaS Application Template, migrating from jQuery-based interactivity to HTMX and Alpine.js while maintaining the proven LAMP stack foundation. This approach prioritizes simplicity, server-side rendering, and lightweight client-side interactivity.

## Core Stack Components

### 1. Server Infrastructure

#### Operating System
- **Ubuntu Server 22.04 LTS** (preferred) or CentOS 8/Rocky Linux
- Rationale: Long-term support, extensive documentation, wide community support

#### Web Server
- **Apache 2.4.x**
- Modules required:
  - mod_rewrite (for clean URLs)
  - mod_ssl (for HTTPS)
  - mod_headers (for security headers and HTMX headers)
  - mod_deflate (for compression)
  - mod_expires (for caching)

#### Database
- **MariaDB 10.6+**
- Rationale: Drop-in MySQL replacement with better performance
- InnoDB storage engine for transactional support
- UTF8MB4 character set for full Unicode support
- Optimized for concurrent team access

#### Backend Language
- **PHP 8.1+**
- Extensions required:
  - PDO and PDO_MySQL
  - mbstring (multi-byte string support)
  - openssl (encryption)
  - fileinfo (file upload validation)
  - gd or imagick (image processing)
  - json
  - session
  - ctype
  - filter
  - zip (for exports)

## Frontend Technologies

### Core Framework
- **Bootstrap 5.3.x**
- Responsive grid system
- Pre-built components
- Utility classes for rapid development
- Modern, clean design language

### JavaScript Libraries

#### Primary Interactivity Layer
- **HTMX 1.9.x** (latest stable version)
  - Server-driven UI updates
  - AJAX without JavaScript
  - WebSocket and SSE support
  - Progressive enhancement
  - Minimal client-side state

- **Alpine.js 3.x**
  - Lightweight reactive framework
  - Component-based interactivity
  - Simple state management
  - Declarative syntax in HTML
  - Perfect companion to HTMX

#### Supporting Libraries (Minimal jQuery)
- **jQuery 3.7.1** (ONLY for Bootstrap components)
  - Bootstrap modal, dropdown, collapse support
  - Will be phased out when Bootstrap 6 removes jQuery dependency
  - Not used for custom application logic

### HTMX-Compatible Components

1. **Sortable.js** (replacing jQuery UI Sortable)
   - Drag-and-drop for Kanban boards
   - No jQuery dependency
   - Touch-friendly
   - Works with HTMX events

2. **Tom Select** (replacing Select2)
   - Enhanced select boxes
   - No jQuery dependency
   - Alpine.js integration
   - AJAX data loading via HTMX

3. **Pikaday** or **Flatpickr** (replacing jQuery Datepicker)
   - Date picker functionality
   - Lightweight
   - Alpine.js compatible
   - No dependencies

4. **Tabulator** or **Grid.js** (replacing DataTables)
   - Advanced table functionality
   - Server-side processing via HTMX
   - Alpine.js integration for state
   - Export capabilities

5. **Vanilla Calendar Pro** or **FullCalendar** (v6 - no jQuery)
   - Full-featured calendar
   - HTMX for event loading
   - Alpine.js for interactions

### Charts & Visualization
- **Chart.js 4.x**
- Lightweight and flexible
- Responsive charts
- Alpine.js integration for updates
- No jQuery dependency

### Notification System
- **Notyf** or **Alpine Toast** (replacing Toastr)
- Lightweight notifications
- No dependencies
- Alpine.js integration

## Application Architecture

### PHP Structure (HTMX-Oriented MVC)
```
project-root/
├── index.php              # Main entry point & router
├── config/
│   ├── database.php       # Database configuration
│   ├── constants.php      # Application constants
│   ├── functions.php      # Global helper functions
│   └── htmx.php          # HTMX response helpers
├── includes/
│   ├── header.php         # Common header with HTMX setup
│   ├── footer.php         # Common footer with Alpine init
│   ├── auth.php           # Authentication functions
│   ├── session.php        # Session management
│   └── csrf.php          # CSRF token handling
├── classes/
│   ├── Database.php       # PDO database wrapper
│   ├── User.php           # User model
│   ├── Team.php           # Team model
│   ├── Task.php           # Task model
│   ├── Board.php          # Kanban board model
│   ├── Event.php          # Calendar event model
│   └── Notification.php   # Notification model
├── pages/
│   ├── auth/
│   │   ├── login.php      # Login page (HTMX forms)
│   │   ├── register.php   # Registration (HTMX validation)
│   │   └── reset.php      # Password reset
│   ├── dashboard.php      # Main dashboard (HTMX partials)
│   ├── tasks.php          # Todo list view (HTMX updates)
│   ├── kanban.php         # Kanban board (HTMX + Alpine)
│   ├── calendar.php       # Calendar view (HTMX events)
│   ├── teams.php          # Team management
│   └── profile.php        # User profile
├── partials/              # HTMX partial responses
│   ├── task-row.php       # Single task row
│   ├── task-list.php      # Task list partial
│   ├── kanban-card.php    # Kanban card template
│   ├── calendar-event.php # Calendar event partial
│   ├── notification.php   # Notification item
│   └── chart-data.php     # Chart update partial
├── api/                   # HTMX endpoints
│   ├── auth.php           # Authentication endpoints
│   ├── tasks.php          # Task CRUD (returns HTML)
│   ├── kanban.php         # Kanban operations
│   ├── calendar.php       # Event management
│   ├── teams.php          # Team operations
│   ├── notifications.php  # Notification endpoints
│   ├── search.php         # Live search endpoint
│   └── upload.php         # File upload handler
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── style.css      # Custom styles
│   │   └── dark-theme.css # Dark theme
│   ├── js/
│   │   ├── htmx.min.js    # HTMX library
│   │   ├── alpine.min.js  # Alpine.js library
│   │   ├── app.js         # Main Alpine components
│   │   └── bootstrap.bundle.min.js # Bootstrap (includes jQuery)
│   └── images/
├── uploads/
│   ├── avatars/           # User avatars
│   └── attachments/       # File attachments
├── vendor/                # Composer packages
├── logs/                  # Application logs
└── .htaccess             # Apache configuration
```

### Database Schema Overview
```sql
-- Core tables
users                 # User accounts with authentication
teams                 # Team/organization structure
team_members          # User-team relationships
roles                 # System roles
user_roles            # Role assignments

-- Task Management
tasks                 # Core task data
task_assignees        # Task assignments
task_comments         # Comment thread
task_attachments      # File attachments
task_categories       # Categories/labels
task_history          # Audit trail

-- Kanban
boards                # Board definitions
board_columns         # Column configuration
board_cards           # Card positions
card_movements        # Movement history

-- Calendar
events                # Calendar events
event_attendees       # Event participants
event_reminders       # Reminder queue
recurring_patterns    # Recurrence rules

-- System
notifications         # User notifications
activity_logs         # Activity tracking
user_preferences      # User settings
sessions              # Active sessions
password_resets       # Reset tokens
email_queue          # Email processing
```

## HTMX Implementation Patterns

### Form Submission with HTMX
```html
<form hx-post="/api/tasks.php"
      hx-target="#task-list"
      hx-swap="afterbegin"
      hx-on::after-request="this.reset()">
    <input type="text" name="title" required>
    <button type="submit">Add Task</button>
</form>
```

### Live Search
```html
<input type="search"
       name="search"
       hx-get="/api/search.php"
       hx-trigger="input changed delay:500ms, search"
       hx-target="#search-results"
       placeholder="Search...">
```

### Infinite Scroll
```html
<div id="task-list"
     hx-get="/api/tasks.php?page=2"
     hx-trigger="revealed"
     hx-swap="afterend">
    <!-- Tasks loaded here -->
</div>
```

### Real-time Updates with SSE
```html
<div hx-sse="connect:/api/notifications.php">
    <div id="notification-count"
         hx-sse="swap:notification">
    </div>
</div>
```

## Alpine.js Implementation Patterns

### Component State Management
```html
<div x-data="taskManager()" x-init="loadTasks()">
    <template x-for="task in tasks" :key="task.id">
        <div class="task-item">
            <span x-text="task.title"></span>
            <button @click="deleteTask(task.id)">Delete</button>
        </div>
    </template>
</div>

<script>
function taskManager() {
    return {
        tasks: [],
        async loadTasks() {
            // Fetch tasks via HTMX or directly
        },
        async deleteTask(id) {
            // Delete via HTMX trigger
        }
    }
}
</script>
```

### Drag and Drop with Alpine
```html
<div x-data="kanbanBoard()">
    <div class="kanban-column"
         @drop="onDrop($event, 'todo')"
         @dragover.prevent>
        <div draggable="true"
             @dragstart="onDragStart($event, task)">
            <!-- Card content -->
        </div>
    </div>
</div>
```

### Modal Management
```html
<div x-data="{ showModal: false }">
    <button @click="showModal = true">Open Modal</button>

    <div x-show="showModal"
         x-transition
         class="modal">
        <div @click.away="showModal = false">
            <!-- Modal content with HTMX form -->
        </div>
    </div>
</div>
```

## Security Implementation

### HTMX Security Headers
```php
// HTMX CSRF protection
header('X-CSRF-Token: ' . $_SESSION['csrf_token']);

// Check for HTMX request
if (isset($_SERVER['HTTP_HX_REQUEST'])) {
    // Return partial response
}

// Trigger client-side events
header('HX-Trigger: {"notification": "Task created"}');
```

### Alpine.js Security
```javascript
// Sanitize user input in Alpine components
Alpine.data('secureComponent', () => ({
    sanitize(input) {
        const div = document.createElement('div');
        div.textContent = input;
        return div.innerHTML;
    }
}));
```

## Migration Strategy from jQuery

### Phase 1: Parallel Implementation
1. Keep existing jQuery functionality
2. Add HTMX attributes to forms and links
3. Introduce Alpine.js for new components
4. Test thoroughly

### Phase 2: Progressive Replacement
1. Replace jQuery AJAX with HTMX
2. Convert jQuery plugins to Alpine components
3. Migrate event handlers to Alpine
4. Update DataTables to Grid.js/Tabulator

### Phase 3: Complete Migration
1. Remove jQuery except for Bootstrap
2. Optimize HTMX responses
3. Consolidate Alpine components
4. Performance testing

## Performance Optimization

### HTMX Optimization
```html
<!-- Preload content -->
<div hx-get="/api/data.php" hx-trigger="load">

<!-- Debounce requests -->
<input hx-get="/search" hx-trigger="keyup changed delay:500ms">

<!-- Cache responses -->
<div hx-get="/api/static.php" hx-boost="true">
```

### Alpine.js Optimization
```javascript
// Lazy load Alpine components
document.addEventListener('alpine:init', () => {
    Alpine.data('heavyComponent', () =>
        import('./components/heavy.js')
    );
});
```

## Development Tools

### Version Control
```bash
# .gitignore
/config/database.php
/uploads/
/vendor/
/logs/
.env
```

### Local Development
```php
// config/database.php (local)
define('DB_HOST', 'localhost:8889');
define('DB_NAME', 'saas_template');
define('DB_USER', 'root');
define('DB_PASS', 'root');
```

### Composer Dependencies
```json
{
    "require": {
        "phpmailer/phpmailer": "^6.8",
        "vlucas/phpdotenv": "^5.5",
        "monolog/monolog": "^3.0"
    }
}
```

## Monitoring & Maintenance

### HTMX Request Logging
```php
if (isset($_SERVER['HTTP_HX_REQUEST'])) {
    error_log('HTMX Request: ' . $_SERVER['REQUEST_URI']);
    error_log('HX-Trigger: ' . ($_SERVER['HTTP_HX_TRIGGER'] ?? 'none'));
}
```

### Alpine.js Error Tracking
```javascript
Alpine.store('errors', {
    items: [],
    add(error) {
        this.items.push(error);
        console.error('Alpine Error:', error);
    }
});
```

## Cost Comparison

### Development Impact
- **Learning Curve**: Minimal - HTMX is HTML attributes, Alpine is simpler than jQuery
- **Development Speed**: Faster - less JavaScript to write
- **Maintenance**: Easier - server-side logic, minimal client state

### Performance Benefits
- **Bundle Size**:
  - jQuery: ~90KB
  - HTMX + Alpine: ~35KB combined
- **Time to Interactive**: Faster with less JavaScript
- **Server Load**: Slightly higher but offset by caching

## Migration Plan

### Week 1-2: Foundation
- Install HTMX and Alpine.js
- Create helper functions for HTMX responses
- Set up partial templates structure
- Implement basic HTMX forms

### Week 3-4: Core Features
- Convert task CRUD to HTMX
- Implement Alpine.js task components
- Migrate search to HTMX live search
- Convert modals to Alpine.js

### Week 5: Kanban Board
- Replace jQuery UI with Sortable.js
- Implement HTMX updates for card moves
- Alpine.js for card state management

### Week 6: Calendar
- Migrate to FullCalendar v6 (no jQuery)
- HTMX for event loading
- Alpine.js for event modals

### Week 7: Tables & Forms
- Replace DataTables with Grid.js
- Convert Select2 to Tom Select
- Implement Alpine validation

### Week 8: Polish & Testing
- Remove unnecessary jQuery code
- Optimize HTMX responses
- Performance testing
- Documentation updates

## Conclusion

This technology stack modernizes the frontend while maintaining the reliable LAMP backend:

- **Simplicity**: HTMX reduces JavaScript complexity
- **Performance**: Smaller bundle size, faster interactions
- **Maintainability**: Server-side rendering, minimal client state
- **Progressive Enhancement**: Works without JavaScript
- **Modern UX**: Smooth interactions with Alpine.js

The migration from jQuery to HTMX/Alpine.js provides a more maintainable, performant, and modern application while keeping the simplicity that makes LAMP stack development efficient.