<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once APP_ROOT . "/config/config.php";
require_once APP_ROOT . "/app/model/Search.php";
require_once APP_ROOT . "/app/model/Friends.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . url('/login'));
    exit;
}

$search_query = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$search_results = [];

if ($search_query !== "") {
    $search = new Search($search_query);
    $search_results = $search->getProfiles();
}

$friends = new Friends($_SESSION['user_id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["sendRequest"])) {
        $friends->sendRequest((int)$_POST["sendRequest"]);
    }
}

$my_friends = $friends->getFriends();
$blocked = $friends->getBlocked();
$outgoing = $friends->getOutgoingInvites();

$my_friend_ids = array_map(function ($p) {
    return $p->getId();
}, $my_friends);
$blocked_ids = array_map(function ($p) {
    return $p->getId();
}, $blocked);
$outgoing_ids = array_map(function ($p) {
    return $p->getId();
}, $outgoing);

require_once APP_ROOT . "/app/view/searchView.php";
