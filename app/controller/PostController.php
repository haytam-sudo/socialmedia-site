<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once APP_ROOT . "/config/config.php";
require_once APP_ROOT . "/app/model/profile.php";
require_once APP_ROOT . "/app/model/posts.php";

$msg = "";

// Only require login when actually posting; GET (viewing profile) may have session from cookie
if ($_SERVER["REQUEST_METHOD"] === "POST" && empty($_SESSION["user_id"])) {
    header('Location: ' . url('/login'));
    exit;
}

/**
 * Sanitize and process image upload
 * Returns array: ['error' => error message or null, 'imgUrl' => image URL or null]
 */
function processImageUpload()
{
    $result = ['error' => null, 'imgUrl' => null];

    $hasImage = isset($_FILES["img"]) && $_FILES["img"]["error"] !== UPLOAD_ERR_NO_FILE;

    if (!$hasImage) {
        return $result;
    }

    $file = $_FILES["img"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        $result['error'] = "Upload failed (error code: " . $file["error"] . ").";
        return $result;
    }

    if ($file["size"] > 3 * 1024 * 1024) {
        $result['error'] = "Image too large (max 3MB).";
        return $result;
    }

    $allowed = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp",
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file["tmp_name"]);
    finfo_close($finfo);

    if (!isset($allowed[$mime])) {
        $result['error'] = "Only JPG, PNG, or WEBP images are allowed.";
        return $result;
    }

    $uploadDirFs = APP_ROOT . "/public/uploads/";
    if (!is_dir($uploadDirFs)) {
        mkdir($uploadDirFs, 0775, true);
    }

    $filename = "post_" . (int)($_SESSION["user_id"] ?? 0) . "_" . bin2hex(random_bytes(8)) . "." . $allowed[$mime];
    $destFs = $uploadDirFs . $filename;

    if (!move_uploaded_file($file["tmp_name"], $destFs)) {
        $result['error'] = "Failed to save uploaded image.";
        return $result;
    }

    $result['imgUrl'] = "/uploads/" . $filename;
    return $result;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "add_post") {

    $content = $_POST["content"] ?? "";
    $hasImage = isset($_FILES["img"]) && $_FILES["img"]["error"] !== UPLOAD_ERR_NO_FILE;

    if ($content === "" && !$hasImage) {
        $msg = "Please add text or an image.";
    } else {
        // Process image upload
        $imageResult = processImageUpload();
        if ($imageResult['error']) {
            $msg = $imageResult['error'];
        } else {
            $imgUrl = $imageResult['imgUrl'];

            if ($msg === "") {
                try {
                    global $pdo;
                    $title = trim($content) === '' ? 'Post' : mb_substr(strip_tags($content), 0, 200);
                    $stmt = $pdo->prepare(
                        "INSERT INTO posts (profile_id, title, content, img_url) VALUES (?, ?, ?, ?)"
                    );
                    $userId = (int)($_SESSION["user_id"] ?? 0);
                    if ($userId === 0) {
                        $msg = "You must be logged in to post.";
                    } else {
                        $stmt->execute([
                            $userId,
                            $title,
                            $content === "" ? "" : $content,
                            $imgUrl
                        ]);

                        header('Location: ' . url('/profile/' . $userId));
                        exit;
                    }
                } catch (PDOException $e) {
                    error_log("ADD POST ERROR: " . $e->getMessage());
                    $msg = "Database error while creating post.";
                }
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "edit_post") {

    $postId = (int)($_POST["post_id"] ?? 0);
    $content = $_POST["content"] ?? "";

    if ($postId === 0) {
        $msg = "Invalid post ID.";
    } else {
        // Check if user owns this post
        try {
            global $pdo;
            $stmt = $pdo->prepare("SELECT profile_id FROM posts WHERE id = ?");
            $stmt->execute([$postId]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post || $post['profile_id'] !== (int)($_SESSION["user_id"] ?? 0)) {
                $msg = "You don't have permission to edit this post.";
            } elseif ($content === "" && !isset($_FILES["img"])) {
                $msg = "Please add text or an image.";
            } else {
                // Process image upload
                $imageResult = processImageUpload();
                if ($imageResult['error']) {
                    $msg = $imageResult['error'];
                } else {
                    $imgUrl = $imageResult['imgUrl'] ?? $post['img_url'];

                    try {
                        $title = trim($content) === '' ? 'Post' : mb_substr(strip_tags($content), 0, 200);
                        $editPost = Posts::getById($postId);
                        $editPost->editPost($postId, $title, $content, $imgUrl);

                        $userId = (int)($_SESSION["user_id"] ?? 0);
                        header('Location: ' . url('/profile/' . $userId));
                        exit;
                    } catch (PDOException $e) {
                        error_log("EDIT POST ERROR: " . $e->getMessage());
                        $msg = "Database error while editing post.";
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("EDIT POST CHECK ERROR: " . $e->getMessage());
            $msg = "Database error.";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "delete_post") {

    $postId = (int)($_POST["post_id"] ?? 0);

    if ($postId === 0) {
        $msg = "Invalid post ID.";
    } else {
        // Check if user owns this post
        try {
            global $pdo;
            $stmt = $pdo->prepare("SELECT profile_id, img_url FROM posts WHERE id = ?");
            $stmt->execute([$postId]);
            $post = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$post) {
                $msg = "Post not found.";
            } elseif ($post['profile_id'] !== (int)($_SESSION["user_id"] ?? 0)) {
                $msg = "You don't have permission to delete this post.";
            } else {
                try {
                    $deletePost = Posts::getById($postId);
                    if ($deletePost->deletePost($postId)) {
                        // Delete associated image if it exists
                        if ($post['img_url']) {
                            $imagePath = APP_ROOT . "/public" . $post['img_url'];
                            if (file_exists($imagePath)) {
                                unlink($imagePath);
                            }
                        }

                        $userId = (int)($_SESSION["user_id"] ?? 0);
                        header('Location: ' . url('/profile/' . $userId));
                        exit;
                    } else {
                        $msg = "Failed to delete post.";
                    }
                } catch (PDOException $e) {
                    error_log("DELETE POST ERROR: " . $e->getMessage());
                    $msg = "Database error while deleting post.";
                }
            }
        } catch (PDOException $e) {
            error_log("DELETE POST CHECK ERROR: " . $e->getMessage());
            $msg = "Database error.";
        }
    }
}
