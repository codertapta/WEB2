<?php
include("../../../config.php");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $newPass = password_hash('123456', PASSWORD_DEFAULT);
    $conn->query("UPDATE users SET password='$newPass' WHERE id=$id");
    header("Location: customers.php");
}
