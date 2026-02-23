<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/forms.css">
    <link rel="stylesheet" href="/css/editprofile.css">
    <title>Edit profile</title>
</head>

<body>

    <div id="editprofil">
        <h2>Edit Profile</h2>

        <?php if ($msg): ?>
            <div class="alert"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form action="" method="post" enctype="multipart/form-data">
            <img src="<?= htmlspecialchars($avatarForView) ?>" alt="Current profile picture">

            <label for="img">Change profile picture</label>
            <input type="file" name="img" id="img" accept="image/*">

            <label for="username">Change username</label>
            <input type="text" name="username" id="username"
                value="<?= htmlspecialchars($profile->getUsername()) ?>">

            <label for="bio">Bio</label>
            <textarea name="bio" id="bio" rows="4"><?= htmlspecialchars($profile->getBio()) ?></textarea>

            <label for="email">Change email</label>
            <input type="email" name="email" id="email"
                value="<?= htmlspecialchars($profile->getEmail()) ?>">

            <label for="password">New password</label>
            <input type="password" name="password" id="password">

            <label for="confirm">Confirm password</label>
            <input type="password" name="confirm" id="confirm">

            <button type="submit" name="action" value="update_profile">Save Changes</button>
        </form>
    </div>

</body>

</html>