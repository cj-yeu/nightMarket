<?php
session_start();
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please enter your email and password.";
        header("Location: ../login.php");
        exit();
    }

    $sql = "SELECT user_id, full_name, email, password, role, status FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 1) {

        $user = mysqli_fetch_assoc($result);

        if ($user['status'] !== 'active') {
            $_SESSION['error'] = "Your account is inactive. Please contact admin.";
            header("Location: ../login.php");
            exit();
        }

        if (password_verify($password, $user['password'])) {

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/dashboard.php");
                exit();
            } else {
                header("Location: ../index.php");
                exit();
            }

        } else {
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: ../login.php");
            exit();
        }

    } else {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: ../login.php");
        exit();
    }

} else {
    header("Location: ../login.php");
    exit();
}