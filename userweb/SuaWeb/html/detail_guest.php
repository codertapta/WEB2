<?php
session_start();
require __DIR__ . "/../../../config.php";

mysqli_select_db($conn, "shop");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$result = mysqli_query($conn, "SELECT * FROM products WHERE id = $id");
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "Không tìm thấy sản phẩm";
    exit;
}

$categories = [
    1 => 'Laptop AI',
    2 => 'Laptop Gaming',
    3 => 'Laptop mỏng nhẹ'
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title><?= $product['name'] ?></title>

    <link rel="stylesheet" href="../css/products_guest.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<!-- ===== MODAL ===== -->
<div class="modal-overlay" id="loginModal">
    <div class="modal-box">
        <button class="btn-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        <i class="fas fa-lock lock-icon"></i>
        <h3>Yêu cầu đăng nhập</h3>
        <p>Bạn cần đăng nhập hoặc đăng ký để sử dụng chức năng này.</p>
        <div class="modal-buttons">
            <a href="login.php" class="btn-login">Đăng nhập</a>
            <a href="register.php" class="btn-register">Đăng ký</a>
        </div>
    </div>
</div>

<!-- ===== HEADER ===== -->
<header class="header">
    <div class="logo-section">
        <a href="products_guest.php" class="logo">
            <img src="../img/logo.png">
        </a>
        <a href="products_guest.php" class="logo-text">MUIT</a>
    </div>

    <nav>
        <ul class="nav-links">
            <li><a href="products_guest.php">Trang chủ</a></li>
            <li><a href="products_guest.php?category=1">Laptop AI</a></li>
            <li><a href="products_guest.php?category=2">Laptop Gaming</a></li>
            <li><a href="products_guest.php?category=3">Laptop mỏng nhẹ</a></li>
        </ul>
    </nav>

    <div class="search-and-hotline">
        <form action="timkiemnc_guest.php" method="get" class="search-container">
            <input type="text" name="ten" placeholder="Tìm kiếm sản phẩm...">
            <button type="submit">Tìm</button>
        </form>
        <div class="hotline">Hotline:19001234</div>
    </div>

    <!-- 🔒 LOCK -->
    <div class="right-icons">
        <a href="#" onclick="return showModal(event)" class="icon-link">
            <i class="fas fa-user"></i>
        </a>
        <a href="#" onclick="return showModal(event)" class="icon-link">
            <i class="fas fa-shopping-cart"></i>
        </a>
        <a href="#" onclick="return showModal(event)" class="icon-link">
            <i class="fas fa-receipt"></i>
        </a>
    </div>
</header>

<!-- ===== DETAIL ===== -->
<div class="product-detail-container">

    <!-- ẢNH -->
    <div class="product-image-section">
        <img src="<?= $product['image'] ?>" class="main-product-image">
    </div>

    <!-- INFO -->
    <div class="product-info-section">
        <h1 class="product-name"><?= $product['name'] ?></h1>

        <div class="product-price">
            <span class="price-label">Giá bán:</span>
            <span class="price-value">
                <?= number_format($product['price']) ?> VNĐ
            </span>
        </div>

        <p class="product-category">
            Loại: <?= $categories[$product['category_id']] ?? 'Khác' ?>
        </p>

        <p><?= $product['description'] ?? 'Chưa có mô tả sản phẩm' ?></p>

        <!-- 🔒 BUTTON -->
        <div class="add-to-cart-container">
            <input type="number" value="1" min="1" class="quantity-input" id="qty-input" />
            <button href="#" class="add-to-cart" onclick="return showModal(event)">
                <i class="fas fa-cart-plus"></i>
            </button>
            <button href="#" class="buy-now-btn" id="buy-now-btn" onclick="return showModal(event)">
                Mua ngay
            </button>

            

        </div>

        <hr>

        <!-- SPEC -->
        <h2>Thông số kỹ thuật</h2>

        <table class="spec-table">
            <tr><td>CPU</td><td><?= $product['cpu'] ?? 'Chưa có' ?></td></tr>
            <tr><td>RAM</td><td><?= $product['ram'] ?? 'Chưa có' ?></td></tr>
            <tr><td>Ổ cứng</td><td><?= $product['storage'] ?? 'Chưa có' ?></td></tr>
            <tr><td>GPU</td><td><?= $product['gpu'] ?? 'Chưa có' ?></td></tr>
            <tr><td>Màn hình</td><td><?= $product['screen'] ?? 'Chưa có' ?></td></tr>
            <tr><td>Pin</td><td><?= $product['battery'] ?? 'Chưa có' ?></td></tr>
            <tr><td>Trọng lượng</td><td><?= $product['weight'] ?? 'Chưa có' ?></td></tr>
            <tr><td>OS</td><td><?= $product['os'] ?? 'Chưa có' ?></td></tr>
        </table>

    </div>
</div>

<!-- ===== JS ===== -->
<script>
function showModal(e) {
    if (e) e.preventDefault();
    document.getElementById("loginModal").classList.add("active");
    return false;
}

function closeModal() {
    document.getElementById("loginModal").classList.remove("active");
}

document.getElementById("loginModal").addEventListener("click", function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>