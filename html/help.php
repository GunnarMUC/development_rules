<?php
session_start();
require_once 'includes/auth.php';
require_login();

$pageTitle = 'Help Center';
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <main class="col-12 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="help-header">
                <h1 class="h2">Help Center</h1>
            </div>

            <div class="row" id="help-container">
                <div class="col-md-8">
                    <!-- Getting Started Section -->
                    <div class="card mb-4" id="getting-started-section">
                        <div class="card-header">
                            <h4><i class="bi bi-rocket-takeoff"></i> Getting Started</h4>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="gettingStartedAccordion">
                                <div class="accordion-item" id="gs-item-1">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#gs-collapse-1">
                                            How do I create my first task?
                                        </button>
                                    </h2>
                                    <div id="gs-collapse-1" class="accordion-collapse collapse show" data-bs-parent="#gettingStartedAccordion">
                                        <div class="accordion-body">
                                            <ol>
                                                <li>Navigate to the Dashboard or My Tasks page</li>
                                                <li>Click the "Create Task" button</li>
                                                <li>Fill in the task details (title, description, priority, due date)</li>
                                                <li>Assign the task to yourself or a team member</li>
                                                <li>Click "Save" to create the task</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item" id="gs-item-2">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gs-collapse-2">
                                            How do I join or create a team?
                                        </button>
                                    </h2>
                                    <div id="gs-collapse-2" class="accordion-collapse collapse" data-bs-parent="#gettingStartedAccordion">
                                        <div class="accordion-body">
                                            <strong>To create a team:</strong>
                                            <ol>
                                                <li>Go to "My Teams" page</li>
                                                <li>Click "Create Team" button</li>
                                                <li>Enter team name and description</li>
                                                <li>Click "Create" to establish your new team</li>
                                            </ol>
                                            <strong>To join a team:</strong>
                                            <p>You need to be invited by a team administrator. They can add you from the team management page.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item" id="gs-item-3">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gs-collapse-3">
                                            How do I use the Kanban board?
                                        </button>
                                    </h2>
                                    <div id="gs-collapse-3" class="accordion-collapse collapse" data-bs-parent="#gettingStartedAccordion">
                                        <div class="accordion-body">
                                            The Kanban board helps visualize your workflow:
                                            <ul>
                                                <li><strong>To Do:</strong> Tasks that haven't been started</li>
                                                <li><strong>In Progress:</strong> Tasks currently being worked on</li>
                                                <li><strong>Done:</strong> Completed tasks</li>
                                            </ul>
                                            <p>Simply drag and drop tasks between columns to update their status.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Frequently Asked Questions -->
                    <div class="card mb-4" id="faq-section">
                        <div class="card-header">
                            <h4><i class="bi bi-question-circle"></i> Frequently Asked Questions</h4>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="faqAccordion">
                                <div class="accordion-item" id="faq-item-1">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-1">
                                            Can I delete a task?
                                        </button>
                                    </h2>
                                    <div id="faq-collapse-1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Yes, you can delete tasks that you created or tasks assigned to you. Click on the task to view details, then click the "Delete" button. Please note that deleted tasks cannot be recovered.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item" id="faq-item-2">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-2">
                                            How do I change my password?
                                        </button>
                                    </h2>
                                    <div id="faq-collapse-2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Go to Settings > Account Settings > Change Password. Enter your current password, then your new password twice to confirm. Click "Update Password" to save the changes.
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item" id="faq-item-3">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-3">
                                            What are task priorities?
                                        </button>
                                    </h2>
                                    <div id="faq-collapse-3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            Task priorities help you organize work by importance:
                                            <ul>
                                                <li><span class="badge bg-danger">High</span> - Urgent tasks requiring immediate attention</li>
                                                <li><span class="badge bg-warning">Medium</span> - Important tasks with standard deadlines</li>
                                                <li><span class="badge bg-info">Low</span> - Tasks that can be completed when time permits</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item" id="faq-item-4">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq-collapse-4">
                                            How do notifications work?
                                        </button>
                                    </h2>
                                    <div id="faq-collapse-4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                        <div class="accordion-body">
                                            You'll receive notifications for:
                                            <ul>
                                                <li>New tasks assigned to you</li>
                                                <li>Updates on tasks you're following</li>
                                                <li>Comments on your tasks</li>
                                                <li>Team invitations</li>
                                                <li>Upcoming task deadlines</li>
                                            </ul>
                                            <p>Configure your notification preferences in Settings > Notifications.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Troubleshooting -->
                    <div class="card mb-4" id="troubleshooting-section">
                        <div class="card-header">
                            <h4><i class="bi bi-wrench"></i> Troubleshooting</h4>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="troubleshootingAccordion">
                                <div class="accordion-item" id="ts-item-1">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ts-collapse-1">
                                            I can't log in to my account
                                        </button>
                                    </h2>
                                    <div id="ts-collapse-1" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                        <div class="accordion-body">
                                            <ol>
                                                <li>Ensure you're using the correct email address</li>
                                                <li>Check that Caps Lock is off</li>
                                                <li>Try resetting your password using the "Forgot Password" link</li>
                                                <li>Clear your browser cache and cookies</li>
                                                <li>If problems persist, contact support</li>
                                            </ol>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item" id="ts-item-2">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ts-collapse-2">
                                            Tasks are not updating
                                        </button>
                                    </h2>
                                    <div id="ts-collapse-2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                                        <div class="accordion-body">
                                            <ul>
                                                <li>Refresh your browser page (F5 or Ctrl+R)</li>
                                                <li>Check your internet connection</li>
                                                <li>Try logging out and back in</li>
                                                <li>Ensure you have the necessary permissions to update the task</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Quick Links -->
                    <div class="card mb-4" id="quick-links-section">
                        <div class="card-header">
                            <h5><i class="bi bi-link-45deg"></i> Quick Links</h5>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                <a href="dashboard.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-speedometer2"></i> Dashboard
                                </a>
                                <a href="my-tasks.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-list-task"></i> My Tasks
                                </a>
                                <a href="my-teams.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-people"></i> My Teams
                                </a>
                                <a href="calendar.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-calendar3"></i> Calendar
                                </a>
                                <a href="reports.php" class="list-group-item list-group-item-action">
                                    <i class="bi bi-graph-up"></i> Reports
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Support -->
                    <div class="card mb-4" id="contact-support-section">
                        <div class="card-header">
                            <h5><i class="bi bi-headset"></i> Contact Support</h5>
                        </div>
                        <div class="card-body">
                            <p>Need additional help? Our support team is here to assist you.</p>
                            <div class="mb-3">
                                <strong>Email:</strong><br>
                                <a href="mailto:support@taskmanager.com">support@taskmanager.com</a>
                            </div>
                            <div class="mb-3">
                                <strong>Response Time:</strong><br>
                                Within 24 business hours
                            </div>
                            <div>
                                <strong>Office Hours:</strong><br>
                                Monday - Friday<br>
                                9:00 AM - 5:00 PM EST
                            </div>
                        </div>
                    </div>

                    <!-- Keyboard Shortcuts -->
                    <div class="card" id="keyboard-shortcuts-section">
                        <div class="card-header">
                            <h5><i class="bi bi-keyboard"></i> Keyboard Shortcuts</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <td><kbd>Ctrl</kbd> + <kbd>N</kbd></td>
                                    <td>New Task</td>
                                </tr>
                                <tr>
                                    <td><kbd>Ctrl</kbd> + <kbd>S</kbd></td>
                                    <td>Save</td>
                                </tr>
                                <tr>
                                    <td><kbd>Esc</kbd></td>
                                    <td>Close Modal</td>
                                </tr>
                                <tr>
                                    <td><kbd>/</kbd></td>
                                    <td>Search</td>
                                </tr>
                                <tr>
                                    <td><kbd>?</kbd></td>
                                    <td>Help</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>