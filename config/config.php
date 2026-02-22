<?php
try {
    $pdo = new PDO(
        "mysql:host=127.0.0.1;dbname=socialmedia;charset=utf8",
        "root",
        ""
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed: " . $e->getMessage());  // Show full message for debugging
}

/** Base path for the app (used by router and url helper). */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', '/website/public');
}

/** Load router to make url() function available */
require_once __DIR__ . '/../app/router.php';
