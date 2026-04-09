<?php
session_start();
include("../../../config.php");

// 🔒 CHECK LOGIN
if (!isset($_SESSION['adminLoggedIn'])) {
    header("Location: admin-login.php");
    exit;
}

// Hàm kiểm tra ký tự đặc biệt
function hasSpecialChars($str) {
    return preg_match('/[^\p{L}\p{N}\s\.\-_]/u', $str);
}

// 🔥 THÊM SẢN PHẨM
if (isset($_POST['add'])) {
    $errors = [];
    
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errors[] = "Tên sản phẩm không được để trống";
    } elseif (hasSpecialChars($name)) {
        $errors[] = "Tên sản phẩm không được chứa ký tự đặc biệt";
    }
    
    $description = trim($_POST['description']);
    if (!empty($description) && hasSpecialChars($description)) {
        $errors[] = "Mô tả không được chứa ký tự đặc biệt";
    }
    
    $cpu = trim($_POST['cpu']);
    if (!empty($cpu) && hasSpecialChars($cpu)) {
        $errors[] = "CPU không được chứa ký tự đặc biệt";
    }
    
    $ram = trim($_POST['ram']);
    if (!empty($ram) && hasSpecialChars($ram)) {
        $errors[] = "RAM không được chứa ký tự đặc biệt";
    }
    
    $storage = trim($_POST['storage']);
    if (!empty($storage) && hasSpecialChars($storage)) {
        $errors[] = "Storage không được chứa ký tự đặc biệt";
    }
    
    $gpu = trim($_POST['gpu']);
    if (!empty($gpu) && hasSpecialChars($gpu)) {
        $errors[] = "GPU không được chứa ký tự đặc biệt";
    }
    
    $screen = trim($_POST['screen']);
    if (!empty($screen) && hasSpecialChars($screen)) {
        $errors[] = "Screen không được chứa ký tự đặc biệt";
    }
    
    $weight = trim($_POST['weight']);
    if (!empty($weight) && hasSpecialChars($weight)) {
        $errors[] = "Weight không được chứa ký tự đặc biệt";
    }
    
    $battery = trim($_POST['battery']);
    if (!empty($battery) && hasSpecialChars($battery)) {
        $errors[] = "Battery không được chứa ký tự đặc biệt";
    }
    
    $os = trim($_POST['os']);
    if (!empty($os) && hasSpecialChars($os)) {
        $errors[] = "OS không được chứa ký tự đặc biệt";
    }
    
    $quantity = $_POST['quantity'];
    if (!is_numeric($quantity)) {
        $errors[] = "Số lượng phải là số";
    } else {
        $quantity = intval($quantity);
        if ($quantity < 0) {
            $errors[] = "Số lượng không được âm";
        }
    }
    
    $cost_price = $_POST['cost_price'];
    if (!is_numeric($cost_price)) {
        $errors[] = "Giá vốn phải là số";
    } else {
        $cost_price = floatval($cost_price);
        if ($cost_price < 0) {
            $errors[] = "Giá vốn không được âm";
        }
    }
    
    if (!empty($errors)) {
        echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
        exit;
    }
    
    $category_id = intval($_POST['category_id']);
    $status = intval($_POST['status']);
    $unit = $_POST['unit'];
    $profit_rate = floatval($_POST['profit_rate']);
    $price = $cost_price + ($cost_price * $profit_rate / 100);
    
    $image = "";
    if (!empty($_FILES['image']['name'])) {
        $filename = basename($_FILES['image']['name']);
        $uploadDir = __DIR__ . "/../../../uploads/img/";
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
        $image = "../../../uploads/img/" . $filename;
    }
    
    $name = $conn->real_escape_string($name);
    $description = $conn->real_escape_string($description);
    $cpu = $conn->real_escape_string($cpu);
    $ram = $conn->real_escape_string($ram);
    $storage = $conn->real_escape_string($storage);
    $gpu = $conn->real_escape_string($gpu);
    $screen = $conn->real_escape_string($screen);
    $weight = $conn->real_escape_string($weight);
    $battery = $conn->real_escape_string($battery);
    $os = $conn->real_escape_string($os);
    $unit = $conn->real_escape_string($unit);
    
    $conn->query("INSERT INTO products 
    (name, category_id, description, image, status, unit, quantity, cost_price, profit_rate, price, cpu, ram, storage, gpu, screen, weight, battery, os)
    VALUES 
    ('$name', $category_id, '$description', '$image', $status, '$unit', $quantity, $cost_price, $profit_rate, $price, '$cpu', '$ram', '$storage', '$gpu', '$screen', '$weight', '$battery', '$os')");
    
    header("Location: products.php");
    exit;
}

