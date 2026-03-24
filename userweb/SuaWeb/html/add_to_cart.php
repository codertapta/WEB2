<?php
require __DIR__ . "/../../../config.php";

session_start();
$user_id = $_SESSION['user_id'];

$product_id = $_GET['id'];

// kiểm tra đã có trong giỏ chưa
$check = mysqli_query($conn,"SELECT * FROM cart WHERE user_id=$user_id AND product_id=$product_id");

if(mysqli_num_rows($check) > 0){
    // đã có → tăng số lượng
    mysqli_query($conn,"UPDATE cart SET quantity = quantity + 1 WHERE user_id=$user_id AND product_id=$product_id");
}else{
    // chưa có → thêm mới
    mysqli_query($conn,"INSERT INTO cart(user_id,product_id,quantity) VALUES($user_id,$product_id,1)");
}

// quay lại trang trước
header("Location: products.php");
?>