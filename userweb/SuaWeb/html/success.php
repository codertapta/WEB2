<?php require __DIR__ . "/../../../config.php"; ?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>Đặt hàng thành công</title>
  <link rel="stylesheet" href="../cart/style.css">
</head>

<body>

<header>🎉 Đặt hàng thành công</header>

<?php
$id = $_GET['id'];

$order = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM orders WHERE id=$id"));

$details = mysqli_query($conn,"
SELECT order_details.*,products.name 
FROM order_details 
JOIN products ON order_details.product_id = products.id
WHERE order_id=$id
");
?>

<p><b>Địa chỉ:</b> <?php echo $order['address']; ?></p>
<p><b>Thanh toán:</b> <?php echo $order['payment_method']; ?></p>

<table>
<tr>
<th>Sản phẩm</th>
<th>Số lượng</th>
<th>Giá</th>
</tr>

<?php while($row = mysqli_fetch_assoc($details)) { ?>
<tr>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['quantity']; ?></td>
<td><?php echo number_format($row['price']); ?>₫</td>
</tr>
<?php } ?>

</table>

<h3 class="total">Tổng: <?php echo number_format($order['total_price']); ?>₫</h3>

<div class="checkout">
  <button onclick="window.location.href='products.php'">🏠 Trang chủ</button>
  <button onclick="window.location.href='orders.php'">📜 Lịch sử</button>
</div>

</body>
</html>