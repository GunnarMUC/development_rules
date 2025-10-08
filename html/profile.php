<?php
// Profile page
require_once 'includes/session.php';
require_once 'includes/auth.php';

// Require login
require_login();

// Get current user
$user = get_current_user_info();

// Get database connection
require_once 'includes/db.php';

// Handle profile update
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Update profile information
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $job_title = trim($_POST['job_title']);
        $department = trim($_POST['department']);
        $bio = trim($_POST['bio']);

        try {
            $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, job_title = ?, department = ?, bio = ? WHERE id = ?");
            $stmt->execute([$first_name, $last_name, $email, $phone, $job_title, $department, $bio, $user['id']]);

            // Update session with new info
            $_SESSION['user']['first_name'] = $first_name;
            $_SESSION['user']['last_name'] = $last_name;
            $_SESSION['user']['email'] = $email;

            $success_message = 'Profile updated successfully!';

            // Refresh user data
            $user = get_current_user_info();
        } catch (PDOException $e) {
            $error_message = 'Error updating profile. Please try again.';
        }
    } elseif (isset($_POST['change_password'])) {
        // Change password
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $error_message = 'New passwords do not match.';
        } else {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $stored_password = $stmt->fetchColumn();

            if (password_verify($current_password, $stored_password)) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user['id']]);

                $success_message = 'Password changed successfully!';
            } else {
                $error_message = 'Current password is incorrect.';
            }
        }
    }
}

// Fetch complete user profile data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user['id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch user statistics
$userId = $user['id'];

// Get total tasks
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tasks WHERE user_id = ? OR assigned_to = ?");
$stmt->execute([$userId, $userId]);
$totalTasks = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get completed tasks
$stmt = $pdo->prepare("SELECT COUNT(*) as completed FROM tasks WHERE (user_id = ? OR assigned_to = ?) AND status = 'completed'");
$stmt->execute([$userId, $userId]);
$completedTasks = $stmt->fetch(PDO::FETCH_ASSOC)['completed'];

// Get teams count
$stmt = $pdo->prepare("SELECT COUNT(*) as teams FROM team_members WHERE user_id = ?");
$stmt->execute([$userId]);
$teamsCount = $stmt->fetch(PDO::FETCH_ASSOC)['teams'];

// Calculate completion rate
$completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

// Page-specific settings
$page_title = 'Profile';

// Include header
include_once 'includes/header.php';
?>

