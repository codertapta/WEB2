<?php
session_start();
if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once(__DIR__ . '/../../../config.php');

$message = '';
$messageType = '';

// ─── Xử lý POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Thêm hoặc sửa phiếu nhập
    if ($action === 'save') {
        $id          = intval($_POST['id'] ?? 0);
        $importDate  = $conn->real_escape_string($_POST['import_date'] ?? '');
        $importer    = $conn->real_escape_string(trim($_POST['importer'] ?? ''));
        $productIds  = $_POST['product_id']  ?? [];
        $costPrices  = $_POST['cost_price']  ?? [];
        $quantities  = $_POST['quantity']    ?? [];

        if (empty($importDate) || empty($importer) || empty($productIds)) {
            $message = 'Vui lòng điền đầy đủ thông tin và ít nhất một sản phẩm!';
            $messageType = 'error';
        } else {
            if ($id > 0) {
                // Sửa — chỉ cho phép khi chưa hoàn thành
                $check = $conn->query("SELECT status FROM import_orders WHERE id=$id");
                $row   = $check->fetch_assoc();
                if ($row && $row['status'] == 0) {
                    $conn->query("UPDATE import_orders SET import_date='$importDate', importer='$importer' WHERE id=$id");
                    $conn->query("DELETE FROM import_details WHERE import_order_id=$id");
                } else {
                    $id = 0; // fallback: tạo mới nếu không tìm thấy
                }
            }
            if ($id === 0) {
                $conn->query("INSERT INTO import_orders (import_date, importer) VALUES ('$importDate', '$importer')");
                $id = $conn->insert_id;
            }

            foreach ($productIds as $i => $pid) {
                $pid   = intval($pid);
                $price = intval($costPrices[$i] ?? 0);
                $qty   = intval($quantities[$i]  ?? 0);
                if ($pid > 0 && $qty > 0) {
                    $conn->query("INSERT INTO import_details (import_order_id, product_id, cost_price, quantity)
                                  VALUES ($id, $pid, $price, $qty)");
                }
            }
            $message = 'Lưu phiếu nhập thành công!';
            $messageType = 'success';
        }
    }

    // Hoàn thành phiếu → cập nhật tồn kho & giá vốn sản phẩm
    if ($action === 'complete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $details = $conn->query("SELECT * FROM import_details WHERE import_order_id=$id");
            while ($d = $details->fetch_assoc()) {
                $pid   = $d['product_id'];
                $qty   = $d['quantity'];
                $price = $d['cost_price'];
                $conn->query("UPDATE products SET quantity = quantity + $qty, cost_price = $price WHERE id = $pid");
            }
            $conn->query("UPDATE import_orders SET status=1 WHERE id=$id");
            $message = 'Phiếu nhập đã được hoàn thành và tồn kho đã được cập nhật!';
            $messageType = 'success';
        }
    }
}

// ─── Lấy dữ liệu ─────────────────────────────────────────────────────────────
$search     = $conn->real_escape_string(trim($_GET['search'] ?? ''));
$searchDate = $conn->real_escape_string(trim($_GET['date']   ?? ''));
$editId     = intval($_GET['edit'] ?? 0);

$where = "1=1";
if ($search !== '')     $where .= " AND io.id LIKE '%$search%'";
if ($searchDate !== '') $where .= " AND io.import_date = '$searchDate'";

