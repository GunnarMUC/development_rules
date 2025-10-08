<?php
session_start();
require_once 'includes/auth.php';
require_login();

require_once 'classes/Team.php';

$teamModel = new Team();
$userId = $_SESSION['user_id'];
$userTeams = $teamModel->getUserTeams($userId);

// Get current team details if team_id is provided
$currentTeam = null;
$teamMembers = [];
$isAdmin = false;

if (isset($_GET['team_id'])) {
    $teamId = intval($_GET['team_id']);
    $currentTeam = $teamModel->getTeam($teamId);

    if ($currentTeam && $teamModel->isTeamMember($teamId, $userId)) {
        $teamMembers = $teamModel->getMembers($teamId);
        $isAdmin = $teamModel->isTeamAdmin($teamId, $userId);
    } else {
        // Redirect if user is not a member
        header('Location: teams.php');
        exit;
    }
}

$pageTitle = 'Team Management';
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <main class="col-12 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="teams-header">
                <h1 class="h2">Teams</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                        <i class="bi bi-plus-circle"></i> Create Team
                    </button>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card" id="teams-list-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Your Teams</h5>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php if (empty($userTeams)): ?>
                                <div class="list-group-item text-muted" id="no-teams-message">
                                    You are not a member of any teams yet.
                                </div>
                            <?php else: ?>
                                <?php foreach ($userTeams as $team): ?>
                                    <a href="teams.php?team_id=<?php echo $team['id']; ?>"
                                       class="list-group-item list-group-item-action <?php echo ($currentTeam && $currentTeam['id'] == $team['id']) ? 'active' : ''; ?>"
                                       id="team-item-<?php echo $team['id']; ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($team['name']); ?></h6>
                                            <small>
                                                <?php if ($team['role'] === 'admin'): ?>
                                                    <span class="badge bg-primary">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Member</span>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <?php if ($team['description']): ?>
                                            <p class="mb-1 small"><?php echo htmlspecialchars(substr($team['description'], 0, 100)); ?></p>
                                        <?php endif; ?>
                                        <small>Joined: <?php echo date('M d, Y', strtotime($team['joined_at'])); ?></small>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <?php if ($currentTeam): ?>
                        <div class="card" id="team-details-card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($currentTeam['name']); ?></h5>
                                    </div>
                                    <?php if ($isAdmin): ?>
                                        <div class="col-auto">
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editTeamModal">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if ($currentTeam['description']): ?>
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($currentTeam['description'])); ?></p>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <small class="text-muted">
                                        Created by <?php echo htmlspecialchars($currentTeam['creator_first_name'] . ' ' . $currentTeam['creator_last_name']); ?>
                                        on <?php echo date('M d, Y', strtotime($currentTeam['created_at'])); ?>
                                    </small>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3" id="members-header">
                                    <h6>Team Members</h6>
                                    <?php if ($isAdmin): ?>
                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                                            <i class="bi bi-person-plus"></i> Invite Member
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm" id="members-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Joined</th>
                                                <?php if ($isAdmin): ?>
                                                    <th>Actions</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($teamMembers as $member): ?>
                                                <tr id="member-row-<?php echo $member['user_id']; ?>">
                                                    <td>
                                                        <?php
                                                        $name = $member['first_name'] . ' ' . $member['last_name'];
                                                        if (trim($name) === '') {
                                                            $name = $member['username'] ?: 'User #' . $member['user_id'];
                                                        }
                                                        echo htmlspecialchars($name);
                                                        ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                                    <td>
                                                        <?php if ($member['role'] === 'admin'): ?>
                                                            <span class="badge bg-primary">Admin</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">Member</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($member['joined_at'])); ?></td>
                                                    <?php if ($isAdmin && $member['user_id'] != $userId): ?>
                                                        <td>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-secondary change-role-btn"
                                                                        data-user-id="<?php echo $member['user_id']; ?>"
                                                                        data-current-role="<?php echo $member['role']; ?>">
                                                                    <i class="bi bi-arrow-repeat"></i>
                                                                </button>
                                                                <button class="btn btn-outline-danger remove-member-btn"
                                                                        data-user-id="<?php echo $member['user_id']; ?>"
                                                                        data-user-name="<?php echo htmlspecialchars($name); ?>">
                                                                    <i class="bi bi-x"></i>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    <?php elseif ($isAdmin): ?>
                                                        <td>-</td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card" id="no-team-selected">
                            <div class="card-body text-center text-muted py-5">
                                <i class="bi bi-people" style="font-size: 3rem;"></i>
                                <p class="mt-3">Select a team from the list or create a new one to get started.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Create Team Modal -->
