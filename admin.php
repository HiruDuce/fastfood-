<?php 
include 'db.php'; 
if(session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin - Quản lý sản phẩm</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css?v=3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
</head>
<body>
<!-- HEADER / NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">FastFood Admin</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav"></div>
  </div>
</nav>

<!-- Sidebar (UI only, navigates existing tabs) -->
<aside class="admin-sidebar">
  <div class="sidebar-header">
    <span class="brand">Admin</span>
  </div>
  <ul class="sidebar-menu">
    <li><a href="#" class="sidebar-link active" data-target="food" onclick="showTab('food');setActiveSidebar(this);return false;"><i class="fa-solid fa-burger"></i><span>Đồ Ăn</span></a></li>
    <li><a href="#" class="sidebar-link" data-target="drink" onclick="showTab('drink');setActiveSidebar(this);return false;"><i class="fa-solid fa-mug-saucer"></i><span>Đồ Uống</span></a></li>
    <li><a href="#" class="sidebar-link" data-target="promo" onclick="showTab('promo');setActiveSidebar(this);return false;"><i class="fa-solid fa-tags"></i><span>Khuyến Mãi</span></a></li>
    <li class="divider"></li>
    <li>
      <a href="index.php" class="sidebar-link back-home">
        <i class="fa-solid fa-arrow-left"></i><span>Trang chủ</span>
      </a>
    </li>
  </ul>
  <div class="sidebar-footer small text-muted">&nbsp;</div>
 </aside>


<main class="container admin-main" style="padding:20px;">

    <!-- Inline alert host -->
    <div id="alertHost" class="mt-3"></div>

    <!-- Tab Buttons (hidden by CSS; sidebar controls tabs) -->
    <div class="mb-3">
        <button class="tab-button active" onclick="showTab('food')">Đồ Ăn</button>
        <button class="tab-button" onclick="showTab('drink')">Đồ Uống</button>
        <button class="tab-button" onclick="showTab('promo')">Khuyến Mãi</button>
    </div>

    <!-- DANH SÁCH ĐỒ ĂN -->
    <div id="food" class="tab-content" style="display:block;">
        <h2>Danh sách đồ ăn</h2>
        <span class="add-btn" data-bs-toggle="modal" data-bs-target="#addModal">➕ Thêm sản phẩm</span>

    <table>
            <tr>
                <th>ID</th>
                <th>Tên món</th>
                <th>Giá</th>
                <th>Mô tả</th>
                <th>Hình ảnh</th>
                <th>Hành động</th>
            </tr>
            <?php
            $res_food = $conn->query("SELECT * FROM products WHERE category='food'");
            while($row = $res_food->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>".htmlspecialchars($row['name'])."</td>
                    <td>".number_format($row['price'])." VND</td>
                    <td>".htmlspecialchars($row['description'])."</td>
                    <td><img src='images/".htmlspecialchars($row['image'])."'></td>
                    <td>
                        <div class='actions'>
                          <a href='#' class='btn-action edit' role='button' style='cursor:pointer' data-id='{$row['id']}' data-bs-toggle='modal' data-bs-target='#editModal'>
                            <i class='fa-solid fa-pen'></i><span>Sửa</span>
                          </a>
                          <a href='#' class='btn-action danger deleteProduct' role='button' style='cursor:pointer' data-id='{$row['id']}'>
                            <i class='fa-solid fa-trash'></i><span>Xóa</span>
                          </a>
                        </div>
                    </td>
                </tr>";
            }
            ?>
        </table>
    </div>

    <!-- DANH SÁCH ĐỒ UỐNG -->
    <div id="drink" class="tab-content">
        <h2>Danh sách đồ uống</h2>
        <span class="add-btn" data-bs-toggle="modal" data-bs-target="#addModal">➕ Thêm sản phẩm</span>
        <table>
            <tr>
                <th>ID</th>
                <th>Tên đồ uống</th>
                <th>Giá</th>
                <th>Mô tả</th>
                <th>Hình ảnh</th>
                <th>Hành động</th>
            </tr>
            <?php
            $res_drink = $conn->query("SELECT * FROM products WHERE category='drink'");
            while($row = $res_drink->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['id']}</td>
                    <td>".htmlspecialchars($row['name'])."</td>
                    <td>".number_format($row['price'])." VND</td>
                    <td>".htmlspecialchars($row['description'])."</td>
                    <td><img src='images/".htmlspecialchars($row['image'])."'></td>
                    <td>
                        <div class='actions'>
                          <a class='btn-action edit' data-id='{$row['id']}' data-bs-toggle='modal' data-bs-target='#editModal'>
                            <i class='fa-solid fa-pen'></i><span>Sửa</span>
                          </a>
                          <a href='#' class='btn-action danger deleteProduct' data-id='{$row['id']}'>
                            <i class='fa-solid fa-trash'></i><span>Xóa</span>
                          </a>
                        </div>
                    </td>
                </tr>";
            }
            ?>
        </table>
    </div>

    <!-- DANH SÁCH KHUYẾN MÃI -->
