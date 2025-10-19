<?php
session_start();
include 'db.php';

// Yêu cầu đăng nhập để xem đơn hàng
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Lấy user_id
$user_id = null;
$stmtU = $conn->prepare('SELECT id, fullname FROM users WHERE username = ? LIMIT 1');
$stmtU->bind_param('s', $_SESSION['username']);
$stmtU->execute();
$resU = $stmtU->get_result();
if ($rowU = $resU->fetch_assoc()) {
    $user_id = (int)$rowU['id'];
}
$stmtU->close();

if ($user_id === null) {
    echo '<script>alert("Không tìm thấy tài khoản. Vui lòng đăng nhập lại."); location.href="login.php";</script>';
    exit;
}

// Lấy danh sách đơn hàng của user
$stmt = $conn->prepare('SELECT id, status, total, created_at FROM orders WHERE user_id = ? ORDER BY id DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đơn hàng của tôi - FastFood</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="d-flex flex-column min-vh-100">
<?php include 'header.php'; ?>
<main class="flex-fill">
  <div class="container py-5">
    <h2 class="mb-4 text-center">Đơn hàng của tôi</h2>

    <?php if ($orders->num_rows === 0): ?>
      <div class="alert alert-info">Bạn chưa có đơn hàng nào.</div>
      <a href="index.php" class="btn btn-primary">Tiếp tục mua sắm</a>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th>Mã đơn</th>
              <th>Ngày đặt</th>
              <th>Trạng thái</th>
              <th class="text-end">Tổng tiền</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php while($o = $orders->fetch_assoc()): ?>
              <tr>
                <td>#<?php echo (int)$o['id']; ?></td>
                <td><?php echo htmlspecialchars($o['created_at']); ?></td>
                <td>
                  <?php 
                    $status = $o['status'];
                    $badge = 'secondary';
                    if ($status === 'pending') $badge = 'warning';
                    if ($status === 'paid') $badge = 'success';
                    if ($status === 'cancelled') $badge = 'danger';
                  ?>
                  <span class="badge bg-<?php echo $badge; ?>"><?php echo htmlspecialchars($status); ?></span>
                </td>
                <td class="text-end fw-bold text-danger"><?php echo number_format($o['total'],0,',','.'); ?>₫</td>
                <td class="text-end">
                  <a class="btn btn-sm btn-outline-primary" href="order_detail.php?id=<?php echo (int)$o['id']; ?>">Chi tiết</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
