<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$userId = $_SESSION['user_id'];

if (isset($_GET['id'])) {

    $postId = (int) $_GET['id'];

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM posts
         WHERE id = ?
         AND user_id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $postId,
        $userId
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);
}

header("Location: view.php");
exit();