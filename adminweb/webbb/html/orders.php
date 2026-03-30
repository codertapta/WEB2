<?php
session_start();
if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
    header('Location: admin-login.php');
    exit;
}

require_once(__DIR__ . '/../../../config.php');

$message     = '';
$messageType = '';

// ─── Xử lý POST ──────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $orderId = intval($_POST['order_id'] ?? 0);

    // Cập nhật trạng thái đơn hàng
    if ($action === 'update_status' && $orderId > 0) {
        $status = $conn->real_escape_string($_POST['status'] ?? '');
        $allowed = ['pending', 'processing', 'shipping', 'delivered', 'cancelled'];
        if (in_array($status, $allowed)) {
            $conn->query("UPDATE orders SET status='$status' WHERE id=$orderId");
            $message     = 'Cập nhật trạng thái đơn hàng thành công!';
            $messageType = 'success';
        }
    }

    // Thêm đơn hàng mới
    if ($action === 'add') {
        $userId     = intval($_POST['user_id'] ?? 0);
        $totalPrice = intval($_POST['total_price'] ?? 0);
        $status     = $conn->real_escape_string($_POST['status'] ?? 'pending');
        $orderDate  = date('Y-m-d H:i:s');

        if ($userId > 0 && $totalPrice > 0) {
            $conn->query("INSERT INTO orders (user_id, order_date, total_price, status)
                          VALUES ($userId, '$orderDate', $totalPrice, '$status')");
            $message     = 'Thêm đơn hàng thành công!';
            $messageType = 'success';
        } else {
            $message     = 'Vui lòng điền đầy đủ thông tin!';
            $messageType = 'error';
        }
    }
}

// ─── Bộ lọc & tìm kiếm ───────────────────────────────────────────────────────
$dateFrom  = $conn->real_escape_string($_GET['date_from'] ?? '');
$dateTo    = $conn->real_escape_string($_GET['date_to']   ?? '');
$filterStatus = $conn->real_escape_string($_GET['status'] ?? '');

$where = "1=1";
if ($dateFrom !== '')     $where .= " AND DATE(o.order_date) >= '$dateFrom'";
if ($dateTo   !== '')     $where .= " AND DATE(o.order_date) <= '$dateTo'";
if ($filterStatus !== '') $where .= " AND o.status = '$filterStatus'";

$orders = $conn->query("
    SELECT o.*,
           CONCAT(u.ho, ' ', u.ten) AS customer_name,
           u.sdt, u.email
    FROM   orders o
    LEFT JOIN users u ON u.id = o.user_id
    WHERE  $where
    ORDER BY o.order_date DESC
");

// Thống kê
$stats = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status='delivered' THEN 1 ELSE 0 END) AS delivered,
        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS cancelled,
        SUM(CASE WHEN status='delivered' THEN total_price ELSE 0 END) AS revenue
    FROM orders
")->fetch_assoc();

$users = $conn->query("SELECT id, ho, ten FROM users WHERE status='active' ORDER BY ho");

// Map trạng thái → nhãn tiếng Việt & class CSS
$statusMap = [
    'pending'   => ['label' => 'Chờ xác nhận', 'class' => 'status-new'],
    'processing'=> ['label' => 'Đang xử lý',   'class' => 'status-processing'],
    'shipping'  => ['label' => 'Đang giao',     'class' => 'status-processing'],
    'delivered' => ['label' => 'Đã giao',       'class' => 'status-completed'],
    'cancelled' => ['label' => 'Đã hủy',        'class' => 'status-hidden'],
];
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Quản Lý Đơn Hàng</title>
  <link rel="stylesheet" href="../css/style.css"/>
</head>
<body>

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
    <a href="import.php"     class="menu-item"><span>🚚</span> Quản Lý Nhập Hàng</a>
    <a href="price.php"      class="menu-item"><span>🏷️</span> Quản Lý Giá Bán</a>
    <a href="orders.php"     class="menu-item active"><span>🛒</span> Quản Lý Đơn Hàng</a>
    <a href="inventory.php"  class="menu-item"><span>📦</span> Quản Lý Tồn Kho</a>
  </nav>
</div>

