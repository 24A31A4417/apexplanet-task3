<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/navbar.php';

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? 'editor';

$search = trim($_GET['search'] ?? '');

$limit = 5;
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $limit;

/*
|--------------------------------------------------------------------------
| Count Total Posts
|--------------------------------------------------------------------------
*/

if ($userRole === 'admin') {

    $countSql = "
        SELECT COUNT(*) AS total
        FROM posts
        WHERE title LIKE ?
        OR content LIKE ?
    ";

    $countStmt = mysqli_prepare($conn, $countSql);

    $searchParam = "%$search%";

    mysqli_stmt_bind_param(
        $countStmt,
        "ss",
        $searchParam,
        $searchParam
    );

} else {

    $countSql = "
        SELECT COUNT(*) AS total
        FROM posts
        WHERE user_id = ?
        AND (
            title LIKE ?
            OR content LIKE ?
        )
    ";

    $countStmt = mysqli_prepare($conn, $countSql);

    $searchParam = "%$search%";

    mysqli_stmt_bind_param(
        $countStmt,
        "iss",
        $userId,
        $searchParam,
        $searchParam
    );
}

mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$totalPosts = mysqli_fetch_assoc($countResult)['total'] ?? 0;
mysqli_stmt_close($countStmt);

$totalPages = ceil($totalPosts / $limit);

/*
|--------------------------------------------------------------------------
| Fetch Posts
|--------------------------------------------------------------------------
*/

if ($userRole === 'admin') {

    $sql = "
        SELECT posts.*, users.username
        FROM posts
        INNER JOIN users ON posts.user_id = users.id
        WHERE posts.title LIKE ?
        OR posts.content LIKE ?
        ORDER BY posts.created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ssii",
        $searchParam,
        $searchParam,
        $limit,
        $offset
    );

} else {

    $sql = "
        SELECT posts.*, users.username
        FROM posts
        INNER JOIN users ON posts.user_id = users.id
        WHERE posts.user_id = ?
        AND (
            posts.title LIKE ?
            OR posts.content LIKE ?
        )
        ORDER BY posts.created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "issii",
        $userId,
        $searchParam,
        $searchParam,
        $limit,
        $offset
    );
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            <?php echo ($userRole === 'admin') ? 'All Posts (Admin)' : 'My Posts'; ?>
        </h2>

        <a href="create.php" class="btn btn-success">
            + Create New Post
        </a>

    </div>

    <form method="GET" class="mb-4">

        <div class="input-group">

            <input
                type="text"
                name="search"
                class="form-control"
                placeholder="Search posts..."
                value="<?php echo htmlspecialchars($search); ?>">

            <button class="btn btn-primary" type="submit">
                Search
            </button>

        </div>

    </form>

    <?php if (mysqli_num_rows($result) > 0): ?>

        <?php while ($post = mysqli_fetch_assoc($result)): ?>

            <div class="card shadow-sm mb-3">

                <div class="card-body">

                    <h4 class="fw-bold">
                        <?php echo htmlspecialchars($post['title']); ?>
                    </h4>

                    <p>
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </p>

                    <small class="text-muted d-block mb-2">
                        Created: <?php echo $post['created_at']; ?>
                    </small>

                    <?php if ($userRole === 'admin'): ?>
                        <small class="text-primary d-block mb-3">
                            Author: <?php echo htmlspecialchars($post['username']); ?>
                        </small>
                    <?php endif; ?>

                    <div class="mt-3">

                        <?php if ($userRole === 'admin' || $post['user_id'] == $userId): ?>

                            <a
                                href="edit.php?id=<?php echo $post['id']; ?>"
                                class="btn btn-warning btn-sm">
                                Edit
                            </a>

                            <a
                                href="delete.php?id=<?php echo $post['id']; ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Delete this post?')">
                                Delete
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        <?php endwhile; ?>

        <?php if ($totalPages > 1): ?>

            <nav class="mt-4">

                <ul class="pagination justify-content-center">

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>

                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">

                            <a
                                class="page-link"
                                href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">

                                <?php echo $i; ?>

                            </a>

                        </li>

                    <?php endfor; ?>

                </ul>

            </nav>

        <?php endif; ?>

    <?php else: ?>

        <div class="alert alert-info">
            No posts found.
        </div>

    <?php endif; ?>

</div>

<?php
mysqli_stmt_close($stmt);
require_once '../includes/footer.php';
?>