<?php include 'db.php'; session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Liên hệ - FastFood</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<style>
</style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include 'header.php'; ?>

<main class="flex-fill">
<div class="container py-5">
    <h2 class="text-center mb-4 text-uppercase">Liên hệ với chúng tôi</h2>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="contact-info h-100">
                <h5 class="mb-3">Thông tin cửa hàng</h5>
                <p><strong>Địa chỉ:</strong> 123 FastFood Street, TP.HCM</p>
                <p><strong>Hotline:</strong> 1900-XXXX</p>
                <p><strong>Email:</strong> contact@fastfood.com</p>
                <p><strong>Giờ mở cửa:</strong> 8:00 - 22:00</p>
            </div>
        </div>
        <div class="col-md-6">
            <form class="contact-form" method="post" action="#">
                <h5 class="mb-3">Gửi tin nhắn</h5>
                <div class="mb-2">
                    <label class="form-label">Tên của bạn</label>
                    <input type="text" name="name" class="form-control" placeholder="Nguyễn Văn A" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" placeholder="email@domain.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tin nhắn</label>
                    <textarea name="message" class="form-control" rows="5" placeholder="Nội dung liên hệ..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Gửi tin nhắn</button>
            </form>
        </div>
    </div>
    
</div>
</main>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
