<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
#cart-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
#cart-modal.show { display: flex; }
#cart-modal .modal-inner {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
}
</style>
</head>
<body>
<button id="test-btn">Mở modal</button>

<div id="cart-modal">
  <div class="modal-inner">
    <button id="close-modal">X</button>
    <p>Hello modal</p>
  </div>
</div>

<script>
const modal=document.getElementById('cart-modal');
document.getElementById('test-btn').addEventListener('click', ()=> modal.classList.add('show'));
document.getElementById('close-modal').addEventListener('click', ()=> modal.classList.remove('show'));
</script>
</body>
</html>
