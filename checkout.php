<?php
session_start();
include 'db.php';

// Buy-now mode: chỉ mua sản phẩm vừa chọn, không lấy toàn bộ giỏ hàng
$isBuyNow = isset($_GET['buy_now']) && $_GET['buy_now'] == '1' && !empty($_SESSION['buy_now']);
$orderSource = $isBuyNow ? ($_SESSION['buy_now'] ?? []) : ($_SESSION['cart'] ?? []);

// Nếu không có dữ liệu phù hợp, điều hướng hợp lý
if (empty($orderSource)) {
    header('Location: ' . ($isBuyNow ? 'index.php' : 'cart.php'));
    exit;
}

$success   = false;
$orderId   = null;
$errorMsg  = '';

$fullname = trim($_POST['fullname'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$address  = trim($_POST['address'] ?? '');
$payment  = $_POST['payment_method'] ?? 'COD';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $errorMsg === '') {
    $subtotal = 0;
    foreach ($orderSource as $pid => $item) {
        $subtotal += ((float)$item['price']) * ((int)$item['quantity']);
    }
    $discount = 0;
    $total = $subtotal - $discount;

    $user_id = null;
    if (isset($_SESSION['username'])) {
        $stmtU = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        if ($stmtU) {
            $stmtU->bind_param('s', $_SESSION['username']);
            $stmtU->execute();
            $resU = $stmtU->get_result();
            if ($rowU = $resU->fetch_assoc()) {
                $user_id = (int)$rowU['id'];
            }
            $stmtU->close();
        }
    }

    $stmt = $conn->prepare("INSERT INTO orders (user_id, fullname, phone, address, payment_method, status, subtotal, discount, total) VALUES (?,?,?,?,?,'pending',?,?,?)");
    if ($stmt) {
        $stmt->bind_param('issssddd', $user_id, $fullname, $phone, $address, $payment, $subtotal, $discount, $total);
        if ($stmt->execute()) {
            $orderId = $stmt->insert_id;
            $success = true;

            $stmtItem = $conn->prepare('INSERT INTO order_items (order_id, product_id, name, price, quantity, image) VALUES (?,?,?,?,?,?)');
            foreach ($orderSource as $pid => $item) {
                $pidInt = (int)$pid;
                $name   = $item['name'];
                $price  = (float)$item['price'];
                $qty    = (int)$item['quantity'];
                $img    = $item['image'] ?? '';
                $stmtItem->bind_param('iisdis', $orderId, $pidInt, $name, $price, $qty, $img);
                $stmtItem->execute();
            }
            $stmtItem->close();

            // Clear only the source used
            if ($isBuyNow) {
                unset($_SESSION['buy_now']);
            } else {
                unset($_SESSION['cart']);
            }
        } else {
            $errorMsg = 'Không thể tạo đơn: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $errorMsg = 'Lỗi hệ thống: ' . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thanh toán - FastFood</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/checkout.css">
</head>
<body>
<div class="wrapper">
<?php include 'header.php'; ?>
<main class="checkout-page">
    <h2 class="page-title">Thanh toán</h2>

    <?php if ($success): ?>
        <div class="alert success">
            Đặt hàng thành công! Mã đơn của bạn: <strong>#<?php echo htmlspecialchars($orderId); ?></strong>
        </div>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-primary">Về trang chủ</a>
            <?php if(isset($_SESSION['username'])): ?>
                <a href="my_orders.php" class="btn btn-outline-primary">Xem đơn hàng của tôi</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php if ($errorMsg): ?>
            <div class="alert error"><?php echo htmlspecialchars($errorMsg); ?></div>
        <?php endif; ?>

        <div class="checkout-container">
            <!-- Thông tin nhận hàng -->
            <div class="checkout-left">
                <h3>Thông tin nhận hàng</h3>
                <form method="post" class="checkout-form">
                    <label>Họ và tên</label>
                    <input type="text" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required>

                    <label>Số điện thoại</label>
                    <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>

                    <label>Địa chỉ</label>
                    <textarea name="address" required><?php echo htmlspecialchars($address); ?></textarea>

                    <label>Phương thức thanh toán</label>
                    <select name="payment_method">
                        <option value="COD" <?php echo $payment==='COD'?'selected':''; ?>>Thanh toán khi nhận hàng (COD)</option>
                    </select>

                    <button type="submit" class="btn btn-success">Xác nhận đặt mua</button>
                </form>
            </div>

            <!-- Đơn hàng -->
            <div class="checkout-right">
                <h3>Đơn hàng của bạn</h3>
                <div class="order-items">
                    <?php 
                    $sum = 0; 
                    if (!empty($orderSource)): 
                        foreach ($orderSource as $id => $item): 
                            $sub = $item['price'] * $item['quantity']; 
                            $sum += $sub;
                    ?>
                        <div class="order-item">
                            <div>
                                <div class="name"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div class="qty">x<?php echo (int)$item['quantity']; ?></div>
                            </div>
                            <div class="price"><?php echo number_format($sub,0,',','.'); ?>₫</div>
                        </div>
                    <?php endforeach; else: ?>
                        <p class="empty">Giỏ hàng của bạn đang trống.</p>
                    <?php endif; ?>
                </div>

                <div class="totals">
                    <div><span>Tổng tạm tính</span><span><?php echo number_format($sum,0,',','.'); ?>₫</span></div>
                    <div><span>Giảm giá</span><span>0₫</span></div>
                    <hr>
                    <div class="grand-total"><span>Tổng cộng</span><span class="highlight"><?php echo number_format($sum,0,',','.'); ?>₫</span></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>
<?php include 'footer.php'; ?>
</div>
</body>
</html>
