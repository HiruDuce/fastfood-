<?php
session_start();
include 'db.php';

// Expect: POST product_id, quantity
$id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$qty = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
if ($qty < 1) { $qty = 1; }

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Thiếu sản phẩm']);
    exit;
}

$stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$product = $res->fetch_assoc()) {
    http_response_code(404);
    echo json_encode(['status'=>'error','message'=>'Không tìm thấy sản phẩm']);
    exit;
}

// Tính giá sau giảm (nếu có KM hiệu lực)
$orig_price = (int)$product['price'];
$percent = 0; $amount = 0;
$q = $conn->prepare("SELECT MAX(pi.discount_percent) AS percent, MAX(pi.discount_amount) AS amount
                      FROM promotion_items pi
                      JOIN promotions pr ON pr.id = pi.promotion_id
                      WHERE pi.product_id = ? AND CURDATE() BETWEEN pr.start_date AND pr.end_date");
$q->bind_param('i', $id);
$q->execute();
$rr = $q->get_result();
if ($rowd = $rr->fetch_assoc()) {
    $percent = (int)($rowd['percent'] ?? 0);
    $amount  = (int)($rowd['amount'] ?? 0);
}
$q->close();
$final_price = $orig_price;
if ($percent>0) $final_price = min($final_price, (int)floor($orig_price*(100-$percent)/100));
if ($amount>0)  $final_price = min($final_price, max($orig_price-$amount,0));

// Store a dedicated temporary list for buy now
$_SESSION['buy_now'] = [
    $product['id'] => [
        'name' => $product['name'],
        'price'=> (float)$final_price,
        'image'=> $product['image'] ?? '',
        'quantity' => $qty,
    ]
];

echo json_encode(['status'=>'success']);
