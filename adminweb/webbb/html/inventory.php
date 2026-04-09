<?php
session_start();
require_once(__DIR__ . '/../../../config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../users/trangchu.html");
    exit;
}

// ---- Ngưỡng cảnh báo ----
$low_stock_threshold = 5;
if (isset($_GET['threshold']) && is_numeric($_GET['threshold']) && $_GET['threshold'] >= 0) {
    $low_stock_threshold = intval($_GET['threshold']);
    $_SESSION['low_stock_threshold'] = $low_stock_threshold;
} elseif (isset($_SESSION['low_stock_threshold'])) {
    $low_stock_threshold = $_SESSION['low_stock_threshold'];
}

// ---- Tra cứu tồn kho hiện tại ----
$search_name = trim($_GET['search_name'] ?? '');
$search_category = intval($_GET['search_category'] ?? 0);

// ---- Tra cứu tồn kho tại thời điểm (snapshot) ----
$snapshot_date = $_GET['snapshot_date'] ?? '';
$snapshot_product_id = intval($_GET['snapshot_product_id'] ?? 0); // 0 = tất cả
$snapshot_results = [];

if ($snapshot_date !== '') {
    $target_datetime = $snapshot_date . ' 23:59:59';
    
    // Nếu chọn tất cả sản phẩm
    if ($snapshot_product_id == 0) {
        $sql_snapshot = "
            SELECT p.id, p.name, p.quantity AS current_qty,
                COALESCE(import_after.total_import, 0) AS import_after,
                COALESCE(export_after.total_export, 0) AS export_after
            FROM products p
            LEFT JOIN (
                SELECT idet.product_id, SUM(idet.quantity) AS total_import
                FROM import_details idet
                JOIN import_orders io ON idet.import_order_id = io.id
                WHERE io.created_at > ?
                GROUP BY idet.product_id
            ) import_after ON p.id = import_after.product_id
            LEFT JOIN (
                SELECT od.product_id, SUM(od.quantity) AS total_export
                FROM order_details od
                JOIN orders o ON od.order_id = o.id
                WHERE o.order_date > ? AND o.status != 'cancelled'
                GROUP BY od.product_id
            ) export_after ON p.id = export_after.product_id
            ORDER BY p.name
        ";
        $stmt = $conn->prepare($sql_snapshot);
        $stmt->bind_param("ss", $target_datetime, $target_datetime);
        $stmt->execute();
        $snapshot_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        // Một sản phẩm cụ thể
        $sql_snapshot = "
            SELECT p.id, p.name, p.quantity AS current_qty,
                COALESCE(
                    (SELECT SUM(idet.quantity) 
                     FROM import_details idet 
                     JOIN import_orders io ON idet.import_order_id = io.id 
                     WHERE idet.product_id = p.id AND io.created_at > ?), 0
                ) AS import_after,
                COALESCE(
                    (SELECT SUM(od.quantity) 
                     FROM order_details od 
                     JOIN orders o ON od.order_id = o.id 
                     WHERE od.product_id = p.id AND o.order_date > ? AND o.status != 'cancelled'), 0
                ) AS export_after
            FROM products p
            WHERE p.id = ?
        ";
        $stmt = $conn->prepare($sql_snapshot);
        $stmt->bind_param("ssi", $target_datetime, $target_datetime, $snapshot_product_id);
        $stmt->execute();
        $snapshot_results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// ---- Lịch sử biến động (timeline) ----
$timeline_from = $_GET['timeline_from'] ?? '';
$timeline_to = $_GET['timeline_to'] ?? '';
$timeline_product_id = intval($_GET['timeline_product_id'] ?? 0);
$timeline_events = [];
$start_balance = 0;
$timeline_product = null;

if ($timeline_from !== '' && $timeline_to !== '' && $timeline_product_id > 0) {
    // Lấy thông tin sản phẩm
    $stmt = $conn->prepare("SELECT id, name, quantity FROM products WHERE id = ?");
    $stmt->bind_param("i", $timeline_product_id);
    $stmt->execute();
    $timeline_product = $stmt->get_result()->fetch_assoc();
    
    if ($timeline_product) {
        $start_datetime = $timeline_from . ' 00:00:00';
        $end_datetime = $timeline_to . ' 23:59:59';
        $pid = $timeline_product_id;
        
        // Tính tồn đầu kỳ (tại thời điểm bắt đầu ngày timeline_from)
        $stmt_start = $conn->prepare("
            SELECT 
                p.quantity AS current_qty,
                COALESCE(
                    (SELECT SUM(idet.quantity) FROM import_details idet 
                     JOIN import_orders io ON idet.import_order_id = io.id 
                     WHERE idet.product_id = p.id AND io.created_at >= ?), 0
                ) AS import_after_start,
                COALESCE(
                    (SELECT SUM(od.quantity) FROM order_details od 
                     JOIN orders o ON od.order_id = o.id 
                     WHERE od.product_id = p.id AND o.order_date >= ? AND o.status != 'cancelled'), 0
                ) AS export_after_start
            FROM products p
            WHERE p.id = ?
        ");
        $stmt_start->bind_param("ssi", $start_datetime, $start_datetime, $pid);
        $stmt_start->execute();
        $start_data = $stmt_start->get_result()->fetch_assoc();
        $start_balance = $start_data['current_qty'] - $start_data['import_after_start'] + $start_data['export_after_start'];
        
        // Lấy tất cả giao dịch trong khoảng
        $events_sql = "
            (
                SELECT 
                    'import' AS type,
                    io.created_at AS event_time,
                    idet.quantity AS change_qty,
                    io.id AS ref_id
                FROM import_details idet
                JOIN import_orders io ON idet.import_order_id = io.id
                WHERE idet.product_id = ? AND io.created_at BETWEEN ? AND ?
            )
            UNION ALL
            (
                SELECT 
                    'export' AS type,
                    o.order_date AS event_time,
                    -od.quantity AS change_qty,
                    o.id AS ref_id
                FROM order_details od
                JOIN orders o ON od.order_id = o.id
                WHERE od.product_id = ? AND o.order_date BETWEEN ? AND ? AND o.status != 'cancelled'
            )
            ORDER BY event_time ASC
        ";
        $stmt_events = $conn->prepare($events_sql);
        $stmt_events->bind_param("ississ", $pid, $start_datetime, $end_datetime, $pid, $start_datetime, $end_datetime);
        $stmt_events->execute();
        $timeline_events = $stmt_events->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// ---- Query danh mục & danh sách tồn hiện tại (như cũ) ----
$sql = "
    SELECT p.id, p.name, c.name AS category_name, p.quantity,
           p.cpu, p.ram, p.storage, p.gpu
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE 1=1
";
$params = [];
$types = '';

if ($search_name !== '') {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%$search_name%";
    $types .= 's';
}
if ($search_category > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $search_category;
    $types .= 'i';
}
$sql .= " ORDER BY p.quantity ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// Tính thống kê nhanh
$total_products = count($products);
$out_of_stock = count(array_filter($products, fn($p) => $p['quantity'] == 0));
$low_stock = count(array_filter($products, fn($p) => $p['quantity'] > 0 && $p['quantity'] <= $low_stock_threshold));
$stable = $total_products - $out_of_stock - $low_stock;

?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản Lý Tồn Kho</title>
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        .threshold-form {
            background: #f8fafc; padding: 10px 15px; border-radius: 8px;
            margin-bottom: 15px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap;
        }
        .card { margin-bottom: 30px; }
        .timeline-table td, .timeline-table th { padding: 8px 12px; }
        .import-row { background-color: #f0fdf4; }
        .export-row { background-color: #fef2f2; }
        .balance-cell { font-weight: 600; }
    </style>
</head>
<body>
    <div class="sidebar">
        <!-- sidebar giữ nguyên -->
        <div class="logo">
            <h2>Muit Store</h2>
            <p>Hệ Thống Quản Lý</p>
        </div>
        <nav class="menu">
            <a href="index.php" class="menu-item"><span>📊</span> Tổng quan</a>
            <a href="customers.php" class="menu-item"><span>👥</span> Quản Lý Khách Hàng</a>
            <a href="categories.php" class="menu-item"><span>📂</span> Loại Sản Phẩm</a>
            <a href="products.php" class="menu-item"><span>💻</span> Danh Mục Sản Phẩm</a>
            <a href="import.php" class="menu-item"><span>🚚</span> Quản Lý Nhập Hàng</a>
            <a href="price.php" class="menu-item"><span>🏷️</span> Quản Lý Giá Bán</a>
            <a href="orders.php" class="menu-item"><span>🛒</span> Quản Lý Đơn Hàng</a>
            <a href="inventory.php" class="menu-item active"><span>📦</span> Quản Lý Tồn Kho</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Quản Lý Tồn Kho & Cảnh Báo</h1>
            <div class="info">
                <img src="../img/logo.png" alt="Admin" />
                <span><?= htmlspecialchars($_SESSION['adminUser']['name'] ?? 'Admin') ?></span>
                <a href="?logout=1" style="margin-left:10px; color:#dc2626">Đăng xuất</a>
            </div>
        </div>

        <div class="container">
            <!-- Ngưỡng cảnh báo -->
            <div class="threshold-form">
                <form method="GET" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <input type="hidden" name="search_name" value="<?= htmlspecialchars($search_name) ?>">
                    <input type="hidden" name="search_category" value="<?= $search_category ?>">
                    <div>
                        <label>⚠️ Ngưỡng sắp hết hàng:</label>
                        <input type="number" name="threshold" min="0" value="<?= $low_stock_threshold ?>" step="1" />
                    </div>
                    <button type="submit" class="btn-edit">Áp dụng</button>
                    <small>(Số lượng ≤ ngưỡng sẽ được cảnh báo)</small>
                </form>
            </div>

            <!-- Thống kê nhanh -->
            <div class="inv-summary-bar" style="display:flex; gap:16px; margin-bottom:20px;">
                <div class="inv-summary-card" style="flex:1; background:#fff; border-radius:10px; padding:16px; border-left:4px solid #dc2626;">
                    <span>📦 Tổng SP: <?= $total_products ?></span>
                </div>
                <div class="inv-summary-card" style="flex:1; background:#fff; border-radius:10px; padding:16px; border-left:4px solid #16a34a;">
                    <span>✅ Ổn định: <?= $stable ?></span>
                </div>
                <div class="inv-summary-card" style="flex:1; background:#fff; border-radius:10px; padding:16px; border-left:4px solid #b45309;">
                    <span>⚠️ Sắp hết: <?= $low_stock ?> (≤<?= $low_stock_threshold ?>)</span>
                </div>
                <div class="inv-summary-card" style="flex:1; background:#fff; border-radius:10px; padding:16px; border-left:4px solid #9f1239;">
                    <span>🚫 Hết hàng: <?= $out_of_stock ?></span>
                </div>
            </div>

            <!-- Tra cứu tồn kho hiện tại (giữ nguyên) -->
            <div class="card">
                <h3>📋 Tra Cứu Số Lượng Tồn Hiện Tại</h3>
                <form method="GET" style="display:flex; gap:10px; margin-bottom:15px; flex-wrap:wrap;">
                    <input type="hidden" name="threshold" value="<?= $low_stock_threshold ?>">
                    <div style="flex:1;">
                        <input type="text" name="search_name" class="input-field" placeholder="Tên sản phẩm..." value="<?= htmlspecialchars($search_name) ?>" />
                    </div>
                    <div style="flex:1;">
                        <select name="search_category" class="input-field">
                            <option value="0">Tất cả loại</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $search_category == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn-edit">Tra cứu</button>
                        <a href="inventory.php" class="disable">Xóa</a>
                    </div>
                </form>
                <table>
                    <thead><tr><th>Mã SP</th><th>Tên SP</th><th>Loại</th><th>Tồn</th><th>Trạng thái</th><th>Chi tiết</th></tr></thead>
                    <tbody>
                        <?php foreach ($products as $p): 
                            $qty = intval($p['quantity']);
                            if ($qty == 0) { $badge='status status-new'; $label='Hết hàng'; }
                            elseif ($qty <= $low_stock_threshold) { $badge='status status-processing'; $label='Sắp hết'; }
                            else { $badge='status status-completed'; $label='Ổn định'; }
                        ?>
                        <tr>
                            <td>SP<?= str_pad($p['id'],3,'0',STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= htmlspecialchars($p['category_name']??'—') ?></td>
                            <td><strong><?= $qty ?></strong></td>
                            <td><span class="<?= $badge ?>"><?= $label ?></span></td>
                            <td><a href="#popup-chitiet" onclick="setDetail(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)" class="disable">Xem</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($products)): ?>
                        <tr><td colspan="6" style="text-align:center;">Không có sản phẩm</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- 1. TRA CỨU TỒN KHO TẠI THỜI ĐIỂM -->
            <div class="card">
                <h3>📅 Tra Cứu Tồn Kho Tại Một Thời Điểm (Cuối Ngày)</h3>
                <form method="GET" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
                    <input type="hidden" name="threshold" value="<?= $low_stock_threshold ?>">
                    <div style="min-width:200px;">
                        <label>Chọn sản phẩm:</label>
                        <select name="snapshot_product_id" class="input-field">
                            <option value="0">-- Tất cả sản phẩm --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $snapshot_product_id == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name']) ?> (Tồn hiện tại: <?= $p['quantity'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="min-width:150px;">
                        <label>Ngày tra cứu:</label>
                        <input type="date" name="snapshot_date" class="input-field" value="<?= htmlspecialchars($snapshot_date) ?>" required>
                    </div>
                    <div>
                        <button type="submit" class="btn-save">Tra cứu</button>
                        <a href="inventory.php?threshold=<?= $low_stock_threshold ?>" class="disable">Xóa</a>
                    </div>
                </form>

                <?php if ($snapshot_date && !empty($snapshot_results)): ?>
                <table style="margin-top:15px;">
                    <thead><tr><th>Mã SP</th><th>Tên sản phẩm</th><th>Tồn hiện tại</th><th>Tồn tại <?= htmlspecialchars($snapshot_date) ?> (cuối ngày)</th></tr></thead>
                    <tbody>
                        <?php foreach ($snapshot_results as $row): 
                            $snapshot_qty = $row['current_qty'] - $row['import_after'] + $row['export_after'];
                        ?>
                        <tr>
                            <td>SP<?= str_pad($row['id'],3,'0',STR_PAD_LEFT) ?></td>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= $row['current_qty'] ?></td>
                            <td><strong><?= $snapshot_qty ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php elseif ($snapshot_date): ?>
                    <p style="margin-top:15px; color:#999;">Không có dữ liệu hoặc sản phẩm không tồn tại.</p>
                <?php endif; ?>
            </div>

            <!-- 2. LỊCH SỬ BIẾN ĐỘNG TỒN KHO -->
            <div class="card">
                <h3>📈 Lịch Sử Biến Động Tồn Kho Theo Sản Phẩm</h3>
                <form method="GET" style="display:flex; gap:10px; align-items:end; flex-wrap:wrap;">
                    <input type="hidden" name="threshold" value="<?= $low_stock_threshold ?>">
                    <div style="min-width:220px;">
                        <label>Chọn sản phẩm:</label>
                        <select name="timeline_product_id" class="input-field" required>
                            <option value="">-- Chọn sản phẩm --</option>
                            <?php foreach ($products as $p): ?>
                                <option value="<?= $p['id'] ?>" <?= $timeline_product_id == $p['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="min-width:140px;">
                        <label>Từ ngày:</label>
                        <input type="date" name="timeline_from" class="input-field" value="<?= htmlspecialchars($timeline_from) ?>" required>
                    </div>
                    <div style="min-width:140px;">
                        <label>Đến ngày:</label>
                        <input type="date" name="timeline_to" class="input-field" value="<?= htmlspecialchars($timeline_to) ?>" required>
                    </div>
                    <div>
                        <button type="submit" class="btn-save">Xem lịch sử</button>
                        <a href="inventory.php?threshold=<?= $low_stock_threshold ?>" class="disable">Xóa</a>
                    </div>
                </form>

                <?php if ($timeline_from && $timeline_to && $timeline_product): ?>
                    <div style="margin-top:15px; background:#f9fafb; padding:12px; border-radius:8px;">
                        <strong>Sản phẩm:</strong> <?= htmlspecialchars($timeline_product['name']) ?><br>
                        <strong>Tồn đầu kỳ (<?= $timeline_from ?>):</strong> <?= $start_balance ?> sản phẩm<br>
                        <strong>Tồn hiện tại:</strong> <?= $timeline_product['quantity'] ?>
                    </div>
                    <?php if (!empty($timeline_events)): ?>
                        <table class="timeline-table" style="margin-top:15px; width:100%;">
                            <thead><tr><th>Thời gian</th><th>Loại</th><th>Số lượng thay đổi</th><th>Tồn sau giao dịch</th><th>Tham chiếu</th></tr></thead>
                            <tbody>
                                <?php 
                                $balance = $start_balance;
                                foreach ($timeline_events as $event): 
                                    $change = $event['change_qty'];
                                    $balance += $change;
                                    $type_label = $event['type'] == 'import' ? '📥 Nhập' : '📤 Xuất';
                                    $row_class = $event['type'] == 'import' ? 'import-row' : 'export-row';
                                ?>
                                <tr class="<?= $row_class ?>">
                                    <td><?= date('d/m/Y H:i', strtotime($event['event_time'])) ?></td>
                                    <td><?= $type_label ?></td>
                                    <td><?= ($change > 0 ? '+' : '') . $change ?></td>
                                    <td class="balance-cell"><?= $balance ?></td>
                                    <td>#<?= $event['ref_id'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="margin-top:15px; color:#666;">Không có giao dịch nào trong khoảng thời gian này.</p>
                    <?php endif; ?>
                <?php elseif ($timeline_from && $timeline_to && !$timeline_product): ?>
                    <p style="margin-top:15px; color:#999;">Vui lòng chọn sản phẩm.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Popup chi tiết sản phẩm -->
    <div id="popup-chitiet" class="popup-overlay">
        <div class="popup">
            <a href="inventory.php?threshold=<?= $low_stock_threshold ?>" class="close-btn">&times;</a>
            <h3>Chi tiết sản phẩm</h3>
            <div id="detail-content">
                <p><strong>Tên:</strong> <span id="d-name"></span></p>
                <p><strong>Mã:</strong> <span id="d-id"></span></p>
                <p><strong>Loại:</strong> <span id="d-cat"></span></p>
                <p><strong>Tồn kho:</strong> <span id="d-qty"></span></p>
                <p><strong>Trạng thái:</strong> <span id="d-status"></span></p>
                <p><strong>CPU:</strong> <span id="d-cpu"></span></p>
                <p><strong>RAM:</strong> <span id="d-ram"></span></p>
                <p><strong>Ổ cứng:</strong> <span id="d-storage"></span></p>
                <p><strong>GPU:</strong> <span id="d-gpu"></span></p>
            </div>
        </div>
    </div>

    <script>
        function setDetail(p) {
            document.getElementById('d-name').textContent = p.name ?? '—';
            document.getElementById('d-id').textContent = 'SP' + String(p.id).padStart(3, '0');
            document.getElementById('d-cat').textContent = p.category_name ?? '—';
            document.getElementById('d-qty').textContent = p.quantity;
            document.getElementById('d-cpu').textContent = p.cpu ?? '—';
            document.getElementById('d-ram').textContent = p.ram ?? '—';
            document.getElementById('d-storage').textContent = p.storage ?? '—';
            document.getElementById('d-gpu').textContent = p.gpu ?? '—';
            const qty = parseInt(p.quantity);
            const threshold = <?= $low_stock_threshold ?>;
            let statusText = qty === 0 ? '🔴 Hết hàng' : (qty <= threshold ? '🟡 Sắp hết hàng' : '🟢 Ổn định');
            document.getElementById('d-status').textContent = statusText;
        }
        window.addEventListener('click', function(e) {
            if (e.target.classList.contains('popup-overlay')) {
                window.location.href = 'inventory.php?threshold=<?= $low_stock_threshold ?>';
            }
        });
    </script>
</body>
</html>