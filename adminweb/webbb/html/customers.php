<?php
session_start();
include("../../../config.php");

// 🔒 Kiểm tra admin login
if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
    header("Location: login.html");
    exit;
}

// ====================== XỬ LÝ THÊM KHÁCH HÀNG ======================
if (isset($_POST['addUser'])) {
    $ho      = trim($_POST['ho'] ?? '');
    $ten     = trim($_POST['ten'] ?? '');
    $sdt     = trim($_POST['sdt'] ?? '');
    $diachi  = trim($_POST['diachi'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $status  = $_POST['status'] ?? 'active';

    $errors = [];

    // ── Validate Họ ──────────────────────────────────────────────
    if (empty($ho)) {
        $errors[] = "Vui lòng nhập Họ.";
    } elseif (preg_match('/[0-9]/', $ho)) {
        $errors[] = "Họ không được chứa chữ số.";
    } elseif (preg_match('/[^a-zA-ZÀ-ỹ\s]/u', $ho)) {
        $errors[] = "Họ không được chứa ký tự đặc biệt.";
    }

    // ── Validate Tên ─────────────────────────────────────────────
    if (empty($ten)) {
        $errors[] = "Vui lòng nhập Tên.";
    } elseif (preg_match('/[0-9]/', $ten)) {
        $errors[] = "Tên không được chứa chữ số.";
    } elseif (preg_match('/[^a-zA-ZÀ-ỹ\s]/u', $ten)) {
        $errors[] = "Tên không được chứa ký tự đặc biệt.";
    }

    // ── Validate SĐT ─────────────────────────────────────────────
    if (!empty($sdt)) {
        if (!preg_match('/^0[0-9]{9}$/', $sdt)) {
            $errors[] = "Số điện thoại phải bắt đầu bằng số 0 và có đúng 10 chữ số.";
        }
    }

    // ── Validate Email ───────────────────────────────────────────
    if (empty($email)) {
        $errors[] = "Vui lòng nhập Email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không đúng định dạng.";
    }

    // ── Validate Địa chỉ ─────────────────────────────────────────
    if (!empty($diachi)) {
        // Cho phép chữ cái (kể cả tiếng Việt), số, khoảng trắng, dấu phẩy, dấu chấm và dấu /
        if (preg_match('/[^a-zA-ZÀ-ỹ0-9\s,.\/-]/u', $diachi)) {
            $errors[] = "Địa chỉ không được chứa ký tự đặc biệt (chỉ cho phép dấu /).";
        }
    }

    // ── Validate Password ────────────────────────────────────────
    if (empty($password)) {
        $errors[] = "Vui lòng nhập Mật khẩu.";
    }

    if (!empty($errors)) {
        $error = implode("<br>", $errors);
    } else {
        // Escape sau khi validate
        $ho      = $conn->real_escape_string($ho);
        $ten     = $conn->real_escape_string($ten);
        $sdt     = $conn->real_escape_string($sdt);
        $diachi  = $conn->real_escape_string($diachi);
        $email   = $conn->real_escape_string($email);
        $status  = $conn->real_escape_string($status);

        $check = $conn->query("SELECT id FROM users WHERE email = '$email'");
        if ($check && $check->num_rows > 0) {
            $error = "Email này đã được sử dụng!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (ho, ten, sdt, diachi, email, password, status) 
                    VALUES ('$ho', '$ten', '$sdt', '$diachi', '$email', '$hashed_password', '$status')";
            if ($conn->query($sql)) {
                $success = "✅ Thêm khách hàng thành công!";
            } else {
                $error = "Lỗi khi thêm: " . $conn->error;
            }
        }
    }
}

// 🔍 Tìm kiếm
$search = "";
if (isset($_GET['q']) && $_GET['q'] != "") {
    $search = $conn->real_escape_string($_GET['q']);
    $sql = "SELECT * FROM users WHERE CONCAT(ho, ' ', ten) LIKE '%$search%' OR email LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM users";
}
$result = $conn->query($sql);
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Quản Lý Khách Hàng</title>
    <link rel="stylesheet" href="../css/style.css" />
    <style>
    .popup-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.3);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .popup {
        background: #fff;
        padding: 25px 30px;
        border-radius: 12px;
        width: 420px;
        max-width: 90%;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        position: relative;
        animation: popupShow 0.25s ease;
        max-height: 90vh;
        overflow-y: auto;
    }

    @keyframes popupShow {
        from { transform: translateY(-20px); opacity: 0; }
        to   { transform: translateY(0);    opacity: 1; }
    }

    .close-btn {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 20px;
        cursor: pointer;
    }

    /* ── Validation styles ── */
    .field-wrapper {
        position: relative;
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
        margin: 0 0 8px 2px;
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
            <a href="index.php"      class="menu-item"><span>📊</span> Tổng quan</a>
            <a href="customers.php"  class="menu-item active"><span>👥</span> Quản Lý Khách Hàng</a>
            <a href="categories.php" class="menu-item"><span>📂</span> Loại Sản Phẩm</a>
            <a href="products.php"   class="menu-item"><span>💻</span> Danh Mục Sản Phẩm</a>
            <a href="import.php"     class="menu-item"><span>🚚</span> Quản Lý Nhập Hàng</a>
            <a href="price.php"      class="menu-item"><span>🏷️</span> Quản Lý Giá Bán</a>
            <a href="orders.php"     class="menu-item"><span>🛒</span> Quản Lý Đơn Hàng</a>
            <a href="inventory.php"  class="menu-item"><span>📦</span> Quản Lý Tồn Kho</a>
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
            <?php if (isset($success)): ?>
                <div style="background:#d4edda;color:#155724;padding:15px;margin:15px 0;border-radius:5px;text-align:center;">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div style="background:#f8d7da;color:#721c24;padding:15px;margin:15px 0;border-radius:5px;text-align:center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Tìm kiếm -->
            <div class="card" style="margin-bottom:20px">
                <div class="title"><h3>Tìm kiếm khách hàng</h3></div>
                <div class="search">
                    <form method="get" action="">
                        <input type="text" name="q" placeholder="🔍 Tìm kiếm theo tên hoặc email..." class="input-field"
                               value="<?php echo htmlspecialchars($search); ?>" />
                        <button class="btn-search">Tìm kiếm</button>
                        <a href="customers.php"><button type="button" class="btn-reset">Làm mới</button></a>
                    </form>
                </div>
            </div>

            <!-- Danh sách -->
            <div class="card">
                <div style="margin-bottom:15px;">
                    <a href="#popupForm" class="head" onclick="openForm()">+ Thêm tài khoản</a>
                </div>
                <div class="title"><h3>Danh sách khách hàng</h3></div>
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
                                    <form method="post" action="reset_password.php" style="display:inline;">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button class="sua">Reset mật khẩu</button>
                                    </form>
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

    <!-- POPUP FORM -->
    <div id="popupForm" class="popup-overlay">
        <div class="popup">
            <span class="close-btn" onclick="closeForm()">✖</span>
            <h3>Thêm tài khoản khách hàng</h3>

            <form method="post" id="addUserForm" novalidate>

                <div class="field-wrapper">
                    <input type="text" id="ho" name="ho" placeholder="Họ *" class="input-field">
                    <span class="field-error-msg" id="err-ho"></span>
                </div>

                <div class="field-wrapper">
                    <input type="text" id="ten" name="ten" placeholder="Tên *" class="input-field">
                    <span class="field-error-msg" id="err-ten"></span>
                </div>

                <div class="field-wrapper">
                    <input type="text" id="sdt" name="sdt" placeholder="SĐT" class="input-field" maxlength="10">
                    <span class="field-error-msg" id="err-sdt"></span>
                </div>

                <div class="field-wrapper">
                    <input type="text" id="diachi" name="diachi" placeholder="Địa chỉ" class="input-field">
                    <span class="field-error-msg" id="err-diachi"></span>
                </div>

                <div class="field-wrapper">
                    <input type="email" id="email" name="email" placeholder="Email *" class="input-field">
                    <span class="field-error-msg" id="err-email"></span>
                </div>

                <div class="field-wrapper">
                    <input type="password" id="password" name="password" placeholder="Mật khẩu *" class="input-field">
                    <span class="field-error-msg" id="err-password"></span>
                </div>

                <select name="status" class="input-field" style="margin-bottom:12px;">
                    <option value="active">Hoạt động</option>
                    <option value="locked">Khóa</option>
                </select>

                <button type="submit" name="addUser" class="btn-add">Thêm khách hàng</button>
            </form>
        </div>
    </div>

    <script>
    // ── Regex patterns ──────────────────────────────────────────────────────
    // Chữ cái tiếng Việt + khoảng trắng, không cho số và ký tự đặc biệt
    const nameRegex    = /^[a-zA-ZÀ-ỹ\s]+$/u;
    const phoneRegex   = /^0[0-9]{9}$/;
    const emailRegex   = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    // Địa chỉ: chữ cái (kể cả tiếng Việt), số, khoảng trắng, dấu phẩy, chấm, gạch ngang và /
    const addressRegex = /^[a-zA-ZÀ-ỹ0-9\s,.\/-]*$/u;

    // ── Hiển thị / xóa lỗi ─────────────────────────────────────────────────
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

    // ── Validate từng trường ───────────────────────────────────────────────
    function validateHo() {
        const val = document.getElementById('ho').value.trim();
        if (!val)                        return setError('ho', 'Vui lòng nhập Họ.');
        if (/[0-9]/.test(val))           return setError('ho', 'Họ không được chứa chữ số.');
        if (!nameRegex.test(val))        return setError('ho', 'Họ không được chứa ký tự đặc biệt.');
        return setOk('ho');
    }

    function validateTen() {
        const val = document.getElementById('ten').value.trim();
        if (!val)                        return setError('ten', 'Vui lòng nhập Tên.');
        if (/[0-9]/.test(val))           return setError('ten', 'Tên không được chứa chữ số.');
        if (!nameRegex.test(val))        return setError('ten', 'Tên không được chứa ký tự đặc biệt.');
        return setOk('ten');
    }

    function validateSdt() {
        const val = document.getElementById('sdt').value.trim();
        if (!val) return setOk('sdt'); // không bắt buộc
        if (!phoneRegex.test(val))
            return setError('sdt', 'SĐT phải bắt đầu bằng số 0 và có đúng 10 chữ số.');
        return setOk('sdt');
    }

    function validateDiachi() {
        const val = document.getElementById('diachi').value.trim();
        if (!val) return setOk('diachi'); // không bắt buộc
        if (!addressRegex.test(val))
            return setError('diachi', 'Địa chỉ không được chứa ký tự đặc biệt (chỉ cho phép dấu /).');
        return setOk('diachi');
    }

    function validateEmail() {
        const val = document.getElementById('email').value.trim();
        if (!val)                    return setError('email', 'Vui lòng nhập Email.');
        if (!emailRegex.test(val))   return setError('email', 'Email không đúng định dạng (vd: abc@mail.com).');
        return setOk('email');
    }

    function validatePassword() {
        const val = document.getElementById('password').value;
        if (!val) return setError('password', 'Vui lòng nhập Mật khẩu.');
        return setOk('password');
    }

    // ── Gắn sự kiện blur (validate ngay khi rời ô) ─────────────────────────
    document.getElementById('ho').addEventListener('blur', validateHo);
    document.getElementById('ten').addEventListener('blur', validateTen);
    document.getElementById('sdt').addEventListener('blur', validateSdt);
    document.getElementById('diachi').addEventListener('blur', validateDiachi);
    document.getElementById('email').addEventListener('blur', validateEmail);
    document.getElementById('password').addEventListener('blur', validatePassword);

    // ── Validate toàn bộ khi submit ────────────────────────────────────────
    document.getElementById('addUserForm').addEventListener('submit', function(e) {
        const ok = [
            validateHo(),
            validateTen(),
            validateSdt(),
            validateDiachi(),
            validateEmail(),
            validatePassword()
        ].every(Boolean);

        if (!ok) {
            e.preventDefault(); // Chặn submit nếu có lỗi
            // Cuộn đến lỗi đầu tiên trong popup
            const firstErr = document.querySelector('.input-error');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    // ── Mở / đóng popup ───────────────────────────────────────────────────
    function openForm() {
        document.getElementById('popupForm').style.display = 'flex';
    }

    function closeForm() {
        document.getElementById('popupForm').style.display = 'none';
    }

    window.onclick = function(e) {
        const popup = document.getElementById('popupForm');
        if (e.target === popup) closeForm();
    };

    // Nếu PHP trả về lỗi → mở lại popup tự động
    <?php if (isset($error)): ?>
    window.addEventListener('DOMContentLoaded', openForm);
    <?php endif; ?>
    </script>
</body>
</html>