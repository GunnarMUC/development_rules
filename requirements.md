# SaaS Application Template - Complete Documentation Package

---

# Document 1: Requirements Document
## Todo Management, Kanban Board & Calendar System

## Project Overview

A production-ready SaaS application template built on the LAMP stack with jQuery and Bootstrap 5, designed to accelerate the development of team collaboration and task management applications. This template provides essential SaaS functionality including user authentication, role-based access control, team management, and three core productivity modules: Todo Lists, Kanban Boards, and Calendar with appointments.

## Executive Summary

### Purpose
Provide a reusable SaaS template that enables developers to:
- Rapidly deploy team collaboration applications using proven PHP/jQuery architecture
- Implement secure user authentication and authorization with PHP sessions
- Build task management systems with multiple views (List, Kanban, Calendar)
- Enable team-based workflows and collaboration
- Create scalable multi-team architectures
- Customize and extend for specific business needs

### Key Features
- Secure PHP-based authentication system
- Role-based access control (User, Group Admin, Global Admin)
- Team management and collaboration
- Todo list with jQuery-powered interactions
- Kanban board with jQuery UI drag-and-drop
- Calendar with jQuery datepicker integration
- Interactive dashboard with Chart.js
- Responsive Bootstrap 5 design
- AJAX updates using jQuery

## User Roles & Permissions

### 1. Global Administrator
**Primary Users**: System administrators, platform owners

**Permissions**:
- Full system access and control
- Manage all users across all teams
- Create and delete teams
- Assign Group Admins
- Access all team data and tasks
- System configuration and settings
- View global analytics and reports
- Manage user roles and permissions
- Access audit logs

**Restrictions**:
- None - complete system access

### 2. Group Administrator
**Primary Users**: Team leaders, department managers

**Permissions**:
- Manage team members (add/remove users)
- Create and assign tasks to team members
- Manage team-specific settings
- View all team tasks and progress
- Edit/delete any task within the team
- Create team projects and categories
- Export team data and reports
- Manage team calendar events

**Restrictions**:
- Limited to assigned teams only
- Cannot create new teams
- Cannot assign Global Admin role
- Cannot access other teams' data

### 3. Standard User
**Primary Users**: Team members, individual contributors

**Permissions**:
- Create personal tasks
- View and edit assigned tasks
- Update task status and progress
- Add comments and attachments
- View team calendar
- Create personal appointments
- Access team Kanban board
- View team dashboard

**Restrictions**:
- Cannot add/remove team members
- Cannot delete other users' tasks (unless assigned)
- Limited to team-assigned projects
- Cannot modify team settings

## Functional Requirements

### 1. Authentication & User Management

#### 1.1 User Registration & Authentication
- **Registration Features**
  - Email-based registration with PHP validation
  - Email verification using PHP mail functions
  - Secure password requirements (PHP password_hash)
  - Profile setup wizard with jQuery steps
  - Team invitation system via email

- **Login System**
  - PHP session-based authentication
  - Remember me functionality (secure cookies)
  - Password reset via email (PHPMailer)
  - Session management with PHP
  - Failed login attempt tracking
  - Account lockout after failed attempts

#### 1.2 User Profile Management
- **Profile Features**
  - Avatar upload with PHP file handling
  - Display name
  - Email address
  - Timezone settings
  - Notification preferences (stored in database)
  - Theme preferences (light/dark) via jQuery

### 2. Team Management

#### 2.1 Team Structure
- **Team Features**
  - Create multiple teams (PHP/MySQL)
  - Team naming and description
  - Team member invitation via email (PHPMailer)
  - Member role assignment
  - Team visibility settings

#### 2.2 Team Collaboration
- **Collaboration Features**
  - Shared task lists (jQuery DataTables)
  - Task assignment to members
  - Team activity feed (jQuery AJAX polling)
  - @mentions in comments (jQuery autocomplete)
  - Team-wide announcements

### 3. Todo List Module

#### 3.1 Task Management
- **Task Features**
  - CRUD operations via PHP/AJAX
  - Task title and description
  - Priority levels (Low, Medium, High, Critical)
  - Due date assignment (jQuery datepicker)
  - Task categories/labels (Select2 jQuery plugin)
  - Task status tracking
  - Percentage complete (jQuery UI slider)
  - Subtask support

