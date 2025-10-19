<?php include 'db.php'; session_start(); ?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Khuyến mãi - FastFood</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<style>
</style>
</head>
<body class="d-flex flex-column min-vh-100">

<?php include 'header.php'; ?>

<main class="flex-fill">
<div class="container py-5 promotions-section">
    <h2 class="text-center mb-4">Khuyến mãi hiện tại</h2>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
        <?php
        // Lấy danh sách sản phẩm đang có khuyến mãi còn hiệu lực hôm nay
        $sql = "
        SELECT p.*,
               COALESCE(d.discount_percent, 0) AS discount_percent,
               COALESCE(d.discount_amount, 0)  AS discount_amount
        FROM products p
        JOIN (
            SELECT pi.product_id,
                   MAX(pi.discount_percent) AS discount_percent,
                   MAX(pi.discount_amount)  AS discount_amount
            FROM promotion_items pi
            JOIN promotions pr ON pr.id = pi.promotion_id
            WHERE CURDATE() BETWEEN pr.start_date AND pr.end_date
            GROUP BY pi.product_id
        ) d ON d.product_id = p.id
        ORDER BY p.id DESC";
        $res = $conn->query($sql);
        if ($res && $res->num_rows > 0) {
            while($row = $res->fetch_assoc()){
                $orig = (int)$row['price'];
                $percent = (int)$row['discount_percent'];
                $amount  = (int)$row['discount_amount'];
                $final = $orig;
                if ($percent>0) $final = min($final, (int)floor($orig*(100-$percent)/100));
                if ($amount>0)  $final = min($final, max($orig-$amount,0));
                $badge = $percent>0 ? ('-'.$percent.'%') : ('-'.number_format($amount,0,',','.').'₫');
                echo '<div class="col">';
                echo '  <div class="card h-100 shadow-sm position-relative">';
                echo '    <span class="position-absolute top-0 start-0 m-2 badge bg-danger discount-badge">'.$badge.'</span>';
                echo '    <img class="card-img-top" src="images/'.htmlspecialchars($row['image']).'" alt="'.htmlspecialchars($row['name']).'" loading="lazy">';
                echo '    <div class="card-body text-center">';
                echo '      <h5 class="card-title fw-bold mb-1">'.htmlspecialchars($row['name']).'</h5>';
                echo '      <p class="card-text text-muted clamp-2">'.htmlspecialchars($row['description']).'</p>';
                echo '      <p class="mb-2">';
                echo '        <span class="text-muted text-decoration-line-through me-2">'.number_format($orig,0,',','.').'₫</span>';
                echo '        <span class="text-danger fw-bold fs-5">'.number_format($final,0,',','.').'₫</span>';
                echo '      </p>';
                echo '    </div>';
                echo '  </div>';
                echo '</div>';
            }
        } else {
            echo '<p class="text-center empty-state fw-semibold" style="color:#e5e7eb !important;">Hiện chưa có sản phẩm nào đang khuyến mãi.</p>';
        }
        ?>
    </div>
</div>
</main>

<?php include 'footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
