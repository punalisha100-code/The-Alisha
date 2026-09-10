"use strict";

const productsData = [
  {
    id: "prod-linen-blazer",
    title: "Tailored Linen Blazer",
    price: 3500,
    description: "Relaxed silhouette crafted from breathable organic linen with sleek notched lapels.",
    imageUrl: "https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=800&q=80",
  },
  {
    id: "prod-knit-sweater",
    title: "Oversized Knit Sweater",
    price: 2999,
    description: "Heavyweight merino wool blend featuring ribbed trim and cozy dropped shoulders.",
    imageUrl: "https://images.unsplash.com/photo-1576995853123-5a10305d93c0?auto=format&fit=crop&w=800&q=80",
  },
  {
    id: "prod-pleated-trousers",
    title: "Pleated Wide Trousers",
    price: 3500,
    description: "High-waisted fit with deep pleats, wide leg profile, and premium fluid drape.",
    imageUrl: "https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=800&q=80",
  },
  {
    id: "prod-kurthi",
    title: "Embroidered Kurthi",
    price: 2000,
    description: "Hand-crafted traditional embroidery kurthi with contemporary cut and comfortable pure cotton.",
    imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSi-AzqnZErD3BL4UEBWRuKh5VIglaKnmptb_euF0wf0g&s",
  },
  {
    id: "prod-pants",
    title: "Tailored Slim Pants",
    price: 1300,
    description: "Stretch-comfort tailored pants featuring mid-rise waistband and clean modern taper.",
    imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRm1cXLAx0b2tJxWlcIRkU8IFVoyPdiEdOUDhHwxzJW-J3IDi0-5n02oZxk&s=10",
  },
  {
    id: "prod-tshirt",
    title: "Casual Cotton T-shirt",
    price: 700,
    description: "Super-soft combed cotton tee with ribbed crew neck and relaxed effortless fit.",
    imageUrl: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSLh9z9iQyStGqWEBw6_tpNNpzDUL45V2txdAuZOKwMiMALVqCAvzWlmuHh&s=10",
  }
];

function formatPrice(price) {
  return `Rs. ${price.toLocaleString("en-IN")}`;
}

function escapeHTML(str) {
  return String(str || "").replace(/[&<>'"]/g, (tag) => {
    const replacements = { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' };
    return replacements[tag] || tag;
  });
}

function renderProducts() {
  const grid = document.getElementById("productGrid");
  if (!grid) return;

  grid.innerHTML = productsData
    .map((product) => {
      return `
      <article class="product-card" data-id="${product.id}">
        <div class="image-wrapper">
          <span class="badge-new">New</span>
          <img
            src="${product.imageUrl}"
            alt="${escapeHTML(product.title)}"
            class="product-image"
            loading="lazy"
          />
          <div class="image-overlay">
            <button
              class="quick-cart-btn"
              type="button"
            >+ Add to Cart </button>
          </div>
        </div>
        <div class="product-info">
          <div class="product-header">
            <h2 class="product-title">${escapeHTML(product.title)}</h2>
            <span class="product-price">${formatPrice(product.price)}</span>
          </div>
          <p class="product-desc">${escapeHTML(product.description)}</p>
          <button
            class="add-to-cart-btn"
            type="button"
          >
            <span>Add to Cart</span>
            <span class="cart-arrow">→</span>
          </button>
        </div>
      </article>
    `;
    })
    .join("");
}

function addToCart(productId) {
  const product = productsData.find((item) => item.id === productId);
  if (!product) return;

  if (window.AlishaCart) {
    window.AlishaCart.add({
      productId: product.id,
      name: product.title,
      price: product.price,
      image: product.imageUrl,
      size: 'M'
    });
  }
}

document.addEventListener("DOMContentLoaded", () => {
  renderProducts();
});

