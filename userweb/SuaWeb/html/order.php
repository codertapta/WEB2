<?php
ob_start();
require __DIR__ . "/../../../config.php";

session_start();
$user_id = $_SESSION['user_id'];

// ===== LẤY DỮ LIỆU =====
$address_type = $_POST['address_type'] ?? 'old';
$new_address  = $_POST['new_address'] ?? '';
$payment      = $_POST['payment'] ?? 'cod';

// ===== LẤY ĐỊA CHỈ =====
if ($address_type == 'new' && !empty($new_address)) {
    $address = $new_address;
} else {
    $user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
    $address = $user['diachi'];
}

// ===== NHẬN SELECTED[] TỪ CHECKOUT =====
$selected = $_POST['selected'] ?? [];

if (empty($selected)) {
    die("❌ Không có sản phẩm nào được chọn!");
}

$ids = implode(',', array_map('intval', $selected));

// ===== LẤY GIỎ HÀNG (chỉ sản phẩm được chọn) =====
$cart = mysqli_query($conn, "
SELECT cart.*, shop.products.price 
FROM cart 
JOIN shop.products ON cart.product_id = shop.products.id
WHERE cart.user_id = $user_id AND cart.id IN ($ids)
");

if (mysqli_num_rows($cart) == 0) {
    die("❌ Giỏ hàng trống!");
}

// ===== TÍNH TỔNG =====
$total = 0;
while ($c = mysqli_fetch_assoc($cart)) {
    $total += $c['price'] * $c['quantity'];
}

mysqli_data_seek($cart, 0);

// ===== TẠO MÃ ĐƠN HÀNG RANDOM =====
$ma_don = 'MUIT-' . strtoupper(substr(md5(uniqid()), 0, 6));

// ===== INSERT ORDER =====
$insert_order = mysqli_query($conn, "
INSERT INTO orders(user_id, address, payment_method, total_price, order_date, ma_don)
VALUES($user_id, '$address', '$payment', $total, NOW(), '$ma_don')
");

if (!$insert_order) {
    die("❌ Lỗi insert order: " . mysqli_error($conn));
}

$order_id = mysqli_insert_id($conn);

// ===== INSERT DETAILS =====
while ($c = mysqli_fetch_assoc($cart)) {
    $insert_detail = mysqli_query($conn, "
    INSERT INTO order_details(order_id, product_id, quantity, price)
    VALUES($order_id, {$c['product_id']}, {$c['quantity']}, {$c['price']})
    ");

    if (!$insert_detail) {
        die("❌ Lỗi order_details: " . mysqli_error($conn));
    }
}

// ===== XÓA CHỈ SẢN PHẨM ĐÃ ĐẶT =====
mysqli_query($conn, "DELETE FROM cart WHERE user_id=$user_id AND id IN ($ids)");

// ===== CHUYỂN TRANG =====
header("Location: thanhcong.php?order_id=" . $order_id); exit;