# The-Alisha E-Commerce Platform - Complete Implementation Plan

**Status:** Phase 1 - Planning & Architecture
**Target Completion:** 6-8 weeks
**Current Score:** 5.5/10 → Target: 9/10+

---

## 📋 EXECUTIVE SUMMARY

The-Alisha is a functional MVP e-commerce platform with a solid foundation. The audit identified critical issues requiring immediate attention, followed by feature completions and quality improvements.

**Key Actions:**
1. Fix stock protection (prevent over-selling)
2. Normalize database architecture
3. Add security hardening
4. Complete incomplete features
5. Implement real payment processing
6. Add comprehensive testing

---

## 🔴 PHASE 1: CRITICAL FIXES (1-2 weeks)

### Priority 1.1: Stock Protection System
**Severity:** 🔴 CRITICAL  
**Impact:** Prevents data corruption, inventory loss

**Current Problem:**
- Products can be oversold infinitely
- Stock quantity never decreases after purchase
- Admin has no visibility into actual available inventory

**Implementation:**
```
1. Create order_items table (normalized database)
2. Deduct stock on order creation in /orders.php
3. Prevent checkout if stock insufficient
4. Add stock audit log
5. Implement stock restoration on order cancellation
6. Add low-stock alerts in admin dashboard
```

**Files to Modify:**
- `/includes/functions.php` - Add order_items table + stock logic
- `/server-side/backend/orders.php` - Add stock deduction
- `/admin/index.php` - Add low-stock warnings
- `/admin/orders.php` - Add cancellation with stock restore

**Timeline:** 2-3 days

---

### Priority 1.2: Database Normalization
**Severity:** 🔴 CRITICAL  
**Impact:** Enables scalability, fixes data integrity

**Current Problem:**
- Order items stored as JSON strings, not separate table
- Can't query orders by product
- Difficult to manage order modifications
- No referential integrity

**Implementation:**
```
CREATE TABLE order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER,
    product_id TEXT,
    product_name TEXT,
    unit_price REAL,
    quantity INTEGER,
    total_price REAL,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE order_addresses (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER,
    full_name TEXT,
    phone TEXT,
    address TEXT,
    city TEXT,
    postal_code TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE INDEX idx_order_user ON orders(user_id);
CREATE INDEX idx_order_status ON orders(status);
CREATE INDEX idx_product_stock ON products(stock);
CREATE INDEX idx_product_category ON products(category);
```

**Files to Create:**
- `/server-side/backend/migrations/001_normalize_orders.sql`

**Files to Modify:**
- `/includes/functions.php` - Add new table creation
- `/server-side/backend/orders.php` - Use order_items table

**Timeline:** 2-3 days

---

### Priority 1.3: Security Hardening
**Severity:** 🔴 CRITICAL  
**Impact:** Prevents unauthorized access, data theft

**Vulnerabilities to Fix:**
1. Add CSRF token protection
2. Add rate limiting on auth endpoints
3. Remove hardcoded test credentials
4. Add input validation/sanitization
5. Add secure password hashing verification
6. Add SQL injection prevention (use prepared statements everywhere)

**Files to Modify:**
- `/includes/functions.php` - Add CSRF generation, rate limiting
- `/server-side/backend/auth/login.php` - Add CSRF check, rate limit
- `/server-side/backend/register.php` - Add validation, rate limit
- `/server-side/backend/orders.php` - Verify user ownership
- `/admin/login.php` - Add security checks

**Timeline:** 2 days

---

## 🟠 PHASE 2: DATABASE ARCHITECTURE (1 week)

### Priority 2.1: Add Missing Tables
**Files to Create:** `/server-side/backend/migrations/`
```
002_add_reviews_table.sql
003_add_wishlist_table.sql
004_add_addresses_table.sql
005_add_audit_log_table.sql
```

