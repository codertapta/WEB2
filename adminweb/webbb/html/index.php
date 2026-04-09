<?php
session_start();

// Nếu chưa login admin, redirect về login
if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// Kết nối database
include("../../../config.php");

// Lấy thông tin admin
$adminName = $_SESSION['adminUser']['name'] ?? 'Admin';

// ---- Xử lý ngưỡng cảnh báo sắp hết hàng (đồng bộ với inventory) ----
$low_stock_threshold = 5; // mặc định

if (isset($_GET['threshold']) && is_numeric($_GET['threshold']) && $_GET['threshold'] >= 0) {
    $low_stock_threshold = intval($_GET['threshold']);
    $_SESSION['low_stock_threshold'] = $low_stock_threshold;
} elseif (isset($_SESSION['low_stock_threshold'])) {
    $low_stock_threshold = $_SESSION['low_stock_threshold'];
}

// ========== LẤY DỮ LIỆU THỐNG KÊ ==========

// 1. Doanh thu tháng hiện tại
$month = date('m');
$year  = date('Y');
$sql_revenue = "SELECT SUM(total_price) as revenue FROM orders WHERE MONTH(order_date) = $month AND YEAR(order_date) = $year";
$result_revenue = mysqli_query($conn, $sql_revenue);
$revenue_data = mysqli_fetch_assoc($result_revenue);
$revenue = $revenue_data['revenue'] ?? 0;
$revenue_formatted = number_format($revenue, 0, ',', '.') . 'đ';

// 2. Đơn hàng tháng
$sql_orders_count = "SELECT COUNT(*) as count FROM orders WHERE MONTH(order_date) = $month AND YEAR(order_date) = $year";
$result_orders_count = mysqli_query($conn, $sql_orders_count);
$orders_count_data = mysqli_fetch_assoc($result_orders_count);
$orders_count = $orders_count_data['count'] ?? 0;

// 3. Tổng sản phẩm tồn kho
$sql_stock_total = "SELECT SUM(quantity) as total_stock FROM products";
$result_stock_total = mysqli_query($conn, $sql_stock_total);
$stock_total_data = mysqli_fetch_assoc($result_stock_total);
$total_stock = $stock_total_data['total_stock'] ?? 0;

// 4. Số sản phẩm sắp hết hàng (dùng ngưỡng động)
$sql_low_stock = "SELECT COUNT(*) as count FROM products WHERE quantity <= $low_stock_threshold AND quantity > 0";
$result_low_stock = mysqli_query($conn, $sql_low_stock);
$low_stock_data = mysqli_fetch_assoc($result_low_stock);
$low_stock_count = $low_stock_data['count'] ?? 0;

// 5. Đơn hàng mới nhất (5 đơn)
$sql_recent_orders = "
    SELECT o.id, o.ma_don, o.total_price, o.status, o.order_date, u.ho, u.ten
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
    LIMIT 5
";
$recent_orders = mysqli_query($conn, $sql_recent_orders);

// 6. Sản phẩm bán chạy nhất (top 3)
$sql_top_products = "
    SELECT p.name, SUM(od.quantity) as total_sold
    FROM order_details od
    JOIN products p ON od.product_id = p.id
    GROUP BY od.product_id
    ORDER BY total_sold DESC
    LIMIT 3
";
$top_products = mysqli_query($conn, $sql_top_products);
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tổng quan - Muit Store</title>
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        .threshold-widget {
            background: #fff3cd;
            border: 1px solid #ffecb5;
            border-radius: 8px;
            padding: 8px 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .threshold-widget label { font-weight: 600; margin-right: 5px; }
        .threshold-widget input { width: 80px; padding: 5px; border-radius: 6px; border: 1px solid #ccc; }
        .threshold-widget button { background: #dc2626; color: white; border: none; padding: 5px 12px; border-radius: 6px; cursor: pointer; }
    </style>
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
            <!-- Widget điều chỉnh ngưỡng cảnh báo -->
            <div class="threshold-widget">
                <form method="GET" style="display: flex; align-items: center; gap: 10px;">
                    <label>⚠️ Ngưỡng sắp hết hàng:</label>
                    <input type="number" name="threshold" min="0" value="<?= $low_stock_threshold ?>" step="1" />
                    <button type="submit">Áp dụng</button>
                </form>
                <small style="color:#856404;">(Số lượng ≤ <?= $low_stock_threshold ?> sẽ được cảnh báo)</small>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>Doanh Thu Tháng</h3>
                        <p class="stat-number"><?= $revenue_formatted ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-info">
                        <h3>Đơn Hàng Tháng</h3>
                        <p class="stat-number"><?= $orders_count ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">💻</div>
                    <div class="stat-info">
                        <h3>Sản Phẩm Tồn</h3>
                        <p class="stat-number"><?= $total_stock ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-info">
                        <h3>Sắp Hết Hàng</h3>
                        <p class="stat-number"><?= $low_stock_count ?></p>
                        <span class="stat-change">Ngưỡng ≤ <?= $low_stock_threshold ?></span>
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
                                    <th>Tổng Tiền</th>
                                    <th>Trạng Thái</th>
                                    <th>Ngày Đặt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($recent_orders) > 0): ?>
                                    <?php while ($order = mysqli_fetch_assoc($recent_orders)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($order['ma_don'] ?? '#DH' . $order['id']) ?></td>
                                            <td><?= htmlspecialchars($order['ho'] . ' ' . $order['ten']) ?></td>
                                            <td><?= number_format($order['total_price'], 0, ',', '.') ?>đ</td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status_text = '';
                                                switch ($order['status']) {
                                                    case 'pending':
                                                        $status_class = 'status-new';
                                                        $status_text = 'Mới Đặt';
                                                        break;
                                                    case 'processing':
                                                        $status_class = 'status-processing';
                                                        $status_text = 'Đang Xử Lý';
                                                        break;
                                                    case 'completed':
                                                        $status_class = 'status-completed';
                                                        $status_text = 'Đã Giao';
                                                        break;
                                                    case 'cancelled':
                                                        $status_class = 'status-cancelled';
                                                        $status_text = 'Đã Hủy';
                                                        break;
                                                    default:
                                                        $status_class = 'status-new';
                                                        $status_text = ucfirst($order['status']);
                                                }
                                                ?>
                                                <span class="status <?= $status_class ?>"><?= $status_text ?></span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($order['order_date'])) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5">Chưa có đơn hàng nào</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-4">
                    <div class="card">
                        <h3>Sản Phẩm Bán Chạy</h3>

                        <div class="product-list">
                            <?php 
                            $rank = 1;
                            if (mysqli_num_rows($top_products) > 0):
                                while ($prod = mysqli_fetch_assoc($top_products)):
                            ?>
                                <div class="product-item">
                                    <div class="product-rank"><?= $rank++ ?></div>
                                    <div class="product-details">
                                        <strong><?= htmlspecialchars($prod['name']) ?></strong>
                                        <span><?= $prod['total_sold'] ?> đã bán</span>
                                    </div>
                                </div>
                            <?php 
                                endwhile;
                            else:
                            ?>
                                <p>Chưa có dữ liệu bán hàng</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>