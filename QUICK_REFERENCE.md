# The Alisha - Quick Reference Guide

## 🚀 Quick Start

### URLs
```
Homepage:      http://localhost/The-Alisha/
Cart:          http://localhost/The-Alisha/cart.html
My Orders:     http://localhost/The-Alisha/my-orders.html
Admin:         http://localhost/The-Alisha/admin/index.php
```

### Default Credentials

**Admin Account:**
```
Email:    admin@alisha.com
Password: admin123
```

**Test User:**
```
Email:    punalisha100@gmail.com
Password: password123
```

---

## 📁 Key Files & Locations

### Frontend
| Page | Path | API Calls |
|------|------|-----------|
| Homepage | `Client-side/Home/The.alisha/index.html` | `/products.php` |
| Cart | `cart.html` | `/cart.php`, `/orders.php`, `/coupons.php` |
| Login | `Client-side/Home/Login/login.html` | `/auth/login.php` |
| Register | `Client-side/Home/Register/register.html` | `/register.php` |

### Backend APIs
| Purpose | File | Methods |
|---------|------|---------|
| Products | `server-side/backend/products.php` | GET, POST |
| Cart | `server-side/backend/cart.php` | GET, POST, PUT/PATCH |
| Orders | `server-side/backend/orders.php` | GET, POST |
| Coupons | `server-side/backend/coupons.php` | GET, POST |
| Auth | `server-side/backend/auth/login.php` | POST |
| Register | `server-side/backend/register.php` | POST |

### Admin Pages
| Page | File | Purpose |
|------|------|---------|
| Dashboard | `admin/index.php` | Analytics & metrics |
| Customers | `admin/customers.php` | Customer list |
| Orders | `admin/orders.php` | Order management |
| Order Detail | `admin/order_view.php` | Order details & status |
| Reports | `admin/reports.php` | Sales reports |

### Data Storage
| File | Location | Purpose |
|------|----------|---------|
| Users | `server-side/backend/data/users.json` | User accounts |
| Products | `server-side/backend/data/products.json` | Product catalog |
| Carts | `server-side/backend/data/carts.json` | Shopping carts |
| Orders | `server-side/backend/data/orders.json` | Order records |
| Coupons | `server-side/backend/data/coupons.json` | Discount codes |
| SQLite DB | `server-side/backend/data/the_alisha.sqlite` | Database cache |

---

## 🔌 API Quick Reference

### Products
```bash
# Get all products
GET /The-Alisha/server-side/backend/products.php

# Get single product
GET /The-Alisha/server-side/backend/products.php?id=prod-pink-dress

# Filter by category
GET /The-Alisha/server-side/backend/products.php?category=fashion

# Search
GET /The-Alisha/server-side/backend/products.php?search=dress

# Sort
GET /The-Alisha/server-side/backend/products.php?sort=price_asc

# Price range
GET /The-Alisha/server-side/backend/products.php?min_price=1000&max_price=5000
```

### Cart
```bash
# Get cart
GET /The-Alisha/server-side/backend/cart.php

# Add item
POST /The-Alisha/server-side/backend/cart.php
Body: {
  "action": "add",
  "productId": "prod-id",
  "name": "Product Name",
  "price": 1000,
  "quantity": 1,
  "image": "url"
}

# Remove item
POST /The-Alisha/server-side/backend/cart.php
Body: {"action": "delete", "productId": "prod-id"}

# Clear cart
POST /The-Alisha/server-side/backend/cart.php
Body: {"action": "clear"}
```

### Orders
```bash
# Create order
POST /The-Alisha/server-side/backend/orders.php
Body: {
  "fullName": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "address": "123 Main St",
  "city": "Kathmandu",
  "paymentMethod": "Cash on Delivery",
  "items": [],
  "couponCode": "ALISHA10"
}

# Get user orders
GET /The-Alisha/server-side/backend/orders.php

# Get all orders (admin)
GET /The-Alisha/server-side/backend/orders.php?all=1
```

### Authentication
```bash
# Login
POST /The-Alisha/server-side/backend/auth/login.php
Body: {"email": "user@example.com", "password": "password"}

# Check session
GET /The-Alisha/server-side/backend/auth/session.php

# Register
POST /The-Alisha/server-side/backend/register.php
Body: {
  "fullName": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "password": "password123",
  "confirmPassword": "password123",
  "terms": true
}
```

### Coupons
```bash
# Validate coupon
POST /The-Alisha/server-side/backend/coupons.php
Body: {"code": "ALISHA10", "subtotal": 1000}

# List coupons
GET /The-Alisha/server-side/backend/coupons.php
```

---

## 💾 Coupon Codes (Live)

| Code | Type | Value | Min Spend | Description |
|------|------|-------|-----------|-------------|
| `ALISHA10` | Percent | 10% | Rs. 500 | 10% off entire order |
| `SUMMER50` | Flat | Rs. 200 | Rs. 1000 | Rs. 200 flat discount |
| `FREESHIP` | Free Shipping | - | None | Free shipping |

---

## 🗄️ Database Structure

