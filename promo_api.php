<?php
include 'db.php';
header('Content-Type: application/json');

if(session_status() === PHP_SESSION_NONE) session_start();

// ========== LẤY CHI TIẾT KHUYẾN MÃI ==========
if (isset($_GET['action']) && $_GET['action'] === 'get' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = $conn->query("SELECT * FROM promotions WHERE id=$id LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $promo = $res->fetch_assoc();
        echo json_encode(["status"=>"success","promo"=>$promo]);
    } else {
        echo json_encode(["status"=>"error","message"=>"Không tìm thấy khuyến mãi"]);
    }
    exit;
}

// ========== XỬ LÝ POST ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
        exit;
    }
    // Đảm bảo có biến $action trước khi xử lý
    $action = $_POST['action'] ?? '';

    // --- UPSERT GIẢM GIÁ TRỰC TIẾP THEO SẢN PHẨM ---
    if ($action === 'upsert_direct_discount') {
        $product_id       = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $discount_percent = isset($_POST['discount_percent']) && $_POST['discount_percent'] !== '' ? intval($_POST['discount_percent']) : null;
        $discount_amount  = isset($_POST['discount_amount']) && $_POST['discount_amount'] !== '' ? intval($_POST['discount_amount']) : null;
        $start_date       = $_POST['start_date'] ?? '';
        $end_date         = $_POST['end_date'] ?? '';

        if ($product_id<=0) { echo json_encode(["status"=>"error","message"=>"Thiếu sản phẩm"]); exit; }
        if ($discount_percent===null && $discount_amount===null) { echo json_encode(["status"=>"error","message"=>"Nhập Giảm % hoặc Giảm tiền"]); exit; }
        if ($discount_percent!==null && ($discount_percent<0 || $discount_percent>95)) { echo json_encode(["status"=>"error","message"=>"Giảm % phải 0–95"]); exit; }
        if ($discount_amount!==null && $discount_amount<0) { echo json_encode(["status"=>"error","message"=>"Giảm tiền không hợp lệ"]); exit; }
        if (!$start_date || !$end_date) { echo json_encode(["status"=>"error","message"=>"Nhập ngày bắt đầu/kết thúc"]); exit; }

        // Validate product
        $stmt = $conn->prepare("SELECT COUNT(*) c FROM products WHERE id=?");
        $stmt->bind_param("i", $product_id); $stmt->execute(); $c2 = ($stmt->get_result()->fetch_assoc()['c'] ?? 0); $stmt->close();
        if (!$c2) { echo json_encode(["status"=>"error","message"=>"Sản phẩm không tồn tại"]); exit; }

        // Tìm promotion theo khoảng ngày; nếu chưa có thì tạo mới (title chuẩn hóa)
        $title = 'Direct '.$start_date.' → '.$end_date;
        $promotion_id = 0;
        $stmt = $conn->prepare("SELECT id FROM promotions WHERE start_date=? AND end_date=? LIMIT 1");
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $promotion_id = intval($row['id']);
        }
        $stmt->close();

        if ($promotion_id === 0) {
            $desc = 'Khuyến mãi trực tiếp theo sản phẩm';
            $image = '';
            $stmt = $conn->prepare("INSERT INTO promotions (title, description, image, start_date, end_date) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $title, $desc, $image, $start_date, $end_date);
            if (!$stmt->execute()) { echo json_encode(["status"=>"error","message"=>'Không tạo được promotion: '.$conn->error]); exit; }
            $promotion_id = $stmt->insert_id;
            $stmt->close();
        }

        // Upsert mapping
        $sql = "INSERT INTO promotion_items (promotion_id, product_id, discount_percent, discount_amount)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE discount_percent=VALUES(discount_percent), discount_amount=VALUES(discount_amount)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiii", $promotion_id, $product_id, $discount_percent, $discount_amount);
        if ($stmt->execute()) { echo json_encode(["status"=>"success","message"=>"Đã lưu giảm giá trực tiếp"]); exit; }
        echo json_encode(["status"=>"error","message"=>$conn->error]); exit;
    }
    
    // --- ADD ---
    if ($action === 'add_promo') {
        $title = $_POST['title'];
        $desc = $_POST['description'];
        $start = $_POST['start_date'];
        $end = $_POST['end_date'];

        $imageName = "";
        if (!empty($_FILES['image']['name'])) {
            $original = basename($_FILES['image']['name']);
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                $imageName = time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
                move_uploaded_file($_FILES['image']['tmp_name'], "images/".$imageName);
            }
        }

        $stmt = $conn->prepare("INSERT INTO promotions (title, description, image, start_date, end_date) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $title, $desc, $imageName, $start, $end);
        if ($stmt->execute()) {
            echo json_encode(["status"=>"success","message"=>"Thêm khuyến mãi thành công"]);
        } else {
            echo json_encode(["status"=>"error","message"=>$conn->error]);
        }
        exit;
    }

    // --- EDIT ---
    if ($action === 'edit_promo') {
        $id    = intval($_POST['id']);
        $title = $_POST['title'];
        $desc  = $_POST['description'];
        $start = $_POST['start_date'];
        $end   = $_POST['end_date'];

        // Lấy ảnh hiện tại từ DB để giữ nguyên nếu không upload mới
        $imageName = "";
        $cur = $conn->prepare("SELECT image FROM promotions WHERE id=? LIMIT 1");
        $cur->bind_param("i", $id);
        $cur->execute();
        $resCur = $cur->get_result();
        if ($row = $resCur->fetch_assoc()) {
            $imageName = $row['image'];
        }
        $cur->close();

        if (!empty($_FILES['image']['name'])) {
            $original = basename($_FILES['image']['name']);
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                $imageName = time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
                move_uploaded_file($_FILES['image']['tmp_name'], "images/".$imageName);
            }
        }

        $stmt = $conn->prepare("UPDATE promotions SET title=?, description=?, image=?, start_date=?, end_date=? WHERE id=?");
        $stmt->bind_param("sssssi", $title, $desc, $imageName, $start, $end, $id);

        if ($stmt->execute()) {
            echo json_encode(["status"=>"success","message"=>"Cập nhật khuyến mãi thành công"]);
        } else {
            echo json_encode(["status"=>"error","message"=>$conn->error]);
        }
        exit;
    }

    // --- DELETE PROMO ITEM (xóa giảm giá theo id trong promotion_items) ---
    if ($action === 'delete_promo_item') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        if ($id<=0) { echo json_encode(["status"=>"error","message"=>"Thiếu id"]); exit; }
        $stmt = $conn->prepare("DELETE FROM promotion_items WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) { echo json_encode(["status"=>"success","message"=>"Đã xóa"]); exit; }
        echo json_encode(["status"=>"error","message"=>"Không thể xóa"]); exit;
    }

    // Hành động không hợp lệ
    echo json_encode(["status"=>"error","message"=>"Hành động không hợp lệ"]); exit;
}
