<?php 
include 'db.php'; 
session_start();

// Xử lý thêm sản phẩm vào giỏ
if(isset($_POST['add_to_cart'])){
    $id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    if ($quantity < 1) { $quantity = 1; }

    // Lấy dữ liệu sản phẩm từ DB để tin cậy giá/tên/ảnh
    $stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($product = $result->fetch_assoc()) {
        // Tính giá sau giảm (nếu có khuyến mãi đang hiệu lực)
        $orig_price = (int)$product['price'];
        $percent = 0; $amount = 0;
        if ($id > 0) {
            $q = $conn->prepare("SELECT MAX(pi.discount_percent) AS percent, MAX(pi.discount_amount) AS amount
                                  FROM promotion_items pi
                                  JOIN promotions pr ON pr.id = pi.promotion_id
                                  WHERE pi.product_id = ? AND CURDATE() BETWEEN pr.start_date AND pr.end_date");
            $q->bind_param('i', $id);
            $q->execute();
            $r = $q->get_result();
            if ($rowd = $r->fetch_assoc()) {
                $percent = (int)($rowd['percent'] ?? 0);
                $amount  = (int)($rowd['amount'] ?? 0);
            }
            $q->close();
        }
        $final_price = $orig_price;
        if ($percent > 0) { $final_price = min($final_price, (int)floor($orig_price * (100 - $percent) / 100)); }
        if ($amount  > 0) { $final_price = min($final_price, max($orig_price - $amount, 0)); }

        if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if(isset($_SESSION['cart'][$id])){
            $_SESSION['cart'][$id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$id] = [
                'name'=>$product['name'],
                'price'=>$final_price,
                'image'=>$product['image'],
                'quantity'=>$quantity
            ];
        }
    }
    $stmt->close();
    exit; // ⚡ Quan trọng: tránh render lại HTML khi gọi bằng fetch
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>🍔🍟 FastFood Siêu Tốc</title>

<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Oswald:wght@700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/promotions.css">
<link rel="stylesheet" href="css/menu.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<style>
</style>
</head>
<body>
<div class="wrapper">

<?php include 'header.php'; ?>

<!-- Hero -->
<section id="home" class="hero">
    <div class="inner-image"><img src="images/22.png" alt=""></div>
    <div class="hero-content">
        <h1>Đồ Ăn Nhanh Tươi Ngon, Giao Hàng Siêu Tốc!</h1>
        <p>Burger, Gà Rán, Khoai Tây Chiên... Chất Lượng Tuyệt Hảo.</p>
        <a href="#menu" class="btn-primary">Xem Thực Đơn Ngay!</a>
    </div>
</section>

<!-- Promotions -->
<div class="container">
    <section id="promotions" class="promotions-section py-5 bg-light boxed-section">
        <h2 class="text-center mb-2">Ưu đãi đang diễn ra</h2>
        <div class="text-center mb-4 promotions-cta">
            <a href="#menu" class="btn btn-outline-danger" onclick="showTab('food')"><i class="fa-solid fa-tags me-1"></i>Xem tất cả ưu đãi</a>
        </div>
        <div class="row g-3 g-md-4 justify-content-center">
            <?php
            // Lấy tối đa 8 sản phẩm đang có khuyến mãi, sắp theo mức giảm mạnh nhất
            $sql_promos = "
            SELECT p.*,
                   d.discount_percent,
                   d.discount_amount,
                   d.end_date AS promo_end,
                   COALESCE(d.discount_percent,0)*p.price/100 + COALESCE(d.discount_amount,0) AS discount_score
            FROM products p
            JOIN (
                SELECT pi.product_id,
                       MAX(pi.discount_percent) AS discount_percent,
                       MAX(pi.discount_amount)  AS discount_amount,
                       MAX(pr.end_date)         AS end_date
                FROM promotion_items pi
                JOIN promotions pr ON pr.id = pi.promotion_id
                WHERE CURDATE() BETWEEN pr.start_date AND pr.end_date
                GROUP BY pi.product_id
            ) d ON d.product_id = p.id
            ORDER BY discount_score DESC, p.id DESC
            LIMIT 8";
            $result_promos = $conn->query($sql_promos);
            if($result_promos && $result_promos->num_rows>0){
                while($row=$result_promos->fetch_assoc()){ ?>
                    <div class="col-md-3 mb-4 g-0">
                        <div class="card h-100 shadow-sm position-relative">
                            <?php 
                              $orig_price = (int)$row['price'];
                              $percent = isset($row['discount_percent']) ? (int)$row['discount_percent'] : 0;
                              $amount  = isset($row['discount_amount']) ? (int)$row['discount_amount'] : 0;
                              $final_price = $orig_price;
                              if($percent>0){ $final_price = min($final_price, (int)floor($orig_price * (100 - $percent)/100)); }
                              if($amount>0){ $final_price = min($final_price, max($orig_price - $amount, 0)); }
                              $has_discount = $final_price < $orig_price;
                              if($has_discount){
                                  $badge = $percent>0 ? ('-'.$percent.'%') : ('-'.number_format($amount,0,',','.').'₫');
                                  echo '<span class="position-absolute top-0 start-0 m-2 badge bg-danger discount-badge">'.$badge.'</span>';
                              }
                              $promoEnd = isset($row['promo_end']) ? $row['promo_end'] : null;
                            ?>
                            <?php if(!empty($promoEnd)) { ?>
                              <span class="position-absolute top-0 end-0 m-2 badge bg-dark countdown-badge" data-end="<?= htmlspecialchars($promoEnd) ?>"></span>
                            <?php } ?>
                            <img src="images/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>" loading="lazy">
                            <div class="card-body text-center">
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h5>
                                <p class="card-text text-muted clamp-2"><?= htmlspecialchars($row['description'] ?? '') ?></p>
                                <?php if($has_discount){ ?>
                                  <p class="mb-2">
                                    <span class="text-muted text-decoration-line-through me-2"><?= number_format($orig_price,0,',','.') ?>₫</span>
                                    <span class="text-danger fw-bold fs-5"><?= number_format($final_price,0,',','.') ?>₫</span>
                                  </p>
                                <?php } else { ?>
                                  <p class="text-danger fw-bold fs-5 mb-2"><?= number_format($orig_price,0,',','.') ?>₫</p>
                                <?php } ?>
                                <form class="add-to-cart-form d-flex justify-content-center gap-2">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="name" value="<?= htmlspecialchars($row['name']) ?>">
                                    <input type="hidden" name="price" value="<?= htmlspecialchars($has_discount ? $final_price : $orig_price) ?>">
                                    <input type="hidden" name="image" value="<?= htmlspecialchars($row['image']) ?>">
                                    <button type="button" class="btn btn-primary open-modal-btn"><i class="fa-solid fa-cart-plus"></i>Thêm vào Giỏ</button>
                                    <button type="button" class="btn btn-outline-primary buy-now-btn"><i class="fa-solid fa-bolt"></i>Mua ngay</button>
                                </form>
                            </div>
                        </div>
                    </div>
            <?php } } else { echo '<p class="text-center empty-state fw-semibold" style="color:#e5e7eb !important;">Hiện chưa có ưu đãi nào đang diễn ra.</p>'; } ?>
        </div>
    </section>

    <!-- Menu -->
    <section id="menu" class="menu-section py-5 bg-light">
        <h2 class="text-center mb-4">Thực Đơn Hôm Nay</h2>

        <!-- Tab Buttons -->
        <div class="text-center mb-4">
            <button class="btn btn-outline-primary tab-button" onclick="showTab('food')">Đồ Ăn</button>
            <button class="btn btn-outline-primary tab-button" onclick="showTab('drink')">Đồ Uống</button>

        </div>

        <!-- Tab Content: Đồ ăn -->
        <div id="food" class="row g-3 g-md-4 tab-content menu-tab fade-tab active showing">
            <?php
            $sql_food = "
            SELECT p.*,
                   COALESCE(d.discount_percent, 0) AS discount_percent,
                   COALESCE(d.discount_amount, 0)  AS discount_amount,
                   d.end_date AS promo_end
            FROM products p
            LEFT JOIN (
                SELECT pi.product_id,
                       MAX(pi.discount_percent) AS discount_percent,
                       MAX(pi.discount_amount)  AS discount_amount,
                       MAX(pr.end_date)         AS end_date
                FROM promotion_items pi
                JOIN promotions pr ON pr.id = pi.promotion_id
                WHERE CURDATE() BETWEEN pr.start_date AND pr.end_date
                GROUP BY pi.product_id
            ) d ON d.product_id = p.id
            WHERE p.category='food'
            ORDER BY p.id DESC";
            $result_food = $conn->query($sql_food);
            if($result_food->num_rows>0){
                while($row=$result_food->fetch_assoc()){ ?>
                    <div class="col-md-3 mb-4 g-0">
                        <div class="card h-100 shadow-sm position-relative">
                            <?php 
                              $orig_price = (int)$row['price'];
                              $percent = isset($row['discount_percent']) ? (int)$row['discount_percent'] : 0;
                              $amount  = isset($row['discount_amount']) ? (int)$row['discount_amount'] : 0;
                              $final_price = $orig_price;
                              if($percent>0){ $final_price = min($final_price, (int)floor($orig_price * (100 - $percent)/100)); }
                              if($amount>0){ $final_price = min($final_price, max($orig_price - $amount, 0)); }
                              $has_discount = $final_price < $orig_price;
                              if($has_discount){
                                  $badge = $percent>0 ? ('-'.$percent.'%') : ('-'.number_format($amount,0,',','.').'₫');
                                  echo '<span class="position-absolute top-0 start-0 m-2 badge bg-danger discount-badge">'.$badge.'</span>';
                              }
                            ?>
                            <?php $promoEnd = isset($row['promo_end']) ? $row['promo_end'] : null; ?>
                            <?php if(!empty($promoEnd)) { ?>
                              <span class="position-absolute top-0 end-0 m-2 badge bg-dark countdown-badge" data-end="<?= htmlspecialchars($promoEnd) ?>"></span>
                            <?php } ?>
                            <img src="images/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>" loading="lazy">
                            <div class="card-body text-center">
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h5>
                                <p class="card-text text-muted clamp-2"><?= htmlspecialchars($row['description']) ?></p>
                                <?php if($has_discount){ ?>
                                  <p class="mb-2">
                                    <span class="text-muted text-decoration-line-through me-2"><?= number_format($orig_price,0,',','.') ?>₫</span>
                                    <span class="text-danger fw-bold fs-5"><?= number_format($final_price,0,',','.') ?>₫</span>
                                  </p>
                                <?php } else { ?>
                                  <p class="text-danger fw-bold fs-5 mb-2"><?= number_format($orig_price,0,',','.') ?>₫</p>
                                <?php } ?>
                                <form class="add-to-cart-form d-flex justify-content-center gap-2">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="name" value="<?= htmlspecialchars($row['name']) ?>">
                                    <input type="hidden" name="price" value="<?= htmlspecialchars($has_discount ? $final_price : $orig_price) ?>">
                                    <input type="hidden" name="image" value="<?= htmlspecialchars($row['image']) ?>">
                                    <button type="button" class="btn btn-primary open-modal-btn"><i class="fa-solid fa-cart-plus"></i>Thêm vào Giỏ</button>
                                    <button type="button" class="btn btn-outline-primary buy-now-btn"><i class="fa-solid fa-bolt"></i>Mua ngay</button>
                                </form>
                            </div>
                        </div>
                    </div>
            <?php }}else{ echo "<p class='text-center text-danger'>Chưa có sản phẩm đồ ăn.</p>"; } ?>
        </div>

        <!-- Tab Content: Đồ uống -->
        <div id="drink" class="row g-3 g-md-4 tab-content menu-tab fade-tab">
            <?php
            $sql_drink = "
            SELECT p.*,
                   COALESCE(d.discount_percent, 0) AS discount_percent,
                   COALESCE(d.discount_amount, 0)  AS discount_amount,
                   d.end_date AS promo_end
            FROM products p
            LEFT JOIN (
                SELECT pi.product_id,
                       MAX(pi.discount_percent) AS discount_percent,
                       MAX(pi.discount_amount)  AS discount_amount,
                       MAX(pr.end_date)         AS end_date
                FROM promotion_items pi
                JOIN promotions pr ON pr.id = pi.promotion_id
                WHERE CURDATE() BETWEEN pr.start_date AND pr.end_date
                GROUP BY pi.product_id
            ) d ON d.product_id = p.id
            WHERE p.category='drink'
            ORDER BY p.id DESC";
            $result_drink = $conn->query($sql_drink);
            if($result_drink->num_rows>0){
                while($row=$result_drink->fetch_assoc()){ ?>
                    <div class="col-md-3 mb-4 g-0">
                        <div class="card h-100 shadow-sm position-relative">
                            <?php 
                              $orig_price = (int)$row['price'];
                              $percent = isset($row['discount_percent']) ? (int)$row['discount_percent'] : 0;
                              $amount  = isset($row['discount_amount']) ? (int)$row['discount_amount'] : 0;
                              $final_price = $orig_price;
                              if($percent>0){ $final_price = min($final_price, (int)floor($orig_price * (100 - $percent)/100)); }
                              if($amount>0){ $final_price = min($final_price, max($orig_price - $amount, 0)); }
                              $has_discount = $final_price < $orig_price;
                              if($has_discount){
                                  $badge = $percent>0 ? ('-'.$percent.'%') : ('-'.number_format($amount,0,',','.').'₫');
                                  echo '<span class=\"position-absolute top-0 start-0 m-2 badge bg-danger discount-badge\">'.$badge.'</span>';
                              }
                            ?>
                            <?php $promoEnd = isset($row['promo_end']) ? $row['promo_end'] : null; ?>
                            <?php if(!empty($promoEnd)) { ?>
                              <span class=\"position-absolute top-0 end-0 m-2 badge bg-dark countdown-badge\" data-end=\"<?= htmlspecialchars($promoEnd) ?>\"></span>
                            <?php } ?>
                            <img src="images/<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($row['name']) ?>" loading="lazy">
                            <div class="card-body text-center">
                                <h5 class="card-title fw-bold"><?= htmlspecialchars($row['name']) ?></h5>
                                <p class="card-text text-muted"><?= htmlspecialchars($row['description']) ?></p>
                                <?php if($has_discount){ ?>
                                  <p class="mb-2">
                                    <span class="text-muted text-decoration-line-through me-2"><?= number_format($orig_price,0,',','.') ?>₫</span>
                                    <span class="text-danger fw-bold fs-5"><?= number_format($final_price,0,',','.') ?>₫</span>
                                  </p>
                                <?php } else { ?>
                                  <p class="text-danger fw-bold fs-5 mb-2"><?= number_format($orig_price,0,',','.') ?>₫</p>
                                <?php } ?>
                                <form class="add-to-cart-form d-flex justify-content-center gap-2">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="name" value="<?= htmlspecialchars($row['name']) ?>">
                                    <input type="hidden" name="price" value="<?= htmlspecialchars($has_discount ? $final_price : $orig_price) ?>">
                                    <input type="hidden" name="image" value="<?= htmlspecialchars($row['image']) ?>">
                                    <button type="button" class="btn btn-primary open-modal-btn"><i class="fa-solid fa-cart-plus"></i>Thêm vào Giỏ</button>
                                    <button type="button" class="btn btn-outline-primary buy-now-btn"><i class="fa-solid fa-bolt"></i>Mua ngay</button>
                                </form>
                            </div>
                        </div>
                    </div>
            <?php }}else{ echo "<p class='text-center text-danger'>Chưa có sản phẩm đồ uống.</p>"; } ?>
        </div>
    </section>
</div>







</div><!-- end wrapper -->

<!-- Footer -->
<?php include 'footer.php'; ?>

<!-- Bootstrap JS (required for Modal) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Success Toast (top-right, below header) -->
<div class="position-fixed end-0 p-3" style="z-index:1080; top:72px;">
  <div id="addSuccessToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
    <div class="d-flex">
      <div class="toast-body">
        Đã thêm sản phẩm vào giỏ hàng!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<!-- Bootstrap Modal -->
<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cartModalLabel">Thêm vào giỏ hàng</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex flex-column align-items-center text-center w-100" style="gap:10px;">
          <img id="modal-image" src="" class="rounded" alt="" style="width:110px;height:110px;object-fit:cover;">
          <h6 id="modal-name" class="fw-bold mb-1"></h6>
          <p id="modal-price" class="text-danger fw-bold fs-5 mb-1"></p>
          <div class="mb-0">
            <input type="number" id="modal-qty" class="form-control text-center mx-auto" value="1" min="1" style="width:110px;">
          </div>
        </div>
      </div>
      <div class="modal-footer d-flex gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        <button type="button" id="modal-add-btn" class="btn btn-primary open-modal-btn"><i class="fa-solid fa-cart-plus"></i>Thêm vào giỏ</button>
        <button type="button" id="modal-buy-btn" class="btn btn-outline-primary buy-now-btn"><i class="fa-solid fa-bolt"></i>Mua ngay</button>
      </div>
    </div>
  </div>
</div>

<script src="js/tabs.js" defer></script>
<script src="js/cart-modal.js" defer></script>
<script src="js/countdown.js" defer></script>
</body>
</html>
