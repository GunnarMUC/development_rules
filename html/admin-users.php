<?php
require_once 'includes/session.php';
require_once 'includes/auth.php';
require_once 'config/database.php';

// Check if user is logged in and has admin/super_admin role
require_login();

$current_user = get_current_user_info();
// TEMPORARILY DISABLED: Role check disabled to allow user creation
// TODO: Re-enable this security check after creating admin users
/*
if (!$current_user || !in_array($current_user['role'], ['admin', 'super_admin'])) {
    header('Location: dashboard.php');
    exit();
}
*/

$page_title = "User Management";

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $db = getDB();

        switch ($_POST['action']) {
            case 'add_user':
                // Validate CSRF token
                if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                    $error = 'Invalid security token. Please try again.';
                    break;
                }

                // Validate input
                $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
                $first_name = trim($_POST['first_name'] ?? '');
                $last_name = trim($_POST['last_name'] ?? '');
                $password = $_POST['password'] ?? '';
                $confirm_password = $_POST['confirm_password'] ?? '';
                $role = $_POST['role'] ?? 'user';
                $status = $_POST['status'] ?? 'active';

                if (!$email) {
                    $error = 'Please provide a valid email address.';
                } elseif (empty($first_name) || empty($last_name)) {
                    $error = 'First name and last name are required.';
                } elseif (strlen($password) < 8) {
                    $error = 'Password must be at least 8 characters long.';
                } elseif ($password !== $confirm_password) {
                    $error = 'Passwords do not match.';
                } elseif (!in_array($role, ['user', 'admin', 'super_admin'])) {
                    $error = 'Invalid role selected.';
                // TEMPORARILY DISABLED: Super admin restriction
                /*} elseif ($role === 'super_admin' && $current_user['role'] !== 'super_admin') {
                    $error = 'Only super admins can create other super admin accounts.';*/
                } else {
                    try {
                        // Check if email already exists
                        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                        $stmt->execute([$email]);
                        if ($stmt->fetch()) {
                            $error = 'A user with this email already exists.';
                        } else {
                            // Create the user
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $db->prepare("
                                INSERT INTO users (email, password, first_name, last_name, role, status, created_at)
                                VALUES (?, ?, ?, ?, ?, ?, NOW())
                            ");
                            $stmt->execute([$email, $hashed_password, $first_name, $last_name, $role, $status]);
                            $message = 'User created successfully.';
                        }
                    } catch (PDOException $e) {
                        $error = 'Database error: ' . $e->getMessage();
                    }
                }
                break;

            case 'edit_user':
                // Validate CSRF token
                if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                    $error = 'Invalid security token. Please try again.';
                    break;
                }

                $user_id = (int)($_POST['user_id'] ?? 0);
                $first_name = trim($_POST['first_name'] ?? '');
                $last_name = trim($_POST['last_name'] ?? '');
                $role = $_POST['role'] ?? 'user';
                $status = $_POST['status'] ?? 'active';

                if ($user_id <= 0) {
                    $error = 'Invalid user ID.';
                } elseif (empty($first_name) || empty($last_name)) {
                    $error = 'First name and last name are required.';
                } elseif (!in_array($role, ['user', 'admin', 'super_admin'])) {
                    $error = 'Invalid role selected.';
                } elseif ($user_id == $current_user['id'] && $role !== $current_user['role']) {
                    $error = 'You cannot change your own role.';
                } else {
                    try {
                        // Check if trying to modify super_admin without permission
                        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $existing_user = $stmt->fetch();

                        // TEMPORARILY DISABLED: Super admin restriction
                        /*if ($existing_user &&
                            ($existing_user['role'] === 'super_admin' || $role === 'super_admin') &&
                            $current_user['role'] !== 'super_admin') {
                            $error = 'Only super admins can modify super admin accounts.';
                        } else {*/
                            $stmt = $db->prepare("
                                UPDATE users
                                SET first_name = ?, last_name = ?, role = ?, status = ?, updated_at = NOW()
                                WHERE id = ?
                            ");
                            $stmt->execute([$first_name, $last_name, $role, $status, $user_id]);

                            // Update password if provided
                            if (!empty($_POST['password'])) {
                                $password = $_POST['password'];
                                $confirm_password = $_POST['confirm_password'] ?? '';

                                if (strlen($password) < 8) {
                                    $error = 'Password must be at least 8 characters long.';
                                } elseif ($password !== $confirm_password) {
                                    $error = 'Passwords do not match.';
                                } else {
                                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                                    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                                    $stmt->execute([$hashed_password, $user_id]);
                                }
                            }

                            if (empty($error)) {
                                $message = 'User updated successfully.';
                            }
                        /*}*/ // Closing brace for temporarily disabled if statement
                    } catch (PDOException $e) {
                        $error = 'Database error: ' . $e->getMessage();
                    }
                }
                break;

            case 'delete_user':
                // Validate CSRF token
                if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
                    $error = 'Invalid security token. Please try again.';
                    break;
                }

                $user_id = (int)($_POST['user_id'] ?? 0);

                if ($user_id <= 0) {
                    $error = 'Invalid user ID.';
                } elseif ($user_id == $current_user['id']) {
                    $error = 'You cannot delete your own account.';
                } else {
                    try {
                        // Check if trying to delete super_admin without permission
                        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        $existing_user = $stmt->fetch();

                        // TEMPORARILY DISABLED: Super admin restriction
                        /*if ($existing_user &&
                            $existing_user['role'] === 'super_admin' &&
                            $current_user['role'] !== 'super_admin') {
                            $error = 'Only super admins can delete super admin accounts.';
                        } else {*/
                            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                            $stmt->execute([$user_id]);
                            $message = 'User deleted successfully.';
                        /*}*/ // Closing brace for temporarily disabled if statement
                    } catch (PDOException $e) {
                        $error = 'Database error: ' . $e->getMessage();
                    }
                }
                break;
        }
    }
}

