<?php
$page_title = "Social Media Data";
$base_path = "../";

include '../includes/header.php';
include '../functions/auth.php';
checkAdmin();
include '../config/db.php';
include '../includes/navbar.php';

$sql = "
    SELECT 
        smd.*,
        nm.market_name,
        nm.area,
        u.full_name AS admin_name
    FROM social_media_data smd
    LEFT JOIN night_markets nm ON smd.market_id = nm.market_id
    LEFT JOIN users u ON smd.added_by = u.user_id
    ORDER BY smd.created_at DESC
";

$result = mysqli_query($conn, $sql);
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Social Media Data Extraction</h2>

        <div>
            <a href="dashboard.php" class="btn btn-outline-dark">Back to Dashboard</a>
            <a href="social-media-form.php" class="btn btn-warning">Add Social Media Data</a>
        </div>
    </div>

    <?php include '../includes/alert.php'; ?>

    <div class="alert alert-info">
        This module is semi-automated. Admin manually enters or pastes social media post content, while the system stores extracted keywords and mentioned food for client viewing.
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Image</th>
                            <th>Post Info</th>
                            <th>Night Market</th>
                            <th>Platform</th>
                            <th>Keywords</th>
                            <th>Mentioned Food</th>
                            <th>Status</th>
                            <th width="220">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($row['image'])): ?>
                                            <img src="../assets/uploads/social/<?php echo htmlspecialchars($row['image']); ?>" 
                                                 width="80" height="60"
                                                 style="object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            <span class="text-muted">No image</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <strong><?php echo htmlspecialchars($row['post_title']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars(substr($row['post_content'], 0, 80)); ?>...
                                        </small>

                                        <?php if (!empty($row['post_url'])): ?>
                                            <br>
                                            <a href="<?php echo htmlspecialchars($row['post_url']); ?>" target="_blank" class="small">
                                                View Post
                                            </a>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if (!empty($row['market_name'])): ?>
                                            <strong><?php echo htmlspecialchars($row['market_name']); ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($row['area']); ?>, Selangor
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Not linked</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <span class="badge bg-dark">
                                            <?php echo htmlspecialchars($row['platform']); ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php echo !empty($row['extracted_keywords']) 
                                            ? htmlspecialchars($row['extracted_keywords']) 
                                            : '<span class="text-muted">No keywords</span>'; ?>
                                    </td>

                                    <td>
                                        <?php echo !empty($row['mentioned_food']) 
                                            ? htmlspecialchars($row['mentioned_food']) 
                                            : '<span class="text-muted">Not set</span>'; ?>
                                    </td>

                                    <td>
                                        <?php if ($row['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <a href="social-media-form.php?id=<?php echo $row['social_data_id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            Edit
                                        </a>

                                        <?php if ($row['status'] === 'active'): ?>
                                            <a href="../actions/social-media-action.php?action=deactivate&id=<?php echo $row['social_data_id']; ?>" 
                                               class="btn btn-sm btn-secondary"
                                               onclick="return confirm('Deactivate this social media data?');">
                                                Deactivate
                                            </a>
                                        <?php else: ?>
                                            <a href="../actions/social-media-action.php?action=activate&id=<?php echo $row['social_data_id']; ?>" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('Activate this social media data?');">
                                                Activate
                                            </a>
                                        <?php endif; ?>

                                        <a href="../actions/social-media-action.php?action=delete&id=<?php echo $row['social_data_id']; ?>" 
                                           class="btn btn-sm btn-danger mt-1"
                                           onclick="return confirm('Delete this record permanently?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No social media data found.
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