<?php
ob_start();
require_once __DIR__ . '/../../../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id  = (int)$_SESSION['user_id'];
$order_id = (int)($_POST['order_id'] ?? 0);

if ($order_id <= 0) {
    header("Location: orders.php");
    exit;
}

// Kiểm tra đơn này có thuộc user không, và status phải là confirmed mới được bấm
$check = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT id, status FROM orders 
     WHERE id = $order_id AND user_id = $user_id"
));

if (!$check) {
    die("Không tìm thấy đơn hàng!");
}

if ($check['status'] !== 'confirmed') {
    die("Đơn hàng chưa được xác nhận, không thể cập nhật!");
}

// Cập nhật sang delivered
mysqli_query($conn,
    "UPDATE orders SET status = 'delivered' WHERE id = $order_id AND user_id = $user_id"
);

header("Location: orders.php");
exit;