<!-- ── MAIN ────────────────────────────────────────────── -->
<div class="main-content">
  <div class="top-bar">
    <h1>Quản Lý Đơn Hàng</h1>
    <div class="info">
      <img src="../img/logo.png" alt="Admin"/>
      <span><?= htmlspecialchars($_SESSION['adminUser']['name'] ?? 'Admin') ?></span>
      <button onclick="window.location.href='admin-logout.php'">Đăng xuất</button>
    </div>
    <a href="#popup-add" class="head">+ Thêm đơn</a>
  </div>

  <div class="container">

    <?php if ($message): ?>
    <div class="msg <?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Thống kê -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">🛒</div>
        <div class="stat-info">
          <h3>Tổng Đơn Hàng</h3>
          <p class="stat-number"><?= $stats['total'] ?></p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
          <h3>Chờ Xác Nhận</h3>
          <p class="stat-number"><?= $stats['pending'] ?></p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
          <h3>Đã Giao</h3>
          <p class="stat-number"><?= $stats['delivered'] ?></p>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
          <h3>Doanh Thu</h3>
          <p class="stat-number"><?= number_format($stats['revenue'] ?? 0, 0, ',', '.') ?>đ</p>
        </div>
      </div>
    </div>

    <!-- Bộ lọc -->
    <div class="card" style="margin-top:20px">
      <h3>Danh Sách Đơn Hàng</h3>
      <form method="GET" action="orders.php" class="search-bar" style="margin-bottom:16px">
        <label>Từ ngày</label>
        <input type="date" name="date_from" class="input-field" value="<?= htmlspecialchars($dateFrom) ?>"/>
        <label>Đến ngày</label>
        <input type="date" name="date_to"   class="input-field" value="<?= htmlspecialchars($dateTo) ?>"/>
        <label>Tình trạng</label>
        <select name="status" class="input-field">
          <option value="">Tất cả</option>
          <?php foreach ($statusMap as $val => $info): ?>
          <option value="<?= $val ?>" <?= $filterStatus === $val ? 'selected' : '' ?>>
            <?= $info['label'] ?>
          </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-edit">Tra cứu</button>
        <a href="orders.php" class="btn-reset" style="text-decoration:none;padding:6px 10px">Làm mới</a>
      </form>

      <table>
        <thead>
          <tr>
            <th>Mã ĐH</th>
            <th>Khách Hàng</th>
            <th>Ngày Đặt</th>
            <th>Tổng Tiền</th>
            <th>Tình Trạng</th>
            <th>Thao Tác</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($orders && $orders->num_rows > 0): ?>
          <?php while ($o = $orders->fetch_assoc()):
            $st = $statusMap[$o['status']] ?? ['label' => $o['status'], 'class' => 'status-processing'];
          ?>
          <tr>
            <td><strong>#DH<?= str_pad($o['id'], 3, '0', STR_PAD_LEFT) ?></strong></td>
            <td>
              <?= htmlspecialchars($o['customer_name'] ?? 'Khách vãng lai') ?>
              <?php if ($o['sdt']): ?>
                <br/><small style="color:#888"><?= htmlspecialchars($o['sdt']) ?></small>
              <?php endif; ?>
            </td>
            <td><?= date('d/m/Y H:i', strtotime($o['order_date'])) ?></td>
            <td><strong style="color:#dc2626"><?= number_format($o['total_price'], 0, ',', '.') ?>đ</strong></td>
            <td><span class="status <?= $st['class'] ?>"><?= $st['label'] ?></span></td>
            <td style="white-space:nowrap">
              <!-- Xem chi tiết -->
              <a href="#detail-<?= $o['id'] ?>" class="btn-search">Chi tiết</a>

              <?php if (!in_array($o['status'], ['delivered', 'cancelled'])): ?>
                <!-- Cập nhật trạng thái -->
                <a href="#status-<?= $o['id'] ?>" class="btn-edit">Cập nhật</a>
              <?php endif; ?>

              <?php if ($o['status'] === 'pending'): ?>
                <form method="POST" style="display:inline"
                      onsubmit="return confirm('Xác nhận hủy đơn hàng này?')">
                  <input type="hidden" name="action"   value="update_status"/>
                  <input type="hidden" name="order_id" value="<?= $o['id'] ?>"/>
                  <input type="hidden" name="status"   value="cancelled"/>
                  <button type="submit" class="xoa">Hủy đơn</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" style="text-align:center;color:#aaa">Không có đơn hàng nào.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ══ POPUP CHI TIẾT ════════════════════════════════════ -->
