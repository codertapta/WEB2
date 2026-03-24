<?php
require __DIR__ . "/../../../config.php";

$id = $_GET['id'];
mysqli_query($conn,"DELETE FROM cart WHERE id=$id");

header("Location: cart.php");
?>