**New Tables:**
```sql
-- Customer addresses (reuse across orders)
CREATE TABLE addresses (
    id INTEGER PRIMARY KEY,
    user_id TEXT,
    type TEXT (home|office|other),
    full_name TEXT,
    phone TEXT,
    address TEXT,
    city TEXT,
    postal_code TEXT,
    is_default INTEGER,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Product reviews & ratings
CREATE TABLE reviews (
    id INTEGER PRIMARY KEY,
    product_id TEXT,
    user_id TEXT,
    rating INTEGER (1-5),
    title TEXT,
    comment TEXT,
    verified_purchase INTEGER,
    status TEXT (pending|approved|rejected),
    created_at TEXT,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Wishlist
CREATE TABLE wishlist (
    id INTEGER PRIMARY KEY,
    user_id TEXT,
    product_id TEXT,
    created_at TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Coupon usage tracking
CREATE TABLE coupon_usage (
    id INTEGER PRIMARY KEY,
    coupon_id TEXT,
    order_id INTEGER,
    user_id TEXT,
    used_at TEXT,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Admin audit log
CREATE TABLE audit_log (
    id INTEGER PRIMARY KEY,
    admin_id TEXT,
    action TEXT,
    entity_type TEXT,
    entity_id TEXT,
    details TEXT,
    timestamp TEXT,
    FOREIGN KEY (admin_id) REFERENCES users(id)
);
```

**Timeline:** 2 days

---

### Priority 2.2: Add Indexes & Constraints
**Files to Modify:** `/includes/functions.php`

```sql
-- Performance indexes
CREATE INDEX IF NOT EXISTS idx_user_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_product_category ON products(category);
CREATE INDEX IF NOT EXISTS idx_product_stock ON products(stock);
CREATE INDEX IF NOT EXISTS idx_order_user ON orders(user_id);
CREATE INDEX IF NOT EXISTS idx_order_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_order_date ON orders(created_at);
CREATE INDEX IF NOT EXISTS idx_review_product ON reviews(product_id);
CREATE INDEX IF NOT EXISTS idx_review_user ON reviews(user_id);
CREATE INDEX IF NOT EXISTS idx_wishlist_user ON wishlist(user_id);
```

**Timeline:** 1 day

---

## 🟡 PHASE 3: BACKEND API COMPLETION (2 weeks)

### Priority 3.1: Enhance Products API
**File:** `/server-side/backend/products.php`

**Features to Add:**
- Get single product details (including reviews, ratings)
- Add product (admin only)
- Update product (admin only)
- Delete product (admin only)
- Upload product image (admin only)
- Batch operations
- Advanced filtering (price range, multiple categories, ratings)

**New Endpoints:**
```
GET  /products.php?id=<id>              # Get single product with reviews
POST /products.php                       # Create (admin)
PUT  /products.php                       # Update (admin)
DELETE /products.php                     # Delete (admin)
POST /products.php?action=upload         # Image upload (admin)
```

**Timeline:** 3 days

---

### Priority 3.2: Implement Wishlist API
**File:** `/server-side/backend/wishlist.php`

**Endpoints:**
```
GET  /wishlist.php                       # Get user's wishlist
POST /wishlist.php                       # Add to wishlist
DELETE /wishlist.php?id=<id>             # Remove from wishlist
POST /wishlist.php?action=move-to-cart   # Move item to cart
```

**Timeline:** 2 days

---

### Priority 3.3: Implement Reviews API
**File:** `/server-side/backend/reviews.php`

**Endpoints:**
```
GET  /reviews.php?product_id=<id>        # Get product reviews
POST /reviews.php                        # Submit review (verified purchase)
PUT  /reviews.php?id=<id>                # Update review
DELETE /reviews.php?id=<id>              # Delete review
GET  /reviews.php?user_id=<id>           # Get user's reviews (admin)
PUT  /reviews.php?id=<id>&action=approve # Approve review (admin)
```

**Logic:**
- Only customers with verified purchase can review
- Reviews pending approval by admin
- Rating update product's average rating
- Helpful/unhelpful voting

**Timeline:** 3 days

---

### Priority 3.4: Enhance Orders API
**File:** `/server-side/backend/orders.php`

**Improvements:**
- Add order tracking/timeline
- Add order cancellation with refund logic
- Add invoice PDF generation
- Add order notes (admin/customer)
- Add shipping tracking integration points
- Implement order status workflows

