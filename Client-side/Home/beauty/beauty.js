"use strict";

const beautyProducts = [
  {
    id: "prod-dewy-serum",
    category: "skincare",
    title: "Dewy Glow Serum",
    image: "https://images.unsplash.com/photo-1611930022073-b7a4ba5fcccd?auto=format&fit=crop&w=900&q=85",
    tag: "Skin essential",
    price: 1200,
        description: "A featherlight serum with hyaluronic acid for a fresh, glassy finish.",
  },
  {
    id: "prod-rose-cleanser",
    category: "skincare",
    title: "Rose Cloud Cleanser",
    image: "https://images.unsplash.com/photo-1556228578-8c89e6adf883?auto=format&fit=crop&w=900&q=85",
    tag: "Daily ritual",
    price: 850,
        description: "A soft cream-to-milk cleanser that leaves skin calm, never tight.",
  },
  {
    id: "prod-face-mask",
    category: "skincare",
    title: "Rose Clay Glow Mask",
    image: "https://images.unsplash.com/photo-1570172619644-dfd03ed5d881?auto=format&fit=crop&w=900&q=85",
    tag: "Slow Sunday",
    price: 950,
        description: "Mineral-rich pink clay for a smooth, softly illuminated complexion.",
  },
  {
    id: "prod-rosy-lipstick",
    category: "makeup",
    title: "Rosy Velvet Lipstick",
    image: "https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=900&q=85",
    tag: "Soft colour",
    price: 700,
        description: "A blurred rose shade with a comfortable, velvety matte finish.",
  },
  {
    id: "prod-liquid-foundation",
    category: "makeup",
    title: "Natural Finish Foundation",
    image: "https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=85",
    tag: "Second skin",
    price: 1500,
        description: "Buildable, breathable coverage that keeps your real skin in view.",
  },
  {
    id: "prod-lash-mascara",
    category: "makeup",
    title: "Lash Volume Mascara",
    image: "https://images.unsplash.com/photo-1631214524020-7e18db9e4f9f?auto=format&fit=crop&w=900&q=85",
    tag: "Eye edit",
    price: 650,
       description: "A clean, buildable formula for lifted lashes without the weight.",
  },
  {
    id: "prod-rosy-blush",
    category: "makeup",
    title: "Soft Rosy Cheek Blush",
    image: "https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=900&q=85",
    tag: "Just flushed",
    price: 700,
       description: "A sheer cream blush that melts into cheeks with a natural flush.",
  },
  {
    id: "prod-lip-gloss",
    category: "makeup",
    title: "Glass Shine Lip Gloss",
    image: "https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=900&q=85",
    tag: "High shine",
    price: 550,
        description: "A clear, cushiony gloss with a non-sticky, mirror-like shine.",
  },
  {
    id: "prod-floral-perfume",
    category: "fragrance",
    title: "Floral Signature Eau de Parfum",
    image: "https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=85",
    tag: "New scent",
    price: 2800,
        description: "Petals, clean musk, and warm woods in a quietly magnetic blend.",
  },
  {
    id: "prod-body-oil",
    category: "fragrance",
    title: "Sunlit Neroli Body Oil",
    image: "https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=85",
    tag: "Body care",
    price: 1100,
        description: "A silky botanical oil scented with neroli, orange blossom, and cedar.",
  }
];

const state = { activeFilter: "all", search: "" };
const gridContainer = document.getElementById("productGrid");
const resultCount = document.getElementById("result-count");

function escapeHTML(value) {
  return String(value ?? "").replace(/[&<>'"]/g, (character) => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", "'": "&#39;", '"': "&quot;"
  }[character]));
}

function formatPrice(price) {
  return window.AlishaCart?.formatPrice(price) || `Rs. ${Number(price).toLocaleString("en-IN")}`;
}

function renderCard(product, index) {
  return `
    <article class="product-card" data-id="${escapeHTML(product.id)}" style="animation-delay:${index * 45}ms">
      <div class="image-wrapper">
        <img class="product-image" src="${escapeHTML(product.image)}" alt="${escapeHTML(product.title)}" loading="lazy">
        <span class="badge-new">${escapeHTML(product.tag)}</span>
      </div>
      <div class="product-info">
        <div class="product-header">
          <h2 class="product-title">${escapeHTML(product.title)}</h2>
          <span class="product-price">${formatPrice(product.price)}</span>
        </div>
        <p class="product-desc">${escapeHTML(product.description)}</p>
        <button class="add-to-cart-btn" type="button" data-action="add-to-cart" data-product-id="${escapeHTML(product.id)}">
          <span>Add to Cart</span><span class="cart-arrow">↗</span>
        </button>
      </div>
    </article>
  `;
}

function getVisibleProducts() {
  const search = state.search.toLowerCase();
  return beautyProducts.filter((product) => {
    const matchesFilter = state.activeFilter === "all" || product.category === state.activeFilter;
    const matchesSearch = !search || `${product.title} ${product.description} ${product.tag}`.toLowerCase().includes(search);
    return matchesFilter && matchesSearch;
  });
}

function renderApp() {
  const visibleProducts = getVisibleProducts();
  if (resultCount) {
    resultCount.textContent = `${visibleProducts.length} ${visibleProducts.length === 1 ? "piece" : "pieces"}`;
  }
  gridContainer.innerHTML = visibleProducts.length
    ? visibleProducts.map(renderCard).join("")
    : '<div class="empty-state">Nothing in this edit matches your search.</div>';
}

function addToCart(product) {
  if (!window.AlishaCart) return;
  window.AlishaCart.add({ productId: product.id, name: product.title, price: product.price, image: product.image, size: "Standard" });
}

gridContainer?.addEventListener("click", (event) => {
  const addButton = event.target.closest(".add-to-cart-btn");
  if (!addButton) return;
  if (addButton.dataset.alishaCartHandled === "true") return;

  addButton.dataset.alishaCartHandled = "true";
  setTimeout(() => {
    delete addButton.dataset.alishaCartHandled;
  }, 250);

  const card = addButton.closest(".product-card");
  if (!card) return;

  const productId = addButton.dataset.productId || card.dataset.id;
  const product = beautyProducts.find((item) => item.id === productId);
  if (!product || !window.AlishaCart || typeof window.AlishaCart.add !== "function") return;

  window.AlishaCart.add({
    productId: product.id,
    name: product.title,
    price: product.price,
    image: product.image,
    size: "Standard"
  });
});

document.querySelectorAll("[data-filter]").forEach((button) => {
  button.addEventListener("click", () => {
    document.querySelectorAll("[data-filter]").forEach((item) => item.classList.remove("is-active"));
    button.classList.add("is-active");
    state.activeFilter = button.dataset.filter;
    renderApp();
  });
});

document.getElementById("beauty-search")?.addEventListener("input", (event) => {
  state.search = event.target.value.trim();
  renderApp();
});

document.addEventListener("DOMContentLoaded", renderApp);
