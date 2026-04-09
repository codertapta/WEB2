<?php
session_start();
include("../../../config.php");

if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// ====================== XỬ LÝ THÊM / SỬA ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id     = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $code   = trim($_POST['code'] ?? '');
    $name   = trim($_POST['name'] ?? '');
    $status = ($_POST['status'] ?? '') === 'Hoạt động' ? 1 : 0;

    // Lỗi từng trường riêng biệt
    $errCode = '';
    $errName = '';

    if (empty($code)) {
        $errCode = "Vui lòng nhập mã loại.";
    }

    if (empty($name)) {
        $errName = "Vui lòng nhập tên loại sản phẩm.";
    } elseif (preg_match('/[0-9]/', $name)) {
        $errName = "Tên loại sản phẩm không được chứa chữ số.";
    } elseif (preg_match('/[^a-zA-ZÀ-ỹ\s]/u', $name)) {
        $errName = "Tên loại sản phẩm không được chứa ký tự đặc biệt.";
    }

    if ($errCode || $errName) {
        // Giữ lại giá trị để điền lại vào form
        $reopenPopup = true;
        $fillId      = $id;
        $fillCode    = htmlspecialchars($code);
        $fillName    = htmlspecialchars($name);
        $fillStatus  = $_POST['status'] ?? 'Hoạt động';
    } else {
        $code = $conn->real_escape_string($code);
        $name = $conn->real_escape_string($name);

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET code=?, name=?, status=? WHERE id=?");
            $stmt->bind_param("ssii", $code, $name, $status, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (code, name, status) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $code, $name, $status);
        }
        $stmt->execute();
        $stmt->close();
        header("Location: categories.php");
        exit;
    }
}

// Xóa category
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE id=$delete_id");
    header("Location: categories.php");
    exit;
}