**Timeline:** 3 days

---

### Priority 3.5: Implement Address Management
**File:** `/server-side/backend/addresses.php`

**Endpoints:**
```
GET  /addresses.php                      # Get user's addresses
POST /addresses.php                      # Add address
PUT  /addresses.php?id=<id>              # Update address
DELETE /addresses.php?id=<id>            # Delete address
PUT  /addresses.php?id=<id>&action=set-default  # Set default
```

**Timeline:** 1 day

---

## 🟢 PHASE 4: FRONTEND IMPLEMENTATION (2 weeks)

### Priority 4.1: Product Detail Page
**File:** `/Client-side/Home/Product/product-detail.html`

**Features:**
- Product image gallery (main + thumbnails)
- Detailed description
- Specifications
- Price with discount display
- Stock status
- Size/color selection
- Quantity selector
- Add to cart button
- Add to wishlist button
- Share buttons
- Reviews section
- Related products
- Customer Q&A (optional)

**Timeline:** 3 days

---

### Priority 4.2: Complete Wishlist Feature
**Files:**
- `/public/components/wishlist/wishlist-manager.js`
- `/Client-side/Home/Wishlist/wishlist.html`

**Features:**
- View wishlist items
- Remove from wishlist
- Move to cart
- Share wishlist (optional)
- Save for later
- Notify on price drop (optional)

**Timeline:** 2 days

---

### Priority 4.3: Enhanced Customer Account
**Files:**
- `/Client-side/Home/Profile/profile.html`
- `/Client-side/Home/Addresses/addresses.html`
- `/Client-side/Home/Reviews/my-reviews.html`

**Features:**
- View/edit profile
- Manage addresses
- View/manage reviews
- Order history with details
- Download invoice
- Track order (real-time)
- Account settings
- Password change
- Notification preferences

**Timeline:** 3 days

---

### Priority 4.4: Enhanced Checkout
**File:** `/cart.html`

**Improvements:**
- Select/manage delivery addresses
- Guest address vs saved
- Order summary with tax
- Multiple payment methods
- Order confirmation modal
- Invoice download
- Email confirmation link
- SMS notification option

**Timeline:** 2 days

---

### Priority 4.5: Product Reviews Display
**Files:** Modify all product listing pages

**Features:**
- Show average rating
- Show review count
- Review snippet preview
- "Read reviews" link to detail page
- Customer-submitted photos in reviews

**Timeline:** 2 days

---

## 🔵 PHASE 5: ADMIN PANEL ENHANCEMENT (1.5 weeks)

### Priority 5.1: Product Management
**Files:**
- `/admin/products.php`
- `/admin/product-edit.php`
- `/admin/product-upload.php`

**Features:**
- Product listing with pagination
- Add/edit/delete products
- Bulk import from CSV
- Image upload (server storage)
- Stock management
- Category management
- Brand management
- Price/discount management
- Bulk price updates

**Timeline:** 3 days

---

### Priority 5.2: Inventory Management
**File:** `/admin/inventory.php`

**Features:**
- Real-time stock levels
- Low stock alerts (configurable threshold)
- Stock history log
- Bulk stock updates
- Stock transfer between categories
- Expired stock handling

**Timeline:** 2 days

---

### Priority 5.3: Review Moderation
**File:** `/admin/reviews.php`

**Features:**
- Pending reviews queue
- Approve/reject/flag reviews
- Filter by rating
- Filter by product
- Delete inappropriate reviews
- View reviewer details
- Bulk actions

**Timeline:** 2 days

---

### Priority 5.4: Advanced Reports
**File:** `/admin/reports.php` (enhance)

**Reports:**
- Revenue by category
- Product performance
- Customer segmentation
- Coupon usage analysis
- Inventory reports
- Customer acquisition cost
- Customer lifetime value
- Repeat customer rate

**Timeline:** 2 days

---

## 🟣 PHASE 6: INTEGRATIONS & EMAIL (1 week)

