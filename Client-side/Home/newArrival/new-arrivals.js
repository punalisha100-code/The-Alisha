const productsData = [
  { id: "prod-floral-maxi", title: "Emerald Floral Maxi Dress", price: 2500, badge: "New", image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQePizqT3-jra4cnzS0WnnJZzbfecXn_5Z5mW0Y0wDB5g&s=10" },
  { id: "prod-rose-flutter", title: "Rose Flutter Midi Dress", price: 2400, badge: "Trending", image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSQzJal3ISYePvUWMuXNhZ5-HVd1MI9WdFPBnB9HMnXCh91PRdJ2Q9ghQ0&s=10" },
  { id: "prod-bohemian-tiered", title: "Bohemian Tiered Sundress", price: 1700, badge: "New", image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQvm-1mpeBBTR2jZ2TItXQRhYkPcqg49jRtvV8w29H1IvOQviRPEcj6UjE&s=10" },
  { id: "prod-satin-slip", title: "Burgundy Satin Slip Gown", price: 2800, badge: "Hot", image: "https://assets.myntassets.com/assets/images/2026/JUNE/29/gZ5nVj1X_33f40317bd864bca88a3e97dd520f8f5.jpg" },
  { id: "prod-summer-pastel", title: "Pastel Blossom Onepiece", price: 2000, badge: "New", image: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQf5ZFcKB3NWWhxIzpX8g-7C7XF9BCIXM7ZlBwcnYKCVA&s=10" }
];

class ProductCatalog {
  constructor(containerId, products) {
    this.container = document.getElementById(containerId);
    this.products = products || [];
  }

  init() {
    this.render();
    this.attachHandlers();
  }

  render() {
    if (!this.container) return;
    this.container.innerHTML = this.products
      .map(p => this.createCardHTML(p))
      .join('');
  }

  createCardHTML(product) {
    const formattedPrice = window.AlishaCart ? window.AlishaCart.formatPrice(product.price) : `Rs. ${product.price}`;
    return `
      <article class="product-card" data-id="${this.escapeHTML(product.id)}">
        <div class="media-wrapper">
          <span class="badge">${this.escapeHTML(product.badge)}</span>
          <img src="${this.escapeHTML(product.image)}" alt="${this.escapeHTML(product.title)}" loading="lazy">
        </div>

        <div class="card-body">
          <div class="product-meta">
            <h2 class="product-title">${this.escapeHTML(product.title)}</h2>
            <span class="product-price">${formattedPrice}</span>
          </div>
          <div style="margin-top: auto; display:flex; gap:.5rem;">
            <button class="btn btn-primary add-to-cart add-to-cart-btn" data-id="${this.escapeHTML(product.id)}" type="button">Add to Bag</button>
          </div>
        </div>
      </article>
    `;
  }

  attachHandlers() {
    if (!this.container) return;

    this.container.addEventListener('click', (e) => {
      const addBtn = e.target.closest('.add-to-cart');
      if (addBtn) {
        e.stopPropagation();
        const id = addBtn.dataset.id;
        const product = this.products.find(p => p.id === id);
        if (product && window.AlishaCart) {
          window.AlishaCart.add({
            productId: product.id,
            name: product.title,
            price: product.price,
            image: product.image,
            size: 'M'
          });
        }
      }
    });
  }

  escapeHTML(str) {
    return String(str || '').replace(/[&<>'"]/g, tag => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
    }[tag] || tag));
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const catalog = new ProductCatalog('productGrid', productsData);
  catalog.init();
});