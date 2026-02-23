<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Only redirect if this is a GET request (viewing login page), not when processing POST
if ($_SERVER["REQUEST_METHOD"] === "GET" && !empty($_SESSION["user_id"])) {
    header('Location: /profile/' . (int)$_SESSION["user_id"]);
    exit;
}

require_once APP_ROOT . "/config/config.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["email"]) && isset($_POST["password"])) {

        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        if ($email === "" || $password === "") {
            $msg = "Email and password are required.";
        } else {
            try {
                global $pdo;
                $sql = "SELECT id, password_hash FROM profiles WHERE email = ? LIMIT 1";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email]);

                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    $msg = "This email doesn't exist.";
                } else {
                    if (password_verify($password, $user["password_hash"])) {
                        // login success
                        $_SESSION["user_id"] = $user["id"];

                        header('Location: /profile/' . (int)$_SESSION["user_id"]);
                        exit;
                    } else {
                        $msg = "Wrong password.";
                    }
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $msg = "Database error.";
            }
        }
    } else {
        $msg = "Please fill in all fields.";
    }
}

require_once APP_ROOT . "/app/view/loginView.php";
