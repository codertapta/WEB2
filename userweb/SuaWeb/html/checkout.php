<?php require_once __DIR__ . '/../../../config.php'; ?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đặt hàng</title>

  <link rel="stylesheet" href="../css/cart.css">
</head>

<body>

<header>🧾 Thanh toán đơn hàng</header>

<?php
session_start();
$user_id = $_SESSION['user_id'];
// lấy user
$user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id=$user_id"));

// lấy giỏ hàng
$sql = "SELECT cart.*,products.name,products.price 
        FROM cart 
        JOIN products ON cart.product_id = products.id
        WHERE user_id=$user_id";

$result = mysqli_query($conn,$sql);

// ❗ kiểm tra giỏ hàng
if(mysqli_num_rows($result) == 0){
  echo "<p style='text-align:center;margin-top:20px'>Giỏ hàng trống!</p>";
  echo "<div style='text-align:center'><a href='products.php'>← Quay lại mua hàng</a></div>";
  exit;
}

$total = 0;
?>

<form action="order.php" method="POST">

<h3>📍 Địa chỉ giao hàng</h3>

<label>
<input type="radio" name="address_type" value="old" checked>
<?php echo $user['address']; ?>
</label>

<br>

<label>
<input type="radio" name="address_type" value="new">
Nhập địa chỉ mới
</label>

<br><br>

<input type="text" name="new_address" placeholder="Nhập địa chỉ mới">

<hr>

<h3>💳 Phương thức thanh toán</h3>

<label>
<input type="radio" name="payment" value="cod" checked>
Thanh toán khi nhận hàng
</label><br>

<label>
<input type="radio" name="payment" value="bank">
Chuyển khoản ngân hàng
</label><br>

<div style="margin-left:20px;color:red">
STK: 123456789 - Ngân hàng ABC<br>
Tên: NGUYEN KHANH
</div>

<label>
<input type="radio" name="payment" value="online">
Thanh toán online
</label>

<hr>

<h3>🛒 Tóm tắt đơn hàng</h3>

<table border="1" width="100%">
<tr>
<th>Sản phẩm</th>
<th>Số lượng</th>
<th>Giá</th>
<th>Thành tiền</th>
</tr>

<?php 
while($row = mysqli_fetch_assoc($result)) { 
  $thanhtien = $row['price'] * $row['quantity'];
  $total += $thanhtien;
?>

<tr>
<td><?php echo $row['name']; ?></td>
<td><?php echo $row['quantity']; ?></td>
<td><?php echo number_format($row['price']); ?>₫</td>
<td><?php echo number_format($thanhtien); ?>₫</td>
</tr>

<?php } ?>

</table>

<h3 class="total">Tổng tiền: <?php echo number_format($total); ?>₫</h3>

<br>
<button type="submit">✅ Xác nhận đặt hàng</button>

</form>

</body>
</html>