#### 3.2 Task Organization
- **Organization Features**
  - Personal vs Team tasks
  - Filter by status, priority, assignee (jQuery)
  - Sort functionality (jQuery DataTables)
  - Search functionality (jQuery AJAX)
  - Bulk actions (jQuery checkboxes)
  - Task templates (PHP)

#### 3.3 Task Assignment
- **Assignment Features**
  - Assign to team members (Select2)
  - Multiple assignees support
  - Task delegation
  - Reassignment workflow
  - Assignment notifications (PHP/email)

### 4. Kanban Board Module

#### 4.1 Board Structure
- **Kanban Features**
  - Default columns (PHP-generated)
  - Custom column creation (jQuery modal)
  - Column ordering (jQuery UI sortable)
  - Work-in-progress limits (PHP validation)
  - Swimlanes by category/assignee

#### 4.2 Card Management
- **Card Features**
  - jQuery UI drag-and-drop between columns
  - Quick edit with jQuery inline editing
  - Card details modal (Bootstrap modal)
  - Color coding by priority (CSS classes)
  - Due date indicators
  - Assignee avatars
  - Progress indicators (jQuery progress bar)

#### 4.3 Board Views
- **View Options**
  - Team board view
  - Personal board view
  - Filter by assignee (jQuery)
  - Filter by label/category
  - Completed items archive

### 5. Calendar Module

#### 5.1 Calendar Features
- **Core Functionality**
  - FullCalendar jQuery plugin integration
  - Month, Week, Day views
  - Task due dates display
  - Appointment creation (jQuery modal)
  - Recurring events (PHP logic)
  - All-day events
  - Event reminders (PHP cron)

#### 5.2 Appointment Management
- **Appointment Features**
  - Create appointments with jQuery forms
  - Start and end times (jQuery timepicker)
  - Location field
  - Attendee management (Select2)
  - Color coding by type
  - Appointment categories

#### 5.3 Calendar Integration
- **Integration Features**
  - Automatic task due date display
  - Drag-and-drop rescheduling (FullCalendar)
  - Quick task creation from calendar
  - Team calendar overlay
  - Personal calendar filter

### 6. Dashboard & Analytics

#### 6.1 Dashboard Widgets
- **Key Metrics**
  - Tasks completed this week (PHP queries)
  - Overdue tasks count
  - Team productivity chart (Chart.js)
  - Task distribution by status
  - Upcoming deadlines
  - Recent activity feed (jQuery AJAX)

#### 6.2 Progress Charts
- **Visualization Options**
  - Task completion trend (Chart.js line)
  - Status distribution (Chart.js pie)
  - Team member workload (Chart.js bar)
  - Priority breakdown (Chart.js doughnut)
  - Burndown chart for projects
  - Calendar heatmap (jQuery plugin)

#### 6.3 Customization
- **Dashboard Features**
  - Widget arrangement (jQuery UI sortable)
  - Show/hide widgets (jQuery toggle)
  - Date range filters (jQuery datepicker)
  - Export chart data (PHP CSV export)
  - Print dashboard view

### 7. Notifications & Communication

#### 7.1 Notification System
- **Notification Types**
  - Task assignments
  - Due date reminders
  - Status updates
  - Team invitations
  - @mentions
  - Comment replies

#### 7.2 Delivery Methods
- **Communication Channels**
  - In-app notifications (jQuery toast)
  - Email notifications (PHPMailer)
  - Dashboard alerts (Bootstrap alerts)
  - jQuery notification plugin

### 8. Search & Filtering

#### 8.1 Global Search
- **Search Capabilities**
  - Search across all modules (PHP/MySQL)
  - jQuery AJAX live search
  - Task title and description search
  - User search (jQuery autocomplete)
  - Date range search (jQuery datepicker)
  - Advanced filters

#### 8.2 Module-Specific Filters
- **Filter Options**
  - Status filters (jQuery)
  - Priority filters
  - Assignee filters
  - Date filters
  - Category/label filters
  - Combination filters (jQuery multi-select)

## Technical Specifications

### 1. Technology Stack
- **Backend**: PHP 8.1+
- **Database**: MariaDB 10.6+
- **Frontend Framework**: Bootstrap 5.3.x
- **JavaScript**: jQuery 3.7.1
- **jQuery Plugins**:
  - jQuery Validation (forms)
  - DataTables (tables)
  - Select2 (dropdowns)
  - jQuery UI (drag-drop, datepicker)
  - FullCalendar (calendar)