// 🔥 UPDATE
if (isset($_POST['update'])) {
    $errors = [];
    
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errors[] = "Tên sản phẩm không được để trống";
    } elseif (hasSpecialChars($name)) {
        $errors[] = "Tên sản phẩm không được chứa ký tự đặc biệt";
    }
    
    $description = trim($_POST['description']);
    if (!empty($description) && hasSpecialChars($description)) {
        $errors[] = "Mô tả không được chứa ký tự đặc biệt";
    }
    
    $cpu = trim($_POST['cpu']);
    if (!empty($cpu) && hasSpecialChars($cpu)) {
        $errors[] = "CPU không được chứa ký tự đặc biệt";
    }
    
    $ram = trim($_POST['ram']);
    if (!empty($ram) && hasSpecialChars($ram)) {
        $errors[] = "RAM không được chứa ký tự đặc biệt";
    }
    
    $storage = trim($_POST['storage']);
    if (!empty($storage) && hasSpecialChars($storage)) {
        $errors[] = "Storage không được chứa ký tự đặc biệt";
    }
    
    $gpu = trim($_POST['gpu']);
    if (!empty($gpu) && hasSpecialChars($gpu)) {
        $errors[] = "GPU không được chứa ký tự đặc biệt";
    }
    
    $screen = trim($_POST['screen']);
    if (!empty($screen) && hasSpecialChars($screen)) {
        $errors[] = "Screen không được chứa ký tự đặc biệt";
    }
    
    $weight = trim($_POST['weight']);
    if (!empty($weight) && hasSpecialChars($weight)) {
        $errors[] = "Weight không được chứa ký tự đặc biệt";
    }
    
    $battery = trim($_POST['battery']);
    if (!empty($battery) && hasSpecialChars($battery)) {
        $errors[] = "Battery không được chứa ký tự đặc biệt";
    }
    
    $os = trim($_POST['os']);
    if (!empty($os) && hasSpecialChars($os)) {
        $errors[] = "OS không được chứa ký tự đặc biệt";
    }
    
    $quantity = $_POST['quantity'];
    if (!is_numeric($quantity)) {
        $errors[] = "Số lượng phải là số";
    } else {
        $quantity = intval($quantity);
        if ($quantity < 0) {
            $errors[] = "Số lượng không được âm";
        }
    }
    
    $cost_price = $_POST['cost_price'];
    if (!is_numeric($cost_price)) {
        $errors[] = "Giá vốn phải là số";
    } else {
        $cost_price = floatval($cost_price);
        if ($cost_price < 0) {
            $errors[] = "Giá vốn không được âm";
        }
    }
    
    if (!empty($errors)) {
        echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
        exit;
    }
    
    $id = intval($_POST['id']);
    $status = intval($_POST['status']);
    $category_id = intval($_POST['category_id']);
    $unit = $_POST['unit'];
    $profit_rate = floatval($_POST['profit_rate']);
    $price = $cost_price + ($cost_price * $profit_rate / 100);
    
    $name = $conn->real_escape_string($name);
    $description = $conn->real_escape_string($description);
    $cpu = $conn->real_escape_string($cpu);
    $ram = $conn->real_escape_string($ram);
    $storage = $conn->real_escape_string($storage);
    $gpu = $conn->real_escape_string($gpu);
    $screen = $conn->real_escape_string($screen);
    $weight = $conn->real_escape_string($weight);
    $battery = $conn->real_escape_string($battery);
    $os = $conn->real_escape_string($os);
    $unit = $conn->real_escape_string($unit);
    
    if (!empty($_FILES['image']['name'])) {
        $newFilename = basename($_FILES['image']['name']);
        $uploadDir = __DIR__ . "/../../../uploads/img/";
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFilename);
        $newImage = "../../../uploads/img/" . $newFilename;
        $conn->query("UPDATE products SET 
            name='$name', description='$description', status=$status, category_id=$category_id,
            image='$newImage', unit='$unit', quantity=$quantity, cost_price=$cost_price,
            profit_rate=$profit_rate, price=$price, cpu='$cpu', ram='$ram', storage='$storage',
            gpu='$gpu', screen='$screen', weight='$weight', battery='$battery', os='$os'
            WHERE id=$id");
    } else {
        $conn->query("UPDATE products SET 
            name='$name', description='$description', status=$status, category_id=$category_id,
            unit='$unit', quantity=$quantity, cost_price=$cost_price, profit_rate=$profit_rate,
            price=$price, cpu='$cpu', ram='$ram', storage='$storage', gpu='$gpu',
            screen='$screen', weight='$weight', battery='$battery', os='$os'
            WHERE id=$id");
    }
    
    header("Location: products.php");
    exit;
}

