<?php
session_start();
require __DIR__ . "/../../../config.php";

mysqli_select_db($conn, "shop");

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
$where = "WHERE 1";

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

// ===== DANH MỤC =====
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
    <title>Tìm kiếm - MUIT</title>

    <link rel="stylesheet" href="../css/products_guest.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <li><a href="?loai=1">Laptop AI</a></li>
            <li><a href="?loai=2">Laptop Gaming</a></li>
            <li><a href="?loai=3">Laptop mỏng nhẹ</a></li>
        </ul>
    </nav>

    <div class="search-and-hotline">
        <form method="get" class="search-container">
            <input type="text" name="ten" placeholder="Tìm kiếm..." value="<?= $keyword ?>">
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
        <p>Kết quả cho: "<?= $keyword ?>"</p>
    </div>

    <!-- ===== FORM ===== -->
    <form method="get">
        <section class="form_timkiem">

            <h2>TÌM KIẾM NÂNG CAO</h2>

            <input type="text" name="ten" placeholder="Tên sản phẩm" value="<?= $keyword ?>">

            <select name="loai">
                <option value="0">Tất cả</option>
                <?php foreach ($categories as $id => $name): ?>
                    <option value="<?= $id ?>" <?= ($category == $id ? 'selected' : '') ?>>
                        <?= $name ?>
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
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="product-item">

                    <img src="<?= $row['image'] ?>" alt="<?= $row['name'] ?>">

                    <h3><?= $row['name'] ?></h3>

                    <p>Giá: <?= number_format($row['price']) ?> VND</p>

                    <p>Phân loại: <?= $categories[$row['category_id']] ?? 'Khác' ?></p>

                    <div class="product-actions">

                        <a href="#" class="buy-now-link" onclick="return showModal(event)">
                            Mua ngay
                        </a>

                        <a href="#" class="add-to-cart" onclick="return showModal(event)">
                            <i class="fas fa-cart-plus"></i>
                        </a>

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
            <a class="page prev" href="?page=<?= $page-1 ?>&ten=<?= $keyword ?>&loai=<?= $category ?>">&lt;</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a class="page <?= ($i == $page ? 'active' : '') ?>"
               href="?page=<?= $i ?>&ten=<?= $keyword ?>&loai=<?= $category ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $total_pages): ?>
            <a class="page next" href="?page=<?= $page+1 ?>&ten=<?= $keyword ?>&loai=<?= $category ?>">&gt;</a>
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