- **Charts**: Chart.js 4.x
- **Icons**: Bootstrap Icons
- **Email**: PHPMailer
- **Authentication**: PHP sessions with secure cookies

### 2. Database Schema

```sql
-- Core Tables
users                      # User accounts
teams                      # Team definitions
team_members              # User-team relationships
roles                     # Role definitions (user, group_admin, global_admin)
user_roles                # User role assignments

-- Todo/Task Tables
tasks                     # Task entries
task_assignees           # Task-user assignments
task_comments            # Task comments
task_attachments         # File attachments
task_categories          # Categories/labels
task_history             # Task change history

-- Kanban Tables
boards                   # Kanban boards
board_columns            # Board columns
board_cards              # Kanban cards (linked to tasks)
column_positions         # Card ordering

-- Calendar Tables
events                   # Calendar events/appointments
event_attendees          # Event participants
event_reminders          # Reminder settings
recurring_patterns       # Recurring event patterns

-- System Tables
notifications            # User notifications
activity_logs            # System activity tracking
user_preferences         # User settings
sessions                 # Active sessions
password_resets          # Password reset tokens
```

### 3. File Structure
```
project-root/
├── index.php                 # Application entry point
├── config/
│   ├── database.php         # Database configuration
│   ├── constants.php        # Application constants
│   └── functions.php        # Global helper functions
├── includes/
│   ├── header.php           # Common header with navbar
│   ├── footer.php           # Common footer with scripts
│   ├── sidebar.php          # Sidebar navigation
│   ├── auth.php             # Authentication functions
│   └── session.php          # Session management
├── classes/
│   ├── Database.php         # PDO database connection
│   ├── User.php             # User model
│   ├── Task.php             # Task model
│   ├── Team.php             # Team model
│   ├── Board.php            # Kanban board model
│   └── Event.php            # Calendar event model
├── pages/
│   ├── login.php            # Login page
│   ├── register.php         # Registration page
│   ├── dashboard.php        # Main dashboard
│   ├── tasks.php            # Todo list view
│   ├── kanban.php           # Kanban board view
│   ├── calendar.php         # Calendar view
│   ├── teams.php            # Team management
│   └── profile.php          # User profile
├── api/
│   ├── auth.php             # Authentication endpoints
│   ├── tasks.php            # Task CRUD operations
│   ├── kanban.php           # Kanban operations
│   ├── calendar.php         # Calendar operations
│   ├── teams.php            # Team operations
│   └── upload.php           # File upload handling
├── assets/
│   ├── css/
│   │   └── style.css        # Custom styles
│   ├── js/
│   │   ├── app.js           # Main jQuery application
│   │   ├── tasks.js         # Task-specific jQuery
│   │   ├── kanban.js        # Kanban jQuery code
│   │   ├── calendar.js      # Calendar jQuery code
│   │   └── charts.js        # Chart.js configurations
│   └── images/              # Application images
├── uploads/
│   ├── avatars/             # User avatars
│   └── attachments/         # Task attachments
├── vendor/                   # Composer packages (PHPMailer, etc.)
└── .htaccess                # Apache configuration
```

### 4. Security Requirements

#### Authentication Security
- Password hashing using PASSWORD_DEFAULT
- Secure session management with PHP
- CSRF protection on all forms
- XSS prevention through htmlspecialchars()
- SQL injection prevention via PDO prepared statements

#### Access Control
- PHP session-based authentication checks
- Role-based permission validation
- Team-based data isolation
- Resource ownership verification

### 5. Performance Requirements
- Page load time < 2 seconds
- jQuery AJAX updates < 500ms
- Support for 100+ concurrent users
- Efficient database queries with indexing
- Asset minification and compression
- jQuery plugin lazy loading

## User Interface Design

### 1. Layout Structure
- **Header**: Bootstrap navbar with logo, search, notifications
- **Sidebar**: jQuery-powered collapsible navigation
- **Main Content**: Dynamic content loaded via jQuery AJAX
- **Footer**: Copyright, version info

### 2. Responsive Design
- Bootstrap 5 responsive grid
- Mobile-first approach
- jQuery mobile touch events
- Collapsible sidebar on mobile

### 3. Theme Support
- Light theme (default)
- Dark theme option
- jQuery theme switcher
- LocalStorage for preference

