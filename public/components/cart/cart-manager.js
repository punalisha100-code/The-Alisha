/**
 * =========================================================
 * THE ALISHA WEARS — UNIVERSAL CLIENT CART MANAGER
 * =========================================================
 */
(function (global) {
  'use strict';

  if (global.AlishaCart && global.AlishaCart.__initialized) {
    return;
  }

  const API_BASE = '/The-Alisha/server-side/backend';
  const STORAGE_KEY = 'alisha_cart_cache';
  const CART_SESSION_KEY = 'alisha_cart_id';

  // Get or initialize client-side cart token
  function getCartSessionId() {
    let id = localStorage.getItem(CART_SESSION_KEY);
    if (!id) {
      id = 'cart_' + Math.random().toString(36).substring(2, 12) + Date.now().toString(36);
      localStorage.setItem(CART_SESSION_KEY, id);
    }
    return id;
  }

  const sessionId = getCartSessionId();

  // Core Cart State
  const state = {
    items: [],
    subtotal: 0,
    shipping: 0,
    total: 0,
    itemCount: 0,
    isOpen: false,
    coupon: null,
  };

  const addDebounceMap = new Map();

  function normalizeCartItems() {
    const merged = new Map();

    state.items.forEach((item) => {
      const productId = String(item.productId || item.id || '').trim();
      const size = String(item.size || 'M');
      const key = `${productId}|${size}`;
      const qty = Number(item.quantity) || 1;

      if (!productId) return;

      if (!merged.has(key)) {
        merged.set(key, {
          ...item,
          productId,
          size,
          quantity: qty,
          price: Number(item.price) || 0,
          color: item.color || 'Standard',
          image: item.image || ''
        });
        return;
      }

      const current = merged.get(key);
      current.quantity = (Number(current.quantity) || 0) + qty;
      current.price = Number(current.price) || Number(item.price) || 0;
      current.name = current.name || item.name || 'Product';
      current.image = current.image || item.image || '';
      current.color = current.color || item.color || 'Standard';
    });

    state.items = Array.from(merged.values());
  }

  // Load cached cart from localStorage for instant initial render
  try {
    const cached = JSON.parse(localStorage.getItem(STORAGE_KEY));
    if (cached && Array.isArray(cached.items)) {
      Object.assign(state, cached);
      state.items = Array.isArray(state.items) ? state.items : [];
      normalizeCartItems();
    }
  } catch (e) {
    // ignore
  }

  function formatPrice(val) {
    const num = parseFloat(val) || 0;
    return 'Rs. ' + num.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function escapeHTML(str) {
    return String(str || '').replace(/[&<>'"]/g, (tag) => {
      const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' };
      return map[tag] || tag;
    });
  }

  function recalculate() {
    normalizeCartItems();

    let sub = 0;
    let count = 0;
    state.items.forEach((item) => {
      item.quantity = Math.max(1, parseInt(item.quantity, 10) || 1);
      item.price = parseFloat(item.price) || 0;
      item.lineTotal = item.price * item.quantity;
      sub += item.lineTotal;
      count += item.quantity;
    });

    state.subtotal = sub;
    state.itemCount = count;

    let discount = 0;
    if (state.coupon) {
      if (state.coupon.type === 'percent') {
        discount = Math.round((sub * state.coupon.value) / 100);
      } else if (state.coupon.type === 'flat') {
        discount = Math.min(sub, state.coupon.value);
      }
    }
    state.discount = discount;

    const isFreeShip = sub >= 2000 || (state.coupon && state.coupon.type === 'free_shipping') || sub === 0;
    state.shipping = isFreeShip ? 0 : 150;
    state.total = Math.max(0, sub - discount + state.shipping);

    // Save cache
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify({
        items: state.items,
        subtotal: state.subtotal,
        shipping: state.shipping,
        total: state.total,
        itemCount: state.itemCount,
        coupon: state.coupon
      }));
      localStorage.setItem('alishaCartCount', String(state.itemCount));
    } catch (e) {
      // ignore
    }

    updateBadges();
    renderDrawer();
    window.dispatchEvent(new CustomEvent('alisha:cart-updated', { detail: state }));
  }

  function updateBadges() {
    const badges = document.querySelectorAll('.cart-badge, .cart-count, [data-cart-count]');
    badges.forEach((b) => {
      b.textContent = String(state.itemCount);
      if (b.classList) {
        b.classList.toggle('has-items', state.itemCount > 0);
        b.toggleAttribute('data-has-items', state.itemCount > 0);
      }
    });
  }

  // Inject UI Components
  function injectDrawerDOM() {
    if (document.getElementById('alishaCartDrawer')) return;

    // Overlay
    const overlay = document.createElement('div');
    overlay.id = 'alishaCartDrawerOverlay';
    overlay.className = 'alisha-cart-drawer-overlay';
    overlay.addEventListener('click', closeDrawer);

    // Drawer container
    const drawer = document.createElement('aside');
    drawer.id = 'alishaCartDrawer';
    drawer.className = 'alisha-cart-drawer';
    drawer.setAttribute('aria-label', 'Shopping Cart');
    drawer.innerHTML = `
      <div class="alisha-drawer-header">
        <h2 class="alisha-drawer-title">
          <span>🛍️ Your Bag</span>
          <span class="alisha-drawer-count" id="alishaDrawerCount">0</span>
        </h2>
        <button class="alisha-drawer-close" id="alishaDrawerCloseBtn" aria-label="Close cart">&times;</button>
      </div>

      <div class="alisha-shipping-meter" id="alishaShippingMeter">
        <div class="alisha-shipping-text">
          <span id="alishaShippingMsg">Add items to unlock Free Shipping</span>
          <span id="alishaShippingTarget">Goal: Rs. 2,000</span>
        </div>
        <div class="alisha-progress-track">
          <div class="alisha-progress-fill" id="alishaShippingProgress" style="width: 0%;"></div>
        </div>
      </div>

      <div class="alisha-drawer-body" id="alishaDrawerBody">
        <!-- Rendered via JS -->
      </div>

      <div class="alisha-drawer-footer" id="alishaDrawerFooter">
        <div class="alisha-summary-row">
          <span>Subtotal</span>
          <strong id="alishaDrawerSubtotal">Rs. 0.00</strong>
        </div>
        <div class="alisha-summary-row" id="alishaDrawerDiscountRow" style="display: none;">
          <span style="color: var(--cart-primary-dark)">Discount</span>
          <strong id="alishaDrawerDiscount" style="color: var(--cart-primary-dark)">-Rs. 0.00</strong>
        </div>
        <div class="alisha-summary-row">
          <span>Delivery</span>
          <span id="alishaDrawerShipping">Rs. 150.00</span>
        </div>
        <div class="alisha-summary-row total-row">
          <span>Total</span>
          <span id="alishaDrawerTotal">Rs. 0.00</span>
        </div>

        <div class="alisha-drawer-actions">
          <a href="/The-Alisha/cart.html?checkout=1" class="alisha-checkout-btn">
            <span>Proceed to Checkout</span>
            <span>&rarr;</span>
          </a>
          <a href="/The-Alisha/cart.html" class="alisha-viewcart-btn">View Bag & Apply Coupon</a>
          <button type="button" class="alisha-clear-cart-link" id="alishaClearCartBtn">Clear Entire Bag</button>
        </div>
      </div>
    `;

    // Toast container
    const toastContainer = document.createElement('div');
    toastContainer.id = 'alishaToastContainer';
    toastContainer.className = 'alisha-toast-container';

    document.body.appendChild(overlay);
    document.body.appendChild(drawer);
    document.body.appendChild(toastContainer);

    document.getElementById('alishaDrawerCloseBtn')?.addEventListener('click', closeDrawer);
    document.getElementById('alishaClearCartBtn')?.addEventListener('click', () => {
      if (state.items.length === 0) return;
      if (confirm('Are you sure you want to remove all items from your bag?')) {
        clearCart();
      }
    });

    // Handle Escape key
    window.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && state.isOpen) {
        closeDrawer();
      }
    });
  }

  function renderDrawer() {
    const body = document.getElementById('alishaDrawerBody');
    const countBadge = document.getElementById('alishaDrawerCount');
    const subtotalEl = document.getElementById('alishaDrawerSubtotal');
    const shippingEl = document.getElementById('alishaDrawerShipping');
    const totalEl = document.getElementById('alishaDrawerTotal');
    const discountRow = document.getElementById('alishaDrawerDiscountRow');
    const discountEl = document.getElementById('alishaDrawerDiscount');
    const shippingMsg = document.getElementById('alishaShippingMsg');
    const shippingProgress = document.getElementById('alishaShippingProgress');
    const footer = document.getElementById('alishaDrawerFooter');

    if (!body) return;

    if (countBadge) countBadge.textContent = String(state.itemCount);
    if (subtotalEl) subtotalEl.textContent = formatPrice(state.subtotal);
    if (shippingEl) shippingEl.textContent = state.shipping === 0 ? 'FREE' : formatPrice(state.shipping);
    if (totalEl) totalEl.textContent = formatPrice(state.total);

    if (discountRow && discountEl) {
      if (state.discount && state.discount > 0) {
        discountRow.style.display = 'flex';
        discountEl.textContent = '-' + formatPrice(state.discount);
      } else {
        discountRow.style.display = 'none';
      }
    }

    // Shipping meter calculation
    if (shippingMsg && shippingProgress) {
      const freeThreshold = 2000;
      if (state.subtotal >= freeThreshold) {
        shippingMsg.innerHTML = '🎉 You unlocked <strong>FREE Shipping!</strong>';
        shippingProgress.style.width = '100%';
      } else if (state.subtotal > 0) {
        const remaining = freeThreshold - state.subtotal;
        shippingMsg.innerHTML = `Add <strong>${formatPrice(remaining)}</strong> more for FREE shipping`;
        shippingProgress.style.width = Math.min(100, Math.round((state.subtotal / freeThreshold) * 100)) + '%';
      } else {
        shippingMsg.textContent = 'Add items over Rs. 2,000 for FREE Shipping';
        shippingProgress.style.width = '0%';
      }
    }

    if (state.items.length === 0) {
      body.innerHTML = `
        <div class="alisha-drawer-empty">
          <div class="alisha-empty-icon">🛍️</div>
          <h3>Your bag is empty</h3>
          <p>Explore our curated collections and add your favorite pieces.</p>
          <a href="/The-Alisha/Client-side/Home/newArrival/new-Arrivals.html" class="alisha-shop-now-btn" onclick="AlishaCart.closeDrawer()">Start Shopping</a>
        </div>
      `;
      if (footer) footer.style.display = 'none';
      return;
    }

    if (footer) footer.style.display = 'block';

    body.innerHTML = state.items.map((item) => {
      const safeId = escapeHTML(item.productId);
      const safeName = escapeHTML(item.name);
      const safeImg = escapeHTML(item.image || 'https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=400&q=80');
      const safeSize = escapeHTML(item.size || 'M');

      return `
        <article class="alisha-cart-item" data-product-id="${safeId}">
          <div class="alisha-item-thumb">
            <img src="${safeImg}" alt="${safeName}" onerror="this.src='https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=400&q=80'">
          </div>
          <div class="alisha-item-details">
            <div>
              <h4 class="alisha-item-title">${safeName}</h4>
              <div class="alisha-item-meta">Size: ${safeSize}</div>
              <div class="alisha-item-price">${formatPrice(item.price)}</div>
            </div>
            <div class="alisha-item-controls">
              <div class="alisha-qty-box">
                <button type="button" class="alisha-qty-btn" onclick="AlishaCart.updateQuantity('${safeId}', ${item.quantity - 1})" aria-label="Decrease quantity">&minus;</button>
                <span class="alisha-qty-val">${item.quantity}</span>
                <button type="button" class="alisha-qty-btn" onclick="AlishaCart.updateQuantity('${safeId}', ${item.quantity + 1})" aria-label="Increase quantity">&plus;</button>
              </div>
              <button type="button" class="alisha-item-delete" onclick="AlishaCart.removeItem('${safeId}')" title="Delete this item">
                <span>🗑️</span> Remove
              </button>
            </div>
          </div>
        </article>
      `;
    }).join('');
  }

  function openDrawer() {
    injectDrawerDOM();
    state.isOpen = true;
    document.getElementById('alishaCartDrawer')?.classList.add('is-open');
    document.getElementById('alishaCartDrawerOverlay')?.classList.add('is-open');
    document.body.style.overflow = 'hidden';
  }

  function closeDrawer() {
    state.isOpen = false;
    document.getElementById('alishaCartDrawer')?.classList.remove('is-open');
    document.getElementById('alishaCartDrawerOverlay')?.classList.remove('is-open');
    document.body.style.overflow = '';
  }

  function toggleDrawer() {
    if (state.isOpen) closeDrawer();
    else openDrawer();
  }

  function showToast(message, type = 'success', actionText = 'View Bag') {
    injectDrawerDOM();
    const container = document.getElementById('alishaToastContainer');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `alisha-toast ${type}`;
    const icon = type === 'error' ? '✕' : (type === 'info' ? 'ℹ' : '✓');

    toast.innerHTML = `
      <div class="alisha-toast-icon">${icon}</div>
      <div class="alisha-toast-content">
        <p class="alisha-toast-title">${escapeHTML(message)}</p>
      </div>
      ${actionText ? `<button type="button" class="alisha-toast-action">${escapeHTML(actionText)}</button>` : ''}
    `;

    if (actionText) {
      toast.querySelector('.alisha-toast-action')?.addEventListener('click', () => {
        openDrawer();
        toast.remove();
      });
    }

    container.appendChild(toast);

    setTimeout(() => {
      toast.classList.add('hiding');
      setTimeout(() => toast.remove(), 300);
    }, 3200);
  }

  // Sync with backend API
  async function syncFromBackend() {
    try {
      const res = await fetch(`${API_BASE}/cart.php`, {
        credentials: 'include',
        headers: { 'X-Cart-Session': sessionId }
      });
      if (!res.ok) return;
      const data = await res.json();
      if (data.success && data.cart && Array.isArray(data.cart.items)) {
        state.items = data.cart.items;
        recalculate();
      }
    } catch (e) {
      // Keep local state
    }
  }

  async function syncToBackend(action, payload = {}) {
    try {
      await fetch(`${API_BASE}/cart.php`, {
        method: action === 'delete' || action === 'clear' ? 'POST' : (action === 'put' ? 'PUT' : 'POST'),
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'X-Cart-Session': sessionId
        },
        body: JSON.stringify({ ...payload, action })
      });
    } catch (e) {
      console.warn('Backend cart sync failed, operating in offline cache mode', e);
    }
  }

  // Cart CRUD API
  function addItem(product) {
    const pId = String(product.productId || product.id || '').trim();
    const pName = String(product.name || product.title || 'Selected Product').trim();
    const pPrice = parseFloat(product.price) || 0;
    const pQty = Math.max(1, parseInt(product.quantity, 10) || 1);
    const pImg = String(product.image || product.imageUrl || '');
    const pSize = String(product.size || 'M');
    const pColor = String(product.color || 'Standard');

    if (!pId || pPrice <= 0) {
      showToast('Could not add item to bag (invalid product)', 'error');
      return;
    }

    const now = Date.now();
    const dedupeKey = `${pId}|${pSize}|${pColor}`;
    const lastAdd = addDebounceMap.get(dedupeKey) || 0;
    if (now - lastAdd < 300) {
      return;
    }
    addDebounceMap.set(dedupeKey, now);
    setTimeout(() => addDebounceMap.delete(dedupeKey), 500);

    const existing = state.items.find((item) => item.productId === pId && (item.size || 'M') === pSize);
    if (existing) {
      existing.quantity += pQty;
      if (pImg && !existing.image) existing.image = pImg;
    } else {
      state.items.push({
        productId: pId,
        name: pName,
        price: pPrice,
        quantity: pQty,
        image: pImg,
        size: pSize,
        color: pColor,
      });
    }

    recalculate();
    showToast(`Added "${pName}" to your bag!`, 'success');
    syncToBackend('add', { productId: pId, name: pName, price: pPrice, quantity: pQty, image: pImg, size: pSize, color: pColor });
  }

  function removeItem(productId) {
    const id = String(productId).trim();
    const item = state.items.find((it) => it.productId === id);
    const itemName = item ? item.name : 'Item';

    state.items = state.items.filter((it) => it.productId !== id);
    recalculate();
    showToast(`Removed "${itemName}" from bag`, 'info', '');
    syncToBackend('delete', { productId: id });
  }

  function updateQuantity(productId, quantity) {
    const id = String(productId).trim();
    const qty = parseInt(quantity, 10);

    if (qty <= 0) {
      removeItem(id);
      return;
    }

    const item = state.items.find((it) => it.productId === id);
    if (item) {
      item.quantity = qty;
      recalculate();
      syncToBackend('put', { productId: id, quantity: qty });
    }
  }

  function clearCart() {
    state.items = [];
    state.coupon = null;
    recalculate();
    showToast('Shopping bag cleared', 'info', '');
    syncToBackend('clear');
  }

  async function applyCoupon(code) {
    const cleanCode = String(code || '').trim().toUpperCase();
    if (!cleanCode) return { success: false, message: 'Please enter a coupon code.' };

    try {
      const res = await fetch(`${API_BASE}/coupons.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: cleanCode, subtotal: state.subtotal })
      });
      const data = await res.json();
      if (data.success && data.coupon) {
        state.coupon = data.coupon;
        recalculate();
        showToast(`Promo code ${cleanCode} applied!`, 'success', '');
        return { success: true, message: data.message, coupon: data.coupon };
      } else {
        return { success: false, message: data.message || 'Invalid coupon code.' };
      }
    } catch (e) {
      return { success: false, message: 'Unable to validate coupon.' };
    }
  }

  // Parse product from any DOM container
  function parseProductElement(element) {
    const id = element.dataset.id || element.dataset.productId ||
               element.querySelector('[data-id]')?.dataset.id ||
               (element.querySelector('h2, h3')?.textContent?.trim().toLowerCase().replace(/[^a-z0-9]+/g, '-') + '-' + Date.now());
    const name = element.querySelector('.product-title, .title, h2, h3')?.textContent?.trim() || 'Fashion Item';
    const priceText = element.querySelector('.product-price, .price, p')?.textContent?.trim() || '0';
    const price = parseFloat(priceText.replace(/[^0-9,.-]/g, '').replace(/,/g, '')) || 0;
    const image = element.querySelector('img')?.src || '';
    return { productId: id, name, price, image };
  }

  // Auto-hook click events on Add to Cart buttons & Cart Icons
  function initGlobalHandlers() {
    injectDrawerDOM();
    recalculate();
    syncFromBackend();

    // Universal delegation for Add to Cart buttons
    document.addEventListener('click', (e) => {
      // Cart open triggers
      const cartToggle = e.target.closest('[data-cart-toggle], .cart-icon, .navbar-icons a[aria-label="Shopping cart"], a[href*="cart.html"], .open-cart-btn');
      if (cartToggle && !cartToggle.getAttribute('href')?.includes('cart.html#table')) {
        // If clicking normal cart link on any page, open the drawer for instant smooth interaction
        if (!window.location.pathname.endsWith('cart.html')) {
          e.preventDefault();
          openDrawer();
          return;
        }
      }

      // Add to Cart buttons
      const addBtn = e.target.closest('.add-to-cart-btn, .add-to-cart, .quick-cart-btn, button[src*="cart.php"], [data-action="add-to-cart"]');
      if (addBtn) {
        if (addBtn.dataset.alishaCartHandled === 'true') return;
        addBtn.dataset.alishaCartHandled = 'true';
        setTimeout(() => {
          delete addBtn.dataset.alishaCartHandled;
        }, 250);

        e.preventDefault();
        const card = addBtn.closest('.product, .product-card, .look-card, article');
        if (card) {
          const product = parseProductElement(card);
          if (product.price > 0) {
            addItem(product);
          }
        }
      }
    });
  }

  // DOM ready initialization
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initGlobalHandlers);
  } else {
    initGlobalHandlers();
  }

  // Expose API
  global.AlishaCart = {
    __initialized: true,
    state,
    addItem,
    add: addItem,
    removeItem,
    remove: removeItem,
    updateQuantity,
    update: updateQuantity,
    clearCart,
    clear: clearCart,
    openDrawer,
    closeDrawer,
    toggleDrawer,
    showToast,
    applyCoupon,
    formatPrice,
    recalculate,
    syncFromBackend
  };
})(window);