<div id="promo" class="tab-content">
    <h2>Khuyến mãi</h2>
    <!-- Ẩn nút thêm khuyến mãi truyền thống theo yêu cầu -->
    <span class="add-btn d-none" data-bs-toggle="modal" data-bs-target="#addPromoModal">➕ Thêm khuyến mãi</span>
    <!-- Form giảm giá trực tiếp theo sản phẩm (đúng vị trí tab Khuyến Mãi) -->
    <div class="card p-3 mb-3">
      <h5 class="mb-3">Giảm giá trực tiếp cho sản phẩm</h5>
      <form id="directDiscountForm" class="row g-2 align-items-end">
        <input type="hidden" name="action" value="upsert_direct_discount">
        <div class="col-md-4">
          <label class="form-label">Sản phẩm</label>
          <select name="product_id" class="form-select" required>
            <option value="" selected disabled>-- Chọn sản phẩm --</option>
            <?php
              // Nhóm theo category để dễ tìm
              $grp = [
                'Đồ ăn'  => "SELECT id, name, price FROM products WHERE category='food' ORDER BY name ASC",
                'Đồ uống'=> "SELECT id, name, price FROM products WHERE category='drink' ORDER BY name ASC",
              ];
              foreach($grp as $labelGrp => $sqlGrp){
                echo '<optgroup label="'.htmlspecialchars($labelGrp).'">';
                if ($rs = $conn->query($sqlGrp)){
                  while($pr = $rs->fetch_assoc()){
                    $label = $pr['name']." (".number_format($pr['price'])."₫)";
                    echo '<option value="'.(int)$pr['id'].'">'.htmlspecialchars($label).'</option>';
                  }
                }
                echo '</optgroup>';
              }
            ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Giảm %</label>
          <input type="number" min="0" max="95" step="1" name="discount_percent" class="form-control" placeholder="VD: 20">
        </div>
        <div class="col-md-2">
          <label class="form-label">Giảm tiền (₫)</label>
          <input type="number" min="0" step="1000" name="discount_amount" class="form-control" placeholder="VD: 10000">
        </div>
        <div class="col-md-2">
          <label class="form-label">Bắt đầu</label>
          <input type="date" name="start_date" class="form-control" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Kết thúc</label>
          <input type="date" name="end_date" class="form-control" required>
        </div>
        <div class="col-12 mt-2">
          <button type="submit" class="btn btn-primary">Lưu giảm giá</button>
          <span class="small text-muted ms-2">Chỉ cần nhập một trong hai: Giảm % hoặc Giảm tiền</span>
        </div>
      </form>
    </div>
    <!-- Bỏ bảng danh sách khuyến mãi truyền thống theo yêu cầu -->

    <!-- Danh sách khuyến mãi (hiển thị để xem/sửa/xóa) -->
    <div class="mt-4">
      <h4>Danh sách khuyến mãi</h4>
      <table>
        <tr>
          <th>ID</th>
          <th>Tiêu đề</th>
          <th>Thời gian</th>
          <th>Hành động</th>
        </tr>
        <?php
          if ($rsPr = $conn->query("SELECT id, title, start_date, end_date FROM promotions ORDER BY id DESC")) {
            while($pr = $rsPr->fetch_assoc()){
              echo '<tr>';
              echo '<td>'.(int)$pr['id'].'</td>';
              echo '<td>'.htmlspecialchars($pr['title']).'</td>';
              echo '<td>'.htmlspecialchars($pr['start_date']).' → '.htmlspecialchars($pr['end_date']).'</td>';
              echo '<td>'
                   .'<a href="#" class="btn-action edit editPromo" data-id="'.(int)$pr['id'].'"><i class="fa-solid fa-pen"></i><span>Sửa</span></a> '
                   .'<a href="#" class="btn-action danger deletePromo" data-id="'.(int)$pr['id'].'"><i class="fa-solid fa-trash"></i><span>Xóa</span></a>'
                   .'</td>';
              echo '</tr>';
            }
          }
        ?>
      </table>
    </div>

    <div class="mt-4">
      <h4>Danh sách sản phẩm đang trong khuyến mãi</h4>
      <table>
        <tr>
          <th>ID</th>
          <th>Promo</th>
          <th>Sản phẩm</th>
          <th>Giảm %</th>
          <th>Giảm tiền</th>
          <th>Hiệu lực</th>
          <th>Hành động</th>
        </tr>
        <?php
          $sqlPi = "SELECT pi.id, pi.promotion_id, pi.product_id, pi.discount_percent, pi.discount_amount,
                            pr.title, pr.start_date, pr.end_date,
                            p.name AS product_name, p.price AS product_price
                     FROM promotion_items pi
                     JOIN promotions pr ON pr.id = pi.promotion_id
                     JOIN products p ON p.id = pi.product_id
                     ORDER BY pi.id DESC";
          if($resPi = $conn->query($sqlPi)){
            $today = date('Y-m-d');
            while($r = $resPi->fetch_assoc()){
              $active = ($today >= $r['start_date'] && $today <= $r['end_date']);
              $effect = $active ? '<span class="badge bg-success">Đang hiệu lực</span>' : '<span class="badge bg-secondary">Ngoài mốc</span>';
              echo '<tr>';
              echo '<td>'.(int)$r['id'].'</td>';
              echo '<td>'.htmlspecialchars($r['title']).'</td>';
              echo '<td>'.htmlspecialchars($r['product_name']).' ('.number_format($r['product_price']).'₫)</td>';
              echo '<td>'.($r['discount_percent']!==null? (int)$r['discount_percent'] : '').'</td>';
              echo '<td>'.($r['discount_amount']!==null? number_format($r['discount_amount']).'₫' : '').'</td>';
              echo '<td>'.$effect.'<div class="small text-muted">'.$r['start_date'].' → '.$r['end_date'].'</div></td>';
              echo '<td><a href="#" class="btn-action danger deletePromoItem" data-id="'.(int)$r['id'].'"><i class="fa-solid fa-trash"></i><span>Xóa</span></a></td>';
              echo '</tr>';
            }
          }
        ?>
      </table>
    </div>
  </div>


