<?php
session_start();
include('../../../config.php'); // Chỉnh đường dẫn tới file config.php của bạn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Lấy admin từ DB
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        // So sánh mật khẩu trực tiếp (plain text)
        if ($password === $admin['password']) {
            $_SESSION['adminLoggedIn'] = true;
            $_SESSION['adminUser'] = $admin;
            header("Location: index.php"); // chuyển tới dashboard
            exit;
        }
    }

    // Sai username/password
    $_SESSION['loginError'] = "Tên đăng nhập hoặc mật khẩu không đúng!";
    header("Location: admin-login.php");
    exit;
}
