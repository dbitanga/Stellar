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

document.addEventListener('click', function(e) {
  if (e.target.classList.contains('qty-btn') || e.target.classList.contains('remove-btn')) {
    e.preventDefault();
    
    const form = e.target.closest('form');
    const formData = new FormData(form);
    formData.append('action', e.target.value);

    fetch('cart.php', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        if (data.cart_empty) {
          location.reload(); // Refresh cleanly only if cart becomes completely empty to show empty state
          return;
        }

        // Update item list and summary dynamically without full page reload
        // (Alternatively, you can dynamically redraw the items container here based on data.items)
        location.reload(); // Or swap out DOM nodes seamlessly
      }
    })
    .catch(err => console.error('Cart update error:', err));
  }
});