</main>

<!-- MODAL THÊM -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Thêm sản phẩm mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add">
          <div class="mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Giá</label>
            <input type="number" name="price" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control"></textarea>
          </div>
          <!-- Loại: ẩn khỏi UI, tự set theo tab hiện tại -->
          <input type="hidden" name="category" id="add_category" value="food">
          <div class="mb-3">
            <label class="form-label">Hình ảnh</label>
            <input type="file" name="image" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Lưu</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL SỬA -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Sửa sản phẩm</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="id" id="edit_id">
          <div class="mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="name" id="edit_name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Giá</label>
            <input type="number" name="price" id="edit_price" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" id="edit_description" class="form-control"></textarea>
          </div>
          <div class="mb-3 d-none">
            <label class="form-label">Loại</label>
            <select name="category" id="edit_category" class="form-select">
              <option value="food">Đồ ăn</option>
              <option value="drink">Đồ uống</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Hình ảnh (chọn để thay)</label>
            <input type="file" name="image" class="form-control">
            <img id="edit_image" src="" alt="" class="mt-2" style="width:100px;">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Cập nhật</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL THÊM KHUYẾN MÃI -->
<div class="modal fade" id="addPromoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addPromoForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Thêm khuyến mãi mới</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add_promo">
          <div class="mb-3">
            <label class="form-label">Tiêu đề</label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" class="form-control"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Ảnh</label>
            <input type="file" name="image" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Ngày bắt đầu</label>
            <input type="date" name="start_date" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Ngày kết thúc</label>
            <input type="date" name="end_date" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Lưu</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL SỬA KHUYẾN MÃI -->
