<?php
session_start();
require __DIR__ . "/../../../config.php";

mysqli_select_db($conn, "shop");

// ===== LẤY DANH MỤC TỪ DATABASE (CHỦ ĐỘNG) =====
$categories = [];
$catQuery = mysqli_query($conn, "SELECT id, name FROM categories WHERE status = 1 ORDER BY id");
while ($cat = mysqli_fetch_assoc($catQuery)) {
    $categories[$cat['id']] = $cat['name'];
}

// ===== LẤY DỮ LIỆU =====
$keyword  = $_GET['ten']   ?? '';
$category = (int)($_GET['loai'] ?? 0);
$giatu    = (int)($_GET['giatu'] ?? 0);
$giaden   = (int)($_GET['giaden'] ?? 0);

$keyword = mysqli_real_escape_string($conn, $keyword);

// ===== PHÂN TRANG =====
$limit = 8;
$page  = (int)($_GET['page'] ?? 1);
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// ===== SQL =====
$where = "WHERE status = 1";

if ($keyword != '') {
    $where .= " AND name LIKE '%$keyword%'";
}
if ($category > 0) {
    $where .= " AND category_id = $category";
}
if ($giatu > 0) {
    $where .= " AND price >= $giatu";
}
if ($giaden > 0) {
    $where .= " AND price <= $giaden";
}

$sql = "SELECT * FROM products $where LIMIT $limit OFFSET $offset";
$result = mysqli_query($conn, $sql);

$total_sql = "SELECT COUNT(*) as total FROM products $where";
$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $limit);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tìm kiếm - MUIT</title>

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

<!-- ===== MODAL ===== -->
<div class="modal-overlay" id="loginModal">
    <div class="modal-box">
        <button class="btn-close" onclick="closeModal()"><i class="fas fa-times"></i></button>
        <i class="fas fa-lock"></i>
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
        <a href="products_guest.php" class="logo"><img src="../img/logo.png"></a>
        <a href="products_guest.php" class="logo-text">MUIT</a>
    </div>

    <nav>
        <ul class="nav-links">
            <li><a href="products_guest.php">Trang chủ</a></li>
            <?php foreach ($categories as $id => $name): ?>
                <li><a href="?loai=<?= $id ?>"><?= htmlspecialchars($name) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="search-and-hotline">
        <form method="get" class="search-container">
            <input type="text" name="ten" placeholder="Tìm kiếm..." value="<?= htmlspecialchars($keyword) ?>">
            <button type="submit">Tìm</button>
        </form>
        <div class="hotline">Hotline: 19001234</div>
    </div>

    <div class="right-icons">
        <a href="#" onclick="return showModal(event)" class="icon-link"><i class="fas fa-user"></i></a>
        <a href="#" onclick="return showModal(event)" class="icon-link"><i class="fas fa-shopping-cart"></i></a>
        <a href="#" onclick="return showModal(event)" class="icon-link"><i class="fas fa-receipt"></i></a>
    </div>
</header>

<!-- ===== MAIN ===== -->
<main>

    <div class="timkiem">
        <h1>Tìm kiếm sản phẩm</h1>
        <p>Kết quả cho: "<?= htmlspecialchars($keyword) ?>"</p>
    </div>

    <!-- ===== FORM ===== -->
    <form method="get">
        <section class="form_timkiem">

            <h2>TÌM KIẾM NÂNG CAO</h2>

            <input type="text" name="ten" placeholder="Tên sản phẩm" value="<?= htmlspecialchars($keyword) ?>">

            <select name="loai">
                <option value="0">Tất cả</option>
                <?php foreach ($categories as $id => $name): ?>
                    <option value="<?= $id ?>" <?= ($category == $id ? 'selected' : '') ?>>
                        <?= htmlspecialchars($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="number" name="giatu" placeholder="Giá từ" value="<?= $giatu ?>">
            <input type="number" name="giaden" placeholder="Giá đến" value="<?= $giaden ?>">

            <div class="timsp">
                <button type="submit">Tìm kiếm</button>
                <button type="reset">Đặt lại</button>
            </div>

        </section>
    </form>

    <!-- ===== PRODUCTS ===== -->
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
                            <a href="#" class="buy-now-link" onclick="return showModal(event)">Mua ngay</a>
                            <a href="#" class="add-to-cart" onclick="return showModal(event)">
                                <i class="fas fa-cart-plus"></i>
                            </a>
                        <?php else: ?>
                            <span class="out-of-stock-label">Tạm hết hàng</span>
                        <?php endif; ?>
                        <a href="detail_guest.php?id=<?= $row['id'] ?>" class="view-detail">
                            <i class="fas fa-eye"></i> Xem chi tiết
                        </a>
                    </div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Không tìm thấy sản phẩm</p>
        <?php endif; ?>
    </div>

    <!-- ===== PAGINATION ===== -->
    <div class="product-pagination">
        <?php if ($page > 1): ?>
            <a class="page prev" href="?page=<?= $page-1 ?>&ten=<?= urlencode($keyword) ?>&loai=<?= $category ?>&giatu=<?= $giatu ?>&giaden=<?= $giaden ?>">&lt;</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a class="page <?= ($i == $page ? 'active' : '') ?>"
               href="?page=<?= $i ?>&ten=<?= urlencode($keyword) ?>&loai=<?= $category ?>&giatu=<?= $giatu ?>&giaden=<?= $giaden ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a class="page next" href="?page=<?= $page+1 ?>&ten=<?= urlencode($keyword) ?>&loai=<?= $category ?>&giatu=<?= $giatu ?>&giaden=<?= $giaden ?>">&gt;</a>
        <?php endif; ?>
    </div>

</main>

<!-- ===== JS ===== -->
<script>
function showModal(e) {
    e.preventDefault();
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