<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project_web_data"; // tên database của bạn

$conn = mysqli_connect($servername,$username,$password,$dbname);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}
?>