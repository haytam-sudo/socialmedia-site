<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once APP_ROOT . "/config/config.php";
require_once APP_ROOT . "/app/model/profile.php";

// Validate id from URL (router sets $_GET['id'] after matching route)
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
if (!$profile_id) {
    http_response_code(400);
    die("Invalid profile id");
}

// Must be logged in
if (!isset($_SESSION["user_id"])) {
    header('Location: /login');
    exit;
}

// Only allow editing own profile
if ((int)$_SESSION["user_id"] !== (int)$profile_id) {
    http_response_code(403);
    die("Not allowed");
}

// Get profile data
$profile = Profile::getById($profile_id);
if (!$profile) {
    http_response_code(404);
    die("Profile not found");
}

$msg = "";

/* ===== Handle Update ===== */
if ($_SERVER["REQUEST_METHOD"] === "POST" && (($_POST["action"] ?? "") === "update_profile")) {

    $username = trim($_POST["username"] ?? "");
    $bio      = trim($_POST["bio"] ?? "");
    $email    = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm  = $_POST["confirm"] ?? "";

    // Validation
    if ($username === "" || $email === "") {
        $msg = "Username and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email address.";
    } elseif ($password !== "" && $password !== $confirm) {
        $msg = "Passwords do not match.";
    } elseif ($password !== "" && strlen($password) < 6) {
        $msg = "Password must be at least 6 characters.";
    }

    // Check uniqueness
    if ($msg === "") {
        try {
            global $pdo;

            // Email unique 
            $stmt = $pdo->prepare("SELECT id FROM profiles WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && (int)$row["id"] !== (int)$profile_id) {
                $msg = "This email is already in use.";
            }

            // Username unique (except me)
            if ($msg === "") {
                $stmt = $pdo->prepare("SELECT id FROM profiles WHERE username = ? LIMIT 1");
                $stmt->execute([$username]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && (int)$row["id"] !== (int)$profile_id) {
                    $msg = "This username is already taken.";
                }
            }
        } catch (PDOException $e) {
            error_log("EDIT PROFILE UNIQUE CHECK ERROR: " . $e->getMessage());
            $msg = "Database error. Please try again later.";
        }
    }

    // Avatar upload — keep current if no new file
    $avatarUrl = $profile->getAvatarUrl();

    if ($msg === "") {
        $hasImage = isset($_FILES["img"]) && $_FILES["img"]["error"] !== UPLOAD_ERR_NO_FILE;

        if ($hasImage) {
            $file = $_FILES["img"];

            if ($file["error"] !== UPLOAD_ERR_OK) {
                $msg = "Upload failed (error code: " . (int)$file["error"] . ").";
            } elseif ($file["size"] > 3 * 1024 * 1024) {
                $msg = "Image too large (max 3MB).";
            } else {
                $allowed = [
                    "image/jpeg" => "jpg",
                    "image/png"  => "png",
                    "image/webp" => "webp",
                ];

                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_file($finfo, $file["tmp_name"]);
                finfo_close($finfo);

                if (!isset($allowed[$mime])) {
                    $msg = "Only JPG, PNG, or WEBP images are allowed.";
                } else {
                    $uploadDirFs = APP_ROOT . "/public/uploads/";
                    if (!is_dir($uploadDirFs)) {
                        mkdir($uploadDirFs, 0775, true);
                    }

                    $filename = "avatar_" . (int)$profile_id . "_" . bin2hex(random_bytes(8)) . "." . $allowed[$mime];
                    $destFs = $uploadDirFs . $filename;

                    if (!move_uploaded_file($file["tmp_name"], $destFs)) {
                        $msg = "Failed to save uploaded image.";
                    } else {
                        $avatarUrl = "/uploads/" . $filename;
                    }
                }
            }
        }
    }

    // Update DB
    if ($msg === "") {
        $profile->editprofile($username, $bio, $email, $avatarUrl,  $password, $profile_id);
    }

    // Reload profile after update attempt (so form shows latest)
    $profile = Profile::getById($profile_id);
}

$defaultAvatar = "/images/default_avatar.png";
$avatarForView = $profile->getAvatarUrl() ?: $defaultAvatar;

require_once APP_ROOT . "/app/view/editprofilView.php";
