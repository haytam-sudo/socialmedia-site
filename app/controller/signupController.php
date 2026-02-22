<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Only redirect if this is a GET request (viewing signup page), not when processing POST
if ($_SERVER["REQUEST_METHOD"] === "GET" && !empty($_SESSION["user_id"])) {
    header('Location: ' . url('/profile/' . (int)$_SESSION["user_id"]));
    exit;
}
require_once "/opt/lampp/htdocs/website/config/config.php";
require_once "/opt/lampp/htdocs/website/app/model/profile.php";

$msg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST["username"], $_POST["email"], $_POST["password"], $_POST["confirm_password"], $_POST["date_of_birth"])) {
        $msg = "Please fill in all fields.";
        return;
    }

    $username = trim($_POST["username"]);
    $email    = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm  = $_POST["confirm_password"];
    $dob      = $_POST["date_of_birth"];

    if ($username === "" || $email === "" || $password === "" || $confirm === "" || $dob === "") {
        $msg = "All fields are required.";
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email address.";
        return;
    }

    if ($password !== $confirm) {
        $msg = "Passwords do not match.";
        return;
    }
    //TODO:: add passwors verification
    if (strlen($password) < 6) {
        $msg = "Password must be at least 6 characters.";
        return;
    }
    try {
        $birthDate = new DateTime($dob);
        $today     = new DateTime();
        $age       = $today->diff($birthDate)->y;

        if ($age < 18) {
            $msg = "You must be at least 18 years old to sign up.";
            return;
        }
    } catch (Exception $e) {
        $msg = "Invalid date of birth.";
        return;
    }

    try {
        global $pdo;

        // Email unique
        $stmt = $pdo->prepare("SELECT id FROM profiles WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $msg = "This email is already registered.";
            return;
        }

        // Username unique
        $stmt = $pdo->prepare("SELECT id FROM profiles WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $msg = "This username is already taken.";
            return;
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $bio = null;
        $avatar_url = null;
        $date_of_birth = $dob;
        $p = new Profile($username, $email, $password_hash, $date_of_birth, $bio, $avatar_url);
        $newId = $p->addprofile();
        if ($newId) {
            $_SESSION["user_id"] = $newId;
            header('Location: ' . url('/profile/' . (int)$newId));
            exit;
        } else {
            $msg = "Signup failed. Check server logs for details.";
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $msg = "Database error. Please try again later.";
        return;
    }
}
