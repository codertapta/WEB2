<?php
ob_start();
require __DIR__ . "/../../../config.php";

session_start();
$user_id = $_SESSION['user_id'];

// ===== DEBUG USER =====
echo "User ID: $user_id <br>";

// ===== LẤY DỮ LIỆU =====
$address_type = $_POST['address_type'] ?? 'old';
$new_address = $_POST['new_address'] ?? '';
$payment = $_POST['payment'] ?? 'cod';

// ===== LẤY ĐỊA CHỈ =====
if($address_type == 'new' && !empty($new_address)){
    $address = $new_address;
}else{
    $user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id=$user_id"));
    $address = $user['address'];
}

// ===== LẤY GIỎ HÀNG =====
$cart = mysqli_query($conn,"
SELECT cart.*, products.price 
FROM cart 
JOIN products ON cart.product_id = products.id
WHERE user_id=$user_id
");

// ❗ DEBUG CART
echo "Số sản phẩm trong cart: " . mysqli_num_rows($cart) . "<br>";

// nếu rỗng → dừng luôn
if(mysqli_num_rows($cart) == 0){
    echo "❌ Giỏ hàng trống!";
    exit;
}

// ===== TÍNH TỔNG =====
$total = 0;
while($c = mysqli_fetch_assoc($cart)){
    $total += $c['price'] * $c['quantity'];
}

// reset con trỏ
mysqli_data_seek($cart, 0);

// ===== INSERT ORDER =====
$insert_order = mysqli_query($conn,"
INSERT INTO orders(user_id,address,payment_method,total_price,order_date)
VALUES($user_id,'$address','$payment',$total,NOW())
");

if(!$insert_order){
    die("❌ Lỗi insert order: " . mysqli_error($conn));
}

$order_id = mysqli_insert_id($conn);

// ===== INSERT DETAILS =====
while($c = mysqli_fetch_assoc($cart)){
    $insert_detail = mysqli_query($conn,"
    INSERT INTO order_details(order_id,product_id,quantity,price)
    VALUES($order_id,{$c['product_id']},{$c['quantity']},{$c['price']})
    ");

    if(!$insert_detail){
        die("❌ Lỗi order_details: " . mysqli_error($conn));
    }
}

// ===== XÓA GIỎ =====
mysqli_query($conn,"DELETE FROM cart WHERE user_id=$user_id");

// ===== DEBUG OK =====
// ===== CHUYỂN TRANG ===== 
header("Location: orders.php"); exit;