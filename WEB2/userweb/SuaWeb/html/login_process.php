<?php
session_start();
include("../../../config.php");

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = mysqli_query($conn,$sql);

if(mysqli_num_rows($result) == 1){
    $user = mysqli_fetch_assoc($result);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['ten'];

    header("Location: products.php");
}
else{
    echo "Sai email hoặc mật khẩu";
}
?>