$sql    = "SELECT * FROM categories ORDER BY id ASC";
$result = $conn->query($sql);
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Loại Sản Phẩm</title>
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        .popup-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .popup {
            background: #fff;
            padding: 20px 30px;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px; right: 10px;
            text-decoration: none;
            font-size: 18px;
            color: #fff;
            cursor: pointer;
        }

        a.head, a.sua, a.xoa {
            text-decoration: none;
            color: #fff;
            cursor: pointer;
            transition: color 0.2s;
        }

        /* ── Validation styles (giống customers.php) ── */
        .field-wrapper {
            margin-bottom: 4px;
        }

        .field-wrapper label {
            display: block;
            margin-bottom: 4px;
        }

        .field-wrapper .input-field {
            width: 100%;
            box-sizing: border-box;
        }

        .field-wrapper .input-field.input-error {
            border: 1.5px solid #e53935 !important;
            background: #fff5f5;
        }

        .field-wrapper .input-field.input-ok {
            border: 1.5px solid #43a047 !important;
        }

        .field-error-msg {
            color: #e53935;
            font-size: 12px;
            margin: 0 0 10px 2px;
            display: none;
            min-height: 16px;
        }

        .field-error-msg.visible {
            display: block;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo">
            <h2>Muit Store</h2>
            <p>Hệ Thống Quản Lý</p>
        </div>
        <nav class="menu">
            <a href="index.php"       class="menu-item"><span>📊</span> Tổng quan</a>
            <a href="./customers.php" class="menu-item"><span>👥</span> Quản Lý Khách Hàng</a>
            <a href="categories.php"  class="menu-item active"><span>📂</span> Loại Sản Phẩm</a>
            <a href="./products.php"  class="menu-item"><span>💻</span> Danh Mục Sản Phẩm</a>
            <a href="import.php"      class="menu-item"><span>🚚</span> Quản Lý Nhập Hàng</a>
            <a href="price.php"       class="menu-item"><span>🏷️</span> Quản Lý Giá Bán</a>
            <a href="orders.php"      class="menu-item"><span>🛒</span> Quản Lý Đơn Hàng</a>
            <a href="inventory.php"   class="menu-item"><span>📦</span> Quản Lý Tồn Kho</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Loại sản phẩm</h1>
            <div class="info">
                <img src="../img/logo.png" alt="admin" />
                <span><?php echo $_SESSION['adminUser']['name']; ?></span>
                <button onclick="logout()">Đăng xuất</button>
            </div>
        </div>

        <div class="container">
            <div class="card">
                <div class="title">
                    <h3>Quản lý loại sản phẩm</h3>
                    <a href="#popupForm" class="head" onclick="openForm()">+ Thêm loại sản phẩm</a>
                </div>

                <div class="search">
                    <input type="text" id="searchInput"
                           placeholder="🔍 Tìm kiếm theo mã hoặc tên loại..."
                           onkeyup="searchTable()" />
                </div>

                <table id="categoryTable">
                    <thead>
                        <tr>
                            <th>Mã loại</th>
                            <th>Loại sản phẩm</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($row['code']); ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td>
                                        <span class="status <?php echo $row['status'] ? 'status-active' : 'status-hidden'; ?>">
                                            <?php echo $row['status'] ? 'Hoạt động' : 'Ẩn'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="#popupForm" class="sua"
                                           onclick="editForm(
                                               <?php echo $row['id']; ?>,
                                               '<?php echo addslashes($row['code']); ?>',
                                               '<?php echo addslashes($row['name']); ?>',
                                               '<?php echo $row['status'] ? 'Hoạt động' : 'Ẩn'; ?>'
                                           )">Sửa</a>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="xoa"
                                           onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">Chưa có dữ liệu</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- POPUP FORM -->
    <div id="popupForm" class="popup-overlay" style="display:none;">
        <div class="popup">
            <a href="#" class="close-btn" onclick="closeForm()">✖</a>
            <h3>Thêm / Sửa Loại Sản Phẩm</h3>

            <form method="POST" id="categoryForm" novalidate>
                <input type="hidden" name="id" id="categoryId"
                       value="<?php echo $fillId ?? 0; ?>" />

                <!-- Mã loại -->
                <div class="field-wrapper">
                    <label>Mã loại</label>
                    <input type="text" name="code" id="categoryCode" class="input-field
                           <?php echo !empty($errCode) ? 'input-error' : ''; ?>"
                           placeholder="Nhập mã loại"
                           value="<?php echo $fillCode ?? ''; ?>">
                    <span class="field-error-msg <?php echo !empty($errCode) ? 'visible' : ''; ?>"
                          id="err-categoryCode">
                        <?php echo $errCode ?? ''; ?>
                    </span>
                </div>

                <!-- Tên loại -->
                <div class="field-wrapper">
                    <label>Tên loại sản phẩm</label>
                    <input type="text" name="name" id="categoryName" class="input-field
                           <?php echo !empty($errName) ? 'input-error' : ''; ?>"
                           placeholder="Nhập tên loại"
                           value="<?php echo $fillName ?? ''; ?>">
                    <span class="field-error-msg <?php echo !empty($errName) ? 'visible' : ''; ?>"
                          id="err-categoryName">
                        <?php echo $errName ?? ''; ?>
                    </span>
                </div>

                <!-- Trạng thái -->
                <div class="field-wrapper">
                    <label>Trạng thái</label>
                    <select name="status" id="categoryStatus" class="input-field"
                            style="margin-bottom:12px;">
                        <option <?php echo (($fillStatus ?? 'Hoạt động') === 'Hoạt động') ? 'selected' : ''; ?>>Hoạt động</option>
                        <option <?php echo (($fillStatus ?? '') === 'Ẩn') ? 'selected' : ''; ?>>Ẩn</option>
                    </select>
                </div>

                <button type="submit" class="btn-save">Lưu thông tin</button>
            </form>
        </div>
    </div>

    <script>
    const nameRegex = /^[a-zA-ZÀ-ỹ\s]+$/u;

    function setError(fieldId, msg) {
        const input = document.getElementById(fieldId);
        const errEl = document.getElementById('err-' + fieldId);
        input.classList.add('input-error');
        input.classList.remove('input-ok');
        errEl.textContent = msg;
        errEl.classList.add('visible');
        return false;
    }

    function setOk(fieldId) {
        const input = document.getElementById(fieldId);
        const errEl = document.getElementById('err-' + fieldId);
        input.classList.remove('input-error');
        input.classList.add('input-ok');
        errEl.textContent = '';
        errEl.classList.remove('visible');
        return true;
    }

    function validateCode() {
        const val = document.getElementById('categoryCode').value.trim();
        if (!val) return setError('categoryCode', 'Vui lòng nhập mã loại.');
        return setOk('categoryCode');
    }

    function validateName() {
        const val = document.getElementById('categoryName').value.trim();
        if (!val)                 return setError('categoryName', 'Vui lòng nhập tên loại sản phẩm.');
        if (/[0-9]/.test(val))    return setError('categoryName', 'Tên loại không được chứa chữ số.');
        if (!nameRegex.test(val)) return setError('categoryName', 'Tên loại không được chứa ký tự đặc biệt.');
        return setOk('categoryName');
    }

    // Validate ngay khi rời ô
    document.getElementById('categoryCode').addEventListener('blur', validateCode);
    document.getElementById('categoryName').addEventListener('blur', validateName);

    // Validate khi submit
    document.getElementById('categoryForm').addEventListener('submit', function(e) {
        const ok = [validateCode(), validateName()].every(Boolean);
        if (!ok) {
            e.preventDefault();
            const firstErr = document.querySelector('#categoryForm .input-error');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    function clearValidation() {
        ['categoryCode', 'categoryName'].forEach(id => {
            document.getElementById(id).classList.remove('input-error', 'input-ok');
            const errEl = document.getElementById('err-' + id);
            if (errEl) { errEl.textContent = ''; errEl.classList.remove('visible'); }
        });
    }

    function openForm() {
        document.getElementById('popupForm').style.display = 'flex';
        document.getElementById('categoryId').value     = 0;
        document.getElementById('categoryCode').value   = '';
        document.getElementById('categoryName').value   = '';
        document.getElementById('categoryStatus').value = 'Hoạt động';
        clearValidation();
    }

    function editForm(id, code, name, status) {
        document.getElementById('popupForm').style.display = 'flex';
        document.getElementById('categoryId').value     = id;
        document.getElementById('categoryCode').value   = code;
        document.getElementById('categoryName').value   = name;
        document.getElementById('categoryStatus').value = status;
        clearValidation();
    }

    function closeForm() {
        document.getElementById('popupForm').style.display = 'none';
    }

    document.getElementById('popupForm').addEventListener('click', function(e) {
        if (e.target === this) closeForm();
    });

    // Nếu PHP trả lỗi → tự mở lại popup
    <?php if (!empty($reopenPopup)): ?>
    window.addEventListener('DOMContentLoaded', function() {
        document.getElementById('popupForm').style.display = 'flex';
    });
    <?php endif; ?>

    function logout() {
        sessionStorage.clear();
        window.location.href = "login.html";
    }

    function searchTable() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const trs   = document.getElementById('categoryTable').getElementsByTagName('tr');
        for (let i = 1; i < trs.length; i++) {
            const tdCode = trs[i].getElementsByTagName('td')[0]?.textContent.toLowerCase() ?? '';
            const tdName = trs[i].getElementsByTagName('td')[1]?.textContent.toLowerCase() ?? '';
            trs[i].style.display = (tdCode.includes(input) || tdName.includes(input)) ? '' : 'none';
        }
    }
    </script>
</body>
</html>