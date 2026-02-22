<?php
require_once "/opt/lampp/htdocs/website/app/controller/getPostsbyUser.php";
require_once "/opt/lampp/htdocs/website/app/model/Friends.php";
$msg = $msg ?? "";

// Get profile posts
$posts = getPostsByUser($profile_id);

$defaultAvatar = "/website/public/images/default_avatar.png";
$avatar = $profile->getAvatarUrl() ?: $defaultAvatar;
$username = $profile->getUsername();

// Initialize Friends for relationship checks
$friends = new Friends($_SESSION['user_id']);

// Handle form submission first
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["sendRequest"])) {
        $friends->sendRequest((int)$_POST["sendRequest"]);
    } elseif (!empty($_POST["removeFriend"])) {
        $friends->removeFriend((int)$_POST["removeFriend"]);
    } elseif (!empty($_POST["blockFriend"])) {
        $friends->blockUser((int)$_POST["blockFriend"]);
    }
}

// Load friend data after processing the form
$my_friends = $friends->getFriends();
$blocked = $friends->getBlocked();
$outgoing = $friends->getOutgoingInvites();

// Convert to IDs for easier comparison
$my_friend_ids = array_map(function ($p) {
    return $p->getId();
}, $my_friends);
$blocked_ids = array_map(function ($p) {
    return $p->getId();
}, $blocked);
$outgoing_ids = array_map(function ($p) {
    return $p->getId();
}, $outgoing);
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
                                    <img class="post-img" src="<?= htmlspecialchars($post["img_url"]) ?>" alt="post image">
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
                        <li><a href="<?= url('/') ?>">Home</a></li>
                        <?php if (!empty($_SESSION["user_id"])): ?>
                            <li><a href="<?= url('/profile/' . (int)$_SESSION["user_id"]) ?>">Profile</a></li>
                            <li><a href="<?= url('/explore') ?>">Explore</a></li>
                            <li><a href="<?= url('/notifications') ?>">Notifications</a></li>
                            <li><a href="<?= url('/messages') ?>">Messages</a></li>
                            <li><a href="<?= url('/settings') ?>">Settings</a></li>
                            <li><a href="<?= url('/logout') ?>">Log out</a></li>
                        <?php else: ?>
                            <li><a href="<?= url('/login') ?>">Log in</a></li>
                            <li><a href="<?= url('/signup') ?>">Sign up</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>

            </div>
        </td>
    </tr>
</table>