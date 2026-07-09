<?php
$page_title = "Manage Reviews";
$base_path = "../";

include '../includes/header.php';
include '../functions/auth.php';
checkAdmin();
include '../config/db.php';
include '../includes/navbar.php';

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$where = "";
if (!empty($status_filter)) {
    $safe_status = mysqli_real_escape_string($conn, $status_filter);
    $where = "WHERE r.status = '$safe_status'";
}

$sql = "
    SELECT 
        r.*,
        u.full_name,
        u.email,
        nm.market_name,
        nm.area
    FROM reviews r
    INNER JOIN users u ON r.user_id = u.user_id
    INNER JOIN night_markets nm ON r.market_id = nm.market_id
    $where
    ORDER BY r.created_at DESC
";

$result = mysqli_query($conn, $sql);
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Reviews</h2>

        <a href="dashboard.php" class="btn btn-outline-dark">
            Back to Dashboard
        </a>
    </div>

    <?php include '../includes/alert.php'; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="manage-reviews.php" class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Reviews</option>
                        <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo ($status_filter === 'approved') ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo ($status_filter === 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-warning w-100">Filter</button>
                </div>

                <div class="col-md-2">
                    <a href="manage-reviews.php" class="btn btn-outline-secondary w-100">Clear</a>
                </div>

            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>User</th>
                            <th>Night Market</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Visit Date</th>
                            <th>Status</th>
                            <th width="230">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($review = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($review['full_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($review['email']); ?>
                                        </small>
                                    </td>

                                    <td>
                                        <strong><?php echo htmlspecialchars($review['market_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($review['area']); ?>, Selangor
                                        </small>
                                    </td>

                                    <td>
                                        <span class="text-warning fw-bold">
                                            <?php echo str_repeat("★", $review['rating']); ?>
                                        </span>
                                        <br>
                                        <small><?php echo $review['rating']; ?>/5</small>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars(substr($review['comment'], 0, 100)); ?>...
                                        <br>
                                        <small class="text-muted">
                                            Submitted: <?php echo date("d M Y", strtotime($review['created_at'])); ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?php 
                                        echo !empty($review['visit_date']) 
                                            ? date("d M Y", strtotime($review['visit_date'])) 
                                            : '<span class="text-muted">Not set</span>'; 
                                        ?>
                                    </td>

                                    <td>
                                        <?php if ($review['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($review['status'] === 'approved'): ?>
                                            <span class="badge bg-success">Approved</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Rejected</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($review['status'] !== 'approved'): ?>
                                            <a href="../actions/review-action.php?action=approve&id=<?php echo $review['review_id']; ?>" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Approve this review?');">
                                                Approve
                                            </a>
                                        <?php endif; ?>

                                        <?php if ($review['status'] !== 'rejected'): ?>
                                            <a href="../actions/review-action.php?action=reject&id=<?php echo $review['review_id']; ?>" 
                                               class="btn btn-sm btn-secondary"
                                               onclick="return confirm('Reject this review?');">
                                                Reject
                                            </a>
                                        <?php endif; ?>

                                        <a href="../actions/review-action.php?action=delete&id=<?php echo $review['review_id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Delete this review permanently?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No reviews found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>