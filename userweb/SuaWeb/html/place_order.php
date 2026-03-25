<?php
require_once __DIR__ . '/../../../config.php';
session_start();

$user_id = $_SESSION['user_id'];
$selected = $_POST['selected'] ?? [];
$payment  = $_POST['payment'] ?? 'cod';

// ❗ check selected
if (empty($selected)) {
    die("Không có sản phẩm nào!");
}

// Lấy địa chỉ
if ($_POST['address_type'] == 'new' && !empty($_POST['new_address'])) {
    $address = mysqli_real_escape_string($conn, $_POST['new_address']);
} else {
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT diachi FROM users WHERE id=$user_id"));
    $address = $user['diachi'];
}

// Lấy sản phẩm
$ids = implode(',', array_map('intval', $selected));
$sql = "SELECT cart.*, shop.products.price 
        FROM cart 
        JOIN shop.products ON cart.product_id = shop.products.id
        WHERE cart.user_id = $user_id AND cart.id IN ($ids)";
$result = mysqli_query($conn, $sql);

$total = 0;
$items = [];

while ($row = mysqli_fetch_assoc($result)) {
    $thanhtien = $row['price'] * $row['quantity'];
    $total += $thanhtien;
    $items[] = $row;
}

// Tạo đơn hàng
$sql_insert = "
  INSERT INTO orders(user_id, total_price, payment_method, address, order_date)
  VALUES($user_id, $total, '$payment', '$address', NOW())
";

if (!mysqli_query($conn, $sql_insert)) {
    die("Lỗi tạo đơn hàng: " . mysqli_error($conn));
}

$order_id = mysqli_insert_id($conn);

// Thêm chi tiết
foreach ($items as $item) {
    mysqli_query($conn, "
      INSERT INTO order_details(order_id, product_id, quantity, price)
      VALUES($order_id, {$item['product_id']}, {$item['quantity']}, {$item['price']})
    ");
}

// Xoá cart
mysqli_query($conn, "DELETE FROM cart WHERE id IN ($ids) AND user_id=$user_id");

// Redirect
header("Location: thanhcong.php?order_id=" . $order_id);
exit;