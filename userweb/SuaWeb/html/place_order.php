<?php
require_once __DIR__ . '/../../../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Bạn chưa đăng nhập!");
}

$user_id = (int)$_SESSION['user_id'];

// ===== LẤY DỮ LIỆU CHUNG =====
$mode         = $_POST['mode'] ?? 'cart';
$payment      = $_POST['payment'] ?? 'cod';
$address_type = $_POST['address_type'] ?? 'old';
$new_address  = trim($_POST['new_address'] ?? '');

// ===== XỬ LÝ ĐỊA CHỈ =====
if ($address_type === 'new') {

    if (empty($new_address)) {
        die("Vui lòng nhập địa chỉ mới!");
    }
    $address = mysqli_real_escape_string($conn, $new_address);

} else {

    $res  = mysqli_query($conn, "SELECT diachi FROM users WHERE id = $user_id");
    $user = mysqli_fetch_assoc($res);

    if (!$user || empty($user['diachi'])) {
        die("Bạn chưa có địa chỉ mặc định, vui lòng nhập địa chỉ mới!");
    }
    $address = mysqli_real_escape_string($conn, $user['diachi']);
}

// ===== LẤY SẢN PHẨM THEO MODE =====
$total = 0;
$items = [];

if ($mode === 'buy_now') {
    // --- MUA NGAY: lấy thẳng từ products ---
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity   = max(1, (int)($_POST['quantity'] ?? 1));

    if ($product_id <= 0) {
        die("Sản phẩm không hợp lệ!");
    }

    $res     = mysqli_query($conn, "SELECT * FROM shop.products WHERE id = $product_id");
    $product = mysqli_fetch_assoc($res);

    if (!$product) {
        die("Sản phẩm không tồn tại!");
    }

    $items[] = [
        'product_id' => $product_id,
        'price'      => $product['price'],
        'quantity'   => $quantity,
        'thanhtien'  => $product['price'] * $quantity,
    ];
    $total = $items[0]['thanhtien'];

} else {
    // --- TỪ GIỎ HÀNG: lấy từ cart ---
    $selected = $_POST['selected'] ?? [];

    if (empty($selected)) {
        die("Không có sản phẩm nào!");
    }

    $ids    = implode(',', array_map('intval', $selected));
    $sql    = "SELECT cart.*, shop.products.price 
               FROM cart 
               JOIN shop.products ON cart.product_id = shop.products.id
               WHERE cart.user_id = $user_id AND cart.id IN ($ids)";
    $result = mysqli_query($conn, $sql);

    if (!$result || mysqli_num_rows($result) == 0) {
        die("Giỏ hàng trống!");
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $row['thanhtien'] = $row['price'] * $row['quantity'];
        $total += $row['thanhtien'];
        $items[] = $row;
    }
}

// ===== TẠO MÃ ĐƠN =====
$ma_don = 'DH' . time();

// ===== INSERT ORDER =====
$insert_order = "
    INSERT INTO orders (user_id, ma_don, address, total_price, payment_method, order_date, status)
    VALUES ($user_id, '$ma_don', '$address_escaped', $total, '$payment_escaped', NOW(), 'pending')
";

if (!mysqli_query($conn, $insert_order)) {
    die("Lỗi tạo đơn: " . mysqli_error($conn));
}

$order_id = mysqli_insert_id($conn);

// ===== INSERT ORDER DETAILS =====
foreach ($items as $item) {
    $pid = (int)$item['product_id'];
    $prc = (int)$item['price'];
    $qty = (int)$item['quantity'];

    mysqli_query($conn, "
        INSERT INTO order_details (order_id, product_id, price, quantity)
        VALUES ($order_id, $pid, $prc, $qty)
    ");
}

// ===== XOÁ CART (chỉ khi từ giỏ hàng) =====
if ($mode === 'cart') {
    $ids = implode(',', array_map('intval', $selected));
    mysqli_query($conn, "DELETE FROM cart WHERE id IN ($ids) AND user_id = $user_id");
}

// ===== CHUYỂN TRANG =====
header("Location: thanhcong.php?order_id=" . $order_id);
exit;