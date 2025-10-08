<?php
session_start();
require_once 'includes/auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

require_once 'classes/Team.php';

$teamModel = new Team();
$userId = $_SESSION['user_id'];

// Get all teams the user belongs to
$userTeams = $teamModel->getUserTeams($userId);

// Collect all team members from all teams
$allTeamMembers = [];
foreach ($userTeams as $team) {
    $members = $teamModel->getMembers($team['id']);
    foreach ($members as $member) {
        // Add team information to each member
        $member['team_name'] = $team['name'];
        $member['team_id'] = $team['id'];
        $allTeamMembers[] = $member;
    }
}

$pageTitle = 'Team Members';
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php require_once 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom" id="team-members-header">
                <h1 class="h2">Team Members</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="teams.php" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Teams
                    </a>
                </div>
            </div>

            <?php if (empty($allTeamMembers)): ?>
                <div class="alert alert-info" id="no-members-alert">
                    <i class="bi bi-info-circle"></i> You are not part of any teams yet.
                    <a href="teams.php">Join or create a team</a> to see team members.
                </div>
            <?php else: ?>
                <div class="card" id="members-card">
                    <div class="card-header" id="members-card-header">
                        <h5 class="mb-0">All Team Members</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Team</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($allTeamMembers as $member): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <span class="avatar-title rounded-circle bg-primary text-white">
                                                            <?php echo strtoupper(substr($member['name'], 0, 1)); ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <?php echo htmlspecialchars($member['name']); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                                            <td>
                                                <a href="teams.php?team_id=<?php echo $member['team_id']; ?>">
                                                    <?php echo htmlspecialchars($member['team_name']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php if ($member['role'] === 'admin'): ?>
                                                    <span class="badge bg-primary">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Member</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($member['joined_at'])); ?></td>
                                            <td>
                                                <a href="teams.php?team_id=<?php echo $member['team_id']; ?>"
                                                   class="btn btn-sm btn-outline-primary" title="View Team">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    display: inline-block;
}

.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 500;
}
</style>

<?php require_once 'includes/footer.php'; ?>