<?php
session_start();
include("../../../config.php");

// 🔒 CHECK LOGIN
if (!isset($_SESSION['adminLoggedIn'])) {
    header("Location: admin-login.php");
    exit;
}

// 🔥 THÊM SẢN PHẨM
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $category_id = intval($_POST['category_id']);
    $description = $_POST['description'];
    $status = intval($_POST['status']);

    $unit = $_POST['unit'];
    $quantity = intval($_POST['quantity']);
    $cost_price = intval($_POST['cost_price']);
    $profit_rate = intval($_POST['profit_rate']);

    $price = $cost_price + ($cost_price * $profit_rate / 100);

    $image = "";
    if (!empty($_FILES['image']['name'])) {
        $target = "../img/" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $image = $target;
    }

    $conn->query("INSERT INTO products 
    (name, category_id, description, image, status, unit, quantity, cost_price, profit_rate, price)
    VALUES 
    ('$name', $category_id, '$description', '$image', $status, '$unit', $quantity, $cost_price, $profit_rate, $price)");

    header("Location: products.php");
    exit;
}

// 🔥 UPDATE
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $description = $_POST['description'];
    $status = intval($_POST['status']);
    $category_id = intval($_POST['category_id']);

    $unit = $_POST['unit'];
    $quantity = intval($_POST['quantity']);
    $cost_price = intval($_POST['cost_price']);
    $profit_rate = intval($_POST['profit_rate']);

    $price = $cost_price + ($cost_price * $profit_rate / 100);

    if (!empty($_FILES['image']['name'])) {
        $target = "../img/" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);

        $conn->query("UPDATE products SET 
            name='$name',
            description='$description',
            status=$status,
            category_id=$category_id,
            image='$target',
            unit='$unit',
            quantity=$quantity,
            cost_price=$cost_price,
            profit_rate=$profit_rate,
            price=$price
            WHERE id=$id");
    } else {
        $conn->query("UPDATE products SET 
            name='$name',
            description='$description',
            status=$status,
            category_id=$category_id,
            unit='$unit',
            quantity=$quantity,
            cost_price=$cost_price,
            profit_rate=$profit_rate,
            price=$price
            WHERE id=$id");
    }

    header("Location: products.php");
    exit;
}

// 🔥 DELETE (ĐÚNG ĐỀ)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $check = $conn->query("SELECT quantity FROM products WHERE id = $id");
    $row = $check->fetch_assoc();

    if ($row['quantity'] > 0) {
        $conn->query("UPDATE products SET status = 0 WHERE id = $id");
    } else {
        $conn->query("DELETE FROM products WHERE id = $id");
    }

    header("Location: products.php");
    exit;
}

// 🔍 SEARCH
$keyword = "";
if (isset($_GET['keyword'])) {
    $keyword = $conn->real_escape_string($_GET['keyword']);
}

// LOAD CATEGORY
$categories = $conn->query("SELECT * FROM categories WHERE status = 1");

// LOAD PRODUCT
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.name LIKE '%$keyword%'";

$result = $conn->query($sql);
?>

<!doctype html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Danh Mục Sản Phẩm</title>
    <link rel="stylesheet" href="../css/style.css" />
</head>

