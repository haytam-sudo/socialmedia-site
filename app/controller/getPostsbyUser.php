<?php
require_once APP_ROOT . "/config/config.php";

function getPostsByUser($profileId)
{
    try {
        global $pdo;

        $sql = "SELECT * FROM posts WHERE profile_id = ? ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$profileId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log($e->getMessage());
        return [];
    }
}
