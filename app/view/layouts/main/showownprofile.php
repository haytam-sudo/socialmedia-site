<?php
require_once "/opt/lampp/htdocs/website/app/controller/getPostsbyUser.php";
require_once "/opt/lampp/htdocs/website/app/controller/PostController.php";
require_once "/opt/lampp/htdocs/website/app/model/Friends.php";

$msg = $msg ?? "";

// Get profile posts
$posts = getPostsByUser($profile_id);

$defaultAvatar = "/website/public/images/default_avatar.png";
$avatar = $profile->getAvatarUrl() ?: $defaultAvatar;
$username = $profile->getUsername();

$friendsModel = new Friends((int)$profile_id);
$friendsList = $friendsModel->getFriends();
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
                <?php if (!empty($_SESSION["user_id"])): ?>
                    <a href="<?= url('/profile/' . (int)$_SESSION["user_id"] . '/edit') ?>"><button class="btn">edit profil</button></a>
                <?php endif; ?>
                <h3>Friends</h3>
                <?php if (!empty($friendsList)): ?>

                    <div class="friend-list">
                        <?php foreach ($friendsList as $friend): ?>

                            <a href="<?= url('/profile/' . (int)$friend->getId()) ?>" class="friend-link">
                                <div class="friend-item">
                                    <?php $fAvatar = $friend->getAvatarUrl() ?: $defaultAvatar; ?>
                                    <img src="<?= htmlspecialchars($fAvatar) ?>" alt="" class="friend-avatar"><?= htmlspecialchars($friend->getUsername()) ?>
                                </div>
                            </a>

                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            </div>

        </td>
        <td width="50%" valign="top">
            <div id="posts">

                <div id="posts">
                    <button id="openAddPost" class="btn">Add post</button>
                    <?php if (!empty($posts)): ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="post">
                                <div class="post-menu">
                                    <button class="post-menu-btn" data-post-id="<?= htmlspecialchars($post["id"]) ?>" aria-label="Post menu">⋯</button>
                                    <div class="post-menu-dropdown" aria-hidden="true">
                                        <button type="button" class="edit-post-btn" data-post-id="<?= htmlspecialchars($post["id"]) ?>">Edit</button>
                                        <button type="button" class="delete-post-btn" data-post-id="<?= htmlspecialchars($post["id"]) ?>">Delete</button>
                                    </div>
                                </div>
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
                        <?php endif; ?>
                        <li><a href="<?= url('/explore') ?>">Explore</a></li>
                        <li><a href="<?= url('/notifications') ?>">Notifications</a></li>
                        <li><a href="<?= url('/messages') ?>">Messages</a></li>
                        <li><a href="<?= url('/settings') ?>">Settings</a></li>
                        <li><a href="<?= url('/logout') ?>">Log out</a></li>
                    </ul>
                </nav>
            </div>

        </td>
    </tr>
</table>

<div id="addPostModal" class="modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Create a post</h3>
            <button type="button" id="closeAddPost" class="icon-btn" aria-label="Close">✕</button>
        </div>

        <form action="" method="post" enctype="multipart/form-data" class="form">
            <input type="hidden" name="action" value="add_post">

            <label for="content">Content</label>
            <textarea name="content" id="content" rows="4" placeholder="What's on your mind?"></textarea>

            <label for="img">Image </label>
            <input type="file" name="img" id="img" accept="image/*">

            <div class="actions">
                <button type="button" id="cancelAddPost" class="btn ghost">Cancel</button>
                <button type="submit" class="btn primary">Post</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Post Modal -->
<div id="editPostModal" class="modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Edit post</h3>
            <button type="button" class="icon-btn close-edit-modal" aria-label="Close">✕</button>
        </div>

        <form action="" method="post" enctype="multipart/form-data" class="form">
            <input type="hidden" name="action" value="edit_post">
            <input type="hidden" name="post_id" id="editPostId">

            <label for="editContent">Content</label>
            <textarea name="content" id="editContent" rows="4" placeholder="What's on your mind?"></textarea>

            <label for="editImg">Image </label>
            <input type="file" name="img" id="editImg" accept="image/*">


            <div class="actions">
                <button type="button" class="btn ghost close-edit-modal">Cancel</button>
                <button type="submit" class="btn primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deletePostModal" class="modal" aria-hidden="true">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Delete post</h3>
            <button type="button" class="icon-btn close-delete-modal" aria-label="Close">✕</button>
        </div>

        <div class="modal-content">
            <p>Are you sure you want to delete this post? This action cannot be undone.</p>
        </div>

        <form action="" method="post" class="form">
            <input type="hidden" name="action" value="delete_post">
            <input type="hidden" name="post_id" id="deletePostId">

            <div class="actions">
                <button type="button" class="btn ghost close-delete-modal">Cancel</button>
                <button type="submit" class="btn danger">Delete</button>
            </div>
        </form>
    </div>
</div>