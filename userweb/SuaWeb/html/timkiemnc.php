<?php
session_start();
require __DIR__ . "/../../../config.php";

$user_id = $_SESSION['user_id'] ?? 0;

// đếm giỏ hàng
$count = ["total" => 0];
if ($user_id > 0) {
    $result_cart = mysqli_query($conn, "SELECT COUNT(*) as total FROM cart WHERE user_id=$user_id");
    $count = mysqli_fetch_assoc($result_cart);
}

// ===== LẤY DANH MỤC TỪ DATABASE =====
$categories = [];
$catQuery = mysqli_query($conn, "SELECT id, name FROM categories WHERE status = 1 ORDER BY id");
while ($cat = mysqli_fetch_assoc($catQuery)) {
    $categories[$cat['id']] = $cat['name'];
}

// ===== LẤY DỮ LIỆU TÌM KIẾM =====
$keyword  = isset($_GET['ten'])    ? $_GET['ten']        : '';
$category = isset($_GET['loai'])   ? (int)$_GET['loai']  : 0;
$giatu    = isset($_GET['giatu'])  ? (int)$_GET['giatu'] : 0;
$giaden   = isset($_GET['giaden']) ? (int)$_GET['giaden']: 0;

$keyword = mysqli_real_escape_string($conn, $keyword);

// ===== PHÂN TRANG =====
$limit  = 8;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// ===== XÂY DỰNG CÂU SQL =====
$where = "WHERE status = 1";
if ($keyword  != '') $where .= " AND name LIKE '%$keyword%'";
if ($category  > 0)  $where .= " AND category_id = $category";
if ($giatu     > 0)  $where .= " AND price >= $giatu";
if ($giaden    > 0)  $where .= " AND price <= $giaden";

$sql    = "SELECT * FROM products $where LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$total_result   = mysqli_query($conn, "SELECT COUNT(*) as total FROM products $where");
$total_row      = mysqli_fetch_assoc($total_result);
$total_products = $total_row['total'];
$total_pages    = ceil($total_products / $limit);
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm sản phẩm - MUIT</title>
    <link rel="stylesheet" href="../css/style.css">
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
        .product-actions .disabled-link {
            pointer-events: none;
            opacity: 0.5;
            cursor: default;
        }
    </style>
</head>

<body>

    <!-- ===== HEADER ===== -->
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
                <?php foreach ($categories as $id => $name): ?>
                    <li><a href="products.php?category=<?= $id ?>"><?= htmlspecialchars($name) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="search-and-hotline">
            <form action="timkiemnc.php" method="get" class="search-container">
                <input type="text" name="ten" placeholder="Tìm kiếm sản phẩm..." value="<?= htmlspecialchars($keyword) ?>">
                <button type="submit">Tìm</button>
            </form>
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

    <main>
        <div class="timkiem">
            <h1>Tìm kiếm sản phẩm</h1>
            <p>Kết quả tìm kiếm cho: "<?= htmlspecialchars($keyword) ?>"</p>
        </div>

        <!-- ===== FORM TÌM KIẾM NÂNG CAO ===== -->
        <form method="get">
            <section class="form_timkiem">
                <h2>TÌM KIẾM NÂNG CAO</h2>

                <div class="tensp">
                    <label>Tên sản phẩm:</label>
                    <input type="text" name="ten" placeholder="Nhập tên sản phẩm" value="<?= htmlspecialchars($keyword) ?>" />
                </div>

                <div class="phanloaisp">
                    <label>Phân loại:</label>
                    <select name="loai">
                        <option value="0">Tất cả</option>
                        <?php foreach ($categories as $id => $name): ?>
                            <option value="<?= $id ?>" <?= ($category == $id) ? 'selected' : '' ?>><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="giasp">
                    <div class="tu">
                        <label>Giá từ:</label>
                        <input type="number" name="giatu" value="<?= $giatu ?>" />
                    </div>
                    <div class="den">
                        <label>Giá đến:</label>
                        <input type="number" name="giaden" value="<?= $giaden ?>" />
                    </div>
                </div>

                <div class="timsp">
                    <button type="submit">Tìm kiếm</button>
                    <button type="reset">Đặt lại</button>
                </div>
            </section>
        </form>

        <!-- ===== DANH SÁCH SẢN PHẨM ===== -->
        <div class="product-grid">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)):
                    $stock = (int)$row['quantity'];
                    $inStock = ($stock > 0);
                    $stockText = $inStock ? "Còn $stock sản phẩm" : "Hết hàng";
                    $stockClass = $inStock ? "in-stock" : "out-of-stock";
                ?>
                    <div class="product-item">
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        <h3><?= htmlspecialchars($row['name']) ?></h3>
                        <p>Giá: <?= number_format($row['price']) ?> VND</p>
                        <p>Phân loại: <?= htmlspecialchars($categories[$row['category_id']] ?? 'Khác') ?></p>
                        <p class="stock-info <?= $stockClass ?>">📦 Tồn kho: <?= $stockText ?></p>
                        <div class="product-actions">
                            <?php if ($inStock): ?>
                                <a href="checkout.php?id=<?= $row['id'] ?>&qty=1" class="buy-now-link">Mua ngay</a>
                                <a href="add_to_cart.php?id=<?= $row['id'] ?>&qty=1" class="add-to-cart">
                                    <i class="fas fa-cart-plus"></i>
                                </a>
                            <?php else: ?>
                                <span class="out-of-stock-label">Tạm hết hàng</span>
                            <?php endif; ?>
                            <a href="product_detail.php?id=<?= $row['id'] ?>" class="view-detail">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Không tìm thấy sản phẩm</p>
            <?php endif; ?>
        </div>

        <!-- ===== PHÂN TRANG ===== -->
        <div class="product-pagination">
            <?php if ($page > 1) { ?>
                <a class="page prev" href="?page=<?= $page - 1 ?>&ten=<?= urlencode($keyword) ?>&loai=<?= $category ?>&giatu=<?= $giatu ?>&giaden=<?= $giaden ?>">&lt;</a>
            <?php } ?>

            <?php
            $start = max(1, $page - 2);
            $end   = min($total_pages, $page + 2);
            for ($i = $start; $i <= $end; $i++) {
                $active = ($i == $page) ? "active" : "";
            ?>
                <a class="page <?= $active ?>" href="?page=<?= $i ?>&ten=<?= urlencode($keyword) ?>&loai=<?= $category ?>&giatu=<?= $giatu ?>&giaden=<?= $giaden ?>"><?= $i ?></a>
            <?php } ?>

            <?php if ($page < $total_pages) { ?>
                <a class="page next" href="?page=<?= $page + 1 ?>&ten=<?= urlencode($keyword) ?>&loai=<?= $category ?>&giatu=<?= $giatu ?>&giaden=<?= $giaden ?>">&gt;</a>
            <?php } ?>
        </div>
    </main>

</body>
</html>