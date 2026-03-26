<?php
session_start();
include("../../../config.php"); // kết nối database

// 🔒 Kiểm tra admin đã login chưa
if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
    header("Location: login.html");
    exit;
}

// 🔍 Xử lý tìm kiếm
$search = "";
if (isset($_GET['q']) && $_GET['q'] != "") {
    $search = $conn->real_escape_string($_GET['q']);
    $sql = "SELECT * FROM users 
            WHERE CONCAT(ho, ' ', ten) LIKE '%$search%' 
               OR email LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM users";
}

$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản Lý Khách Hàng</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <h2>Muit Store</h2>
            <p>Hệ Thống Quản Lý</p>
        </div>
        <nav class="menu">
            <a href="index.php" class="menu-item"><span>📊</span> Tổng quan</a>
            <a href="customers.php" class="menu-item active"><span>👥</span> Quản Lý Khách Hàng</a>
            <a href="categories.php" class="menu-item"><span>📂</span> Loại Sản Phẩm</a>
            <a href="products.php" class="menu-item"><span>💻</span> Danh Mục Sản Phẩm</a>
            <a href="import.php" class="menu-item"><span>🚚</span> Quản Lý Nhập Hàng</a>
            <a href="price.php" class="menu-item"><span>🏷️</span> Quản Lý Giá Bán</a>
            <a href="orders.php" class="menu-item"><span>🛒</span> Quản Lý Đơn Hàng</a>
            <a href="inventory.php" class="menu-item"><span>📦</span> Quản Lý Tồn Kho</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Quản Lý Khách Hàng</h1>
            <div class="info">
                <img src="../img/logo.png" alt="Admin" />
                <span><?php echo $_SESSION['adminUser']['name']; ?></span>
                <form method="post" action="logout.php" style="display:inline;">
                    <button>Đăng xuất</button>
                </form>
            </div>
        </div>

        <div class="container">
            <!-- Tìm kiếm -->
            <div class="card" style="margin-bottom: 20px">
                <div class="title">
                    <h3>Tìm kiếm khách hàng</h3>
                </div>
                <div class="search">
                    <form method="get" action="">
                        <input type="text" name="q" placeholder="🔍 Tìm kiếm theo tên hoặc email..." class="input-field" value="<?php echo htmlspecialchars($search); ?>" />
                        <button class="btn-search">Tìm kiếm</button>
                        <a href="customers.php"><button type="button" class="btn-reset">Làm mới</button></a>
                    </form>
                </div>
            </div>

            <!-- Danh sách khách hàng -->
            <div class="card">
                <div class="title">
                    <h3>Danh sách khách hàng</h3>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Mã số</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Trạng Thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['ho'] . ' ' . $row['ten']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td>
                                    <b class="status <?php echo isset($row['status']) && $row['status'] == 'active' ? 'status-completed' : 'status-new'; ?>">
                                        <?php echo isset($row['status']) && $row['status'] == 'active' ? 'Hoạt động' : 'Đã khóa'; ?>
                                    </b>
                                </td>
                                <td>
                                    <!-- Reset mật khẩu -->
                                    <form method="post" action="reset_password.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button class="sua">Reset mật khẩu</button>
                                    </form>

                                    <!-- Khóa / Mở khóa -->
                                    <?php if (isset($row['status']) && $row['status'] == 'active'): ?>
                                        <form method="post" action="lock_user.php" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button class="lock">Khóa</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="unlock_user.php" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                            <button class="unlock">Mở khóa</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>