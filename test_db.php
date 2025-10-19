<?php
$host = getenv("MYSQLHOST");
$db   = getenv("MYSQLDATABASE");
$user = getenv("MYSQLUSER");
$pass = getenv("MYSQLPASSWORD");
$port = getenv("MYSQLPORT");

// Kết nối MySQLi
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Kết nối DB thất bại: " . $conn->connect_error);
} else {
    echo "Kết nối DB thành công với MySQLi!";
}

// Kiểm tra các bảng
$result = $conn->query("SHOW TABLES");
while($row = $result->fetch_array()) {
    echo $row[0] . "<br>";
}
?>
