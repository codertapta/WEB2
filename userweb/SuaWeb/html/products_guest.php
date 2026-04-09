<?php 
require __DIR__ . "/../../../config.php";

// Lấy danh sách loại sản phẩm đang hoạt động từ database
$categories = [];
$catQuery = mysqli_query($conn, "SELECT id, name FROM categories WHERE status = 1 ORDER BY id");
while ($cat = mysqli_fetch_assoc($catQuery)) {
    $categories[$cat['id']] = $cat['name'];
}

$limit    = 8;
$page     = isset($_GET['page'])     ? (int)$_GET['page']     : 1;
if ($page < 1) $page = 1;
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$offset   = ($page - 1) * $limit;

if ($category > 0) {
    $sql = "SELECT * FROM shop.products WHERE category_id = $category AND status = 1 LIMIT $limit OFFSET $offset";
} else {
    $sql = "SELECT * FROM shop.products WHERE status = 1 LIMIT $limit OFFSET $offset";
}
$result = mysqli_query($conn, $sql);

$total_sql    = $category > 0
    ? "SELECT COUNT(*) as total FROM shop.products WHERE category_id = $category AND status = 1"
    : "SELECT COUNT(*) as total FROM shop.products WHERE status = 1";
$total_result = mysqli_query($conn, $total_sql);
$total_row    = mysqli_fetch_assoc($total_result);
$total_pages  = ceil($total_row['total'] / $limit);
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>MUIT</title>
    <link rel="stylesheet" href="../css/products_guest.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            display: inline-block;
        }
    </style>
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
            <?php foreach ($categories as $id => $name): ?>
                <li><a href="?category=<?= $id ?>"><?= htmlspecialchars($name) ?></a></li>
            <?php endforeach; ?>
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
        <?php while ($row = mysqli_fetch_assoc($result)) { 
            $stock = (int)$row['quantity'];
            $inStock = ($stock > 0);
            $stockText = $inStock ? "Còn $stock sản phẩm" : "Hết hàng";
            $stockClass = $inStock ? "in-stock" : "out-of-stock";
        ?>
            <div class="product-item">
                <img src="<?= htmlspecialchars($row['image']); ?>">
                <h3><?= htmlspecialchars($row['name']); ?></h3>
                <p>Giá: <?= number_format($row['price']); ?> VND</p>
                <p>Loại: <?= htmlspecialchars($categories[$row['category_id']] ?? 'Khác'); ?></p>
                <p class="stock-info <?= $stockClass ?>">📦 Tồn kho: <?= $stockText ?></p>
                <div class="product-actions">
                    <?php if ($inStock): ?>
                        <a href="#" class="buy-now-link" onclick="showModal(event)">Mua ngay</a>
                        <a href="#" class="add-to-cart" onclick="showModal(event)">
                            <i class="fas fa-cart-plus"></i>
                        </a>
                    <?php else: ?>
                        <span class="out-of-stock-label">Tạm hết hàng</span>
                    <?php endif; ?>
                    <a href="detail_guest.php?id=<?= $row['id']; ?>" class="view-detail">
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