<div class="modal fade" id="createTeamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createTeamForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="teamName" class="form-label">Team Name</label>
                        <input type="text" class="form-control" id="teamName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="teamDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="teamDescription" name="description" rows="3"></textarea>
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
<?php if ($currentTeam && $isAdmin): ?>
<div class="modal fade" id="editTeamModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Team</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTeamForm">
                <input type="hidden" name="team_id" value="<?php echo $currentTeam['id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editTeamName" class="form-label">Team Name</label>
                        <input type="text" class="form-control" id="editTeamName" name="name"
                               value="<?php echo htmlspecialchars($currentTeam['name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="editTeamDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="editTeamDescription" name="description" rows="3"><?php echo htmlspecialchars($currentTeam['description']); ?></textarea>
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

<!-- Invite Member Modal -->
<div class="modal fade" id="inviteMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invite Team Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="inviteMemberForm">
                <input type="hidden" name="team_id" value="<?php echo $currentTeam['id']; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="memberEmail" class="form-label">User Email</label>
                        <input type="email" class="form-control" id="memberEmail" name="email" required>
                        <div class="form-text">Enter the email address of the user you want to invite.</div>
                    </div>
                    <div class="mb-3">
                        <label for="memberRole" class="form-label">Role</label>
                        <select class="form-select" id="memberRole" name="role">
                            <option value="member" selected>Member</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Send Invitation</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {
    // Create team form submission
    $('#createTeamForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'api/teams.php',
            method: 'POST',
            data: {
                action: 'create',
                name: $('#teamName').val(),
                description: $('#teamDescription').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.href = 'teams.php?team_id=' + response.team_id;
                } else {
                    alert(response.message || 'Failed to create team');
                }
            },
            error: function() {
                alert('An error occurred while creating the team');
            }
        });
    });

    // Edit team form submission
    $('#editTeamForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'api/teams.php',
            method: 'POST',
            data: {
                action: 'update',
                team_id: $('[name="team_id"]', this).val(),
                name: $('#editTeamName').val(),
                description: $('#editTeamDescription').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Failed to update team');
                }
            },
            error: function() {
                alert('An error occurred while updating the team');
            }
        });
    });

    // Invite member form submission
    $('#inviteMemberForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'api/teams.php',
            method: 'POST',
            data: {
                action: 'invite',
                team_id: $('[name="team_id"]', this).val(),
                email: $('#memberEmail').val(),
                role: $('#memberRole').val()
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Failed to invite member');
                }
            },
            error: function() {
                alert('An error occurred while inviting the member');
            }
        });
    });

    // Change member role
    $('.change-role-btn').on('click', function() {
        var userId = $(this).data('user-id');
        var currentRole = $(this).data('current-role');
        var newRole = currentRole === 'admin' ? 'member' : 'admin';

        if (confirm('Change user role to ' + newRole + '?')) {
            $.ajax({
                url: 'api/teams.php',
                method: 'POST',
                data: {
                    action: 'change_role',
                    team_id: <?php echo $currentTeam ? $currentTeam['id'] : 0; ?>,
                    user_id: userId,
                    role: newRole
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || 'Failed to change role');
                    }
                },
                error: function() {
                    alert('An error occurred while changing the role');
                }
            });
        }
    });

    // Remove member
    $('.remove-member-btn').on('click', function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');

        if (confirm('Remove ' + userName + ' from the team?')) {
            $.ajax({
                url: 'api/teams.php',
                method: 'POST',
                data: {
                    action: 'remove_member',
                    team_id: <?php echo $currentTeam ? $currentTeam['id'] : 0; ?>,
                    user_id: userId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || 'Failed to remove member');
                    }
                },
                error: function() {
                    alert('An error occurred while removing the member');
                }
            });
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>