### 4. Interactive Elements
- **jQuery AJAX Integration**
  - Form submissions without refresh
  - Real-time status updates
  - Infinite scroll for lists
  - Live search results
  - Auto-save drafts

- **jQuery UI Components**
  - Drag-and-drop for Kanban
  - Datepicker for dates
  - Autocomplete for mentions
  - Sortable for lists
  - Slider for progress

## Implementation Phases

### Phase 1: Foundation (Week 1-2)
- Database setup with MariaDB
- PHP authentication system
- Basic user registration/login
- Session management
- jQuery/Bootstrap setup

### Phase 2: Core Features (Week 3-4)
- Team creation and management
- Role-based access control (PHP)
- Basic CRUD for tasks (jQuery AJAX)
- User profile management
- Bootstrap dashboard layout

### Phase 3: Todo Module (Week 5)
- Complete task management (PHP/jQuery)
- Task assignment system
- jQuery DataTables integration
- Task comments with AJAX
- Status updates

### Phase 4: Kanban Module (Week 6)
- Board creation (PHP)
- jQuery UI drag-and-drop
- Column management
- Card details (Bootstrap modals)
- Board filters (jQuery)

### Phase 5: Calendar Module (Week 7)
- FullCalendar integration
- Event creation (jQuery forms)
- Due date integration
- Appointment management
- Reminders (PHP cron)

### Phase 6: Dashboard & Analytics (Week 8)
- Chart.js implementation
- jQuery widget system
- Activity feed (AJAX polling)
- Progress tracking
- PHP data export

### Phase 7: Polish & Testing (Week 9-10)
- UI/UX refinement with jQuery
- Performance optimization
- Security hardening
- Documentation
- Bug fixes

## Development Environment

### Local Development (MAMP/XAMPP)
- **PHP Version**: 8.1+
- **MariaDB**: 10.6+
- **Web Server**: Apache with mod_rewrite
- **Development URL**: http://localhost:8888
- **Database Name**: saas_template
- **Database User**: root
- **Database Password**: (local dev password)

### Required PHP Extensions
- PDO and PDO_MySQL
- mbstring
- openssl
- fileinfo
- gd or imagick
- json
- session
- ctype
- filter

### jQuery Dependencies
```html
<!-- Core Libraries -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery Plugins -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.datatables.net/1.13.0/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.0.0/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.0.0/dist/chart.umd.min.js"></script>
```

## Success Metrics

### Technical Metrics
- All jQuery plugins properly integrated
- PHP/jQuery AJAX communication working
- MariaDB queries optimized
- Security best practices implemented
- Code documentation complete

### User Experience Metrics
- Smooth jQuery animations
- Responsive on all devices
- Fast AJAX responses
- Clear visual feedback
- Accessible design

## Conclusion

This SaaS template leverages the proven PHP/jQuery/MariaDB stack to provide a solid foundation for building team collaboration and task management applications. The combination of server-side PHP processing and client-side jQuery interactivity offers a robust, maintainable solution that can be easily customized and extended for specific business needs.

---

# Document 2: Technology Stack Documentation

## Overview

This document outlines the technology stack for the SaaS Application Template (Todo, Kanban, Calendar). The approach prioritizes simplicity, reliability, and proven technologies using a traditional LAMP stack with jQuery for rich interactivity.

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
  - mod_headers (for security headers)
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

### JavaScript Library
- **jQuery 3.7.1** (latest stable version)
- Simplifies DOM manipulation
- Extensive plugin ecosystem
- AJAX functionality built-in
- Event handling and animations

### Essential jQuery Plugins

1. **jQuery Validation Plugin** (v1.19.5)
   - Client-side form validation
   - Custom validation rules
   - Error message management
   - Integration with Bootstrap styling

2. **DataTables** (v1.13.x)
   - Advanced table functionality
   - Sorting, filtering, pagination
   - Server-side processing support
   - Export capabilities (CSV, Excel, PDF)
   - Responsive tables

3. **Select2** (v4.1.x)
   - Enhanced select boxes
   - Search functionality
   - Multi-select support
   - AJAX data loading
   - Tagging support

4. **jQuery UI** (v1.13.x)
   - Draggable/Droppable for Kanban
   - Sortable for list ordering
   - Datepicker for date selection
   - Autocomplete for mentions
   - Slider for progress indicators