<div class="modal fade" id="editPromoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editPromoForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title">Sửa khuyến mãi</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="edit_promo">
          <input type="hidden" name="id" id="promo_id">

          <div class="mb-3">
            <label class="form-label">Tiêu đề</label>
            <input type="text" name="title" id="promo_title" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Mô tả</label>
            <textarea name="description" id="promo_description" class="form-control"></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Ảnh (chọn để thay)</label>
            <input type="file" name="image" class="form-control">
            <img id="promo_image" src="" alt="" style="width:100px;margin-top:10px;">
          </div>

          <div class="mb-3">
            <label class="form-label">Ngày bắt đầu</label>
            <input type="date" name="start_date" id="promo_start" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Ngày kết thúc</label>
            <input type="date" name="end_date" id="promo_end" class="form-control">
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Cập nhật</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
        </div>
      </form>
    </div>
  </div>
</div>



<!-- DELETE CONFIRM MODALS -->
<div class="modal fade" id="confirmDeleteProduct" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Xóa sản phẩm</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Bạn có chắc muốn xóa sản phẩm này? Hành động không thể hoàn tác.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <a id="confirmDeleteProductBtn" href="#" class="btn btn-danger">Xóa</a>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="confirmDeletePromo" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Xóa khuyến mãi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Bạn có chắc muốn xóa khuyến mãi này? Hành động không thể hoàn tác.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <a id="confirmDeletePromoBtn" href="#" class="btn btn-danger">Xóa</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Helper: set and get active tab
function showTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.style.display='none');
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    const target = document.getElementById(tabId);
    if (target) target.style.display='block';
    const btn = document.querySelector(`[onclick="showTab('${tabId}')"]`);
    if (btn) btn.classList.add('active');
    // persist active tab for reload
    try { sessionStorage.setItem('adminActiveTab', tabId); } catch(e) {}
    // sync sidebar highlight
    const side = document.querySelector(`.sidebar-link[data-target='${tabId}']`);
    setActiveSidebar(side);
}

function getCurrentTabId(){
  const shown = Array.from(document.querySelectorAll('.tab-content')).find(t => t.style.display === 'block');
  return shown ? shown.id : (document.querySelector('.sidebar-link.active')?.getAttribute('data-target') || 'food');
}

// Sidebar active state
function setActiveSidebar(el){
  document.querySelectorAll('.sidebar-link').forEach(a=>a.classList.remove('active'));
  if(el) el.classList.add('active');
}

// Restore last active tab (default food)
document.addEventListener('DOMContentLoaded', () => {
  let target = 'food';
  try { target = sessionStorage.getItem('adminActiveTab') || 'food'; } catch(e) {}
  showTab(target);
  // also update sidebar highlight on load
  const side = document.querySelector(`.sidebar-link[data-target='${target}']`);
  setActiveSidebar(side);
  // Ensure add modal uses current tab as category
  const addModalEl = document.getElementById('addModal');
  if (addModalEl) {
    addModalEl.addEventListener('show.bs.modal', function(){
      const cur = getCurrentTabId();
      const input = document.getElementById('add_category');
      if (input) input.value = (cur === 'drink') ? 'drink' : 'food';
    });
  }
});

// Helper: render Bootstrap alert
function renderAlert(type, message) {
  const host = document.getElementById('alertHost');
  if (!host) return;
  const id = 'al_' + Date.now();
  const div = document.createElement('div');
  div.id = id;
  div.className = `alert alert-${type} alert-dismissible fade show`;
  div.role = 'alert';
  div.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
  host.appendChild(div);
  // Auto-dismiss after 4s
  setTimeout(() => {
    const el = document.getElementById(id);
    if (el) {
      const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
      bsAlert.close();
    }
  }, 4000);
}

