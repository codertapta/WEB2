<?php
session_start();
include("../../../config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ho     = trim($_POST['ho'] ?? '');
    $ten    = trim($_POST['ten'] ?? '');
    $sdt    = trim($_POST['sdt'] ?? '');
    $diachi = trim($_POST['diachi'] ?? '');

    $error = null;

    // 1. Kiểm tra họ
    if (!preg_match('/^[\p{L}\s]+$/u', $ho)) {
        $error = "Họ không được chứa số hoặc ký tự đặc biệt!";
    } elseif (empty($ho)) {
        $error = "Họ không được để trống!";
    }

    // 2. Kiểm tra tên
    if (!$error && !preg_match('/^[\p{L}\s]+$/u', $ten)) {
        $error = "Tên không được chứa số hoặc ký tự đặc biệt!";
    } elseif (empty($ten)) {
        $error = "Tên không được để trống!";
    }

    // 3. Kiểm tra số điện thoại
    if (!$error && !preg_match('/^0[0-9]{9}$/', $sdt)) {
        $error = "Số điện thoại phải bắt đầu bằng 0 và có đúng 10 chữ số!";
    }

    // 4. Kiểm tra địa chỉ
    if (!$error && !preg_match('/^[\p{L}\p{N}\s\/]+$/u', $diachi)) {
        $error = "Địa chỉ không được chứa ký tự đặc biệt (chỉ cho phép chữ, số, khoảng trắng và dấu /)!";
    }

    // 5. Kiểm tra trùng số điện thoại với người dùng khác (nếu cần)
    if (!$error) {
        $sdt_esc = mysqli_real_escape_string($conn, $sdt);
        $check_sdt = mysqli_query($conn, "SELECT id FROM users WHERE sdt = '$sdt_esc' AND id != $user_id");
        if (mysqli_num_rows($check_sdt) > 0) {
            $error = "Số điện thoại này đã được sử dụng bởi tài khoản khác!";
        }
    }

    // Cập nhật nếu không có lỗi
    if (!$error) {
        $ho_esc     = mysqli_real_escape_string($conn, $ho);
        $ten_esc    = mysqli_real_escape_string($conn, $ten);
        $diachi_esc = mysqli_real_escape_string($conn, $diachi);

        $sql = "UPDATE users SET ho='$ho_esc', ten='$ten_esc', sdt='$sdt_esc', diachi='$diachi_esc' WHERE id=$user_id";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['edit_success'] = "Cập nhật thông tin thành công!";
            header("Location: edit-profile.php");
            exit;
        } else {
            $error = "Có lỗi xảy ra, vui lòng thử lại!";
        }
    }

    // Nếu có lỗi, lưu vào session và quay lại form
    if ($error) {
        $_SESSION['edit_error'] = $error;
        header("Location: edit-profile.php");
        exit;
    }
} else {
    // Nếu không phải POST thì chuyển về form
    header("Location: edit-profile.php");
    exit;
}
?>