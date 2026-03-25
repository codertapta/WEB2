<?php require __DIR__ . "/../../../config.php"; ?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Lịch sử đơn hàng</title>
  <link rel="stylesheet" href="../css/style.css">
</head>

<body>

<header style="padding:15px; background:#d70018; color:white; text-align:center;">
  📜 Lịch sử mua hàng

</header>

<div style="margin: 20px;">
  <button class="back-btn" onclick="window.location.href='products.php'">
    ← Quay về trang chủ
  </button>
</div>


<?php
session_start();
$user_id = $_SESSION['user_id'];

// lấy orders
$orders = mysqli_query($conn,"
SELECT * FROM orders 
WHERE user_id=$user_id 
ORDER BY order_date DESC
");

// debug
if(!$orders){
    die("Lỗi orders: " . mysqli_error($conn));
}
?>

<div style="width:80%; margin:auto;">

<?php if(mysqli_num_rows($orders) == 0) { ?>
  <p style="text-align:center;">Chưa có đơn hàng</p>
<?php } ?>

<?php while($o = mysqli_fetch_assoc($orders)) { ?>

<div style="margin:20px 0; padding:20px; border:1px solid #ddd; border-radius:10px;">

  <p><b>📅 Ngày:</b> <?php echo $o['order_date']; ?></p>

  <p><b>📍 Địa chỉ:</b> <?php echo $o['address']; ?></p>

  <p><b>💳 Thanh toán:</b>
    <?php 
      $pay = $o['payment_method'] ?? 'cod';

      if($pay == 'cod') echo "Thanh toán khi nhận hàng";
      elseif($pay == 'bank') echo "Chuyển khoản";
      else echo "Thanh toán online";
    ?>
  </p>

  <p><b>💰 Tổng:</b> <?php echo number_format($o['total_price']); ?>₫</p>

  <hr>

  <b>🛒 Sản phẩm:</b>

  <?php
  $details = mysqli_query($conn,"
  SELECT order_details.*, shop.products.name 
  FROM order_details 
  JOIN shop.products ON order_details.product_id = shop.products.id
  WHERE order_id=".$o['id']);

  if(!$details){
      die("Lỗi details: " . mysqli_error($conn));
  }
  ?>

  <?php if(mysqli_num_rows($details) == 0){ ?>
    <p style="color:red">Không có sản phẩm</p>
  <?php } ?>

  <ul>
  <?php while($d = mysqli_fetch_assoc($details)) { ?>
    <li>
      <?php echo $d['name']; ?> 
      x <?php echo $d['quantity']; ?> 
      - <?php echo number_format($d['price']); ?>₫
    </li>
  <?php } ?>
  </ul>

</div>

<?php } ?>

</div>

</body>
</html>