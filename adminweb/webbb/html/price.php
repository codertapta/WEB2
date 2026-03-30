<?php
session_start();
if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once(__DIR__ . '/../../../config.php');

$message     = '';
$messageType = '';

// ─── Hàm tính giá vốn bình quân của một sản phẩm ────────────────────────────
// Công thức: (tồn_hiện_tại * giá_vốn_hiện_tại + tổng_nhập_mới * giá_nhập_mới) / (tồn + nhập)
// Ở đây ta tính lại từ toàn bộ lịch sử nhập theo thứ tự thời gian (rolling average)
function tinhGiaVonBinhQuan(int $productId, $conn): float {
    // Lấy tất cả lần nhập theo thứ tự thời gian, chỉ lấy đơn nhập đã hoàn thành
    $sql = "
        SELECT id_sub.quantity, id_sub.cost_price
        FROM   import_details id_sub
        JOIN   import_orders  io ON io.id = id_sub.import_order_id
        WHERE  id_sub.product_id = ?
        AND    io.status = 1
        ORDER  BY io.import_date ASC, io.id ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $productId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($rows)) return 0.0;

    // Lấy số lượng đã bán (đơn không cancelled)
    $soldSql = "
        SELECT COALESCE(SUM(od.quantity), 0) AS sold
        FROM   order_details od
        JOIN   orders o ON o.id = od.order_id
        WHERE  od.product_id = ?
        AND    o.status != 'cancelled'
    ";
    $stmt2 = $conn->prepare($soldSql);
    $stmt2->bind_param('i', $productId);
    $stmt2->execute();
    $sold = (int)$stmt2->get_result()->fetch_assoc()['sold'];

    // Rolling weighted average
    $tonKho   = 0;
    $giaVon   = 0.0;

    foreach ($rows as $r) {
        $qtyNhap  = intval($r['quantity']);
        $giaNhap  = floatval($r['cost_price']);

        if ($tonKho + $qtyNhap == 0) continue;

        $giaVon  = ($tonKho * $giaVon + $qtyNhap * $giaNhap) / ($tonKho + $qtyNhap);
        $tonKho += $qtyNhap;

        // Trừ số đã bán (giả sử bán trải đều — giá vốn không đổi khi bán theo BQGQ)
        // Thực ra khi bán thì tonKho giảm nhưng giaVon không đổi
    }

    // Sau khi nhập xong, trừ số đã bán để giữ đúng tồn
    // Giá vốn bình quân không thay đổi khi bán
    return $giaVon;
}

// ─── Xử lý POST: cập nhật tỷ lệ lợi nhuận & giá bán ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action']     ?? '';
    $productId  = intval($_POST['product_id']  ?? 0);
    $profitRate = floatval($_POST['profit_rate'] ?? 0);

    if ($action === 'update' && $productId > 0) {
        // Tính lại giá vốn bình quân mới nhất
        $giaVonBQ = tinhGiaVonBinhQuan($productId, $conn);

        if ($giaVonBQ > 0) {
            // Giá bán = giá vốn * (100% + tỷ lệ lợi nhuận%)
            $newPrice = round($giaVonBQ * (1 + $profitRate / 100));

            $stmt = $conn->prepare("
                UPDATE products
                SET    profit_rate = ?,
                       cost_price  = ?,
                       price       = ?
                WHERE  id = ?
            ");
            $costInt = round($giaVonBQ);
            $stmt->bind_param('diii', $profitRate, $costInt, $newPrice, $productId);
            $stmt->execute();

            $message     = 'Cập nhật giá bán thành công!';
            $messageType = 'success';
        } else {
            $message     = 'Sản phẩm chưa có lịch sử nhập hàng — không thể tính giá vốn!';
            $messageType = 'error';
        }
    }
}

