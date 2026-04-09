<?php
require_once __DIR__ . '/../../../config.php';
session_start();

$user_id = $_SESSION['user_id'];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Giỏ Hàng</title>
    <link rel="stylesheet" href="../css/cart.css" />
</head>
<body>

<header>GIỎ HÀNG CỦA BẠN</header>

<div style="margin: 20px;">
    <button class="back-btn" onclick="window.location.href='products.php'">
        ← Quay về trang chủ
    </button>
</div>

<!-- Bọc trong form để gửi selected[] sang checkout.php -->
<form action="checkout.php" method="POST">
    <table>
        <thead>
            <tr>
                <th>
                    <input type="checkbox" id="check-all"> Chọn tất cả
                </th>
                <th>Sản Phẩm</th>
                <th>Đơn Giá</th>
                <th>Số Lượng</th>
                <th>Số Tiền</th>
                <th>Thao Tác</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $sql = "SELECT cart.id, shop.products.name, shop.products.price, shop.products.image, cart.quantity 
                    FROM cart 
                    JOIN shop.products ON cart.product_id = shop.products.id 
                    WHERE cart.user_id = $user_id";

            $result = mysqli_query($conn, $sql);

            while ($row = mysqli_fetch_assoc($result)) {
                $thanhtien = $row['price'] * $row['quantity'];
            ?>
                <tr>
                    <td>
                        <input 
                            type="checkbox" 
                            class="item-check" 
                            name="selected[]" 
                            value="<?php echo $row['id']; ?>" 
                            data-thanhtien="<?php echo $thanhtien; ?>"
                        >
                    </td>

                    <td class="product-info">
                        <img src="<?php echo $row['image']; ?>" />
                        <div><?php echo $row['name']; ?></div>
                    </td>

                    <td><?php echo number_format($row['price']); ?>₫</td>

                    <td>
                        <div class="quantity">
                            <a href="update_cart.php?id=<?php echo $row['id']; ?>&type=decrease">-</a>
                            <input type="number" value="<?php echo $row['quantity']; ?>" readonly>
                            <a href="update_cart.php?id=<?php echo $row['id']; ?>&type=increase">+</a>
                        </div>
                    </td>

                    <td><?php echo number_format($thanhtien); ?>₫</td>

                    <td>
                        <a href="delete_cart.php?id=<?php echo $row['id']; ?>">
                            <button class="delete-btn" type="button">Xóa</button>
                        </a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="total">
        Tổng cộng: <span id="total-display">0₫</span>
    </div>

    <div class="checkout">
        <button type="submit">Tiến hành đặt hàng</button>
    </div>
</form>

<script>
function tinhTong() {
    let total = 0;

    document.querySelectorAll('.item-check:checked').forEach(cb => {
        total += parseFloat(cb.dataset.thanhtien);
    });

    document.getElementById('total-display').textContent =
        total.toLocaleString('vi-VN') + '₫';
}

document.getElementById('check-all').addEventListener('change', function() {
    document.querySelectorAll('.item-check').forEach(cb => {
        cb.checked = this.checked;
    });
    tinhTong();
});

document.querySelectorAll('.item-check').forEach(cb => {
    cb.addEventListener('change', tinhTong);
});

// Chặn submit nếu chưa chọn gì
document.querySelector('form').addEventListener('submit', function(e) {
    if (document.querySelectorAll('.item-check:checked').length === 0) {
        e.preventDefault();
        alert('Vui lòng chọn ít nhất 1 sản phẩm!');
    }
});
</script>

</body>
</html>