<?php
$page_title = "Night Markets";
include 'includes/header.php';
include 'config/db.php';
include 'includes/navbar.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$area = isset($_GET['area']) ? trim($_GET['area']) : '';
$day = isset($_GET['day']) ? trim($_GET['day']) : '';

$where = "WHERE nm.status = 'active' AND nm.state = 'Selangor'";

if (!empty($search)) {
    $safe_search = mysqli_real_escape_string($conn, $search);
    $where .= " AND (
        nm.market_name LIKE '%$safe_search%' 
        OR nm.address LIKE '%$safe_search%' 
        OR nm.area LIKE '%$safe_search%'
    )";
}

if (!empty($area)) {
    $safe_area = mysqli_real_escape_string($conn, $area);
    $where .= " AND nm.area = '$safe_area'";
}

if (!empty($day)) {
    $safe_day = mysqli_real_escape_string($conn, $day);
    $where .= " AND EXISTS (
        SELECT 1 
        FROM market_operating_days mod2 
        WHERE mod2.market_id = nm.market_id 
        AND mod2.day_of_week = '$safe_day'
    )";
}

$sql = "
    SELECT 
        nm.*,
        GROUP_CONCAT(mods.day_of_week ORDER BY FIELD(mods.day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') SEPARATOR ', ') AS operating_days
    FROM night_markets nm
    LEFT JOIN market_operating_days mods ON nm.market_id = mods.market_id
    $where
    GROUP BY nm.market_id
    ORDER BY nm.market_name ASC
";

$result = mysqli_query($conn, $sql);

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

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<section class="py-5 bg-white">
    <div class="container">
        <div class="text-center mb-4">
            <h1 class="fw-bold">Discover Selangor Night Markets</h1>
            <p class="lead text-muted">
                Search night markets by area and operating day.
            </p>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="market-list.php" class="row g-3">

                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search market name or area"
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Area</label>
                        <select name="area" class="form-select">
                            <option value="">All Areas</option>
                            <?php foreach ($selangor_areas as $area_option): ?>
                                <option value="<?php echo $area_option; ?>"
                                    <?php echo ($area === $area_option) ? 'selected' : ''; ?>>
                                    <?php echo $area_option; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Operating Day</label>
                        <select name="day" class="form-select">
                            <option value="">All Days</option>
                            <?php foreach ($days as $day_option): ?>
                                <option value="<?php echo $day_option; ?>"
                                    <?php echo ($day === $day_option) ? 'selected' : ''; ?>>
                                    <?php echo $day_option; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-warning w-100">
                            Filter
                        </button>
                    </div>

                </form>

                <?php if (!empty($search) || !empty($area) || !empty($day)): ?>
                    <div class="mt-3">
                        <a href="market-list.php" class="btn btn-outline-secondary btn-sm">
                            Clear Filter
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($market = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-4">
                        <div class="card market-card shadow-sm h-100">

                            <?php if (!empty($market['image'])): ?>
                                <img src="assets/uploads/markets/<?php echo htmlspecialchars($market['image']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($market['market_name']); ?>">
                            <?php else: ?>
                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height:180px;">
                                    No Image
                                </div>
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($market['market_name']); ?>
                                </h5>

                                <p class="text-muted mb-2">
                                    <?php echo htmlspecialchars($market['area']); ?>, Selangor
                                </p>

                                <p class="small mb-2">
                                    <strong>Operating Days:</strong>
                                    <?php echo $market['operating_days'] ? htmlspecialchars($market['operating_days']) : 'Not set'; ?>
                                </p>

                                <p class="small mb-2">
                                    <strong>Time:</strong>
                                    <?php 
                                    if (!empty($market['opening_time']) && !empty($market['closing_time'])) {
                                        echo date("h:i A", strtotime($market['opening_time'])) . " - " . date("h:i A", strtotime($market['closing_time']));
                                    } else {
                                        echo "Not set";
                                    }
                                    ?>
                                </p>

                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($market['description'], 0, 100)); ?>...
                                </p>
                            </div>

                            <div class="card-footer bg-white border-0">
                                <a href="market-detail.php?id=<?php echo $market['market_id']; ?>" 
                                   class="btn btn-dark w-100">
                                    View Details
                                </a>
                            </div>

                        </div>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        No night markets found. Please try another search or filter.
                    </div>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>