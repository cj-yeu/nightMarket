<?php
$page_title = "Reviews";
include 'includes/header.php';
include 'config/db.php';
include 'includes/navbar.php';

if (!isset($_GET['market_id']) || empty($_GET['market_id'])) {
    $_SESSION['error'] = "Invalid night market.";
    header("Location: market-list.php");
    exit();
}

$market_id = intval($_GET['market_id']);

$market_sql = "SELECT * FROM night_markets WHERE market_id = ? AND status = 'active' AND state = 'Selangor'";
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

$avg_sql = "
    SELECT 
        AVG(rating) AS average_rating,
        COUNT(*) AS total_reviews
    FROM reviews
    WHERE market_id = ?
    AND status = 'approved'
";
$avg_stmt = mysqli_prepare($conn, $avg_sql);
mysqli_stmt_bind_param($avg_stmt, "i", $market_id);
mysqli_stmt_execute($avg_result = $avg_stmt);
$avg_result = mysqli_stmt_get_result($avg_stmt);
$rating_data = mysqli_fetch_assoc($avg_result);

$average_rating = $rating_data['average_rating'] ? number_format($rating_data['average_rating'], 1) : "No rating yet";
$total_reviews = $rating_data['total_reviews'];

$review_sql = "
    SELECT 
        r.*,
        u.full_name
    FROM reviews r
    INNER JOIN users u ON r.user_id = u.user_id
    WHERE r.market_id = ?
    AND r.status = 'approved'
    ORDER BY r.created_at DESC
";
$review_stmt = mysqli_prepare($conn, $review_sql);
mysqli_stmt_bind_param($review_stmt, "i", $market_id);
mysqli_stmt_execute($review_stmt);
$reviews = mysqli_stmt_get_result($review_stmt);
?>

<section class="py-5 bg-white">
    <div class="container">

        <div class="mb-4">
            <a href="market-detail.php?id=<?php echo $market_id; ?>" class="btn btn-outline-dark btn-sm">
                ← Back to Market Detail
            </a>
        </div>

        <?php include 'includes/alert.php'; ?>

        <div class="row g-4">

            <div class="col-md-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="fw-bold mb-2">
                            <?php echo htmlspecialchars($market['market_name']); ?>
                        </h2>

                        <p class="text-muted mb-3">
                            <?php echo htmlspecialchars($market['area']); ?>, Selangor
                        </p>

                        <div class="p-3 bg-light rounded">
                            <h5 class="mb-1">Average Rating</h5>

                            <h2 class="fw-bold mb-0">
                                <?php echo $average_rating; ?>
                                <?php if ($total_reviews > 0): ?>
                                    <span class="text-warning">★</span>
                                <?php endif; ?>
                            </h2>

                            <small class="text-muted">
                                Based on <?php echo $total_reviews; ?> approved review(s)
                            </small>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <?php if ($_SESSION['role'] === 'client'): ?>
                        <div class="card shadow-sm">
                            <div class="card-body p-4">
                                <h4 class="mb-3">Write a Review</h4>

                                <form action="actions/review-action.php" method="POST" enctype="multipart/form-data">

                                    <input type="hidden" name="action" value="create">
                                    <input type="hidden" name="market_id" value="<?php echo $market_id; ?>">

                                    <div class="mb-3">
                                        <label class="form-label">Rating</label>
                                        <select name="rating" class="form-select" required>
                                            <option value="">-- Select Rating --</option>
                                            <option value="5">5 - Excellent</option>
                                            <option value="4">4 - Good</option>
                                            <option value="3">3 - Average</option>
                                            <option value="2">2 - Poor</option>
                                            <option value="1">1 - Very Poor</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Visit Date</label>
                                        <input type="date" name="visit_date" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Comment</label>
                                        <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience..." required></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Upload Review Photos</label>
                                        <input type="file" name="review_images[]" class="form-control" accept="image/*" multiple>
                                        <small class="text-muted">
                                            You can upload more than one photo.
                                        </small>
                                    </div>

                                    <button type="submit" class="btn btn-warning w-100">
                                        Submit Review
                                    </button>

                                    <p class="small text-muted mt-2 mb-0">
                                        Your review will be shown after admin approval.
                                    </p>

                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Admin account cannot submit client reviews.
                        </div>
                    <?php endif; ?>

                <?php else: ?>

                    <div class="alert alert-warning">
                        Please <a href="login.php">login</a> as client to write a review.
                    </div>

                <?php endif; ?>
            </div>

            <div class="col-md-7">
                <h3 class="fw-bold mb-4">User Reviews</h3>

                <?php if ($reviews && mysqli_num_rows($reviews) > 0): ?>

                    <?php while ($review = mysqli_fetch_assoc($reviews)): ?>

                        <div class="card shadow-sm mb-3">
                            <div class="card-body">

                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="mb-0">
                                            <?php echo htmlspecialchars($review['full_name']); ?>
                                        </h5>

                                        <small class="text-muted">
                                            <?php echo date("d M Y", strtotime($review['created_at'])); ?>

                                            <?php if (!empty($review['visit_date'])): ?>
                                                · Visited on <?php echo date("d M Y", strtotime($review['visit_date'])); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>

                                    <div class="text-warning fw-bold">
                                        <?php echo str_repeat("★", $review['rating']); ?>
                                        <span class="text-muted">
                                            <?php echo str_repeat("☆", 5 - $review['rating']); ?>
                                        </span>
                                    </div>
                                </div>

                                <p class="mb-3">
                                    <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                </p>

                                <?php
                                $image_sql = "SELECT * FROM review_images WHERE review_id = ?";
                                $image_stmt = mysqli_prepare($conn, $image_sql);
                                mysqli_stmt_bind_param($image_stmt, "i", $review['review_id']);
                                mysqli_stmt_execute($image_stmt);
                                $images = mysqli_stmt_get_result($image_stmt);
                                ?>

                                <?php if ($images && mysqli_num_rows($images) > 0): ?>
                                    <div class="row g-2">
                                        <?php while ($image = mysqli_fetch_assoc($images)): ?>
                                            <div class="col-4 col-md-3">
                                                <img src="assets/uploads/reviews/<?php echo htmlspecialchars($image['image']); ?>"
                                                     class="img-fluid rounded"
                                                     style="height: 90px; width: 100%; object-fit: cover;">
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="alert alert-info">
                        No approved reviews yet. Be the first to share your experience.
                    </div>

                <?php endif; ?>
            </div>

        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>