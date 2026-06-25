<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$userId = $_SESSION['user_id'];

if (!isset($_GET['id'])) {
    header("Location: view.php");
    exit();
}

$postId = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Get Post Securely
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM posts
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

$result = mysqli_stmt_get_result($stmt);

$post = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$post) {
    die("Post not found.");
}

$message = '';
$messageType = '';

/*
|--------------------------------------------------------------------------
| Update Post
|--------------------------------------------------------------------------
*/

if (isset($_POST['update_post'])) {

    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    // Server-side Validation
    if (empty($title) || empty($content)) {

        $message = "All fields are required.";
        $messageType = "danger";

    } elseif (strlen($title) < 3) {

        $message = "Title must contain at least 3 characters.";
        $messageType = "danger";

    } else {

        $updateStmt = mysqli_prepare(
            $conn,
            "UPDATE posts
             SET title = ?, content = ?
             WHERE id = ?
             AND user_id = ?"
        );

        mysqli_stmt_bind_param(
            $updateStmt,
            "ssii",
            $title,
            $content,
            $postId,
            $userId
        );

        if (mysqli_stmt_execute($updateStmt)) {

            header("Location: view.php");
            exit();

        } else {

            $message = "Failed to update post.";
            $messageType = "danger";
        }

        mysqli_stmt_close($updateStmt);
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

        <div class="card-header bg-warning">
            <h3>Edit Post</h3>
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
                        value="<?php echo htmlspecialchars($post['title']); ?>"
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
                        required><?php echo htmlspecialchars($post['content']); ?></textarea>

                </div>

                <button
                    type="submit"
                    name="update_post"
                    class="btn btn-success">

                    Update Post

                </button>

            </form>

        </div>

    </div>

</div>

<?php require_once '../includes/footer.php'; ?>