<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Search Results</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/components.css">
    <link rel="stylesheet" href="/css/search.css">
</head>

<body>
    <?php require_once APP_ROOT . "/app/view/layouts/header/header.php"; ?>

    <div class="main-container">
        <div class="search-results">
            <h1>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>

            <?php if (empty($search_results)): ?>
                <p class="no-results">No profiles found matching your search.</p>
            <?php else: ?>
                <div class="profiles-grid">
                    <?php foreach ($search_results as $profile): ?>
                        <div class="profile-card">
                            <a href="<?php echo url('/profile/' . (int)$profile['id']); ?>" class="profile-link">
                                <div class="profile-header">
                                    <?php if (!empty($profile['avatar_url'])): ?>
                                        <img src="<?php echo htmlspecialchars(public_url($profile['avatar_url'])); ?>" alt="<?php echo htmlspecialchars($profile['username']); ?>" class="profile-pic">
                                    <?php else: ?>
                                        <div class="profile-pic-placeholder">No Image</div>
                                    <?php endif; ?>
                                </div>
                                <div class="profile-info">
                                    <h3><?php echo htmlspecialchars($profile['username']); ?></h3>
                                    <p><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></p>
                                </div>
                            </a>
                            <?php
                            $is_me = ($_SESSION["user_id"] === $profile["id"]) ? true : false;
                            $is_friend = in_array($profile['id'], $my_friend_ids);
                            $is_blocked = in_array($profile['id'], $blocked_ids);
                            $has_outgoing = in_array($profile['id'], $outgoing_ids);

                            if (!$is_friend && !$is_blocked && !$has_outgoing && !$is_me):
                            ?>
                                <form action="" method="post" class="add-friend-form">
                                    <input type="hidden" name="sendRequest" value="<?= $profile['id'] ?>">
                                    <button type="submit" class="btn-add-friend">Send Request</button>
                                </form>
                            <?php elseif ($is_friend): ?>
                                <p class="status-text">Already friends</p>
                            <?php elseif ($has_outgoing): ?>
                                <p class="status-text">Request pending</p>
                            <?php elseif ($is_blocked): ?>
                                <p class="status-text">Blocked</p>
                            <?php elseif ($is_me): ?>
                                <p class="status-text"></p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>