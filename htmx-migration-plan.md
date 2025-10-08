# HTMX & Alpine.js Migration Plan

## Executive Summary

This document provides a comprehensive plan for migrating the SaaS application from jQuery-based interactivity to HTMX and Alpine.js. The migration will be done incrementally to minimize risk while maintaining full functionality throughout the process.

## Migration Principles

1. **Progressive Enhancement**: Keep existing functionality working during migration
2. **Incremental Changes**: Migrate one component/page at a time
3. **Server-First Approach**: Move logic to PHP where appropriate
4. **Minimal Client State**: Use Alpine.js only where necessary
5. **Testing at Each Step**: Ensure functionality before moving forward

## Phase 1: Foundation Setup (Week 1)

### 1.1 Install Core Libraries
```html
<!-- Add to header.php -->
<script src="https://unpkg.com/htmx.org@1.9.10"></script>
<script src="https://unpkg.com/alpinejs@3.13.5/dist/cdn.min.js" defer></script>

<!-- Keep Bootstrap bundle for now (includes jQuery) -->
<script src="/assets/js/bootstrap.bundle.min.js"></script>
```

### 1.2 Create HTMX Helper Functions
Create `/config/htmx.php`:
```php
<?php
function isHtmxRequest() {
    return isset($_SERVER['HTTP_HX_REQUEST']);
}

function htmxRedirect($url) {
    header('HX-Redirect: ' . $url);
}

function htmxTrigger($event, $data = null) {
    $trigger = is_array($data) ? json_encode([$event => $data]) : $event;
    header('HX-Trigger: ' . $trigger);
}

function htmxRefresh() {
    header('HX-Refresh: true');
}

function renderPartial($file, $data = []) {
    extract($data);
    include "partials/$file.php";
}
```

### 1.3 Create Partials Directory Structure
```
/html/partials/
├── tasks/
│   ├── task-row.php
│   ├── task-list.php
│   └── task-form.php
├── notifications/
│   ├── notification-badge.php
│   └── notification-list.php
├── dashboard/
│   ├── stats-widget.php
│   └── activity-feed.php
└── common/
    ├── alert.php
    └── spinner.php
```

## Phase 2: Authentication Pages Migration

### 2.1 Login Page (`login.php`)

**Current jQuery Implementation:**
```javascript
$('#loginForm').validate({
    submitHandler: function(form) {
        $.ajax({
            url: '/api/auth.php',
            type: 'POST',
            data: $(form).serialize(),
            success: function(response) {
                window.location.href = '/dashboard.php';
            }
        });
    }
});
```

**New HTMX Implementation:**
```html
<form hx-post="/api/auth.php"
      hx-target="#error-message"
      hx-swap="innerHTML"
      x-data="{ loading: false }"
      @htmx:before-request="loading = true"
      @htmx:after-request="loading = false">

    <input type="email" name="email" required
           x-model="email"
           :class="{ 'is-invalid': !email && $el.touched }">

    <input type="password" name="password" required>

    <button type="submit" :disabled="loading">
        <span x-show="!loading">Login</span>
        <span x-show="loading">Loading...</span>
    </button>

    <div id="error-message"></div>
</form>
```

**PHP Handler Update:**
```php
// api/auth.php
if (isHtmxRequest()) {
    if ($loginSuccess) {
        htmxRedirect('/dashboard.php');
    } else {
        echo '<div class="alert alert-danger">Invalid credentials</div>';
    }
} else {
    // Keep existing JSON response for compatibility
}
```

### 2.2 Registration Page (`register.php`)

**New HTMX Implementation with Alpine Validation:**
```html
<div x-data="registrationForm()">
    <form hx-post="/api/register.php"
          hx-target="#form-response"
          hx-swap="innerHTML"
          @htmx:after-request="handleResponse">

        <input type="text" name="name" required
               x-model="formData.name"
               @blur="validateName">
        <span x-show="errors.name" x-text="errors.name"></span>

        <input type="email" name="email" required
               x-model="formData.email"
               @blur="validateEmail">

        <input type="password" name="password" required
               x-model="formData.password"
               @input="checkPasswordStrength">

        <div x-show="passwordStrength"
             :class="passwordStrengthClass">
            Password strength: <span x-text="passwordStrength"></span>
        </div>

        <button type="submit" :disabled="!isValid">Register</button>
    </form>
</div>

<script>
function registrationForm() {
    return {
        formData: { name: '', email: '', password: '' },
        errors: {},
        passwordStrength: '',

        validateName() {
            this.errors.name = this.formData.name.length < 3
                ? 'Name must be at least 3 characters' : '';
        },

        validateEmail() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            this.errors.email = !emailRegex.test(this.formData.email)
                ? 'Invalid email address' : '';
        },

        checkPasswordStrength() {
            // Password strength logic
        },

        get isValid() {
            return Object.values(this.errors).every(e => !e);
        }
    }
}
</script>
```

