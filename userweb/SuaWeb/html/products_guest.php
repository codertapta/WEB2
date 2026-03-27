<?php 
require __DIR__ . "/../../../config.php";

$limit    = 8;
$page     = isset($_GET['page'])     ? (int)$_GET['page']     : 1;
if ($page < 1) $page = 1;
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$offset   = ($page - 1) * $limit;

if ($category > 0) {
    $sql = "SELECT * FROM shop.products WHERE category_id = $category LIMIT $limit OFFSET $offset";
} else {
    $sql = "SELECT * FROM shop.products LIMIT $limit OFFSET $offset";
}
$result = mysqli_query($conn, $sql);

$total_sql    = $category > 0
    ? "SELECT COUNT(*) as total FROM shop.products WHERE category_id = $category"
    : "SELECT COUNT(*) as total FROM shop.products";
$total_result = mysqli_query($conn, $total_sql);
$total_row    = mysqli_fetch_assoc($total_result);
$total_pages  = ceil($total_row['total'] / $limit);

$categories = [1 => 'Laptop AI', 2 => 'Laptop Gaming', 3 => 'Laptop mỏng nhẹ'];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>MUIT</title>
    <link rel="stylesheet" href="../css/products_guest.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- MODAL -->
<div class="modal-overlay" id="loginModal">
    <div class="modal-box">
        <button class="btn-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        <i class="fas fa-lock lock-icon"></i>
        <h3>Yêu cầu đăng nhập</h3>
        <p>Bạn cần đăng nhập hoặc đăng ký để sử dụng chức năng này.</p>
        <div class="modal-buttons">
            <a href="login.php"    class="btn-login">Đăng nhập</a>
            <a href="register.php" class="btn-register">Đăng ký</a>
        </div>
    </div>
</div>

<!-- HEADER -->
<header class="header">
    <div class="logo-section">
        <a href="products_guest.php" class="logo"><img src="../img/logo.png"></a>
        <a href="products_guest.php" class="logo-text">MUIT</a>
    </div>

    <nav>
        <ul class="nav-links">
            <li><a href="products_guest.php">Trang chủ</a></li>
            <li><a href="?category=1">Laptop AI</a></li>
            <li><a href="?category=2">Laptop Gaming</a></li>
            <li><a href="?category=3">Laptop mỏng nhẹ</a></li>
        </ul>
    </nav>

    <div class="search-and-hotline">
        <form action="timkiemnc_guest.php" method="get" class="search-container">
            <input type="text" name="ten" placeholder="Tìm kiếm sản phẩm...">
            <button type="submit">Tìm</button>
        </form>
        <div class="hotline">Hotline: 19001234</div>
    </div>

    <div class="right-icons">
        <a href="#" class="icon-link guest-lock" onclick="showModal(event)" title="Tài khoản">
            <i class="fas fa-user"></i>
        </a>
        <a href="#" class="icon-link guest-lock" onclick="showModal(event)" title="Giỏ hàng">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count">0</span>
        </a>
        <a href="#" class="icon-link guest-lock" onclick="showModal(event)" title="Đơn hàng">
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
                    <a href="#" class="buy-now-link" onclick="showModal(event)">Mua ngay</a>
                    <a href="#" class="add-to-cart"  onclick="showModal(event)">
                        <i class="fas fa-cart-plus"></i>
                    </a>
                    <a href="detail_guest.php?id=<?php echo $row['id']; ?>" class="view-detail">
                        <i class="fas fa-eye"></i> Xem chi tiết
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- PAGINATION -->
    <div class="product-pagination">
        <?php if ($page > 1) { ?>
            <a class="page prev" href="?page=<?= $page-1 ?>&category=<?= $category ?>">&lt;</a>
        <?php } ?>
        <?php
        $start = max(1, $page - 2);
        $end   = min($total_pages, $page + 2);
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i == $page) ? "active" : "";
        ?>
            <a class="page <?= $active ?>" href="?page=<?= $i ?>&category=<?= $category ?>"><?= $i ?></a>
        <?php } ?>
        <?php if ($page < $total_pages) { ?>
            <a class="page next" href="?page=<?= $page+1 ?>&category=<?= $category ?>">&gt;</a>
        <?php } ?>
    </div>
</main>

<footer>
    <div class="f3">
        <p>Copyright 2026 MUIT. All Rights Reserved.</p>
    </div>
</footer>

<script>
    function showModal(e) {
        e.preventDefault();
        document.getElementById('loginModal').classList.add('active');
    }
    function closeModal() {
        document.getElementById('loginModal').classList.remove('active');
    }
    document.getElementById('loginModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>

</body>
</html>