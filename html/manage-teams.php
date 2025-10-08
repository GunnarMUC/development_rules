<?php
session_start();
require_once 'includes/auth.php';
require_login();

require_once 'classes/Team.php';
$teamModel = new Team();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? 'user';

// Only admins can manage all teams
if ($userRole !== 'admin') {
    $_SESSION['error'] = "Access denied. Admin privileges required.";
    header('Location: teams.php');
    exit;
}

// Handle team actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $teamId = isset($_POST['team_id']) ? intval($_POST['team_id']) : 0;

    switch ($action) {
        case 'delete':
            if ($teamId > 0) {
                try {
                    $teamModel->deleteTeam($teamId);
                    $_SESSION['success'] = "Team deleted successfully.";
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error deleting team: " . $e->getMessage();
                }
            }
            break;

        case 'edit':
            if ($teamId > 0) {
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');

                if (!empty($name)) {
                    try {
                        $teamModel->updateTeam($teamId, $name, $description);
                        $_SESSION['success'] = "Team updated successfully.";
                    } catch (Exception $e) {
                        $_SESSION['error'] = "Error updating team: " . $e->getMessage();
                    }
                } else {
                    $_SESSION['error'] = "Team name is required.";
                }
            }
            break;

        case 'create':
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (!empty($name)) {
                try {
                    $newTeamId = $teamModel->create($name, $description, $userId);
                    $_SESSION['success'] = "Team created successfully.";
                } catch (Exception $e) {
                    $_SESSION['error'] = "Error creating team: " . $e->getMessage();
                }
            } else {
                $_SESSION['error'] = "Team name is required.";
            }
            break;
    }

    header('Location: manage-teams.php');
    exit;
}

// Get all teams
$allTeams = $teamModel->getAllTeams();

$pageTitle = 'Manage Teams';
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="manage-teams-header">
                <h1 class="h2">Manage Teams</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                        <i class="bi bi-plus-circle"></i> Create New Team
                    </button>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                    <?php echo htmlspecialchars($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
                    <?php echo htmlspecialchars($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="card" id="teams-table-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Team Name</th>
                                    <th>Description</th>
                                    <th>Members</th>
                                    <th>Created By</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($allTeams)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No teams found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($allTeams as $team): ?>
                                        <tr>
                                            <td><?php echo $team['id']; ?></td>
                                            <td><?php echo htmlspecialchars($team['name']); ?></td>
                                            <td><?php echo htmlspecialchars($team['description'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php
                                                $memberCount = $teamModel->getMemberCount($team['id']);
                                                echo $memberCount;
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($team['created_by_name'] ?? 'Unknown'); ?></td>
                                            <td><?php echo date('Y-m-d H:i', strtotime($team['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="teams.php?team_id=<?php echo $team['id']; ?>" class="btn btn-sm btn-info" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-warning"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editTeamModal"
                                                            data-team-id="<?php echo $team['id']; ?>"
                                                            data-team-name="<?php echo htmlspecialchars($team['name']); ?>"
                                                            data-team-description="<?php echo htmlspecialchars($team['description'] ?? ''); ?>"
                                                            title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteTeamModal"
                                                            data-team-id="<?php echo $team['id']; ?>"
                                                            data-team-name="<?php echo htmlspecialchars($team['name']); ?>"
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Create Team Modal -->
<div class="modal fade" id="createTeamModal" tabindex="-1" aria-labelledby="createTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="manage-teams.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTeamModalLabel">Create New Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label for="createTeamName" class="form-label">Team Name</label>
                        <input type="text" class="form-control" id="createTeamName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="createTeamDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="createTeamDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Team Modal -->
<div class="modal fade" id="editTeamModal" tabindex="-1" aria-labelledby="editTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="manage-teams.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTeamModalLabel">Edit Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="team_id" id="editTeamId">
                    <div class="mb-3">
                        <label for="editTeamName" class="form-label">Team Name</label>
                        <input type="text" class="form-control" id="editTeamName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTeamDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editTeamDescription" name="description" rows="3"></textarea>
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

<!-- Delete Team Modal -->
<div class="modal fade" id="deleteTeamModal" tabindex="-1" aria-labelledby="deleteTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="manage-teams.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteTeamModalLabel">Delete Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="team_id" id="deleteTeamId">
                    <p>Are you sure you want to delete the team "<span id="deleteTeamName"></span>"?</p>
                    <p class="text-danger">This action cannot be undone. All team data will be permanently deleted.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Team</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Handle edit team modal
document.addEventListener('DOMContentLoaded', function() {
    var editTeamModal = document.getElementById('editTeamModal');
    editTeamModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var teamId = button.getAttribute('data-team-id');
        var teamName = button.getAttribute('data-team-name');
        var teamDescription = button.getAttribute('data-team-description');

        document.getElementById('editTeamId').value = teamId;
        document.getElementById('editTeamName').value = teamName;
        document.getElementById('editTeamDescription').value = teamDescription;
    });

    // Handle delete team modal
    var deleteTeamModal = document.getElementById('deleteTeamModal');
    deleteTeamModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var teamId = button.getAttribute('data-team-id');
        var teamName = button.getAttribute('data-team-name');

        document.getElementById('deleteTeamId').value = teamId;
        document.getElementById('deleteTeamName').textContent = teamName;
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>