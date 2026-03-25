<?php
require_once __DIR__ . '/../../../config.php';
session_start();

// ===== KIỂM TRA LOGIN =====
if (!isset($_SESSION['user_id'])) {
    die("Bạn chưa đăng nhập!");
}

$user_id = (int)$_SESSION['user_id'];

// ===== LẤY DỮ LIỆU =====
$selected = $_POST['selected'] ?? [];
$payment  = $_POST['payment'] ?? 'cod';
$address_type = $_POST['address_type'] ?? 'old';
$new_address  = trim($_POST['new_address'] ?? '');

// ===== VALIDATE =====
if (empty($selected)) {
    die("Không có sản phẩm nào!");
}

// ===== XỬ LÝ ĐỊA CHỈ CHUẨN =====
if ($address_type === 'new') {

    if (empty($new_address)) {
        die("Vui lòng nhập địa chỉ mới!");
    }

    $address = mysqli_real_escape_string($conn, $new_address);

} else {

    $res = mysqli_query($conn, "SELECT diachi FROM users WHERE id = $user_id");

    if (!$res) {
        die("Lỗi lấy địa chỉ: " . mysqli_error($conn));
    }

    $user = mysqli_fetch_assoc($res);

    if (!$user || empty($user['diachi'])) {
        die("Bạn chưa có địa chỉ mặc định, vui lòng nhập địa chỉ mới!");
    }

    $address = mysqli_real_escape_string($conn, $user['diachi']);
}

// ===== LẤY SẢN PHẨM =====
$ids = implode(',', array_map('intval', $selected));

$sql = "SELECT cart.*, shop.products.price 
        FROM cart 
        JOIN shop.products ON cart.product_id = shop.products.id
        WHERE cart.user_id = $user_id AND cart.id IN ($ids)";

$result = mysqli_query($conn, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Giỏ hàng trống!");
}

// ===== TÍNH TỔNG =====
$total = 0;
$items = [];

while ($row = mysqli_fetch_assoc($result)) {
    $row['thanhtien'] = $row['price'] * $row['quantity'];
    $total += $row['thanhtien'];
    $items[] = $row;
}

// ===== TẠO MÃ ĐƠN =====
$ma_don = 'DH' . time();

// ===== TẠO ORDER =====
$insert_order = "
INSERT INTO orders (user_id, ma_don, address, total_price, payment_method, order_date)
VALUES ($user_id, '$ma_don', '$address', $total, '$payment', NOW())
";

if (!mysqli_query($conn, $insert_order)) {
    die("Lỗi tạo đơn: " . mysqli_error($conn));
}

$order_id = mysqli_insert_id($conn);

// ===== LƯU CHI TIẾT =====
foreach ($items as $item) {
    $product_id = (int)$item['product_id'];
    $price      = (int)$item['price'];
    $quantity   = (int)$item['quantity'];

    mysqli_query($conn, "
        INSERT INTO order_details (order_id, product_id, price, quantity)
        VALUES ($order_id, $product_id, $price, $quantity)
    ");
}

// ===== XOÁ CART =====
mysqli_query($conn, "DELETE FROM cart WHERE id IN ($ids) AND user_id = $user_id");

// ===== CHUYỂN TRANG =====
header("Location: thanhcong.php?order_id=" . $order_id);
exit;