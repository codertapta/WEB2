<?php
require __DIR__ . "/../../../config.php";
session_start();

$user_id    = $_SESSION['user_id'];
$product_id = (int)$_GET['id'];
$qty        = isset($_GET['qty']) ? max(1, (int)$_GET['qty']) : 1; // ← đọc số lượng

$check = mysqli_query(
    $conn,
    "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id"
);

if (mysqli_num_rows($check) > 0) {
    mysqli_query(
        $conn,
        "UPDATE cart SET quantity = quantity + $qty 
         WHERE user_id = $user_id AND product_id = $product_id"
    );
} else {
    mysqli_query(
        $conn,
        "INSERT INTO cart(user_id, product_id, quantity) 
         VALUES($user_id, $product_id, $qty)"
    );
}

$referer = $_SERVER['HTTP_REFERER'] ?? 'products.php';
header("Location: $referer");
exit;
?>