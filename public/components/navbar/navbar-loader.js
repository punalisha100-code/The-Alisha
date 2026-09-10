(function () {
  const root = document.getElementById('navbar-root');
  if (!root) return;

  // Load CSS files
  const cssPaths = [
    '/The-Alisha/public/components/navbar/navbar.css',
    '/The-Alisha/public/components/cart/cart.css'
  ];

  cssPaths.forEach((href) => {
    if (!document.querySelector(`link[href="${href}"]`)) {
      const link = document.createElement('link');
      link.rel = 'stylesheet';
      link.href = href;
      document.head.appendChild(link);
    }
  });

  // Load Cart Manager JS if not loaded
  if (!window.AlishaCart && !document.querySelector('script[src*="cart-manager.js"]')) {
    const cartScript = document.createElement('script');
    cartScript.src = '/The-Alisha/public/components/cart/cart-manager.js';
    document.body.appendChild(cartScript);
  }

  fetch('/The-Alisha/public/components/navbar/navbar.html')
    .then((response) => response.text())
    .then((html) => {
      root.innerHTML = html;
      const menuToggle = root.querySelector('.navbar-menu-toggle');
      const mobileMenu = root.querySelector('#navbar-mobile-menu');
      const mobileLinks = root.querySelectorAll('.navbar-mobile-menu a');
      const navLinks = root.querySelectorAll('.nav-link');
      const path = window.location.pathname.split('/').pop() || 'index.html';

      if (navLinks.length) {
        navLinks.forEach((link) => {
          const page = link.dataset.navPage;
          if (page && (page === path || (page === 'index.html' && (path === '' || path === 'index.html')))) {
            link.classList.add('active');
          }
        });
      }

      if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', () => {
          const open = !mobileMenu.classList.contains('open');
          mobileMenu.classList.toggle('open', open);
          mobileMenu.hidden = !open;
          menuToggle.setAttribute('aria-expanded', String(open));
        });

        document.addEventListener('click', (event) => {
          if (!event.target.closest('.alisha-navbar')) {
            mobileMenu.classList.remove('open');
            mobileMenu.hidden = true;
            menuToggle.setAttribute('aria-expanded', 'false');
          }
        });
      }

      mobileLinks.forEach((link) => {
        link.addEventListener('click', () => {
          mobileMenu.classList.remove('open');
          mobileMenu.hidden = true;
          menuToggle?.setAttribute('aria-expanded', 'false');
        });
      });

      // Check session
      fetch('/The-Alisha/server-side/backend/auth/session.php', {
        credentials: 'include'
      })
        .then((response) => response.json())
        .then((session) => {
          if (!session.authenticated || !session.user) return;

          const user = session.user;
          const firstName = (user.fullName || 'Account').split(' ')[0];

          // Show My Orders in mobile menu
          root.querySelectorAll('[data-my-orders-link]').forEach((el) => {
            el.style.display = 'block';
          });

          // Update Desktop Auth Button
          const userWrap = root.querySelector('#navUserMenu');
          if (userWrap) {
            const isAdmin = (user.role || '') === 'admin';
            userWrap.innerHTML = `
              <div class="user-dropdown-container" style="position:relative; display:inline-block;">
                <button type="button" class="user-greeting-btn" style="background: rgba(248,92,168,0.12); border: 1px solid rgba(248,92,168,0.3); color: var(--cart-primary-dark, #c83b77); border-radius: 999px; padding: 0.45rem 0.9rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.4rem; font-size: 0.88rem;">
                  <span>👤</span>
                  <span>${firstName}</span>
                  <span style="font-size:0.7rem;">▼</span>
                </button>
                <div class="user-dropdown-menu" style="display:none; position:absolute; right:0; top:calc(100% + 6px); background:#fff; border-radius:14px; box-shadow:0 14px 35px rgba(0,0,0,0.15); border:1px solid rgba(200,59,119,0.15); min-width:180px; padding:0.5rem 0; z-index:10000; text-align:left;">
                  <div style="padding:0.5rem 1rem; border-bottom:1px solid #f2e6ee; font-size:0.82rem; color:#6c6379;">Signed in as<br><strong style="color:#2d2436;">${user.email || user.fullName}</strong></div>
                  <a href="/The-Alisha/my-orders.html" style="display:block; padding:0.6rem 1rem; color:#2d2436; text-decoration:none; font-size:0.88rem; font-weight:600;">📦 My Orders</a>
                  ${isAdmin ? '<a href="/The-Alisha/admin/index.php" style="display:block; padding:0.6rem 1rem; color:#4f46e5; text-decoration:none; font-size:0.88rem; font-weight:700;">⚙️ Admin Panel</a>' : ''}
                  <a href="#" id="desktopLogoutBtn" style="display:block; padding:0.6rem 1rem; color:#ef4444; text-decoration:none; font-size:0.88rem; font-weight:600; border-top:1px solid #f2e6ee;">🚪 Sign Out</a>
                </div>
              </div>
            `;

            const greetingBtn = userWrap.querySelector('.user-greeting-btn');
            const dropMenu = userWrap.querySelector('.user-dropdown-menu');

            if (greetingBtn && dropMenu) {
              greetingBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                dropMenu.style.display = dropMenu.style.display === 'none' ? 'block' : 'none';
              });

              document.addEventListener('click', () => {
                dropMenu.style.display = 'none';
              });
            }

            document.getElementById('desktopLogoutBtn')?.addEventListener('click', async (e) => {
              e.preventDefault();
              await fetch('/The-Alisha/server-side/backend/logout.php', { method: 'POST', credentials: 'include' });
              window.location.reload();
            });
          }

          // Update Mobile Auth Link
          root.querySelectorAll('.mobile-utilities [data-auth-link]').forEach((link) => {
            link.textContent = `Sign Out (${firstName})`;
            link.href = '#';
            link.addEventListener('click', async (event) => {
              event.preventDefault();
              await fetch('/The-Alisha/server-side/backend/logout.php', { method: 'POST', credentials: 'include' });
              window.location.reload();
            });
          });
        })
        .catch((error) => console.warn('Session check note:', error));

      // Trigger cart badge refresh
      if (window.AlishaCart) {
        window.AlishaCart.recalculate();
      }
    })
    .catch((error) => {
      console.error('Navbar load failed', error);
    });
})();

