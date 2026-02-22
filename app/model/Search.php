<?php
require_once APP_ROOT . "/config/config.php";
class Search
{
    private $search;
    public function __construct($search)
    {
        $this->search = $search;
    }
    public function getProfiles()
    {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT * FROM profiles WHERE username LIKE ?");
            $searchTerm = '%' . $this->search . '%';
            $stmt->execute([$searchTerm]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            error_log("Search error: " . $e->getMessage());
            return [];
        }
    }
}
