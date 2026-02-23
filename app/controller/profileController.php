<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once APP_ROOT . "/config/config.php";
require_once APP_ROOT . "/app/model/profile.php";
require_once APP_ROOT . "/app/model/Friends.php";
require_once APP_ROOT . "/app/controller/getPostsbyUser.php";
require_once APP_ROOT . "/app/controller/PostController.php";

if (empty($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($profile_id === null || $profile_id <= 0) {
    if (!empty($_SESSION['user_id'])) {
        header('Location: /profile/' . (int)$_SESSION['user_id']);
        exit;
    }
    header('Location: /login');
    exit;
}

$profile = Profile::getById((int)$profile_id);
if (!$profile) {
    http_response_code(404);
    die("Profile not found");
}

$isOwnProfile = !empty($_SESSION["user_id"]) && (int)$_SESSION["user_id"] === (int)$profile_id;

$msg = $msg ?? "";

$posts = getPostsByUser($profile_id);

if ($isOwnProfile) {
    $friendsModel = new Friends((int)$profile_id);
    $friendsList = $friendsModel->getFriends();
} else {
    $viewerFriends = new Friends((int)$_SESSION['user_id']);

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        if (!empty($_POST["sendRequest"])) {
            $viewerFriends->sendRequest((int)$_POST["sendRequest"]);
            header('Location: /profile/' . $profile_id);
            exit;
        } elseif (!empty($_POST["removeFriend"])) {
            $viewerFriends->removeFriend((int)$_POST["removeFriend"]);
            header('Location: /profile/' . $profile_id);
            exit;
        } elseif (!empty($_POST["blockFriend"])) {
            $viewerFriends->blockUser((int)$_POST["blockFriend"]);
            header('Location: /profile/' . $profile_id);
            exit;
        }
    }

    $my_friends = $viewerFriends->getFriends();
    $blocked = $viewerFriends->getBlocked();
    $outgoing = $viewerFriends->getOutgoingInvites();

    $my_friend_ids = array_map(function ($p) {
        return $p->getId();
    }, $my_friends);
    $blocked_ids = array_map(function ($p) {
        return $p->getId();
    }, $blocked);
    $outgoing_ids = array_map(function ($p) {
        return $p->getId();
    }, $outgoing);
}

require_once APP_ROOT . "/app/view/profileView.php";
