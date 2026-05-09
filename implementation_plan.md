# Ridly — Digital Voucher E-Commerce: Implementation Plan

> **Project**: Ridly (Laravel 13, Tailwind v4, Alpine.js 3, MySQL, Midtrans)  
> **Approach**: 4 Phases, sequential, each requiring your green light before starting.

---

## Current Codebase Audit

After reviewing every file in the project, here is a summary of what exists and what needs fixing.

### What Already Exists
| Feature | Status | Notes |
|---|---|---|
| Laravel 13 skeleton | ✅ Working | PHP 8.4, Vite 8, TW v4 |
| User model + auth | ⚠️ Partial | Login/register work via AJAX modal, but has bugs |
| Product catalog | ✅ Working | 10 seeded products, category filtering, search |
| Favorites | ✅ Working | Toggle via AJAX, persisted in DB |
| Shopping cart | ❌ Session-only | Not persisted to DB; cart view uses **hardcoded dummy data** |
| Checkout / Orders | ❌ Broken | `checkout()` references `invoice_   id` (typo with space), `invoice_number`, `payment_url`, `payment_return` route — all undefined |
| Midtrans integration | ⚠️ Scaffolded | Config exists, `midtrans/midtrans-php` installed, but checkout is broken |
| Point Shop | ⚠️ UI-only | Page renders hardcoded `$rewards` array, "Redeem" button does nothing |
| Gacha | ⚠️ UI-only | Frontend spin animation works, but uses **client-side RNG** — no server validation, no point deduction, no prize persistence |
| Inventory | ❌ Hardcoded | Shows 3 dummy items in Alpine.js, not from DB |
| Transactions | ❌ Hardcoded | Shows 3 dummy rows from `@php` block, not from DB |
| Profile | ✅ Working | Name edit + password change via AJAX |
| Admin panel | ❌ None | No admin routes, controllers, or views |
| Dark/Light theme | ✅ Working | Toggle + localStorage persistence |
| Pixel city background | ✅ Working | Parallax SVG cityscape |

### Critical Bugs Found