// ADD - Thêm sản phẩm
document.getElementById("addForm").addEventListener("submit", function(e){
  e.preventDefault();
  const form = this;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('[type="submit"]');
  const oldHTML = submitBtn ? submitBtn.innerHTML : '';
  if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Đang lưu'; }
  fetch("product_api.php", { method: "POST", body: formData })
    .then(r => r.json())
    .then(json => {
      console.log('ADD response:', json);
      if (json.status === 'success') {
        renderAlert('success', json.message || 'Thêm sản phẩm thành công');
        try { sessionStorage.setItem('adminActiveTab', getCurrentTabId()); } catch(e) {}
        location.reload();
      } else {
        renderAlert('danger', 'Lỗi: ' + (json.message || 'Không rõ'));
      }
    })
    .catch(err => {
      console.error(err);
      renderAlert('danger', 'Lỗi mạng hoặc server.');
    })
    .finally(() => {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = oldHTML; }
    });
});

// Load dữ liệu khi click nút Sửa sản phẩm
document.querySelectorAll('.edit').forEach(btn => {
  btn.addEventListener('click', function(){
    const id = this.getAttribute('data-id');
    
    fetch("product_api.php?action=get&id=" + id)
      .then(r => r.json())
      .then(data => {
        if (data.status === "success") {
          document.getElementById("edit_id").value = data.product.id;
          document.getElementById("edit_name").value = data.product.name;
          document.getElementById("edit_price").value = data.product.price;
          document.getElementById("edit_description").value = data.product.description;
          document.getElementById("edit_category").value = data.product.category;
          document.getElementById("edit_image").src = "images/" + data.product.image;
        } else {
          alert("Không tìm thấy sản phẩm");
        }
      })
      .catch(err => {
        console.error(err);
        alert("Lỗi tải dữ liệu sản phẩm");
      });
  });
});

// EDIT - Cập nhật sản phẩm
document.getElementById("editForm").addEventListener("submit", function(e){
  e.preventDefault();
  const form = this;
  const formData = new FormData(form);
  if (!formData.has('action')) formData.append('action', 'edit');
  const submitBtn = form.querySelector('[type="submit"]');
  const oldHTML = submitBtn ? submitBtn.innerHTML : '';
  if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Đang cập nhật'; }
  fetch("product_api.php", { method: "POST", body: formData })
    .then(r => r.json())
    .then(json => {
      console.log('EDIT response:', json);
      if (json.status === 'success') {
        renderAlert('success', json.message || 'Cập nhật thành công');
        try { sessionStorage.setItem('adminActiveTab', getCurrentTabId()); } catch(e) {}
        location.reload();
      } else {
        renderAlert('danger', 'Lỗi: ' + (json.message || 'Không rõ'));
      }
    })
    .catch(err => {
      console.error(err);
      renderAlert('danger', 'Lỗi mạng hoặc server.');
    })
    .finally(() => {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = oldHTML; }
    });
});

// ADD PROMO
document.getElementById("addPromoForm").addEventListener("submit", function(e){
  e.preventDefault();
  const form = this;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('[type="submit"]');
  const oldHTML = submitBtn ? submitBtn.innerHTML : '';
  if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Đang lưu'; }
  fetch("promo_api.php", { method: "POST", body: formData })
    .then(r => r.json())
    .then(json => {
      if (json.status === 'success') {
        renderAlert('success', json.message || 'Thêm khuyến mãi thành công');
        try { sessionStorage.setItem('adminActiveTab', getCurrentTabId()); } catch(e) {}
        location.reload();
      } else {
        renderAlert('danger', 'Lỗi: ' + (json.message || 'Không rõ'));
      }
    })
    .catch(err => {
      console.error(err);
      renderAlert('danger', 'Lỗi mạng hoặc server.');
    })
    .finally(() => {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = oldHTML; }
    });
});

// Load dữ liệu khi click nút Sửa khuyến mãi
document.querySelectorAll('.editPromo').forEach(btn => {
  btn.addEventListener('click', function(){
    const id = this.getAttribute('data-id');
    fetch("promo_api.php?action=get&id=" + id)
      .then(r => r.json())
      .then(data => {
        if (data.status === "success") {
          document.getElementById("promo_id").value = data.promo.id;
          document.getElementById("promo_title").value = data.promo.title;
          document.getElementById("promo_description").value = data.promo.description;
          document.getElementById("promo_start").value = data.promo.start_date;
          document.getElementById("promo_end").value = data.promo.end_date;
          document.getElementById("promo_image").src = "images/" + data.promo.image;
        } else {
          alert("Không tìm thấy khuyến mãi");
        }
      })
      .catch(err => {
        console.error(err);
        alert("Lỗi tải dữ liệu khuyến mãi");
      });
  });
});