5. **FullCalendar** (v6.x)
   - Full-featured calendar
   - Month/Week/Day views
   - Event management
   - Drag-and-drop support
   - Recurring events

6. **Additional Plugins**
   - **Toastr** - Toast notifications
   - **SweetAlert2** - Beautiful alert dialogs
   - **jQuery Form** - AJAX form submissions
   - **jQuery Mask** - Input masking
   - **Moment.js** - Date/time manipulation

### Charts & Visualization
- **Chart.js 4.x**
- Lightweight and flexible
- Responsive charts
- Multiple chart types
- Animation support
- jQuery integration wrapper

## Application Architecture

### PHP Structure (Simple MVC Pattern)
```
project-root/
├── index.php              # Main entry point & router
├── config/
│   ├── database.php       # Database configuration
│   ├── constants.php      # Application constants
│   ├── functions.php      # Global helper functions
│   └── mail.php          # PHPMailer configuration
├── includes/
│   ├── header.php         # Common header
│   ├── footer.php         # Common footer
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
│   │   ├── login.php      # Login page
│   │   ├── register.php   # Registration
│   │   └── reset.php      # Password reset
│   ├── dashboard.php      # Main dashboard
│   ├── tasks.php          # Todo list view
│   ├── kanban.php         # Kanban board
│   ├── calendar.php       # Calendar view
│   ├── teams.php          # Team management
│   └── profile.php        # User profile
├── api/
│   ├── auth.php           # Authentication endpoints
│   ├── tasks.php          # Task CRUD
│   ├── kanban.php         # Kanban operations
│   ├── calendar.php       # Event management
│   ├── teams.php          # Team operations
│   ├── notifications.php  # Notification endpoints
│   └── upload.php         # File upload handler
├── assets/
│   ├── css/
│   │   ├── bootstrap.min.css
│   │   ├── style.css      # Custom styles
│   │   └── dark-theme.css # Dark theme
│   ├── js/
│   │   ├── jquery.min.js
│   │   ├── app.js         # Main application JS
│   │   ├── tasks.js       # Task management
│   │   ├── kanban.js      # Kanban functionality
│   │   ├── calendar.js    # Calendar code
│   │   └── charts.js      # Dashboard charts
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

## Security Implementation

### PHP Security
1. **Password Hashing**
   ```php
   // Registration
   $hash = password_hash($password, PASSWORD_DEFAULT);
   
   // Login verification
   if (password_verify($password, $hash)) {
       // Authenticated
   }
   ```

2. **SQL Injection Prevention**
   ```php
   // Always use prepared statements
   $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
   $stmt->execute([$email]);
   ```

3. **Session Security**
   ```php
   // config/session.php
   ini_set('session.use_only_cookies', 1);
   ini_set('session.cookie_httponly', 1);
   ini_set('session.cookie_secure', 1);
   ini_set('session.cookie_samesite', 'Lax');
   session_start();
   session_regenerate_id(true);
   ```

4. **CSRF Protection**
   ```php
   // Generate token
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
   
   // Verify on submission
   if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
       die('CSRF validation failed');
   }
   ```

5. **File Upload Security**
   ```php
   $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
   $maxSize = 5 * 1024 * 1024; // 5MB
   
   // Validate and sanitize
   $filename = uniqid() . '_' . basename($_FILES['file']['name']);
   ```

### Frontend Security with jQuery
1. **XSS Prevention**
   ```javascript
   // Always escape user input
   $('#output').text(userInput); // Safe
   // Never use .html() with user input
   ```

2. **AJAX Security**
   ```javascript
   $.ajaxSetup({
       headers: {
           'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
       }
   });
   ```

## jQuery Implementation Patterns

### AJAX Form Submission
```javascript
$(document).ready(function() {
    $('#taskForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '/api/tasks.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend: function() {
                $('#submitBtn').prop('disabled', true);
                $('.spinner').show();
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Task created successfully');
                    $('#taskModal').modal('hide');
                    refreshTaskList();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                toastr.error('An error occurred');
            },
            complete: function() {
                $('#submitBtn').prop('disabled', false);
                $('.spinner').hide();
            }
        });
    });
});
```

### DataTable Implementation
```javascript
$(document).ready(function() {
    var taskTable = $('#tasksTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/api/tasks.php',
            type: 'GET',
            data: function(d) {
                d.team_id = $('#teamFilter').val();
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            { data: 'id' },
            { data: 'title' },
            { data: 'priority' },
            { data: 'assignee' },
            { data: 'due_date' },
            { data: 'status' },
            { data: 'actions', orderable: false }
        ],
        order: [[4, 'asc']], // Order by due date
        responsive: true
    });
});
```

### Kanban Drag-and-Drop
```javascript
$(function() {
    // Make columns sortable
    $('.kanban-column').sortable({
        connectWith: '.kanban-column',
        handle: '.card-header',
        placeholder: 'kanban-placeholder',
        update: function(event, ui) {
            if (this === ui.item.parent()[0]) {
                var cardId = ui.item.data('card-id');
                var newColumn = $(this).data('column-id');
                
                $.post('/api/kanban.php', {
                    action: 'move',
                    card_id: cardId,
                    column_id: newColumn
                });
            }
        }
    });
});
```

### Calendar Integration
```javascript
$(document).ready(function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: '/api/calendar.php?action=list',
        editable: true,
        eventClick: function(info) {
            showEventModal(info.event);
        }
    });
    calendar.render();
});
```

## Third-Party Services

### Email Service
- **PHPMailer** (via Composer)
```php
composer require phpmailer/phpmailer
```

### PDF Generation
- **TCPDF** or **Dompdf** for reports
```php
composer require tecnickcom/tcpdf
```

### Image Processing
- **Intervention Image** for avatar processing
```php
composer require intervention/image
```

## Performance Optimization

### PHP Optimization
- OPcache enabled
- Database query caching
- Prepared statement reuse
- Lazy loading of classes

### jQuery Optimization
```javascript
// Cache jQuery selectors
var $taskList = $('#taskList');

