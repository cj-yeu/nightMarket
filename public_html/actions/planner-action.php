<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    $_SESSION['error'] = "Please login as client to use visit planner.";
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'];

    if ($action === 'create_plan') {

        $plan_title = trim($_POST['plan_title']);
        $plan_date = $_POST['plan_date'];
        $notes = trim($_POST['notes']);

        if (empty($plan_title) || empty($plan_date)) {
            $_SESSION['error'] = "Please fill in plan title and date.";
            header("Location: ../planner.php");
            exit();
        }

        $sql = "INSERT INTO visit_plans (user_id, plan_title, plan_date, notes)
                VALUES (?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $plan_title, $plan_date, $notes);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Visit plan created successfully.";
        } else {
            $_SESSION['error'] = "Failed to create visit plan.";
        }

        header("Location: ../planner.php");
        exit();

    } elseif ($action === 'add_item') {

        $plan_id = intval($_POST['plan_id']);
        $market_id = intval($_POST['market_id']);
        $planned_time = !empty($_POST['planned_time']) ? $_POST['planned_time'] : null;
        $sequence_no = intval($_POST['sequence_no']);
        $notes = trim($_POST['notes']);

        if ($plan_id <= 0 || $market_id <= 0) {
            $_SESSION['error'] = "Invalid plan or market.";
            header("Location: ../market-list.php");
            exit();
        }

        if ($sequence_no <= 0) {
            $sequence_no = 1;
        }

        $check_plan_sql = "
            SELECT plan_id 
            FROM visit_plans 
            WHERE plan_id = ? 
            AND user_id = ?
            AND status = 'planned'
        ";
        $check_plan_stmt = mysqli_prepare($conn, $check_plan_sql);
        mysqli_stmt_bind_param($check_plan_stmt, "ii", $plan_id, $user_id);
        mysqli_stmt_execute($check_plan_stmt);
        $check_plan_result = mysqli_stmt_get_result($check_plan_stmt);

        if (mysqli_num_rows($check_plan_result) === 0) {
            $_SESSION['error'] = "Plan not found or cannot be edited.";
            header("Location: ../planner.php");
            exit();
        }

        $check_market_sql = "
            SELECT market_id 
            FROM night_markets 
            WHERE market_id = ? 
            AND status = 'active'
            AND state = 'Selangor'
        ";
        $check_market_stmt = mysqli_prepare($conn, $check_market_sql);
        mysqli_stmt_bind_param($check_market_stmt, "i", $market_id);
        mysqli_stmt_execute($check_market_stmt);
        $check_market_result = mysqli_stmt_get_result($check_market_stmt);

        if (mysqli_num_rows($check_market_result) === 0) {
            $_SESSION['error'] = "Night market not found.";
            header("Location: ../market-list.php");
            exit();
        }

        $duplicate_sql = "
            SELECT plan_item_id
            FROM visit_plan_items
            WHERE plan_id = ?
            AND market_id = ?
        ";
        $duplicate_stmt = mysqli_prepare($conn, $duplicate_sql);
        mysqli_stmt_bind_param($duplicate_stmt, "ii", $plan_id, $market_id);
        mysqli_stmt_execute($duplicate_stmt);
        $duplicate_result = mysqli_stmt_get_result($duplicate_stmt);

        if (mysqli_num_rows($duplicate_result) > 0) {
            $_SESSION['error'] = "This night market is already added to the selected plan.";
            header("Location: ../planner.php?market_id=" . $market_id);
            exit();
        }

        $sql = "INSERT INTO visit_plan_items (plan_id, market_id, planned_time, sequence_no, notes)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iisis", $plan_id, $market_id, $planned_time, $sequence_no, $notes);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Night market added to visit plan.";
        } else {
            $_SESSION['error'] = "Failed to add night market to plan.";
        }

        header("Location: ../planner.php");
        exit();

    } else {
        $_SESSION['error'] = "Invalid action.";
        header("Location: ../planner.php");
        exit();
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $action = $_GET['action'] ?? '';
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        $_SESSION['error'] = "Invalid request.";
        header("Location: ../planner.php");
        exit();
    }

    if ($action === 'complete_plan' || $action === 'cancel_plan' || $action === 'reopen_plan') {

        if ($action === 'complete_plan') {
            $status = 'completed';
        } elseif ($action === 'cancel_plan') {
            $status = 'cancelled';
        } else {
            $status = 'planned';
        }

        $sql = "UPDATE visit_plans SET status = ? WHERE plan_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sii", $status, $id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Plan status updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update plan status.";
        }

        header("Location: ../planner.php");
        exit();

    } elseif ($action === 'delete_plan') {

        $sql = "DELETE FROM visit_plans WHERE plan_id = ? AND user_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $id, $user_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Visit plan deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete visit plan.";
        }

        header("Location: ../planner.php");
        exit();

    } elseif ($action === 'delete_item') {

        $check_sql = "
            SELECT vpi.plan_item_id
            FROM visit_plan_items vpi
            INNER JOIN visit_plans vp ON vpi.plan_id = vp.plan_id
            WHERE vpi.plan_item_id = ?
            AND vp.user_id = ?
        ";

        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ii", $id, $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) === 0) {
            $_SESSION['error'] = "Plan item not found.";
            header("Location: ../planner.php");
            exit();
        }

        $sql = "DELETE FROM visit_plan_items WHERE plan_item_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Market removed from plan.";
        } else {
            $_SESSION['error'] = "Failed to remove market from plan.";
        }

        header("Location: ../planner.php");
        exit();

    } else {
        $_SESSION['error'] = "Invalid action.";
        header("Location: ../planner.php");
        exit();
    }

} else {
    header("Location: ../planner.php");
    exit();
}