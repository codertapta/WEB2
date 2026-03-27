<?php
require_once __DIR__ . '/../../../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

// ========================================================
// XÁC ĐỊNH LUỒNG: "Mua ngay" (GET ?id) hay "Từ giỏ" (POST selected[])
// ========================================================
$is_buy_now = isset($_GET['id']) && (int)$_GET['id'] > 0;

$rows  = [];
$total = 0;

if ($is_buy_now) {
    // --- LUỒNG MUA NGAY ---
    $product_id = (int)$_GET['id'];
    $qty        = max(1, (int)($_GET['qty'] ?? 1));

    $sql    = "SELECT * FROM shop.products WHERE id = $product_id";
    $result = mysqli_query($conn, $sql);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        echo "<p style='text-align:center;margin-top:40px;color:#888'>
              Sản phẩm không tồn tại! 
              <a href='products.php' style='color:#d70018'>← Quay lại</a></p>";
        exit;
    }

    $thanhtien = $product['price'] * $qty;
    $rows[]    = [
        'name'      => $product['name'],
        'price'     => $product['price'],
        'quantity'  => $qty,
        'thanhtien' => $thanhtien,
    ];
    $total = $thanhtien;

} else {
    // --- LUỒNG TỪ GIỎ HÀNG ---
    $selected = $_POST['selected'] ?? [];

    if (empty($selected)) {
        echo "<p style='text-align:center;margin-top:40px;color:#888'>
              Bạn chưa chọn sản phẩm nào!
              <a href='cart.php' style='color:#d70018'>← Quay lại giỏ hàng</a></p>";
        exit;
    }

    $ids    = implode(',', array_map('intval', $selected));
    $sql    = "SELECT cart.*, shop.products.name, shop.products.price 
               FROM cart 
               JOIN shop.products ON cart.product_id = shop.products.id 
               WHERE cart.user_id = $user_id AND cart.id IN ($ids)";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $row['thanhtien'] = $row['price'] * $row['quantity'];
        $total += $row['thanhtien'];
        $rows[] = $row;
    }

    if (empty($rows)) {
        echo "<p style='text-align:center;margin-top:40px;color:#888'>
              Giỏ hàng trống!
              <a href='cart.php' style='color:#d70018'>← Quay lại</a></p>";
        exit;
    }
}
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán</title>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/checkout.css">
</head>
<body>

<header>
    <a href="<?php echo $is_buy_now ? 'products.php' : 'cart.php'; ?>" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        <?php echo $is_buy_now ? 'Trang chủ' : 'Giỏ hàng'; ?>
    </a>
    <h1>🧾 Xác nhận đặt hàng</h1>
</header>

<div class="container">

    <!-- CỘT TRÁI -->
    <div>
        <div class="card" style="margin-bottom:20px">
            <div class="card-title"><i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng</div>

            <form id="order-form" action="place_order.php" method="POST">

                <!-- Đánh dấu luồng để place_order.php biết xử lý theo cách nào -->
                <?php if ($is_buy_now): ?>
                    <input type="hidden" name="mode"       value="buy_now">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <input type="hidden" name="quantity"   value="<?php echo $qty; ?>">
                <?php else: ?>
                    <input type="hidden" name="mode" value="cart">
                    <?php foreach ($selected as $sid): ?>
                        <input type="hidden" name="selected[]" value="<?php echo (int)$sid; ?>">
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- Địa chỉ -->
                <label class="address-option">
                    <input type="radio" name="address_type" value="old" checked>
                    <span>
                        <i class="fas fa-home" style="color:#d70018;margin-right:6px"></i>
                        <?php echo htmlspecialchars($user['diachi']); ?>
                    </span>
                </label>

                <label class="address-option">
                    <input type="radio" name="address_type" value="new" id="radio_new_address">
                    <span>
                        <i class="fas fa-plus-circle" style="color:#d70018;margin-right:6px"></i>
                        Nhập địa chỉ mới
                    </span>
                </label>

                <input type="text"
                       id="new_address_input"
                       name="new_address"
                       class="new-address-input"
                       placeholder="Nhập địa chỉ giao hàng mới..."
                       style="display:none;">

                <!-- Thanh toán -->
                <div class="card" style="margin-top:20px">
                    <div class="card-title"><i class="fas fa-credit-card"></i> Phương thức thanh toán</div>

                    <label class="payment-option">
                        <input type="radio" name="payment" value="cod" checked>
                        <div class="pay-icon pay-cod"><i class="fas fa-truck"></i></div>
                        <div class="pay-info">
                            <strong>Thanh toán khi nhận hàng</strong>
                            <small>COD - Trả tiền mặt khi nhận</small>
                        </div>
                    </label>

                    <label class="payment-option">
                        <input type="radio" name="payment" value="bank" id="radio_bank">
                        <div class="pay-icon pay-bank"><i class="fas fa-university"></i></div>
                        <div class="pay-info">
                            <strong>Chuyển khoản ngân hàng</strong>
                            <small>Chuyển khoản trước khi giao</small>
                        </div>
                    </label>

                    <div class="bank-detail" id="bank-detail" style="display:none;">
                        <i class="fas fa-info-circle" style="color:#d70018"></i>
                        STK: <strong>123456789</strong> — Ngân hàng ABC<br>
                        Chủ TK: <strong>NGUYEN KHANH</strong>
                    </div>

                    <label class="payment-option">
                        <input type="radio" name="payment" value="online">
                        <div class="pay-icon pay-online"><i class="fas fa-mobile-alt"></i></div>
                        <div class="pay-info">
                            <strong>Thanh toán online</strong>
                            <small>Ví điện tử, QR Code</small>
                        </div>
                    </label>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-check-circle"></i> Xác nhận đặt hàng
                </button>

            </form>
        </div>
    </div>

    <!-- CỘT PHẢI: Tóm tắt -->
    <div class="card">
        <div class="card-title"><i class="fas fa-shopping-bag"></i> Tóm tắt đơn hàng</div>

        <table class="order-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th style="text-align:center">SL</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                <tr>
                    <td>
                        <div class="product-name"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div class="product-qty"><?php echo number_format($row['price']); ?>₫ / cái</div>
                    </td>
                    <td style="text-align:center;color:#888">x<?php echo $row['quantity']; ?></td>
                    <td><?php echo number_format($row['thanhtien']); ?>₫</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary-row">
            <span>Tạm tính</span>
            <span><?php echo number_format($total); ?>₫</span>
        </div>
        <div class="summary-row">
            <span>Phí vận chuyển</span>
            <span style="color:#2e7d32">Miễn phí</span>
        </div>
        <div class="summary-row total">
            <span>Tổng cộng</span>
            <span><?php echo number_format($total); ?>₫</span>
        </div>
    </div>

</div>

<script>
document.getElementById('radio_new_address').addEventListener('change', function() {
    const input = document.getElementById('new_address_input');
    input.style.display = this.checked ? 'block' : 'none';
    if (this.checked) input.focus();
});

document.getElementById('radio_bank').addEventListener('change', function() {
    document.getElementById('bank-detail').style.display = this.checked ? 'block' : 'none';
});

document.querySelectorAll('input[name="payment"]').forEach(el => {
    el.addEventListener('change', function() {
        if (this.value !== 'bank') {
            document.getElementById('bank-detail').style.display = 'none';
        }
    });
});
</script>

</body>
</html>