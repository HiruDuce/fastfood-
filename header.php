<?php
if(!isset($_SESSION)) session_start();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">FastFood 🚀</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link active" href="index.php#home">Trang Chủ</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php#menu">Thực Đơn</a></li>
        <li class="nav-item"><a class="nav-link" href="promotions.php">Khuyến Mãi</a></li>
        <li class="nav-item"><a class="nav-link" href="contact.php">Liên Hệ</a></li>
      </ul>
      <div class="d-flex align-items-center ms-3">
        <a href="cart.php" class="nav-link text-white me-2">
          <i class="fa-solid fa-cart-shopping"></i>
          <?php 
            $total_qty = 0;
            if(isset($_SESSION['cart'])) {
                foreach($_SESSION['cart'] as $item) $total_qty += $item['quantity'];
            }
          ?>
          <span id="cart-count" class="ms-1">(<?= (int)$total_qty ?>)</span>
        </a>
        <?php if(isset($_SESSION['username'])): ?>
            <div class="dropdown">
              <a
                class="px-3 py-1 rounded-pill border border-light text-white d-inline-flex align-items-center gap-2 text-decoration-none dropdown-toggle"
                href="#"
                id="userMenuDropdown"
                role="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                title="Tài khoản">
                <i class="fa-solid fa-user"></i>
                <span class="fw-semibold"><?= htmlspecialchars($_SESSION['fullname'] ?? '') ?></span>
                <?php if(($_SESSION['role'] ?? '') === 'admin'): ?>
                  <span class="badge bg-warning text-dark d-inline-flex align-items-center gap-1">
                    <i class="fa-solid fa-shield-halved"></i> Admin
                  </span>
                <?php endif; ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenuDropdown">
                <li>
                  <a class="dropdown-item" href="my_orders.php">
                    <i class="fa-solid fa-receipt me-2"></i>Đơn hàng của tôi
                  </a>
                </li>
                <?php if(($_SESSION['role'] ?? '') === 'admin'): ?>
                <li>
                  <a class="dropdown-item" href="admin.php">
                    <i class="fa-solid fa-shield-halved me-2"></i>Quản trị
                  </a>
                </li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="logout.php">
                    <i class="fa-solid fa-right-from-bracket me-2"></i>Đăng xuất
                  </a>
                </li>
              </ul>
            </div>
        <?php else: ?>
            <a class="nav-link text-white" href="login.php">Đăng nhập</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
