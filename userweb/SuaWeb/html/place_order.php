<?php
require_once __DIR__ . '/../../../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    $_SESSION['order_error'] = "Bạn chưa đăng nhập!";
    header("Location: checkout.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// ===== LẤY DỮ LIỆU CHUNG =====
$mode         = $_POST['mode']         ?? 'cart';
$address_type = $_POST['address_type'] ?? 'old';
$new_address  = trim($_POST['new_address'] ?? '');
$payment      = mysqli_real_escape_string($conn, $_POST['payment'] ?? 'cod');

// ===== XỬ LÝ ĐỊA CHỈ =====
if ($address_type === 'new') {
    if (empty($new_address)) {
        $_SESSION['order_error'] = "Vui lòng nhập địa chỉ mới!";
        header("Location: checkout.php");
        exit;
    }
    $address = mysqli_real_escape_string($conn, $new_address);
} else {
    $res  = mysqli_query($conn, "SELECT diachi FROM users WHERE id = $user_id");
    $user = mysqli_fetch_assoc($res);
    if (!$user || empty($user['diachi'])) {
        $_SESSION['order_error'] = "Bạn chưa có địa chỉ mặc định, vui lòng nhập địa chỉ mới!";
        header("Location: checkout.php");
        exit;
    }
    $address = mysqli_real_escape_string($conn, $user['diachi']);
}

// ===== LẤY SẢN PHẨM THEO MODE =====
$total    = 0;
$items    = [];
$selected = [];

if ($mode === 'buy_now') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity   = max(1, (int)($_POST['quantity'] ?? 1));

    if ($product_id <= 0) {
        $_SESSION['order_error'] = "Sản phẩm không hợp lệ!";
        header("Location: checkout.php");
        exit;
    }

    $res     = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id");
    $product = mysqli_fetch_assoc($res);

    if (!$product) {
        $_SESSION['order_error'] = "Sản phẩm không tồn tại!";
        header("Location: checkout.php");
        exit;
    }

    // Kiểm tra tồn kho
    if ($product['quantity'] < $quantity) {
        $_SESSION['order_error'] = "Sản phẩm '{$product['name']}' chỉ còn {$product['quantity']} cái, không đủ số lượng!";
        header("Location: checkout.php");
        exit;
    }

    $items[] = [
        'product_id' => $product_id,
        'price'      => $product['price'],
        'quantity'   => $quantity,
    ];
    $total = $product['price'] * $quantity;

} else {
    $selected = $_POST['selected'] ?? [];

    if (empty($selected)) {
        $_SESSION['order_error'] = "Không có sản phẩm nào!";
        header("Location: checkout.php");
        exit;
    }

    $ids    = implode(',', array_map('intval', $selected));
    $result = mysqli_query($conn, "
        SELECT cart.*, products.price, products.name, products.quantity as stock
        FROM cart 
        JOIN products ON cart.product_id = products.id
        WHERE cart.user_id = $user_id AND cart.id IN ($ids)
    ");

    if (!$result || mysqli_num_rows($result) == 0) {
        $_SESSION['order_error'] = "Giỏ hàng trống!";
        header("Location: checkout.php");
        exit;
    }

    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['stock'] < $row['quantity']) {
            $_SESSION['order_error'] = "Sản phẩm '{$row['name']}' chỉ còn {$row['stock']} cái, vui lòng cập nhật giỏ hàng!";
            header("Location: checkout.php");
            exit;
        }
        $total += $row['price'] * $row['quantity'];
        $items[] = [
            'product_id' => $row['product_id'],
            'price'      => $row['price'],
            'quantity'   => $row['quantity'],
            'cart_id'    => $row['id']
        ];
    }
}

// Bắt đầu transaction
mysqli_begin_transaction($conn);

try {
    // 1. Cập nhật tồn kho
    foreach ($items as $item) {
        $pid = (int)$item['product_id'];
        $qty = (int)$item['quantity'];
        $update_sql = "UPDATE products SET quantity = quantity - $qty WHERE id = $pid AND quantity >= $qty";
        mysqli_query($conn, $update_sql);
        if (mysqli_affected_rows($conn) == 0) {
            throw new Exception("Cập nhật tồn kho thất bại cho sản phẩm ID $pid (có thể do hết hàng)");
        }
    }

    // 2. Tạo đơn hàng
    $ma_don = 'DH' . time();
    $sql_order = "INSERT INTO orders (user_id, ma_don, address, total_price, payment_method, order_date, status)
                  VALUES ($user_id, '$ma_don', '$address', $total, '$payment', NOW(), 'pending')";
    if (!mysqli_query($conn, $sql_order)) {
        throw new Exception("Lỗi tạo đơn: " . mysqli_error($conn));
    }
    $order_id = mysqli_insert_id($conn);

    // 3. Chi tiết đơn hàng
    foreach ($items as $item) {
        $pid = (int)$item['product_id'];
        $prc = (int)$item['price'];
        $qty = (int)$item['quantity'];
        $sql_detail = "INSERT INTO order_details (order_id, product_id, price, quantity)
                       VALUES ($order_id, $pid, $prc, $qty)";
        if (!mysqli_query($conn, $sql_detail)) {
            throw new Exception("Lỗi thêm chi tiết đơn hàng: " . mysqli_error($conn));
        }
    }

    // 4. Xóa giỏ hàng
    if ($mode === 'cart' && !empty($selected)) {
        $ids = implode(',', array_map('intval', $selected));
        mysqli_query($conn, "DELETE FROM cart WHERE id IN ($ids) AND user_id = $user_id");
    }

    mysqli_commit($conn);
    header("Location: thanhcong.php?order_id=" . $order_id);
    exit;

} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['order_error'] = "Đặt hàng thất bại: " . $e->getMessage();
    header("Location: checkout.php");
    exit;
}
?>