<?php
session_start();

// Xóa toàn bộ session
session_unset();
session_destroy();

// Quay về trang login
header("Location: admin-login.php");
exit;
