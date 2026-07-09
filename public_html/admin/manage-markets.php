<?php
$page_title = "Manage Night Markets";
$base_path = "../";

include '../includes/header.php';
include '../functions/auth.php';
checkAdmin();
include '../config/db.php';
include '../includes/navbar.php';

$sql = "
    SELECT 
        nm.*,
        GROUP_CONCAT(mods.day_of_week ORDER BY FIELD(mods.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') SEPARATOR ', ') AS operating_days
    FROM night_markets nm
    LEFT JOIN market_operating_days mods ON nm.market_id = mods.market_id
    GROUP BY nm.market_id
    ORDER BY nm.created_at DESC
";

$result = mysqli_query($conn, $sql);
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Night Markets</h2>
        <a href="market-form.php" class="btn btn-warning">Add New Market</a>
    </div>

    <?php include '../includes/alert.php'; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Image</th>
                            <th>Market Name</th>
                            <th>Area</th>
                            <th>State</th>
                            <th>Operating Days</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($market = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($market['image'])): ?>
                                            <img src="../assets/uploads/markets/<?php echo htmlspecialchars($market['image']); ?>" 
                                                 width="80" height="60" 
                                                 style="object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <strong><?php echo htmlspecialchars($market['market_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(substr($market['address'], 0, 60)); ?>...
                                        </small>
                                    </td>

                                    <td><?php echo htmlspecialchars($market['area']); ?></td>
                                    <td><?php echo htmlspecialchars($market['state']); ?></td>

                                    <td>
                                        <?php echo $market['operating_days'] ? htmlspecialchars($market['operating_days']) : '<span class="text-muted">Not set</span>'; ?>
                                    </td>

                                    <td>
                                        <?php 
                                            echo date("h:i A", strtotime($market['opening_time'])) . " - " . date("h:i A", strtotime($market['closing_time']));
                                        ?>
                                    </td>

                                    <td>
                                        <?php if ($market['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <a href="market-form.php?id=<?php echo $market['market_id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            Edit
                                        </a>

                                        <?php if ($market['status'] === 'active'): ?>
                                            <a href="../actions/market-action.php?action=deactivate&id=<?php echo $market['market_id']; ?>" 
                                               class="btn btn-sm btn-secondary"
                                               onclick="return confirm('Deactivate this market?');">
                                                Deactivate
                                            </a>
                                        <?php else: ?>
                                            <a href="../actions/market-action.php?action=activate&id=<?php echo $market['market_id']; ?>" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Activate this market?');">
                                                Activate
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No night markets found.
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