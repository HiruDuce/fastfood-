<?php
// Sử dụng hàm getenv() để lấy các biến môi trường do Railway cung cấp
$host = getenv("MYSQLHOST");
$user = getenv("MYSQLUSER");
$pass = getenv("MYSQLPASSWORD");
$db   = getenv("MYSQLDATABASE");
$port = getenv("MYSQLPORT"); // Thêm Port vì Railway không dùng Port mặc định 3306

// Khởi tạo kết nối mới
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>