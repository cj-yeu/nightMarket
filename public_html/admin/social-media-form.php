public_html/admin/social-media-form.php

放这个：

<?php
$page_title = "Social Media Form";
$base_path = "../";

include '../includes/header.php';
include '../functions/auth.php';
checkAdmin();
include '../config/db.php';
include '../includes/navbar.php';

$is_edit = false;

$social = [
    'social_data_id' => '',
    'market_id' => '',
    'platform' => '',
    'post_url' => '',
    'post_title' => '',
    'post_content' => '',
    'extracted_keywords' => '',
    'mentioned_food' => '',
    'image' => ''
];

if (isset($_GET['id'])) {
    $is_edit = true;
    $social_data_id = intval($_GET['id']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM social_media_data WHERE social_data_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $social_data_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $social = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['error'] = "Social media data not found.";
        header("Location: social-media-data.php");
        exit();
    }
}

$markets = mysqli_query($conn, "
    SELECT market_id, market_name, area
    FROM night_markets
    WHERE status = 'active'
    AND state = 'Selangor'
    ORDER BY market_name ASC
");

$platforms = ['Facebook', 'Instagram', 'TikTok', 'Xiaohongshu', 'Other'];
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo $is_edit ? 'Edit Social Media Data' : 'Add Social Media Data'; ?></h2>
        <a href="social-media-data.php" class="btn btn-outline-dark">Back</a>
    </div>

    <?php include '../includes/alert.php'; ?>

    <div class="card shadow-sm">
        <div class="card-body p-4">

            <form action="../actions/social-media-action.php" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
                <input type="hidden" name="social_data_id" value="<?php echo htmlspecialchars($social['social_data_id']); ?>">
                <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($social['image']); ?>">

                <div class="mb-3">
                    <label class="form-label">Related Night Market</label>
                    <select name="market_id" class="form-select" required>
                        <option value="">-- Select Night Market --</option>

                        <?php while ($market = mysqli_fetch_assoc($markets)): ?>
                            <option value="<?php echo $market['market_id']; ?>"
                                <?php echo ($social['market_id'] == $market['market_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($market['market_name'] . " - " . $market['area']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Platform</label>
                    <select name="platform" class="form-select" required>
                        <option value="">-- Select Platform --</option>

                        <?php foreach ($platforms as $platform): ?>
                            <option value="<?php echo $platform; ?>"
                                <?php echo ($social['platform'] === $platform) ? 'selected' : ''; ?>>
                                <?php echo $platform; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Post URL</label>
                    <input type="url" name="post_url" class="form-control"
                           placeholder="Example: https://www.instagram.com/..."
                           value="<?php echo htmlspecialchars($social['post_url']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Post Title</label>
                    <input type="text" name="post_title" class="form-control"
                           value="<?php echo htmlspecialchars($social['post_title']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Post Content</label>
                    <textarea name="post_content" class="form-control" rows="5" required><?php echo htmlspecialchars($social['post_content']); ?></textarea>
                    <small class="text-muted">
                        Admin can paste copied social media caption or post content here.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Extracted Keywords</label>
                    <input type="text" name="extracted_keywords" class="form-control"
                           placeholder="Example: satay, apam balik, family friendly"
                           value="<?php echo htmlspecialchars($social['extracted_keywords']); ?>">
                    <small class="text-muted">
                        Leave empty to let the system generate simple keywords from the post content.
                    </small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Mentioned Food</label>
                    <input type="text" name="mentioned_food" class="form-control"
                           placeholder="Example: Apam Balik, Satay, Lok Lok"
                           value="<?php echo htmlspecialchars($social['mentioned_food']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Post Screenshot / Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">

                    <?php if (!empty($social['image'])): ?>
                        <div class="mt-3">
                            <p class="mb-1">Current Image:</p>
                            <img src="../assets/uploads/social/<?php echo htmlspecialchars($social['image']); ?>" 
                                 width="180" height="110"
                                 style="object-fit: cover; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-warning">
                    <?php echo $is_edit ? 'Update Data' : 'Add Data'; ?>
                </button>

            </form>

        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>