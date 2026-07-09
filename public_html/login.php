<?php
$page_title = "Login";
include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-4 text-center">Login</h3>

                    <?php include 'includes/alert.php'; ?>

                    <form action="actions/login-action.php" method="POST">

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            Login
                        </button>

                    </form>

                    <p class="text-center mt-3 mb-0">
                        No account yet?
                        <a href="register.php">Register here</a>
                    </p>

                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>