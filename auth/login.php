<?php

session_start();

require_once '../config/database.php';

$message = '';
$messageType = '';

if (isset($_POST['login'])) {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Server-side Validation
    if (empty($email) || empty($password)) {

        $message = "All fields are required.";
        $messageType = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Invalid email format.";
        $messageType = "danger";

    } else {

        // Prepared Statement
        $stmt = mysqli_prepare(
            $conn,
            "SELECT * FROM users WHERE email = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {

            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {

                // Session Variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                // User Role Session
                $_SESSION['role'] = $user['role'];

                header("Location: ../index.php");
                exit();

            } else {

                $message = "Invalid password.";
                $messageType = "danger";
            }

        } else {

            $message = "User not found.";
            $messageType = "danger";
        }

        mysqli_stmt_close($stmt);
    }
}

require_once '../includes/header.php';
?>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow">

                <div class="card-header text-center bg-primary text-white">
                    <h3>Login</h3>
                </div>

                <div class="card-body">

                    <?php if (!empty($message)) : ?>

                        <div class="alert alert-<?php echo $messageType; ?>">
                            <?php echo $message; ?>
                        </div>

                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                maxlength="100"
                                required>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                minlength="6"
                                required>

                        </div>

                        <button
                            type="submit"
                            name="login"
                            class="btn btn-success w-100">

                            Login

                        </button>

                    </form>

                    <div class="text-center mt-3">

                        <a href="register.php">
                            Create New Account
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once '../includes/footer.php'; ?>