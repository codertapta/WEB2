<?php require __DIR__ . "/../../../config.php"; ?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Danh sách sản phẩm - MUIT</title>

  <link rel="stylesheet" href="../css/style.css" />

  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
</head>

<body>

<?php
  session_start();
  $user_id = $_SESSION['user_id'];
// đếm giỏ hàng
$count = mysqli_fetch_assoc(mysqli_query($conn,
"SELECT SUM(quantity) as total FROM cart WHERE user_id=$user_id"));
?>

<!-- Header -->
<header class="header">
  <div class="logo-section">
    <a href="products.php" class="logo">
      <img src="../img/logo.png" alt="Logo MUIT" />
    </a>
    <a href="products.php" class="logo-text">MUIT</a>
  </div>

  <nav>
    <ul class="nav-links">
      <li><a href="products.php">Trang chủ</a></li>
      <li><a href="#">Laptop AI</a></li>
      <li><a href="#">Laptop Gaming</a></li>
      <li><a href="#">Laptop mỏng nhẹ</a></li>
    </ul>
  </nav>

  <div class="search-and-hotline">
    <div class="search-container">
      <input type="text" placeholder="Tìm kiếm sản phẩm..." />
      <button type="submit">Tìm</button>
    </div>
    <div class="hotline">Hotline:19001234</div>
  </div>

  <div class="right-icons">
    <a href="profile.php" class="icon-link" title="Tài khoản">
      <i class="fas fa-user"></i>
    </a>

    <a href="cart.php" class="icon-link" title="Giỏ hàng">
      <i class="fas fa-shopping-cart"></i>
      <span class="cart-count">
        <?php echo $count['total'] ? $count['total'] : 0; ?>
      </span>
    </a>

    <a href="orders.php" class="icon-link" title="Đơn hàng của tôi">
      <i class="fas fa-receipt"></i>
    </a>
  </div>
</header>

<main>
  <div class="product-grid">

    <?php
    $result = mysqli_query($conn,"SELECT * FROM products");

    while($row = mysqli_fetch_assoc($result)) {
    ?>

    <!-- Product -->
    <div class="product-item">
      <img src="../img/<?php echo $row['image']; ?>" alt="">
      <h3><?php echo $row['name']; ?></h3>
      <p>Giá: <?php echo number_format($row['price']); ?> VND</p>

      <div class="product-actions">

        <!-- Mua ngay -->
        <a href="checkout.php?id=<?php echo $row['id']; ?>" class="buy-now-link">
          Mua ngay
        </a>

        <!-- Thêm giỏ -->
        <a href="add_to_cart.php?id=<?php echo $row['id']; ?>"
           class="add-to-cart" title="Thêm vào giỏ hàng">
          <i class="fas fa-cart-plus"></i>
        </a>

        <!-- Chi tiết -->
        <a href="product_detail.php?id=<?php echo $row['id']; ?>"
           class="view-detail">
          <i class="fas fa-eye"></i> Xem chi tiết
        </a>

      </div>
    </div>

    <?php } ?>

  </div>

  <!-- Pagination (giữ nguyên UI) -->
  <div class="product-pagination">
    <a class="page prev" href="#">&lt;</a>
    <a class="page active" href="#">1</a>
    <a class="page" href="#">2</a>
    <a class="page" href="#">3</a>
    <a class="page next" href="#">&gt;</a>
  </div>
</main>

<!-- Footer -->
<footer>
  <div class="f1">
    <div class="f1_content">
      <div class="logo">
        <img src="../img/logo.png" alt="ảnh logo" />
        <span><h3>MUIT</h3></span>
      </div>

      <div>
        <h2>Đăng kí nhận thông tin</h2>
        <p>Nhận thông tin mới nhất về chúng tôi</p>
      </div>

      <div class="email">
        <input type="email" placeholder="Nhập email của bạn" />
        <button type="submit">Đăng ký</button>
      </div>
    </div>
  </div>

  <div class="f2">
    <div class="f2_content1">
      <h4>VỀ CHÚNG TÔI</h4>
      <ul>
        <li>Giới thiệu về công ty</li>
        <li>Quy chế hoạt động</li>
        <li>Dự án Doanh nghiệp</li>
        <li>Tin tức khuyến mại</li>
        <li>Giới thiệu máy đổi trả</li>
      </ul>
    </div>

    <div class="f2_content2">
      <h4>CHÍNH SÁCH</h4>
      <ul>
        <li>Chính sách bảo hành</li>
        <li>Chính sách đổi trả</li>
        <li>Chính sách bảo mật</li>
        <li>Chính sách trả góp</li>
      </ul>
    </div>

    <div class="f2_content3">
      <h4>LIÊN HỆ</h4>
      <ul>
        <li>273 An Dương Vương, Q5, TP.HCM</li>
        <li>01234567890</li>
        <li>quangkhu@gmail.com</li>
      </ul>
    </div>
  </div>

  <div class="f3">
    <p>Copyright 2026 MUIT. All Rights Reserved.</p>
  </div>
</footer>

</body>
</html>