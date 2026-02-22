<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once APP_ROOT . "/config/config.php";
require_once APP_ROOT . "/app/model/Friends.php";
require_once APP_ROOT . "/app/model/profile.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . url('/login'));
    exit;
}

$friends = new Friends($_SESSION["user_id"]);
$incoming_invites = $friends->getIncomingInvites();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["acceptRequest"])) {
        $friends->addFriend((int)$_POST["acceptRequest"]);
        header('Location: ' . url('/notifications'));
        exit;
    }
    if (!empty($_POST["rejectRequest"])) {
        $friends->rejectRequest((int)$_POST["rejectRequest"]);
        header('Location: ' . url('/notifications'));
        exit;
    }
}

require_once APP_ROOT . "/app/view/notifications.php";
