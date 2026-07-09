<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

function uploadStallImage($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        return false;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_name = 'stall_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $upload_path = '../assets/uploads/stalls/' . $new_name;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $new_name;
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'];
    $market_id = intval($_POST['market_id']);
    $stall_name = trim($_POST['stall_name']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);

    if (empty($market_id) || empty($stall_name) || empty($category)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: ../admin/stall-form.php");
        exit();
    }

    $uploaded_image = uploadStallImage($_FILES['image']);

    if ($uploaded_image === false) {
        $_SESSION['error'] = "Invalid image upload. Please upload JPG, PNG, or WEBP image.";
        header("Location: ../admin/stall-form.php");
        exit();
    }

    if ($action === 'create') {

        $sql = "INSERT INTO stalls (market_id, stall_name, category, description, image)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issss", $market_id, $stall_name, $category, $description, $uploaded_image);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Stall added successfully.";
            header("Location: ../admin/manage-stalls.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to add stall.";
            header("Location: ../admin/stall-form.php");
            exit();
        }

    } elseif ($action === 'update') {

        $stall_id = intval($_POST['stall_id']);
        $old_image = $_POST['old_image'];
        $final_image = $uploaded_image ? $uploaded_image : $old_image;

        $sql = "UPDATE stalls
                SET market_id = ?, stall_name = ?, category = ?, description = ?, image = ?
                WHERE stall_id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issssi", $market_id, $stall_name, $category, $description, $final_image, $stall_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Stall updated successfully.";
            header("Location: ../admin/manage-stalls.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update stall.";
            header("Location: ../admin/stall-form.php?id=" . $stall_id);
            exit();
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $action = $_GET['action'] ?? '';
    $stall_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($stall_id <= 0) {
        $_SESSION['error'] = "Invalid stall ID.";
        header("Location: ../admin/manage-stalls.php");
        exit();
    }

    if ($action === 'activate') {
        $status = 'active';
    } elseif ($action === 'deactivate') {
        $status = 'inactive';
    } else {
        $_SESSION['error'] = "Invalid action.";
        header("Location: ../admin/manage-stalls.php");
        exit();
    }

    $sql = "UPDATE stalls SET status = ? WHERE stall_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $stall_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Stall status updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update stall status.";
    }

    header("Location: ../admin/manage-stalls.php");
    exit();

} else {
    header("Location: ../admin/manage-stalls.php");
    exit();
}