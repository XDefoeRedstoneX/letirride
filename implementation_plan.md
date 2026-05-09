# Ridly — Digital Voucher E-Commerce: Implementation Plan (v2)

> **Project**: Ridly (Laravel 13, Tailwind v4, Alpine.js 3, SQLite local / MySQL production, Midtrans)  
> **Approach**: 4 Phases, sequential — wait for green light before each phase.

---

## Decisions Locked In (from your feedback)

| Question | Decision |
|---|---|
| Database | SQLite for local dev. MySQL `.env` with `root` / `1234` for testing. Production already configured. |
| Admin Panel | Deferred to Phase 3 — customer side first. |
| Midtrans | Sandbox mode, credentials already set. |
| Currency | **Indonesian Rupiah (Rp)** — update all seeder prices to Rupiah values. |
| Gacha cost | **Points only** — remove Rupiah spin option from UI. |
| Registration | **Auto-login** after successful signup. |
| Product images | Use **placeholders** for now. |
| Referral reward | **Points for each purchase** for both referrer and referee. |
| Phone column | Not adding — not required by Midtrans for digital goods. |
| Login ToS checkbox | **Remove** "Agree to Terms" from login form. Keep on signup only. |

---

## Updated Database State (already migrated)

The following tables/changes **already exist** — no need to create new migrations for these:

| Item | Status | Notes |
|---|---|---|
| `cart_items` table | ✅ Migrated | `id, user_id, product_id, quantity, unique(user_id, product_id), timestamps` |
| `CartItem` model | ✅ Created | With `user()` and `product()` relationships |
| `favorites` table | ✅ Migrated | `id, user_id, product_id, unique(user_id, product_id), created_at` |
| `referral_code` + `referred_by` on `users` | ✅ Migrated | `referral_code` nullable unique, `referred_by` FK to users |
| `referrals` table | ✅ Migrated | `id, referrer_id, referred_user_id, reward_discount_id, status, created_at` |
| `Referral` model | ✅ Created | With `referrer()`, `referredUser()`, `rewardDiscount()` relationships |
| `User` model | ✅ Updated | `cartItems()`, `referrals()`, `referredBy()`, `referral()` relationships, fillable includes `referral_code`, `referred_by` |
| `Product` model | ✅ Updated | `cartItems()` relationship added |
| `invoice_id` typo | ✅ Fixed | You already fixed the space in `invoice_   id` → `invoice_id` |

---

## Remaining Bugs (updated from audit)