// DELETE with modal - Products
document.querySelectorAll('.deleteProduct').forEach(btn => {
  btn.addEventListener('click', function(e){
    e.preventDefault();
    const id = this.getAttribute('data-id');
    const modalEl = document.getElementById('confirmDeleteProduct');
    const modal = new bootstrap.Modal(modalEl);
    const confirmBtn = document.getElementById('confirmDeleteProductBtn');
    confirmBtn.href = 'delete_product.php?id=' + encodeURIComponent(id);
    modal.show();
  });
});

// DELETE with modal - Promotions
document.querySelectorAll('.deletePromo').forEach(btn => {
  btn.addEventListener('click', function(e){
    e.preventDefault();
    const id = this.getAttribute('data-id');
    const modalEl = document.getElementById('confirmDeletePromo');
    const modal = new bootstrap.Modal(modalEl);
    const confirmBtn = document.getElementById('confirmDeletePromoBtn');
    confirmBtn.href = 'delete_promo.php?id=' + encodeURIComponent(id);
    modal.show();
  });
});

// EDIT PROMO
document.getElementById("editPromoForm").addEventListener("submit", function(e){
  e.preventDefault();
  const form = this;
  const formData = new FormData(form);
  const submitBtn = form.querySelector('[type="submit"]');
  const oldHTML = submitBtn ? submitBtn.innerHTML : '';
  if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Đang cập nhật'; }
  fetch("promo_api.php", { method:"POST", body: formData })
    .then(r => r.json())
    .then(json => {
      if (json.status === 'success') {
        renderAlert('success', json.message || 'Cập nhật khuyến mãi thành công');
        try { sessionStorage.setItem('adminActiveTab', getCurrentTabId()); } catch(e) {}
        location.reload();
      } else {
        renderAlert('danger', 'Lỗi: ' + (json.message || 'Không rõ'));
      }
    })
    .catch(err => {
      console.error(err);
      renderAlert('danger', 'Lỗi mạng hoặc server.');
    })
    .finally(() => {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = oldHTML; }
    });
});

// Giảm giá trực tiếp theo sản phẩm
const directForm = document.getElementById('directDiscountForm');
if (directForm) {
  directForm.addEventListener('submit', function(e){
    e.preventDefault();
    const btn = directForm.querySelector('[type="submit"]');
    const old = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Đang lưu'; }
    fetch('promo_api.php', { method:'POST', body: new FormData(directForm) })
      .then(r=>r.json())
      .then(j=>{
        if (j.status === 'success') {
          renderAlert('success', j.message || 'Đã lưu giảm giá trực tiếp');
          try { sessionStorage.setItem('adminActiveTab','promo'); } catch(e) {}
          location.reload();
        } else {
          renderAlert('danger', j.message || 'Không thể lưu');
        }
      })
      .catch(()=>renderAlert('danger','Lỗi mạng'))
      .finally(()=>{ if (btn) { btn.disabled = false; btn.innerHTML = old; } });
  });
}

// Xóa dòng khuyến mãi sản phẩm (promotion_items)
document.querySelectorAll('.deletePromoItem').forEach(a=>{
  a.addEventListener('click', function(e){
    e.preventDefault();
    const id = this.getAttribute('data-id');
    if (!id) return;
    if (!confirm('Xóa sản phẩm khỏi khuyến mãi?')) return;
    const fd = new FormData();
    fd.append('action','delete_promo_item');
    fd.append('id', id);
    fetch('promo_api.php', { method:'POST', body: fd })
      .then(r=>r.json())
      .then(j=>{
        if (j.status === 'success') {
          renderAlert('success', j.message || 'Đã xóa');
          try { sessionStorage.setItem('adminActiveTab','promo'); } catch(e) {}
          location.reload();
        } else {
          renderAlert('danger', j.message || 'Không thể xóa');
        }
      })
      .catch(()=>renderAlert('danger','Lỗi mạng'));
  });
});
</script>
</body>
</html>