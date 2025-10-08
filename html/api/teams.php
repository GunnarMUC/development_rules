<?php
session_start();
require_once '../includes/auth.php';
requireLogin();

require_once '../classes/Team.php';
require_once '../classes/Database.php';

header('Content-Type: application/json');

$teamModel = new Team();
$db = Database::getInstance();
$pdo = $db->getConnection();
$userId = $_SESSION['user_id'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                throw new Exception('Team name is required');
            }

            $teamId = $teamModel->create($name, $description, $userId);

            if ($teamId) {
                echo json_encode(['success' => true, 'team_id' => $teamId]);
            } else {
                throw new Exception('Failed to create team');
            }
            break;

        case 'update':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $teamId = intval($_POST['team_id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (!$teamId || empty($name)) {
                throw new Exception('Team ID and name are required');
            }

            // Check if user is admin
            if (!$teamModel->isTeamAdmin($teamId, $userId)) {
                throw new Exception('You do not have permission to edit this team');
            }

            if ($teamModel->updateTeam($teamId, $name, $description)) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to update team');
            }
            break;

        case 'invite':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $teamId = intval($_POST['team_id'] ?? 0);
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'member';

            if (!$teamId || empty($email)) {
                throw new Exception('Team ID and email are required');
            }

            // Check if user is admin
            if (!$teamModel->isTeamAdmin($teamId, $userId)) {
                throw new Exception('You do not have permission to invite members to this team');
            }

            // Find user by email
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new Exception('User with this email not found');
            }

            if ($teamModel->invite($teamId, $user['id'], $role)) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('User is already a member of this team');
            }
            break;

        case 'change_role':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $teamId = intval($_POST['team_id'] ?? 0);
            $targetUserId = intval($_POST['user_id'] ?? 0);
            $newRole = $_POST['role'] ?? '';

            if (!$teamId || !$targetUserId || !in_array($newRole, ['member', 'admin'])) {
                throw new Exception('Invalid parameters');
            }

            // Check if user is admin
            if (!$teamModel->isTeamAdmin($teamId, $userId)) {
                throw new Exception('You do not have permission to change member roles');
            }

            if ($teamModel->updateMemberRole($teamId, $targetUserId, $newRole)) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to update member role');
            }
            break;

        case 'remove_member':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $teamId = intval($_POST['team_id'] ?? 0);
            $targetUserId = intval($_POST['user_id'] ?? 0);

            if (!$teamId || !$targetUserId) {
                throw new Exception('Invalid parameters');
            }

            // Check if user is admin
            if (!$teamModel->isTeamAdmin($teamId, $userId)) {
                throw new Exception('You do not have permission to remove members');
            }

            if ($targetUserId == $userId) {
                throw new Exception('You cannot remove yourself from the team');
            }

            if ($teamModel->removeMember($teamId, $targetUserId)) {
                echo json_encode(['success' => true]);
            } else {
                throw new Exception('Failed to remove member');
            }
            break;

        case 'list':
            $teams = $teamModel->getUserTeams($userId);
            echo json_encode(['success' => true, 'teams' => $teams]);
            break;

        case 'get':
            $teamId = intval($_GET['team_id'] ?? 0);

            if (!$teamId) {
                throw new Exception('Team ID is required');
            }

            if (!$teamModel->isTeamMember($teamId, $userId)) {
                throw new Exception('You are not a member of this team');
            }

            $team = $teamModel->getTeam($teamId);
            $members = $teamModel->getMembers($teamId);

            echo json_encode([
                'success' => true,
                'team' => $team,
                'members' => $members
            ]);
            break;

        case 'switch_team':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Invalid request method');
            }

            $teamId = intval($_POST['team_id'] ?? 0);

            if (!$teamId) {
                throw new Exception('Team ID is required');
            }

            // Verify user is a member of this team
            if (!$teamModel->isTeamMember($teamId, $userId)) {
                throw new Exception('You are not a member of this team');
            }

            // Store the current team in session
            $_SESSION['current_team_id'] = $teamId;

            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}