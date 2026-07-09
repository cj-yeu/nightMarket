<?php
$page_title = "Manage Stalls";
$base_path = "../";

include '../includes/header.php';
include '../functions/auth.php';
checkAdmin();
include '../config/db.php';
include '../includes/navbar.php';

$sql = "
    SELECT 
        s.*,
        nm.market_name
    FROM stalls s
    INNER JOIN night_markets nm ON s.market_id = nm.market_id
    ORDER BY s.created_at DESC
";

$result = mysqli_query($conn, $sql);
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Manage Stalls</h2>
        <div>
            <a href="manage-foods.php" class="btn btn-outline-dark">Manage Foods</a>
            <a href="stall-form.php" class="btn btn-warning">Add New Stall</a>
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
                            <th>Stall Name</th>
                            <th>Night Market</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th width="190">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($stall = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($stall['image'])): ?>
                                            <img src="../assets/uploads/stalls/<?php echo htmlspecialchars($stall['image']); ?>" 
                                                 width="80" height="60"
                                                 style="object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <strong><?php echo htmlspecialchars($stall['stall_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(substr($stall['description'], 0, 60)); ?>...
                                        </small>
                                    </td>

                                    <td><?php echo htmlspecialchars($stall['market_name']); ?></td>
                                    <td><?php echo htmlspecialchars($stall['category']); ?></td>

                                    <td>
                                        <?php if ($stall['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <a href="stall-form.php?id=<?php echo $stall['stall_id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            Edit
                                        </a>

                                        <?php if ($stall['status'] === 'active'): ?>
                                            <a href="../actions/stall-action.php?action=deactivate&id=<?php echo $stall['stall_id']; ?>" 
                                               class="btn btn-sm btn-secondary"
                                               onclick="return confirm('Deactivate this stall?');">
                                                Deactivate
                                            </a>
                                        <?php else: ?>
                                            <a href="../actions/stall-action.php?action=activate&id=<?php echo $stall['stall_id']; ?>" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Activate this stall?');">
                                                Activate
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No stalls found.
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