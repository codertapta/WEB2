<?php
session_start();

// Nếu chưa login admin, redirect về login
if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// Lấy thông tin admin
$adminName = $_SESSION['adminUser']['name'] ?? 'Admin';
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tổng quan - Muit Store</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <h2>Muit Store</h2>
            <p>Hệ Thống Quản Lý</p>
        </div>

        <nav class="menu">
            <a href="index.php" class="menu-item active"><span>📊</span> Tổng quan</a>
            <a href="customers.php" class="menu-item"><span>👥</span> Quản Lý Khách Hàng</a>
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
            <h1>Tổng quan</h1>

            <div class="info">
                <img src="../img/logo.png" alt="Admin" />
                <span><?= htmlspecialchars($adminName) ?></span>
                <form method="POST" action="logout.php" style="display:inline;">
                    <button type="submit">Đăng xuất</button>
                </form>
            </div>
        </div>

        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>Doanh Thu Tháng</h3>
                        <p class="stat-number">450,000,000đ</p>
                        <span class="stat-change positive">+12.5%</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <h3>Đơn Hàng Tháng</h3>
                        <p class="stat-number">156</p>
                        <span class="stat-change positive">+8.3%</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">💻</div>
                    <div class="stat-info">
                        <h3>Sản Phẩm Tồn</h3>
                        <p class="stat-number">89</p>
                        <span class="stat-change negative">-3 SP</span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-info">
                        <h3>Sắp Hết Hàng</h3>
                        <p class="stat-number">5</p>
                        <span class="stat-change">Cần nhập thêm</span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <h3>Đơn Hàng Mới Nhất</h3>

                        <table>
                            <thead>
                                <tr>
                                    <th>Mã ĐH</th>
                                    <th>Khách Hàng</th>
                                    <th>Sản Phẩm</th>
                                    <th>Tổng Tiền</th>
                                    <th>Trạng Thái</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>#DH089</td>
                                    <td>Nguyễn Văn A</td>
                                    <td>Dell XPS 13</td>
                                    <td>28,500,000đ</td>
                                    <td><span class="status status-new">Mới Đặt</span></td>
                                </tr>

                                <tr>
                                    <td>#DH088</td>
                                    <td>Trần Thị B</td>
                                    <td>MacBook Air M2</td>
                                    <td>32,000,000đ</td>
                                    <td><span class="status status-processing">Đang Xử Lý</span></td>
                                </tr>

                                <tr>
                                    <td>#DH087</td>
                                    <td>Lê Văn C</td>
                                    <td>Lenovo ThinkPad</td>
                                    <td>25,800,000đ</td>
                                    <td><span class="status status-completed">Đã Giao</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-4">
                    <div class="card">
                        <h3>Sản Phẩm Bán Chạy</h3>

                        <div class="product-list">
                            <div class="product-item">
                                <div class="product-rank">1</div>
                                <div class="product-details">
                                    <strong>MacBook Pro M3</strong>
                                    <span>45 đã bán</span>
                                </div>
                            </div>

                            <div class="product-item">
                                <div class="product-rank">2</div>
                                <div class="product-details">
                                    <strong>Dell XPS 15</strong>
                                    <span>38 đã bán</span>
                                </div>
                            </div>

                            <div class="product-item">
                                <div class="product-rank">3</div>
                                <div class="product-details">
                                    <strong>ASUS ROG Strix</strong>
                                    <span>32 đã bán</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>