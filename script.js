const sections = document.querySelectorAll('.fade-section');

const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      observer.unobserve(entry.target); 
    }
  });
}, {
  threshold: 0.15 
});

sections.forEach((section) => observer.observe(section));

function toggleCard(cardId) {
  const card = document.getElementById(cardId);
  if (!card) return;

  card.classList.toggle('expanded');

  const button = card.querySelector('.btn-text-toggle');
  if (button) {
    button.textContent = card.classList.contains('expanded') ? 'View Less' : 'View More';
  }
}

document.addEventListener('DOMContentLoaded', function () {
  // Product card "View More / View Less" toggles (products.php)
  document.querySelectorAll('.btn-text-toggle[data-card-id]').forEach(function (button) {
    button.addEventListener('click', function () {
      toggleCard(button.dataset.cardId);
    });
  });

  // Delete-account confirmation modal (Account/account.php)
  const deleteTrigger = document.getElementById('delete-account-trigger');
  const modal = document.getElementById('delete-modal');
  const cancelBtn = document.getElementById('cancel-delete');

  if (deleteTrigger && modal) {
    deleteTrigger.addEventListener('click', function () {
      modal.classList.add('visible');
    });
  }

  if (cancelBtn && modal) {
    cancelBtn.addEventListener('click', function () {
      modal.classList.remove('visible');
    });
  }

  if (modal) {
    modal.addEventListener('click', function (event) {
      if (event.target === modal) {
        modal.classList.remove('visible');
      }
    });
  }
});

document.addEventListener('DOMContentLoaded', () => {
  // Inject notification element into the DOM if it doesn't already exist
  if (!document.getElementById('cartNotification')) {
    const modal = document.createElement('div');
    modal.id = 'cartNotification';
    modal.className = 'cart-notification-modal';
    modal.innerHTML = `
      <img id="notifImg" src="" alt="" class="cart-notification-img">
      <div class="cart-notification-text">
        <h4 id="notifTitle"></h4>
        <p>Added to your cart successfully!</p>
      </div>
    `;
    document.body.appendChild(modal);
  }

  const notifModal = document.getElementById('cartNotification');
  const notifImg = document.getElementById('notifImg');
  const notifTitle = document.getElementById('notifTitle');
  let timeoutId;

  // Intercept all product add-to-cart forms
  const addForms = document.querySelectorAll('form[action="Cart/add_to_cart.php"]');
  addForms.forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      const formData = new FormData(form);

      fetch('Cart/add_to_cart.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          notifImg.src = data.item.image;
          notifTitle.textContent = data.item.name;

          // Trigger display
          notifModal.classList.add('show');

          // Reset timer if clicked multiple times rapidly
          clearTimeout(timeoutId);

          // Automatically fade out after 3 seconds
          timeoutId = setTimeout(() => {
            notifModal.classList.remove('show');
          }, 3000);
        } else if (data.message === 'Not logged in') {
          window.location.href = 'Login/login.php';
        }
      })
      .catch(err => console.error('Cart error:', err));
    });
  });
});

// Asynchronous Cart Quantity & Removal Handler (Cart/cart.php)
document.addEventListener('click', function(e) {
  if (e.target.classList.contains('qty-btn') || e.target.classList.contains('remove-btn')) {
    e.preventDefault();
    
    const form = e.target.closest('form');
    const formData = new FormData(form);
    
    // Explicitly append the clicked button's action value ('increase', 'decrease', or 'remove')
    formData.append('action', e.target.value);

    fetch('cart.php', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        const cartContainer = document.querySelector('.cart-container');
        if (cartContainer && data.cart_html) {
          cartContainer.innerHTML = data.cart_html;
        }
      }
    })
    .catch(err => console.error('Cart update error:', err));
  }
});

// Checkout Modal Interaction Handler (Event Delegation for AJAX safety)
document.addEventListener('click', (e) => {
  const checkoutModal = document.getElementById('checkoutModal');
  
  // 1. Open Modal when clicking "Proceed to Checkout"
  if (e.target && e.target.id === 'openCheckoutBtn') {
    if (checkoutModal) {
      checkoutModal.classList.add('active');
      checkoutModal.classList.add('show');
    }
  }

  // 2. Close Modal when clicking the close button (if you add one later)
  if (e.target && e.target.id === 'closeCheckoutBtn') {
    if (checkoutModal) {
      checkoutModal.classList.remove('active');
      checkoutModal.classList.remove('show');
    }
  }

  // 3. Close Modal when clicking outside the modal content overlay
  if (checkoutModal && e.target === checkoutModal) {
    checkoutModal.classList.remove('active');
    checkoutModal.classList.remove('show');
  }
});

document.addEventListener('click', (e) => {
  const checkoutModal = document.getElementById('checkoutModal');
  
  // Open Modal
  if (e.target && e.target.id === 'openCheckoutBtn') {
    if (checkoutModal) {
      checkoutModal.classList.add('active', 'show');
    }
  }

  // Close confirmation modal and reload or redirect
  if (e.target && e.target.id === 'closeConfirmationBtn') {
    window.location.href = 'cart.php';
  }

  // Close modal on background click
  if (checkoutModal && e.target === checkoutModal) {
    checkoutModal.classList.remove('active', 'show');
  }
});

// Handle asynchronous checkout form submission
document.addEventListener('submit', async (e) => {
  if (e.target && e.target.id === 'checkoutForm') {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
      // Point directly to your process_checkout.php file inside the Cart folder:
      const response = await fetch('cart.php', {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });
      
      const data = await response.json();
      
      if (data.success) {
        const formView = document.getElementById('checkoutFormView');
        const confirmedView = document.getElementById('orderConfirmedView');
        const dateRangeSpan = document.getElementById('deliveryDateRange');
        
        if (formView && confirmedView && dateRangeSpan) {
          formView.style.display = 'none';
          confirmedView.style.display = 'flex';
          dateRangeSpan.textContent = data.delivery_window;
        }
      } else {
        alert(data.message || 'Checkout failed. Please try again.');
      }
    } catch (error) {
      console.error('Checkout error:', error);
      alert('An unexpected error occurred.');
    }
  }
});

// Example of how your product card button should pass the product_id
document.querySelectorAll('.add-to-cart-btn').forEach(button => {
    button.addEventListener('click', async (e) => {
        const productId = e.target.dataset.productId; // e.g., data-product-id="1"
        const name = e.target.dataset.name;
        const price = e.target.dataset.price;
        const specs = e.target.dataset.specs;
        const image = e.target.dataset.image;

        const formData = new FormData();
        formData.append('product_id', productId); // Must match what add_to_cart.php expects!
        formData.append('name', name);
        formData.append('price', price);
        formData.append('specs', specs);
        formData.append('image', image);

        const response = await fetch('add_to_cart.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            alert('Added to cart!');
        }
    });
});