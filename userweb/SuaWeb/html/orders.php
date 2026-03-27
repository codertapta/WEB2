<?php require __DIR__ . "/../../../config.php"; ?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lịch sử đơn hàng</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/orders.css">
</head>
<body>

<header class="site-header">
  <a href="products.php" class="brand">MUIT</a>
  <h1><i class="fas fa-receipt" style="color:var(--red);margin-right:8px"></i>Lịch sử đơn hàng</h1>
  <a href="products.php" class="back-btn">
    <i class="fas fa-arrow-left"></i> Trang chủ
  </a>
</header>

<div class="page-wrap">

  <div class="page-title">Đơn hàng của bạn</div>
  <div class="page-subtitle">Theo dõi toàn bộ lịch sử mua hàng tại MUIT</div>

  <?php
  session_start();
  $user_id = $_SESSION['user_id'];

  $orders = mysqli_query($conn, "
    SELECT * FROM orders 
    WHERE user_id = $user_id 
    ORDER BY order_date DESC
  ");

  if (!$orders) die("Lỗi: " . mysqli_error($conn));

  // Map status → hiển thị
  $status_map = [
    'pending'   => ['label' => 'Chờ xác nhận', 'class' => 'status-pending',   'icon' => 'fa-clock'],
    'confirmed' => ['label' => 'Đã xác nhận',  'class' => 'status-confirmed', 'icon' => 'fa-check-circle'],
    'delivered' => ['label' => 'Đã nhận hàng', 'class' => 'status-delivered', 'icon' => 'fa-box-open'],
    'cancelled' => ['label' => 'Đã hủy',       'class' => 'status-cancelled', 'icon' => 'fa-times-circle'],
  ];
  ?>

  <?php if (mysqli_num_rows($orders) == 0): ?>
    <div class="empty-state">
      <i class="fas fa-box-open"></i>
      <p>Bạn chưa có đơn hàng nào.</p>
    </div>

  <?php else: ?>
    <?php $delay = 0; while ($o = mysqli_fetch_assoc($orders)): $delay += 60; ?>

    <?php
      $pay = $o['payment_method'] ?? 'cod';
      $pay_label = match($pay) {
        'bank'   => ['class' => 'pay-bank',   'icon' => 'fa-university', 'text' => 'Chuyển khoản'],
        'online' => ['class' => 'pay-online',  'icon' => 'fa-mobile-alt', 'text' => 'Thanh toán online'],
        default  => ['class' => 'pay-cod',     'icon' => 'fa-truck',      'text' => 'COD'],
      };

      $status     = $o['status'] ?? 'pending';
      $status_info = $status_map[$status] ?? $status_map['pending'];
    ?>

    <div class="order-card" style="animation-delay:<?php echo $delay; ?>ms">

      <!-- Đầu card -->
      <div class="order-head">
        <div class="order-head-left">
          <div class="order-icon"><i class="fas fa-box"></i></div>
          <div>
            <div class="order-id"># <?php echo htmlspecialchars($o['ma_don']); ?></div>
            <div class="order-date">
              <i class="fas fa-calendar-alt" style="margin-right:4px"></i>
              <?php echo date('d/m/Y — H:i', strtotime($o['order_date'])); ?>
            </div>
          </div>
        </div>

        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;justify-content:flex-end">
          <!-- BADGE TRẠNG THÁI -->
          <span class="status-badge <?php echo $status_info['class']; ?>">
            <i class="fas <?php echo $status_info['icon']; ?>"></i>
            <?php echo $status_info['label']; ?>
          </span>

          <div class="order-total-badge">
            <?php echo number_format($o['total_price']); ?>₫
          </div>
        </div>
      </div>

      <!-- Meta -->
      <div class="order-meta">
        <div class="meta-item">
          <i class="fas fa-map-marker-alt icon-addr"></i>
          <div>
            <div style="font-size:11px;margin-bottom:2px">Địa chỉ giao</div>
            <div class="meta-val" style="font-size:13px"><?php echo htmlspecialchars($o['address']); ?></div>
          </div>
        </div>
        <div class="meta-item">
          <i class="fas fa-credit-card icon-pay"></i>
          <div>
            <div style="font-size:11px;margin-bottom:4px">Thanh toán</div>
            <span class="pay-badge <?php echo $pay_label['class']; ?>">
              <i class="fas <?php echo $pay_label['icon']; ?>"></i>
              <?php echo $pay_label['text']; ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Sản phẩm -->
      <div class="order-products">
        <div class="products-label">Chi tiết sản phẩm</div>

        <?php
        $details = mysqli_query($conn, "
          SELECT order_details.*, shop.products.name 
          FROM order_details 
          JOIN shop.products ON order_details.product_id = shop.products.id
          WHERE order_id = " . (int)$o['id']);
        ?>

        <?php if (!$details || mysqli_num_rows($details) == 0): ?>
          <p style="color:#aaa;font-size:13px">Không có sản phẩm</p>
        <?php endif; ?>

        <?php while ($d = mysqli_fetch_assoc($details)): ?>
        <div class="product-row">
          <div class="product-row-left">
            <div class="product-dot"></div>
            <div class="product-name"><?php echo htmlspecialchars($d['name']); ?></div>
          </div>
          <span class="product-qty">x<?php echo $d['quantity']; ?></span>
          <div class="product-price"><?php echo number_format($d['price'] * $d['quantity']); ?>₫</div>
        </div>
        <?php endwhile; ?>

        <!-- NÚT ĐÃ NHẬN HÀNG: chỉ hiện khi status = confirmed -->
        <?php if ($status === 'confirmed'): ?>
        <form action="update_order_status.php" method="POST" class="confirm-form">
          <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
          <button type="submit" class="btn-received"
                  onclick="return confirm('Bạn xác nhận đã nhận được hàng?')">
            <i class="fas fa-check-double"></i> Đã nhận được hàng
          </button>
        </form>
        <?php endif; ?>

      </div>
    </div>

    <?php endwhile; ?>
  <?php endif; ?>

</div>

</body>
</html>