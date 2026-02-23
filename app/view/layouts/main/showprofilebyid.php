<?php
$msg = $msg ?? "";

$defaultAvatar =  "/images/default_avatar.png";
$avatar = public_url($profile->getAvatarUrl() ?: $defaultAvatar);
$username = $profile->getUsername();
?>


<?php if ($msg): ?>
    <div class="alert"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<table width="100%" border="0" cellpadding="10" cellspacing="10">
    <tr>
        <td width="25%" valign="top">

            <div id="profileinfo">
                <img src="<?= htmlspecialchars($avatar) ?>" alt="avatar" class="profile-avatar">
                <h2 class="profile-username"><?= htmlspecialchars($username) ?></h2>
                <p class="profile-bio"><?= nl2br(htmlspecialchars($profile->getBio())) ?></p>
                <?php
                $is_friend = in_array($profile_id, $my_friend_ids);
                $is_blocked = in_array($profile_id, $blocked_ids);
                $has_outgoing = in_array($profile_id, $outgoing_ids);

                if (!$is_friend && !$is_blocked && !$has_outgoing && !$isOwnProfile):
                ?>
                    <form action="" method="post" class="add-friend-form">
                        <input type="hidden" name="sendRequest" value="<?= $profile_id ?>">
                        <button type="submit" class="btn-add-friend">Send Request</button>
                    </form>
                <?php elseif ($is_friend): ?>
                    <form action="" method="post" class="add-friend-form">
                        <input type="hidden" name="removeFriend" value="<?= $profile_id ?>">
                        <button type="submit" class="btn-add-friend">Remove Friend</button>
                    </form>
                    <form action="" method="post" class="add-friend-form">
                        <input type="hidden" name="blockFriend" value="<?= $profile_id ?>">
                        <button type="submit" class="btn-add-friend danger">Block</button>
                    </form>
                <?php elseif ($has_outgoing): ?>
                    <p class="status-text">Request pending</p>
                <?php elseif ($is_blocked): ?>
                    <p class="status-text">Blocked</p>
                <?php elseif ($isOwnProfile): ?>
                    <p class="status-text"></p>
                <?php endif; ?>
            </div>

        </td>
        <td width="50%" valign="top">
            <div id="posts">
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <div class="post">
                            <div class="info">
                                <img src="<?= htmlspecialchars($avatar) ?>" alt="profile picture" class="avatar">
                                <div class="meta">
                                    <p class="username"><?= htmlspecialchars($username) ?></p>
                                    <p class="date"><?= htmlspecialchars($post["created_at"]) ?></p>
                                </div>
                            </div>

                            <div class="content">
                                <p><?= nl2br(htmlspecialchars($post["content"])) ?></p>
                                <?php if (!empty($post["img_url"])): ?>
                                    <img class="post-img" src="<?= htmlspecialchars(public_url($post["img_url"])) ?>" alt="post image">
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty">No posts to show</p>
                <?php endif; ?>
            </div>
        </td>
        <td width="25%" valign="top">

            <div id="menu">
                <h2>Menu</h2>
                <nav>
                    <ul>
                        <li><a href="/">Home</a></li>
                        <?php if (!empty($_SESSION["user_id"])): ?>
                            <li><a href="<?= '/profile/' . (int)$_SESSION["user_id"] ?>">Profile</a></li>
                            <li><a href="/explore">Explore</a></li>
                            <li><a href="/notifications">Notifications</a></li>
                            <li><a href="/messages">Messages</a></li>
                            <li><a href="/settings">Settings</a></li>
                            <li><a href="/logout">Log out</a></li>
                        <?php else: ?>
                            <li><a href="/login">Log in</a></li>
                            <li><a href="/signup">Sign up</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>

            </div>
        </td>
    </tr>
</table>