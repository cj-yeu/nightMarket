<?php
$page_title = "Manage Visit Plans";
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
    $where = "WHERE vp.status = '$safe_status'";
}

$sql = "
    SELECT 
        vp.*,
        u.full_name,
        u.email,
        COUNT(vpi.plan_item_id) AS total_items
    FROM visit_plans vp
    INNER JOIN users u ON vp.user_id = u.user_id
    LEFT JOIN visit_plan_items vpi ON vp.plan_id = vpi.plan_id
    $where
    GROUP BY vp.plan_id
    ORDER BY vp.plan_date DESC, vp.created_at DESC
";

$result = mysqli_query($conn, $sql);
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Visit Plans</h2>

        <a href="dashboard.php" class="btn btn-outline-dark">
            Back to Dashboard
        </a>
    </div>

    <?php include '../includes/alert.php'; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="manage-planners.php" class="row g-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label">Filter by Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Plans</option>
                        <option value="planned" <?php echo ($status_filter === 'planned') ? 'selected' : ''; ?>>Planned</option>
                        <option value="completed" <?php echo ($status_filter === 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($status_filter === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-warning w-100">Filter</button>
                </div>

                <div class="col-md-2">
                    <a href="manage-planners.php" class="btn btn-outline-secondary w-100">Clear</a>
                </div>

            </form>
        </div>
    </div>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>

        <?php while ($plan = mysqli_fetch_assoc($result)): ?>

            <div class="card shadow-sm mb-4">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="fw-bold mb-1">
                                <?php echo htmlspecialchars($plan['plan_title']); ?>
                            </h4>

                            <p class="text-muted mb-1">
                                Client:
                                <strong><?php echo htmlspecialchars($plan['full_name']); ?></strong>
                                |
                                <?php echo htmlspecialchars($plan['email']); ?>
                            </p>

                            <p class="text-muted mb-1">
                                Plan Date:
                                <?php echo date("d M Y", strtotime($plan['plan_date'])); ?>
                                |
                                Total Markets:
                                <?php echo $plan['total_items']; ?>
                            </p>

                            <?php if (!empty($plan['notes'])): ?>
                                <p class="mb-0">
                                    <?php echo nl2br(htmlspecialchars($plan['notes'])); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div>
                            <?php if ($plan['status'] === 'planned'): ?>
                                <span class="badge bg-warning text-dark">Planned</span>
                            <?php elseif ($plan['status'] === 'completed'): ?>
                                <span class="badge bg-success">Completed</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Cancelled</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php
                    $items_sql = "
                        SELECT
                            vpi.*,
                            nm.market_name,
                            nm.area
                        FROM visit_plan_items vpi
                        INNER JOIN night_markets nm ON vpi.market_id = nm.market_id
                        WHERE vpi.plan_id = ?
                        ORDER BY vpi.sequence_no ASC, vpi.planned_time ASC
                    ";
                    $items_stmt = mysqli_prepare($conn, $items_sql);
                    mysqli_stmt_bind_param($items_stmt, "i", $plan['plan_id']);
                    mysqli_stmt_execute($items_stmt);
                    $items = mysqli_stmt_get_result($items_stmt);
                    ?>

                    <?php if ($items && mysqli_num_rows($items) > 0): ?>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Seq</th>
                                        <th>Night Market</th>
                                        <th>Area</th>
                                        <th>Planned Time</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php while ($item = mysqli_fetch_assoc($items)): ?>
                                        <tr>
                                            <td><?php echo $item['sequence_no']; ?></td>

                                            <td>
                                                <?php echo htmlspecialchars($item['market_name']); ?>
                                            </td>

                                            <td>
                                                <?php echo htmlspecialchars($item['area']); ?>, Selangor
                                            </td>

                                            <td>
                                                <?php 
                                                echo !empty($item['planned_time'])
                                                    ? date("h:i A", strtotime($item['planned_time']))
                                                    : '<span class="text-muted">Not set</span>';
                                                ?>
                                            </td>

                                            <td>
                                                <?php 
                                                echo !empty($item['notes'])
                                                    ? htmlspecialchars($item['notes'])
                                                    : '<span class="text-muted">No notes</span>';
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>

                        <div class="alert alert-info mb-0">
                            No night markets added to this plan.
                        </div>

                    <?php endif; ?>

                </div>
            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <div class="alert alert-info">
            No visit plans found.
        </div>

    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>