// ─── Lấy sản phẩm đang sửa (popup) ─────────────────────────────────────────
$editId      = intval($_GET['edit'] ?? 0);
$editProduct = null;
if ($editId > 0) {
    $stmt = $conn->prepare("
        SELECT p.*, c.name AS category_name
        FROM   products p
        JOIN   categories c ON c.id = p.category_id
        WHERE  p.id = ?
    ");
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editProduct = $stmt->get_result()->fetch_assoc();

    // Tính giá vốn bình quân live (để hiển thị giá trị mới nhất)
    if ($editProduct) {
        $editProduct['cost_price_bq'] = tinhGiaVonBinhQuan($editId, $conn);
    }
}

// ─── Tìm kiếm & danh sách ────────────────────────────────────────────────────
$search = $conn->real_escape_string(trim($_GET['search'] ?? ''));
$catId  = intval($_GET['category_id'] ?? 0);

$where = "1=1";
if ($search !== '') $where .= " AND (p.name LIKE '%$search%' OR p.id LIKE '%$search%')";
if ($catId  >  0)  $where .= " AND p.category_id = $catId";

$products = $conn->query("
    SELECT p.*, c.name AS category_name
    FROM   products p
    JOIN   categories c ON c.id = p.category_id
    WHERE  $where
    ORDER  BY p.id ASC
");

$categories = $conn->query("SELECT * FROM categories WHERE status = 1 ORDER BY name");

// Thống kê
$statsRow      = $conn->query("
    SELECT
        COUNT(*) AS total,
        ROUND(AVG(CASE WHEN profit_rate > 0 THEN profit_rate END), 1) AS avg_rate,
        SUM(CASE WHEN cost_price > 0 THEN 1 ELSE 0 END) AS has_cost
    FROM products WHERE status = 1
")->fetch_assoc();

$totalProducts = $statsRow['total']    ?? 0;
$avgProfitRate = $statsRow['avg_rate'] ?? 0;
$hasCostCount  = $statsRow['has_cost'] ?? 0;
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản Lý Giá Bán</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>

<!-- ── SIDEBAR ───────────────────────────────────────────────────────────── -->
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
        <a href="price.php"      class="menu-item active"><span>🏷️</span> Quản Lý Giá Bán</a>
        <a href="orders.php"     class="menu-item"><span>🛒</span> Quản Lý Đơn Hàng</a>
        <a href="inventory.php"  class="menu-item"><span>📦</span> Quản Lý Tồn Kho</a>
    </nav>
</div>

<!-- ── MAIN ──────────────────────────────────────────────────────────────── -->
<div class="main-content">
    <div class="top-bar">
        <h1>Quản Lý Giá Bán Sản Phẩm</h1>
        <div class="info">
            <img src="../img/logo.png" alt="Admin" />
            <span><?= htmlspecialchars($_SESSION['adminUser']['name'] ?? 'Admin') ?></span>
            <button onclick="window.location.href='logout.php'">Đăng xuất</button>
        </div>
    </div>

    <div class="container">

        <!-- Flash message -->
        <?php if ($message): ?>
        <div class="flash-msg <?= $messageType ?>">
            <?= $messageType === 'success' ? '✅' : '❌' ?>
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <!-- Thống kê -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">💻</div>
                <div class="stat-info">
                    <h3>Tổng Sản Phẩm</h3>
                    <p class="stat-number"><?= $totalProducts ?></p>
                    <p class="stat-change">Đang kinh doanh</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-info">
                    <h3>Tỷ Lệ LN Trung Bình</h3>
                    <p class="stat-number"><?= $avgProfitRate ?>%</p>
                    <p class="stat-change">Sản phẩm đã đặt giá</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🏷️</div>
                <div class="stat-info">
                    <h3>Đã Có Giá Vốn</h3>
                    <p class="stat-number"><?= $hasCostCount ?></p>
                    <p class="stat-change positive">Sẵn sàng đặt giá bán</p>
                </div>
            </div>
        </div>

        <!-- Tìm kiếm -->
        <div class="card" style="margin-top:20px">
            <div class="title"><h3>Tìm kiếm sản phẩm</h3></div>
            <form method="GET" action="price.php" class="price-search">
                <input type="text" name="search" class="input-field"
                       placeholder="🔍 Tên hoặc mã sản phẩm..."
                       value="<?= htmlspecialchars($search) ?>" />
                <select name="category_id" class="input-field">
                    <option value="">-- Tất cả loại --</option>
                    <?php $categories->data_seek(0); while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $catId ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" class="btn-search">Tìm kiếm</button>
                <a href="price.php" class="btn-reset" style="text-decoration:none; padding:6px 10px">Làm mới</a>
            </form>
        </div>

        <!-- Bảng giá -->
        <div class="card" style="margin-top:20px">
            <h3>Danh Sách Giá Bán</h3>
            <table>
                <thead>
                    <tr>
                        <th>Mã SP</th>
                        <th>Loại</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Giá Vốn BQ</th>
                        <th>Tỷ Lệ LN</th>
                        <th>Giá Bán</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($products && $products->num_rows > 0): ?>
                    <?php while ($p = $products->fetch_assoc()):
                        // Tính giá vốn bình quân live cho từng sản phẩm
                        $giaVonBQ = tinhGiaVonBinhQuan((int)$p['id'], $conn);
                        $hasImport = $giaVonBQ > 0;
                    ?>
                    <tr>
                        <td>SP<?= str_pad($p['id'], 3, '0', STR_PAD_LEFT) ?></td>
                        <td><?= htmlspecialchars($p['category_name']) ?></td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td>
                            <?php if ($hasImport): ?>
                                <strong><?= number_format($giaVonBQ, 0, ',', '.') ?>đ</strong>
                                <div class="cost-note">Bình quân gia quyền</div>
                            <?php else: ?>
                                <span style="color:#aaa">Chưa nhập hàng</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['profit_rate'] > 0): ?>
                                <span class="profit-badge"><?= $p['profit_rate'] ?>%</span>
                            <?php else: ?>
                                <span class="profit-badge zero">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['price'] > 0): ?>
                                <strong style="color:#dc2626">
                                    <?= number_format($p['price'], 0, ',', '.') ?>đ
                                </strong>
                            <?php else: ?>
                                <span style="color:#aaa">Chưa đặt giá</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($hasImport): ?>
                                <a href="price.php?edit=<?= $p['id'] ?>&search=<?= urlencode($search) ?>&category_id=<?= $catId ?>#popup-price"
                                   class="btn-edit">Cập nhật giá</a>
                            <?php else: ?>
                                <button class="disable" disabled title="Cần nhập hàng trước">Cập nhật giá</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center; color:#999; padding:20px">
                            Không tìm thấy sản phẩm nào.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /.container -->
</div><!-- /.main-content -->


<!-- ══════════════════════════════════════════════════════
     POPUP CẬP NHẬT GIÁ BÁN
══════════════════════════════════════════════════════ -->
<div id="popup-price" class="popup-overlay">
    <div class="popup">
        <a href="price.php?search=<?= urlencode($search) ?>&category_id=<?= $catId ?>"
           class="close-btn">&times;</a>
        <h3 style="color:#dc2626; margin-bottom:16px">Cập Nhật Giá Bán</h3>

        <?php if ($editProduct): ?>
        <?php
            $costBQ  = $editProduct['cost_price_bq'];
            $curRate = floatval($editProduct['profit_rate']);
        ?>
        <form method="POST" action="price.php">
            <input type="hidden" name="action"     value="update" />
            <input type="hidden" name="product_id" value="<?= $editProduct['id'] ?>" />

            <label>Loại sản phẩm:</label>
            <input type="text" class="input-field"
                   value="<?= htmlspecialchars($editProduct['category_name']) ?>" readonly />

            <label>Tên sản phẩm:</label>
            <input type="text" class="input-field"
                   value="<?= htmlspecialchars($editProduct['name']) ?>" readonly />

            <label>Giá vốn bình quân:</label>
            <input type="text" class="input-field" id="popupCostPrice" readonly
                   value="<?= number_format($costBQ, 0, ',', '.') ?>đ" />
            <div class="cost-note" style="margin-bottom:10px">
                Tính theo phương pháp bình quân gia quyền từ lịch sử nhập hàng
            </div>

            <label>% Lợi nhuận:</label>
            <input type="number" name="profit_rate" id="popupProfitRate"
                   class="input-field"
                   placeholder="Nhập tỷ lệ % (VD: 20)"
                   min="0" max="1000" step="0.1" required
                   value="<?= $curRate ?>" />

            <label>Giá bán dự kiến:</label>
            <div class="price-preview-box">
                <span class="preview-label">💰 Giá bán =</span>
                <span class="preview-value" id="popupPricePreview">
                    <?php
                    if ($curRate > 0 && $costBQ > 0) {
                        echo number_format(round($costBQ * (1 + $curRate / 100)), 0, ',', '.') . 'đ';
                    } else {
                        echo '—';
                    }
                    ?>
                </span>
            </div>
            <div class="cost-note" style="margin-bottom:14px">
                Giá bán = Giá vốn BQ × (100% + Tỷ lệ lợi nhuận%)
            </div>

            <button type="submit" class="btn-save">💾 Lưu Thông Tin</button>
        </form>

        <script>
            (function () {
                const costPrice   = <?= round($costBQ, 4) ?>;
                const profitInput = document.getElementById('popupProfitRate');
                const preview     = document.getElementById('popupPricePreview');

                function calcPrice() {
                    const rate = parseFloat(profitInput.value);
                    if (isNaN(rate) || costPrice <= 0) {
                        preview.textContent = '—';
                        return;
                    }
                    const sell = Math.round(costPrice * (1 + rate / 100));
                    preview.textContent = sell.toLocaleString('vi-VN') + 'đ';
                }

                profitInput.addEventListener('input', calcPrice);
                calcPrice();
            })();
        </script>

        <?php else: ?>
        <div style="text-align:center; padding:30px 0; color:#888">
            <div style="font-size:40px; margin-bottom:12px">🏷️</div>
            <p>Chọn sản phẩm cần cập nhật giá từ danh sách.</p>
            <p style="margin-top:6px; font-size:13px">
                <em>Chỉ sản phẩm đã nhập hàng mới có thể cập nhật giá bán.</em>
            </p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($editProduct): ?>
<script>
    // Tự mở popup khi đang ở chế độ sửa
    window.location.hash = 'popup-price';
</script>
<?php endif; ?>

</body>
</html>