// Debounce search
var searchTimer;
$('#search').on('keyup', function() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() {
        performSearch();
    }, 300);
});

// Event delegation
$('#taskList').on('click', '.task-item', function() {
    // Handle click
});
```

### Database Optimization
```sql
-- Key indexes
ALTER TABLE tasks ADD INDEX idx_team_status (team_id, status);
ALTER TABLE tasks ADD INDEX idx_assignee_due (assignee_id, due_date);
ALTER TABLE events ADD INDEX idx_user_date (user_id, start_date);
```

### Asset Optimization
```html
<!-- Minified versions -->
<link href="/assets/css/style.min.css" rel="stylesheet">
<script src="/assets/js/app.min.js"></script>

<!-- CDN with fallback -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>window.jQuery || document.write('<script src="/assets/js/jquery.min.js"><\/script>')</script>
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

### Local Development (MAMP)
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

## Deployment Strategy

### Production Configuration
```php
// config/constants.php
define('ENVIRONMENT', 'production');
define('DEBUG_MODE', false);
define('SECURE_COOKIES', true);
define('FORCE_SSL', true);
```

### Deployment Checklist
1. Run composer install --no-dev
2. Minify CSS/JS assets
3. Set proper file permissions
4. Configure Apache virtual host
5. Set up SSL certificate
6. Configure cron jobs
7. Set up backup strategy

## Monitoring & Maintenance

### Error Logging
```php
// Monolog implementation
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('app');
$log->pushHandler(new StreamHandler('/logs/app.log', Logger::WARNING));
```

### Performance Monitoring
- Apache mod_status
- MariaDB slow query log
- PHP error logs
- jQuery AJAX error tracking

## Cost Estimation

### Development Environment
- **MAMP PRO**: $79 (optional, free version works)
- **PHPStorm IDE**: $89/year (optional)
- **Local development**: Free

### Hosting Options
1. **Shared Hosting**: $10-20/month
   - cPanel with PHP/MariaDB
   - Limited resources
   - Good for MVP

2. **VPS**: $20-40/month
   - Full control
   - Better performance
   - Scalable

3. **Cloud (AWS/DigitalOcean)**: $40-100/month
   - Auto-scaling
   - Load balancing
   - Professional grade

## Conclusion

This technology stack leverages the proven reliability of PHP with the rich interactivity of jQuery to create a robust SaaS application. The combination provides:

- **Simplicity**: Well-understood technologies with extensive documentation
- **Reliability**: Battle-tested LAMP stack with jQuery's stability
- **Maintainability**: Clear structure and widespread developer knowledge
- **Performance**: Optimized for typical SaaS workloads
- **Scalability**: Clear upgrade path as the application grows

