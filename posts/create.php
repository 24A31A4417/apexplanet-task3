<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$message = '';
$messageType = '';

if (isset($_POST['create_post'])) {

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    $userId = $_SESSION['user_id'];

    // Server-side Validation
    if (empty($title) || empty($content)) {

        $message = "All fields are required.";
        $messageType = "danger";

    } elseif (strlen($title) < 3) {

        $message = "Title must contain at least 3 characters.";
        $messageType = "danger";

    } else {

        // Prepared Statement
        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO posts (user_id, title, content)
             VALUES (?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "iss",
            $userId,
            $title,
            $content
        );

        if (mysqli_stmt_execute($stmt)) {

            $message = "Post created successfully!";
            $messageType = "success";

        } else {

            $message = "Failed to create post.";
            $messageType = "danger";
        }

        mysqli_stmt_close($stmt);
    }
}

require_once '../includes/header.php';
require_once '../includes/navbar.php';
?>

<div class="container mt-4">

    <?php if (!empty($message)) : ?>

        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>

    <?php endif; ?>

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h3>Create Post</h3>
        </div>

        <div class="card-body">

            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">
                        Title
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        minlength="3"
                        maxlength="255"
                        required>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Content
                    </label>

                    <textarea
                        name="content"
                        rows="6"
                        class="form-control"
                        minlength="10"
                        required></textarea>

                </div>

                <button
                    type="submit"
                    name="create_post"
                    class="btn btn-primary">

                    Publish Post

                </button>

            </form>

        </div>

    </div>

</div>

<?php require_once '../includes/footer.php'; ?>