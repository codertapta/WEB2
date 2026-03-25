<?php
require_once __DIR__ . '/../../../config.php';
session_start();

$user_id  = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Lấy thông tin đơn hàng
$order = mysqli_fetch_assoc(mysqli_query($conn,
  "SELECT orders.*, CONCAT(users.ho, ' ', users.ten) as hoten, users.sdt
   FROM orders 
   JOIN users ON orders.user_id = users.id
   WHERE orders.id = $order_id AND orders.user_id = $user_id"
));

if (!$order) {
  echo "<p style='text-align:center;margin-top:40px'>Không tìm thấy đơn hàng! <a href='products.php'>← Về trang chủ</a></p>";
  exit;
}

// Lấy chi tiết sản phẩm
$details = mysqli_query($conn,
  "SELECT order_details.*, shop.products.name, shop.products.image
   FROM order_details
   JOIN shop.products ON order_details.product_id = shop.products.id
   WHERE order_details.order_id = $order_id"
);

$pay_text = [
  'cod'    => 'Thanh toán khi nhận hàng (COD)',
  'bank'   => 'Chuyển khoản ngân hàng',
  'online' => 'Thanh toán online',
];
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đặt hàng thành công</title>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: 'Be Vietnam Pro', sans-serif; background: #f5f5f7; color: #1a1a1a; }
    header { background: #d70018; color: white; padding: 18px 32px; display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 12px rgba(215,0,24,0.3); }
    header .check-icon { width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    header h1 { font-size: 1.2rem; font-weight: 700; }
    .container { max-width: 860px; margin: 32px auto; padding: 0 20px; }
    .success-banner { background: white; border-radius: 16px; padding: 28px; display: flex; align-items: center; gap: 20px; margin-bottom: 24px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); border-left: 5px solid #d70018; }
    .success-banner .icon { width: 56px; height: 56px; background: #fff5f5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #d70018; flex-shrink: 0; }
    .success-banner h2 { font-size: 1.1rem; font-weight: 700; color: #d70018; margin-bottom: 4px; }
    .success-banner p { font-size: 0.88rem; color: #666; }
    .ma-don { display: inline-block; background: #fff5f5; border: 1px dashed #d70018; color: #d70018; font-weight: 700; font-size: 1rem; padding: 4px 12px; border-radius: 6px; margin-top: 6px; letter-spacing: 1px; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    .card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 16px rgba(0,0,0,0.07); }
    .card-title { font-size: 0.85rem; font-weight: 700; color: #d70018; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; padding-bottom: 10px; border-bottom: 2px solid #f5f5f7; display: flex; align-items: center; gap: 8px; }
    .info-row { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 12px; font-size: 0.88rem; }
    .info-row .label { color: #888; min-width: 90px; flex-shrink: 0; }
    .info-row .value { font-weight: 600; color: #1a1a1a; }
    .products-card { margin-bottom: 20px; }
    .product-row { display: flex; align-items: center; gap: 14px; padding: 14px 0; border-bottom: 1px solid #f5f5f5; }
    .product-row:last-child { border-bottom: none; }
    .product-row img { width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
    .product-row .pname { font-weight: 600; font-size: 0.9rem; }
    .product-row .pqty { font-size: 0.8rem; color: #888; margin-top: 3px; }
    .product-row .pthanhtin { margin-left: auto; font-weight: 700; color: #d70018; white-space: nowrap; }
    .total-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 0; font-size: 0.88rem; color: #666; border-bottom: 1px solid #f5f5f5; }
    .total-row.final { border-bottom: none; font-size: 1.15rem; font-weight: 700; color: #d70018; padding-top: 16px; }
    .buttons { display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px; }
    .btn { padding: 13px 24px; border: none; border-radius: 10px; font-size: 0.9rem; font-weight: 600; font-family: inherit; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background 0.2s, transform 0.1s; text-decoration: none; }
    .btn:active { transform: scale(0.97); }
    .btn-gray { background: #eee; color: #444; }
    .btn-gray:hover { background: #ddd; }
    .btn-red { background: #d70018; color: white; }
    .btn-red:hover { background: #b8001a; }
    @media (max-width: 640px) { .grid { grid-template-columns: 1fr; } .buttons { flex-direction: column; } .btn { justify-content: center; } }
  </style>
</head>
<body>

<header>
  <div class="check-icon"><i class="fas fa-check"></i></div>
  <h1>Đặt hàng thành công!</h1>
</header>

<div class="container">

  <div class="success-banner">
    <div class="icon"><i class="fas fa-box-open"></i></div>
    <div>
      <h2>Cảm ơn bạn đã đặt hàng!</h2>
      <p>Đơn hàng của bạn đang được xử lý. Chúng tôi sẽ liên hệ sớm nhất.</p>
      <div class="ma-don"><?php echo htmlspecialchars($order['ma_don']); ?></div>
    </div>
  </div>

  <div class="grid">

    <div class="card">
      <div class="card-title"><i class="fas fa-user"></i> Thông tin người nhận</div>
      <div class="info-row">
        <span class="label">Họ tên</span>
        <span class="value"><?php echo htmlspecialchars($order['hoten']); ?></span>
      </div>
      <div class="info-row">
        <span class="label">Số điện thoại</span>
        <span class="value"><?php echo htmlspecialchars($order['sdt']); ?></span>
      </div>
      <div class="info-row">
        <span class="label">Địa chỉ</span>
        <span class="value"><?php echo htmlspecialchars($order['address']); ?></span>
      </div>
    </div>

    <div class="card">
      <div class="card-title"><i class="fas fa-receipt"></i> Chi tiết đơn hàng</div>
      <div class="info-row">
        <span class="label">Mã đơn</span>
        <span class="value" style="color:#d70018"><?php echo htmlspecialchars($order['ma_don']); ?></span>
      </div>
      <div class="info-row">
        <span class="label">Ngày đặt</span>
        <span class="value"><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></span>
      </div>
      <div class="info-row">
        <span class="label">Thanh toán</span>
        <span class="value"><?php echo $pay_text[$order['payment_method']] ?? $order['payment_method']; ?></span>
      </div>
      <div class="info-row">
        <span class="label">Trạng thái</span>
        <span class="value" style="color:#2e7d32"><i class="fas fa-circle" style="font-size:0.6rem;margin-right:4px"></i>Đang xử lý</span>
      </div>
    </div>

  </div>

  <div class="card products-card">
    <div class="card-title"><i class="fas fa-shopping-bag"></i> Sản phẩm đã đặt</div>

    <?php
    $total = 0;
    $detail_rows = [];
    while ($d = mysqli_fetch_assoc($details)) {
      $d['thanhtien'] = $d['price'] * $d['quantity'];
      $total += $d['thanhtien'];
      $detail_rows[] = $d;
    }
    foreach ($detail_rows as $d): ?>
    <div class="product-row">
      <img src="../img/<?php echo htmlspecialchars($d['image']); ?>">
      <div>
        <div class="pname"><?php echo htmlspecialchars($d['name']); ?></div>
        <div class="pqty"><?php echo number_format($d['price']); ?>₫ × <?php echo $d['quantity']; ?></div>
      </div>
      <div class="pthanhtin"><?php echo number_format($d['thanhtien']); ?>₫</div>
    </div>
    <?php endforeach; ?>

    <div class="total-row">
      <span>Tạm tính</span>
      <span><?php echo number_format($total); ?>₫</span>
    </div>
    <div class="total-row">
      <span>Phí vận chuyển</span>
      <span style="color:#2e7d32">Miễn phí</span>
    </div>
    <div class="total-row final">
      <span>Tổng thanh toán</span>
      <span><?php echo number_format($order['total_price']); ?>₫</span>
    </div>
  </div>

  <div class="buttons">
    <a href="products.php" class="btn btn-gray">
      <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
    </a>
    <a href="orders.php" class="btn btn-red">
      <i class="fas fa-receipt"></i> Xem đơn hàng
    </a>
  </div>

</div>
</body>
</html>