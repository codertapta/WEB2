<?php
// ===== PHẢI ĐẦU TIÊN, KHÔNG CÓ GÌ TRƯỚC =====
require_once __DIR__ . '/../../../config.php';
session_start();

$user_id = $_SESSION['user_id'];
$user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));

$selected = $_POST['selected'] ?? [];
$rows     = [];
$total    = 0;
$ids      = '';

if (!empty($selected)) {
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
}
?>
<!doctype html>
<!-- phần còn lại giữ nguyên y chang -->
 <?php require_once __DIR__ . '/../../../config.php'; ?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Thanh toán</title>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Be Vietnam Pro', sans-serif;
      background: #f5f5f7;
      color: #1a1a1a;
      min-height: 100vh;
    }

    /* HEADER */
    header {
      background: #d70018;
      color: white;
      padding: 16px 32px;
      display: flex;
      align-items: center;
      gap: 16px;
      box-shadow: 0 2px 12px rgba(215,0,24,0.3);
    }

    header h1 {
      font-size: 1.2rem;
      font-weight: 700;
      letter-spacing: 0.5px;
    }

    .back-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255,255,255,0.15);
      color: white;
      border: 1px solid rgba(255,255,255,0.3);
      padding: 7px 14px;
      border-radius: 8px;
      font-size: 0.85rem;
      font-weight: 500;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.2s;
    }

    .back-btn:hover { background: rgba(255,255,255,0.25); }

    /* LAYOUT */
    .container {
      max-width: 960px;
      margin: 32px auto;
      padding: 0 20px;
      display: grid;
      grid-template-columns: 1fr 380px;
      gap: 24px;
      align-items: start;
    }

    /* CARD */
    .card {
      background: white;
      border-radius: 16px;
      padding: 28px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.07);
    }

    .card-title {
      font-size: 1rem;
      font-weight: 700;
      color: #d70018;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
      padding-bottom: 14px;
      border-bottom: 2px solid #f5f5f7;
    }

    /* ĐỊA CHỈ */
    .address-option {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      padding: 14px;
      border: 2px solid #eee;
      border-radius: 10px;
      margin-bottom: 10px;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
    }

    .address-option:has(input:checked) {
      border-color: #d70018;
      background: #fff5f5;
    }

    .address-option input[type="radio"] {
      accent-color: #d70018;
      margin-top: 3px;
      width: 16px;
      height: 16px;
    }

    .address-option span {
      font-size: 0.9rem;
      font-weight: 500;
      color: #333;
    }

    .new-address-input {
      width: 100%;
      padding: 12px 14px;
      border: 2px solid #eee;
      border-radius: 10px;
      font-size: 0.9rem;
      font-family: inherit;
      margin-top: 8px;
      transition: border-color 0.2s;
      display: none;
    }

    .new-address-input:focus {
      outline: none;
      border-color: #d70018;
    }

    /* THANH TOÁN */
    .payment-option {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px;
      border: 2px solid #eee;
      border-radius: 10px;
      margin-bottom: 10px;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
    }

    .payment-option:has(input:checked) {
      border-color: #d70018;
      background: #fff5f5;
    }

    .payment-option input[type="radio"] {
      accent-color: #d70018;
      width: 16px;
      height: 16px;
    }

    .payment-option .pay-icon {
      width: 36px;
      height: 36px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1rem;
    }

    .pay-cod   { background: #fff3e0; color: #e65100; }
    .pay-bank  { background: #e8f5e9; color: #2e7d32; }
    .pay-online{ background: #e3f2fd; color: #1565c0; }

    .pay-info { flex: 1; }
    .pay-info strong { display: block; font-size: 0.9rem; font-weight: 600; }
    .pay-info small  { font-size: 0.78rem; color: #888; }

    .bank-detail {
      background: #f9f9f9;
      border-left: 3px solid #d70018;
      border-radius: 6px;
      padding: 10px 14px;
      margin: 6px 0 10px 48px;
      font-size: 0.82rem;
      color: #555;
      display: none;
    }

    /* BẢNG SẢN PHẨM */
    .order-table {
      width: 100%;
      border-collapse: collapse;
    }

    .order-table th {
      text-align: left;
      font-size: 0.78rem;
      font-weight: 600;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      padding: 8px 0;
      border-bottom: 1px solid #eee;
    }

    .order-table td {
      padding: 12px 0;
      font-size: 0.88rem;
      border-bottom: 1px solid #f5f5f5;
      vertical-align: middle;
    }

    .order-table td:last-child,
    .order-table th:last-child {
      text-align: right;
    }

    .product-name { font-weight: 600; color: #1a1a1a; }
    .product-qty  { color: #888; font-size: 0.82rem; }

    /* TỔNG & NÚT */
    .summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      font-size: 0.88rem;
      color: #666;
    }

    .summary-row.total {
      border-top: 2px solid #eee;
      margin-top: 8px;
      padding-top: 16px;
      font-size: 1.1rem;
      font-weight: 700;
      color: #d70018;
    }

    .btn-submit {
      width: 100%;
      padding: 15px;
      background: #d70018;
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      margin-top: 20px;
      transition: background 0.2s, transform 0.1s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-submit:hover   { background: #b8001a; }
    .btn-submit:active  { transform: scale(0.98); }

    @media (max-width: 700px) {
      .container { grid-template-columns: 1fr; }
    }
  </style>
</head>

<body>

<!-- HEADER -->
<header>
  <a href="cart.php" class="back-btn">
    <i class="fas fa-arrow-left"></i> Giỏ hàng
  </a>
  <h1>🧾 Xác nhận đặt hàng</h1>
</header>

<?php
$user_id = $_SESSION['user_id'];
$user    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));

$selected = $_POST['selected'] ?? [];

if (empty($selected)) {
  echo "<p style='text-align:center;margin-top:40px;color:#888'>Bạn chưa chọn sản phẩm nào! <a href='cart.php' style='color:#d70018'>← Quay lại giỏ hàng</a></p>";
  exit;
}

$ids    = implode(',', array_map('intval', $selected));
$sql    = "SELECT cart.*, shop.products.name, shop.products.price 
           FROM cart 
           JOIN shop.products ON cart.product_id = shop.products.id
           WHERE cart.user_id = $user_id AND cart.id IN ($ids)";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
  echo "<p style='text-align:center;margin-top:40px;color:#888'>Giỏ hàng trống! <a href='cart.php' style='color:#d70018'>← Quay lại</a></p>";
  exit;
}

$rows  = [];
$total = 0;
while ($row = mysqli_fetch_assoc($result)) {
  $row['thanhtien'] = $row['price'] * $row['quantity'];
  $total += $row['thanhtien'];
  $rows[] = $row;
}
?>

<div class="container">

  <!-- CỘT TRÁI: Địa chỉ + Thanh toán -->
  <div>

    <!-- ĐỊA CHỈ -->
    <div class="card" style="margin-bottom:20px">
      <div class="card-title"><i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng</div>

      <label class="address-option">
        <input type="radio" name="address_type" value="old" form="order-form" checked>
        <span><i class="fas fa-home" style="color:#d70018;margin-right:6px"></i><?php echo htmlspecialchars($user['diachi']); ?></span>
      </label>

      <label class="address-option" onclick="toggleNewAddress(true)">
        <input type="radio" name="address_type" value="new" form="order-form" onchange="toggleNewAddress(true)">
        <span><i class="fas fa-plus-circle" style="color:#d70018;margin-right:6px"></i>Nhập địa chỉ mới</span>
      </label>

      <input type="text" id="new-address-input" name="new_address" 
             form="order-form" class="new-address-input" 
             placeholder="Nhập địa chỉ giao hàng mới...">
    </div>

    <!-- THANH TOÁN -->
    <div class="card">
      <div class="card-title"><i class="fas fa-credit-card"></i> Phương thức thanh toán</div>

      <label class="payment-option">
        <input type="radio" name="payment" value="cod" form="order-form" checked>
        <div class="pay-icon pay-cod"><i class="fas fa-truck"></i></div>
        <div class="pay-info">
          <strong>Thanh toán khi nhận hàng</strong>
          <small>COD - Trả tiền mặt khi nhận</small>
        </div>
      </label>

      <label class="payment-option" onclick="document.getElementById('bank-detail').style.display='block'">
        <input type="radio" name="payment" value="bank" form="order-form" onchange="document.getElementById('bank-detail').style.display='block'">
        <div class="pay-icon pay-bank"><i class="fas fa-university"></i></div>
        <div class="pay-info">
          <strong>Chuyển khoản ngân hàng</strong>
          <small>Chuyển khoản trước khi giao</small>
        </div>
      </label>

      <div class="bank-detail" id="bank-detail">
        <i class="fas fa-info-circle" style="color:#d70018"></i>
        STK: <strong>123456789</strong> — Ngân hàng ABC<br>
        Chủ TK: <strong>NGUYEN KHANH</strong>
      </div>

      <label class="payment-option">
        <input type="radio" name="payment" value="online" form="order-form">
        <div class="pay-icon pay-online"><i class="fas fa-mobile-alt"></i></div>
        <div class="pay-info">
          <strong>Thanh toán online</strong>
          <small>Ví điện tử, QR Code</small>
        </div>
      </label>
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

    <!-- FORM GỬI -->
    <form id="order-form" action="place_order.php" method="POST">
      <?php foreach ($selected as $sid): ?>
        <input type="hidden" name="selected[]" value="<?php echo (int)$sid; ?>">
      <?php endforeach; ?>
      <button type="submit" class="btn-submit">
        <i class="fas fa-check-circle"></i> Xác nhận đặt hàng
      </button>
    </form>

  </div>

</div>

<script>
  function toggleNewAddress(show) {
    document.getElementById('new-address-input').style.display = show ? 'block' : 'none';
  }

  // Ẩn bank detail khi chọn khác
  document.querySelectorAll('input[name="payment"]').forEach(el => {
    el.addEventListener('change', function() {
      document.getElementById('bank-detail').style.display = 
        this.value === 'bank' ? 'block' : 'none';
    });
  });
</script>

</body>
</html>