## Phase 3: Dashboard Migration

### 3.1 Main Dashboard (`dashboard.php`)

**Create Dashboard Partials:**

`/partials/dashboard/stats-widget.php`:
```php
<div class="col-md-3" id="stats-widget-<?= $widget_id ?>"
     hx-get="/api/dashboard.php?widget=<?= $widget_id ?>"
     hx-trigger="load, every 30s">
    <div class="card">
        <div class="card-body">
            <h5><?= $title ?></h5>
            <h2><?= $value ?></h2>
            <small><?= $subtitle ?></small>
        </div>
    </div>
</div>
```

`/partials/dashboard/activity-feed.php`:
```php
<div id="activity-feed"
     hx-get="/api/activities.php?limit=10"
     hx-trigger="load, newActivity from:body"
     hx-swap="innerHTML">
    <?php foreach($activities as $activity): ?>
        <div class="activity-item">
            <img src="<?= $activity['user_avatar'] ?>" class="avatar">
            <div>
                <strong><?= $activity['user_name'] ?></strong>
                <?= $activity['description'] ?>
                <small><?= $activity['time_ago'] ?></small>
            </div>
        </div>
    <?php endforeach; ?>
</div>
```

**Chart Updates with Alpine:**
```html
<div x-data="dashboardCharts()" x-init="initCharts()">
    <canvas id="completionChart"></canvas>
    <button @click="updateChart('week')">Week</button>
    <button @click="updateChart('month')">Month</button>
</div>

<script>
function dashboardCharts() {
    return {
        chart: null,

        async initCharts() {
            const ctx = document.getElementById('completionChart');
            this.chart = new Chart(ctx, await this.getChartConfig());
        },

        async updateChart(period) {
            const response = await fetch(`/api/charts.php?period=${period}`);
            const data = await response.json();
            this.chart.data = data;
            this.chart.update();
        }
    }
}
</script>
```

## Phase 4: Task Management Migration

### 4.1 Task List Page (`tasks.php`)

**Replace DataTables with HTMX Table:**
```html
<div x-data="taskFilter()">
    <!-- Filters -->
    <select x-model="filters.status"
            @change="applyFilter">
        <option value="">All Status</option>
        <option value="pending">Pending</option>
        <option value="in_progress">In Progress</option>
        <option value="completed">Completed</option>
    </select>

    <input type="search"
           x-model="filters.search"
           @input.debounce.500ms="applyFilter"
           placeholder="Search tasks...">

    <!-- Task Table -->
    <table id="task-table"
           hx-get="/api/tasks.php"
           hx-trigger="load, taskUpdated from:body"
           hx-include="[name='filter']">
        <thead>
            <tr>
                <th hx-get="/api/tasks.php?sort=title"
                    hx-target="#task-table">Title</th>
                <th hx-get="/api/tasks.php?sort=priority"
                    hx-target="#task-table">Priority</th>
                <th hx-get="/api/tasks.php?sort=due_date"
                    hx-target="#task-table">Due Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="task-tbody">
            <!-- Loaded via HTMX -->
        </tbody>
    </table>

    <!-- Pagination -->
    <div hx-get="/api/tasks.php?page=2"
         hx-trigger="revealed"
         hx-swap="afterend">
        Load More
    </div>
</div>
```

