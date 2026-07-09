<?php
$page_title = "Stall Form";
$base_path = "../";

include '../includes/header.php';
include '../functions/auth.php';
checkAdmin();
include '../config/db.php';
include '../includes/navbar.php';

$is_edit = false;

$stall = [
    'stall_id' => '',
    'market_id' => '',
    'stall_name' => '',
    'category' => '',
    'description' => '',
    'image' => ''
];

if (isset($_GET['id'])) {
    $is_edit = true;
    $stall_id = intval($_GET['id']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM stalls WHERE stall_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $stall_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $stall = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['error'] = "Stall not found.";
        header("Location: manage-stalls.php");
        exit();
    }
}

$markets = mysqli_query($conn, "SELECT market_id, market_name FROM night_markets WHERE status = 'active' ORDER BY market_name ASC");

$categories = [
    'Main Food',
    'Snack',
    'Dessert',
    'Beverage',
    'Fried Food',
    'Grilled Food',
    'Local Cuisine',
    'Other'
];
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo $is_edit ? 'Edit Stall' : 'Add Stall'; ?></h2>
        <a href="manage-stalls.php" class="btn btn-outline-dark">Back</a>
    </div>

    <?php include '../includes/alert.php'; ?>

    <div class="card shadow-sm">
        <div class="card-body p-4">

            <form action="../actions/stall-action.php" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
                <input type="hidden" name="stall_id" value="<?php echo htmlspecialchars($stall['stall_id']); ?>">
                <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($stall['image']); ?>">

                <div class="mb-3">
                    <label class="form-label">Night Market</label>
                    <select name="market_id" class="form-select" required>
                        <option value="">-- Select Night Market --</option>

                        <?php while ($market = mysqli_fetch_assoc($markets)): ?>
                            <option value="<?php echo $market['market_id']; ?>"
                                <?php echo ($stall['market_id'] == $market['market_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($market['market_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Stall Name</label>
                    <input type="text" name="stall_name" class="form-control"
                           value="<?php echo htmlspecialchars($stall['stall_name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
                        <option value="">-- Select Category --</option>

                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category; ?>"
                                <?php echo ($stall['category'] === $category) ? 'selected' : ''; ?>>
                                <?php echo $category; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($stall['description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Stall Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">

                    <?php if (!empty($stall['image'])): ?>
                        <div class="mt-3">
                            <p class="mb-1">Current Image:</p>
                            <img src="../assets/uploads/stalls/<?php echo htmlspecialchars($stall['image']); ?>" 
                                 width="160" height="100"
                                 style="object-fit: cover; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-warning">
                    <?php echo $is_edit ? 'Update Stall' : 'Add Stall'; ?>
                </button>

            </form>

        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>