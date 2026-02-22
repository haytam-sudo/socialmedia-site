<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Friend Requests</title>
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_PATH) ?>/css/main.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_PATH) ?>/css/components.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_PATH) ?>/css/notifications.css">
</head>

<body>
    <?php require_once APP_ROOT . "/app/view/layouts/header/header.php"; ?>

    <div class="main-container">
        <div class="notifications-container">
            <h1>Friend Requests</h1>

            <?php if (empty($incoming_invites)): ?>
                <p class="no-results">No pending friend requests.</p>
            <?php else: ?>
                <div class="profiles-grid">
                    <?php foreach ($incoming_invites as $profile): ?>
                        <div class="profile-card">
                            <a href="<?php echo url('/profile/' . (int)$profile->getId()); ?>" class="profile-link">
                                <div class="profile-header">
                                    <?php if (!empty($profile->getAvatarUrl())): ?>
                                        <img src="<?php echo htmlspecialchars($profile->getAvatarUrl()); ?>" alt="<?php echo htmlspecialchars($profile->getUsername()); ?>" class="profile-pic">
                                    <?php else: ?>
                                        <div class="profile-pic-placeholder">No Image</div>
                                    <?php endif; ?>
                                </div>
                                <div class="profile-info">
                                    <h3><?php echo htmlspecialchars($profile->getUsername()); ?></h3>
                                    <p><?php echo htmlspecialchars($profile->getBio() ?? ''); ?></p>
                                </div>
                            </a>
                            <div class="request-actions">
                                <form action="" method="post" class="inline-form">
                                    <input type="hidden" name="acceptRequest" value="<?= $profile->getId() ?>">
                                    <button type="submit" class="btn-accept">Accept</button>
                                </form>
                                <form action="" method="post" class="inline-form">
                                    <input type="hidden" name="rejectRequest" value="<?= $profile->getId() ?>">
                                    <button type="submit" class="btn-reject">Reject</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>