**Task Row Partial (`/partials/tasks/task-row.php`):**
```php
<tr id="task-<?= $task['id'] ?>"
    x-data="{ editing: false, task: <?= json_encode($task) ?> }">

    <td>
        <span x-show="!editing" x-text="task.title"></span>
        <input x-show="editing" x-model="task.title"
               @keyup.enter="saveTask()"
               @keyup.escape="editing = false">
    </td>

    <td>
        <select x-show="editing" x-model="task.priority">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>
        <span x-show="!editing"
              :class="'badge bg-' + getPriorityColor(task.priority)">
            <span x-text="task.priority"></span>
        </span>
    </td>

    <td><?= $task['due_date'] ?></td>

    <td>
        <button @click="editing = !editing" x-show="!editing">Edit</button>
        <button @click="saveTask()" x-show="editing">Save</button>

        <button hx-delete="/api/tasks.php?id=<?= $task['id'] ?>"
                hx-target="closest tr"
                hx-swap="outerHTML swap:1s"
                hx-confirm="Delete this task?">
            Delete
        </button>
    </td>
</tr>
```

### 4.2 Create Task Modal

**Alpine.js Modal with HTMX Form:**
```html
<div x-data="{ showCreateModal: false }">
    <button @click="showCreateModal = true">Create Task</button>

    <div x-show="showCreateModal"
         x-transition
         class="modal-backdrop"
         @click.self="showCreateModal = false">

        <div class="modal-content">
            <h3>Create New Task</h3>

            <form hx-post="/api/tasks.php"
                  hx-target="#task-tbody"
                  hx-swap="afterbegin"
                  @htmx:after-request="showCreateModal = false">

                <input type="text" name="title" required>

                <select name="priority">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>

                <input type="date" name="due_date">

                <textarea name="description"></textarea>

                <div hx-get="/api/users.php?team=current"
                     hx-trigger="load"
                     hx-target="this">
                    <!-- Load assignee options -->
                </div>

                <button type="submit">Create Task</button>
                <button type="button" @click="showCreateModal = false">Cancel</button>
            </form>
        </div>
    </div>
</div>
```

## Phase 5: Kanban Board Migration

### 5.1 Kanban Board (`kanban.php`)

**Replace jQuery UI with Sortable.js + HTMX:**
```html
<div x-data="kanbanBoard()" x-init="initSortable()">
    <div class="kanban-board">
        <?php foreach($columns as $column): ?>
        <div class="kanban-column"
             data-column-id="<?= $column['id'] ?>">

            <div class="column-header">
                <h4><?= $column['name'] ?></h4>
                <span class="badge"><?= $column['task_count'] ?></span>
            </div>

            <div class="kanban-cards"
                 id="column-<?= $column['id'] ?>"
                 hx-post="/api/kanban.php?action=reorder"
                 hx-trigger="card-moved"
                 hx-vals='js:{cards: getColumnCards(this)}'>

                <?php foreach($column['tasks'] as $task): ?>
                    <?php include 'partials/kanban-card.php'; ?>
                <?php endforeach; ?>
            </div>

            <button hx-get="/api/kanban.php?action=new-card&column=<?= $column['id'] ?>"
                    hx-target="#column-<?= $column['id'] ?>"
                    hx-swap="beforeend">
                Add Card
            </button>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function kanbanBoard() {
    return {
        initSortable() {
            document.querySelectorAll('.kanban-cards').forEach(el => {
                new Sortable(el, {
                    group: 'kanban',
                    animation: 150,
                    onEnd: (evt) => {
                        const data = {
                            taskId: evt.item.dataset.taskId,
                            fromColumn: evt.from.dataset.columnId,
                            toColumn: evt.to.dataset.columnId,
                            newIndex: evt.newIndex
                        };

                        // Trigger HTMX request
                        htmx.ajax('POST', '/api/kanban.php?action=move', {
                            values: data,
                            target: evt.item
                        });
                    }
                });
            });
        }
    }
}
</script>
```

**Kanban Card Partial (`/partials/kanban-card.php`):**
```php
<div class="kanban-card"
     data-task-id="<?= $task['id'] ?>"
     x-data="{ expanded: false }">

    <div class="card-header">
        <span class="priority-badge priority-<?= $task['priority'] ?>">
            <?= $task['priority'] ?>
        </span>
        <button @click="expanded = !expanded">
            <i :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
        </button>
    </div>

    <h5><?= $task['title'] ?></h5>

    <div x-show="expanded" x-transition>
        <p><?= $task['description'] ?></p>

        <div class="card-meta">
            <span><i class="bi-calendar"></i> <?= $task['due_date'] ?></span>
            <span><i class="bi-person"></i> <?= $task['assignee'] ?></span>
        </div>

        <div class="card-actions">
            <button hx-get="/api/tasks.php?id=<?= $task['id'] ?>&action=edit"
                    hx-target="#modal-container"
                    hx-swap="innerHTML">
                Edit
            </button>
        </div>
    </div>

    <div class="progress">
        <div class="progress-bar" style="width: <?= $task['progress'] ?>%"></div>
    </div>
</div>
```

