<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "/opt/lampp/htdocs/website/app/model/profile.php";

// Validate id from URL (router sets $_GET['id'] after matching route)
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($profile_id === null || $profile_id <= 0) {
    // Invalid profile ID - redirect to own profile if logged in, otherwise login
    if (!empty($_SESSION['user_id'])) {
        header('Location: ' . url('/profile/' . (int)$_SESSION['user_id']));
        exit;
    }
    header('Location: ' . url('/login'));
    exit;
}

// Get profile data
$profile = Profile::getById((int)$profile_id);
if (!$profile) {
    http_response_code(404);
    die("Profile not found");
}


$isOwnProfile = !empty($_SESSION["user_id"]) && (int)$_SESSION["user_id"] === (int)$profile_id;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile</title>
    <link rel="stylesheet" href="/website/public/css/main.css">
    <link rel="stylesheet" href="/website/public/css/components.css">
    <link rel="stylesheet" href="/website/public/css/profile.css">
</head>

<body>
    <?php require_once "/opt/lampp/htdocs/website/app/view/layouts/header/header.php"; ?>

    <?php
    if ($isOwnProfile) {
        require_once "/opt/lampp/htdocs/website/app/view/layouts/main/showownprofile.php";
    } else {
        require_once "/opt/lampp/htdocs/website/app/view/layouts/main/showprofilebyid.php";
    }
    ?>

    <script src="/website/public/js/sidebar.js"></script>
    <script src="/website/public/js/addpost.js"></script>
    <script src="/website/public/js/postactions.js"></script>
</body>

</html>