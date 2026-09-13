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