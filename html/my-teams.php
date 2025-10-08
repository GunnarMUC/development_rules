<?php
session_start();
require_once 'includes/auth.php';
require_login();

require_once 'classes/Team.php';

$teamModel = new Team();
$userId = $_SESSION['user_id'];
$userTeams = $teamModel->getUserTeams($userId);

$pageTitle = 'My Teams';
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <main class="col-12 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="my-teams-header">
                <h1 class="h2">My Teams</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createTeamModal">
                        <i class="bi bi-plus-circle"></i> Create Team
                    </button>
                </div>
            </div>

            <div class="row" id="my-teams-container">
                <?php if (empty($userTeams)): ?>
                    <div class="col-12" id="no-teams-message">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> You are not a member of any teams yet. Create a new team or wait to be invited to one.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($userTeams as $team): ?>
                        <div class="col-md-4 mb-4" id="team-card-<?php echo $team['id']; ?>">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($team['name']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($team['description'] ?? 'No description'); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-people"></i> <?php echo $teamModel->getMemberCount($team['id']); ?> members
                                        </small>
                                        <div class="btn-group" role="group">
                                            <a href="teams.php?team_id=<?php echo $team['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <?php if ($teamModel->isTeamAdmin($team['id'], $userId)): ?>
                                                <a href="teams.php?team_id=<?php echo $team['id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="bi bi-gear"></i> Manage
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <small class="text-muted">
                                        Joined <?php echo date('M j, Y', strtotime($team['joined_at'] ?? $team['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- Create Team Modal -->
<div class="modal fade" id="createTeamModal" tabindex="-1" aria-labelledby="createTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="create_team.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTeamModalLabel">Create New Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="teamName" class="form-label">Team Name</label>
                        <input type="text" class="form-control" id="teamName" name="team_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="teamDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="teamDescription" name="team_description" rows="3"></textarea>
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

<?php require_once 'includes/footer.php'; ?>