<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

function uploadSocialImage($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    $file_type = mime_content_type($file['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        return false;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_name = 'social_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
    $upload_path = '../assets/uploads/social/' . $new_name;

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $new_name;
    }

    return false;
}

function generateSimpleKeywords($text) {
    $text = strtolower(strip_tags($text));
    $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);

    $words = explode(' ', $text);

    $stop_words = [
        'the', 'and', 'for', 'with', 'this', 'that', 'from', 'you', 'your',
        'are', 'was', 'were', 'have', 'has', 'had', 'not', 'but', 'all',
        'our', 'can', 'will', 'just', 'very', 'also', 'into', 'about',
        'market', 'night', 'food', 'selangor'
    ];

    $counts = [];

    foreach ($words as $word) {
        $word = trim($word);

        if (strlen($word) < 4) {
            continue;
        }

        if (in_array($word, $stop_words)) {
            continue;
        }

        if (!isset($counts[$word])) {
            $counts[$word] = 0;
        }

        $counts[$word]++;
    }

    arsort($counts);

    $keywords = array_slice(array_keys($counts), 0, 8);

    return implode(', ', $keywords);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'];
    $market_id = intval($_POST['market_id']);
    $platform = trim($_POST['platform']);
    $post_url = trim($_POST['post_url']);
    $post_title = trim($_POST['post_title']);
    $post_content = trim($_POST['post_content']);
    $extracted_keywords = trim($_POST['extracted_keywords']);
    $mentioned_food = trim($_POST['mentioned_food']);

    if (empty($market_id) || empty($platform) || empty($post_title) || empty($post_content)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: ../admin/social-media-form.php");
        exit();
    }

    $valid_platforms = ['Facebook', 'Instagram', 'TikTok', 'Xiaohongshu', 'Other'];

    if (!in_array($platform, $valid_platforms)) {
        $_SESSION['error'] = "Invalid platform selected.";
        header("Location: ../admin/social-media-form.php");
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
        $_SESSION['error'] = "Selected night market not found.";
        header("Location: ../admin/social-media-form.php");
        exit();
    }

    if (empty($extracted_keywords)) {
        $extracted_keywords = generateSimpleKeywords($post_title . " " . $post_content);
    }

    $uploaded_image = uploadSocialImage($_FILES['image']);

    if ($uploaded_image === false) {
        $_SESSION['error'] = "Invalid image upload. Please upload JPG, PNG, or WEBP image.";
        header("Location: ../admin/social-media-form.php");
        exit();
    }

    if ($action === 'create') {

        $added_by = $_SESSION['user_id'];

        $sql = "INSERT INTO social_media_data
                (market_id, platform, post_url, post_title, post_content, extracted_keywords, mentioned_food, image, added_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "isssssssi",
            $market_id,
            $platform,
            $post_url,
            $post_title,
            $post_content,
            $extracted_keywords,
            $mentioned_food,
            $uploaded_image,
            $added_by
        );

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Social media data added successfully.";
            header("Location: ../admin/social-media-data.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to add social media data.";
            header("Location: ../admin/social-media-form.php");
            exit();
        }

    } elseif ($action === 'update') {

        $social_data_id = intval($_POST['social_data_id']);
        $old_image = $_POST['old_image'];
        $final_image = $uploaded_image ? $uploaded_image : $old_image;

        $sql = "UPDATE social_media_data
                SET market_id = ?, platform = ?, post_url = ?, post_title = ?, post_content = ?,
                    extracted_keywords = ?, mentioned_food = ?, image = ?
                WHERE social_data_id = ?";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param(
            $stmt,
            "isssssssi",
            $market_id,
            $platform,
            $post_url,
            $post_title,
            $post_content,
            $extracted_keywords,
            $mentioned_food,
            $final_image,
            $social_data_id
        );

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Social media data updated successfully.";
            header("Location: ../admin/social-media-data.php");
            exit();
        } else {
            $_SESSION['error'] = "Failed to update social media data.";
            header("Location: ../admin/social-media-form.php?id=" . $social_data_id);
            exit();
        }
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $action = $_GET['action'] ?? '';
    $social_data_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($social_data_id <= 0) {
        $_SESSION['error'] = "Invalid social media data ID.";
        header("Location: ../admin/social-media-data.php");
        exit();
    }

    if ($action === 'activate' || $action === 'deactivate') {

        $status = ($action === 'activate') ? 'active' : 'inactive';

        $sql = "UPDATE social_media_data SET status = ? WHERE social_data_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $social_data_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Social media data status updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update social media data status.";
        }

        header("Location: ../admin/social-media-data.php");
        exit();

    } elseif ($action === 'delete') {

        $sql = "DELETE FROM social_media_data WHERE social_data_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $social_data_id);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Social media data deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete social media data.";
        }

        header("Location: ../admin/social-media-data.php");
        exit();

    } else {
        $_SESSION['error'] = "Invalid action.";
        header("Location: ../admin/social-media-data.php");
        exit();
    }

} else {
    header("Location: ../admin/social-media-data.php");
    exit();
}