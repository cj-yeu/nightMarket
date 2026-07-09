<?php
$page_title = "Visit Planner";
include 'includes/header.php';
include 'functions/auth.php';
checkClient();
include 'config/db.php';
include 'includes/navbar.php';

$user_id = $_SESSION['user_id'];

$selected_market = null;

if (isset($_GET['market_id'])) {
    $market_id = intval($_GET['market_id']);

    $market_sql = "SELECT * FROM night_markets WHERE market_id = ? AND status = 'active' AND state = 'Selangor'";
    $market_stmt = mysqli_prepare($conn, $market_sql);
    mysqli_stmt_bind_param($market_stmt, "i", $market_id);
    mysqli_stmt_execute($market_stmt);
    $market_result = mysqli_stmt_get_result($market_stmt);

    if (mysqli_num_rows($market_result) === 1) {
        $selected_market = mysqli_fetch_assoc($market_result);
    }
}

$plans_sql = "
    SELECT *
    FROM visit_plans
    WHERE user_id = ?
    ORDER BY plan_date DESC, created_at DESC
";
$plans_stmt = mysqli_prepare($conn, $plans_sql);
mysqli_stmt_bind_param($plans_stmt, "i", $user_id);
mysqli_stmt_execute($plans_stmt);
$plans = mysqli_stmt_get_result($plans_stmt);

$active_plans_sql = "
    SELECT *
    FROM visit_plans
    WHERE user_id = ?
    AND status = 'planned'
    ORDER BY plan_date ASC
";
$active_stmt = mysqli_prepare($conn, $active_plans_sql);
mysqli_stmt_bind_param($active_stmt, "i", $user_id);
mysqli_stmt_execute($active_stmt);
$active_plans = mysqli_stmt_get_result($active_stmt);
?>

<section class="py-5 bg-white">
    <div class="container">

        <div class="text-center mb-5">
            <h1 class="fw-bold">My Visit Planner</h1>
            <p class="lead text-muted">
                Create your own night market visit plan and organize your trip.
            </p>
        </div>

        <?php include 'includes/alert.php'; ?>

        <div class="row g-4">

            <div class="col-md-4">

                <div class="card shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h4 class="mb-3">Create New Plan</h4>

                        <form action="actions/planner-action.php" method="POST">
                            <input type="hidden" name="action" value="create_plan">

                            <div class="mb-3">
                                <label class="form-label">Plan Title</label>
                                <input type="text" name="plan_title" class="form-control" placeholder="Example: Weekend Food Hunt" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Plan Date</label>
                                <input type="date" name="plan_date" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-warning w-100">
                                Create Plan
                            </button>
                        </form>
                    </div>
                </div>

                <?php if ($selected_market): ?>
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <h4 class="mb-3">Add Market to Plan</h4>

                            <div class="alert alert-info">
                                Selected Market:<br>
                                <strong><?php echo htmlspecialchars($selected_market['market_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($selected_market['area']); ?>, Selangor</small>
                            </div>

                            <?php if (mysqli_num_rows($active_plans) > 0): ?>

                                <form action="actions/planner-action.php" method="POST">
                                    <input type="hidden" name="action" value="add_item">
                                    <input type="hidden" name="market_id" value="<?php echo $selected_market['market_id']; ?>">

                                    <div class="mb-3">
                                        <label class="form-label">Choose Plan</label>
                                        <select name="plan_id" class="form-select" required>
                                            <option value="">-- Select Plan --</option>

                                            <?php while ($plan_option = mysqli_fetch_assoc($active_plans)): ?>
                                                <option value="<?php echo $plan_option['plan_id']; ?>">
                                                    <?php echo htmlspecialchars($plan_option['plan_title']); ?>
                                                    -
                                                    <?php echo date("d M Y", strtotime($plan_option['plan_date'])); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Planned Time</label>
                                        <input type="time" name="planned_time" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Visit Sequence</label>
                                        <input type="number" name="sequence_no" class="form-control" value="1" min="1">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes" class="form-control" rows="3" placeholder="Example: Try apam balik first"></textarea>
                                    </div>

                                    <button type="submit" class="btn btn-dark w-100">
                                        Add to Plan
                                    </button>
                                </form>

                            <?php else: ?>
                                <div class="alert alert-warning mb-0">
                                    You need to create a planned visit first before adding a market.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <div class="col-md-8">

                <h3 class="fw-bold mb-4">My Plans</h3>

                <?php if ($plans && mysqli_num_rows($plans) > 0): ?>

                    <?php while ($plan = mysqli_fetch_assoc($plans)): ?>

                        <div class="card shadow-sm mb-4">
                            <div class="card-body">

                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h4 class="fw-bold mb-1">
                                            <?php echo htmlspecialchars($plan['plan_title']); ?>
                                        </h4>

                                        <p class="text-muted mb-1">
                                            Plan Date:
                                            <?php echo date("d M Y", strtotime($plan['plan_date'])); ?>
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
                                        nm.area,
                                        nm.image,
                                        nm.opening_time,
                                        nm.closing_time
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
                                                    <th>Planned Time</th>
                                                    <th>Notes</th>
                                                    <th width="90">Action</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php while ($item = mysqli_fetch_assoc($items)): ?>
                                                    <tr>
                                                        <td><?php echo $item['sequence_no']; ?></td>

                                                        <td>
                                                            <strong>
                                                                <?php echo htmlspecialchars($item['market_name']); ?>
                                                            </strong>
                                                            <br>
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars($item['area']); ?>, Selangor
                                                            </small>
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

                                                        <td>
                                                            <a href="actions/planner-action.php?action=delete_item&id=<?php echo $item['plan_item_id']; ?>" 
                                                               class="btn btn-sm btn-danger"
                                                               onclick="return confirm('Remove this market from plan?');">
                                                                Remove
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                <?php else: ?>

                                    <div class="alert alert-info mt-3">
                                        No night market added to this plan yet.
                                    </div>

                                <?php endif; ?>

                                <div class="mt-3 d-flex flex-wrap gap-2">

                                    <?php if ($plan['status'] !== 'completed'): ?>
                                        <a href="actions/planner-action.php?action=complete_plan&id=<?php echo $plan['plan_id']; ?>" 
                                           class="btn btn-sm btn-success"
                                           onclick="return confirm('Mark this plan as completed?');">
                                            Mark Completed
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($plan['status'] !== 'cancelled'): ?>
                                        <a href="actions/planner-action.php?action=cancel_plan&id=<?php echo $plan['plan_id']; ?>" 
                                           class="btn btn-sm btn-secondary"
                                           onclick="return confirm('Cancel this plan?');">
                                            Cancel Plan
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($plan['status'] !== 'planned'): ?>
                                        <a href="actions/planner-action.php?action=reopen_plan&id=<?php echo $plan['plan_id']; ?>" 
                                           class="btn btn-sm btn-warning"
                                           onclick="return confirm('Set this plan back to planned?');">
                                            Reopen Plan
                                        </a>
                                    <?php endif; ?>

                                    <a href="actions/planner-action.php?action=delete_plan&id=<?php echo $plan['plan_id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Delete this plan permanently?');">
                                        Delete Plan
                                    </a>

                                </div>

                            </div>
                        </div>

                    <?php endwhile; ?>

                <?php else: ?>

                    <div class="alert alert-info">
                        You have not created any visit plan yet.
                    </div>

                <?php endif; ?>

            </div>

        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>