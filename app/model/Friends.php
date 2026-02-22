<?php
require_once "/opt/lampp/htdocs/website/config/config.php";
require_once "/opt/lampp/htdocs/website/app/model/profile.php";

class Friends
{
    private int $user_id;

    private array $friends = [];
    private array $incoming_invites = [];
    private array $outgoing_invites = [];
    private array $blocked = [];

    public function __construct(int $user_id)
    {
        $this->user_id = $user_id;
    }

    /** Helper: turn an array of ids into Profile objects */
    private function idsToProfiles(array $ids): array
    {
        $profiles = [];
        foreach ($ids as $id) {
            $p = Profile::getById((int)$id);
            if ($p) $profiles[] = $p;
        }
        return $profiles;
    }

    /** Friends you have (user_id -> dest_id with relationship='friends') */
    public function getFriends(): array
    {
        try {
            global $pdo;

            $stmt = $pdo->prepare(
                "SELECT dest_id
                 FROM friends
                 WHERE user_id = ? AND relationship = 'friends'"
            );
            $stmt->execute([$this->user_id]);

            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->friends = $this->idsToProfiles($ids);
            return $this->friends;
        } catch (PDOException $e) {
            error_log("GET FRIENDS ERROR: " . $e->getMessage());
            return [];
        }
    }

    public function getIncomingInvites(): array
    {
        try {
            global $pdo;

            $stmt = $pdo->prepare(
                "SELECT user_id
                 FROM friends
                 WHERE dest_id = ? AND relationship = 'pending'"
            );
            $stmt->execute([$this->user_id]);

            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->incoming_invites = $this->idsToProfiles($ids);
            return $this->incoming_invites;
        } catch (PDOException $e) {
            error_log("GET INCOMING INVITES ERROR: " . $e->getMessage());
            return [];
        }
    }

