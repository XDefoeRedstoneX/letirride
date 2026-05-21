# Ridly — Task List

> Single source of truth for what's done, partial, and remaining.
> Legend: `[x]` done · `[~]` partial · `[ ]` todo
> Last updated: 2026-05-18

---

## Roadmap (phased rollout)

1. **Phase 1** — Close partial-implementation gaps (tickets POST, forgot-password, FAQ wiring, admin point-shop/FAQ CRUD, change-email)
2. **Phase 2** — Voucher email delivery automation
3. **Phase 3** — Admin completeness & operational tools (redeem-code generator, refunds, settings page, surveys, user ban)
4. **Phase 4** — Optional / Extreme features (Google OAuth, gifting, point-boosted gacha, news, forum, character themes)

---

## 1. Authentication & Account

- [x] Email/password login & register — [AuthController.php](app/Http/Controllers/AuthController.php), [routes/web.php](routes/web.php#L23-L24)
- [x] Logout — [routes/web.php:35](routes/web.php#L35)
- [x] Change password — [routes/web.php:64](routes/web.php#L64)
- [x] Update profile (name, avatar) — [profile.blade.php](resources/views/pages/profile.blade.php), [settings.blade.php](resources/views/pages/settings.blade.php)
- [x] Dark / light mode (client-side, localStorage)
- [~] Change email — UI shows email as locked; no update endpoint — [settings.blade.php](resources/views/pages/settings.blade.php)
- [~] Forgot password — GET `showForgot` only; missing email send + reset POST — [routes/web.php:72](routes/web.php#L72)
- [ ] Google OAuth — `google_id` column exists, no flow implemented

## 2. Product Catalogue

- [x] Categories (5 seeded) & Subcategories (17 seeded) — [DatabaseSeeder.php](database/seeders/DatabaseSeeder.php#L108-L158)
- [x] Product listing with search + category filter — [StoreController.php](app/Http/Controllers/StoreController.php), [products.blade.php](resources/views/pages/products.blade.php)
- [x] **Subcategory accordion** inside each category (drill down: Subscriptions → Netflix → products) — [products.blade.php](resources/views/pages/products.blade.php)
- [x] **Stockout UI** — voucher products show "OUT OF STOCK" overlay + disabled BUY when no `product_keys` available; cart endpoint rejects add-to-cart past available stock — [StoreController.php](app/Http/Controllers/StoreController.php), [CartController.php](app/Http/Controllers/CartController.php)
- [x] Product detail modal (stock count shown for voucher products)
- [x] Direct-topup flow with player-ID capture (extra) — [TopupCredential.php](app/Models/TopupCredential.php)

## 3. Cart & Checkout

- [x] Cart CRUD (add / update / remove / count) — [CartController.php](app/Http/Controllers/CartController.php), [cart.blade.php](resources/views/pages/cart.blade.php)
- [x] Discount voucher selection at checkout
- [x] Midtrans Snap integration (process, pay, callback, verify, finish, status) — [CheckoutController.php](app/Http/Controllers/CheckoutController.php), [config/midtrans.php](config/midtrans.php)
- [x] Order fulfilment + key dispensing
- [ ] **Automated voucher delivery via email** (xxxx.xxxx.xxxx format) — `app/Mail/`, `app/Notifications/`, `app/Jobs/` all empty

## 4. Inventory & Transactions

- [x] Inventory page (claimed keys, reveal modal) — [InventoryController.php](app/Http/Controllers/InventoryController.php), [inventory.blade.php](resources/views/pages/inventory.blade.php)
- [x] Transactions / payment history — [TransactionController.php](app/Http/Controllers/TransactionController.php), [transactions.blade.php](resources/views/pages/transactions.blade.php)
- [x] Cancel pending order

## 5. Points & Gacha

- [x] Earn points on fulfilled orders (per-product multiplier) — [Product.php](app/Models/Product.php) `calculatePoints()`
- [x] Point balance display (navbar + profile + point-shop)
- [x] Point shop browse + redeem → discount voucher — [PointController.php](app/Http/Controllers/PointController.php), [point-shop.blade.php](resources/views/pages/point-shop.blade.php)
- [x] Gacha roll (weighted RNG → discount) — [GachaController.php](app/Http/Controllers/GachaController.php), [gacha.blade.php](resources/views/pages/gacha.blade.php)
- [ ] Spend points to boost gacha win chance (extreme spec)

## 6. Customer Support & Engagement

- [~] Submit support ticket — UI exists, `/tickets` is `fn () => view(...)`; no POST handler/controller — [routes/web.php:114](routes/web.php#L114), [tickets.blade.php](resources/views/pages/tickets.blade.php)
- [~] Customer FAQ page — UI exists, not fed from seeded `faqs` table — [routes/web.php:112](routes/web.php#L112), [faq.blade.php](resources/views/pages/faq.blade.php)
- [x] Static pages: terms, privacy, about — [routes/web.php:109-111](routes/web.php#L109-L111)
- [ ] Surveys / feedback box (distinct from tickets)
- [ ] Redeem-code "easter egg" entry (codes from external sources)
- [ ] News / events
- [ ] Forum
- [ ] Character selection with per-character UI themes

## 7. Extras (built, beyond original spec)

- [x] Favorites / Wishlist — [FavoriteController.php](app/Http/Controllers/FavoriteController.php), [favorites.blade.php](resources/views/pages/favorites.blade.php)
- [x] Referrals — [Referral.php](app/Models/Referral.php), `referral_code` + `referred_by` on users

## 8. Admin Panel

- [x] Role-gated middleware — [EnsureUserIsAdmin.php](app/Http/Middleware/EnsureUserIsAdmin.php), `users.role` column
- [x] Dashboard with KPIs & recent orders — [Admin/DashboardController.php](app/Http/Controllers/Admin/DashboardController.php), [admin/dashboard.blade.php](resources/views/admin/dashboard.blade.php)
- [x] User management (list, role, points) — [Admin/UserController.php](app/Http/Controllers/Admin/UserController.php)
- [x] Product CRUD + bulk key upload — [Admin/ProductController.php](app/Http/Controllers/Admin/ProductController.php)
- [x] Order management (list + status update) — [Admin/OrderController.php](app/Http/Controllers/Admin/OrderController.php)
- [x] Gacha pool CRUD — [Admin/GachaController.php](app/Http/Controllers/Admin/GachaController.php)
- [x] Ticket triage (list + status update) — [Admin/TicketController.php](app/Http/Controllers/Admin/TicketController.php)
- [x] Separate admin layout/sidebar (fixed broken Products link) — [resources/views/admin/layouts/](resources/views/admin/layouts/)
- [~] Point-shop item CRUD — route is `fn () => view(...)`, no controller — [routes/web.php:104](routes/web.php#L104)
- [~] FAQ CRUD — route is `fn () => view(...)`, no controller — [routes/web.php:105](routes/web.php#L105)
- [ ] Dedicated voucher / redeem-code generator
- [ ] Order refund flow (Midtrans refund API + status rollback)
- [ ] Admin settings / configuration page
- [ ] Survey / feedback response viewer
- [ ] User ban / disable

---

## Progress Snapshot

| Area | Done | Partial | Todo |
|---|---:|---:|---:|
| 1. Auth & Account | 5 | 2 | 1 |
| 2. Catalogue | 6 | 0 | 0 |
| 3. Cart & Checkout | 4 | 0 | 1 |
| 4. Inventory & Transactions | 3 | 0 | 0 |
| 5. Points & Gacha | 4 | 0 | 2 |
| 6. Customer Support | 1 | 2 | 5 |
| 7. Extras | 2 | 0 | 0 |
| 8. Admin Panel | 8 | 2 | 5 |
| **Total** | **33** | **6** | **14** |
