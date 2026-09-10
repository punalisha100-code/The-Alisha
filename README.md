# 🛍️ The Alisha — Full-Stack E-Commerce Website

A modern, full-stack online shopping platform crafted with **HTML5, CSS3, Vanilla JavaScript, and PHP**. It provides a real-world shopping experience with guest & authenticated carts, quantity controls, coupon validation, dynamic shipping calculators, multi-step checkout, printable order confirmations, order tracking, and an administrative portal.

---

## ✨ Key Features

### 🛒 Complete Shopping Cart System (Add & Delete)
- **Slide-out Cart Drawer**: Accessible from every page via the header shopping bag icon.
- **Add to Cart**: One-click add from Homepage, Fashion, Beauty Lookbook, Accessories, and New Arrivals.
- **Delete from Cart**: Instant item removal (`🗑️ Delete`) with real-time total updates.
- **Quantity Adjustments**: Increase (`+`) or decrease (`-`) item count, with auto-removal when quantity hits `0`.
- **Clear Entire Bag**: One-click bag clearing.
- **Free Shipping Progress Meter**: Dynamic threshold bar showing amount remaining to unlock free delivery.
- **Persistent Storage**: Works instantly for guests (`alisha_cart_id` cookie/session) and automatically merges items into the user's account upon signing in.

### 💳 Checkout & Order Processing
- **Dedicated Bag & Checkout Page** (`cart.html`):
  - Review items, thumbnails, unit prices, and line subtotals.
  - Apply promo codes:
    - `ALISHA10`: 10% off entire order
    - `SUMMER50`: Rs. 200 flat discount
    - `FREESHIP`: Free express delivery
  - Delivery address form with client-side & server-side validation.
  - Payment method choices: Cash on Delivery (COD), eSewa / Digital Wallets, Debit / Credit Cards.
- **Instant Order Confirmation Modal**: Generates official order reference number (`AL-2026-XXXX`), itemized receipt, and tracking links.

### 📦 Customer Portal & Catalog
- **My Orders Page** (`my-orders.html`): View past orders, delivery addresses, fulfillment statuses, and totals.
- **Authentication**: Customer registration with live input validators and strength meters, secure session login, and sign out dropdown.

### ⚙️ Admin Portal
- **Dashboard** (`admin/index.php`): Key performance metrics (Total Customers, Total Products, Total Orders, Lifetime Revenue, Pending Orders, Low Stock Alerts).
- **Orders Management** (`admin/orders.php` & `admin/order_view.php`): Inspect detailed invoices and update fulfillment (`Pending`, `Processing`, `Shipped`, `Delivered`, `Cancelled`) and payment status.
- **Customer Directory** (`admin/customers.php`): Paginated customer database.
- **Sales Analytics** (`admin/reports.php`): Monthly revenue breakdowns.

---

## 🚀 Getting Started

### 1. Launch with XAMPP
Ensure the project is in your XAMPP web root directory:
```bash
/opt/lampp/htdocs/The-Alisha
```
Start Apache via your terminal or XAMPP Control Panel:
```bash
sudo /opt/lampp/lampp startapache
```

### 2. Access the Application
- **Storefront / Homepage**: [http://localhost/The-Alisha/](http://localhost/The-Alisha/)
- **Shopping Bag & Checkout**: [http://localhost/The-Alisha/cart.html](http://localhost/The-Alisha/cart.html)
- **Customer My Orders**: [http://localhost/The-Alisha/my-orders.html](http://localhost/The-Alisha/my-orders.html)
- **Customer Login**: [http://localhost/The-Alisha/Client-side/Home/Login/login.html](http://localhost/The-Alisha/Client-side/Home/Login/login.html)
- **Admin Control Panel**: [http://localhost/The-Alisha/admin/index.php](http://localhost/The-Alisha/admin/index.php)

---

## 🔑 Demo & Test Accounts

| Role | Email | Password |
|---|---|---|
| **Administrator** | `admin@the-alisha.com` | `Admin@123` |
| **Customer** | `alisha@thealisha.com` | `Password123` |

### 🎟️ Promo Codes to Test
- `ALISHA10` — 10% discount on order subtotal (min spend Rs. 500)
- `SUMMER50` — Rs. 200 flat savings (min spend Rs. 1,000)
- `FREESHIP` — Free shipping discount

---

## 📁 Architecture Overview

```text
The-Alisha/
├── index.php                             # Root router / redirect to storefront
├── cart.html                             # Full checkout & cart management page
├── my-orders.html                        # Customer order tracking page
├── public/
│   ├── components/
│   │   ├── cart/
│   │   │   ├── cart.css                  # Cart drawer, badges & toast styles
│   │   │   └── cart-manager.js           # AlishaCart singleton (CRUD, sync, events)
│   │   └── navbar/
│   │       ├── navbar.html               # Universal navigation bar
│   │       ├── navbar.css                # Navbar styling & mobile drawer
│   │       └── navbar-loader.js          # Component loader with session awareness
│   └── logo/image.png                    # Brand logo
├── Client-side/Home/
│   ├── The.alisha/                       # Homepage
│   ├── fashion/                          # Fashion collection
│   ├── beauty/                           # Beauty lookbook & comments
│   ├── accessories/                      # Accessories showcase
│   ├── newArrival/                       # New arrivals
│   ├── Login/                            # Customer login
│   └── Register/                         # Customer registration
├── admin/                                # Administrator control panel
│   ├── index.php                         # Admin analytics dashboard
│   ├── orders.php                        # Orders management table
│   ├── order_view.php                    # Detailed order & status editor
│   ├── customers.php                     # Customer management
│   ├── reports.php                       # Monthly sales reports
│   └── login.php / logout.php            # Admin auth
├── includes/
│   ├── auth.php                          # Session & role verification
│   └── functions.php                     # SQLite PDO database bridge & helpers
└── server-side/backend/
    ├── config.php                        # Core database & storage config
    ├── cart.php                          # RESTful Cart API (GET, POST, PUT, DELETE)
    ├── products.php                      # Products API
    ├── coupons.php                       # Coupon validation API
    ├── orders.php                        # Order placement & history API
    ├── auth/login.php & session.php      # User authentication endpoints
    └── data/                             # JSON storage & SQLite database
        ├── users.json
        ├── products.json
        ├── carts.json
        ├── orders.json
        ├── coupons.json
        └── the_alisha.sqlite
```