### Priority 6.1: Email Notifications
**File:** `/server-side/backend/email.php`

**Triggers:**
- Order confirmation
- Order status updates
- Shipping notification
- Delivery confirmation
- Review request
- Abandoned cart reminder
- Wishlist price drop

**Implementation:** PHPMailer or SendGrid API

**Timeline:** 2 days

---

### Priority 6.2: Payment Gateway Integration
**File:** `/server-side/backend/payment.php`

**Options:**
1. Stripe integration (credit/debit cards)
2. eSewa/Digital wallets (Nepal context)
3. Cash on Delivery (already working)

**Features:**
- Secure payment processing
- Payment status tracking
- Refund handling
- Invoice generation

**Timeline:** 3 days

---

### Priority 6.3: SMS Notifications (Optional)
**File:** `/server-side/backend/sms.php`

**Events:**
- Order confirmation
- Shipping updates
- Delivery confirmation

**Timeline:** 1 day

---

## 🎯 PHASE 7: QUALITY ASSURANCE (1 week)

### Priority 7.1: Testing
**Files:** `/tests/`

**Test Coverage:**
- Unit tests for API endpoints
- Integration tests for workflows
- Security tests (CSRF, SQL injection, XSS)
- Performance tests (load testing)
- E2E tests (customer flow)

**Tools:** PHPUnit, Jest (JavaScript)

**Timeline:** 3 days

---

### Priority 7.2: Performance Optimization
- Database query optimization
- Caching layer (Redis or file cache)
- Asset minification & compression
- Image optimization
- Lazy loading
- CDN integration (static assets)

**Timeline:** 2 days

---

### Priority 7.3: Security Audit
- OWASP Top 10 review
- Penetration testing (basic)
- SSL/TLS setup
- Security headers
- Dependency audit

**Timeline:** 2 days

---

## 📊 PHASE 8: LAUNCH PREPARATION (3-5 days)

### Checklist
- [ ] All critical fixes completed
- [ ] Security audit passed
- [ ] Performance baseline met
- [ ] All features tested
- [ ] Documentation complete
- [ ] Admin trained
- [ ] Customer support ready
- [ ] Monitoring setup
- [ ] Backup strategy
- [ ] Deployment procedure
- [ ] Rollback procedure
- [ ] Go-live checklist

---

## 📈 SUCCESS METRICS

| Metric | Current | Target | Timeline |
|--------|---------|--------|----------|
| Overall Score | 5.5/10 | 9/10 | Week 8 |
| Feature Completeness | 60% | 95% | Week 8 |
| Security Score | 3/10 | 9/10 | Week 2 |
| Performance Score | 5/10 | 8/10 | Week 7 |
| Test Coverage | 0% | 70%+ | Week 6 |
| Database Normalized | 30% | 100% | Week 2 |
| API Endpoints | 8 | 20+ | Week 4 |
| Admin Features | 70% | 95% | Week 5 |

---

## 🚀 QUICK WINS (This Week)

1. ✅ Stock protection system (2-3 days)
2. ✅ Database normalization (2-3 days)
3. ✅ CSRF protection (1 day)
4. 🔶 Update admin dashboard with low-stock alerts
5. 🔶 Add order item details view

---

## 📝 RISK MITIGATION

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|-----------|
| Data loss (stock changes) | High | Critical | Add database backups, transaction logging |
| Stock race condition | Medium | High | Add row-level locking, queue processing |
| Payment integration delay | Medium | Medium | Use Cash on Delivery as fallback |
| Performance issues | Low | Medium | Regular load testing, caching |
| Security breach | Low | Critical | Security audit, penetration testing |

---

## 🔄 NEXT STEP

**PROCEED TO: Phase 1, Priority 1.1 (Stock Protection System)**

This is the most critical issue and must be resolved first to prevent data corruption and business loss.

**Estimated Time:** 2-3 days  
**Complexity:** Medium  
**Risk:** Low (if done with proper testing)

---

**Document Version:** 1.0  
**Last Updated:** September 1, 2026  
**Prepared For:** Development Team  
**Next Review:** After Phase 1 Completion
