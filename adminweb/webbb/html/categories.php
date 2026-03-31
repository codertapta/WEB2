<?php
session_start();
include("../../../config.php"); // Kết nối database

// 🔒 Kiểm tra admin đã login chưa
if (!isset($_SESSION['adminLoggedIn']) || $_SESSION['adminLoggedIn'] !== true) {
    header("Location: admin-login.php");
    exit;
}

// Xử lý POST thêm/sửa loại
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $code = $_POST['code'];
    $name = $_POST['name'];
    $status = $_POST['status'] === 'Hoạt động' ? 1 : 0;

    if ($id > 0) {
        // Sửa
        $stmt = $conn->prepare("UPDATE categories SET code=?, name=?, status=? WHERE id=?");
        $stmt->bind_param("ssii", $code, $name, $status, $id);
    } else {
        // Thêm
        $stmt = $conn->prepare("INSERT INTO categories (code, name, status) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $code, $name, $status);
    }
    $stmt->execute();
    $stmt->close();
    header("Location: categories.php");
    exit;
}

// Xóa category
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM categories WHERE id=$delete_id");
    header("Location: categories.php");
    exit;
}

// 🔍 Lấy danh sách category
$sql = "SELECT * FROM categories ORDER BY id ASC";
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
        /* --- Popup Overlay --- */
        .popup-overlay {
            position: fixed;
            /* cố định trên viewport */
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            /* nền mờ */
            display: flex;
            /* dùng flex để canh giữa popup */
            justify-content: center;
            /* canh ngang */
            align-items: center;
            /* canh dọc */
            z-index: 1000;
            /* nổi trên tất cả */
        }

        /* --- Popup chính --- */
        .popup {
            background: #fff;
            padding: 20px 30px;
            border-radius: 12px;
            max-width: 400px;
            width: 90%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            position: relative;
        }

        /* --- Nút đóng --- */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            text-decoration: none;
            /* bỏ gạch chân */
            font-size: 18px;
            color: #fff;
            cursor: pointer;
        }

        /* --- Link Sửa / Thêm / Xóa --- */
        a.head,
        a.sua,
        a.xoa {
            text-decoration: none;
            /* bỏ gạch chân */
            color: #fff;
            cursor: pointer;
            transition: color 0.2s;
        }

        a.head:hover,
        a.sua:hover,
        a.xoa:hover {
            color: #fff
        }
    </style>
</head>

<body>
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo">
            <h2>Muit Store</h2>
            <p>Hệ Thống Quản Lý</p>
        </div>
        <nav class="menu">
            <a href="index.php" class="menu-item"><span>📊</span> Tổng quan</a>
            <a href="./customers.php" class="menu-item"><span>👥</span> Quản Lý Khách Hàng</a>
            <a href="categories.php" class="menu-item active"><span>📂</span> Loại Sản Phẩm</a>
            <a href="./products.php" class="menu-item"><span>💻</span> Danh Mục Sản Phẩm</a>
            <a href="import.php" class="menu-item"><span>🚚</span> Quản Lý Nhập Hàng</a>
            <a href="price.php" class="menu-item"><span>🏷️</span> Quản Lý Giá Bán</a>
            <a href="orders.php" class="menu-item"><span>🛒</span> Quản Lý Đơn Hàng</a>
            <a href="inventory.php" class="menu-item"><span>📦</span> Quản Lý Tồn Kho</a>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
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
                    <a href="#popupForm" class="head" onclick="openForm();">+ Thêm loại sản phẩm</a>
                </div>

                <div class="search">
                    <input type="text" id="searchInput" placeholder="🔍 Tìm kiếm theo mã hoặc tên loại..." onkeyup="searchTable()" />
                </div>

                <!-- TABLE -->
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
                                    <td>#<?php echo $row['code']; ?></td>
                                    <td><?php echo $row['name']; ?></td>
                                    <td>
                                        <span class="status <?php echo ($row['status'] ? 'status-active' : 'status-hidden'); ?>">
                                            <?php echo ($row['status'] ? 'Hoạt động' : 'Ẩn'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="#popupForm" class="sua" onclick="editForm(<?php echo $row['id']; ?>, '<?php echo $row['code']; ?>', '<?php echo $row['name']; ?>', '<?php echo ($row['status'] ? 'Hoạt động' : 'Ẩn'); ?>')">Sửa</a>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="xoa" onclick="return confirm('Bạn có chắc muốn xóa?')">Xóa</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">Chưa có dữ liệu</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- POPUP FORM -->
    <div id="popupForm" class="popup-overlay" style="display:none;">
        <div class="popup">
            <a href="#" class="close-btn" onclick="closeForm();">✖</a>
            <h3>Thêm / Sửa Loại Sản Phẩm</h3>

            <form method="POST">
                <input type="hidden" name="id" id="categoryId" value="0" />
                <label>Mã loại</label>
                <input type="text" name="code" id="categoryCode" class="input-field" placeholder="Nhập mã loại" required />
                <label>Tên loại sản phẩm</label>
                <input type="text" name="name" id="categoryName" class="input-field" placeholder="Nhập tên loại" required />
                <label>Trạng thái</label>
                <select name="status" id="categoryStatus" class="input-field">
                    <option>Hoạt động</option>
                    <option>Ẩn</option>
                </select>
                <button type="submit" class="btn-save">Lưu thông tin</button>
            </form>
        </div>
    </div>

    <script>
        function logout() {
            sessionStorage.clear();
            window.location.href = "login.html";
        }

        // Mở popup để thêm
        function openForm() {
            document.getElementById('popupForm').style.display = 'flex';
            document.getElementById('categoryId').value = 0;
            document.getElementById('categoryCode').value = '';
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryStatus').value = 'Hoạt động';
        }

        // Mở popup để sửa
        function editForm(id, code, name, status) {
            document.getElementById('popupForm').style.display = 'flex';
            document.getElementById('categoryId').value = id;
            document.getElementById('categoryCode').value = code;
            document.getElementById('categoryName').value = name;
            document.getElementById('categoryStatus').value = status;
        }

        // Đóng popup
        function closeForm() {
            document.getElementById('popupForm').style.display = 'none';
        }

        // Tìm kiếm bảng
        function searchTable() {
            let input = document.getElementById('searchInput').value.toLowerCase();
            let table = document.getElementById('categoryTable');
            let trs = table.getElementsByTagName('tr');
            for (let i = 1; i < trs.length; i++) {
                let tdCode = trs[i].getElementsByTagName('td')[0].textContent.toLowerCase();
                let tdName = trs[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                trs[i].style.display = (tdCode.includes(input) || tdName.includes(input)) ? '' : 'none';
            }
        }
    </script>
</body>

</html>