| # | Bug | Location | Phase |
|---|---|---|---|
| ~~1~~ | ~~`invoice_   id` typo~~ | ~~StoreController:125~~ | ~~Fixed by you~~ |
| 2 | `checkout()` references `$order->invoice_number` (column is `noinv`) | [StoreController.php:166](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L166) | P2 |
| 3 | `checkout()` references `$order->payment_url` (not a column) | [StoreController.php:180](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L180) | P2 |
| 4 | `checkout()` references route `payment_return` (doesn't exist) | [StoreController.php:175](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L175) | P2 |
| 5 | `checkout()` route never registered in `web.php` | [web.php](file:///home/shika/KULIAH/letitride/letirride/routes/web.php) | P2 |
| 6 | Cart view path is `'cart'` instead of `'pages.cart'` | [StoreController.php:81](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L81) | P1 |
| 7 | Cart page uses **hardcoded dummy items** instead of DB data | [cart.blade.php:4-5](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/cart.blade.php#L4-L5) | P2 |
| 8 | `addToCart()` JS function is **empty** — doesn't call backend | [products.blade.php:176-178](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/products.blade.php#L176-L178) | P2 |
| 9 | Gacha uses **client-side RNG** — exploitable | [gacha.blade.php:37-47](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/gacha.blade.php#L37-L47) | P3 |
| 10 | Gacha references `Auth::user()->balance` — doesn't exist | [gacha.blade.php:22-26](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/gacha.blade.php#L22-L26) | P3 |
| 11 | `GachaController::roll()` references dropped columns `is_grand_prize`, `points_reward` | [GachaController.php:35-40](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/GachaController.php#L35-L40) | P3 |
| 12 | Navbar references `Auth::user()->username` (should be `name`) | [navbar.blade.php:88-90](file:///home/shika/KULIAH/letitride/letirride/resources/views/components/navbar.blade.php#L88-L90) | P1 |
| 13 | `OrderDetail` fillable `total_price_in_cart` mismatches `checkout()` keys | [OrderDetail.php:19](file:///home/shika/KULIAH/letitride/letirride/app/Models/OrderDetail.php#L19) | P2 |
| 14 | `DiscountType::targetCategory()` points to `Product` instead of `Category` | [DiscountType.php:32](file:///home/shika/KULIAH/letitride/letirride/app/Models/DiscountType.php#L32) | P1 |
| 15 | Login form has "Agree to Terms" checkbox (should be removed per your request) | [auth-modal.blade.php:118-121](file:///home/shika/KULIAH/letitride/letirride/resources/views/components/auth-modal.blade.php#L118-L121) | P1 |
| 16 | `point-shop` and `gacha` routes outside `auth` middleware | [web.php:11-12](file:///home/shika/KULIAH/letitride/letirride/routes/web.php#L11-L12) | P1 |
| 17 | Profile shows `Auth::user()->points` (column is `points_balance`) | [profile.blade.php:114](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/profile.blade.php#L114) | P1 |
| 18 | Inventory shows hardcoded "12 Items" count | [profile.blade.php:123](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/profile.blade.php#L123) | P2 |
| 19 | No `Favorite` model — favorites use raw `DB::table()` queries | Multiple files | P1 |
| 20 | `regAuth` redirects to route `login` (doesn't exist) | [AuthController.php:93](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/AuthController.php#L93) | P1 |
| 21 | Seeder `$now->subDays()` mutates Carbon in-place | [DatabaseSeeder.php:428](file:///home/shika/KULIAH/letitride/letirride/database/seeders/DatabaseSeeder.php#L428) | P1 |
| 22 | Missing Midtrans env vars in `.env.example` | [.env.example](file:///home/shika/KULIAH/letitride/letirride/.env.example) | P2 |
| 23 | `Order` fillable has `noinv` but `checkout()` uses `invoice_id` key | [Order.php](file:///home/shika/KULIAH/letitride/letirride/app/Models/Order.php) vs [StoreController.php:125](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L125) | P2 |
| 24 | `StoreController` still uses **session** cart — needs to switch to `cart_items` DB table | [StoreController.php:62-95](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php#L62-L95) | P2 |
| 25 | Point shop shows hardcoded `$rewards` array, "Redeem" does nothing | [point-shop.blade.php:27-36](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/point-shop.blade.php#L27-L36) | P3 |
| 26 | Transactions page shows hardcoded dummy rows | [transactions.blade.php:27-31](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/transactions.blade.php#L27-L31) | P2 |
| 27 | Inventory page shows hardcoded dummy items | [inventory.blade.php:6-10](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/inventory.blade.php#L6-L10) | P2 |

---

## Phase 1 — Foundation: UI/UX + Auth + Bug Fixes

*Goal*: Fix critical bugs, harden auth (with auto-login), upgrade UI, add toast system.

---

### Backend Fixes

#### [MODIFY] [AuthController.php](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/AuthController.php)
- Fix `regAuth()`: auto-login user after registration (remove redirect to non-existent `login` route)
- Add proper validation rules (password min:8, email unique + dns, name min:2)
- Generate unique `referral_code` on registration (needed later in Phase 4, but the DB column is already there)

#### [MODIFY] [web.php](file:///home/shika/KULIAH/letitride/letirride/routes/web.php)
- Move `point-shop` and `gacha` routes inside `auth` middleware group (Bug #16)
- Fix cart view path `'cart'` → `'pages.cart'` (Bug #6)
- Organize routes with proper prefix/name groups

#### [MODIFY] [navbar.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/components/navbar.blade.php)
- Fix `Auth::user()->username` → `Auth::user()->name` (Bug #12)
- Add points balance display in navbar for logged-in users
- Add all nav items to mobile menu (currently missing)

#### [MODIFY] [auth-modal.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/components/auth-modal.blade.php)
- **Remove** "Agree to Terms" checkbox from **login** form (Bug #15)
- Keep Terms checkbox on signup form but link it to actual Terms page
- Add "Show password" toggle
- Add password strength indicator for signup
- Change signup success flow: auto-login → redirect to home (no more `alert()`)

#### [MODIFY] [DiscountType.php](file:///home/shika/KULIAH/letitride/letirride/app/Models/DiscountType.php)
- Fix `targetCategory()`: change `Product::class` → `Category::class` (Bug #14)

#### [MODIFY] [profile.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/profile.blade.php)
- Fix `Auth::user()->points` → `Auth::user()->points_balance` (Bug #17)

#### [NEW] `app/Models/Favorite.php`
- Create proper Eloquent model for favorites (Bug #19)
- Relationships: `belongsTo(User)`, `belongsTo(Product)`

#### [MODIFY] [FavoriteController.php](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/FavoriteController.php)
- Refactor to use `Favorite` model instead of raw `DB::table()` queries

#### [MODIFY] [User.php](file:///home/shika/KULIAH/letitride/letirride/app/Models/User.php)
- Add `favorites()` relationship
- Add `isAdmin(): bool` helper method

#### [MODIFY] [DatabaseSeeder.php](file:///home/shika/KULIAH/letitride/letirride/database/seeders/DatabaseSeeder.php)
- Fix Carbon mutation bug — use `clone $now` consistently (Bug #21 — already partially done, verify all occurrences)
- Update product prices to Rupiah values (e.g., `10.00` → `160000.00`)

---

### Frontend — UI Upgrades

#### [MODIFY] [app.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/layouts/app.blade.php)
- Add meta description and Open Graph tags
- Add CSRF meta tag for AJAX
- Add toast notification component
- Improve footer (About, FAQ, Contact, Support links)

#### [NEW] `resources/views/components/toast-notification.blade.php`
- Alpine.js-powered toast system for success/error/info messages
- Replaces all `alert()` calls

#### [MODIFY] [app.css](file:///home/shika/KULIAH/letitride/letirride/resources/css/app.css)
- Add toast notification styles
- Add loading skeleton animations
- Add password strength indicator styles

#### [MODIFY] All page views — consistency pass
- Ensure all pages use the same design language
- Ensure responsive behavior is consistent

---

### Deliverables for Phase 1
- [ ] All Bug #6, #12, #14, #15, #16, #17, #19, #20, #21 fixed
- [ ] Login modal: no Terms checkbox, show password toggle
- [ ] Signup: auto-login on success, password strength, Terms linked properly
- [ ] Toast notification system replaces all `alert()` calls
- [ ] Proper `Favorite` model
- [ ] `isAdmin()` helper on User
- [ ] Seeder prices updated to Rupiah
- [ ] Points balance visible in navbar

---

## Phase 2 — Core Commerce: DB Cart + Checkout + Midtrans + Inventory

*Goal*: Wire up the complete purchase flow using the existing `cart_items` DB table.

---

### Backend

#### [NEW] `app/Http/Controllers/CartController.php`
- `index()` — fetch user's `CartItem`s from DB with `product` eager-loading → pass to `pages.cart` view
- `store($productId)` — add to cart (increment quantity if exists via `updateOrCreate`)
- `update($cartItemId, Request)` — update quantity (AJAX)
- `destroy($cartItemId)` — remove item (AJAX)
- `count()` — return cart item count for navbar badge (AJAX)

#### [NEW] `app/Http/Controllers/CheckoutController.php`
- `show()` — display checkout page: cart summary + user's available (unused, unexpired) discount vouchers
- `process(Request)` — validate cart not empty, apply discount voucher, create `Order` + `OrderDetail`s, generate Midtrans Snap token, return snap token to frontend
- `callback(Request)` — Midtrans server-to-server notification webhook: verify signature hash, update order status, assign `ProductKey`s, award `points_balance` to user
- `finish($orderId)` — user returns from Midtrans: show receipt/result page

#### [MODIFY] [StoreController.php](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/StoreController.php)
- Remove all session cart methods (`addCart`, `viewCart`, `updateCart`)
- Remove broken `checkout()` method (replaced by `CheckoutController`)
- Remove unused `VoucherCode` import
- Keep `showStore()` as-is

#### [MODIFY] [Order.php](file:///home/shika/KULIAH/letitride/letirride/app/Models/Order.php)
- Align `fillable` with actual columns: fix `noinv` → ensure it matches what `checkout()` uses (either rename column via migration or update code to use `noinv`)
- Add `paid_at` column via new migration if needed

#### [MODIFY] [OrderDetail.php](file:///home/shika/KULIAH/letitride/letirride/app/Models/OrderDetail.php)
- Fix `fillable` to match actual checkout data (Bug #13)

#### [NEW] `app/Http/Controllers/InventoryController.php`
- Fetch user's purchased `ProductKey`s (from paid orders) + active `UserDiscount`s from DB

#### [NEW] `app/Http/Controllers/TransactionController.php`
- Fetch user's `Order`s with `orderDetails.product` eager-loading

#### [MODIFY] [web.php](file:///home/shika/KULIAH/letitride/letirride/routes/web.php)
- Add cart routes: `GET /cart`, `POST /cart/{product}`, `PATCH /cart/{cartItem}`, `DELETE /cart/{cartItem}`, `GET /cart/count`
- Add checkout routes: `GET /checkout`, `POST /checkout`, `POST /midtrans/callback` (no auth), `GET /checkout/finish/{order}`
- Update inventory + transactions routes to use new controllers

#### [MODIFY] [.env.example](file:///home/shika/KULIAH/letitride/letirride/.env.example)
- Add `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION=false`

---

### Frontend

#### [MODIFY] [products.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/products.blade.php)
- Wire `addToCart()` to call `POST /cart/{product}` via AJAX
- Show toast on success
- Update navbar cart badge count

#### [MODIFY] [cart.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/cart.blade.php)
- **Complete rewrite**: fetch real cart data from `CartController@index`
- Quantity +/- controls with AJAX
- Remove item with AJAX
- Show discount voucher selector dropdown (user's available vouchers)
- Real-time total calculation
- "Proceed to Checkout" → triggers Midtrans Snap.js popup

#### [NEW] `resources/views/pages/checkout-result.blade.php`
- Post-payment result: order summary, product keys (if paid), status

#### [MODIFY] [inventory.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/inventory.blade.php)
- Pull real data from `InventoryController`
- Show purchased product keys with copy-to-clipboard
- Show active discount vouchers
- Filter tabs (Products / Vouchers)

#### [MODIFY] [transactions.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/transactions.blade.php)
- Pull real order history from `TransactionController`
- Show order status badges (pending/paid/failed)
- Expandable rows to show order details

#### [MODIFY] [profile.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/profile.blade.php)
- Show real inventory count from DB (Bug #18)

---

### Deliverables for Phase 2
- [ ] All Bug #2, #3, #4, #5, #7, #8, #13, #18, #22, #23, #24, #26, #27 fixed
- [ ] Full DB cart (add, update quantity, remove, persist across sessions)
- [ ] Checkout with Midtrans Snap.js popup
- [ ] Midtrans webhook handler (server notification)
- [ ] Post-payment: assign ProductKeys, award points
- [ ] Real inventory page (purchased keys + vouchers)
- [ ] Real transaction history page

---

## Phase 3 — Engagement: Points + Gacha + Admin Panel

*Goal*: Build server-side gacha, point shop redemption, and the admin dashboard.

---

### Point Shop

#### [NEW] `app/Models/PointShopItem.php`
- Model for existing `point_shop_items` table

#### [NEW] `app/Http/Controllers/PointShopController.php`
- `index()` — fetch active point shop items from DB
- `redeem($itemId)` — validate sufficient points → deduct → create `UserDiscount` → record `PointShopPurchase` → return JSON

#### [MODIFY] [point-shop.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/point-shop.blade.php)
- Replace hardcoded `$rewards` with DB data (Bug #25)
- Wire "Redeem" buttons to AJAX
- Disable if insufficient points
- Toast feedback

---

### Gacha (Server-Side)

#### [MODIFY] [GachaController.php](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/GachaController.php)
- **Complete rewrite** of `roll()` (Bugs #9, #10, #11):
  - Points-only cost (remove Rupiah option)
  - Server-side weighted RNG using `gacha_pools` table
  - Deduct `points_balance` from user
  - Create `UserDiscount` for won prize
  - Return JSON with prize data for frontend animation

#### [NEW] `app/Models/GachaHistory.php` + migration
```
gacha_histories: id, user_id (FK), gacha_pool_id (FK), points_spent, created_at
```

#### [MODIFY] [gacha.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/gacha.blade.php)
- Load prize pool from server (not hardcoded JS)
- Remove Rupiah spin button — points only
- Spin animation calls AJAX → server decides winner → frontend animates
- Update points in navbar after spin
- "Claim Reward" button confirms prize

---

### Admin Dashboard

#### [NEW] `app/Http/Middleware/EnsureUserIsAdmin.php`
- Check `$user->role === 'admin'`

#### [NEW] `app/Http/Controllers/Admin/DashboardController.php`
- Stats: revenue, orders, users, active products

#### [NEW] `app/Http/Controllers/Admin/ProductController.php`
- CRUD products + manage product keys

#### [NEW] `app/Http/Controllers/Admin/OrderController.php`
- View/filter all orders, update status

#### [NEW] `app/Http/Controllers/Admin/UserController.php`
- View users, adjust points, manage roles

#### [NEW] `app/Http/Controllers/Admin/GachaController.php`
- Manage gacha pool entries

#### [NEW] `app/Http/Controllers/Admin/TicketController.php`
- View/respond to support tickets

#### [NEW] `resources/views/admin/` directory
- `layouts/admin.blade.php` — dedicated admin layout with sidebar
- Pages: dashboard, products CRUD, orders, users, gacha, tickets

#### [MODIFY] [web.php](file:///home/shika/KULIAH/letitride/letirride/routes/web.php)
- Add `Route::prefix('admin')->middleware(['auth', 'admin'])->group(...)` with all admin resource routes

---

### Deliverables for Phase 3
- [ ] All Bug #9, #10, #11, #25 fixed
- [ ] Server-side gacha with weighted RNG + gacha history
- [ ] Point shop redemption (deduct points, create voucher)
- [ ] Full admin panel with product CRUD, order management, user management, gacha management, ticket management

---

## Phase 4 — Polish: Referral System + 2FA/OAuth + QA

*Goal*: Wire up the already-migrated referral system, add Google OAuth, optional 2FA.

---

### Referral System (DB already exists)

#### [NEW] `app/Http/Controllers/ReferralController.php`
- Show referral code + shareable link on profile
- Apply referral code during registration (optional field)
- On each purchase by referee: award points to both referrer and referee

#### [MODIFY] [AuthController.php](file:///home/shika/KULIAH/letitride/letirride/app/Http/Controllers/AuthController.php)
- Accept optional `referral_code` field during registration
- Look up referrer, set `referred_by`, create `Referral` record

#### [MODIFY] `CheckoutController` (from Phase 2)
- After successful payment: check if user was referred → award referral points to both

#### [MODIFY] [profile.blade.php](file:///home/shika/KULIAH/letitride/letirride/resources/views/pages/profile.blade.php)
- Add referral code display with copy + share buttons
- Show referral stats (count, points earned)

---

### Google OAuth

- Install `laravel/socialite`
- Add Google login/signup button to auth modal
- Handle OAuth callback: create account or link to existing via `google_id`

#### [NEW] `app/Http/Controllers/GoogleAuthController.php`
- `redirect()` → redirect to Google OAuth
- `callback()` → handle response, create/find user, auto-login

---

### Two-Factor Authentication (Optional)

- Install `pragmarx/google2fa-laravel` + `bacon/bacon-qr-code`
- Add 2FA setup in user profile/settings
- QR code generation for authenticator apps
- Recovery codes

#### [NEW] Migration: `add_2fa_columns_to_users_table`
```
two_factor_secret: text, nullable
two_factor_recovery_codes: text, nullable
two_factor_confirmed_at: timestamp, nullable
```

---

### Final Polish
- Full responsive testing
- Loading states for all AJAX actions
- SEO meta tags on all pages
- Performance: eager loading, query optimization
- Security: CSRF, XSS, rate limiting audit
- Pest tests for critical flows

---

## Verification Plan

### Automated Tests
```bash
php artisan test --compact
```
- Pest feature tests for: registration (with auto-login), login, add-to-cart, checkout flow, gacha roll, point redemption, referral

### Manual Verification
- `composer run dev` → visually verify all pages
- Complete purchase flow: browse → cart → checkout → Midtrans sandbox → receipt → inventory
- Gacha: spin → points deducted → prize in inventory
- Admin: CRUD products, view orders, manage users

---

## Phase Summary

| Phase | Scope | Bugs Fixed |
|---|---|---|
| **Phase 1** | UI/UX + Auth (auto-login, remove login ToS) + bug fixes | #6, #12, #14, #15, #16, #17, #19, #20, #21 |
| **Phase 2** | DB Cart + Checkout + Midtrans + real Inventory & Transactions | #2, #3, #4, #5, #7, #8, #13, #18, #22, #23, #24, #26, #27 |
| **Phase 3** | Point Shop + Server-side Gacha + Admin Panel | #9, #10, #11, #25 |
| **Phase 4** | Referral wiring + Google OAuth + 2FA + Final QA | — |

> **Awaiting your green light to begin Phase 1.**
