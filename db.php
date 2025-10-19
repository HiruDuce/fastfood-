<?php
// SỬ DỤNG HÀM THỦ TỤC mysqli_connect()
$host = getenv("MYSQLHOST");
$user = getenv("MYSQLUSER");
$pass = getenv("MYSQLPASSWORD");
$db   = getenv("MYSQLDATABASE");
$port = getenv("MYSQLPORT");

// Sử dụng mysqli_connect()
$conn = mysqli_connect($host, $user, $pass, $db, $port); 

if (!$conn) {
    die("Kết nối thất bại: " . mysqli_connect_error());
}
?>