### Users Table
```sql
id (TEXT) - Primary Key
full_name (TEXT)
email (TEXT) - Unique
phone (TEXT)
role (TEXT) - 'user' or 'admin'
is_active (INTEGER) - 1 or 0
password_hash (TEXT) - bcrypt
created_at (TEXT) - ISO 8601 timestamp
```

### Products Table
```sql
id (TEXT) - Primary Key
name (TEXT)
price (REAL)
old_price (REAL)
category (TEXT)
category_name (TEXT)
image (TEXT) - URL
description (TEXT)
stock (INTEGER) - NOT DECREMENTED
rating (REAL)
reviews_count (INTEGER)
badge (TEXT) - 'New', 'Best Seller', etc.
created_at (TEXT)
```

### Orders Table
```sql
id (INTEGER) - Primary Key, Auto-increment
order_number (TEXT) - Unique, format AL-YYYY-XXXX
user_id (TEXT) - Foreign Key to users
customer_name (TEXT)
customer_email (TEXT)
customer_phone (TEXT)
shipping_address (TEXT)
city (TEXT)
payment_method (TEXT)
payment_status (TEXT) - 'Pending', 'Paid', 'Unpaid (COD)'
status (TEXT) - 'Pending', 'Processing', 'Shipped', 'Delivered'
subtotal (REAL)
discount (REAL)
shipping (REAL)
total_amount (REAL)
notes (TEXT)
created_at (TEXT)
```

---

## 🔒 Security Notes

### Current Vulnerabilities
- ❌ **Stock Protection:** Items can be over-sold
- ❌ **CSRF Protection:** No tokens on forms
- ❌ **Rate Limiting:** Can brute force login
- ❌ **HTTPS:** No encryption (development only)
- ❌ **Input Sanitization:** Incomplete validation

### Security Best Practices Implemented
- ✅ Passwords hashed with bcrypt
- ✅ Sessions use HTTPOnly cookies
- ✅ HTML escaping on output (htmlspecialchars)
- ✅ Type checking with prepared statements (PDO)
- ✅ Session regeneration on login

---

## 📊 Admin Dashboard Metrics

Dashboard shows:
- Total registered customers
- Total products in catalog
- Total orders placed
- Lifetime sales revenue
- Pending orders count
- Low stock products (< 5 items)
- Monthly sales (last 6 months)
- Recent orders (6 latest)
- Recent customers (6 latest)
- Low stock products (6 critical)

---

## 🐛 Common Issues & Fixes

### Issue: Cart not updating
**Cause:** Browser cache not clearing  
**Fix:** Press Ctrl+Shift+Delete and clear cache

### Issue: Admin login fails
**Cause:** Session not started  
**Fix:** Clear cookies, try incognito mode

### Issue: Order not appearing
**Cause:** JSON file sync delay  
**Fix:** Refresh page, check browser console for errors

### Issue: Product search returns nothing
**Cause:** Exact case matching  
**Fix:** Search is case-insensitive, check spelling

---

## 🚀 Development Tips

### Adding a New Product
```php
// Manually add to products.json
{
  "id": "prod-new-item",
  "name": "New Product",
  "price": 2000,
  "oldPrice": 2500,
  "category": "fashion",
  "categoryName": "Fashion",
  "image": "https://...",
  "description": "Description",
  "stock": 10,
  "rating": 5.0,
  "reviewsCount": 1,
  "badge": "New"
}
```

### Adding a New Coupon
```php
// Add to coupons.json
"NEWCODE": {
  "type": "percent",  // or "flat" or "free_shipping"
  "value": 15,        // percentage or flat amount
  "minSpend": 500,
  "description": "Description"
}
```

### Checking Session User
```javascript
fetch('/The-Alisha/server-side/backend/auth/session.php')
  .then(r => r.json())
  .then(data => console.log(data.user))
```

### Common Query Patterns
```javascript
// Get products
fetch('/The-Alisha/server-side/backend/products.php')

// Get user cart
fetch('/The-Alisha/server-side/backend/cart.php')

// Add to cart
fetch('/The-Alisha/server-side/backend/cart.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({action: 'add', ...item})
})
```

---

## 📋 Production Checklist

Before launching to production, complete:

- [ ] Change default admin password
- [ ] Enable HTTPS/SSL certificates
- [ ] Set file permissions (chmod 600 on JSON files)
- [ ] Remove debug output (disable JSON_PRETTY_PRINT)
- [ ] Setup error logging
- [ ] Configure automated backups
- [ ] Implement rate limiting
- [ ] Add CSRF tokens to forms
- [ ] Validate stock availability
- [ ] Test payment flow end-to-end
- [ ] Setup email notifications
- [ ] Create database indexes
- [ ] Configure CDN for images
- [ ] Setup monitoring/alerting
- [ ] Create disaster recovery plan

---

## 📞 Support Files

| File | Purpose |
|------|---------|
| `README.md` | Project overview & features |
| `TECHNICAL_AUDIT.md` | Detailed technical analysis |
| `AUDIT_REPORT.json` | Structured audit data |
| `QUICK_REFERENCE.md` | This file |

---

**Last Updated:** 2026-09-01  
**Status:** Development/Testing  
**Version:** 1.0 (MVP)
