<?php
session_start();

if (isset($_SESSION['adminLoggedIn']) && $_SESSION['adminLoggedIn'] === true) {
    header("Location: index.php");
    exit;
}

$loginError = $_SESSION['loginError'] ?? '';
unset($_SESSION['loginError']);
?>
<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Đăng nhập Admin - MUIT</title>
    <link rel="stylesheet" href="../css/admin-style.css" />
</head>

<body class="admin-login-body">
    <div class="admin-login-container">
        <div class="admin-login-box">
            <div class="admin-login-header">
                <img src="../img/logo.png" alt="Logo" class="admin-logo" />
                <h1>Đăng nhập Admin</h1>
                <p>Hệ thống quản trị laptopshop</p>
            </div>

            <form method="POST" action="admin-login-process.php" class="admin-login-form">
                <div class="form-group">
                    <label for="adminUsername">Tên đăng nhập:</label>
                    <input type="text" id="adminUsername" name="username" required autocomplete="username" />
                </div>

                <div class="form-group">
                    <label for="adminPassword">Mật khẩu:</label>
                    <input type="password" id="adminPassword" name="password" required autocomplete="current-password" />
                </div>

                <?php if ($loginError): ?>
                    <div class="error-message" style="display: block; color:red;">
                        <?= htmlspecialchars($loginError) ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="admin-login-btn">Đăng nhập</button>
            </form>

            <div class="admin-login-footer">
                <p>Lưu ý: Trang này dành riêng cho quản trị viên</p>
                <a href="../users/trangchu.html">← Quay về trang chủ</a>
            </div>
        </div>
    </div>
</body>

</html>