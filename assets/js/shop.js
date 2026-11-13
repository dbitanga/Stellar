// Store page behaviour, carried over from the midterm's script.js.

// Fade sections in as they scroll into view.
const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      observer.unobserve(entry.target);
    }
  });
}, { threshold: 0.15 });

document.querySelectorAll('.fade-section').forEach((section) => observer.observe(section));

// Product card "View More / View Less".
document.querySelectorAll('.btn-text-toggle[data-card-id]').forEach((button) => {
  button.addEventListener('click', () => {
    const card = document.getElementById(button.dataset.cardId);
    if (!card) return;
    card.classList.toggle('expanded');
    button.textContent = card.classList.contains('expanded') ? 'View Less' : 'View More';
  });
});

// Add to cart without leaving the products page, then show the "added" pop-up.
const addForms = document.querySelectorAll('form.add-to-cart-form');
if (addForms.length) {
  const notif = document.createElement('div');
  notif.className = 'cart-notification-modal';
  notif.innerHTML = `
    <img src="" alt="" class="cart-notification-img">
    <div class="cart-notification-text">
      <h4></h4>
      <p>Added to your cart successfully!</p>
    </div>`;
  document.body.appendChild(notif);
  let hideTimer;

  const showNotification = (title, image, message) => {
    notif.querySelector('img').src = image || '';
    notif.querySelector('img').style.display = image ? '' : 'none';
    notif.querySelector('h4').textContent = title;
    notif.querySelector('p').textContent = message;
    notif.classList.add('show');
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => notif.classList.remove('show'), 3000);
  };

  addForms.forEach((form) => {
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: new FormData(form),
          headers: { Accept: 'application/json' },
        });

        // Not logged in: the firewall redirects to the login page instead of answering JSON.
        if (!(response.headers.get('Content-Type') || '').includes('application/json')) {
          window.location.href = form.dataset.loginUrl;
          return;
        }

        const data = await response.json();
        if (data.success) {
          showNotification(data.item.name, data.item.image, 'Added to your cart successfully!');
        } else {
          showNotification('Could not add to cart', null, data.message);
        }
      } catch (error) {
        form.submit();
      }
    });
  });
}

// Checkout modal on the cart page.
const checkoutModal = document.getElementById('checkoutModal');
if (checkoutModal) {
  document.getElementById('openCheckoutBtn')?.addEventListener('click', () => {
    checkoutModal.classList.add('active', 'show');
  });
  checkoutModal.addEventListener('click', (event) => {
    if (event.target === checkoutModal) {
      checkoutModal.classList.remove('active', 'show');
    }
  });
}

// Cart + / − / Remove reload the page; skip the fade-in on that reload (read in base.html.twig).
document.querySelectorAll('.cart-item-actions form').forEach((form) => {
  form.addEventListener('submit', () => {
    try { sessionStorage.setItem('stellarNoFade', '1'); } catch (e) {}
  });
});

// Cancel-order confirmation on the account page, in the same style as the delete-account modal.
const cancelModal = document.getElementById('cancel-order-modal');
if (cancelModal) {
  let pendingForm = null;
  const closeCancel = () => { cancelModal.classList.remove('visible'); pendingForm = null; };

  document.querySelectorAll('form.cancel-order-form').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (form.dataset.confirmed) return;
      event.preventDefault();
      pendingForm = form;
      cancelModal.querySelector('[data-order-number]').textContent = '#' + form.dataset.orderId;
      cancelModal.classList.add('visible');
    });
  });

  cancelModal.querySelector('[data-confirm-cancel]').addEventListener('click', () => {
    if (!pendingForm) return;
    pendingForm.dataset.confirmed = '1';
    pendingForm.submit();
  });
  cancelModal.querySelector('[data-modal-close]').addEventListener('click', closeCancel);
  cancelModal.addEventListener('click', (event) => {
    if (event.target === cancelModal) closeCancel();
  });
}

// Delete-account confirmation on the account page.
const deleteModal = document.getElementById('delete-modal');
if (deleteModal) {
  document.getElementById('delete-account-trigger')?.addEventListener('click', () => deleteModal.classList.add('visible'));
  document.getElementById('cancel-delete')?.addEventListener('click', () => deleteModal.classList.remove('visible'));
  deleteModal.addEventListener('click', (event) => {
    if (event.target === deleteModal) {
      deleteModal.classList.remove('visible');
    }
  });
}
