<footer class="text-center py-3 bg-dark text-white mt-auto">
    <p>&copy; 2025 FastFood. Bản quyền thuộc về Phùng Đức Hiếu. Hotline: 0123456789</p>
</footer>

<!-- Floating Chat (frontend-only) -->
<div class="chat-fab" aria-label="Chat shortcuts">
  <a href="#" class="fab-main" title="Chat qua Messenger">
    <i class="fa-brands fa-facebook-messenger"></i>
  </a>
  <div class="fab-list">
    <!-- Thay link bằng page của bạn -->
    <a href="https://m.me/yourpage" target="_blank" rel="noopener" class="fab-item fb" title="Facebook Page">
      <i class="fa-brands fa-facebook-f"></i>
    </a>
    <a href="tel:0123456789" class="fab-item phone" title="Hotline">
      <i class="fa-solid fa-phone"></i>
    </a>
    <a href="https://zalo.me/yourid" target="_blank" rel="noopener" class="fab-item zalo" title="Zalo">
      <span>Zalo</span>
    </a>
  </div>
</div>

<script>
  (function(){
    const chatFab = document.querySelector('.chat-fab');
    const btn = chatFab ? chatFab.querySelector('.fab-main') : null;
    if(btn && chatFab){
      btn.addEventListener('click', function(e){
        e.preventDefault();
        chatFab.classList.toggle('open');
      });
    }
  })();
</script>
