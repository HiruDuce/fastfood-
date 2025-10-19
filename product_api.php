<?php
header('Content-Type: application/json; charset=utf-8');
include 'db.php';
if(session_status() === PHP_SESSION_NONE) session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['action']) && $_GET['action'] !== 'get')) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(["status" => "error", "message" => "Unauthorized"]);
        exit;
    }
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;
if (!$action) {
    echo json_encode(["status" => "error", "message" => "Thiếu tham số action"]);
    exit;
}

// Hàm upload ảnh an toàn
function uploadImage($file, $oldImage = "") {
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return $oldImage;
    }

    $allowedExt = ['jpg','jpeg','png','webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    $originalName = basename($file['name']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        return $oldImage;
    }
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return $oldImage;
    }
    if (($file['size'] ?? 0) > $maxSize) {
        return $oldImage;
    }

    $unique = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target_file = __DIR__ . "/images/" . $unique;
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return $unique;
    }
    return $oldImage;
}

// GET - Lấy thông tin sản phẩm
if ($action === "get") {
    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(["status"=>"success", "product"=>$row]);
    } else {
        echo json_encode(["status"=>"error", "message"=>"Không tìm thấy sản phẩm"]);
    }
    $stmt->close();
    exit;
}

// ADD - Thêm sản phẩm
if ($action === "add") {
    $name = $_POST['name'] ?? "";
    $price = $_POST['price'] ?? 0;
    $desc = $_POST['description'] ?? "";
    $category = $_POST['category'] ?? "";
    $image = uploadImage($_FILES['image'] ?? []);

    $stmt = $conn->prepare("INSERT INTO products (name, price, description, image, category) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsss", $name, $price, $desc, $image, $category);

    if ($stmt->execute()) {
        echo json_encode(["status"=>"success","message"=>"Thêm sản phẩm thành công!"]);
    } else {
        echo json_encode(["status"=>"error","message"=>$stmt->error]);
    }
    $stmt->close();
    exit;
}

// EDIT - Sửa sản phẩm
if ($action === "edit") {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? "";
    $price = $_POST['price'] ?? 0;
    $desc = $_POST['description'] ?? "";
    $category = $_POST['category'] ?? "";

    // Lấy ảnh cũ
    $oldImage = "";
    $stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $oldImage = $row['image'];
    }
    $stmt->close();

    $image = uploadImage($_FILES['image'] ?? [], $oldImage);

    // Update với prepared statement
    $stmt = $conn->prepare("UPDATE products SET name=?, price=?, description=?, category=?, image=? WHERE id=?");
    $stmt->bind_param("sdsssi", $name, $price, $desc, $category, $image, $id);

    if ($stmt->execute()) {
        echo json_encode(["status"=>"success","message"=>"Cập nhật thành công!"]);
    } else {
        echo json_encode(["status"=>"error","message"=>$stmt->error]);
    }
    $stmt->close();
    exit;
}

echo json_encode(["status" => "error", "message" => "Hành động không hợp lệ"]);
?>