    public function rejectRequest($id)
    {
        try {
            global $pdo;

            $stmt = $pdo->prepare(
                "DELETE FROM friends 
                 WHERE user_id = ? AND dest_id = ? AND relationship = 'pending'"
            );
            $stmt->execute([$id, $this->user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("REJECT REQUEST ERROR: " . $e->getMessage());
            return false;
        }
    }
    public function removeFriend($id)
    {
        try {
            global $pdo;

            // Delete friendship from current user to friend
            $stmt = $pdo->prepare(
                "DELETE FROM friends 
                 WHERE user_id = ? AND dest_id = ? AND relationship = 'friends'"
            );
            $stmt->execute([$this->user_id, $id]);

            // Delete friendship from friend to current user
            $stmt = $pdo->prepare(
                "DELETE FROM friends 
                 WHERE user_id = ? AND dest_id = ? AND relationship = 'friends'"
            );
            $stmt->execute([$id, $this->user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("REMOVE FRIEND ERROR: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Outgoing invites (you invited someone):
     * user_id = me, dest_id = target, relationship='pending'
     */
    public function getOutgoingInvites(): array
    {
        try {
            global $pdo;

            $stmt = $pdo->prepare(
                "SELECT dest_id
                 FROM friends
                 WHERE user_id = ? AND relationship = 'pending'"
            );
            $stmt->execute([$this->user_id]);

            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->outgoing_invites = $this->idsToProfiles($ids);
            return $this->outgoing_invites;
        } catch (PDOException $e) {
            error_log("GET OUTGOING INVITES ERROR: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Blocked users (you blocked them):
     * user_id = me, dest_id = target, relationship='blocked'
     */
    public function getBlocked(): array
    {
        try {
            global $pdo;

            $stmt = $pdo->prepare(
                "SELECT dest_id
                 FROM friends
                 WHERE user_id = ? AND relationship = 'blocked'"
            );
            $stmt->execute([$this->user_id]);

            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $this->blocked = $this->idsToProfiles($ids);
            return $this->blocked;
        } catch (PDOException $e) {
            error_log("GET BLOCKED ERROR: " . $e->getMessage());
            return [];
        }
    }

    /** Add friend: converts a pending invite to a friendship relationship */
    public function addFriend($friend_id)
    {
        try {
            global $pdo;

            // Start transaction
            $pdo->beginTransaction();

            // First, delete the pending invite (both directions)
            $stmt = $pdo->prepare(
                "DELETE FROM friends 
                 WHERE (user_id = ? AND dest_id = ? AND relationship = 'pending')
                    OR (user_id = ? AND dest_id = ? AND relationship = 'pending')"
            );
            $stmt->execute([$friend_id, $this->user_id, $this->user_id, $friend_id]);
            error_log("Deleted pending requests between user $friend_id and user $this->user_id");

            // Insert friendship from current user to friend
            $stmt = $pdo->prepare(
                "INSERT INTO friends (user_id, dest_id, relationship) VALUES (?, ?, 'friends')
                 ON DUPLICATE KEY UPDATE relationship = 'friends'"
            );
            $stmt->execute([$this->user_id, $friend_id]);
            error_log("Inserted/Updated friendship: $this->user_id -> $friend_id");

            // Insert friendship from friend to current user
            $stmt = $pdo->prepare(
                "INSERT INTO friends (user_id, dest_id, relationship) VALUES (?, ?, 'friends')
                 ON DUPLICATE KEY UPDATE relationship = 'friends'"
            );
            $stmt->execute([$friend_id, $this->user_id]);
            error_log("Inserted/Updated friendship: $friend_id -> $this->user_id");

            // Commit transaction
            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("ADD FRIEND ERROR: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send a friend request: check if blocked, check if already pending, then insert
     */
    public function sendRequest($friend_id)
    {
        try {
            global $pdo;

            // Check if target user has blocked us using getBlocked() from target perspective
            $targetFriends = new Friends($friend_id);
            $targetBlocked = $targetFriends->getBlocked();
            $blockedIds = array_map(function ($profile) {
                return $profile->getId();
            }, $targetBlocked);
            if (in_array($this->user_id, $blockedIds)) {
                error_log("Cannot send request: blocked by target user");
                return false;
            }

            // Check if we already blocked this user
            $myBlocked = $this->getBlocked();
            $myBlockedIds = array_map(function ($profile) {
                return $profile->getId();
            }, $myBlocked);
            if (in_array($friend_id, $myBlockedIds)) {
                error_log("Cannot send request: target is blocked");
                return false;
            }

            // Check if request already pending using getOutgoingInvites()
            $myOutgoing = $this->getOutgoingInvites();
            $outgoingIds = array_map(function ($profile) {
                return $profile->getId();
            }, $myOutgoing);
            if (in_array($friend_id, $outgoingIds)) {
                error_log("Request already pending");
                return false;
            }

            // Insert the pending friend request
            $stmt = $pdo->prepare(
                "INSERT INTO friends (user_id, dest_id, relationship) VALUES (?, ?, 'pending')"
            );
            $stmt->execute([$this->user_id, $friend_id]);
            return true;
        } catch (PDOException $e) {
            error_log("SEND REQUEST ERROR: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Block a user: prevents them from sending requests and viewing profile
     */
    public function blockUser($user_id)
    {
        try {
            global $pdo;

            // Remove any existing friendship or pending request
            $stmt = $pdo->prepare(
                "DELETE FROM friends 
                 WHERE (user_id = ? AND dest_id = ?) OR (user_id = ? AND dest_id = ?)"
            );
            $stmt->execute([$this->user_id, $user_id, $user_id, $this->user_id]);

            // Insert the block relationship
            $stmt = $pdo->prepare(
                "INSERT INTO friends (user_id, dest_id, relationship) VALUES (?, ?, 'blocked')"
            );
            $stmt->execute([$this->user_id, $user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("BLOCK USER ERROR: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Unblock a user
     */
    public function unblockUser($user_id)
    {
        try {
            global $pdo;

            $stmt = $pdo->prepare(
                "DELETE FROM friends 
                 WHERE user_id = ? AND dest_id = ? AND relationship = 'blocked'"
            );
            $stmt->execute([$this->user_id, $user_id]);
            return true;
        } catch (PDOException $e) {
            error_log("UNBLOCK USER ERROR: " . $e->getMessage());
            return false;
        }
    }
}
