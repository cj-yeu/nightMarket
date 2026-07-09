<?php
$page_title = "Manage Foods";
$base_path = "../";

include '../includes/header.php';
include '../functions/auth.php';
checkAdmin();
include '../config/db.php';
include '../includes/navbar.php';

$sql = "
    SELECT 
        f.*,
        s.stall_name,
        nm.market_name
    FROM foods f
    INNER JOIN stalls s ON f.stall_id = s.stall_id
    INNER JOIN night_markets nm ON s.market_id = nm.market_id
    ORDER BY f.created_at DESC
";

$result = mysqli_query($conn, $sql);
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Must-Try Foods</h2>
        <div>
            <a href="manage-stalls.php" class="btn btn-outline-dark">Manage Stalls</a>
            <a href="food-form.php" class="btn btn-warning">Add New Food</a>
        </div>
    </div>

    <?php include '../includes/alert.php'; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Image</th>
                            <th>Food Name</th>
                            <th>Stall</th>
                            <th>Night Market</th>
                            <th>Price Range</th>
                            <th>Must Try</th>
                            <th>Status</th>
                            <th width="190">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($food = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($food['image'])): ?>
                                            <img src="../assets/uploads/foods/<?php echo htmlspecialchars($food['image']); ?>" 
                                                 width="80" height="60"
                                                 style="object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <strong><?php echo htmlspecialchars($food['food_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(substr($food['description'], 0, 60)); ?>...
                                        </small>
                                    </td>

                                    <td><?php echo htmlspecialchars($food['stall_name']); ?></td>
                                    <td><?php echo htmlspecialchars($food['market_name']); ?></td>
                                    <td><?php echo htmlspecialchars($food['price_range']); ?></td>

                                    <td>
                                        <?php if ($food['is_must_try'] == 1): ?>
                                            <span class="badge bg-warning text-dark">Must Try</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">Normal</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($food['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <a href="food-form.php?id=<?php echo $food['food_id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            Edit
                                        </a>

                                        <?php if ($food['status'] === 'active'): ?>
                                            <a href="../actions/food-action.php?action=deactivate&id=<?php echo $food['food_id']; ?>" 
                                               class="btn btn-sm btn-secondary"
                                               onclick="return confirm('Deactivate this food?');">
                                                Deactivate
                                            </a>
                                        <?php else: ?>
                                            <a href="../actions/food-action.php?action=activate&id=<?php echo $food['food_id']; ?>" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Activate this food?');">
                                                Activate
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No foods found.
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