<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/forms.css">
    <link rel="stylesheet" href="/css/login.css">
</head>

<body>
    <div id="loginPage">
        <h2>Welcome Back</h2>

        <?php if (!empty($msg)): ?>
            <div class="alert-msg">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="name@example.com" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="••••••••" required>

            <button type="submit">Login</button>

            <div class="form-footer">
                <p>Don't have an account? <a href="<?= url('/signup') ?>">Sign up</a></p>
            </div>
        </form>
    </div>
</body>

</html>