<?php
include 'db.php';
if(session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('Unauthorized'); window.location='login.php';</script>";
    exit;
}

if(isset($_GET['id'])){
    $id = intval($_GET['id']);
    
    // Không cho xóa nếu đã có trong đơn hàng
    $check = $conn->prepare("SELECT COUNT(*) AS c FROM order_items WHERE product_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $res = $check->get_result();
    $cnt = (int)($res->fetch_assoc()['c'] ?? 0);
    $check->close();

    if ($cnt > 0) {
        echo "<script>alert('Không thể xóa sản phẩm vì đã tồn tại trong đơn hàng. Hãy vô hiệu hóa sản phẩm hoặc xóa các mục đơn hàng liên quan trước.'); window.location='admin.php';</script>";
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        echo "<script>alert('Xóa thành công!'); window.location='admin.php';</script>";
    } else {
        echo "Lỗi khi xóa: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "ID không hợp lệ!";
}
?>
