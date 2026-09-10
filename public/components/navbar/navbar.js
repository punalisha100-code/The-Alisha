const navbarMenuToggle = document.querySelector('.navbar-menu-toggle');
const mobileMenu = document.getElementById('navbar-mobile-menu');
const desktopLinks = document.querySelectorAll('.navbar-links a');
const mobileLinks = document.querySelectorAll('.navbar-mobile-menu a');
const allNavLinks = [...desktopLinks, ...mobileLinks];

const closeMobileMenu = () => {
  if (!mobileMenu || !navbarMenuToggle) return;
  mobileMenu.classList.remove('open');
  mobileMenu.hidden = true;
  mobileMenu.setAttribute('aria-hidden', 'true');
  navbarMenuToggle.setAttribute('aria-expanded', 'false');
};

const openMobileMenu = () => {
  if (!mobileMenu || !navbarMenuToggle) return;
  mobileMenu.hidden = false;
  mobileMenu.classList.add('open');
  mobileMenu.setAttribute('aria-hidden', 'false');
  navbarMenuToggle.setAttribute('aria-expanded', 'true');
};

const toggleMobileMenu = () => {
  if (!mobileMenu || !navbarMenuToggle) return;
  const isOpen = mobileMenu.classList.contains('open');

  if (isOpen) {
    closeMobileMenu();
    return;
  }

  openMobileMenu();
};

navbarMenuToggle?.addEventListener('click', (event) => {
  event.stopPropagation();
  toggleMobileMenu();
});

window.addEventListener('click', (event) => {
  if (!event.target.closest('.alisha-navbar') && mobileMenu?.classList.contains('open')) {
    closeMobileMenu();
  }
});

window.addEventListener('keydown', (event) => {
  if (event.key === 'Escape' && mobileMenu?.classList.contains('open')) {
    closeMobileMenu();
  }
});

mobileLinks.forEach((link) => {
  link.addEventListener('click', () => {
    closeMobileMenu();
  });
});

const updateCounters = () => {
  const cartCount = Number(localStorage.getItem('alishaCartCount') || '0');
  const wishlistCount = Number(localStorage.getItem('alishaWishlistCount') || '0');

  document.querySelectorAll('.cart-badge').forEach((badge) => {
    badge.textContent = String(cartCount);
    badge.toggleAttribute('data-has-items', cartCount > 0);
  });

  document.querySelectorAll('.wishlist-badge').forEach((badge) => {
    badge.textContent = String(wishlistCount);
    badge.toggleAttribute('data-has-items', wishlistCount > 0);
  });
};

const highlightActiveLink = () => {
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  allNavLinks.forEach((link) => {
    const href = link.getAttribute('href') || '';
    if (href.endsWith(currentPage)) {
      link.classList.add('active');
    } else {
      link.classList.remove('active');
    }
  });
};

window.addEventListener('DOMContentLoaded', () => {
  updateCounters();
  highlightActiveLink();
});
