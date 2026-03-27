
<?php
session_start();
require __DIR__ . "/../../../config.php";

$user_id = $_SESSION['user_id'] ?? 0;

// đếm giỏ hàng
$count = ["total" => 0];
if ($user_id > 0) {
  $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM cart WHERE user_id=$user_id");
  $count = mysqli_fetch_assoc($result);
}
$conn = mysqli_connect("localhost", "root", "", "shop");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT * FROM products WHERE id = $id";
$result = mysqli_query($conn, $sql);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "Không tìm thấy sản phẩm";
    exit;
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?= $product['name'] ?></title>
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
            <a href="#" class="logo-text">MUIT</a>
        </div>

        <nav>
            <ul class="nav-links">
                <li><a href="products.php">Trang chủ</a></li>
                <li><a href="products.php?category=1">Laptop AI</a></li>
                <li><a href="products.php?category=2">Laptop Gaming</a></li>
                <li><a href="products.php?category=3">Laptop mỏng nhẹ</a></li>
            </ul>
        </nav>

        <div class="search-and-hotline">
            <div class="search-container">
                <input type="text" placeholder="Tìm kiếm sản phẩm...">
                <button>Tìm</button>
            </div>
            <div class="hotline">Hotline:19001234</div>
        </div>

        <div class="right-icons">
            <a href="profile.php" class="icon-link" title="Tài khoản">
                <i class="fas fa-user"></i>
            </a>
             <a href="cart.php" class="icon-link">
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

    <!-- DETAIL -->
    <div class="product-detail-container">

        <!-- ẢNH -->
        <div class="product-image-section">
            <img src="<?= $product['image'] ?>" class="main-product-image">
        </div>

        <!-- THÔNG TIN -->
        <div class="product-info-section">
            <h1 class="product-name"><?= $product['name'] ?></h1>

            <div class="product-price">
                <span class="price-label">Giá bán:</span>
                <span class="price-value">
                    <?= number_format($product['price']) ?> VNĐ
                </span>
            </div>

            <p class="product-short-desc">
                <?= $product['description'] ?? 'Chưa có mô tả sản phẩm' ?>
            </p>

            <!-- NÚT -->
            <div class="add-to-cart-container">

                <input type="number" value="1" min="1" class="quantity-input" id="qty-input" />

                <a href="add_to_cart.php?id=<?php echo $row['id']; ?>" class="add-to-cart">
            <i class="fas fa-cart-plus"></i>
          </a>

                <button class="buy-now-btn" id="buy-now-btn">
                    Mua Ngay
                </button>

            </div>

            <hr>

            <!-- THÔNG SỐ KỸ THUẬT -->
            <h2>Thông số kỹ thuật</h2>

            <table class="spec-table">
                <tr>
                    <td class="spec-name">CPU:</td>
                    <td><?= $product['cpu'] ?? 'Chưa có' ?></td>
                </tr>
                <tr>
                    <td class="spec-name">RAM:</td>
                    <td><?= $product['ram'] ?? 'Chưa có' ?></td>
                </tr>
                <tr>
                    <td class="spec-name">Ổ cứng:</td>
                    <td><?= $product['storage'] ?? 'Chưa có' ?></td>
                </tr>
                <tr>
                    <td class="spec-name">Card đồ họa:</td>
                    <td><?= $product['gpu'] ?? 'Chưa có' ?></td>
                </tr>
                <tr>
                    <td class="spec-name">Màn hình:</td>
                    <td><?= $product['screen'] ?? 'Chưa có' ?></td>
                </tr>
                <tr>
                    <td class="spec-name">Pin:</td>
                    <td><?= $product['battery'] ?? 'Chưa có' ?></td>
                </tr>
                <tr>
                    <td class="spec-name">Trọng lượng:</td>
                    <td><?= $product['weight'] ?? 'Chưa có' ?></td>
                </tr>
                <tr>
                    <td class="spec-name">Hệ điều hành:</td>
                    <td><?= $product['os'] ?? 'Chưa có' ?></td>
                </tr>
            </table>

        </div>
    </div>

    <script>
        document.getElementById('buy-now-btn').addEventListener('click', function () {
            const qty = document.getElementById('qty-input').value || 1;
            window.location.href = 'checkout.php?id=<?= $product['id'] ?>&qty=' + qty;
        });
    </script>

</body>
</html>