| # | Bug | Location |
|---|---|---|
| 1 | `checkout()` has `'invoice_   id'` (space in key) | [StoreController.php:125](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L125) |
| 2 | `checkout()` references `$order->invoice_number` (doesn't exist, column is `noinv`) | [StoreController.php:166](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L166) |
| 3 | `checkout()` references `$order->payment_url` (not a column) | [StoreController.php:180](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L180) |
| 4 | `checkout()` references route `payment_return` (doesn't exist) | [StoreController.php:175](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L175) |
| 5 | `checkout()` route is never registered in `web.php` | [web.php](file:///home/shika/KULIAH/letitride/letirride/routes/web.php) |
| 6 | Cart view path is `'cart'` instead of `'pages.cart'` | [StoreController.php:81](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L81) |
| 7 | Cart page uses **hardcoded dummy items** instead of session/DB data | [cart.blade.php:4-5](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/cart.blade.php#L4-L5) |
| 8 | `addToCart()` JS function in products page is **empty** (closes modal, doesn't call backend) | [products.blade.php:176-178](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/products.blade.php#L176-L178) |
| 9 | Gacha uses **client-side RNG** — exploitable, no server validation | [gacha.blade.php:37-47](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/gacha.blade.php#L37-L47) |
| 10 | Gacha references `Auth::user()->points` and `Auth::user()->balance` — `balance` doesn't exist | [gacha.blade.php:22-26](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/gacha.blade.php#L22-L26) |
| 11 | `GachaController::roll()` references `$wonPrize->is_grand_prize` and `$wonPrize->points_reward` — both columns were **dropped** by migration | [GachaController.php:35-40](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/GachaController.php#L35-L40) |
| 12 | Navbar references `Auth::user()->username` — column is `name`, not `username` | [navbar.blade.php:88-90](file:///home/shika/KULIAH/letitride/letirride/resources/views/components/navbar.blade.php#L88-L90) |
| 13 | `OrderDetail` fillable has `total_price_in_cart` but `checkout()` creates with `price` + `subtotal` keys | [OrderDetail.php:19](file:///home/shika/KULIAH/letitride/letirride/app/Models/OrderDetail.php#L19) vs [StoreController.php:137-143](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L137-L143) |
| 14 | `DiscountType::targetCategory()` points to `Product` instead of `Category` | [DiscountType.php:32](file:///home/shika/KULIAH/letitride/letirride/app/Models/DiscountType.php#L32) |
| 15 | `AuthCert` middleware exists but is a no-op (pass-through) | [AuthCert.php](file:///home/shika/KULIAH/letitride/letirride/app/Http/Middleware/AuthCert.php) |
| 16 | `point-shop` and `gacha` routes are **outside** the `auth` middleware group | [web.php:11-12](file:///home/shika/KULIAH/letitride/letirride/routes/web.php#L11-L12) |
| 17 | Profile shows `Auth::user()->points` but model column is `points_balance` | [profile.blade.php:114](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/profile.blade.php#L114) |
| 18 | Inventory shows hardcoded 12 Items count | [profile.blade.php:123](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/profile.blade.php#L123) |
| 19 | `.env.example` defaults to SQLite but project says MySQL | [.env.example:23](file:///home/shika/KULIAH/letitride/letirride/.env.example#L23) |
| 20 | No `Favorite` model — favorites use raw `DB::table()` queries everywhere | Multiple files |
| 21 | No admin routes or role-based middleware | Entire project |
| 22 | `regAuth` redirects to route `login` on non-JSON (doesn't exist) | [AuthController.php:93](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/AuthController.php#L93) |
| 23 | Seeder uses `$now->subDays()` which mutates the Carbon instance in-place | [DatabaseSeeder.php:428](file:///home/shika/KULIAH/letitride/letirride/database/seeders/DatabaseSeeder.php#L428) |
| 24 | Missing `Midtrans` env vars in `.env.example` | [.env.example](file:///home/shika/KULIAH/letitride/letirride/.env.example) |

---

## User Review Required

> [!IMPORTANT]
> **Database Choice**: The `.env.example` currently defaults to **SQLite**. Your requirements mention **MySQL**. Should we migrate to MySQL now in Phase 1, or keep SQLite for local dev and switch to MySQL later?

> [!IMPORTANT]
> **Admin Panel Scope**: The admin side is not yet built at all. What features do you need the admin to manage? My assumption is:
> - Product CRUD (add/edit/delete vouchers, manage product keys)
> - Order management (view all orders, mark as paid/delivered/failed)
> - User management (view users, ban, adjust points)
> - Gacha pool management (edit prizes, drop rates)
> - View support tickets
> - Dashboard with stats (revenue, orders, users)
> 
> Please confirm or adjust.

> [!IMPORTANT]
> **Midtrans Mode**: Should we use **Sandbox** mode for development? Do you already have Midtrans sandbox credentials, or do you need to create them?

> [!WARNING]
> **Currency**: Seeder products use USD prices (e.g., $10.00) but the UI formats as Indonesian Rupiah (`Rp`). Which currency should Ridly actually use? This affects Midtrans configuration too.

---

## Open Questions

1. **Do you want a dedicated `/admin` panel (separate layout + sidebar) or integrate admin functions into the existing design?**
2. **For the Gacha mechanic — should the cost be only in points, or also allow Rupiah purchases (like the current UI suggests)?**
3. **Should registration auto-login the user, or require them to login separately after signing up?**
4. **Do you have product images/SVGs for all voucher types, or should we generate placeholder assets?**
5. **For the referral system (Phase 4) — what reward should referrer and referee receive? (e.g., 100 points each? A discount voucher?)**

---

## Proposed Changes — 4 Phases

---

## Phase 1 — Foundation: UI/UX Overhaul + Auth Hardening

*Goal*: Fix all critical bugs, harden authentication, upgrade the UI to a premium state, and establish the architectural patterns for all subsequent phases.

---

### Backend — Bug Fixes & Auth

#### [MODIFY] [AuthController.php](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/AuthController.php)
- Fix `regAuth`: remove redirect to non-existent `login` route
- Add proper validation rules (password confirmation, username uniqueness, length limits)
- Add `FormRequest` classes: `LoginRequest`, `RegisterRequest`
- Add email verification support (optional, flagged for Phase 4 2FA)

#### [NEW] `app/Http/Requests/LoginRequest.php`
- Proper validation with rate limiting

#### [NEW] `app/Http/Requests/RegisterRequest.php`
- Name, email (unique, dns), password (min:8, confirmed)

#### [MODIFY] [web.php](file:///home/shika/KULIAH/letitride/letirride/routes/web.php)
- Move `point-shop` and `gacha` routes inside `auth` middleware group (they already have `@auth` guards in views, but routes are unprotected)
- Add proper route organization with `prefix` and `name` groups
- Fix inconsistent route naming

#### [MODIFY] [navbar.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/components/navbar.blade.php)
- Fix `Auth::user()->username` → `Auth::user()->name`
- Add mobile menu items for all nav links (currently missing favorites, about, faq, support in mobile)
- Add points balance display in navbar for logged-in users

#### [MODIFY] [User.php](file:///home/shika/KULIAH/letitride/letirride/app/Models/User.php)
- Add `username` accessor if needed, or standardize on `name`
- Add `isAdmin()` helper method

#### [MODIFY] [DiscountType.php](file:///home/shika/KULIAH/letitride/letirride/app/Models/DiscountType.php)
- Fix `targetCategory()` relationship: change `Product::class` → `Category::class`

#### [NEW] `app/Http/Middleware/EnsureUserIsAdmin.php`
- Role-based middleware for admin routes

---

### Frontend — UI/UX Upgrades

#### [MODIFY] [app.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/layouts/app.blade.php)
- Add meta description, favicon, and Open Graph tags
- Add CSRF meta tag for AJAX requests
- Add toast notification system (Alpine.js component)
- Improve footer with more links (About, FAQ, Contact, Support)

#### [MODIFY] [auth-modal.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/components/auth-modal.blade.php)
- Add password confirmation field to signup
- Add "Show password" toggle
- Improve error display (field-level errors, not just a single message)
- Add password strength indicator
- Link Terms of Service properly (currently `href="#"`)

#### [MODIFY] [app.css](file:///home/shika/KULIAH/letitride/letirride/resources/css/app.css)
- Add toast notification styles
- Add form input focus styles
- Add loading skeleton animations
- Add smooth page transition classes

#### [NEW] `resources/views/components/toast-notification.blade.php`
- Alpine.js-powered toast system for success/error/info messages

#### [MODIFY] [products.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/products.blade.php)
- Wire up `addToCart()` to actually call the backend
- Show cart count update in navbar after adding
- Add quantity selector in buy modal
- Add product description display

#### [MODIFY] All remaining page views
- Replace all hardcoded data with proper Blade `@props` / controller data
- Ensure consistent design language across all pages

---

## Phase 2 — Core Commerce: DB Cart + Checkout + Midtrans

*Goal*: Build the complete purchase flow — from browsing → cart → checkout → payment → receipt → inventory.

---

### Database Changes

#### [NEW] `database/migrations/xxxx_create_carts_table.php`
```
carts: id, user_id (FK), product_id (FK), quantity, created_at, updated_at
UNIQUE(user_id, product_id)
```

#### [NEW] `app/Models/Cart.php`
- Relationships: `belongsTo(User)`, `belongsTo(Product)`

#### [MODIFY] Users table migration
- Add `phone` column (for Midtrans customer details)

---

### Backend

#### [NEW] `app/Http/Controllers/CartController.php`
- `index()` — fetch user's cart items from DB with product eager-loading
- `store($productId)` — add to cart (increment if exists)
- `update($cartId, Request)` — update quantity
- `destroy($cartId)` — remove item
- All return JSON for Alpine.js AJAX

#### [NEW] `app/Http/Controllers/CheckoutController.php`
- `show()` — display checkout page with cart summary + available user discounts
- `process(Request)` — validate cart, apply discount, create Order + OrderDetails, generate Midtrans Snap token, return payment page
- `callback(Request)` — Midtrans webhook handler (server-to-server notification)
- `finish(Request)` — Midtrans finish redirect (user returns from payment)
- After successful payment: assign `ProductKey` to order, award points to user

#### [MODIFY] [StoreController.php](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php)
- Remove all session cart logic
- Remove broken `checkout()` method (moved to `CheckoutController`)
- Keep `showStore()` as-is (it works)

#### [MODIFY] [Order.php](file:///home/shika/KULIAH/letitride/letirride/app/Models/Order.php)
- Fix `fillable` to match actual DB columns
- Add `paid_at` timestamp column

#### [NEW] `app/Http/Controllers/InventoryController.php`
- Fetch user's purchased product keys + active discount vouchers from DB

#### [NEW] `app/Http/Controllers/TransactionController.php`
- Fetch user's order history from DB with order details

---

### Frontend

#### [MODIFY] [cart.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/cart.blade.php)
- Complete rewrite: fetch real cart data from DB
- Quantity +/- controls with AJAX updates
- Remove item with AJAX
- Show discount code input field
- Real-time total calculation
- "Proceed to Checkout" button

#### [NEW] `resources/views/pages/checkout.blade.php`
- Order summary (items, quantities, prices)
- Discount voucher selector (from user's available vouchers)
- Address/phone fields (for Midtrans)
- Midtrans Snap.js payment popup integration

#### [NEW] `resources/views/pages/receipt.blade.php`
- Post-payment receipt with order details, product keys, and download/copy buttons

#### [MODIFY] [inventory.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/inventory.blade.php)
- Pull real data: purchased product keys + discount vouchers
- Copy-to-clipboard for product keys
- Filter by type (Products, Vouchers)

#### [MODIFY] [transactions.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/transactions.blade.php)
- Pull real order history from DB
- Show order status (pending, paid, failed)
- Expand row to show order details / product keys

#### [MODIFY] [profile.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/profile.blade.php)
- Fix `Auth::user()->points` → `Auth::user()->points_balance`
- Show real inventory count from DB
- Add phone number field

---

### Midtrans Integration

#### [MODIFY] [midtrans.php](file:///home/shika/KULIAH/letitride/letirride/config/midtrans.php)
- Already correct, just needs env vars

#### [MODIFY] [.env.example](file:///home/shika/KULIAH/letitride/letirride/.env.example)
- Add `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION`

#### [NEW] Route: `POST /midtrans/callback` (webhook, no auth)
- Verify signature, update order status, assign product keys

---

## Phase 3 — Engagement: Point Rewards + Gacha + Admin Panel

*Goal*: Build the gamification layer and the admin management panel.

---

### Point Rewards System

#### [MODIFY] `CheckoutController` (from Phase 2)
- After successful payment, award `point_reward` from each purchased product to user's `points_balance`

#### [NEW] `app/Http/Controllers/PointShopController.php`
- `index()` — fetch point shop items from DB
- `redeem($itemId)` — validate points, deduct, create `UserDiscount`, record `PointShopPurchase`

#### [MODIFY] [point-shop.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/point-shop.blade.php)
- Replace hardcoded `$rewards` array with DB data
- Wire "Redeem" button to AJAX endpoint
- Show success/error toast
- Disable button if insufficient points

#### [NEW] `app/Models/PointShopItem.php`
- Already has a table from migration, just needs the model

---

### Gacha Mechanic (Server-Side)

#### [MODIFY] [GachaController.php](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/GachaController.php)
- Complete rewrite of `roll()`:
  - Accept cost type (points only — based on your requirements)
  - Server-side weighted RNG using `gacha_pools` table
  - Deduct points from user
  - Create `UserDiscount` for won discount voucher
  - Return JSON with prize data for frontend animation
  - Pity system consideration (track consecutive rolls without rare+)

#### [NEW] `app/Models/GachaHistory.php` + migration
```
gacha_histories: id, user_id (FK), gacha_pool_id (FK), cost_type, cost_amount, created_at
```

#### [MODIFY] [gacha.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/gacha.blade.php)
- Load prize pool from server data (not hardcoded JS)
- Spin animation triggers AJAX call → server decides winner → frontend animates to result
- Update points balance in navbar after spin
- Show gacha history

---

### Admin Dashboard

#### [NEW] `app/Http/Controllers/Admin/DashboardController.php`
- Stats overview: total revenue, total orders, total users, active products

#### [NEW] `app/Http/Controllers/Admin/ProductController.php`
- Full CRUD: list, create, edit, delete products
- Manage product keys (bulk add, view status)
- Upload product images

#### [NEW] `app/Http/Controllers/Admin/OrderController.php`
- View all orders with filters (status, date range, user)
- Update order status manually

#### [NEW] `app/Http/Controllers/Admin/UserController.php`
- View all users, search, filter
- Adjust points balance
- Ban/unban users

#### [NEW] `app/Http/Controllers/Admin/GachaController.php`
- Manage gacha pool entries (CRUD)
- View gacha history/analytics

#### [NEW] `app/Http/Controllers/Admin/TicketController.php`
- View/respond to support tickets
- Update ticket status

#### [NEW] `resources/views/admin/` directory
- `layouts/admin.blade.php` — admin layout with sidebar navigation
- `dashboard.blade.php` — stats cards + recent orders
- `products/index.blade.php`, `products/create.blade.php`, `products/edit.blade.php`
- `orders/index.blade.php`, `orders/show.blade.php`
- `users/index.blade.php`
- `gacha/index.blade.php`
- `tickets/index.blade.php`, `tickets/show.blade.php`

#### [MODIFY] [web.php](file:///home/shika/KULIAH/letitride/letirride/routes/web.php)
- Add `Route::prefix('admin')->middleware(['auth', 'admin'])->group(...)` with all admin routes

---

## Phase 4 — Polish: Referral Codes + 2FA/OAuth + Final QA

*Goal*: Add engagement features and security hardening. These are "idea phase" features.

---

### Referral System

#### [NEW] Migration: `add_referral_columns_to_users_table`
```
referral_code: string, unique, auto-generated on registration
referred_by: FK to users, nullable
```

#### [NEW] `database/migrations/xxxx_create_referral_rewards_table.php`
```
referral_rewards: id, referrer_id (FK), referee_id (FK), reward_type, reward_value, created_at
```

#### [NEW] `app/Http/Controllers/ReferralController.php`
- Show referral code + share link on profile page
- Apply referral code during registration
- Award points/discount to both referrer and referee after referee's first purchase

#### [MODIFY] Registration flow
- Add optional referral code field

---

### 2FA + Google OAuth

#### Google OAuth
- Install `laravel/socialite`
- Add Google login button to auth modal
- Create/link accounts on Google OAuth callback
- Use existing `google_id` column on users table

#### Two-Factor Authentication
- Install `pragmarx/google2fa-laravel` + `bacon/bacon-qr-code`
- Add 2FA setup page in user settings
- QR code generation for authenticator app
- Require 2FA code on login when enabled
- Recovery codes

#### [NEW] Migration: `add_2fa_columns_to_users_table`
```
two_factor_secret: text, nullable
two_factor_recovery_codes: text, nullable
two_factor_confirmed_at: timestamp, nullable
```

---

### Final Polish
- Full responsive testing (mobile, tablet, desktop)
- Loading states for all AJAX actions
- Error boundaries and fallback UI
- SEO meta tags on all pages
- Performance: eager loading, query optimization, caching
- Security audit: CSRF, XSS, SQL injection, rate limiting
- Write Pest tests for critical flows (auth, checkout, gacha)

---

## Verification Plan

### Automated Tests
- `php artisan test --compact` after each phase
- Write Pest feature tests for: registration, login, add-to-cart, checkout, gacha roll, point redemption
- Browser smoke tests via browser subagent

### Manual Verification
- Run `composer run dev` and visually verify each page
- Test complete purchase flow: browse → cart → checkout → Midtrans sandbox → receipt → inventory
- Test gacha: spin → verify points deducted → verify prize in inventory
- Test admin: CRUD products, view orders, manage users

---

## Phase Execution Summary

| Phase | Scope | Key Deliverables |
|---|---|---|
| **Phase 1** | UI/UX + Auth + Bug Fixes | All 24 bugs fixed, premium UI, toast system, hardened auth |
| **Phase 2** | DB Cart + Checkout + Midtrans | Full purchase flow end-to-end, real inventory & transactions |
| **Phase 3** | Points + Gacha + Admin | Server-side gacha, point shop redemption, full admin panel |
| **Phase 4** | Referral + 2FA/OAuth + QA | Referral codes, Google login, 2FA, final polish & tests |

> **Awaiting your green light to begin Phase 1.**