<body>

    <div class="sidebar">
        <div class="logo">
            <h2>Muit Store</h2>
            <p>Hệ Thống Quản Lý</p>
        </div>

        <nav class="menu">
            <a href="index.php" class="menu-item">📊 Tổng quan</a>
            <a href="customers.php" class="menu-item">👥 Quản Lý Khách Hàng</a>
            <a href="categories.php" class="menu-item">📂 Loại Sản Phẩm</a>
            <a href="products.php" class="menu-item active">💻 Danh Mục Sản Phẩm</a>
            <a href="import.php" class="menu-item">🚚 Quản Lý Nhập Hàng</a>
            <a href="price.php" class="menu-item">🏷️ Quản Lý Giá Bán</a>
            <a href="orders.php" class="menu-item">🛒 Quản Lý Đơn Hàng</a>
            <a href="inventory.php" class="menu-item">📦 Quản Lý Tồn Kho</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <h1>Danh mục sản phẩm</h1>

            <div class="info">
                <img src="../img/logo.png" />
                <span><?php echo $_SESSION['adminUser']['name']; ?></span>
                <a href="logout.php"><button>Đăng xuất</button></a>
            </div>
        </div>

        <div class="container">
            <div class="card">
                <div class="title">
                    <h3>Tìm kiếm sản phẩm</h3>
                </div>

                <form method="GET" class="search">
                    <input type="text" name="keyword" value="<?php echo $keyword; ?>"
                        placeholder="🔍 Tìm kiếm ..." class="input-field" />

                    <button class="btn-search">Tìm kiếm</button>

                    <a href="products.php">
                        <button type="button" class="btn-reset">Làm mới</button>
                    </a>

                    <a href="#popup-them">
                        <button type="button" class="luu">Thêm sản phẩm</button>
                    </a>
                </form>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Mã</th>
                        <th>Ảnh</th>
                        <th>Tên</th>
                        <th>Loại</th>
                        <th>Mô tả</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td>#SP<?php echo $row['id']; ?></td>

                            <td><img src="<?php echo $row['image']; ?>" width="60"></td>

                            <td><?php echo $row['name']; ?></td>

                            <td><?php echo $row['category_name']; ?></td>

                            <td><?php echo $row['description']; ?></td>

                            <td>
                                <?php if ($row['status'] == 1) { ?>
                                    <span class="status status-active">Hiển thị</span>
                                <?php } else { ?>
                                    <span class="status status-hidden">Ẩn</span>
                                <?php } ?>
                            </td>

                            <td>
                                <a href="#popup-sua-<?php echo $row['id']; ?>">
                                    <button class="sua">Sửa</button>
                                </a>

                                <a href="products.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Xóa?')">
                                    <button class="xoa">Xóa</button>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- POPUP THÊM -->
    <div id="popup-them" class="popup-overlay">
        <div class="popup">
            <a href="#" class="close-btn">✖</a>

            <h2>Thêm sản phẩm</h2>

            <form method="POST" enctype="multipart/form-data">

                <h3>Loại</h3>
                <select name="category_id" class="input-field">
                    <?php while ($c = $categories->fetch_assoc()) { ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                    <?php } ?>
                </select>

                <h3>Tên</h3>
                <input type="text" name="name" class="input-field" required>

                <h3>Ảnh</h3>
                <input type="file" name="image" class="input-field">

                <h3>Mô tả</h3>
                <textarea name="description" class="input-field"></textarea>

                <h3>Đơn vị</h3>
                <input type="text" name="unit" class="input-field">

                <h3>Số lượng</h3>
                <input type="number" name="quantity" class="input-field">

                <h3>Giá vốn</h3>
                <input type="number" name="cost_price" class="input-field">

                <h3>% Lợi nhuận</h3>
                <input type="number" name="profit_rate" class="input-field">

                <h3>Trạng thái</h3>
                <select name="status" class="input-field">
                    <option value="1">Hiển thị</option>
                    <option value="0">Ẩn</option>
                </select>

                <div class="button_box">
                    <button type="submit" name="add" class="luu">Thêm</button>
                </div>

            </form>
        </div>
    </div>



    <?php
    $result2 = $conn->query($sql);
    while ($row = $result2->fetch_assoc()) {
    ?>

        <!-- POPUP SỬA -->
        <div id="popup-sua-<?php echo $row['id']; ?>" class="popup-overlay">
            <div class="popup">
                <a href="#" class="close-btn">✖</a>

                <h2>Sửa sản phẩm</h2>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

                    <h3>Loại</h3>
                    <select name="category_id" class="input-field">
                        <?php
                        $cat2 = $conn->query("SELECT * FROM categories WHERE status=1");
                        while ($c = $cat2->fetch_assoc()) {
                        ?>
                            <option value="<?php echo $c['id']; ?>"
                                <?php if ($c['id'] == $row['category_id']) echo "selected"; ?>>
                                <?php echo $c['name']; ?>
                            </option>
                        <?php } ?>
                    </select>

                    <h3>Ảnh hiện tại</h3>
                    <img src="<?php echo $row['image']; ?>" width="100">

                    <h3>Đổi ảnh</h3>
                    <input type="file" name="image" class="input-field">

                    <h3>Tên</h3>
                    <input type="text" name="name" value="<?php echo $row['name']; ?>" class="input-field">

                    <h3>Mô tả</h3>
                    <textarea name="description" class="input-field"><?php echo $row['description']; ?></textarea>

                    <h3>Đơn vị</h3>
                    <input type="text" name="unit"
                        value="<?php echo !empty($row['unit']) ? $row['unit'] : 'chiếc'; ?>"
                        class="input-field">

                    <h3>Số lượng</h3>
                    <input type="number" name="quantity"
                        value="<?php echo ($row['quantity'] > 0) ? $row['quantity'] : 1; ?>"
                        class="input-field">
                    <h3>Giá vốn</h3>
                    <input type="number" name="cost_price" value="<?php echo $row['cost_price']; ?>" class="input-field">

                    <h3>% Lợi nhuận</h3>
                    <input type="number" name="profit_rate" value="<?php echo $row['profit_rate']; ?>" class="input-field">

                    <h3>Trạng thái</h3>
                    <select name="status" class="input-field">
                        <option value="1" <?php if ($row['status'] == 1) echo "selected"; ?>>Hiển thị</option>
                        <option value="0" <?php if ($row['status'] == 0) echo "selected"; ?>>Ẩn</option>
                    </select>

                    <div class="button_box">
                        <button type="submit" name="update" class="luu">Lưu</button>
                    </div>
                </form>
            </div>
        </div>

    <?php } ?>

</body>

</html>