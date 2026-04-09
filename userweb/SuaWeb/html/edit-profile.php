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

    input.invalid {
      border-color: #e60023;
      background-color: #fff0f0;
    }

    .hint {
      font-size: 11px;
      color: #888;
      margin-top: 4px;
    }

    .error-msg {
      color: #e60023;
      font-size: 12px;
      margin-top: 5px;
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

    @media (max-width: 500px) {
      .edit-card {
        padding: 25px 20px;
      }
    }
  </style>
</head>

<body>
  <div class="edit-card">
    <h2>Chỉnh sửa thông tin</h2>

    <?php
    // Hiển thị thông báo lỗi nếu có (từ session, do update-profile.php gửi về)
    if (isset($_SESSION['edit_error'])) {
        echo '<div class="error-msg" style="margin-bottom:15px;">' . htmlspecialchars($_SESSION['edit_error']) . '</div>';
        unset($_SESSION['edit_error']);
    }
    if (isset($_SESSION['edit_success'])) {
        echo '<div class="error-msg" style="color:green; margin-bottom:15px;">' . htmlspecialchars($_SESSION['edit_success']) . '</div>';
        unset($_SESSION['edit_success']);
    }
    ?>

    <form action="update-profile.php" method="post" id="editForm">
      <div class="form-group">
        <label>Họ</label>
        <input type="text" name="ho" id="ho" value="<?php echo htmlspecialchars($user['ho']); ?>" required placeholder="Nhập họ của bạn">
        <div class="hint">Không chứa số hoặc ký tự đặc biệt</div>
        <div id="hoError" class="error-msg"></div>
      </div>

      <div class="form-group">
        <label>Tên</label>
        <input type="text" name="ten" id="ten" value="<?php echo htmlspecialchars($user['ten']); ?>" required placeholder="Nhập tên của bạn">
        <div class="hint">Không chứa số hoặc ký tự đặc biệt</div>
        <div id="tenError" class="error-msg"></div>
      </div>

      <div class="form-group">
        <label>Số điện thoại</label>
        <input type="tel" name="sdt" id="sdt" value="<?php echo htmlspecialchars($user['sdt']); ?>" required placeholder="Nhập số điện thoại">
        <div class="hint">Bắt đầu bằng 0, đúng 10 chữ số</div>
        <div id="sdtError" class="error-msg"></div>
      </div>

      <div class="form-group">
        <label>Địa chỉ</label>
        <input type="text" name="diachi" id="diachi" value="<?php echo htmlspecialchars($user['diachi']); ?>" required placeholder="Nhập địa chỉ">
        <div class="hint">Không chứa ký tự đặc biệt (cho phép chữ, số, khoảng trắng và dấu /)</div>
        <div id="diachiError" class="error-msg"></div>
      </div>

      <button type="submit" class="btn-main">Lưu thay đổi</button>
      <button type="button" class="btn-cancel" onclick="window.location.href='profile.php'">Hủy</button>
    </form>
  </div>

  <script>
    // Hàm kiểm tra họ hoặc tên (chỉ chữ và khoảng trắng, có ít nhất 1 ký tự)
    function validateNamePart(value) {
      return /^[\p{L}\s]+$/u.test(value);
    }

    // Hàm kiểm tra số điện thoại
    function validatePhone(value) {
      return /^0[0-9]{9}$/.test(value);
    }

    // Hàm kiểm tra địa chỉ (chữ, số, khoảng trắng, dấu /)
    function validateAddress(value) {
      return /^[\p{L}\p{N}\s\/]+$/u.test(value);
    }

    // Lấy các phần tử
    const hoInput = document.getElementById('ho');
    const tenInput = document.getElementById('ten');
    const sdtInput = document.getElementById('sdt');
    const diachiInput = document.getElementById('diachi');

    const hoError = document.getElementById('hoError');
    const tenError = document.getElementById('tenError');
    const sdtError = document.getElementById('sdtError');
    const diachiError = document.getElementById('diachiError');

    // Real-time validation
    function validateHo() {
      const val = hoInput.value.trim();
      if (val === '') {
        hoError.textContent = 'Họ không được để trống';
        hoInput.classList.add('invalid');
        return false;
      } else if (!validateNamePart(val)) {
        hoError.textContent = 'Họ không được chứa số hoặc ký tự đặc biệt';
        hoInput.classList.add('invalid');
        return false;
      } else {
        hoError.textContent = '';
        hoInput.classList.remove('invalid');
        return true;
      }
    }

    function validateTen() {
      const val = tenInput.value.trim();
      if (val === '') {
        tenError.textContent = 'Tên không được để trống';
        tenInput.classList.add('invalid');
        return false;
      } else if (!validateNamePart(val)) {
        tenError.textContent = 'Tên không được chứa số hoặc ký tự đặc biệt';
        tenInput.classList.add('invalid');
        return false;
      } else {
        tenError.textContent = '';
        tenInput.classList.remove('invalid');
        return true;
      }
    }

    function validateSdt() {
      const val = sdtInput.value.trim();
      if (val === '') {
        sdtError.textContent = 'Số điện thoại không được để trống';
        sdtInput.classList.add('invalid');
        return false;
      } else if (!validatePhone(val)) {
        sdtError.textContent = 'Số điện thoại phải bắt đầu bằng 0 và có đúng 10 chữ số';
        sdtInput.classList.add('invalid');
        return false;
      } else {
        sdtError.textContent = '';
        sdtInput.classList.remove('invalid');
        return true;
      }
    }

    function validateDiachi() {
      const val = diachiInput.value.trim();
      if (val === '') {
        diachiError.textContent = 'Địa chỉ không được để trống';
        diachiInput.classList.add('invalid');
        return false;
      } else if (!validateAddress(val)) {
        diachiError.textContent = 'Địa chỉ không được chứa ký tự đặc biệt (chỉ cho phép chữ, số, khoảng trắng và dấu /)';
        diachiInput.classList.add('invalid');
        return false;
      } else {
        diachiError.textContent = '';
        diachiInput.classList.remove('invalid');
        return true;
      }
    }

    hoInput.addEventListener('input', validateHo);
    tenInput.addEventListener('input', validateTen);
    sdtInput.addEventListener('input', validateSdt);
    diachiInput.addEventListener('input', validateDiachi);

    // Kiểm tra trước khi submit
    document.getElementById('editForm').addEventListener('submit', function(e) {
      const isHoValid = validateHo();
      const isTenValid = validateTen();
      const isSdtValid = validateSdt();
      const isDiachiValid = validateDiachi();

      if (!isHoValid || !isTenValid || !isSdtValid || !isDiachiValid) {
        e.preventDefault();
        alert('Vui lòng sửa các lỗi trong form trước khi lưu.');
      }
    });
  </script>
</body>

</html>