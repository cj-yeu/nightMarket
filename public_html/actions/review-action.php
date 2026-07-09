<?php
session_start();
include '../config/db.php';

function uploadReviewImage($file_tmp, $file_name) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $file_type = mime_content_type($file_tmp);

    if (!in_array($file_type, $allowed_types)) {
        return false;
    }

    $extension = pathinfo($file_name, PATHINFO_EXTENSION);
    $new_name = 'review_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $upload_path = '../assets/uploads/reviews/' . $new_name;

    if (move_uploaded_file($file_tmp, $upload_path)) {
        return $new_name;
    }

    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
        $_SESSION['error'] = "Please login as client to submit a review.";
        header("Location: ../login.php");
        exit();
    }

    $action = $_POST['action'];

    if ($action === 'create') {

        $user_id = $_SESSION['user_id'];
        $market_id = intval($_POST['market_id']);
        $rating = intval($_POST['rating']);
        $comment = trim($_POST['comment']);
        $visit_date = !empty($_POST['visit_date']) ? $_POST['visit_date'] : null;

        if ($market_id <= 0 || $rating < 1 || $rating > 5 || empty($comment)) {
            $_SESSION['error'] = "Please provide rating and comment.";
            header("Location: ../review.php?market_id=" . $market_id);
            exit();
        }

        $check_sql = "SELECT market_id FROM night_markets WHERE market_id = ? AND status = 'active' AND state = 'Selangor'";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "i", $market_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) === 0) {
            $_SESSION['error'] = "Night market not found.";
            header("Location: ../market-list.php");
            exit();
        }

        $sql = "INSERT INTO reviews (user_id, market_id, rating, comment, visit_date, status)
                VALUES (?, ?, ?, ?, ?, 'pending')";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iiiss", $user_id, $market_id, $rating, $comment, $visit_date);

        if (mysqli_stmt_execute($stmt)) {

            $review_id = mysqli_insert_id($conn);

            if (isset($_FILES['review_images']) && !empty($_FILES['review_images']['name'][0])) {

                $total_files = count($_FILES['review_images']['name']);

                for ($i = 0; $i < $total_files; $i++) {

                    if ($_FILES['review_images']['error'][$i] === UPLOAD_ERR_OK) {

                        $uploaded_image = uploadReviewImage(
                            $_FILES['review_images']['tmp_name'][$i],
                            $_FILES['review_images']['name'][$i]
                        );

                        if ($uploaded_image !== false) {
                            $image_sql = "INSERT INTO review_images (review_id, image) VALUES (?, ?)";
                            $image_stmt = mysqli_prepare($conn, $image_sql);
                            mysqli_stmt_bind_param($image_stmt, "is", $review_id, $uploaded_image);
                            mysqli_stmt_execute($image_stmt);
                        }
                    }
                }
            }

            $_SESSION['success'] = "Review submitted successfully. Please wait for admin approval.";
            header("Location: ../review.php?market_id=" . $market_id);
            exit();

        } else {
            $_SESSION['error'] = "Failed to submit review.";
            header("Location: ../review.php?market_id=" . $market_id);
            exit();
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit();
    }

    $action = $_GET['action'] ?? '';
    $review_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($review_id <= 0) {
        $_SESSION['error'] = "Invalid review ID.";
        header("Location: ../admin/manage-reviews.php");
        exit();
    }

    if ($action === 'approve') {

        $status = 'approved';

        $sql = "UPDATE reviews SET status = ? WHERE review_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $review_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Review approved successfully.";
        } else {
            $_SESSION['error'] = "Failed to approve review.";
        }

        header("Location: ../admin/manage-reviews.php");
        exit();

    } elseif ($action === 'reject') {

        $status = 'rejected';

        $sql = "UPDATE reviews SET status = ? WHERE review_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $review_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Review rejected successfully.";
        } else {
            $_SESSION['error'] = "Failed to reject review.";
        }

        header("Location: ../admin/manage-reviews.php");
        exit();

    } elseif ($action === 'delete') {

        $sql = "DELETE FROM reviews WHERE review_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $review_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Review deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete review.";
        }

        header("Location: ../admin/manage-reviews.php");
        exit();

    } else {
        $_SESSION['error'] = "Invalid action.";
        header("Location: ../admin/manage-reviews.php");
        exit();
    }

} else {
    header("Location: ../market-list.php");
    exit();
}