<?php
session_start();
require __DIR__ . "/../../../config.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoten  = trim($_POST['hoten']  ?? '');
    $sdt    = trim($_POST['sdt']    ?? '');
    $diachi = trim($_POST['diachi'] ?? '');
    $email  = trim($_POST['email']  ?? '');
    $pass   = $_POST['password']    ?? '';

    // Kiểm tra họ tên: không có số/ký tự đặc biệt, phải có ít nhất 1 khoảng trắng
    if (!preg_match('/^[p{L}\s]+$/u', $hoten)) {
        $error = "Họ tên không được chứa số hoặc ký tự đặc biệt!";
    } elseif (!str_contains($hoten, ' ')) {
        $error = "Họ tên phải có ít nhất họ và tên (có khoảng trắng)!";
    }

    // Kiểm tra SĐT: bắt đầu bằng 0, đúng 10 chữ số
    if (!$error && !preg_match('/^0[0-9]{9}$/', $sdt)) {
        $error = "Số điện thoại phải bắt đầu bằng 0 và có đúng 10 chữ số!";
    }

    // Kiểm tra email đúng định dạng
    if (!$error && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email không đúng định dạng!";
    }

    // Kiểm tra trùng email
    if (!$error) {
        $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email này đã được sử dụng, vui lòng nhập email khác!";
        }
    }

    // Kiểm tra trùng SĐT
    if (!$error) {
        $check_sdt = mysqli_query($conn, "SELECT id FROM users WHERE sdt = '$sdt'");
        if (mysqli_num_rows($check_sdt) > 0) {
            $error = "Số điện thoại này đã được sử dụng, vui lòng nhập số khác!";
        }
    }

    // Lưu vào DB
    if (!$error) {
        $parts = explode(' ', $hoten, 2);
        $ho    = $parts[0];
        $ten   = $parts[1] ?? '';
        $sql = "INSERT INTO users (ho, ten, sdt, diachi, email, password) 
                VALUES ('$ho', '$ten', '$sdt', '$diachi', '$email', '$pass')";
        if (mysqli_query($conn, $sql)) {
            $success = "Đăng ký thành công! Đang chuyển hướng...";
        } else {
            $error = "Có lỗi xảy ra, vui lòng thử lại!";
        }
    }
}
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8"/>
    <title>Đăng ký</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: white;
            color: red;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
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
        input.invalid { border-color: darkred; background: #fff0f0; }
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
        .error   { color: darkred; margin-top: 10px; font-size: 13px; }
        .success { color: green;   margin-top: 10px; font-size: 13px; }
        .hint    { color: #888;    font-size: 11px;  margin: -4px 0 4px; }
        a { color: red; text-decoration: none; }
    </style>
</head>
<body>
<div class="form-box">
    <div class="logo">
        <img src="../img/logo.png" alt="Logo">
        <span><strong>MUIT STORE</strong></span>
    </div>
    <h2>Đăng ký</h2>

    <form method="POST" id="registerForm">
        <input type="text"     name="hoten"    id="hoten"    placeholder="Họ và tên"     required value="<?= htmlspecialchars($_POST['hoten']  ?? '') ?>"/>
        <p class="hint">Không chứa số/ký tự đặc biệt, phải có họ và tên</p>

        <input type="text"     name="sdt"      id="sdt"      placeholder="Số điện thoại" required value="<?= htmlspecialchars($_POST['sdt']    ?? '') ?>"/>
        <p class="hint">Bắt đầu bằng 0, đúng 10 chữ số</p>

        <input type="text"     name="diachi"   id="diachi"   placeholder="Địa chỉ"       required value="<?= htmlspecialchars($_POST['diachi'] ?? '') ?>"/>

        <input type="email"    name="email"    id="email"    placeholder="Email"          required value="<?= htmlspecialchars($_POST['email']  ?? '') ?>"/>

        <input type="password" name="password" id="password" placeholder="Mật khẩu"      required/>

        <button type="submit">Đăng ký</button>
    </form>

    <?php if ($error):   ?><p class="error"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

    <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let valid = true;

    // Họ tên
    const hoten = document.getElementById('hoten');
    const hotenVal = hoten.value.trim();
    if (!/^[\p{L}\s]+$/u.test(hotenVal)) {
        hoten.classList.add('invalid');
        alert('Họ tên không được chứa số hoặc ký tự đặc biệt!');
        valid = false;
    } else if (!hotenVal.includes(' ')) {
        hoten.classList.add('invalid');
        alert('Họ tên phải có ít nhất họ và tên (có khoảng trắng)!');
        valid = false;
    } else {
        hoten.classList.remove('invalid');
    }

    // SĐT
    if (valid) {
        const sdt = document.getElementById('sdt');
        if (!/^0[0-9]{9}$/.test(sdt.value.trim())) {
            sdt.classList.add('invalid');
            alert('Số điện thoại phải bắt đầu bằng 0 và có đúng 10 chữ số!');
            valid = false;
        } else {
            sdt.classList.remove('invalid');
        }
    }

    // Email
    if (valid) {
        const email = document.getElementById('email');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
            email.classList.add('invalid');
            alert('Email không đúng định dạng!');
            valid = false;
        } else {
            email.classList.remove('invalid');
        }
    }

    if (!valid) e.preventDefault();
});
</script>

<?php if ($success): ?>
<script>
    setTimeout(() => { window.location.href = 'login.php'; }, 2000);
</script>
<?php endif; ?>

</body>
</html>