<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . (function_exists('url') ? url('/login') : '/login'));
    exit;
}
$page = isset($_GET['page']) ? preg_replace('/[^a-z]/', '', $_GET['page']) : 'page';
$titles = [
    'explore' => 'Explore',
    'notifications' => 'Notifications',
    'messages' => 'Messages',
    'settings' => 'Settings',
];
$title = $titles[$page] ?? 'Page';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_PATH) ?>/css/main.css">
</head>
<body>
    <div style="max-width: 600px; margin: 3rem auto; padding: 2rem; text-align: center;">
        <h1><?= htmlspecialchars($title) ?></h1>
        <p>Coming soon.</p>
        <p><a href="<?= url('/profile/' . (int)$_SESSION['user_id']) ?>">Back to profile</a></p>
    </div>
</body>
</html>
