<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Profile</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/components.css">
    <link rel="stylesheet" href="/css/profile.css">
</head>

<body>
    <?php require_once APP_ROOT . "/app/view/layouts/header/header.php"; ?>

    <?php
    if ($isOwnProfile) {
        require_once APP_ROOT . "/app/view/layouts/main/showownprofile.php";
    } else {
        require_once APP_ROOT . "/app/view/layouts/main/showprofilebyid.php";
    }
    ?>

    <script src="/js/sidebar.js"></script>
    <script src="/js/addpost.js"></script>
    <script src="/js/postactions.js"></script>
</body>

</html>