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

// chuyển sang database shop
mysqli_select_db($conn, "shop");

// ===== LẤY DỮ LIỆU TÌM KIẾM =====
$keyword  = isset($_GET['ten'])   ? $_GET['ten']        : '';
$category = isset($_GET['loai'])  ? (int)$_GET['loai']  : 0;
$giatu    = isset($_GET['giatu']) ? (int)$_GET['giatu'] : 0;
$giaden   = isset($_GET['giaden'])? (int)$_GET['giaden']: 0;

$keyword = mysqli_real_escape_string($conn, $keyword);

// ===== PHÂN TRANG =====
$limit  = 8;
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// ===== XÂY DỰNG CÂU SQL =====
$where = "WHERE 1";
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

$categories = [1 => 'Laptop AI', 2 => 'Laptop Gaming', 3 => 'Laptop mỏng nhẹ'];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm sản phẩm - MUIT</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                <li><a href="products.php?category=1">Laptop AI</a></li>
                <li><a href="products.php?category=2">Laptop Gaming</a></li>
                <li><a href="products.php?category=3">Laptop mỏng nhẹ</a></li>
            </ul>
        </nav>

        <div class="search-and-hotline">
            <form action="timkiemnc.php" method="get" class="search-container">
                <input type="text" name="ten" placeholder="Tìm kiếm sản phẩm..." value="<?= $keyword ?>">
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
            <p>Kết quả tìm kiếm cho: "<?= $keyword ?>"</p>
        </div>

        <!-- ===== FORM TÌM KIẾM NÂNG CAO ===== -->
        <form method="get">
            <section class="form_timkiem">
                <h2>TÌM KIẾM NÂNG CAO</h2>

                <div class="tensp">
                    <label>Tên sản phẩm:</label>
                    <input type="text" name="ten" placeholder="Nhập tên sản phẩm" value="<?= $keyword ?>" />
                </div>

                <div class="phanloaisp">
                    <label>Phân loại:</label>
                    <select name="loai">
                        <option value="0">Tất cả</option>
                        <?php foreach ($categories as $id => $name) {
                            $sel = ($category == $id) ? 'selected' : '';
                            echo "<option value='$id' $sel>$name</option>";
                        } ?>
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
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="product-item">
                        <img src="<?= $row['image'] ?>" alt="<?= $row['name'] ?>">
                        <h3><?= $row['name'] ?></h3>
                        <p>Giá: <?= number_format($row['price']) ?> VND</p>
                        <p>Phân loại: <?= $categories[$row['category_id']] ?></p>
                        <div class="product-actions">
                            <!-- Mua ngay -->
                            <a href="checkout.php?id=<?= $row['id'] ?>&qty=1" class="buy-now-link">Mua ngay</a>
                            <!-- Thêm giỏ hàng -->
                            <a href="add_to_cart.php?id=<?= $row['id'] ?>&qty=1" class="add-to-cart">
                                <i class="fas fa-cart-plus"></i>
                            </a>
                            <!-- Xem chi tiết -->
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
                <a class="page prev" href="?page=<?= $page - 1 ?>&ten=<?= $keyword ?>&loai=<?= $category ?>&giatu=<?= $giatu ?>&giaden=<?= $giaden ?>">&lt;</a>
            <?php } ?>

            <?php
            $start = max(1, $page - 2);
            $end   = min($total_pages, $page + 2);
            for ($i = $start; $i <= $end; $i++) {
                $active = ($i == $page) ? "active" : "";
            ?>
                <a class="page <?= $active ?>" href="?page=<?= $i ?>&ten=<?= $keyword ?>&loai=<?= $category ?>&giatu=<?= $giatu ?>&giaden=<?= $giaden ?>"><?= $i ?></a>
            <?php } ?>

            <?php if ($page < $total_pages) { ?>
                <a class="page next" href="?page=<?= $page + 1 ?>&ten=<?= $keyword ?>&loai=<?= $category ?>&giatu=<?= $giatu ?>&giaden=<?= $giaden ?>">&gt;</a>
            <?php } ?>
        </div>
    </main>

</body>
</html>