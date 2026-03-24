<?php require_once __DIR__ . '/../../../config.php'; ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Giỏ Hàng</title>
  <link rel="stylesheet" href="../css/cart.css" />
</head>

<body>

<header>GIỎ HÀNG CỦA BẠN</header>

<div style="margin: 20px;">
  <button class="back-btn" onclick="window.location.href='products.php'">
    ← Quay về trang chủ
  </button>
</div>

<table>
  <thead>
    <tr>
      <th>Sản Phẩm</th>
      <th>Đơn Giá</th>
      <th>Số Lượng</th>
      <th>Số Tiền</th>
      <th>Thao Tác</th>
    </tr>
  </thead>

  <tbody>
    <?php
    session_start();
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT cart.id, products.name, products.price, products.image, cart.quantity 
            FROM cart 
            JOIN products ON cart.product_id = products.id 
            WHERE cart.user_id = $user_id";

    $result = mysqli_query($conn, $sql);
    $total = 0;

    while($row = mysqli_fetch_assoc($result)) {
      $thanhtien = $row['price'] * $row['quantity'];
      $total += $thanhtien;
    ?>
    <tr>

      <td class="product-info">
        <img src="../img/<?php echo $row['image']; ?>" />
        <div><?php echo $row['name']; ?></div>
      </td>

      <td><?php echo number_format($row['price']); ?>₫</td>

      <td>
        <div class="quantity">
          <a href="update_cart.php?id=<?php echo $row['id']; ?>&type=decrease">-</a>
          <input type="number" value="<?php echo $row['quantity']; ?>" readonly>
          <a href="update_cart.php?id=<?php echo $row['id']; ?>&type=increase">+</a>
        </div>
      </td>

      <td><?php echo number_format($thanhtien); ?>₫</td>

      <td>
        <a href="delete_cart.php?id=<?php echo $row['id']; ?>">
          <button class="delete-btn">Xóa</button>
        </a>
      </td>

    </tr>
    <?php } ?>
  </tbody>
</table>

<div class="total">
  Tổng cộng: <span><?php echo number_format($total); ?>₫</span>
</div>

<div class="checkout">
  <button onclick="window.location.href='checkout.php'">
    Tiến hành đặt hàng
  </button>
</div>

</body>
</html>