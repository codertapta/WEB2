<?php
include("../../../config.php");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $conn->query("UPDATE users SET status='locked' WHERE id=$id");
    header("Location: customers.php");
}
