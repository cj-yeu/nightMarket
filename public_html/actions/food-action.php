<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

function uploadFoodImage($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        return false;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_name = 'food_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $upload_path = '../assets/uploads/foods/' . $new_name;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $new_name;
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'];
    $stall_id = intval($_POST['stall_id']);
    $food_name = trim($_POST['food_name']);
    $description = trim($_POST['description']);
    $price_range = trim($_POST['price_range']);
    $is_must_try = intval($_POST['is_must_try']);

    if (empty($stall_id) || empty($food_name)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: ../admin/food-form.php");
        exit();
    }

    $uploaded_image = uploadFoodImage($_FILES['image']);

    if ($uploaded_image === false) {
        $_SESSION['error'] = "Invalid image upload. Please upload JPG, PNG, or WEBP image.";
        header("Location: ../admin/food-form.php");
        exit();
    }

    if ($action === 'create') {

        $sql = "INSERT INTO foods (stall_id, food_name, description, price_range, is_must_try, image)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isssis", $stall_id, $food_name, $description, $price_range, $is_must_try, $uploaded_image);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Food added successfully.";
            header("Location: ../admin/manage-foods.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to add food.";
            header("Location: ../admin/food-form.php");
            exit();
        }

    } elseif ($action === 'update') {

        $food_id = intval($_POST['food_id']);
        $old_image = $_POST['old_image'];
        $final_image = $uploaded_image ? $uploaded_image : $old_image;

        $sql = "UPDATE foods
                SET stall_id = ?, food_name = ?, description = ?, price_range = ?, is_must_try = ?, image = ?
                WHERE food_id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isssisi", $stall_id, $food_name, $description, $price_range, $is_must_try, $final_image, $food_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Food updated successfully.";
            header("Location: ../admin/manage-foods.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update food.";
            header("Location: ../admin/food-form.php?id=" . $food_id);
            exit();
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $action = $_GET['action'] ?? '';
    $food_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($food_id <= 0) {
        $_SESSION['error'] = "Invalid food ID.";
        header("Location: ../admin/manage-foods.php");
        exit();
    }

    if ($action === 'activate') {
        $status = 'active';
    } elseif ($action === 'deactivate') {
        $status = 'inactive';
    } else {
        $_SESSION['error'] = "Invalid action.";
        header("Location: ../admin/manage-foods.php");
        exit();
    }

    $sql = "UPDATE foods SET status = ? WHERE food_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $food_id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Food status updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update food status.";
    }

    header("Location: ../admin/manage-foods.php");
    exit();

} else {
    header("Location: ../admin/manage-foods.php");
    exit();
}