$importOrders = $conn->query("
    SELECT io.*,
           COUNT(id2.id)        AS product_count,
           SUM(id2.cost_price * id2.quantity) AS total_value
    FROM   import_orders io
    LEFT JOIN import_details id2 ON id2.import_order_id = io.id
    WHERE  $where
    GROUP BY io.id
    ORDER BY io.created_at DESC
");

$totalOrders = $conn->query("SELECT COUNT(*) AS cnt FROM import_orders")->fetch_assoc()['cnt'] ?? 0;
$totalValue  = $conn->query("
    SELECT SUM(d.cost_price * d.quantity) AS total
    FROM   import_details d
    JOIN   import_orders o ON o.id = d.import_order_id
    WHERE  o.status = 1
")->fetch_assoc()['total'] ?? 0;

$products = $conn->query("SELECT id, name, cost_price FROM products WHERE status=1 ORDER BY name");

// Dữ liệu phiếu đang sửa
$editOrder   = null;
$editDetails = [];
if ($editId > 0) {
    $r = $conn->query("SELECT * FROM import_orders WHERE id=$editId AND status=0");
    if ($r && $r->num_rows > 0) {
        $editOrder = $r->fetch_assoc();
        $dr = $conn->query("SELECT id2.*, p.name AS product_name FROM import_details id2
                            JOIN products p ON p.id = id2.product_id
                            WHERE id2.import_order_id=$editId");
        while ($d = $dr->fetch_assoc()) $editDetails[] = $d;
    }
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Quản Lý Nhập Hàng</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body style="margin:0">

<!-- ── SIDEBAR ─────────────────────────────────────────── -->
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
    <a href="import.php"     class="menu-item active"><span>🚚</span> Quản Lý Nhập Hàng</a>
    <a href="price.php"      class="menu-item"><span>🏷️</span> Quản Lý Giá Bán</a>
    <a href="orders.php"     class="menu-item"><span>🛒</span> Quản Lý Đơn Hàng</a>
    <a href="inventory.php"  class="menu-item"><span>📦</span> Quản Lý Tồn Kho</a>
  </nav>
</div>

<!-- ── MAIN ────────────────────────────────────────────── -->
<div class="main-content">
  <div class="top-bar">
    <h1>Quản Lý Phiếu Nhập Hàng</h1>
    <div class="info">
      <img src="../img/logo.png" alt="Admin" />
      <span>Admin</span>
      <button onclick="logout()">Đăng xuất</button>
    </div>
    <a href="#popupForm" class="head">+ Thêm Phiếu Nhập</a>
  </div>

  <div class="container">

    <?php if ($message): ?>
    <div class="msg <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Thống kê -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📄</div>
        <div class="stat-info">
          <h3>Tổng Phiếu Nhập</h3>
          <p class="stat-number"><?= $totalOrders ?></p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💸</div>
        <div class="stat-info">
          <h3>Tổng Giá Trị Nhập (Hoàn thành)</h3>
          <p class="stat-number"><?= number_format($totalValue, 0, ',', '.') ?>đ</p>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Danh sách -->
      <div class="col-8">
        <div class="card">
          <h3>Danh Sách Phiếu Nhập</h3>

          <form method="GET" action="import.php" style="display:flex;gap:10px;margin-bottom:15px">
            <input type="text"  name="search" class="input-field"
                   placeholder="Tìm theo Mã Phiếu..." value="<?= htmlspecialchars($search) ?>"/>
            <input type="date"  name="date"   class="input-field" value="<?= htmlspecialchars($searchDate) ?>"/>
            <button type="submit" class="btn-search">Tìm kiếm</button>
            <a href="import.php" class="btn-reset" style="text-decoration:none;padding:8px 14px">Làm mới</a>
          </form>

          <table>
            <thead>
              <tr>
                <th>Mã Phiếu</th>
                <th>Ngày Nhập</th>
                <th>Người Nhập</th>
                <th>Số SP</th>
                <th>Giá Trị</th>
                <th>Trạng Thái</th>
                <th>Thao Tác</th>
              </tr>
            </thead>
            <tbody>
            <?php if ($importOrders && $importOrders->num_rows > 0): ?>
              <?php while ($order = $importOrders->fetch_assoc()): ?>
              <tr>
                <td>PN<?= str_pad($order['id'], 3, '0', STR_PAD_LEFT) ?></td>
                <td><?= date('d/m/Y', strtotime($order['import_date'])) ?></td>
                <td><?= htmlspecialchars($order['importer']) ?></td>
                <td><?= $order['product_count'] ?></td>
                <td><?= number_format($order['total_value'] ?? 0, 0, ',', '.') ?>đ</td>
                <td>
                  <?php if ($order['status'] == 1): ?>
                    <span class="status status-completed">Hoàn thành</span>
                  <?php else: ?>
                    <span class="status status-processing">Chưa hoàn thành</span>
                  <?php endif; ?>
                </td>
                <td style="white-space:nowrap">
                  <!-- Xem chi tiết -->
                  <a href="#detail-<?= $order['id'] ?>" class="btn-search">Xem chi tiết</a>

                  <?php if ($order['status'] == 0): ?>
                    <!-- Sửa -->
                    <a href="import.php?edit=<?= $order['id'] ?>#popupForm" class="btn-edit">Sửa</a>

                    <!-- Hoàn thành -->
                    <form method="POST" action="import.php" style="display:inline"
                          onsubmit="return confirm('Xác nhận hoàn thành phiếu này? Tồn kho sẽ được cập nhật.')">
                      <input type="hidden" name="action" value="complete"/>
                      <input type="hidden" name="id"     value="<?= $order['id'] ?>"/>
                      <button type="submit" class="complete">Hoàn thành</button>
                    </form>
                  <?php else: ?>
                    <button class="disable" disabled>Sửa</button>
                    <button class="disable" disabled>Hoàn thành</button>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="7" style="text-align:center">Không có phiếu nhập nào.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Hướng dẫn -->
      <div class="col-4">
        <div class="card">
          <h3>Hướng Dẫn</h3>
          <p>
            Nhấn vào <strong>+ Thêm Phiếu Nhập</strong> để tạo phiếu nhập mới.<br/><br/>
            Bấm <strong>Xem chi tiết</strong> để xem danh sách sản phẩm trong phiếu.<br/><br/>
            Nhấn <strong>Hoàn thành</strong> để xác nhận nhập hàng — tồn kho sẽ tự động được cập nhật và không thể chỉnh sửa sau đó.
          </p>
        </div>
      </div>
    </div>
  </div><!-- /container -->
</div><!-- /main-content -->

<!-- ══════════════════════════════════════════════════════
     POPUP THÊM / SỬA PHIẾU NHẬP
══════════════════════════════════════════════════════ -->
<div id="popupForm" class="popup-overlay">
  <div class="popup">
    <a href="import.php" class="close-btn">✖</a>
    <h3><?= $editOrder ? 'Sửa Phiếu Nhập' : 'Thêm Phiếu Nhập' ?></h3>

    <form method="POST" action="import.php">
      <input type="hidden" name="action" value="save"/>
      <input type="hidden" name="id"     value="<?= $editOrder['id'] ?? 0 ?>"/>

      <label>Ngày nhập</label>
      <input type="date" name="import_date" class="input-field" required
             value="<?= $editOrder['import_date'] ?? date('Y-m-d') ?>"/>

      <label>Người nhập</label>
      <input type="text" name="importer" class="input-field" placeholder="Tên người nhập" required
             value="<?= htmlspecialchars($editOrder['importer'] ?? '') ?>"/>

      <label>Danh sách sản phẩm</label>
      <div id="product-rows">
        <?php if (!empty($editDetails)): ?>
          <?php foreach ($editDetails as $d): ?>
          <div class="product-row">
            <select name="product_id[]" class="input-field" required>
              <option value="">-- Chọn sản phẩm --</option>
              <?php
              $products->data_seek(0);
              while ($p = $products->fetch_assoc()):
              ?>
              <option value="<?= $p['id'] ?>"
                <?= $p['id'] == $d['product_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($p['name']) ?>
              </option>
              <?php endwhile; ?>
            </select>
            <input type="number" name="cost_price[]" class="input-field"
                   placeholder="Giá nhập (đ)" min="0" required value="<?= $d['cost_price'] ?>"/>
            <input type="number" name="quantity[]"   class="input-field"
                   placeholder="Số lượng" min="1" required value="<?= $d['quantity'] ?>"/>
            <button type="button" class="btn-remove" onclick="removeRow(this)">✕</button>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="product-row">
            <select name="product_id[]" class="input-field" required>
              <option value="">-- Chọn sản phẩm --</option>
              <?php $products->data_seek(0); while ($p = $products->fetch_assoc()): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endwhile; ?>
            </select>
            <input type="number" name="cost_price[]" class="input-field" placeholder="Giá nhập (đ)" min="0" required/>
            <input type="number" name="quantity[]"   class="input-field" placeholder="Số lượng"    min="1" required/>
            <button type="button" class="btn-remove" onclick="removeRow(this)">✕</button>
          </div>
        <?php endif; ?>
      </div>

      <button type="button" id="btn-add-row">+ Thêm sản phẩm</button>

      <button type="submit" class="btn-save">Lưu Phiếu Nhập</button>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════════════════════
     POPUP CHI TIẾT — render động cho từng phiếu
══════════════════════════════════════════════════════ -->
<?php
// Reset lại cursor để lặp lại import_orders
$importOrders2 = $conn->query("
    SELECT io.*,
           COUNT(id2.id)                     AS product_count,
           SUM(id2.cost_price * id2.quantity) AS total_value
    FROM   import_orders io
    LEFT JOIN import_details id2 ON id2.import_order_id = io.id
    GROUP BY io.id
    ORDER BY io.created_at DESC
");
while ($order = $importOrders2->fetch_assoc()):
    $oid     = $order['id'];
    $details = $conn->query("
        SELECT id2.*, p.name AS product_name
        FROM   import_details id2
        JOIN   products p ON p.id = id2.product_id
        WHERE  id2.import_order_id = $oid
    ");
    $totalQty   = 0;
    $totalPrice = 0;
    $rows = [];
    while ($d = $details->fetch_assoc()) {
        $totalQty   += $d['quantity'];
        $totalPrice += $d['cost_price'] * $d['quantity'];
        $rows[]      = $d;
    }
?>
<div id="detail-<?= $oid ?>" class="popup-overlay">
  <div class="popup">
    <a href="import.php<?= $search || $searchDate ? '?search='.urlencode($search).'&date='.urlencode($searchDate) : '' ?>" class="close-btn">✖</a>
    <h2>Chi Tiết Phiếu Nhập</h2>
    <div class="detail-info" style="margin-bottom:20px">
      <p><strong>Mã phiếu:</strong> PN<?= str_pad($oid, 3, '0', STR_PAD_LEFT) ?></p>
      <p><strong>Ngày nhập:</strong> <?= date('d/m/Y', strtotime($order['import_date'])) ?></p>
      <p><strong>Người nhập:</strong> <?= htmlspecialchars($order['importer']) ?></p>
      <p><strong>Trạng thái:</strong>
        <?= $order['status'] == 1 ? '<span class="status status-completed">Hoàn thành</span>' : '<span class="status status-processing">Chưa hoàn thành</span>' ?>
      </p>
    </div>
    <h3>Danh sách sản phẩm</h3>
    <table style="margin-bottom:20px">
      <thead>
        <tr>
          <th>STT</th>
          <th>Tên Sản Phẩm</th>
          <th>Giá Nhập</th>
          <th>Số Lượng</th>
          <th>Thành Tiền</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
        <tr><td colspan="5" style="text-align:center">Chưa có sản phẩm.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $i => $d): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($d['product_name']) ?></td>
            <td><?= number_format($d['cost_price'], 0, ',', '.') ?>đ</td>
            <td><?= $d['quantity'] ?></td>
            <td><?= number_format($d['cost_price'] * $d['quantity'], 0, ',', '.') ?>đ</td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
    <h3>Tổng Kết</h3>
    <div class="total-box">
      <p>Tổng loại sản phẩm: <strong><?= count($rows) ?></strong></p>
      <p>Tổng số lượng: <strong><?= $totalQty ?></strong></p>
      <p>Tổng tiền: <strong><?= number_format($totalPrice, 0, ',', '.') ?>đ</strong></p>
    </div>
  </div>
</div>
<?php endwhile; ?>

<script>
  // ── Template cho hàng sản phẩm mới ──────────────────────────────────────────
  const productOptions = `<?php
    $products->data_seek(0);
    $opts = '<option value="">-- Chọn sản phẩm --</option>';
    while ($p = $products->fetch_assoc()) {
        $opts .= '<option value="'.$p['id'].'">'.htmlspecialchars($p['name']).'</option>';
    }
    echo addslashes($opts);
  ?>`;

  document.getElementById('btn-add-row').addEventListener('click', function () {
    const row = document.createElement('div');
    row.className = 'product-row';
    row.innerHTML = `
      <select name="product_id[]" class="input-field" required>
        ${productOptions}
      </select>
      <input type="number" name="cost_price[]" class="input-field" placeholder="Giá nhập (đ)" min="0" required/>
      <input type="number" name="quantity[]"   class="input-field" placeholder="Số lượng"    min="1" required/>
      <button type="button" class="btn-remove" onclick="removeRow(this)">✕</button>
    `;
    document.getElementById('product-rows').appendChild(row);
  });

  function removeRow(btn) {
    const rows = document.querySelectorAll('#product-rows .product-row');
    if (rows.length > 1) btn.closest('.product-row').remove();
    else alert('Phiếu nhập phải có ít nhất một sản phẩm!');
  }

  // Nếu đang sửa thì tự mở popup
  <?php if ($editOrder): ?>
  window.location.hash = 'popupForm';
  <?php endif; ?>

  function logout() {
    window.location.href = 'logout.php';
  }
</script>
</body>
</html>