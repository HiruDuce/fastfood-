<?php
include 'db.php';
if(session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Unauthorized'); window.location='login.php';</script>";
    exit;
}

if(isset($_GET['id'])){
    $id = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM promotions WHERE id=?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        echo "<script>alert('Xóa khuyến mãi thành công!'); window.location='admin.php';</script>";
    } else {
        echo "Lỗi khi xóa: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "ID không hợp lệ!";
}
?>
