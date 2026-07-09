<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo $base_path; ?>index.php">
            Night Market System
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_path; ?>index.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_path; ?>market-list.php">Night Markets</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $base_path; ?>planner.php">Visit Planner</a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $base_path; ?>admin/dashboard.php">Admin Dashboard</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>profile.php">
                            Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-outline-light btn-sm ms-lg-2" href="<?php echo $base_path; ?>logout.php">
                            Logout
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $base_path; ?>login.php">Login</a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-warning btn-sm ms-lg-2" href="<?php echo $base_path; ?>register.php">Register</a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>