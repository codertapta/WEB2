<?php
require __DIR__ . "/../../../config.php";

$id = $_GET['id'];
$type = $_GET['type'];

if($type == "increase"){
    mysqli_query($conn,"UPDATE cart SET quantity = quantity + 1 WHERE id=$id");
}else{
    mysqli_query($conn,"UPDATE cart SET quantity = quantity - 1 WHERE id=$id AND quantity > 1");
}

header("Location: cart.php");
?>