<?php
session_start();
require __DIR__ . "/../../../config.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']    ?? '');
    $pass  = trim($_POST['password'] ?? '');

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    $user   = mysqli_fetch_assoc($result);

    if ($user && $pass === $user['password']) {
        // Đăng nhập thành công
        $_SESSION['user_id'] = $user['id'];
        header("Location: products.php");
        exit;
    } else {
        $error = "Email hoặc mật khẩu không đúng, vui lòng thử lại!";
    }
}
?>

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
        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }
        .logo img { width: 40px; height: 40px; margin-right: 10px; }
        .error { color: darkred; font-size: 13px; margin-top: 8px; }
        a { color: red; text-decoration: none; }
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

    <p>Chưa có tài khoản? <a href="register.php">Đăng ký</a></p>
</div>
</body>
</html>