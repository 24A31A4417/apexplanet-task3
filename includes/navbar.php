<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">

    <div class="container">

        <a class="navbar-brand fw-bold" href="/crud_blog/index.php">
            CRUD Blog
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <div class="navbar-nav ms-auto">

                <a class="nav-link" href="/crud_blog/index.php">
                    Dashboard
                </a>

                <a class="nav-link" href="/crud_blog/posts/create.php">
                    Create Post
                </a>

                <a class="nav-link" href="/crud_blog/posts/view.php">
                    My Posts
                </a>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a class="nav-link text-warning" href="/crud_blog/admin/dashboard.php">
                        Admin Panel
                    </a>
                <?php endif; ?>

                <a class="nav-link text-danger" href="/crud_blog/auth/logout.php">
                    Logout
                </a>

            </div>

        </div>

    </div>

</nav>