## Phase 6: Calendar Migration

### 6.1 Calendar View (`calendar.php`)

**Migrate to FullCalendar v6 (no jQuery) with HTMX:**
```html
<div x-data="calendarApp()" x-init="initCalendar()">
    <div id="calendar"></div>

    <!-- Event Modal -->
    <div x-show="showEventModal"
         x-transition
         class="modal">
        <form hx-post="/api/calendar.php"
              hx-target="#calendar"
              hx-swap="none"
              @htmx:after-request="eventSaved">

            <input type="text" name="title" x-model="event.title">
            <input type="datetime-local" name="start" x-model="event.start">
            <input type="datetime-local" name="end" x-model="event.end">

            <select name="type">
                <option value="task">Task Due Date</option>
                <option value="meeting">Meeting</option>
                <option value="reminder">Reminder</option>
            </select>

            <button type="submit">Save Event</button>
        </form>
    </div>
</div>

<script>
function calendarApp() {
    return {
        calendar: null,
        showEventModal: false,
        event: {},

        initCalendar() {
            const calendarEl = document.getElementById('calendar');
            this.calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: {
                    url: '/api/calendar.php',
                    method: 'GET'
                },
                dateClick: (info) => {
                    this.event = { start: info.dateStr };
                    this.showEventModal = true;
                },
                eventClick: (info) => {
                    this.editEvent(info.event);
                }
            });
            this.calendar.render();
        },

        eventSaved(event) {
            this.showEventModal = false;
            this.calendar.refetchEvents();
            htmx.trigger(document.body, 'showNotification', {
                message: 'Event saved successfully'
            });
        }
    }
}
</script>
```

## Phase 7: Notification System Migration

### 7.1 Real-time Notifications

**Server-Sent Events with HTMX:**
```html
<!-- In header.php -->
<div hx-sse="connect:/api/notifications-stream.php">
    <div id="notification-badge"
         hx-sse="swap:notificationCount">
        <i class="bi-bell"></i>
        <span class="badge">0</span>
    </div>

    <div id="notification-dropdown"
         x-data="{ open: false }"
         @click.away="open = false">

        <button @click="open = !open">
            <i class="bi-bell"></i>
        </button>

        <div x-show="open"
             x-transition
             hx-get="/api/notifications.php?unread=true"
             hx-trigger="load, notificationReceived from:body"
             hx-target="this">
            <!-- Notifications loaded here -->
        </div>
    </div>
</div>
```

**Notification Toast with Alpine:**
```html
<div x-data="notificationToast()"
     @show-notification.window="showToast($event.detail)">
    <div x-show="visible"
         x-transition
         class="toast"
         :class="'toast-' + type">
        <span x-text="message"></span>
        <button @click="visible = false">×</button>
    </div>
</div>

<script>
function notificationToast() {
    return {
        visible: false,
        message: '',
        type: 'info',

        showToast(detail) {
            this.message = detail.message;
            this.type = detail.type || 'info';
            this.visible = true;

            setTimeout(() => {
                this.visible = false;
            }, 5000);
        }
    }
}
</script>
```

## Phase 8: Search Implementation

### 8.1 Global Search

**HTMX Live Search with Alpine Results:**
```html
<div x-data="globalSearch()" @click.away="results = []">
    <input type="search"
           x-model="query"
           hx-get="/api/search.php"
           hx-trigger="input changed delay:500ms"
           hx-target="#search-results"
           hx-indicator="#search-spinner"
           placeholder="Search...">

    <span id="search-spinner" class="htmx-indicator">
        <i class="bi-arrow-repeat spin"></i>
    </span>

    <div id="search-results"
         x-show="results.length > 0"
         x-transition>
        <!-- Results rendered by server -->
    </div>
</div>
```

**Search Results Partial (`/partials/search-results.php`):**
```php
<div class="search-results">
    <?php foreach($results as $group => $items): ?>
    <div class="result-group">
        <h6><?= ucfirst($group) ?></h6>
        <?php foreach($items as $item): ?>
        <a href="<?= $item['url'] ?>"
           class="result-item"
           hx-boost="true">
            <i class="bi-<?= $item['icon'] ?>"></i>
            <span><?= $item['title'] ?></span>
            <small><?= $item['subtitle'] ?></small>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>
```

