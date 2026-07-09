<?php
$page_title = "Market Form";
$base_path = "../";

include '../includes/header.php';
include '../functions/auth.php';
checkAdmin();
include '../config/db.php';
include '../includes/navbar.php';

$is_edit = false;
$market = [
    'market_id' => '',
    'market_name' => '',
    'description' => '',
    'address' => '',
    'area' => '',
    'state' => '',
    'opening_time' => '',
    'closing_time' => '',
    'image' => ''
];

$selected_days = [];

if (isset($_GET['id'])) {
    $is_edit = true;
    $market_id = intval($_GET['id']);

    $stmt = mysqli_prepare($conn, "SELECT * FROM night_markets WHERE market_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $market_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {
        $market = mysqli_fetch_assoc($result);
    } else {
        $_SESSION['error'] = "Market not found.";
        header("Location: manage-markets.php");
        exit();
    }

    $day_sql = "SELECT day_of_week FROM market_operating_days WHERE market_id = ?";
    $day_stmt = mysqli_prepare($conn, $day_sql);
    mysqli_stmt_bind_param($day_stmt, "i", $market_id);
    mysqli_stmt_execute($day_stmt);
    $day_result = mysqli_stmt_get_result($day_stmt);

    while ($row = mysqli_fetch_assoc($day_result)) {
        $selected_days[] = $row['day_of_week'];
    }
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><?php echo $is_edit ? 'Edit Night Market' : 'Add Night Market'; ?></h2>
        <a href="manage-markets.php" class="btn btn-outline-dark">Back</a>
    </div>

    <?php include '../includes/alert.php'; ?>

    <div class="card shadow-sm">
        <div class="card-body p-4">

            <form action="../actions/market-action.php" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="action" value="<?php echo $is_edit ? 'update' : 'create'; ?>">
                <input type="hidden" name="market_id" value="<?php echo htmlspecialchars($market['market_id']); ?>">
                <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($market['image']); ?>">

                <div class="mb-3">
                    <label class="form-label">Market Name</label>
                    <input type="text" name="market_name" class="form-control" 
                           value="<?php echo htmlspecialchars($market['market_name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($market['description']); ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($market['address']); ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Area in Selangor</label>

                        <?php
                        $selangor_areas = [
                            'Petaling Jaya',
                            'Shah Alam',
                            'Klang',
                            'Subang Jaya',
                            'Puchong',
                            'Kajang',
                            'Seri Kembangan',
                            'Ampang',
                            'Rawang',
                            'Selayang',
                            'Cyberjaya',
                            'Sepang',
                            'Bangi',
                            'Kepong',
                            'Cheras Selangor'
                        ];
                        ?>

                        <select name="area" class="form-select" required>
                            <option value="">-- Select Area --</option>

                            <?php foreach ($selangor_areas as $area): ?>
                                <option value="<?php echo $area; ?>"
                                    <?php echo ($market['area'] === $area) ? 'selected' : ''; ?>>
                                    <?php echo $area; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">State</label>
                        <input type="text" class="form-control" value="Selangor" readonly>
                        <input type="hidden" name="state" value="Selangor">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Opening Time</label>
                        <input type="time" name="opening_time" class="form-control" 
                               value="<?php echo htmlspecialchars($market['opening_time']); ?>" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Closing Time</label>
                        <input type="time" name="closing_time" class="form-control" 
                               value="<?php echo htmlspecialchars($market['closing_time']); ?>" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Operating Days</label>

                    <div class="row">
                        <?php foreach ($days as $day): ?>
                            <div class="col-md-3 col-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           name="operating_days[]" 
                                           value="<?php echo $day; ?>"
                                           <?php echo in_array($day, $selected_days) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">
                                        <?php echo $day; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Market Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">

                    <?php if (!empty($market['image'])): ?>
                        <div class="mt-3">
                            <p class="mb-1">Current Image:</p>
                            <img src="../assets/uploads/markets/<?php echo htmlspecialchars($market['image']); ?>" 
                                 width="160" height="100" 
                                 style="object-fit: cover; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-warning">
                    <?php echo $is_edit ? 'Update Market' : 'Add Market'; ?>
                </button>

            </form>

        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>