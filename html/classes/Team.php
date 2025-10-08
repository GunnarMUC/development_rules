<?php
/**
 * Team Model Class
 * Handles team management functionality
 */

require_once __DIR__ . '/Database.php';

class Team {
    private $db;
    private $pdo;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }

    /**
     * Create a new team
     * @param string $name Team name
     * @param string $description Team description
     * @param int $createdBy User ID of team creator
     * @return int|false Team ID or false on failure
     */
    public function create($name, $description, $createdBy) {
        try {
            $this->pdo->beginTransaction();

            // Create the team
            $sql = "INSERT INTO teams (name, description, created_by) VALUES (:name, :description, :created_by)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':created_by' => $createdBy
            ]);

            $teamId = $this->pdo->lastInsertId();

            // Add creator as admin
            $sql = "INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, 'admin')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':team_id' => $teamId,
                ':user_id' => $createdBy
            ]);

            $this->pdo->commit();
            return $teamId;

        } catch (PDOException $e) {
            $this->pdo->rollback();
            error_log("Team creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Invite a user to a team
     * @param int $teamId Team ID
     * @param int $userId User ID to invite
     * @param string $role Role (member or admin)
     * @return bool Success status
     */
    public function invite($teamId, $userId, $role = 'member') {
        try {
            // Check if user is already a member
            $sql = "SELECT id FROM team_members WHERE team_id = :team_id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':team_id' => $teamId, ':user_id' => $userId]);

            if ($stmt->rowCount() > 0) {
                return false; // User already in team
            }

            // Add user to team
            $sql = "INSERT INTO team_members (team_id, user_id, role) VALUES (:team_id, :user_id, :role)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':team_id' => $teamId,
                ':user_id' => $userId,
                ':role' => $role
            ]);

        } catch (PDOException $e) {
            error_log("Team invite failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * User joins a team
     * @param int $teamId Team ID
     * @param int $userId User ID
     * @return bool Success status
     */
    public function join($teamId, $userId) {
        return $this->invite($teamId, $userId, 'member');
    }

    /**
     * Get all members of a team
     * @param int $teamId Team ID
     * @return array Team members
     */
    public function getMembers($teamId) {
        try {
            $sql = "SELECT tm.*, u.email, u.first_name, u.last_name, u.username
                    FROM team_members tm
                    JOIN users u ON tm.user_id = u.id
                    WHERE tm.team_id = :team_id
                    ORDER BY tm.role DESC, u.first_name ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':team_id' => $teamId]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Get team members failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all teams for a user
     * @param int $userId User ID
     * @return array User's teams
     */
    public function getUserTeams($userId) {
        try {
            $sql = "SELECT t.*, tm.role, tm.joined_at
                    FROM teams t
                    JOIN team_members tm ON t.id = tm.team_id
                    WHERE tm.user_id = :user_id
                    ORDER BY t.created_at DESC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll();

        } catch (PDOException $e) {
            error_log("Get user teams failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get team details
     * @param int $teamId Team ID
     * @return array|false Team details or false
     */
    public function getTeam($teamId) {
        try {
            $sql = "SELECT t.*, u.email as creator_email, u.first_name as creator_first_name,
                           u.last_name as creator_last_name
                    FROM teams t
                    JOIN users u ON t.created_by = u.id
                    WHERE t.id = :team_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':team_id' => $teamId]);
            return $stmt->fetch();

        } catch (PDOException $e) {
            error_log("Get team failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update team details
     * @param int $teamId Team ID
     * @param string $name Team name
     * @param string $description Team description
     * @return bool Success status
     */
    public function updateTeam($teamId, $name, $description) {
        try {
            $sql = "UPDATE teams SET name = :name, description = :description WHERE id = :team_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':team_id' => $teamId
            ]);

        } catch (PDOException $e) {
            error_log("Update team failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a member from a team
     * @param int $teamId Team ID
     * @param int $userId User ID
     * @return bool Success status
     */
    public function removeMember($teamId, $userId) {
        try {
            $sql = "DELETE FROM team_members WHERE team_id = :team_id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':team_id' => $teamId, ':user_id' => $userId]);

        } catch (PDOException $e) {
            error_log("Remove member failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update member role
     * @param int $teamId Team ID
     * @param int $userId User ID
     * @param string $role New role
     * @return bool Success status
     */
    public function updateMemberRole($teamId, $userId, $role) {
        try {
            $sql = "UPDATE team_members SET role = :role WHERE team_id = :team_id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                ':role' => $role,
                ':team_id' => $teamId,
                ':user_id' => $userId
            ]);

        } catch (PDOException $e) {
            error_log("Update member role failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is team admin
     * @param int $teamId Team ID
     * @param int $userId User ID
     * @return bool
     */
    public function isTeamAdmin($teamId, $userId) {
        try {
            $sql = "SELECT role FROM team_members WHERE team_id = :team_id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':team_id' => $teamId, ':user_id' => $userId]);
            $result = $stmt->fetch();
            return $result && $result['role'] === 'admin';

        } catch (PDOException $e) {
            error_log("Check team admin failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is team member
     * @param int $teamId Team ID
     * @param int $userId User ID
     * @return bool
     */
    public function isTeamMember($teamId, $userId) {
        try {
            $sql = "SELECT id FROM team_members WHERE team_id = :team_id AND user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':team_id' => $teamId, ':user_id' => $userId]);
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Check team member failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a team
     * @param int $teamId Team ID
     * @return bool Success status
     */
    public function deleteTeam($teamId) {
        try {
            // Team members and tasks will be deleted automatically due to CASCADE
            $sql = "DELETE FROM teams WHERE id = :team_id";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([':team_id' => $teamId]);

        } catch (PDOException $e) {
            error_log("Delete team failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all teams
     * @return array Array of all teams
     */
    public function getAllTeams() {
        try {
            $sql = "SELECT t.*, u.username as created_by_name
                   FROM teams t
                   LEFT JOIN users u ON t.created_by = u.id
                   ORDER BY t.created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get all teams failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get member count for a team
     * @param int $teamId Team ID
     * @return int Number of members
     */
    public function getMemberCount($teamId) {
        try {
            $sql = "SELECT COUNT(*) as count FROM team_members WHERE team_id = :team_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':team_id' => $teamId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get member count failed: " . $e->getMessage());
            return 0;
        }
    }
}