The jQuery ecosystem provides all necessary UI components while PHP handles server-side logic efficiently. This stack has proven successful in numerous production applications and provides an excellent foundation for the SaaS template.

---

# Document 3: Design Notes - jQuery/Bootstrap 5 Implementation

## Overview

This document outlines the UI/UX patterns for the SaaS Application Template using Bootstrap 5 components enhanced with jQuery for interactivity. The design philosophy emphasizes simplicity, consistency, and rich user interactions through jQuery plugins.

## Design Principles

1. **jQuery-First Interactivity**: Leverage jQuery for all dynamic behaviors
2. **Bootstrap Foundation**: Use Bootstrap 5 for layout and styling
3. **Progressive Enhancement**: Start with working HTML, enhance with jQuery
4. **AJAX Everything**: Use jQuery AJAX for seamless updates
5. **Plugin Integration**: Utilize mature jQuery plugins for complex features

## Core jQuery Patterns

### Document Ready Pattern
```javascript
$(document).ready(function() {
    // Initialize all jQuery components
    initializeDataTables();
    initializeDatePickers();
    initializeSelect2();
    initializeTooltips();
    bindEventHandlers();
});
```

### AJAX Pattern
```javascript
// Standard AJAX request pattern
function performAjaxRequest(url, data, successCallback) {
    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        dataType: 'json',
        beforeSend: function() {
            showLoader();
        },
        success: function(response) {
            if (response.success) {
                successCallback(response);
            } else {
                showError(response.message);
            }
        },
        error: function() {
            showError('An error occurred');
        },
        complete: function() {
            hideLoader();
        }
    });
}
```

## Page Layouts

### 1. Authentication Pages (Login/Register)
**Bootstrap Base**: Sign-in example
**jQuery Enhancements**:
```javascript
// Form validation
$('#loginForm').validate({
    rules: {
        email: {
            required: true,
            email: true
        },
        password: {
            required: true,
            minlength: 8
        }
    },
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

### 2. Main Dashboard Layout
**jQuery Sidebar Interactions**:
```javascript
// Collapsible sidebar for mobile
$('#sidebarToggle').on('click', function() {
    $('#sidebar').toggleClass('active');
    $(this).find('i').toggleClass('bi-list bi-x');
});

// Active menu highlighting
$('.nav-link').on('click', function() {
    $('.nav-link').removeClass('active');
    $(this).addClass('active');
    
    // Load content via AJAX
    var page = $(this).data('page');
    loadContent(page);
});
```

### 3. Todo List View
**DataTables Implementation**:
```javascript
var taskTable = $('#taskTable').DataTable({
    ajax: '/api/tasks.php',
    columns: [
        { data: 'id' },
        { data: 'title' },
        { data: 'priority' },
        { data: 'assignee' },
        { data: 'due_date' },
        { data: 'status' }
    ],
    responsive: true,
    pageLength: 25
});
```

### 4. Kanban Board
**jQuery UI Sortable Implementation**:
```javascript
function initializeKanban() {
    $('.kanban-column').sortable({
        connectWith: '.kanban-column',
        handle: '.card-header',
        placeholder: 'kanban-placeholder',
        update: function(event, ui) {
            if (this === ui.item.parent()[0]) {
                var taskId = ui.item.data('task-id');
                var newStatus = $(this).data('status');
                
                $.ajax({
                    url: '/api/kanban.php',
                    type: 'POST',
                    data: {
                        action: 'move',
                        task_id: taskId,
                        status: newStatus
                    }
                });
            }
        }
    });
}
```

### 5. Calendar View
**FullCalendar jQuery Integration**:
```javascript
var calendar = new FullCalendar.Calendar(calendarEl, {
    events: '/api/calendar.php',
    editable: true,
    eventClick: function(info) {
        showEventModal(info.event);
    },
    dateClick: function(info) {
        $('#eventDate').val(info.dateStr);
        $('#addEventModal').modal('show');
    }
});
```

### 6. Dashboard Charts
**Chart.js with jQuery**:
```javascript
function initializeDashboardCharts() {
    $.get('/api/dashboard.php?chart=completion', function(data) {
        var ctx = $('#completionChart')[0].getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Tasks Completed',
                    data: data.values,
                    borderColor: 'rgb(75, 192, 192)'
                }]
            }
        });
    });
}
```

## Form Components

### Select2 for Enhanced Dropdowns
```javascript
$('#assigneeSelect').select2({
    placeholder: 'Select team members',
    ajax: {
        url: '/api/users.php',
        dataType: 'json'
    }
});
```

### Date Pickers
```javascript
$('.datepicker').datepicker({
    dateFormat: 'yy-mm-dd',
    minDate: 0,
    showButtonPanel: true
});
```

### File Upload with jQuery
```javascript
$('#dropzone').on('drop', function(e) {
    e.preventDefault();
    var files = e.originalEvent.dataTransfer.files;
    
    var formData = new FormData();
    $.each(files, function(i, file) {
        formData.append('files[]', file);
    });
    
    $.ajax({
        url: '/api/upload.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false
    });
});
```

## Notification System

### Toastr Integration
```javascript
toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right'
};

