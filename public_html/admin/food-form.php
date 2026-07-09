<?php
$page_title = "Food Form";
$base_path = "../";

include '../includes/header.php';
include '../functions/auth.php';
checkAdmin();
include '../config/db.php';
include '../includes/navbar.php';

$is_edit = false;

$food = [
    'food_id' => '',
    'stall_id' => '',
    'food_name' => '',
    'description' => '',
    'price_range' => '',
    'is_must_try' => 1,
    'image' => ''
];

if (isset($_GET['id'])) {
    $is_edit = true;
    $food_id = intval($_GET['id']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM foods WHERE food_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $food_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $food = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['error'] = "Food not found.";
        header("Location: manage-foods.php");
        exit();
    }
}

$stalls = mysqli_query($conn, "
    SELECT s.stall_id, s.stall_name, nm.market_name
    FROM stalls s
    INNER JOIN night_markets nm ON s.market_id = nm.market_id
    WHERE s.status = 'active'
    ORDER BY nm.market_name ASC, s.stall_name ASC
");
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo $is_edit ? 'Edit Food' : 'Add Must-Try Food'; ?></h2>
        <a href="manage-foods.php" class="btn btn-outline-dark">Back</a>
    </div>

    <?php include '../includes/alert.php'; ?>

    <div class="card shadow-sm">
        <div class="card-body p-4">

            <form action="../actions/food-action.php" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
                <input type="hidden" name="food_id" value="<?php echo htmlspecialchars($food['food_id']); ?>">
                <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($food['image']); ?>">

                <div class="mb-3">
                    <label class="form-label">Stall</label>
                    <select name="stall_id" class="form-select" required>
                        <option value="">-- Select Stall --</option>

                        <?php while ($stall = mysqli_fetch_assoc($stalls)): ?>
                            <option value="<?php echo $stall['stall_id']; ?>"
                                <?php echo ($food['stall_id'] == $stall['stall_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($stall['market_name'] . " - " . $stall['stall_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Food Name</label>
                    <input type="text" name="food_name" class="form-control"
                           value="<?php echo htmlspecialchars($food['food_name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($food['description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Price Range</label>
                    <input type="text" name="price_range" class="form-control"
                           placeholder="Example: RM5 - RM12"
                           value="<?php echo htmlspecialchars($food['price_range']); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Must-Try Food</label>
                    <select name="is_must_try" class="form-select">
                        <option value="1" <?php echo ($food['is_must_try'] == 1) ? 'selected' : ''; ?>>Yes</option>
                        <option value="0" <?php echo ($food['is_must_try'] == 0) ? 'selected' : ''; ?>>No</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Food Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">

                    <?php if (!empty($food['image'])): ?>
                        <div class="mt-3">
                            <p class="mb-1">Current Image:</p>
                            <img src="../assets/uploads/foods/<?php echo htmlspecialchars($food['image']); ?>" 
                                 width="160" height="100"
                                 style="object-fit: cover; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-warning">
                    <?php echo $is_edit ? 'Update Food' : 'Add Food'; ?>
                </button>

            </form>

        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>