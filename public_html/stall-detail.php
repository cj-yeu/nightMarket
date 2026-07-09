<?php
$page_title = "Stalls and Must-Try Food";
include 'includes/header.php';
include 'config/db.php';
include 'includes/navbar.php';

if (!isset($_GET['market_id']) || empty($_GET['market_id'])) {
    $_SESSION['error'] = "Invalid night market.";
    header("Location: market-list.php");
    exit();
}

$market_id = intval($_GET['market_id']);

$market_sql = "SELECT * FROM night_markets WHERE market_id = ? AND status = 'active'";
$market_stmt = mysqli_prepare($conn, $market_sql);
mysqli_stmt_bind_param($market_stmt, "i", $market_id);
mysqli_stmt_execute($market_stmt);
$market_result = mysqli_stmt_get_result($market_stmt);

if (mysqli_num_rows($market_result) === 0) {
    $_SESSION['error'] = "Night market not found.";
    header("Location: market-list.php");
    exit();
}

$market = mysqli_fetch_assoc($market_result);

$stall_sql = "
    SELECT *
    FROM stalls
    WHERE market_id = ?
    AND status = 'active'
    ORDER BY stall_name ASC
";

$stall_stmt = mysqli_prepare($conn, $stall_sql);
mysqli_stmt_bind_param($stall_stmt, "i", $market_id);
mysqli_stmt_execute($stall_stmt);
$stalls = mysqli_stmt_get_result($stall_stmt);
?>

<section class="py-5 bg-white">
    <div class="container">

        <div class="mb-4">
            <a href="market-detail.php?id=<?php echo $market_id; ?>" class="btn btn-outline-dark btn-sm">
                ← Back to Market Detail
            </a>
        </div>

        <div class="text-center mb-5">
            <h1 class="fw-bold">Stalls & Must-Try Food</h1>
            <p class="lead text-muted">
                <?php echo htmlspecialchars($market['market_name']); ?>
            </p>
        </div>

        <?php if ($stalls && mysqli_num_rows($stalls) > 0): ?>

            <?php while ($stall = mysqli_fetch_assoc($stalls)): ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">

                        <div class="row g-4">

                            <div class="col-md-4">
                                <?php if (!empty($stall['image'])): ?>
                                    <img src="assets/uploads/stalls/<?php echo htmlspecialchars($stall['image']); ?>" 
                                         class="img-fluid rounded"
                                         style="height: 220px; width: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center rounded" style="height: 220px;">
                                        No Stall Image
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-8">
                                <h3 class="fw-bold">
                                    <?php echo htmlspecialchars($stall['stall_name']); ?>
                                </h3>

                                <span class="badge bg-dark mb-2">
                                    <?php echo htmlspecialchars($stall['category']); ?>
                                </span>

                                <p>
                                    <?php echo nl2br(htmlspecialchars($stall['description'])); ?>
                                </p>

                                <?php
                                $food_sql = "
                                    SELECT *
                                    FROM foods
                                    WHERE stall_id = ?
                                    AND status = 'active'
                                    ORDER BY is_must_try DESC, food_name ASC
                                ";

                                $food_stmt = mysqli_prepare($conn, $food_sql);
                                mysqli_stmt_bind_param($food_stmt, "i", $stall['stall_id']);
                                mysqli_stmt_execute($food_stmt);
                                $foods = mysqli_stmt_get_result($food_stmt);
                                ?>

                                <h5 class="mt-4">Food Items</h5>

                                <?php if ($foods && mysqli_num_rows($foods) > 0): ?>
                                    <div class="row g-3">
                                        <?php while ($food = mysqli_fetch_assoc($foods)): ?>
                                            <div class="col-md-6">
                                                <div class="card h-100 border-0 bg-light">
                                                    <div class="card-body">

                                                        <?php if (!empty($food['image'])): ?>
                                                            <img src="assets/uploads/foods/<?php echo htmlspecialchars($food['image']); ?>" 
                                                                 class="img-fluid rounded mb-3"
                                                                 style="height: 150px; width: 100%; object-fit: cover;">
                                                        <?php endif; ?>

                                                        <h6 class="fw-bold">
                                                            <?php echo htmlspecialchars($food['food_name']); ?>

                                                            <?php if ($food['is_must_try'] == 1): ?>
                                                                <span class="badge bg-warning text-dark">Must Try</span>
                                                            <?php endif; ?>
                                                        </h6>

                                                        <p class="small mb-2">
                                                            <?php echo nl2br(htmlspecialchars($food['description'])); ?>
                                                        </p>

                                                        <?php if (!empty($food['price_range'])): ?>
                                                            <p class="mb-0">
                                                                <strong>Price:</strong>
                                                                <?php echo htmlspecialchars($food['price_range']); ?>
                                                            </p>
                                                        <?php endif; ?>

                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No food items added for this stall yet.</p>
                                <?php endif; ?>

                            </div>

                        </div>

                    </div>
                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="alert alert-info text-center">
                No stalls added for this night market yet.
            </div>

        <?php endif; ?>

    </div>
</section>

<?php include 'includes/footer.php'; ?>