<?php
session_start();
include("../../../config.php");

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
  die("Lỗi truy vấn: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);
?>
<!doctype html>
<html lang="vi">

<head>
  <meta charset="UTF-8">
  <title>Chỉnh sửa thông tin - MUIT</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Roboto', sans-serif;
    }

    body {
      background: #f0f2f5;
      display: flex;
      justify-content: center;
      align-items: flex-start;
      padding: 50px 10px;
      min-height: 100vh;
    }

    .edit-card {
      background: white;
      width: 100%;
      max-width: 450px;
      border-radius: 16px;
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
      padding: 30px 35px;
      transition: all 0.3s ease;
    }

    .edit-card:hover {
      box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
    }

    .edit-card h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #222;
      font-weight: 700;
      font-size: 24px;
    }

    .form-group {
      margin-bottom: 18px;
      position: relative;
    }

    label {
      display: block;
      font-weight: 500;
      margin-bottom: 6px;
      color: #555;
      font-size: 14px;
    }

    input[type=text],
    input[type=tel] {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 15px;
      transition: all 0.3s ease;
      outline: none;
    }

    input[type=text]:focus,
    input[type=tel]:focus {
      border-color: #e60023;
      box-shadow: 0 0 6px rgba(230, 0, 35, 0.3);
    }

    .btn-main,
    .btn-cancel {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 10px;
      font-weight: 700;
      font-size: 16px;
      cursor: pointer;
      margin-top: 10px;
      transition: all 0.3s ease;
    }

    .btn-main {
      background: linear-gradient(90deg, #e60023, #ff0033);
      color: white;
    }

    .btn-main:hover {
      background: linear-gradient(90deg, #ff0033, #e60023);
      transform: translateY(-2px);
    }

    .btn-cancel {
      background: #f5f5f5;
      color: #555;
      border: 1px solid #ccc;
    }

    .btn-cancel:hover {
      background: #ffe5ea;
      color: #e60023;
      border-color: #e60023;
      transform: translateY(-1px);
    }

    @media (max-width:500px) {
      .edit-card {
        padding: 25px 20px;
      }
    }
  </style>
</head>

<body>
  <div class="edit-card">
    <h2>Chỉnh sửa thông tin</h2>
    <form action="update-profile.php" method="post">
      <div class="form-group">
        <label>Họ</label>
        <input type="text" name="ho" value="<?php echo htmlspecialchars($user['ho']); ?>" required placeholder="Nhập họ của bạn">
      </div>

      <div class="form-group">
        <label>Tên</label>
        <input type="text" name="ten" value="<?php echo htmlspecialchars($user['ten']); ?>" required placeholder="Nhập tên của bạn">
      </div>

      <div class="form-group">
        <label>Số điện thoại</label>
        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required placeholder="Nhập số điện thoại">
      </div>

      <div class="form-group">
        <label>Địa chỉ</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required placeholder="Nhập địa chỉ">
      </div>

      <button type="submit" class="btn-main">Lưu thay đổi</button>
      <!-- Nút Hủy -->
      <button type="button" class="btn-cancel" onclick="window.location.href='profile.php'">Hủy</button>
    </form>
  </div>
</body>

</html>