<?php 
require __DIR__ . "/../../../config.php";
session_start();

$user_id = $_SESSION['user_id'] ?? 0;

// ===== LẤY DANH MỤC LOẠI TỪ DATABASE (CHỦ ĐỘNG) =====
$categories = [];
$catQuery = mysqli_query($conn, "SELECT id, name FROM categories WHERE status = 1 ORDER BY id");
while ($cat = mysqli_fetch_assoc($catQuery)) {
    $categories[$cat['id']] = $cat['name'];
}

// ===== PHÂN TRANG =====
$limit = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$offset = ($page - 1) * $limit;

// ===== QUERY SẢN PHẨM =====
if ($category > 0) {
  $sql = "SELECT * FROM products WHERE category_id = $category AND status = 1 LIMIT $limit OFFSET $offset";
} else {
  $sql = "SELECT * FROM products WHERE status = 1 LIMIT $limit OFFSET $offset";
}
$result = mysqli_query($conn, $sql);

// ===== ĐẾM TỔNG =====
$total_sql = $category > 0 
  ? "SELECT COUNT(*) as total FROM products WHERE category_id = $category AND status = 1"
  : "SELECT COUNT(*) as total FROM products WHERE status = 1";

$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $limit);

// ===== ĐẾM GIỎ HÀNG =====
$count = ["total" => 0];
if ($user_id > 0) {
  $count = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) as total FROM cart WHERE user_id=$user_id"));
}
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
      <?php foreach ($categories as $id => $name): ?>
        <li><a href="?category=<?= $id ?>"><?= htmlspecialchars($name) ?></a></li>
      <?php endforeach; ?>
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
      <span class="cart-count"><?= $count['total'] ?: 0 ?></span>
    </a>
    <a href="orders.php" class="icon-link">
      <i class="fas fa-receipt"></i>
    </a>
  </div>
</header>

<main>
  <div class="product-grid">
    <?php while ($row = mysqli_fetch_assoc($result)) { 
      $stock = $row['quantity'];
      $stock_text = ($stock > 0) ? "Còn $stock sản phẩm" : "Hết hàng";
      $stock_class = ($stock > 0) ? "in-stock" : "out-of-stock";
    ?>
      <div class="product-item">
        <img src="<?= htmlspecialchars($row['image']); ?>">
        <h3><?= htmlspecialchars($row['name']); ?></h3>
        <p>Giá: <?= number_format($row['price']); ?> VND</p>
        <p>Loại: <?= htmlspecialchars($categories[$row['category_id']] ?? 'Khác'); ?></p>
        <p class="stock-info <?= $stock_class ?>">📦 Tồn kho: <?= $stock_text ?></p>
        <div class="product-actions">
          <?php if ($stock > 0): ?>
            <a href="checkout.php?id=<?= $row['id']; ?>" class="buy-now-link">Mua ngay</a>
            <a href="add_to_cart.php?id=<?= $row['id']; ?>&qty=1" class="add-to-cart"><i class="fas fa-cart-plus"></i></a>
          <?php else: ?>
            <span class="out-of-stock-label">Tạm hết hàng</span>
          <?php endif; ?>
          <a href="product_detail.php?id=<?= $row['id']; ?>" class="view-detail"><i class="fas fa-eye"></i> Xem chi tiết</a>
        </div>
      </div>
    <?php } ?>
  </div>

  <div class="product-pagination">
    <?php if ($page > 1): ?>
      <a class="page prev" href="?page=<?= $page-1 ?>&category=<?= $category ?>">&lt;</a>
    <?php endif; ?>
    <?php
    $start = max(1, $page - 2);
    $end = min($total_pages, $page + 2);
    for ($i = $start; $i <= $end; $i++):
      $active = ($i == $page) ? "active" : "";
    ?>
      <a class="page <?= $active ?>" href="?page=<?= $i ?>&category=<?= $category ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($page < $total_pages): ?>
      <a class="page next" href="?page=<?= $page+1 ?>&category=<?= $category ?>">&gt;</a>
    <?php endif; ?>
  </div>
</main>

<footer>
  <div class="f3">
    <p>Copyright 2026 MUIT. All Rights Reserved.</p>
  </div>
</footer>

<style>
  .stock-info {
    font-size: 13px;
    margin: 5px 0;
    font-weight: bold;
  }
  .in-stock {
    color: #2e7d32;
  }
  .out-of-stock {
    color: #c62828;
  }
  .out-of-stock-label {
    background: #ccc;
    color: #333;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: not-allowed;
  }
</style>

</body>
</html>