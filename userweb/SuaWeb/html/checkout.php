<?php
// ===== PHẢI ĐẦU TIÊN, KHÔNG CÓ GÌ TRƯỚC =====
require_once __DIR__ . '/../../../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin user
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id"));

$selected = $_POST['selected'] ?? [];

if (empty($selected)) {
    echo "<p style='text-align:center;margin-top:40px;color:#888'>Bạn chưa chọn sản phẩm nào! 
          <a href='cart.php' style='color:#d70018'>← Quay lại giỏ hàng</a></p>";
    exit;
}

// Lấy sản phẩm từ giỏ hàng
$ids = implode(',', array_map('intval', $selected));
$sql = "SELECT cart.*, shop.products.name, shop.products.price 
        FROM cart 
        JOIN shop.products ON cart.product_id = shop.products.id 
        WHERE cart.user_id = $user_id AND cart.id IN ($ids)";

$result = mysqli_query($conn, $sql);
$rows = [];
$total = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $row['thanhtien'] = $row['price'] * $row['quantity'];
    $total += $row['thanhtien'];
    $rows[] = $row;
}

if (empty($rows)) {
    echo "<p style='text-align:center;margin-top:40px;color:#888'>Giỏ hàng trống! 
          <a href='cart.php' style='color:#d70018'>← Quay lại</a></p>";
    exit;
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
    <a href="cart.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Giỏ hàng
    </a>
    <h1>🧾 Xác nhận đặt hàng</h1>
</header>

<div class="container">

    <!-- CỘT TRÁI: Địa chỉ + Thanh toán -->
    <div>

        <!-- ĐỊA CHỈ GIAO HÀNG -->
        <div class="card" style="margin-bottom:20px">
            <div class="card-title"><i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng</div>
            
            <form id="order-form" action="place_order.php" method="POST">
                
                <!-- Địa chỉ cũ -->
                <label class="address-option">
                    <input type="radio" name="address_type" value="old" checked>
                    <span>
                        <i class="fas fa-home" style="color:#d70018;margin-right:6px"></i>
                        <?php echo htmlspecialchars($user['diachi']); ?>
                    </span>
                </label>

                <!-- Địa chỉ mới -->
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
                       style="display: none;">

                <!-- Hidden selected products -->
                <?php foreach ($selected as $sid): ?>
                    <input type="hidden" name="selected[]" value="<?php echo (int)$sid; ?>">
                <?php endforeach; ?>

                <!-- PHƯƠNG THỨC THANH TOÁN -->
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

    <!-- CỘT PHẢI: Tóm tắt đơn hàng -->
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
// Toggle hiển thị input địa chỉ mới
document.getElementById('radio_new_address').addEventListener('change', function() {
    const input = document.getElementById('new_address_input');
    input.style.display = this.checked ? 'block' : 'none';
    
    if (this.checked) input.focus();
});

// Ẩn/Hiện chi tiết ngân hàng
document.getElementById('radio_bank').addEventListener('change', function() {
    document.getElementById('bank-detail').style.display = this.checked ? 'block' : 'none';
});

// Xử lý tất cả radio payment
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