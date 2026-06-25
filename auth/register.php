<?php

require_once '../config/database.php';

$message = '';
$messageType = '';

if (isset($_POST['register'])) {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Server-side Validation
    if (
        empty($username) ||
        empty($email) ||
        empty($password) ||
        empty($confirmPassword)
    ) {

        $message = "All fields are required.";
        $messageType = "danger";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Invalid email format.";
        $messageType = "danger";

    } elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $messageType = "danger";

    } elseif ($password !== $confirmPassword) {

        $message = "Passwords do not match.";
        $messageType = "danger";

    } else {

        // Check Existing Email (Prepared Statement)
        $checkStmt = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ?"
        );

        mysqli_stmt_bind_param(
            $checkStmt,
            "s",
            $email
        );

        mysqli_stmt_execute($checkStmt);

        $checkResult = mysqli_stmt_get_result($checkStmt);

        if (mysqli_num_rows($checkResult) > 0) {

            $message = "Email already registered.";
            $messageType = "warning";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $role = "editor";

            // Insert User (Prepared Statement)
            $insertStmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (username, email, password, role)
                VALUES (?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $insertStmt,
                "ssss",
                $username,
                $email,
                $hashedPassword,
                $role
            );

            if (mysqli_stmt_execute($insertStmt)) {

                $message = "Registration Successful!";
                $messageType = "success";

            } else {

                $message = "Registration Failed!";
                $messageType = "danger";
            }

            mysqli_stmt_close($insertStmt);
        }

        mysqli_stmt_close($checkStmt);
    }
}

require_once '../includes/header.php';
?>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-6">

            <div class="card shadow">

                <div class="card-header text-center bg-primary text-white">
                    <h3>Create Account</h3>
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
                                Username
                            </label>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                minlength="3"
                                maxlength="50"
                                required>

                        </div>

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

                        <div class="mb-3">

                            <label class="form-label">
                                Confirm Password
                            </label>

                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control"
                                minlength="6"
                                required>

                        </div>

                        <button
                            type="submit"
                            name="register"
                            class="btn btn-primary w-100">

                            Register

                        </button>

                    </form>

                    <div class="text-center mt-3">

                        <a href="login.php">
                            Already have an account? Login
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once '../includes/footer.php'; ?>