<?php
$conn = mysqli_connect("localhost", "root", "", "shop");

$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// phân trang
$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

// lấy data
if ($category > 0) {
  $sql = "SELECT * FROM products WHERE category_id = $category LIMIT $limit OFFSET $offset";
} else {
  $sql = "SELECT * FROM products LIMIT $limit OFFSET $offset";
}
$result = mysqli_query($conn, $sql);

// đếm tổng
if ($category > 0) {
  $total_sql = "SELECT COUNT(*) as total FROM products WHERE category_id = $category";
} else {
  $total_sql = "SELECT COUNT(*) as total FROM products";
}
$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);

$total_products = $total_row['total'];
$total_pages = ceil($total_products / $limit);
?>

<!doctype html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>MUIT</title>

  <link rel="stylesheet" href="../css/style.css">

  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

  <!-- GIỮ NGUYÊN HEADER -->
  <header class="header">
    <div class="logo-section">
      <a href="./products.php" class="logo">
        <img src="../img/logo.png">
      </a>
      <a href="./products.php" class="logo-text">MUIT</a>
    </div>

    <nav>
      <ul class="nav-links">
        <li><a href="products.php">Trang chủ</a></li>
        <li><a href="?category=1">Laptop AI</a></li>
        <li><a href="?category=2">Laptop Gaming</a></li>
        <li><a href="?category=3">Laptop mỏng nhẹ</a></li>
      </ul>
    </nav>

    <div class="search-and-hotline">
      <form action="timkiemnc.php" method="get" class="search-container">
        <input type="text" name="ten" placeholder="Tìm kiếm sản phẩm...">
        <button type="submit">Tìm</button>
      </form>
      <div class="hotline">Hotline:19001234</div>
    </div>

    <div class="right-icons">
      <a href="./profile.php" class="icon-link" title="Tài khoản">
        <i class="fas fa-user"></i>
      </a>

      <a href="giohang.html" class="icon-link" title="Giỏ hàng">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count">3</span>
      </a>

      <a href="donhangdadat.html" class="icon-link" title="Đơn hàng của tôi">
        <i class="fas fa-receipt"></i>
      </a>
    </div>
  </header>

  <!-- MAIN -->
  <main>
    <div class="product-grid">

      <!-- PHP đổ sản phẩm vào đây -->
      <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <div class="product-item">
          <img src="<?= $row['image'] ?>" alt="">
          <h3><?= $row['name'] ?></h3>
          <p>Giá: <?= number_format($row['price']) ?> VND</p>

          <div class="product-actions">
            <a href="#" class="buy-now-link">Mua ngay</a>

            <a href="#" class="add-to-cart">
              <i class="fas fa-cart-plus"></i>
            </a>

            <a href="product_detail.php?id=<?= $row['id'] ?>" class="view-detail">
              <i class="fas fa-eye"></i> Xem chi tiết
            </a>
          </div>
        </div>
      <?php } ?>

    </div>

    <!-- PAGINATION -->
    <div class="product-pagination">

      <!-- nút prev -->
      <?php if ($page > 1) { ?>
        <a class="page prev" href="?page=<?= $page - 1 ?>&category=<?= $category ?>">&lt;</a>
      <?php } ?>

      <!-- số trang -->
      <?php
      $start = max(1, $page - 2);
      $end = min($total_pages, $page + 2);

      for ($i = $start; $i <= $end; $i++) {
        $active = ($i == $page) ? "active" : "";
      ?>
        <a class="page <?= $active ?>" href="?page=<?= $i ?>&category=<?= $category ?>"><?= $i ?></a>
      <?php } ?>

      <!-- nút next -->
      <?php if ($page < $total_pages) { ?>
        <a class="page next" href="?page=<?= $page + 1 ?>&category=<?= $category ?>">&gt;</a>
      <?php } ?>

    </div>
  </main>

</body>

</html>