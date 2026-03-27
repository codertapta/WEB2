<?php 
require __DIR__ . "/../../../config.php";
session_start();

$user_id = $_SESSION['user_id'] ?? 0;

// ===== PHÂN TRANG =====
$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$offset = ($page - 1) * $limit;

// ===== QUERY SẢN PHẨM =====
if ($category > 0) {
  $sql = "SELECT * FROM shop.products WHERE category_id = $category LIMIT $limit OFFSET $offset";
} else {
  $sql = "SELECT * FROM shop.products LIMIT $limit OFFSET $offset";
}
$result = mysqli_query($conn, $sql);

// ===== ĐẾM TỔNG =====
$total_sql = $category > 0 
  ? "SELECT COUNT(*) as total FROM shop.products WHERE category_id = $category"
  : "SELECT COUNT(*) as total FROM shop.products";

$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $limit);

// ===== ĐẾM GIỎ HÀNG =====
$count = ["total" => 0];
if ($user_id > 0) {
  $count = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM cart WHERE user_id=$user_id"));
}

// ===== DANH MỤC =====
$categories = [1 => 'Laptop AI', 2 => 'Laptop Gaming', 3 => 'Laptop mỏng nhẹ'];
?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title>MUIT</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<!-- HEADER -->
<header class="header">
  <div class="logo-section">
    <a href="products.php" class="logo">
      <img src="../img/logo.png">
    </a>
    <a href="products.php" class="logo-text">MUIT</a>
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
    <a href="profile.php" class="icon-link">
      <i class="fas fa-user"></i>
    </a>
    <a href="cart.php" class="icon-link">
      <i class="fas fa-shopping-cart"></i>
      <span class="cart-count">
        <?php echo $count['total'] ? $count['total'] : 0; ?>
      </span>
    </a>
    <a href="orders.php" class="icon-link">
      <i class="fas fa-receipt"></i>
    </a>
  </div>
</header>

<!-- MAIN -->
<main>
  <div class="product-grid">

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
      <div class="product-item">
        <img src="../img/<?php echo $row['image']; ?>">
        <h3><?php echo $row['name']; ?></h3>
        <p>Giá: <?php echo number_format($row['price']); ?> VND</p>
        <p>Loại: <?php echo $categories[$row['category_id']] ?? 'Khác'; ?></p>

        <div class="product-actions">
          <a href="checkout.php?id=<?php echo $row['id']; ?>" class="buy-now-link">
            Mua ngay
          </a>
          <a href="add_to_cart.php?id=<?php echo $row['id']; ?>&qty=1" class="add-to-cart">
            <i class="fas fa-cart-plus"></i>
          </a>
          <a href="product_detail.php?id=<?php echo $row['id']; ?>" class="view-detail">
            <i class="fas fa-eye"></i> Xem chi tiết
          </a>
        </div>
      </div>
    <?php } ?>

  </div>

  <!-- PAGINATION -->
  <div class="product-pagination">

    <?php if ($page > 1) { ?>
      <a class="page prev" href="?page=<?= $page - 1 ?>&category=<?= $category ?>">&lt;</a>
    <?php } ?>

    <?php
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);
    for ($i = $start; $i <= $end; $i++) {
      $active = ($i == $page) ? "active" : "";
    ?>
      <a class="page <?= $active ?>" href="?page=<?= $i ?>&category=<?= $category ?>"><?= $i ?></a>
    <?php } ?>

    <?php if ($page < $total_pages) { ?>
      <a class="page next" href="?page=<?= $page + 1 ?>&category=<?= $category ?>">&gt;</a>
    <?php } ?>

  </div>
</main>

<!-- FOOTER -->
<footer>
  <div class="f3">
    <p>Copyright 2026 MUIT. All Rights Reserved.</p>
  </div>
</footer>

</body>
</html>