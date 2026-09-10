// Accessories Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
  const productsGrid = document.getElementById('productsGrid');
  
  // Sample accessories products data
  // In production, this should be fetched from your backend API
  const accessories = [
    {
      id: 1,
      name: 'Gold Hoop Earrings',
      price: 'Rs200',
      description: 'Classic gold hoops with premium finish',
      image: 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=700&q=85',
      badge: 'New',
      category: 'Earrings'
    },
    {
      id: 2,
      name: 'Leather Crossbody Bag',
      price: 'Rs200',
      description: 'Stylish leather bag for everyday use',
      image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=700&q=85',
      badge: 'New',
      category: 'Bags'
    },
    {
      id: 3,
      name: 'Pearl Pendant Necklace',
      price: 'Rs2009',
      description: 'Elegant pearl pendant on chain',
      image: 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=700&q=85',
      badge: '',
      category: 'Necklaces'
    },
    {
      id: 4,
      name: 'Silk Headscarf',
      price: 'Rs200',
      description: 'Luxurious silk headscarf with patterns',
      image: 'https://images.unsplash.com/photo-1601924994987-69e26d50dc26?auto=format&fit=crop&w=700&q=85',
      badge: 'Sale',
      category: 'Scarves'
    },
    {
      id: 5,
      name: 'Diamond Stud Earrings',
      price: 'Rs200',
      description: 'Sparkling diamond studs',
      image: 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?auto=format&fit=crop&w=700&q=85',
      badge: 'New',
      category: 'Earrings'
    },
    {
      id: 6,
      name: 'Chain Bracelet',
      price: 'Rs200',
      description: 'Delicate chain bracelet in gold',
      image: 'https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=700&q=85',
      badge: '',
      category: 'Bracelets'
    },
    {
      id: 7,
      name: 'Designer Sunglasses',
      price: 'Rs200',
      description: 'Trendy designer sunglasses',
      image: 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=700&q=85',
      badge: 'Trending',
      category: 'Eyewear'
    },
    {
      id: 8,
      name: 'Beaded Anklet',
      price: 'Rs3000',
      description: 'Colorful beaded ankle bracelet',
      image: 'https://images.unsplash.com/photo-1601121141461-9d6647bca1ed?auto=format&fit=crop&w=700&q=85',
      badge: '',
      category: 'Anklets'
    },
    {
      id: 9,
      name: 'Leather Backpack',
      price: 'Rs2000',
      description: 'Premium leather backpack for travel',
      image: 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=700&q=85',
      badge: 'New',
      category: 'Bags'
    },
    {
      id: 10,
      name: 'Gemstone Ring',
      price: 'Rs200',
      description: 'Beautiful gemstone cocktail ring',
      image: 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?auto=format&fit=crop&w=700&q=85',
      badge: '',
      category: 'Rings'
    },
    {
      id: 11,
      name: 'Wool Scarf',
      price: 'Rs200',
      description: 'Warm and cozy wool scarf',
      image: 'https://images.unsplash.com/photo-1520903920243-00d872a2d1c9?auto=format&fit=crop&w=700&q=85',
      badge: '',
      category: 'Scarves'
    },
    {
      id: 12,
      name: 'Watch - Rose Gold',
      price: 'Rs2000',
      description: 'Elegant rose gold watch',
      image: 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&w=700&q=85',
      badge: 'New',
      category: 'Watches'
    }
  ];

  // Render products
  function renderProducts(products) {
    productsGrid.innerHTML = '';

    products.forEach(product => {
      const productCard = document.createElement('div');
      productCard.className = 'product-card';
      productCard.innerHTML = `
        <div class="image-wrapper">
          <img src="${product.image}" alt="${product.name}" class="product-image" />
          ${product.badge ? `<span class="badge-new">${product.badge}</span>` : ''}
          <div class="image-overlay">
            <button class="quick-cart-btn" data-product-id="${product.id}">Quick View</button>
          </div>
        </div>
        <div class="product-info">
          <div class="product-header">
            <h3 class="product-title">${product.name}</h3>
            <span class="product-price">${product.price}</span>
          </div>
          <p class="product-desc">${product.description}</p>
          <button class="add-to-cart-btn" data-product-id="${product.id}">
            Add to Cart
          </button>
        </div>
      `;

      productsGrid.appendChild(productCard);
    });

    // Add event listeners to buttons
    attachEventListeners();
  }

  // Attach event listeners to product buttons
  function attachEventListeners() {
    const addToCartBtns = document.querySelectorAll('.add-to-cart-btn');
    const quickViewBtns = document.querySelectorAll('.quick-cart-btn');

    addToCartBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        if (this.dataset.alishaCartHandled === 'true') return;
        this.dataset.alishaCartHandled = 'true';
        setTimeout(() => { delete this.dataset.alishaCartHandled; }, 250);

        const productId = this.dataset.productId;
        const product = accessories.find(p => p.id == productId);

        if (!product) return;

        if (window.AlishaCart && typeof window.AlishaCart.add === 'function') {
          window.AlishaCart.add({
            productId: String(product.id),
            name: product.name,
            price: Number(String(product.price).replace(/[^\d.]/g, '')) || 0,
            image: product.image,
            size: 'Standard'
          });
        }

        const originalText = this.textContent;
        this.textContent = '✓ Added!';
        this.style.background = 'linear-gradient(90deg, #4caf50, #45a049)';

        setTimeout(() => {
          this.textContent = originalText;
          this.style.background = '';
        }, 2000);
      });
    });

    quickViewBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        const productId = this.dataset.productId;
        const product = accessories.find(p => p.id == productId);
        console.log('Quick view:', product);
        // Implement quick view modal here
      });
    });
  }

  // Load products from backend (optional)
  function loadProductsFromBackend() {
    // Uncomment to fetch from backend
    /*
    fetch('/server-side/backend/products.php?category=accessories')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          renderProducts(data.products);
        }
      })
      .catch(error => console.error('Error loading products:', error));
    */
    
    // For now, render sample products
    renderProducts(accessories);
  }

  // Initialize
  loadProductsFromBackend();
});