function showSuccess(message) {
    toastr.success(message);
}
```

### Real-time Notifications
```javascript
function checkNotifications() {
    $.get('/api/notifications.php?unread=true', function(data) {
        if (data.count > 0) {
            $('#notificationCount').text(data.count).show();
        }
    });
}

setInterval(checkNotifications, 30000);
```

## Search & Filtering

### Live Search with jQuery
```javascript
var searchTimer;
$('#globalSearch').on('keyup', function() {
    clearTimeout(searchTimer);
    var query = $(this).val();
    
    searchTimer = setTimeout(function() {
        $.get('/api/search.php', { q: query }, function(data) {
            // Display results
        });
    }, 300);
});
```

## Mobile Responsiveness

### Touch Events with jQuery
```javascript
if ('ontouchstart' in window) {
    $('.draggable').draggable({
        handle: '.handle',
        helper: 'clone'
    });
    
    $('.swipeable').on('swipeleft', function() {
        $(this).find('.actions').show();
    });
}
```

## Performance Optimization

### Lazy Loading
```javascript
$('img.lazy').lazyload({
    effect: 'fadeIn',
    threshold: 200
});
```

### Caching AJAX Responses
```javascript
var cache = {};

function getCachedData(url, callback) {
    if (cache[url]) {
        callback(cache[url]);
    } else {
        $.get(url, function(data) {
            cache[url] = data;
            callback(data);
        });
    }
}
```

## Theme Switching

### jQuery Theme Manager
```javascript
var currentTheme = localStorage.getItem('theme') || 'light';
$('body').attr('data-theme', currentTheme);

$('#themeToggle').on('click', function() {
    var newTheme = currentTheme === 'light' ? 'dark' : 'light';
    $('body').attr('data-theme', newTheme);
    currentTheme = newTheme;
    localStorage.setItem('theme', newTheme);
});
```

## Accessibility

### jQuery Accessibility Helpers
```javascript
$(document).on('keydown', function(e) {
    // Alt + S for search
    if (e.altKey && e.key === 's') {
        e.preventDefault();
        $('#globalSearch').focus();
    }
});
```

## File Organization

```
/assets/js/
├── jquery.min.js
├── bootstrap.bundle.min.js
├── plugins/
│   ├── jquery.validate.min.js
│   ├── jquery.dataTables.min.js
│   ├── select2.min.js
│   └── jquery-ui.min.js
├── app.js
└── modules/
    ├── tasks.js
    ├── kanban.js
    └── calendar.js
```

## Development Guidelines

1. **Always use jQuery's document ready**
2. **Cache jQuery selectors when reused**
3. **Use event delegation for dynamic content**
4. **Implement proper error handling in AJAX calls**
5. **Debounce/throttle expensive operations**
6. **Use jQuery's built-in animation methods**
7. **Leverage jQuery plugins for complex features**
8. **Maintain consistent naming conventions**

## Conclusion

This jQuery-centric design approach provides:
- **Rich Interactivity**: Smooth animations and transitions
- **Proven Reliability**: Battle-tested jQuery plugins
- **Rapid Development**: Extensive plugin ecosystem
- **Cross-browser Support**: jQuery handles compatibility
- **Easy Maintenance**: Familiar patterns for developers

The combination of Bootstrap's solid foundation with jQuery's powerful DOM manipulation creates an intuitive, responsive, and feature-rich user experience.

---

## End of Documentation Package

This complete documentation package contains all three updated documents for your SaaS Application Template project, fully aligned with the PHP/jQuery/MariaDB stack that has proven successful in your previous projects.