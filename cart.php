<?php
session_start();
include 'db.php';

// Xóa sản phẩm khỏi giỏ
if(isset($_GET['remove'])){
    $id = $_GET['remove'];
    unset($_SESSION['cart'][$id]);
}

// Cập nhật số lượng
if(isset($_POST['update_cart'])){
    foreach($_POST['quantity'] as $id => $qty){
        if($qty <= 0){
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ Hàng</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        html, body { height: 100%; margin:0; }
        .wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        main { flex: 1; } /* nội dung chính chiếm không gian còn lại */
    </style>
</head>
<body>
<div class="wrapper">

    <?php include 'header.php'; ?>

    <!-- Main content -->
    <main class="container py-5">
        <h2 class="mb-4">Giỏ Hàng Của Bạn</h2>

        <?php if(!empty($_SESSION['cart'])): ?>
        <form method="post">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Hình</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Tổng</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total = 0;
                    foreach($_SESSION['cart'] as $id => $item):
                        $subtotal = $item['price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                    <tr>
                        <td><img src="images/<?= $item['image'] ?>" width="60"></td>
                        <td><?= $item['name'] ?></td>
                        <td><?= number_format($item['price'],0,',','.') ?>₫</td>
                        <td>
                            <input type="number" name="quantity[<?= $id ?>]" value="<?= $item['quantity'] ?>" min="1" class="form-control" style="width:70px;">
                        </td>
                        <td><?= number_format($subtotal,0,',','.') ?>₫</td>
                        <td>
                            <a href="cart.php?remove=<?= $id ?>" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="4" class="text-end fw-bold">Tổng cộng:</td>
                        <td colspan="2" class="fw-bold text-danger"><?= number_format($total,0,',','.') ?>₫</td>
                    </tr>
                </tbody>
            </table>
            <div class="d-flex align-items-center gap-2 flex-wrap cart-actions">
                <button type="submit" name="update_cart" class="btn btn-primary">Cập nhật giỏ hàng</button>
                <a href="checkout.php" class="btn btn-success">Đặt mua</a>
            </div>
        </form>
        <?php else: ?>
            <p class="text-center text-danger">Giỏ hàng của bạn đang trống.</p>
        <?php endif; ?>
        <a href="index.php" class="btn btn-secondary mt-3 continue-shopping">Tiếp tục mua sắm</a>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        &copy; 2025 FastFood. Bản quyền thuộc về Cửa Hàng. Hotline: 1900-XXXX
    </footer>

</div>
</body>
</html>
