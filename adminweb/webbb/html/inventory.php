<?php
session_start();
require_once(__DIR__ . '/../../../config.php');

// Kiểm tra đăng nhập
if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
    header("Location: admin-login.php");
    exit;
}

function logout() {
    session_destroy();
    header("Location: ../users/trangchu.html");
    exit;
}

if (isset($_POST['action'])) {
    $action = $_POST['action'];

    // Cập nhật tồn kho thủ công
    if ($action === 'capnhat') {
        $product_id = intval($_POST['product_id']);
        $new_qty    = intval($_POST['new_qty']);
        $stmt = $conn->prepare("UPDATE products SET quantity = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_qty, $product_id);
        $stmt->execute();
        $_SESSION['msg'] = ['type' => 'success', 'text' => 'Cập nhật tồn kho thành công!'];
        header("Location: inventory.php");
        exit;
    }

    // Nhập thêm hàng
    if ($action === 'nhap') {
        $product_id = intval($_POST['product_id']);
        $qty_add    = intval($_POST['qty_add']);
        if ($qty_add > 0) {
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
            $stmt->bind_param("ii", $qty_add, $product_id);
            $stmt->execute();
            $_SESSION['msg'] = ['type' => 'success', 'text' => "Đã nhập thêm $qty_add sản phẩm!"];
        }
        header("Location: inventory.php");
        exit;
    }

    // Xuất hàng
    if ($action === 'xuat') {
        $product_id = intval($_POST['product_id']);
        $qty_out    = intval($_POST['qty_out']);
        // Kiểm tra tồn kho đủ không
        $check = $conn->prepare("SELECT quantity FROM products WHERE id = ?");
        $check->bind_param("i", $product_id);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();
        if ($row && $row['quantity'] >= $qty_out && $qty_out > 0) {
            $stmt = $conn->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->bind_param("ii", $qty_out, $product_id);
            $stmt->execute();
            $_SESSION['msg'] = ['type' => 'success', 'text' => "Đã xuất $qty_out sản phẩm!"];
        } else {
            $_SESSION['msg'] = ['type' => 'error', 'text' => "Số lượng xuất vượt tồn kho hoặc không hợp lệ!"];
        }
        header("Location: inventory.php");
        exit;
    }
}

// ---- Xử lý tìm kiếm ----
$search_name     = trim($_GET['search_name'] ?? '');
$search_category = intval($_GET['search_category'] ?? 0);
$search_date     = $_GET['search_date'] ?? '';

// Tìm kiếm nhập-xuất-tồn theo khoảng thời gian
$range_name  = trim($_GET['range_name'] ?? '');
$range_from  = $_GET['range_from'] ?? '';
$range_to    = $_GET['range_to'] ?? '';

// ---- Query danh sách tồn kho ----
$sql = "
    SELECT p.id, p.name, c.name AS category_name, p.quantity
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE 1=1
";
$params = [];
$types  = '';

if ($search_name !== '') {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%$search_name%";
    $types   .= 's';
}
if ($search_category > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $search_category;
    $types   .= 'i';
}
$sql .= " ORDER BY p.quantity ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ---- Query danh sách loại sản phẩm cho select ----
$categories = $conn->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name")->fetch_all(MYSQLI_ASSOC);

// ---- Query nhập-xuất-tồn theo khoảng thời gian ----
$range_rows = [];
if ($range_from !== '' && $range_to !== '') {
    $range_sql = "
        SELECT
            p.id,
            p.name,
            COALESCE(SUM(CASE WHEN DATE(id_sub.import_date) BETWEEN ? AND ? THEN id_sub.quantity ELSE 0 END), 0) AS total_nhap,
            COALESCE(SUM(CASE WHEN DATE(o.order_date) BETWEEN ? AND ? AND o.status != 'cancelled' THEN od.quantity ELSE 0 END), 0) AS total_xuat,
            p.quantity AS ton_hien_tai
        FROM products p
        LEFT JOIN import_details idet ON idet.product_id = p.id
        LEFT JOIN import_orders id_sub ON id_sub.id = idet.import_order_id
        LEFT JOIN order_details od ON od.product_id = p.id
        LEFT JOIN orders o ON o.id = od.order_id
        WHERE 1=1
    ";
    $range_params = [$range_from, $range_to, $range_from, $range_to];
    $range_types  = 'ssss';

    if ($range_name !== '') {
        $range_sql   .= " AND p.name LIKE ?";
        $range_params[] = "%$range_name%";
        $range_types  .= 's';
    }

    $range_sql .= " GROUP BY p.id, p.name, p.quantity ORDER BY p.name";

    $rs = $conn->prepare($range_sql);
    $rs->bind_param($range_types, ...$range_params);
    $rs->execute();
    $range_rows = $rs->get_result()->fetch_all(MYSQLI_ASSOC);
}

// ---- Lấy thông tin chi tiết một sản phẩm (popup) ----
$detail_product = null;
if (isset($_GET['detail_id'])) {
    $did  = intval($_GET['detail_id']);
    $ds   = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $ds->bind_param("i", $did);
    $ds->execute();
    $detail_product = $ds->get_result()->fetch_assoc();
}

// ---- Flash message ----
$msg = $_SESSION['msg'] ?? null;
unset($_SESSION['msg']);
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản Lý Tồn Kho</title>
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        /* ===== INVENTORY EXTRA STYLES ===== */
        .inv-badge-low  { background: #fee2e2; color: #dc2626; }
        .inv-badge-out  { background: #fecdd3; color: #9f1239; font-weight:700; }
        .inv-badge-ok   { background: #dcfce7; color: #166534; }

        .inv-summary-bar {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .inv-summary-card {
            flex: 1;
            min-width: 160px;
            background: #fff;
            border-radius: 10px;
            padding: 16px 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #dc2626;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .inv-summary-card .ico { font-size: 28px; }
        .inv-summary-card .lbl { font-size: 13px; color: #666; }
        .inv-summary-card .val { font-size: 22px; font-weight: 700; color: #dc2626; }

        .range-table th { background: linear-gradient(to right, #fee2e2, #ffe4e6); color: #dc2626; }
        .qty-badge {
            display: inline-block;
            min-width: 36px;
            text-align: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }
        .flash-msg {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .flash-msg.success { background:#dcfce7; color:#166534; border:1px solid #bbf7d0; }
        .flash-msg.error   { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="logo">
        <h2>Muit Store</h2>
        <p>Hệ Thống Quản Lý</p>
    </div>
    <nav class="menu">
        <a href="index.php"      class="menu-item"><span>📊</span> Tổng quan</a>
        <a href="customers.php"  class="menu-item"><span>👥</span> Quản Lý Khách Hàng</a>
        <a href="categories.php" class="menu-item"><span>📂</span> Loại Sản Phẩm</a>
        <a href="products.php"   class="menu-item"><span>💻</span> Danh Mục Sản Phẩm</a>
        <a href="import.php"     class="menu-item"><span>🚚</span> Quản Lý Nhập Hàng</a>
        <a href="price.php"      class="menu-item"><span>🏷️</span> Quản Lý Giá Bán</a>
        <a href="orders.php"     class="menu-item"><span>🛒</span> Quản Lý Đơn Hàng</a>
        <a href="inventory.php"  class="menu-item active"><span>📦</span> Quản Lý Tồn Kho</a>
    </nav>
</div>

<div class="main-content">
    <div class="top-bar">
        <h1>Quản Lý Số Lượng Tồn</h1>
        <div class="info">
            <img src="../img/logo.png" alt="Admin" />
            <span><?= htmlspecialchars($_SESSION['adminUser']['name'] ?? 'Admin') ?></span>
            <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="logout" />
                <button type="button" onclick="doLogout()">Đăng xuất</button>
            </form>
        </div>
        <a href="#popup-capnhat" class="head">+ Cập nhật tồn kho</a>
    </div>

    <div class="container">

        <?php if ($msg): ?>
        <div class="flash-msg <?= $msg['type'] ?>">
            <?= $msg['type'] === 'success' ? '✅' : '❌' ?>
            <?= htmlspecialchars($msg['text']) ?>
        </div>
        <?php endif; ?>

        <?php
        // Tính tóm tắt
        $total_products = count($products);
        $out_of_stock   = count(array_filter($products, fn($p) => $p['quantity'] == 0));
        $low_stock      = count(array_filter($products, fn($p) => $p['quantity'] > 0 && $p['quantity'] <= 5));
        $stable         = $total_products - $out_of_stock - $low_stock;
        ?>

        <!-- Tóm tắt nhanh -->
        <div class="inv-summary-bar">
            <div class="inv-summary-card">
                <span class="ico">📦</span>
                <div>
                    <div class="lbl">Tổng sản phẩm</div>
                    <div class="val"><?= $total_products ?></div>
                </div>
            </div>
            <div class="inv-summary-card">
                <span class="ico">✅</span>
                <div>
                    <div class="lbl">Ổn định</div>
                    <div class="val" style="color:#16a34a"><?= $stable ?></div>
                </div>
            </div>
            <div class="inv-summary-card">
                <span class="ico">⚠️</span>
                <div>
                    <div class="lbl">Sắp hết hàng (≤5)</div>
                    <div class="val" style="color:#b45309"><?= $low_stock ?></div>
                </div>
            </div>
            <div class="inv-summary-card">
                <span class="ico">🚫</span>
                <div>
                    <div class="lbl">Hết hàng</div>
                    <div class="val" style="color:#9f1239"><?= $out_of_stock ?></div>
                </div>
            </div>
        </div>

        <!-- Bảng tồn kho chính -->
        <div class="card">
            <h3>Tra Cứu Số Lượng Tồn Sản Phẩm</h3>

            <form method="GET" style="display:flex; gap:10px; margin-bottom:15px; flex-wrap:wrap;">
                <div style="flex:1; min-width:180px">
                    <label>Tên sản phẩm:</label>
                    <input type="text" name="search_name" class="input-field"
                           placeholder="Nhập tên..."
                           value="<?= htmlspecialchars($search_name) ?>" />
                </div>
                <div style="flex:1; min-width:180px">
                    <label>Loại sản phẩm:</label>
                    <select name="search_category" class="input-field">
                        <option value="0">Tất cả</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= $search_category == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex; align-items:end">
                    <button type="submit" class="btn-edit">Tra cứu</button>
                    <a href="inventory.php" class="disable" style="margin-left:6px">Xóa lọc</a>
                </div>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Loại</th>
                        <th>Tồn Kho Hiện Tại</th>
                        <th>Trạng Thái</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($products)): ?>
                    <tr><td colspan="6" style="text-align:center; color:#999; padding:20px">Không có sản phẩm nào.</td></tr>
                <?php endif; ?>
                <?php foreach ($products as $p):
                    $qty = intval($p['quantity']);
                    if ($qty == 0) {
                        $badge = 'status status-new'; $label = 'Hết hàng';
                    } elseif ($qty <= 5) {
                        $badge = 'status status-processing'; $label = 'Sắp hết hàng';
                    } else {
                        $badge = 'status status-completed'; $label = 'Ổn định';
                    }
                ?>
                    <tr>
                        <td>SP<?= str_pad($p['id'], 3, '0', STR_PAD_LEFT) ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                        <td><strong><?= $qty ?></strong></td>
                        <td><span class="<?= $badge ?>"><?= $label ?></span></td>
                        <td>
                            <a href="#popup-nhap"
                               onclick="setNhap(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>')"
                               class="btn-edit">Nhập thêm</a>
                            <?php if ($qty > 0): ?>
                            <a href="#popup-xuat"
                               onclick="setXuat(<?= $p['id'] ?>, '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>', <?= $qty ?>)"
                               class="unlock">Xuất hàng</a>
                            <?php endif; ?>
                            <a href="#popup-chitiet"
                               onclick="setDetail(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)"
                               class="disable">Chi tiết</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Nhập - Xuất - Tồn theo thời gian -->
        <div class="card" style="margin-top:30px">
            <h3>Tra Cứu Nhập – Xuất – Tồn Theo Khoảng Thời Gian</h3>

            <form method="GET" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:15px">
                <div style="flex:1; min-width:200px">
                    <label>Tên sản phẩm:</label>
                    <input type="text" name="range_name" class="input-field"
                           placeholder="Nhập tên..."
                           value="<?= htmlspecialchars($range_name) ?>" />
                </div>
                <div style="flex:1; min-width:150px">
                    <label>Từ ngày:</label>
                    <input type="date" name="range_from" class="input-field"
                           value="<?= htmlspecialchars($range_from) ?>" />
                </div>
                <div style="flex:1; min-width:150px">
                    <label>Đến ngày:</label>
                    <input type="date" name="range_to" class="input-field"
                           value="<?= htmlspecialchars($range_to) ?>" />
                </div>
                <div style="display:flex; align-items:end; gap:6px">
                    <button type="submit" class="btn-save" style="width:auto; padding:8px 16px">Tra cứu</button>
                    <a href="inventory.php" class="disable">Xóa</a>
                </div>
            </form>

            <?php if ($range_from && $range_to && !empty($range_rows)): ?>
            <table class="range-table" style="margin-top:16px">
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Tổng Nhập</th>
                        <th>Tổng Xuất</th>
                        <th>Tồn Hiện Tại</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($range_rows as $r): ?>
                    <tr>
                        <td>SP<?= str_pad($r['id'], 3, '0', STR_PAD_LEFT) ?></td>
                        <td><?= htmlspecialchars($r['name']) ?></td>
                        <td><span class="qty-badge" style="background:#dcfce7;color:#166534">+<?= $r['total_nhap'] ?></span></td>
                        <td><span class="qty-badge" style="background:#fee2e2;color:#dc2626">-<?= $r['total_xuat'] ?></span></td>
                        <td><strong><?= $r['ton_hien_tai'] ?></strong></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php elseif ($range_from && $range_to && empty($range_rows)): ?>
                <p style="color:#999; margin-top:12px; text-align:center">Không tìm thấy kết quả trong khoảng thời gian này.</p>
            <?php endif; ?>
        </div>

    </div><!-- /.container -->
</div><!-- /.main-content -->


<!-- ===== POPUPS ===== -->

<!-- Cập nhật tồn kho -->
<div id="popup-capnhat" class="popup-overlay">
    <div class="popup">
        <a href="inventory.php" class="close-btn">&times;</a>
        <h3>Cập nhật tồn kho</h3>
        <form method="POST">
            <input type="hidden" name="action" value="capnhat" />
            <label>Chọn sản phẩm:</label>
            <select name="product_id" class="input-field" required>
                <option value="">-- Chọn sản phẩm --</option>
                <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>">
                    SP<?= str_pad($p['id'], 3, '0', STR_PAD_LEFT) ?> – <?= htmlspecialchars($p['name']) ?> (hiện: <?= $p['quantity'] ?>)
                </option>
                <?php endforeach; ?>
            </select>
            <label>Số lượng mới:</label>
            <input type="number" name="new_qty" class="input-field" min="0" placeholder="Nhập số lượng mới..." required />
            <button type="submit" class="btn-save">Lưu</button>
        </form>
    </div>
</div>

<!-- Nhập thêm -->
<div id="popup-nhap" class="popup-overlay">
    <div class="popup">
        <a href="inventory.php" class="close-btn">&times;</a>
        <h3>Nhập hàng</h3>
        <form method="POST">
            <input type="hidden" name="action" value="nhap" />
            <input type="hidden" name="product_id" id="nhap_product_id" value="" />
            <p id="nhap_product_name" style="margin-bottom:10px; font-weight:600; color:#dc2626"></p>
            <label>Số lượng nhập thêm:</label>
            <input type="number" name="qty_add" class="input-field" min="1" placeholder="Nhập số lượng..." required />
            <button type="submit" class="btn-save">Xác nhận</button>
        </form>
    </div>
</div>

<!-- Xuất hàng -->
<div id="popup-xuat" class="popup-overlay">
    <div class="popup">
        <a href="inventory.php" class="close-btn">&times;</a>
        <h3>Xuất hàng</h3>
        <form method="POST">
            <input type="hidden" name="action" value="xuat" />
            <input type="hidden" name="product_id" id="xuat_product_id" value="" />
            <p id="xuat_product_name" style="margin-bottom:4px; font-weight:600; color:#dc2626"></p>
            <p id="xuat_current_qty" style="margin-bottom:10px; font-size:13px; color:#666"></p>
            <label>Số lượng xuất:</label>
            <input type="number" name="qty_out" id="xuat_qty_out" class="input-field" min="1" placeholder="Nhập số lượng..." required />
            <button type="submit" class="btn-save">Xác nhận</button>
        </form>
    </div>
</div>

<!-- Chi tiết -->
<div id="popup-chitiet" class="popup-overlay">
    <div class="popup">
        <a href="inventory.php" class="close-btn">&times;</a>
        <h3>Chi tiết sản phẩm</h3>
        <div id="detail-content">
            <p><strong>Tên:</strong> <span id="d-name"></span></p>
            <p><strong>Mã:</strong> <span id="d-id"></span></p>
            <p><strong>Loại:</strong> <span id="d-cat"></span></p>
            <p><strong>Tồn kho:</strong> <span id="d-qty"></span> chiếc</p>
            <p><strong>Trạng thái:</strong> <span id="d-status"></span></p>
            <p><strong>CPU:</strong> <span id="d-cpu"></span></p>
            <p><strong>RAM:</strong> <span id="d-ram"></span></p>
            <p><strong>Ổ cứng:</strong> <span id="d-storage"></span></p>
            <p><strong>GPU:</strong> <span id="d-gpu"></span></p>
        </div>
    </div>
</div>

<script>
    function doLogout() {
        <?php
        // PHP logout inline
        ?>
        window.location.href = 'admin-login.php?logout=1';
    }

    function setNhap(id, name) {
        document.getElementById('nhap_product_id').value = id;
        document.getElementById('nhap_product_name').textContent = '📦 ' + name;
    }

    function setXuat(id, name, qty) {
        document.getElementById('xuat_product_id').value = id;
        document.getElementById('xuat_product_name').textContent = '📤 ' + name;
        document.getElementById('xuat_current_qty').textContent = 'Tồn kho hiện tại: ' + qty + ' chiếc';
        document.getElementById('xuat_qty_out').max = qty;
    }

    function setDetail(p) {
        document.getElementById('d-name').textContent    = p.name ?? '—';
        document.getElementById('d-id').textContent      = 'SP' + String(p.id).padStart(3, '0');
        document.getElementById('d-cat').textContent     = p.category_name ?? '—';
        document.getElementById('d-qty').textContent     = p.quantity;
        document.getElementById('d-cpu').textContent     = p.cpu ?? '—';
        document.getElementById('d-ram').textContent     = p.ram ?? '—';
        document.getElementById('d-storage').textContent = p.storage ?? '—';
        document.getElementById('d-gpu').textContent     = p.gpu ?? '—';

        const qty = parseInt(p.quantity);
        let statusText = 'Ổn định';
        if (qty === 0) statusText = '🔴 Hết hàng';
        else if (qty <= 5) statusText = '🟡 Sắp hết hàng';
        else statusText = '🟢 Ổn định';
        document.getElementById('d-status').textContent = statusText;
    }
</script>

</body>
</html>