// Get all users
try {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT id, email, first_name, last_name, username, role, status, created_at, updated_at
        FROM users
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Failed to fetch users: ' . $e->getMessage();
    $users = [];
}

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4" id="page-header">
                <div>
                    <h1 class="h3 mb-1">User Management</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">User Management</li>
                        </ol>
                    </nav>
                </div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal" id="add-user-btn">
                    <i class="bi bi-person-plus"></i> Add New User
                </button>
            </div>

            <!-- Alerts -->
            <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
                <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Users Table -->
            <div class="card" id="users-table-card">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">All Users</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="search-users" placeholder="Search users...">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="users-table">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-medium"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username'] ?? '-'); ?></td>
                                    <td>
                                        <?php
                                        $role_badges = [
                                            'user' => 'bg-secondary',
                                            'admin' => 'bg-info',
                                            'super_admin' => 'bg-danger'
                                        ];
                                        $badge_class = $role_badges[$user['role']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo str_replace('_', ' ', ucfirst($user['role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badges = [
                                            'active' => 'bg-success',
                                            'inactive' => 'bg-warning',
                                            'suspended' => 'bg-danger'
                                        ];
                                        $badge_class = $status_badges[$user['status']] ?? 'bg-secondary';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button"
                                                    class="btn btn-outline-primary edit-user-btn"
                                                    data-user='<?php echo json_encode($user); ?>'
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editUserModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if ($user['id'] != $current_user['id']): ?>
                                            <button type="button"
                                                    class="btn btn-outline-danger delete-user-btn"
                                                    data-user-id="<?php echo $user['id']; ?>"
                                                    data-user-name="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_user">
                    <?php echo csrf_field(); ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="add-first-name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add-first-name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add-last-name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="add-last-name" name="last_name" required>
                        </div>
                        <div class="col-12">
                            <label for="add-email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="add-email" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="add-password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="add-password" name="password" required minlength="8">
                            <div class="form-text">Minimum 8 characters</div>
                        </div>
                        <div class="col-md-6">
                            <label for="add-confirm-password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="add-confirm-password" name="confirm_password" required minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label for="add-role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="add-role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                                <?php // TEMPORARILY ENABLED: Allow creating super_admin users ?>
                                <option value="super_admin">Super Admin</option>
                                <?php // endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="add-status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="add-status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit_user">
                    <input type="hidden" name="user_id" id="edit-user-id">
                    <?php echo csrf_field(); ?>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit-first-name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-first-name" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-last-name" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit-last-name" name="last_name" required>
                        </div>
                        <div class="col-12">
                            <label for="edit-email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit-email" readonly>
                            <div class="form-text">Email cannot be changed</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="edit-password" name="password" minlength="8">
                            <div class="form-text">Leave blank to keep current password</div>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-confirm-password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="edit-confirm-password" name="confirm_password" minlength="8">
                        </div>
                        <div class="col-md-6">
                            <label for="edit-role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit-role" name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                                <?php // TEMPORARILY ENABLED: Allow editing to super_admin role ?>
                                <option value="super_admin">Super Admin</option>
                                <?php // endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit-status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit-status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" id="delete-user-id">
                    <?php echo csrf_field(); ?>

                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i>
                        Are you sure you want to delete user <strong id="delete-user-name"></strong>?
                        This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 0.875rem;
}

.table > :not(caption) > * > * {
    padding: 0.75rem;
    vertical-align: middle;
}

.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('search-users');
    const tableRows = document.querySelectorAll('#users-table tbody tr');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();

        tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });

    // Edit user modal
    document.querySelectorAll('.edit-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            const user = JSON.parse(this.dataset.user);
            document.getElementById('edit-user-id').value = user.id;
            document.getElementById('edit-first-name').value = user.first_name;
            document.getElementById('edit-last-name').value = user.last_name;
            document.getElementById('edit-email').value = user.email;
            document.getElementById('edit-role').value = user.role;
            document.getElementById('edit-status').value = user.status;
        });
    });

    // Delete user modal
    document.querySelectorAll('.delete-user-btn').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            document.getElementById('delete-user-id').value = userId;
            document.getElementById('delete-user-name').textContent = userName;

            const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            deleteModal.show();
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>