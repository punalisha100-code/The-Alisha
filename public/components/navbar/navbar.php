<?php
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$activePage = basename($_SERVER['REQUEST_URI']);
$menuItems = [
    ['label' => 'Home', 'href' => '/The-Alisha/Client-side/Home/The.alisha/index.html', 'slug' => 'index.html'],
    ['label' => 'New Arrivals', 'href' => '/The-Alisha/Client-side/Home/newArrival/new-Arrivals.html', 'slug' => 'new-Arrivals.html'],
    ['label' => 'Fashion', 'href' => '/The-Alisha/Client-side/Home/fashion/Fashion.html', 'slug' => 'Fashion.html'],
    ['label' => 'Beauty', 'href' => '/The-Alisha/Client-side/Home/beauty/Beauty.html', 'slug' => 'Beauty.html'],
    ['label' => 'Accessories', 'href' => '/The-Alisha/Client-side/Home/accessories/Accessories.html', 'slug' => 'Accessories.html'],
];
$currentPath = $_SERVER['REQUEST_URI'];
?>
<header class="alisha-navbar" aria-label="Main navigation">
  <div class="navbar-inner container">
    <div class="navbar-brand">
      <a class="brand-link" href="/The-Alisha/Client-side/Home/The.alisha/index.html">
        <img src="/The-Alisha/public/logo/image.png" alt="The Alisha" class="brand-logo" width="42" height="42" />
        <span class="brand-text">The Alisha</span>
      </a>
    </div>

    <nav class="navbar-links" aria-label="Primary">
      <ul>
        <?php foreach ($menuItems as $item): ?>
          <li>
            <a href="<?= $item['href'] ?>" class="nav-link<?= strpos($currentPath, $item['slug']) !== false ? ' active' : '' ?>">
              <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="navbar-actions">
      <form class="navbar-search" action="/The-Alisha/Client-side/Home/The.alisha/index.html" method="get" role="search">
        <label class="visually-hidden" for="navbar-search-input">Search products</label>
        <input id="navbar-search-input" name="search" type="search" placeholder="Search products..." autocomplete="off" />
        <button type="submit" class="search-button" aria-label="Search products">🔍</button>
      </form>

      <div class="navbar-icons">
        <a href="/The-Alisha/Client-side/Home/Login/login.html" class="icon-button" aria-label="Wishlist">
          <span aria-hidden="true">♡</span>
          <span class="badge wishlist-badge" aria-label="Wishlist count">0</span>
        </a>
        <a href="/The-Alisha/cart.html" class="icon-button" aria-label="Shopping cart">
          <span aria-hidden="true">🛒</span>
          <span class="badge cart-badge" aria-label="Cart items count">0</span>
        </a>
        <a href="/The-Alisha/Client-side/Home/Login/login.html" class="account-link">Login</a>
      </div>
    </div>

    <button class="navbar-menu-toggle" type="button" aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="navbar-mobile-menu">
      <span class="menu-icon">☰</span>
    </button>
  </div>

  <div id="navbar-mobile-menu" class="navbar-mobile-menu" hidden>
    <ul>
      <?php foreach ($menuItems as $item): ?>
        <li>
          <a href="<?= $item['href'] ?>" class="mobile-nav-link<?= strpos($currentPath, $item['slug']) !== false ? ' active' : '' ?>">
            <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
          </a>
        </li>
      <?php endforeach; ?>
      <li><a href="/The-Alisha/Client-side/Home/Login/login.html">Wishlist</a></li>
      <li><a href="/The-Alisha/cart.html">Cart</a></li>
      <li><a href="/The-Alisha/Client-side/Home/Login/login.html">Login</a></li>
    </ul>
  </div>
</header>