## Phase 9: Form Components Migration

### 9.1 Replace Select2 with Tom Select

```html
<div x-data="{ selectedUsers: [] }" x-init="initTomSelect()">
    <select multiple
            x-ref="userSelect"
            name="assignees[]">
        <option value="">Select users...</option>
    </select>
</div>

<script>
function initTomSelect() {
    new TomSelect(this.$refs.userSelect, {
        plugins: ['remove_button'],
        load: function(query, callback) {
            fetch(`/api/users.php?search=${query}`)
                .then(response => response.json())
                .then(callback);
        },
        render: {
            option: function(data) {
                return `<div>
                    <img src="${data.avatar}" class="avatar-sm">
                    ${data.name}
                </div>`;
            }
        }
    });
}
</script>
```

### 9.2 Date Picker Migration

```html
<input type="date"
       x-data="datePicker()"
       x-init="initPicker()"
       x-ref="dateInput">

<script>
function datePicker() {
    return {
        picker: null,
        initPicker() {
            this.picker = new Pikaday({
                field: this.$refs.dateInput,
                format: 'YYYY-MM-DD',
                onSelect: (date) => {
                    // Trigger HTMX update if needed
                    htmx.trigger(this.$refs.dateInput, 'dateSelected');
                }
            });
        }
    }
}
</script>
```

## Phase 10: Cleanup & Optimization

### 10.1 Remove jQuery Dependencies

1. **Remove jQuery Validation** - Use Alpine.js validation
2. **Remove DataTables** - Replaced with HTMX tables
3. **Remove Select2** - Replaced with Tom Select
4. **Remove jQuery UI** - Replaced with Sortable.js
5. **Keep Bootstrap Bundle** - Still needs jQuery

### 10.2 Performance Optimization

```html
<!-- Preload critical HTMX responses -->
<link rel="preload" href="/api/dashboard.php" as="fetch">

<!-- Lazy load Alpine components -->
<div x-data="lazyComponent" x-intersect="loadComponent">
    <!-- Component loads when visible -->
</div>

<!-- HTMX boost for navigation -->
<body hx-boost="true">
    <!-- All links use HTMX navigation -->
</body>
```

### 10.3 Error Handling

```javascript
// Global HTMX error handler
document.body.addEventListener('htmx:responseError', (event) => {
    Alpine.store('notifications').add({
        type: 'error',
        message: 'An error occurred. Please try again.'
    });
});

// Global Alpine error boundary
Alpine.magic('safe', () => {
    return (callback) => {
        try {
            return callback();
        } catch (error) {
            console.error('Alpine Error:', error);
            return null;
        }
    };
});
```

## Testing Strategy

### Unit Testing
- Test HTMX endpoints return correct HTML
- Test Alpine components in isolation
- Validate form submissions

### Integration Testing
- Test drag-and-drop functionality
- Test real-time updates
- Test search functionality
- Test modal interactions

### Performance Testing
- Measure page load times
- Compare bundle sizes
- Test server response times
- Monitor memory usage

## Rollback Plan

If issues arise during migration:

1. **Keep jQuery version in parallel** - Maintain both versions temporarily
2. **Feature flags** - Toggle between jQuery and HTMX versions
3. **Gradual rollout** - Test with subset of users first
4. **Version control** - Tag stable jQuery version before migration

## Success Metrics

- **Bundle size reduction**: Target 60% reduction
- **Time to Interactive**: Target 30% improvement
- **Server response time**: Keep under 200ms
- **User satisfaction**: No degradation in UX
- **Code maintainability**: Reduce JavaScript by 70%

## Timeline Summary

- **Week 1**: Foundation & Setup
- **Week 2**: Authentication Pages
- **Week 3**: Dashboard & Widgets
- **Week 4**: Task Management
- **Week 5**: Kanban Board
- **Week 6**: Calendar
- **Week 7**: Notifications & Search
- **Week 8**: Form Components
- **Week 9**: Testing & Bug Fixes
- **Week 10**: Optimization & Documentation

## Conclusion

This migration plan provides a systematic approach to modernizing the application's frontend while maintaining functionality throughout the process. The combination of HTMX and Alpine.js will result in a simpler, more maintainable, and more performant application.