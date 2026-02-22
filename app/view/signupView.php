<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_PATH) ?>/css/main.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_PATH) ?>/css/forms.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(BASE_PATH) ?>/css/signup.css">
    <title>Sign Up</title>
</head>

<body>
    <div id="signupPage">
        <?php if (!empty($msg)): ?>
            <div class="alert-msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form action="" method="POST">

            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required>

            <label for="date_of_birth">Date of birth</label>
            <input type="date" name="date_of_birth" id="date_of_birth">

            <button type="submit">Create Account</button>

        </form>

        <p>
            Already have an account?
            <a href="<?= url('/login') ?>">Login</a>
        </p>
    </div>
</body>

</html>