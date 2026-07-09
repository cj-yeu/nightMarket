<?php
$page_title = "Market Detail";
include 'includes/header.php';
include 'config/db.php';
include 'includes/navbar.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invalid night market.";
    header("Location: market-list.php");
    exit();
}

$market_id = intval($_GET['id']);

$sql = "
    SELECT 
        nm.*,
        GROUP_CONCAT(mods.day_of_week ORDER BY FIELD(mods.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') SEPARATOR ', ') AS operating_days
    FROM night_markets nm
    LEFT JOIN market_operating_days mods ON nm.market_id = mods.market_id
    WHERE nm.market_id = $market_id
    AND nm.status = 'active'
    AND nm.state = 'Selangor'
    GROUP BY nm.market_id
";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    $_SESSION['error'] = "Night market not found.";
    header("Location: market-list.php");
    exit();
}

$market = mysqli_fetch_assoc($result);
?>

<section class="py-5 bg-white">
    <div class="container">

        <?php include 'includes/alert.php'; ?>

        <div class="mb-4">
            <a href="market-list.php" class="btn btn-outline-dark btn-sm">
                ← Back to Night Markets
            </a>
        </div>

        <div class="row g-4">

            <div class="col-md-6">
                <?php if (!empty($market['image'])): ?>
                    <img src="assets/uploads/markets/<?php echo htmlspecialchars($market['image']); ?>" 
                         class="img-fluid rounded shadow-sm w-100" 
                         style="max-height: 400px; object-fit: cover;"
                         alt="<?php echo htmlspecialchars($market['market_name']); ?>">
                <?php else: ?>
                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded" style="height: 350px;">
                        No Image Available
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <h1 class="fw-bold">
                    <?php echo htmlspecialchars($market['market_name']); ?>
                </h1>

                <p class="text-muted mb-3">
                    <?php echo htmlspecialchars($market['area']); ?>, Selangor
                </p>

                <div class="mb-3">
                    <span class="badge bg-success">Active</span>
                    <span class="badge bg-warning text-dark">Selangor Night Market</span>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h5>Market Information</h5>

                        <p class="mb-2">
                            <strong>Address:</strong><br>
                            <?php echo nl2br(htmlspecialchars($market['address'])); ?>
                        </p>

                        <p class="mb-2">
                            <strong>Operating Days:</strong><br>
                            <?php echo $market['operating_days'] ? htmlspecialchars($market['operating_days']) : 'Not set'; ?>
                        </p>

                        <p class="mb-0">
                            <strong>Opening Hours:</strong><br>
                            <?php 
                            if (!empty($market['opening_time']) && !empty($market['closing_time'])) {
                                echo date("h:i A", strtotime($market['opening_time'])) . " - " . date("h:i A", strtotime($market['closing_time']));
                            } else {
                                echo "Not set";
                            }
                            ?>
                        </p>
                    </div>
                </div>

                <a href="planner.php?market_id=<?php echo $market['market_id']; ?>" 
                   class="btn btn-warning">
                    Add to Visit Planner
                </a>

                <a href="review.php?market_id=<?php echo $market['market_id']; ?>" 
                   class="btn btn-outline-dark">
                    Write Review
                </a>
            </div>

        </div>

        <div class="row mt-5">
            <div class="col-md-8">
                <h3 class="fw-bold">About This Night Market</h3>
                <p class="lead">
                    <?php echo nl2br(htmlspecialchars($market['description'])); ?>
                </p>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5>Quick Actions</h5>

                        <div class="d-grid gap-2">
                            <a href="stall-detail.php?market_id=<?php echo $market['market_id']; ?>" 
                               class="btn btn-outline-primary">
                                View Stalls & Food
                            </a>

                            <a href="review.php?market_id=<?php echo $market['market_id']; ?>" 
                               class="btn btn-outline-success">
                                View Reviews
                            </a>

                            <a href="planner.php?market_id=<?php echo $market['market_id']; ?>" 
                               class="btn btn-outline-warning">
                                Plan Visit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>


<?php
$social_sql = "
    SELECT *
    FROM social_media_data
    WHERE market_id = ?
    AND status = 'active'
    ORDER BY created_at DESC
    LIMIT 3
";

$social_stmt = mysqli_prepare($conn, $social_sql);
mysqli_stmt_bind_param($social_stmt, "i", $market_id);
mysqli_stmt_execute($social_stmt);
$social_result = mysqli_stmt_get_result($social_stmt);
?>

<section class="py-5 bg-light">
    <div class="container">
        <h3 class="fw-bold mb-4">Social Media Highlights</h3>

        <?php if ($social_result && mysqli_num_rows($social_result) > 0): ?>

            <div class="row g-4">
                <?php while ($social = mysqli_fetch_assoc($social_result)): ?>
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100">

                            <?php if (!empty($social['image'])): ?>
                                <img src="assets/uploads/social/<?php echo htmlspecialchars($social['image']); ?>" 
                                     class="card-img-top"
                                     style="height: 180px; object-fit: cover;">
                            <?php else: ?>
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 180px;">
                                    No Image
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <span class="badge bg-dark mb-2">
                                    <?php echo htmlspecialchars($social['platform']); ?>
                                </span>

                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($social['post_title']); ?>
                                </h5>

                                <p class="card-text small">
                                    <?php echo htmlspecialchars(substr($social['post_content'], 0, 120)); ?>...
                                </p>

                                <?php if (!empty($social['extracted_keywords'])): ?>
                                    <p class="small mb-2">
                                        <strong>Keywords:</strong><br>
                                        <?php echo htmlspecialchars($social['extracted_keywords']); ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($social['mentioned_food'])): ?>
                                    <p class="small mb-2">
                                        <strong>Mentioned Food:</strong><br>
                                        <?php echo htmlspecialchars($social['mentioned_food']); ?>
                                    </p>
                                <?php endif; ?>

                                <?php if (!empty($social['post_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($social['post_url']); ?>" 
                                       target="_blank" 
                                       class="btn btn-outline-dark btn-sm">
                                        View Source Post
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>

            <div class="alert alert-info">
                No social media highlights added for this night market yet.
            </div>

        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>