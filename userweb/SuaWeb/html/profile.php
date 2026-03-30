<?php
session_start();
include("../../../config.php");

$user_id = $_SESSION['user_id'] ?? 0;

if ($user_id == 0) {
    header("Location: login.php");
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
$user   = mysqli_fetch_assoc($result);
?>
<!doctype html>
<html lang="vi">

<head>
  <meta charset="UTF-8" />
  <title>Hồ sơ cá nhân - MUIT</title>

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
    }

    body {
      background: #f5f6fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .profile-container {
      width: 420px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      padding: 30px;
    }

    .profile-header {
      text-align: center;
      margin-bottom: 25px;
    }

    .logo {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      margin-bottom: 10px;
    }

    .logo img {
      width: 45px;
    }

    .logo span {
      font-size: 22px;
      font-weight: bold;
      color: #e60023;
    }

    .profile-header h2 {
      font-size: 22px;
      color: #333;
    }

    .avatar {
      display: flex;
      justify-content: center;
      margin: 20px 0;
    }

    .avatar img {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      border: 3px solid #e60023;
    }

    .info {
      margin-top: 10px;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      border-bottom: 1px solid #eee;
    }

    .info-label {
      color: #666;
      font-weight: bold;
    }

    .info-value {
      color: #333;
    }

    .buttons {
      margin-top: 25px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .btn {
      padding: 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      text-align: center;
      text-decoration: none;
      font-weight: bold;
    }

    .btn-main {
      background: #e60023;
      color: white;
    }

    .btn-main:hover {
      background: #c4001d;
    }

    .btn-secondary {
      border: 1px solid #e60023;
      color: #e60023;
      background: white;
    }

    .btn-secondary:hover {
      background: #ffe5ea;
    }

    .logout {
      text-align: center;
      margin-top: 15px;
      font-size: 14px;
    }

    .logout a {
      color: #e60023;
      text-decoration: none;
      font-weight: bold;
    }

    .logout a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <div class="profile-container">
    <div class="profile-header">
      <div class="logo">
        <img src="../img/logo.png" />
        <span>MUIT STORE</span>
      </div>
      <h2>Hồ sơ cá nhân</h2>
    </div>

    <div class="avatar">
      <img src="../img/logo.png" alt="" />
    </div>

    <div class="info">
      <div class="info-row">
        <div class="info-label">Họ</div>
        <div class="info-value"><?php echo htmlspecialchars($user['ho']); ?></div>
      </div>

      <div class="info-row">
        <div class="info-label">Tên</div>
        <div class="info-value"><?php echo htmlspecialchars($user['ten']); ?></div>
      </div>

      <div class="info-row">
        <div class="info-label">Số điện thoại</div>
        <div class="info-value"><?php echo htmlspecialchars($user['sdt']); ?></div>
      </div>

      <div class="info-row">
        <div class="info-label">Địa chỉ</div>
        <div class="info-value"><?php echo htmlspecialchars($user['diachi']); ?></div>
      </div>
    </div>

    <div class="buttons">
      <a href="products.php" class="btn btn-main">
        Quay lại trang sản phẩm
      </a>
      <a href="edit-profile.php" class="btn btn-secondary">
        Chỉnh sửa thông tin
      </a>
    </div>

    <div class="logout">
      <a href="./login.php">Đăng xuất</a>
    </div>
  </div>
</body>

</html>