// 🔥 DELETE
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

// 🔓 MỞ KHÓA
if (isset($_GET['unlock'])) {
    $id = intval($_GET['unlock']);
    $conn->query("UPDATE products SET status = 1 WHERE id = $id");
    header("Location: products.php");
    exit;
}

// 🔍 SEARCH & FILTER
$keyword = isset($_GET['keyword']) ? $conn->real_escape_string($_GET['keyword']) : "";
$statusFilter = $_GET['status_filter'] ?? '';

$categories = $conn->query("SELECT * FROM categories WHERE status = 1");

$sql = "SELECT p.*, c.name AS category_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        WHERE p.name LIKE '%$keyword%'";

if ($statusFilter !== '') {
    $sql .= " AND p.status = " . intval($statusFilter);
}
$sql .= " ORDER BY p.id DESC";
$result = $conn->query($sql);
?>

<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Danh Mục Sản Phẩm</title>
    <link rel="stylesheet" href="../css/style.css" />
    <style>
        .filter-buttons { display: flex; gap: 10px; margin-bottom: 15px; }
        .filter-btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 14px; }
        .filter-btn.active { background: #3b82f6; color: white; }
        .filter-btn.all { background: #6b7280; color: white; }
        .filter-btn.visible { background: #10b981; color: white; }
        .filter-btn.hidden { background: #f59e0b; color: white; }
        .btn-unlock { background: #8b5cf6; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; margin-left: 5px; }
        .btn-unlock:hover { background: #7c3aed; }
        .error-message { color: #dc2626; font-size: 12px; margin-top: 4px; display: none; }
        .input-error { border: 1px solid #dc2626 !important; background-color: #fef2f2 !important; }
        .form-group { margin-bottom: 15px; }
    </style>
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
            <div class="title"><h3>Tìm kiếm sản phẩm</h3></div>
            <form method="GET" class="search">
                <input type="text" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="🔍 Tìm kiếm ..." class="input-field" />
                <button class="btn-search">Tìm kiếm</button>
                <a href="products.php"><button type="button" class="btn-reset">Làm mới</button></a>
                <a href="#popup-them"><button type="button" class="luu">+ Thêm sản phẩm</button></a>
            </form>
            <div class="filter-buttons" style="margin-top: 15px;">
                <a href="products.php?keyword=<?php echo urlencode($keyword); ?>&status_filter=" class="filter-btn all <?php echo $statusFilter === '' ? 'active' : ''; ?>"> Tất cả</a>
                <a href="products.php?keyword=<?php echo urlencode($keyword); ?>&status_filter=1" class="filter-btn visible <?php echo $statusFilter === '1' ? 'active' : ''; ?>"> Đang hiển thị</a>
                <a href="products.php?keyword=<?php echo urlencode($keyword); ?>&status_filter=0" class="filter-btn hidden <?php echo $statusFilter === '0' ? 'active' : ''; ?>"> Đã ẩn</a>
            </div>
        </div>

        <table>
            <thead>
                <tr><th>Mã</th><th>Ảnh</th><th>Tên</th><th>Loại</th><th>Mô tả</th><th>Tồn kho</th><th>Trạng thái</th><th>Thao tác</th></tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>#SP<?php echo $row['id']; ?></td>
                            <td><?php if (!empty($row['image'])): ?><img src="<?php echo htmlspecialchars($row['image']); ?>" width="50" height="50" style="object-fit:cover;"><?php else: ?><span style="color:#999">No image</span><?php endif; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                            <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars(substr($row['description'], 0, 50)); ?></td>
                            <td><?php echo number_format($row['quantity']); ?></td>
                            <td><?php echo $row['status'] == 1 ? '<span class="status status-active"> Hiển thị</span>' : '<span class="status status-hidden"> Đã ẩn</span>'; ?></td>
                            <td style="white-space: nowrap;">
                                <a href="#popup-sua-<?php echo $row['id']; ?>"><button class="sua"> Sửa</button></a>
                                <?php if ($row['status'] == 0): ?>
                                    <a href="products.php?unlock=<?php echo $row['id']; ?>&keyword=<?php echo urlencode($keyword); ?>&status_filter=<?php echo $statusFilter; ?>" onclick="return confirm('Mở khóa sản phẩm này?')"><button class="btn-unlock"> Mở khóa</button></a>
                                <?php else: ?>
                                    <a href="products.php?delete=<?php echo $row['id']; ?>&keyword=<?php echo urlencode($keyword); ?>&status_filter=<?php echo $statusFilter; ?>" onclick="return confirm('Xóa/Ẩn sản phẩm này?')"><button class="xoa"> Xóa/Ẩn</button></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center; padding:40px;">Không tìm thấy sản phẩm nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- POPUP THÊM -->
<div id="popup-them" class="popup-overlay">
    <div class="popup" style="max-width:600px; width:90%; max-height:80vh; overflow-y:auto;">
        <a href="#" class="close-btn">✖</a>
        <h2>➕ Thêm sản phẩm</h2>
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm(this)">
            <div class="form-group">
                <h3>Loại</h3>
                <select name="category_id" class="input-field" required>
                    <?php 
                    $categories->data_seek(0);
                    while ($c = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo $c['name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <h3>Tên sản phẩm <span style="color:red;">*</span></h3>
                <input type="text" name="name" class="input-field" required onblur="validateField(this, 'name')">
                <div class="error-message" id="error-name"></div>
            </div>
            <div class="form-group">
                <h3>Ảnh</h3>
                <input type="file" name="image" class="input-field" accept="image/*">
            </div>
            <div class="form-group">
                <h3>Mô tả</h3>
                <textarea name="description" class="input-field" rows="3" onblur="validateField(this, 'description')"></textarea>
                <div class="error-message" id="error-description"></div>
            </div>
            <h3>Thông số kỹ thuật</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div class="form-group"><h4>CPU</h4><input type="text" name="cpu" class="input-field" onblur="validateField(this, 'cpu')"><div class="error-message" id="error-cpu"></div></div>
                <div class="form-group"><h4>RAM</h4><input type="text" name="ram" class="input-field" onblur="validateField(this, 'ram')"><div class="error-message" id="error-ram"></div></div>
                <div class="form-group"><h4>Storage</h4><input type="text" name="storage" class="input-field" onblur="validateField(this, 'storage')"><div class="error-message" id="error-storage"></div></div>
                <div class="form-group"><h4>GPU</h4><input type="text" name="gpu" class="input-field" onblur="validateField(this, 'gpu')"><div class="error-message" id="error-gpu"></div></div>
                <div class="form-group"><h4>Screen</h4><input type="text" name="screen" class="input-field" onblur="validateField(this, 'screen')"><div class="error-message" id="error-screen"></div></div>
                <div class="form-group"><h4>Weight</h4><input type="text" name="weight" class="input-field" onblur="validateField(this, 'weight')"><div class="error-message" id="error-weight"></div></div>
                <div class="form-group"><h4>Battery</h4><input type="text" name="battery" class="input-field" onblur="validateField(this, 'battery')"><div class="error-message" id="error-battery"></div></div>
                <div class="form-group"><h4>OS</h4><input type="text" name="os" class="input-field" onblur="validateField(this, 'os')"><div class="error-message" id="error-os"></div></div>
            </div>
            <div class="form-group"><h3>Đơn vị</h3><input type="text" name="unit" class="input-field" value="chiếc"></div>
            <div class="form-group"><h3>Số lượng tồn <span style="color:red;">*</span></h3><input type="number" name="quantity" class="input-field" value="0" min="0" onblur="validateNumber(this, 'quantity')"><div class="error-message" id="error-quantity"></div></div>
            <div class="form-group"><h3>Giá vốn <span style="color:red;">*</span></h3><input type="number" name="cost_price" class="input-field" value="0" min="0" onblur="validateNumber(this, 'cost_price')"><div class="error-message" id="error-cost_price"></div></div>
            <div class="form-group"><h3>% Lợi nhuận</h3><input type="number" name="profit_rate" class="input-field" value="30" step="0.5"></div>
            <div class="form-group"><h3>Trạng thái</h3><select name="status" class="input-field"><option value="1">Hiển thị</option><option value="0">Ẩn</option></select></div>
            <div class="button_box" style="margin-top:20px;"><button type="submit" name="add" class="luu">Thêm sản phẩm</button></div>
        </form>
    </div>
</div>

<?php
$result2 = $conn->query($sql);
while ($row = $result2->fetch_assoc()):
?>
<div id="popup-sua-<?php echo $row['id']; ?>" class="popup-overlay">
    <div class="popup" style="max-width:600px; width:90%; max-height:80vh; overflow-y:auto;">
        <a href="#" class="close-btn">✖</a>
        <h2>Sửa sản phẩm #SP<?php echo $row['id']; ?></h2>
        <form method="POST" enctype="multipart/form-data" onsubmit="return validateForm(this)">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <div class="form-group"><h3>Loại</h3><select name="category_id" class="input-field">
                <?php $cat2 = $conn->query("SELECT * FROM categories WHERE status=1");
                while ($c = $cat2->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>" <?php if ($c['id'] == $row['category_id']) echo "selected"; ?>><?php echo $c['name']; ?></option>
                <?php endwhile; ?>
            </select></div>
            <div class="form-group"><h3>Ảnh hiện tại</h3><?php if (!empty($row['image'])): ?><img src="<?php echo htmlspecialchars($row['image']); ?>" width="100" style="border-radius:8px;"><?php else: ?><p style="color:#999">Chưa có ảnh</p><?php endif; ?></div>
            <div class="form-group"><h3>Đổi ảnh</h3><input type="file" name="image" class="input-field" accept="image/*"></div>
            <div class="form-group"><h3>Tên sản phẩm <span style="color:red;">*</span></h3><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" class="input-field" required onblur="validateField(this, 'name')"><div class="error-message"></div></div>
            <div class="form-group"><h3>Mô tả</h3><textarea name="description" class="input-field" rows="3" onblur="validateField(this, 'description')"><?php echo htmlspecialchars($row['description']); ?></textarea><div class="error-message"></div></div>
            <h3>Thông số kỹ thuật</h3>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div class="form-group"><h4>CPU</h4><input type="text" name="cpu" value="<?php echo htmlspecialchars($row['cpu']); ?>" class="input-field" onblur="validateField(this, 'cpu')"><div class="error-message"></div></div>
                <div class="form-group"><h4>RAM</h4><input type="text" name="ram" value="<?php echo htmlspecialchars($row['ram']); ?>" class="input-field" onblur="validateField(this, 'ram')"><div class="error-message"></div></div>
                <div class="form-group"><h4>Storage</h4><input type="text" name="storage" value="<?php echo htmlspecialchars($row['storage']); ?>" class="input-field" onblur="validateField(this, 'storage')"><div class="error-message"></div></div>
                <div class="form-group"><h4>GPU</h4><input type="text" name="gpu" value="<?php echo htmlspecialchars($row['gpu']); ?>" class="input-field" onblur="validateField(this, 'gpu')"><div class="error-message"></div></div>
                <div class="form-group"><h4>Screen</h4><input type="text" name="screen" value="<?php echo htmlspecialchars($row['screen']); ?>" class="input-field" onblur="validateField(this, 'screen')"><div class="error-message"></div></div>
                <div class="form-group"><h4>Weight</h4><input type="text" name="weight" value="<?php echo htmlspecialchars($row['weight']); ?>" class="input-field" onblur="validateField(this, 'weight')"><div class="error-message"></div></div>
                <div class="form-group"><h4>Battery</h4><input type="text" name="battery" value="<?php echo htmlspecialchars($row['battery']); ?>" class="input-field" onblur="validateField(this, 'battery')"><div class="error-message"></div></div>
                <div class="form-group"><h4>OS</h4><input type="text" name="os" value="<?php echo htmlspecialchars($row['os']); ?>" class="input-field" onblur="validateField(this, 'os')"><div class="error-message"></div></div>
            </div>
            <div class="form-group"><h3>Đơn vị</h3><input type="text" name="unit" value="<?php echo !empty($row['unit']) ? $row['unit'] : 'chiếc'; ?>" class="input-field"></div>
            <div class="form-group"><h3>Số lượng tồn <span style="color:red;">*</span></h3><input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" class="input-field" min="0" onblur="validateNumber(this, 'quantity')"><div class="error-message"></div></div>
            <div class="form-group"><h3>Giá vốn <span style="color:red;">*</span></h3><input type="number" name="cost_price" value="<?php echo $row['cost_price']; ?>" class="input-field" min="0" onblur="validateNumber(this, 'cost_price')"><div class="error-message"></div></div>
            <div class="form-group"><h3>% Lợi nhuận</h3><input type="number" name="profit_rate" value="<?php echo $row['profit_rate']; ?>" class="input-field" step="0.5"></div>
            <div class="form-group"><h3>Trạng thái</h3><select name="status" class="input-field"><option value="1" <?php if ($row['status'] == 1) echo "selected"; ?>>Hiển thị</option><option value="0" <?php if ($row['status'] == 0) echo "selected"; ?>>Ẩn</option></select></div>
            <div class="button_box" style="margin-top:20px;"><button type="submit" name="update" class="luu">Lưu thay đổi</button></div>
        </form>
    </div>
</div>
<?php endwhile; ?>

<script>
function validateField(input, fieldName) {
    var errorDiv = input.parentElement.querySelector('.error-message');
    var value = input.value.trim();
    var specialCharsRegex = /[^\p{L}\p{N}\s\.\-_]/u;

    if (fieldName === 'name' && value === '') {
        errorDiv.textContent = 'Tên sản phẩm không được để trống';
        errorDiv.style.display = 'block';
        input.classList.add('input-error');
        return false;
    } else if (value !== '' && specialCharsRegex.test(value)) {
        errorDiv.textContent = 'Không được chứa ký tự đặc biệt';
        errorDiv.style.display = 'block';
        input.classList.add('input-error');
        return false;
    } else {
        errorDiv.style.display = 'none';
        input.classList.remove('input-error');
        input.classList.add('input-success');
        return true;
    }
}

function validateNumber(input, fieldName) {
    var errorDiv = input.parentElement.querySelector('.error-message');
    var value = parseFloat(input.value);

    if (isNaN(value)) {
        errorDiv.textContent = 'Phải là số';
        errorDiv.style.display = 'block';
        input.classList.add('input-error');
        return false;
    } else if (value < 0) {
        errorDiv.textContent = 'Không được âm';
        errorDiv.style.display = 'block';
        input.classList.add('input-error');
        return false;
    } else {
        errorDiv.style.display = 'none';
        input.classList.remove('input-error');
        input.classList.add('input-success');
        return true;
    }
}

// ✅ FIX: nhận form hiện tại làm tham số, dùng form.querySelector thay vì document.querySelector
function validateForm(form) {
    var fields = ['name', 'description', 'cpu', 'ram', 'storage', 'gpu', 'screen', 'weight', 'battery', 'os'];
    var isValid = true;

    for (var i = 0; i < fields.length; i++) {
        var input = form.querySelector('[name="' + fields[i] + '"]');
        if (input && input.value.trim() !== '') {
            if (!validateField(input, fields[i])) {
                isValid = false;
            }
        }
    }

    var quantityInput = form.querySelector('[name="quantity"]');
    if (quantityInput && !validateNumber(quantityInput, 'quantity')) {
        isValid = false;
    }

    var costInput = form.querySelector('[name="cost_price"]');
    if (costInput && !validateNumber(costInput, 'cost_price')) {
        isValid = false;
    }

    var nameInput = form.querySelector('[name="name"]');
    if (nameInput && nameInput.value.trim() === '') {
        alert('Tên sản phẩm không được để trống');
        nameInput.focus();
        return false;
    }

    return isValid;
}
</script>

</body>
</html>