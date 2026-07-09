<?php
session_start();
include '../config/db.php';

function uploadMarketImage($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        return false;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_name = 'market_' . time() . '_' . rand(1000, 9999) . '.' . $extension;

    $upload_path = '../assets/uploads/markets/' . $new_name;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $new_name;
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'];
    $market_name = trim($_POST['market_name']);
    $description = trim($_POST['description']);
    $address = trim($_POST['address']);
    $area = trim($_POST['area']);
    $state = "Selangor";
    $opening_time = $_POST['opening_time'];
    $closing_time = $_POST['closing_time'];
    $operating_days = $_POST['operating_days'] ?? [];

    if (empty($market_name) || empty($address) || empty($area) || empty($opening_time) || empty($closing_time)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: ../admin/market-form.php");
        exit();
    }

    if (empty($operating_days)) {
        $_SESSION['error'] = "Please select at least one operating day.";
        header("Location: ../admin/market-form.php");
        exit();
    }

    $uploaded_image = uploadMarketImage($_FILES['image']);

    if ($uploaded_image === false) {
        $_SESSION['error'] = "Invalid image upload. Please upload JPG, PNG, or WEBP image.";
        header("Location: ../admin/market-form.php");
        exit();
    }

    if ($action === 'create') {

        $created_by = $_SESSION['user_id'] ?? null;

        $sql = "INSERT INTO night_markets 
                (market_name, description, address, area, state, opening_time, closing_time, image, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 
            "ssssssssi", 
            $market_name, 
            $description, 
            $address, 
            $area, 
            $state, 
            $opening_time, 
            $closing_time, 
            $uploaded_image, 
            $created_by
        );

        if (mysqli_stmt_execute($stmt)) {
            $market_id = mysqli_insert_id($conn);

            foreach ($operating_days as $day) {
                $day_sql = "INSERT INTO market_operating_days (market_id, day_of_week) VALUES (?, ?)";
                $day_stmt = mysqli_prepare($conn, $day_sql);
                mysqli_stmt_bind_param($day_stmt, "is", $market_id, $day);
                mysqli_stmt_execute($day_stmt);
            }

            $_SESSION['success'] = "Night market added successfully.";
            header("Location: ../admin/manage-markets.php");
            exit();

        } else {
            $_SESSION['error'] = "Failed to add night market.";
            header("Location: ../admin/market-form.php");
            exit();
        }

    } elseif ($action === 'update') {

        $market_id = intval($_POST['market_id']);
        $old_image = $_POST['old_image'];

        $final_image = $uploaded_image ? $uploaded_image : $old_image;

        $sql = "UPDATE night_markets 
                SET market_name = ?, description = ?, address = ?, area = ?, state = ?, 
                    opening_time = ?, closing_time = ?, image = ?
                WHERE market_id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt, 
            "ssssssssi", 
            $market_name, 
            $description, 
            $address, 
            $area, 
            $state, 
            $opening_time, 
            $closing_time, 
            $final_image, 
            $market_id
        );

        if (mysqli_stmt_execute($stmt)) {

            $delete_days = "DELETE FROM market_operating_days WHERE market_id = ?";
            $delete_stmt = mysqli_prepare($conn, $delete_days);
            mysqli_stmt_bind_param($delete_stmt, "i", $market_id);
            mysqli_stmt_execute($delete_stmt);

            foreach ($operating_days as $day) {
                $day_sql = "INSERT INTO market_operating_days (market_id, day_of_week) VALUES (?, ?)";
                $day_stmt = mysqli_prepare($conn, $day_sql);
                mysqli_stmt_bind_param($day_stmt, "is", $market_id, $day);
                mysqli_stmt_execute($day_stmt);
            }

            $_SESSION['success'] = "Night market updated successfully.";
            header("Location: ../admin/manage-markets.php");
            exit();

        } else {
            $_SESSION['error'] = "Failed to update night market.";
            header("Location: ../admin/market-form.php?id=" . $market_id);
            exit();
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $action = $_GET['action'] ?? '';
    $market_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($market_id <= 0) {
        $_SESSION['error'] = "Invalid market ID.";
        header("Location: ../admin/manage-markets.php");
        exit();
    }

    if ($action === 'activate') {
        $status = 'active';
    } elseif ($action === 'deactivate') {
        $status = 'inactive';
    } else {
        $_SESSION['error'] = "Invalid action.";
        header("Location: ../admin/manage-markets.php");
        exit();
    }

    $sql = "UPDATE night_markets SET status = ? WHERE market_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $market_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Market status updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update market status.";
    }

    header("Location: ../admin/manage-markets.php");
    exit();

} else {
    header("Location: ../admin/manage-markets.php");
    exit();
}