<?php
$orders2 = $conn->query("
    SELECT o.*, CONCAT(u.ho,' ',u.ten) AS customer_name, u.sdt, u.email, u.diachi
    FROM orders o LEFT JOIN users u ON u.id=o.user_id
    ORDER BY o.order_date DESC
");
while ($o = $orders2->fetch_assoc()):
    $st = $statusMap[$o['status']] ?? ['label'=>$o['status'],'class'=>'status-processing'];
    $details = $conn->query("
        SELECT od.*, p.name AS product_name, p.image
        FROM order_details od
        JOIN products p ON p.id = od.product_id
        WHERE od.order_id = {$o['id']}
    ");
?>
<div id="detail-<?= $o['id'] ?>" class="popup-overlay">
  <div class="popup">
    <a href="orders.php" class="close-btn">✖</a>
    <h3 style="color:#dc2626;margin-bottom:14px">Chi Tiết Đơn Hàng #DH<?= str_pad($o['id'],3,'0',STR_PAD_LEFT) ?></h3>
    <div class="detail-info" style="margin-bottom:16px;display:grid;grid-template-columns:1fr 1fr;gap:6px">
      <p><strong>Khách hàng:</strong> <?= htmlspecialchars($o['customer_name'] ?? 'Khách vãng lai') ?></p>
      <p><strong>SĐT:</strong> <?= htmlspecialchars($o['sdt'] ?? '—') ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($o['email'] ?? '—') ?></p>
      <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($o['diachi'] ?? '—') ?></p>
      <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($o['order_date'])) ?></p>
      <p><strong>Trạng thái:</strong> <span class="status <?= $st['class'] ?>"><?= $st['label'] ?></span></p>
    </div>
    <h3 style="margin-bottom:10px">Sản phẩm trong đơn</h3>
    <table style="margin-bottom:16px">
      <thead><tr><th>Hình</th><th>Tên SP</th><th>Đơn giá</th><th>SL</th><th>Thành tiền</th></tr></thead>
      <tbody>
      <?php
      $tongTien = 0;
      if ($details && $details->num_rows > 0):
        while ($d = $details->fetch_assoc()):
          $subtotal = $d['price'] * $d['quantity'];
          $tongTien += $subtotal;
      ?>
        <tr>
          <td><img src="<?= htmlspecialchars($d['image']) ?>" style="width:40px;height:40px;border-radius:4px"/></td>
          <td><?= htmlspecialchars($d['product_name']) ?></td>
          <td><?= number_format($d['price'], 0, ',', '.') ?>đ</td>
          <td><?= $d['quantity'] ?></td>
          <td><strong><?= number_format($subtotal, 0, ',', '.') ?>đ</strong></td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="5" style="text-align:center;color:#aaa">Không có sản phẩm.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    <div class="total-box" style="text-align:right">
      <p style="font-size:16px">Tổng cộng: <strong style="color:#dc2626;font-size:18px"><?= number_format($o['total_price'], 0, ',', '.') ?>đ</strong></p>
    </div>
  </div>
</div>

<!-- ══ POPUP CẬP NHẬT TRẠNG THÁI ════════════════════════ -->
<div id="status-<?= $o['id'] ?>" class="popup-overlay">
  <div class="popup" style="max-width:400px">
    <a href="orders.php" class="close-btn">✖</a>
    <h3 style="color:#dc2626;margin-bottom:16px">Cập Nhật Trạng Thái</h3>
    <p style="margin-bottom:12px">Đơn hàng: <strong>#DH<?= str_pad($o['id'],3,'0',STR_PAD_LEFT) ?></strong></p>
    <form method="POST" action="orders.php">
      <input type="hidden" name="action"   value="update_status"/>
      <input type="hidden" name="order_id" value="<?= $o['id'] ?>"/>
      <label>Chọn trạng thái mới:</label>
      <select name="status" class="input-field">
        <?php foreach ($statusMap as $val => $info): ?>
        <option value="<?= $val ?>" <?= $o['status'] === $val ? 'selected' : '' ?>>
          <?= $info['label'] ?>
        </option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-save" style="margin-top:10px">Lưu Thay Đổi</button>
    </form>
  </div>
</div>
<?php endwhile; ?>

<!-- ══ POPUP THÊM ĐƠN HÀNG ══════════════════════════════ -->
<div id="popup-add" class="popup-overlay">
  <div class="popup" style="max-width:480px">
    <a href="orders.php" class="close-btn">✖</a>
    <h3 style="color:#dc2626;margin-bottom:16px">Thêm Đơn Hàng</h3>
    <form method="POST" action="orders.php">
      <input type="hidden" name="action" value="add"/>
      <label>Khách hàng:</label>
      <select name="user_id" class="input-field" required>
        <option value="">-- Chọn khách hàng --</option>
        <?php $users->data_seek(0); while ($u = $users->fetch_assoc()): ?>
        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['ho'].' '.$u['ten']) ?></option>
        <?php endwhile; ?>
      </select>
      <label>Tổng tiền (đ):</label>
      <input type="number" name="total_price" class="input-field" placeholder="Nhập tổng tiền" min="0" required/>
      <label>Trạng thái:</label>
      <select name="status" class="input-field">
        <?php foreach ($statusMap as $val => $info): ?>
        <option value="<?= $val ?>"><?= $info['label'] ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-save" style="margin-top:10px">Lưu Đơn Hàng</button>
    </form>
  </div>
</div>

</body>
</html>