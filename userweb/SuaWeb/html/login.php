<?php
session_start();
require __DIR__ . "/../../../config.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']    ?? '');
    $pass  = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        // Kiểm tra khóa
        if ($user['status'] !== 'active') {
            $error = "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.";
        } 
        else {
            $passwordValid = false;
            // Nếu mật khẩu đã được hash (bắt đầu bằng $2y$)
            if (strpos($user['password'], '$2y$') === 0) {
                $passwordValid = password_verify($pass, $user['password']);
            } else {
                // Trường hợp mật khẩu chưa hash (plain text) - tương thích ngược
                $passwordValid = ($pass === $user['password']);
            }

            if ($passwordValid) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: products.php");
                exit;
            } else {
                $error = "Email hoặc mật khẩu không đúng, vui lòng thử lại!";
            }
        }
    } else {
        $error = "Email hoặc mật khẩu không đúng, vui lòng thử lại!";
    }
}
?>
<!-- PHẦN HTML GIỮ NGUYÊN NHƯ CŨ -->
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8"/>
    <title>Đăng nhập</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: white;
            color: red;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-box {
            border: 1px solid red;
            padding: 20px;
            border-radius: 8px;
            width: 320px;
            box-shadow: 0 0 10px rgba(255, 0, 0, 0.2);
            text-align: center;
        }
        h2 { margin-bottom: 15px; }
        input, button {
            width: 90%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid red;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background: red;
            color: white;
            cursor: pointer;
            border: none;
        }
        button:hover { background: darkred; }
        .btn-guest {
            display: block;
            width: 90%;
            padding: 10px;
            margin: 8px auto 0;
            background: white;
            color: red;
            border: 1px solid red;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            box-sizing: border-box;
        }
        .btn-guest:hover {
            background: #fff0f0;
        }
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .logo img { width: 40px; height: 40px; margin-right: 10px; }
        .error { color: darkred; font-size: 13px; margin-top: 8px; }
        a { color: red; text-decoration: none; }
        .divider {
            border: none;
            border-top: 1px solid #fbb;
            margin: 12px 0 4px;
        }
    </style>
</head>
<body>
<div class="form-box">
    <div class="logo">
        <img src="../img/logo.png" alt="Logo Cửa Hàng"/>
        <span><strong>MUIT STORE</strong></span>
    </div>
    <h2>Đăng nhập</h2>

    <form method="POST">
        <input type="email"    name="email"    placeholder="Email"     required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
        <input type="password" name="password" placeholder="Mật khẩu" required/>
        <button type="submit">Đăng nhập</button>
    </form>

    <?php if ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <hr class="divider"/>

    <a href="products_guest.php" class="btn-guest">🛒 Sử dụng không cần đăng nhập</a>

    <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
</div>
</body>
</html>