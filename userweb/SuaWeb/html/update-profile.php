<?php
session_start();
include("../../../config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$ho = $_POST['ho'];
$ten = $_POST['ten'];
$phone = $_POST['phone'];
$address = $_POST['address'];

// Cập nhật dữ liệu vào database
$sql = "UPDATE users SET ho='$ho', ten='$ten', phone='$phone', address='$address' WHERE id=$user_id";

if (mysqli_query($conn, $sql)) {
    header("Location: profile.php");
    exit;
} else {
    echo "Lỗi: " . mysqli_error($conn);
}
