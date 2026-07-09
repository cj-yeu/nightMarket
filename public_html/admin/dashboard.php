<?php
$page_title = "Admin Dashboard";
$base_path = "../";

include '../includes/header.php';
include '../functions/auth.php';
checkAdmin();
include '../includes/navbar.php';
?>

<div class="container py-5">
    <h2 class="mb-4">Admin Dashboard</h2>

    <div class="alert alert-success">
        Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>. You are logged in as Admin.
    </div>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>Manage Users</h5>
                    <p>View, activate, or deactivate client accounts.</p>
                    <a href="manage-users.php" class="btn btn-dark btn-sm">Open</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>Manage Night Markets</h5>
                    <p>Add, edit, and manage night market information.</p>
                    <a href="manage-markets.php" class="btn btn-dark btn-sm">Open</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>Manage Stalls & Food</h5>
                    <p>Manage stalls and must-try food details.</p>
                    <a href="manage-stalls.php" class="btn btn-dark btn-sm">Open</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>Manage Reviews</h5>
                    <p>Approve, reject, or delete user reviews.</p>
                    <a href="manage-reviews.php" class="btn btn-dark btn-sm">Open</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>Visit Plans</h5>
                    <p>View clients' visit planning records.</p>
                    <a href="manage-planners.php" class="btn btn-dark btn-sm">Open</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5>Social Media Data</h5>
                    <p>Manage manually entered social media data.</p>
                    <a href="social-media-data.php" class="btn btn-dark btn-sm">Open</a>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>