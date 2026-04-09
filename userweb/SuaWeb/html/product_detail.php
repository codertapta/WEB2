<?php
session_start();
require __DIR__ . "/../../../config.php";

$user_id = $_SESSION['user_id'] ?? 0;

$count = ["total" => 0];
if ($user_id > 0) {
    $result_cart = mysqli_query($conn, "SELECT COUNT(*) as total FROM cart WHERE user_id=$user_id");
    $count       = mysqli_fetch_assoc($result_cart);
}

// ===== LẤY DANH MỤC TỪ DATABASE (CHỦ ĐỘNG) =====
$categories = [];
$catQuery = mysqli_query($conn, "SELECT id, name FROM categories WHERE status = 1 ORDER BY id");
while ($cat = mysqli_fetch_assoc($catQuery)) {
    $categories[$cat['id']] = $cat['name'];
}

// ===== LẤY SẢN PHẨM (CHỈ HIỂN THỊ NẾU STATUS=1) =====
$id             = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$result_product = mysqli_query($conn, "SELECT * FROM products WHERE id = $id AND status = 1");
$product        = mysqli_fetch_assoc($result_product);

if (!$product) {
    echo "Không tìm thấy sản phẩm hoặc sản phẩm đã bị ẩn.";
    exit;
}

$stock = (int)$product['quantity'];
$inStock = ($stock > 0);
$stockText = $inStock ? "Còn $stock sản phẩm" : "Hết hàng";
$stockClass = $inStock ? "in-stock" : "out-of-stock";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stock-info {
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .in-stock {
            color: #2e7d32;
        }
        .out-of-stock {
            color: #c62828;
        }
        .add-to-cart-container button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .out-of-stock-label {
            background: #f5f5f5;
            padding: 8px 15px;
            border-radius: 4px;
            color: #555;
            font-weight: bold;
        }
    </style>
</head>

<body>

<header class="header">
    <div class="logo-section">
        <a href="products.php" class="logo">
            <img src="../img/logo.png">
        </a>
        <a href="#" class="logo-text">MUIT</a>
    </div>

    <nav>
        <ul class="nav-links">
            <li><a href="products.php">Trang chủ</a></li>
            <?php foreach ($categories as $catId => $catName): ?>
                <li><a href="products.php?category=<?= $catId ?>"><?= htmlspecialchars($catName) ?></a></li>
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
            <span class="cart-count">
                <?= $count['total'] ? $count['total'] : 0 ?>
            </span>
        </a>

        <a href="orders.php" class="icon-link">
            <i class="fas fa-receipt"></i>
        </a>
    </div>
</header>

<div class="product-detail-container">

    <div class="product-image-section">
        <img src="<?= htmlspecialchars($product['image']) ?>" class="main-product-image">
    </div>

    <div class="product-info-section">
        <h1 class="product-name"><?= htmlspecialchars($product['name']) ?></h1>

        <div class="product-price">
            <span class="price-label">Giá bán:</span>
            <span class="price-value">
                <?= number_format($product['price']) ?> VNĐ
            </span>
        </div>

        <p class="product-category">
            Loại: <?= htmlspecialchars($categories[$product['category_id']] ?? 'Khác') ?>
        </p>

        <!-- 📦 HIỂN THỊ TỒN KHO -->
        <p class="stock-info <?= $stockClass ?>">
            📦 Tồn kho: <?= $stockText ?>
        </p>

        <p class="product-short-desc">
            <?= nl2br(htmlspecialchars($product['description'] ?? 'Chưa có mô tả sản phẩm')) ?>
        </p>

        <div class="add-to-cart-container">
            <input type="number" value="1" min="1" class="quantity-input" id="qty-input" 
                   <?= !$inStock ? 'disabled' : '' ?> />

            <?php if ($inStock): ?>
                <button id="add-to-cart-btn">
                    <i class="fas fa-cart-plus"></i>
                </button>
                <button id="buy-now-btn">
                    Mua Ngay
                </button>
            <?php else: ?>
                <span class="out-of-stock-label">Tạm hết hàng</span>
            <?php endif; ?>
        </div>

        <hr>

        <h2>Thông số kỹ thuật</h2>

        <table class="spec-table">
            <tr>
                <td>CPU:</td>
                <td><?= htmlspecialchars($product['cpu'] ?? 'Chưa có') ?></td>
            </tr>
            <tr>
                <td>RAM:</td>
                <td><?= htmlspecialchars($product['ram'] ?? 'Chưa có') ?></td>
            </tr>
            <tr>
                <td>Ổ cứng:</td>
                <td><?= htmlspecialchars($product['storage'] ?? 'Chưa có') ?></td>
            </tr>
            <tr>
                <td>GPU:</td>
                <td><?= htmlspecialchars($product['gpu'] ?? 'Chưa có') ?></td>
            </tr>
            <tr>
                <td>Màn hình:</td>
                <td><?= htmlspecialchars($product['screen'] ?? 'Chưa có') ?></td>
            </tr>
            <tr>
                <td>Pin:</td>
                <td><?= htmlspecialchars($product['battery'] ?? 'Chưa có') ?></td>
            </tr>
            <tr>
                <td>Trọng lượng:</td>
                <td><?= htmlspecialchars($product['weight'] ?? 'Chưa có') ?></td>
            </tr>
            <tr>
                <td>OS:</td>
                <td><?= htmlspecialchars($product['os'] ?? 'Chưa có') ?></td>
            </tr>
        </table>

    </div>
</div>

<script>
    document.getElementById('add-to-cart-btn')?.addEventListener('click', function () {
        const qty = document.getElementById('qty-input').value || 1;
        window.location.href = 'add_to_cart.php?id=<?= $product['id'] ?>&qty=' + qty;
    });

    document.getElementById('buy-now-btn')?.addEventListener('click', function () {
        const qty = document.getElementById('qty-input').value || 1;
        window.location.href = 'checkout.php?id=<?= $product['id'] ?>&qty=' + qty;
    });
</script>

</body>
</html>