<!-- Main Container -->
<div class="container-fluid">
    <div class="row">
        <!-- Main Content (full width, no sidebar) -->
        <main class="col-lg-12 px-md-4 mt-4">
            <!-- Page Header -->
            <div class="page-header mb-4" id="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h1 class="page-title" id="page-title">My Profile</h1>
                        <nav aria-label="breadcrumb" id="breadcrumb-nav">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Profile</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <!-- Profile Overview Section -->
            <div class="row mb-4">
                <div class="col-lg-4 mb-4">
                    <div class="card profile-card" id="profile-overview-card">
                        <div class="card-body text-center">
                            <div class="profile-avatar-wrapper mb-3" id="avatar-wrapper">
                                <div class="profile-avatar">
                                    <?php echo strtoupper(substr($profile['first_name'], 0, 1) . substr($profile['last_name'], 0, 1)); ?>
                                </div>
                            </div>
                            <h4 class="mb-1" id="profile-name"><?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?></h4>
                            <p class="text-muted mb-3" id="profile-role"><?php echo htmlspecialchars($profile['job_title'] ?: 'Team Member'); ?></p>

                            <div class="profile-stats" id="profile-stats">
                                <div class="stat-item">
                                    <h5><?php echo $totalTasks; ?></h5>
                                    <small class="text-muted">Total Tasks</small>
                                </div>
                                <div class="stat-item">
                                    <h5><?php echo $completedTasks; ?></h5>
                                    <small class="text-muted">Completed</small>
                                </div>
                                <div class="stat-item">
                                    <h5><?php echo $teamsCount; ?></h5>
                                    <small class="text-muted">Teams</small>
                                </div>
                            </div>

                            <div class="mt-3" id="completion-progress">
                                <label class="text-muted small">Task Completion Rate</label>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completionRate; ?>%"
                                         aria-valuenow="<?php echo $completionRate; ?>" aria-valuemin="0" aria-valuemax="100">
                                        <?php echo $completionRate; ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information Card -->
                    <div class="card mt-4" id="contact-info-card">
                        <div class="card-header">
                            <h5 class="mb-0">Contact Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="contact-item mb-3" id="email-contact">
                                <i class="bi bi-envelope text-primary me-2"></i>
                                <span><?php echo htmlspecialchars($profile['email']); ?></span>
                            </div>
                            <div class="contact-item mb-3" id="phone-contact">
                                <i class="bi bi-telephone text-primary me-2"></i>
                                <span><?php echo htmlspecialchars($profile['phone'] ?: 'Not provided'); ?></span>
                            </div>
                            <div class="contact-item mb-3" id="department-contact">
                                <i class="bi bi-building text-primary me-2"></i>
                                <span><?php echo htmlspecialchars($profile['department'] ?: 'Not specified'); ?></span>
                            </div>
                            <div class="contact-item" id="joined-date">
                                <i class="bi bi-calendar3 text-primary me-2"></i>
                                <span>Joined <?php echo date('F Y', strtotime($profile['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <!-- Profile Settings Tabs -->
                    <div class="card" id="profile-settings-card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" id="profile-tabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="general-tab" data-bs-toggle="tab"
                                            data-bs-target="#general" type="button" role="tab"
                                            aria-controls="general" aria-selected="true">
                                        <i class="bi bi-person me-2"></i>General Information
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="security-tab" data-bs-toggle="tab"
                                            data-bs-target="#security" type="button" role="tab"
                                            aria-controls="security" aria-selected="false">
                                        <i class="bi bi-shield-lock me-2"></i>Security
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="activity-tab" data-bs-toggle="tab"
                                            data-bs-target="#activity" type="button" role="tab"
                                            aria-controls="activity" aria-selected="false">
                                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                                    </button>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="profile-tab-content">
                                <!-- General Information Tab -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    <form method="POST" action="" id="profile-form">
                                        <input type="hidden" name="update_profile" value="1">

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="first_name" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="first_name" name="first_name"
                                                       value="<?php echo htmlspecialchars($profile['first_name']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="last_name" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="last_name" name="last_name"
                                                       value="<?php echo htmlspecialchars($profile['last_name']); ?>" required>
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="email" class="form-label">Email Address</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                       value="<?php echo htmlspecialchars($profile['email']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="phone" class="form-label">Phone Number</label>
                                                <input type="tel" class="form-control" id="phone" name="phone"
                                                       value="<?php echo htmlspecialchars($profile['phone']); ?>"
                                                       placeholder="(555) 123-4567">
                                            </div>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="job_title" class="form-label">Job Title</label>
                                                <input type="text" class="form-control" id="job_title" name="job_title"
                                                       value="<?php echo htmlspecialchars($profile['job_title']); ?>"
                                                       placeholder="e.g. Software Developer">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="department" class="form-label">Department</label>
                                                <select class="form-select" id="department" name="department">
                                                    <option value="">Select Department</option>
                                                    <option value="Engineering" <?php echo $profile['department'] === 'Engineering' ? 'selected' : ''; ?>>Engineering</option>
                                                    <option value="Design" <?php echo $profile['department'] === 'Design' ? 'selected' : ''; ?>>Design</option>
                                                    <option value="Marketing" <?php echo $profile['department'] === 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                                                    <option value="Sales" <?php echo $profile['department'] === 'Sales' ? 'selected' : ''; ?>>Sales</option>
                                                    <option value="HR" <?php echo $profile['department'] === 'HR' ? 'selected' : ''; ?>>Human Resources</option>
                                                    <option value="Operations" <?php echo $profile['department'] === 'Operations' ? 'selected' : ''; ?>>Operations</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="bio" class="form-label">Bio</label>
                                            <textarea class="form-control" id="bio" name="bio" rows="4"
                                                      placeholder="Tell us about yourself..."><?php echo htmlspecialchars($profile['bio']); ?></textarea>
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="button" class="btn btn-secondary me-2" onclick="window.location.reload()">Cancel</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-lg me-2"></i>Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Security Tab -->
                                <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                                    <h5 class="mb-4">Change Password</h5>
                                    <form method="POST" action="" id="password-form">
                                        <input type="hidden" name="change_password" value="1">

                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password"
                                                   name="current_password" required>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="new_password" class="form-label">New Password</label>
                                                <input type="password" class="form-control" id="new_password"
                                                       name="new_password" required minlength="8">
                                                <small class="text-muted">Minimum 8 characters</small>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                                <input type="password" class="form-control" id="confirm_password"
                                                       name="confirm_password" required minlength="8">
                                            </div>
                                        </div>

                                        <div class="alert alert-info" id="password-requirements">
                                            <h6 class="alert-heading">Password Requirements:</h6>
                                            <ul class="mb-0">
                                                <li>At least 8 characters long</li>
                                                <li>Contains at least one uppercase letter</li>
                                                <li>Contains at least one lowercase letter</li>
                                                <li>Contains at least one number</li>
                                            </ul>
                                        </div>

                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-shield-check me-2"></i>Update Password
                                            </button>
                                        </div>
                                    </form>

                                    <hr class="my-4">

                                    <h5 class="mb-3">Two-Factor Authentication</h5>
                                    <div class="alert alert-warning" id="2fa-status">
                                        <i class="bi bi-shield-exclamation me-2"></i>
                                        Two-factor authentication is not enabled. Enable it for enhanced security.
                                    </div>
                                    <button class="btn btn-outline-primary" id="enable-2fa-btn">
                                        <i class="bi bi-phone me-2"></i>Enable Two-Factor Authentication
                                    </button>
                                </div>

                                <!-- Activity Tab -->
                                <div class="tab-pane fade" id="activity" role="tabpanel" aria-labelledby="activity-tab">
                                    <h5 class="mb-4">Recent Activity</h5>
                                    <div class="activity-timeline" id="activity-timeline">
                                        <div class="activity-item" id="activity-item-1">
                                            <div class="activity-icon bg-success">
                                                <i class="bi bi-check-circle"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p class="mb-1">Completed task "Design new dashboard layout"</p>
                                                <small class="text-muted">2 hours ago</small>
                                            </div>
                                        </div>

                                        <div class="activity-item" id="activity-item-2">
                                            <div class="activity-icon bg-primary">
                                                <i class="bi bi-pencil-square"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p class="mb-1">Updated profile information</p>
                                                <small class="text-muted">Yesterday at 3:45 PM</small>
                                            </div>
                                        </div>

                                        <div class="activity-item" id="activity-item-3">
                                            <div class="activity-icon bg-info">
                                                <i class="bi bi-people"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p class="mb-1">Joined team "Development Squad"</p>
                                                <small class="text-muted">3 days ago</small>
                                            </div>
                                        </div>

                                        <div class="activity-item" id="activity-item-4">
                                            <div class="activity-icon bg-warning">
                                                <i class="bi bi-clock"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p class="mb-1">Started working on task "Implement authentication"</p>
                                                <small class="text-muted">5 days ago</small>
                                            </div>
                                        </div>

                                        <div class="activity-item" id="activity-item-5">
                                            <div class="activity-icon bg-success">
                                                <i class="bi bi-trophy"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p class="mb-1">Achieved "Task Master" badge - 50 tasks completed</p>
                                                <small class="text-muted">1 week ago</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button class="btn btn-outline-primary" id="load-more-activity">
                                            <i class="bi bi-arrow-clockwise me-2"></i>Load More Activity
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Additional CSS for Profile Page -->
<style>
/* Profile specific styles */
.profile-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
}

.profile-avatar-wrapper {
    position: relative;
    display: inline-block;
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 600;
    margin: 0 auto;
}

.profile-stats {
    display: flex;
    justify-content: space-around;
    padding: 1.5rem 0;
    border-top: 1px solid #e9ecef;
    border-bottom: 1px solid #e9ecef;
    margin: 1.5rem 0;
}

.profile-stats .stat-item {
    text-align: center;
    flex: 1;
}

.profile-stats .stat-item h5 {
    font-size: 1.5rem;
    margin-bottom: 0.25rem;
    color: #2c3e50;
}

.contact-item {
    display: flex;
    align-items: center;
    font-size: 0.95rem;
}

.contact-item i {
    font-size: 1.1rem;
    width: 24px;
}

/* Tabs styling */
.nav-tabs {
    border-bottom: 0;
}

.nav-tabs .nav-link {
    color: #6c757d;
    background: transparent;
    border: 0;
    padding: 0.75rem 1.25rem;
    font-weight: 500;
}

.nav-tabs .nav-link.active {
    color: #667eea;
    background: transparent;
    border-bottom: 3px solid #667eea;
}

.nav-tabs .nav-link:hover {
    color: #667eea;
}

/* Activity timeline */
.activity-timeline {
    position: relative;
    padding-left: 45px;
}

.activity-timeline::before {
    content: '';
    position: absolute;
    left: 18px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.activity-item {
    position: relative;
    padding-bottom: 2rem;
}

.activity-item:last-child {
    padding-bottom: 0;
}

.activity-icon {
    position: absolute;
    left: -27px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    background: #667eea;
    border: 3px solid #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.activity-content {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
}

/* Alert styles */
.alert {
    border: none;
    border-radius: 8px;
}

/* Form styles */
.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 8px;
    border: 1px solid #dee2e6;
    padding: 0.625rem 0.875rem;
}

.form-control:focus, .form-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

/* Button styles */
.btn {
    padding: 0.625rem 1.25rem;
    font-weight: 500;
    border-radius: 8px;
}

.btn-primary {
    background: #667eea;
    border-color: #667eea;
}

.btn-primary:hover {
    background: #5a67d8;
    border-color: #5a67d8;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .profile-stats {
        padding: 1rem 0;
    }

    .profile-stats .stat-item h5 {
        font-size: 1.25rem;
    }

    .nav-tabs .nav-link {
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .nav-tabs .nav-link i {
        display: none;
    }
}
</style>

<?php
// Include footer
include_once 'includes/footer.php';
?>

<!-- Profile-specific JavaScript -->
<script>
$(document).ready(function() {
    // Password validation
    $('#password-form').on('submit', function(e) {
        var newPassword = $('#new_password').val();
        var confirmPassword = $('#confirm_password').val();

        if (newPassword !== confirmPassword) {
            e.preventDefault();
            showNotification('Passwords do not match!', 'danger');
            return false;
        }

        // Check password requirements
        var hasUpperCase = /[A-Z]/.test(newPassword);
        var hasLowerCase = /[a-z]/.test(newPassword);
        var hasNumbers = /\d/.test(newPassword);

        if (!hasUpperCase || !hasLowerCase || !hasNumbers) {
            e.preventDefault();
            showNotification('Password must meet all requirements!', 'warning');
            return false;
        }
    });

    // Enable 2FA button
    $('#enable-2fa-btn').on('click', function() {
        showNotification('Two-factor authentication setup coming soon!', 'info');
    });

    // Load more activity
    $('#load-more-activity').on('click', function() {
        $(this).html('<i class="bi bi-hourglass-split me-2"></i>Loading...');

        // Simulate loading
        setTimeout(() => {
            showNotification('No more activity to load', 'info');
            $(this).html('<i class="bi bi-arrow-clockwise me-2"></i>Load More Activity');
        }, 1000);
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut();
    }, 5000);
});
</script>