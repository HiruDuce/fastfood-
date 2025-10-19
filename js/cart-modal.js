// cart-modal.js - handle open modal, add to cart, buy now, toast, and cart count update
(function(){
  let currentProduct = {};

  document.addEventListener('click', function(e){
    if(e.target.classList.contains('open-modal-btn')){
      e.preventDefault();
      const form = e.target.closest('.add-to-cart-form');
      if(!form) return;
      currentProduct = {
        id: form.querySelector('[name="product_id"]').value,
        name: form.querySelector('[name="name"]').value,
        price: form.querySelector('[name="price"]').value,
        image: form.querySelector('[name="image"]').value
      };
      document.getElementById('modal-image').src = 'images/' + currentProduct.image;
      document.getElementById('modal-name').textContent = currentProduct.name;
      document.getElementById('modal-price').textContent = parseInt(currentProduct.price).toLocaleString() + '₫';
      document.getElementById('modal-qty').value = 1;
      const modalElement = document.getElementById('cartModal');
      const cartModal = new bootstrap.Modal(modalElement);
      cartModal.show();
    }

    if(e.target.classList.contains('buy-now-btn')){
      e.preventDefault();
      const form = e.target.closest('.add-to-cart-form');
      if(!form) return;
      currentProduct = {
        id: form.querySelector('[name="product_id"]').value,
        name: form.querySelector('[name="name"]').value,
        price: form.querySelector('[name="price"]').value,
        image: form.querySelector('[name="image"]').value
      };
      document.getElementById('modal-image').src = 'images/' + currentProduct.image;
      document.getElementById('modal-name').textContent = currentProduct.name;
      document.getElementById('modal-price').textContent = parseInt(currentProduct.price).toLocaleString() + '₫';
      document.getElementById('modal-qty').value = 1;
      const modalElement = document.getElementById('cartModal');
      const cartModal = new bootstrap.Modal(modalElement);
      cartModal.show();
    }
  });

  // Add to cart from modal
  document.addEventListener('DOMContentLoaded', function(){
    const addBtn = document.getElementById('modal-add-btn');
    if(addBtn){
      addBtn.addEventListener('click', function(){
        const qty = parseInt(document.getElementById('modal-qty').value);
        const formData = new FormData();
        formData.append('add_to_cart', true);
        formData.append('product_id', currentProduct.id);
        formData.append('name', currentProduct.name);
        formData.append('price', currentProduct.price);
        formData.append('image', currentProduct.image);
        formData.append('quantity', qty);
        fetch('index.php', {method:'POST', body:formData})
        .then(()=>{
          const modalElement = document.getElementById('cartModal');
          const cartModal = bootstrap.Modal.getInstance(modalElement);
          if(cartModal) cartModal.hide();
          const countEl = document.getElementById('cart-count');
          if (countEl) {
            const current = parseInt((countEl.textContent || '').replace(/[^0-9]/g,'')) || 0;
            const next = current + qty;
            countEl.textContent = `(${next})`;
          }
          const toastEl = document.getElementById('addSuccessToast');
          const toast = bootstrap.Toast.getOrCreateInstance(toastEl);
          toast.show();
        });
      });
    }

    const buyBtn = document.getElementById('modal-buy-btn');
    if(buyBtn){
      buyBtn.addEventListener('click', function(){
        const qty = parseInt(document.getElementById('modal-qty').value);
        const formData = new FormData();
        formData.append('product_id', currentProduct.id);
        formData.append('quantity', qty);
        fetch('buy_now.php', {method:'POST', body:formData})
        .then(()=>{
          const modalElement = document.getElementById('cartModal');
          const cartModal = bootstrap.Modal.getInstance(modalElement);
          if(cartModal) cartModal.hide();
          window.location.href = 'checkout.php?buy_now=1';
        });
      });
    }
  });
})();
