# Database Synchronization Plan — Local ⇄ CPanel (ULID logical sync)

> Status: **PLAN — not yet implemented.** No schema or code changes happen until this is approved.
> Author context: Laravel 13 / PHP 8.3 e‑commerce app (`subsift9_ridlycommerce`), MariaDB 10.6 on
> Jagoan Hosting (shared cPanel/CloudLinux). Local node runs the same app against a local MySQL/MariaDB.

---

## 1. Goal & non‑goals

**Goal.** Keep a **local** database and the **CPanel** database in agreement so the business can keep
operating from either node, with changes flowing **both directions** on a **1‑minute** cycle, plus an
operator **Pause/Resume** switch and a **Sync Now** button that exist **only on the local node**.

**Non‑goals / explicit limits (read this first):**

- This is **NOT** native MySQL/MariaDB master–master replication. That is **impossible** on this hosting:
  `log_bin = 0`, the DB user has no `SUPER` / `REPLICATION SLAVE` / `BINLOG` privileges, and there is no
  `my.cnf` access on shared CloudLinux. We replicate at the **application/logical layer** instead.
- Because the local machine is **behind NAT** and the **Midtrans payment webhooks**
  (`POST /midtrans/callback`, `POST /gacha/pay/callback`) only reach the **public CPanel domain**, the
  money/inventory core is unavoidably **CPanel‑authoritative**. True "write on both sides" applies only to
  non‑financial user data. See §4.

---

## 2. Topology

```
        ┌──────────────────────────┐                 ┌──────────────────────────┐
        │   LOCAL NODE (master A)  │                 │  CPANEL NODE (master B)  │
        │   Laravel app + MySQL    │                 │  Laravel app + MariaDB   │
        │   behind NAT             │                 │  public, gets webhooks   │
        │                          │                 │                          │
        │  sync:run (every minute) │  ── PULL ───▶    │  reachable on :3306      │
        │  PUSH ───────────────────┼──────────────▶  │  (already verified)      │
        │  Control panel (local-   │                 │  NO outbound to local    │
        │  only): Pause / Sync Now │                 │  (cannot reach NAT)      │
        └──────────────────────────┘                 └──────────────────────────┘
```

- **Only the local node initiates connections.** CPanel never connects to local. Both PULL and PUSH are
  performed by the local scheduler over the direct MySQL connection we already confirmed works.
- CPanel needs **no sync daemon, no cron, no extra service.** It only needs the schema additions (ULID
  columns, `updated_at`, soft deletes, change‑log table) so the local engine can read/write it.

---

## 3. Node identity

Each node gets a stable identifier used to stamp every change and to break ties.

| Node   | `SYNC_NODE_ID` | `SYNC_IS_LOCAL` | Role for money/inventory |
|--------|----------------|-----------------|--------------------------|
| Local  | `local`        | `true`          | replica (read‑only)      |
| CPanel | `cpanel`       | `false`         | **authority**            |

`.env` (local):
```
SYNC_NODE_ID=local
SYNC_IS_LOCAL=true
SYNC_ENABLED=true
SYNC_REMOTE_CONNECTION=cpanel      # a second DB connection defined in config/database.php
```
`.env` (cpanel): `SYNC_NODE_ID=cpanel`, `SYNC_IS_LOCAL=false`. The control panel and the scheduler are
**inert** on cpanel because `SYNC_IS_LOCAL=false`.

A **second DB connection** `cpanel` is added to `config/database.php` (the local `.env` points the default
`mysql` connection at the *local* DB, and the `cpanel` connection at `101.50.1.78`). This is the key change
from today, where the default connection points straight at CPanel.

---

## 4. Table classification (the heart of the design)

Every table is assigned exactly one **sync policy**. Driven by a config map (`config/sync.php`, §8).

### 4.1 `BIDIRECTIONAL` — true read/write master–master on both nodes
Per‑user, append‑mostly, no money, negligible same‑row contention. Conflicts resolved by **last‑write‑wins
on `updated_at`**, tie‑break by `SYNC_NODE_ID`.

- `favorites` — merge by presence; un‑favorite handled via tombstone (§6)
- `tickets` — support tickets (insert + status)
- `gacha_histories` — append‑only log
- `cart_items` — bidirectional (synced, per decision; a customer's cart follows them across nodes)
- `referrals` — the linkage record only (reward grants are restricted, below)
- `users` **profile fields only** — `name, email, password, role, referral_code, email_verified_at,
  google_id, remember_token, referrals_last_seen_at`. **Excludes `points_balance`** (see restricted).

### 4.2 `CPANEL_AUTHORITY` — CPanel is the system of record; local **customers can still perform these**
Financial / inventory / monotonic state. **Decision: local customers get the full customer experience**
(checkout, buy keys, spend points, gacha, point‑shop). To stay safe, the local app does **not** write these
rows into its own DB — instead the operation is executed **synchronously against CPanel in real time**
(§7.4), so CPanel performs the one authoritative allocation. The resulting rows flow back to local via the
normal 1‑minute PULL. This is what prevents double‑selling a `product_key` or corrupting `points_balance`
while two storefronts run.

> Connectivity note: these interactive operations require a live local→CPanel link **at the moment of the
> action** (which local already has). If the link is down, financial actions are unavailable on local (you
> cannot safely sell shared stock while partitioned), but browsing, cart, favorites, and tickets keep
> working and sync later.

- `orders`, `order_details` — status comes from the Midtrans webhook (CPanel only)
- `product_keys` — digital inventory allocation (the double‑sell table)
- `gacha_payments`, `point_shop_purchases` — webhook/financial
- `user_discounts`, `user_gacha_states`, `user_active_boosters` — consumable / pity / monotonic state
- `referral_rewards` — reward grants (financial)
- `topup_credentials` — per‑order top‑up account data (tied to `order_details`), created at checkout
- `users.points_balance` — single‑column carve‑out: this field is **CPanel‑authoritative** even though the
  rest of the `users` row is bidirectional (handled by a column‑level rule, §7.5)

### 4.3 `ADMIN_AUTHORITY` — one‑way admin‑node → replica
Per your "one admin only" decision. Whichever node runs the admin panel is the source; the other receives.
(Default assumption: **CPanel is the admin authority**; flip via config if admin is run locally.)

- `products`, `categories`, `subcategories`, `product_discounts`, `discount_types`
- `faqs`, `news`
- `gacha_pools`, `gacha_boosters`, `gacha_icons`, `gacha_rarity_chances`
- `point_shop_items`, `referral_configs`, `referral_tiers`

### 4.4 `NEVER` — excluded from sync (per‑server state)
- `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `migrations`, `sessions`,
  `password_reset_tokens`
- `sync_changes`, `sync_state` (the sync engine's own tables, §6)

---

## 5. Schema strategy (ULID, timestamps, tombstones)

We **add** columns; we do **not** convert primary keys. Existing `bigint` PKs and all foreign keys are left
untouched (lowest risk on 1,560 `product_keys` / 505 `order_details` rows with live FK graphs). The ULID is a
**global sync key**, matched across nodes; FKs continue to use local bigint ids and are **translated** during
apply via the id‑map (§7.3).

For every **synced** table (buckets 4.1–4.3) add:

| Column        | Type                         | Purpose                                                    |
|---------------|------------------------------|------------------------------------------------------------|
| `ulid`        | `CHAR(26)` `UNIQUE` `NOT NULL`| global identity of the logical row across both nodes       |
| `updated_at`  | `TIMESTAMP NULL`             | last‑write‑wins clock (add where missing — see list below) |
| `deleted_at`  | `TIMESTAMP NULL`             | soft delete = tombstone so deletes propagate               |

**Tables missing `updated_at` today** (must be added): `orders`, `order_details`*, `products`, `categories`,
`subcategories`, `discount_types`, `faqs`, `product_keys`, `gacha_pools`, `gacha_payments`,
`point_shop_purchases`, `referrals`, `referral_rewards`, `user_discounts`, `topup_credentials`,
`gacha_histories`. (*`order_details` already has it.) Confirm per‑table during implementation.

**Soft deletes:** add `deleted_at` + Laravel `SoftDeletes` to every synced model. Today only `news` has it.
Without this, hard deletes are invisible to a watermark scan and silently diverge. Any genuinely
hard‑deleted row is instead recorded as a `delete` event in the change‑log (§6).

ULID generation: `Str::ulid()` on insert (model `creating` hook). ULIDs are **time‑sortable**, which also
gives us a cheap, monotonic ordering for change application.

**Bigint id consistency (refinement for the authority connection, §7.4).** ULIDs are the sync match key,
but the authority‑connection model (a local financial request runs against CPanel) also needs **bigint ids
to be identical across nodes** — because relationships and `Auth::user()` resolve by bigint id, not ULID.
Two mechanisms guarantee this:
- **Partitioned id‑space** — each node sets `auto_increment_increment=2` with a per‑node `auto_increment_offset`
  (local = odd, CPanel = even) as **SESSION** vars on connect (no privileges needed). Concurrent inserts can
  never collide. Set in `config/sync.php` + applied via a `ConnectionEstablished` listener.
- **Preserve id on sync apply** — the engine inserts pulled/pushed rows with their **source id** (safe now
  that the spaces are disjoint), so a row has the **same id on both nodes**. Changes are attributed to the
  node that **owns the connection** they're written on, so authority writes (local→CPanel) come back via PULL.

**Backfill:** a one‑off command assigns a ULID to every existing row on **CPanel first** (the authority),
then the local DB is seeded from CPanel so both sides share the same ULIDs before live sync starts (§11).

---

## 6. Change‑log (outbox) + state tables

Two engine‑owned tables (policy `NEVER`, never synced):

**`sync_changes`** — an append‑only outbox written by an Eloquent observer on every create/update/delete of a
synced model:

| Column        | Notes                                                              |
|---------------|-------------------------------------------------------------------|
| `id`          | local bigint PK                                                    |
| `node_id`     | origin node (`local` / `cpanel`)                                   |
| `table_name`  | e.g. `favorites`                                                   |
| `row_ulid`    | the affected row's ULID                                            |
| `op`          | `insert` / `update` / `delete`                                     |
| `payload`     | JSON snapshot of synced columns (FK columns stored as **ULIDs**)  |
| `row_version` | per‑row counter, bumped each change (conflict detection)          |
| `occurred_at` | source timestamp (from `updated_at`)                              |
| `applied`     | bool — has this been shipped to the peer yet                      |

**`sync_state`** — one row per `(direction, table)` holding the **watermark**: the last `occurred_at` /
`sync_changes.id` successfully pulled from / pushed to the peer, plus last‑run status and error.

Why an outbox and not a raw `updated_at` scan? It captures **deletes**, preserves **operation order**, makes
**FK translation** deterministic, and makes the engine **idempotent and resumable** after a failed minute.

---

## 7. Sync engine

A single artisan command, scheduled every minute on the **local** node only.

```
php artisan sync:run            # one full cycle: pull, then push
php artisan sync:run --dry-run  # log what would change, write nothing
php artisan sync:run --table=favorites   # scope to one table (debug)
```

Registered in `routes/console.php`:
```php
Schedule::command('sync:run')
    ->everyMinute()
    ->withoutOverlapping()                  // a slow run never stacks on the next
    ->when(fn () => config('sync.enabled') && config('sync.is_local'));
```
`withoutOverlapping()` + a cache lock guarantees only one cycle runs at a time. The `when()` guard makes the
schedule a **no‑op on CPanel** and whenever the operator has paused sync (§9).

### 7.1 PULL (CPanel → local)
1. Read `sync_state` watermark for each table.
2. From the `cpanel` connection, read `sync_changes` where `id > watermark AND node_id = 'cpanel'`, ordered
   by `id`.
3. Apply each change locally (§7.3). Advance watermark transactionally per batch.

### 7.2 PUSH (local → CPanel)
1. Read local `sync_changes` where `applied = false AND node_id = 'local'`.
2. For each change whose policy permits a local→cpanel write (i.e. `BIDIRECTIONAL`, or `ADMIN_AUTHORITY`
   when local **is** the admin node), apply it on the `cpanel` connection and mark `applied = true`.
3. `CPANEL_AUTHORITY` rows are **never pushed** as direct writes — they go through the intent queue (§7.4).

### 7.3 Applying a change (idempotent upsert + FK translation)
- Match the target row by **`ulid`** (not bigint id). Insert if absent, update if present.
- **FK translation:** payload foreign keys are ULIDs; before writing, resolve each to the **local** bigint id
  via the target table (e.g. `orders.user_id` ULID → local `users.id`). If the referenced row hasn't arrived
  yet, **defer** the change and retry next cycle (dependency ordering).
- Apply observers are **suppressed** during sync writes (a `Sync::applying()` flag) so applying a pulled
  change does not generate a new outbox entry → no echo/loop.
- `delete` op → soft delete locally (set `deleted_at`).

### 7.4 Direct‑to‑authority execution for restricted operations (local customers, full capability)
When a customer on the **local** node performs a `CPANEL_AUTHORITY` action (checkout, key purchase, points
spend, gacha pull, point‑shop buy), the local app routes that operation to **CPanel as the authority**,
synchronously — separate from the 1‑minute replication loop, because these are interactive:

- **Checkout / orders / `product_keys` / payments.** The order is created **on the CPanel DB** and the
  Midtrans Snap transaction is issued with **CPanel's** server key and **CPanel's** notification/callback URL
  (`/midtrans/callback`). Therefore the payment webhook lands on CPanel (where it already works), CPanel does
  the authoritative `product_keys` allocation, and the `orders` / `order_details` / `product_keys` rows
  replicate back to local on the next PULL. The local browser talks to Midtrans directly (the client key is
  public and origin‑agnostic), so the customer's payment UX is identical.
- **Points / gacha / point‑shop.** The debit/credit and state mutation (`points_balance`,
  `user_gacha_states`, `point_shop_purchases`, `gacha_payments`) are executed against CPanel in the same
  request, then PULLed back. CPanel remains the single place `points_balance` ever changes.

Implementation shape: a thin **`SyncAuthorityGateway`** that the relevant controllers/services call when
`config('sync.is_local')` is true — it performs the write/allocation over the `cpanel` connection (or an
internal HTTP call to CPanel) inside the same user request, and returns the authoritative result to render.
On CPanel itself the gateway is a pass‑through (it just runs locally, since CPanel *is* the authority).

This is the mechanism that gives local customers the **full** customer experience while keeping
`product_keys` and `points_balance` single‑authority — no double‑sell, no balance races.

### 7.5 Conflict resolution rules
| Policy             | Rule                                                                              |
|--------------------|-----------------------------------------------------------------------------------|
| `BIDIRECTIONAL`    | Last‑write‑wins by `updated_at`; tie → higher `SYNC_NODE_ID` wins; `delete` wins over older `update` |
| `users` row        | Row is LWW, **except** `points_balance` which always takes the CPanel value (column‑level override) |
| `CPANEL_AUTHORITY` | CPanel value always wins; local never writes these rows locally — interactive ops execute against CPanel (§7.4) and replicate back |
| `ADMIN_AUTHORITY`  | Admin node value always wins; replica never pushes                                |

All overwrites that discard a losing change are written to a **`sync_conflicts`** log for audit.

---

## 8. Configuration — `config/sync.php`

```php
return [
    'enabled'   => env('SYNC_ENABLED', false),
    'is_local'  => env('SYNC_IS_LOCAL', false),
    'node_id'   => env('SYNC_NODE_ID', 'cpanel'),
    'remote'    => env('SYNC_REMOTE_CONNECTION', 'cpanel'),
    'admin_node'=> env('SYNC_ADMIN_NODE', 'cpanel'),  // who owns ADMIN_AUTHORITY tables

    'tables' => [
        'favorites'      => ['policy' => 'bidirectional', 'fks' => ['user_id' => 'users', 'product_id' => 'products']],
        'tickets'        => ['policy' => 'bidirectional', 'fks' => ['user_id' => 'users']],
        'gacha_histories'=> ['policy' => 'bidirectional', 'fks' => ['user_id' => 'users']],
        'cart_items'     => ['policy' => 'bidirectional', 'fks' => ['user_id' => 'users', 'product_id' => 'products']],
        'referrals'      => ['policy' => 'bidirectional', 'fks' => [/* ... */]],
        'users'          => ['policy' => 'bidirectional', 'column_overrides' => ['points_balance' => 'cpanel']],

        'orders'         => ['policy' => 'cpanel_authority'],
        'order_details'  => ['policy' => 'cpanel_authority'],
        'product_keys'   => ['policy' => 'cpanel_authority'],
        'gacha_payments' => ['policy' => 'cpanel_authority'],
        // ...restricted tables...

        'products'       => ['policy' => 'admin_authority'],
        'categories'     => ['policy' => 'admin_authority'],
        // ...catalog tables...

        // everything not listed = NEVER
    ],
];
```
The classification lives in **one file**, so policy changes need no engine edits.

---

## 9. Local‑only control panel (Pause / Resume / Sync Now)

A small operator UI, reachable **only on the local node**.

**Security gating (defense in depth):**
1. New middleware `EnsureLocalNode` → `abort(404)` unless `config('sync.is_local') === true`. On CPanel the
   routes simply don't exist (404), so the buttons can never be hit in production.
2. Routes also sit behind the existing `auth` + `EnsureUserIsAdmin` middleware.
3. (Optional) additionally require the request IP to be loopback/private.

**Routes** (`routes/web.php`, mirroring the existing `admin` group):
```php
Route::prefix('admin/sync')
    ->middleware(['auth', EnsureUserIsAdmin::class, EnsureLocalNode::class])
    ->group(function () {
        Route::get('/',        [Admin\SyncController::class, 'index'])->name('admin.sync');       // status page
        Route::post('/pause',  [Admin\SyncController::class, 'pause'])->name('admin.sync.pause');
        Route::post('/resume', [Admin\SyncController::class, 'resume'])->name('admin.sync.resume');
        Route::post('/now',    [Admin\SyncController::class, 'now'])->name('admin.sync.now');      // Sync Now
    });
```

**Behavior:**
- **Pause / Resume** flips a persisted flag (`sync_state` row or `cache()->forever('sync.paused', …)`). The
  scheduled `sync:run` checks this flag and no‑ops while paused — the cron keeps firing, but does nothing.
  This is separate from the `SYNC_ENABLED` env kill‑switch (which fully disables scheduling).
- **Sync Now** dispatches `sync:run` immediately (queued job or synchronous run with a lock), so the operator
  doesn't wait up to a minute. Disabled in the UI while a cycle is already running.
- **Status page** shows: current state (running / paused / idle), last successful pull & push time per
  direction, watermark lag, count of unshipped local changes, and the latest entries from `sync_conflicts`.

The panel is **invisible and unroutable on CPanel** by construction (`EnsureLocalNode` + `SYNC_IS_LOCAL`).

---

## 10. Scheduling & execution
- Local: a real OS cron entry runs Laravel's scheduler every minute:
  `* * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1`
  (or `php artisan schedule:work` during development).
- `sync:run` is `withoutOverlapping()`; a stuck cycle auto‑expires its lock after N minutes.
- CPanel needs **no cron** for sync.

---

## 11. Migration & cutover plan (phased)

**Phase 0 — Prep (no behavior change)**
- [ ] Add `cpanel` connection to `config/database.php`; repoint local default `mysql` at the local DB.
- [ ] Stand up the local MySQL/MariaDB (matching charset/collation: `utf8mb4` / InnoDB).

**Phase 1 — Schema additions (both DBs)**
- [ ] Migrations: add `ulid` (unique), `updated_at` (where missing), `deleted_at` to all synced tables.
- [ ] Add `SoftDeletes` + `ulid` boot hook to the corresponding models.
- [ ] Create `sync_changes`, `sync_state`, `sync_conflicts`, `sync_intents` tables.

**Phase 2 — Backfill identity (CPanel authoritative)**
- [ ] `php artisan sync:backfill-ulids` on CPanel (assign ULIDs to all existing rows).
- [ ] Snapshot CPanel → seed local DB so **both sides share identical ULIDs** before any live sync.
- [ ] Seed `sync_state` watermarks to "now" so historical rows aren't reprocessed.

**Phase 3 — Engine, dry run**
- [ ] Implement observers + `sync:run`. Run `sync:run --dry-run` and inspect logs for several cycles.
- [ ] Verify FK translation, dependency deferral, and loop suppression on a handful of test rows.

**Phase 4 — Go live, bidirectional tables only**
- [ ] Enable `SYNC_ENABLED=true` on local with **only `bidirectional`** tables active.
- [ ] Watch `sync_conflicts` and watermark lag for a day.

**Phase 5 — Restricted + admin tables**
- [ ] Enable `cpanel_authority` (one‑way replication into local) and `admin_authority` flows.
- [ ] Wire the `SyncAuthorityGateway` (§7.4) into checkout, gacha, points, and point‑shop so local customers
      transact against CPanel; verify keys/points are allocated exactly once and replicate back.

**Phase 6 — Operate**
- [ ] Control panel live on local; document Pause / Sync Now for operators.

Each phase is independently revertible (additive columns + a feature flag; no destructive changes).

---

## 12. Failure handling & observability
- **Idempotent apply** (upsert by ULID) ⇒ replaying a batch is safe after a crash.
- **Per‑table watermarks** ⇒ one bad table doesn't block the rest; resumes mid‑stream.
- **Retry with deferral** for FK‑dependency‑not‑yet‑present.
- **CPanel unreachable** (laptop offline / network down): cycle logs a warning and no‑ops; backlog drains on
  reconnect (the whole point of the outbox).
- **Logging:** dedicated `sync` log channel; `sync_conflicts` table for every discarded change; status page
  surfaces lag and last error.
- **Alerting (optional):** if watermark lag exceeds a threshold, flag it on the status page.

## 13. Security
- DB credentials stay in `.env` (already the case). Consider a **read‑mostly** dedicated DB user for the
  local→cpanel connection scoped to the synced tables.
- Control‑panel routes are quadruple‑gated: 404 off‑local, `auth`, admin, (optional) loopback‑IP.
- `points_balance`, `product_keys`, `orders` are never writable from local ⇒ no remote‑initiated financial
  mutation path.
- Payment webhooks remain CPanel‑only; nothing in this design exposes them to the local node.

## 14. Testing
- Unit: conflict‑resolution matrix (§7.5), FK translation, ULID assignment, loop suppression.
- Feature: simulate concurrent edits on both connections (using the test sqlite/MySQL) and assert convergence.
- Scenario drills: local offline 1h then reconnect; delete on A while update on B; un‑favorite race;
  attempted local checkout against `product_keys` (must block or queue, never double‑allocate).

---

## 15. Decisions (resolved) + the one consequence to acknowledge

**Resolved:**
1. **Admin authority node = CPanel.** `SYNC_ADMIN_NODE=cpanel`. Catalog/config edits happen on CPanel and
   replicate one‑way to local.
2. **`cart_items` = synced** (bidirectional). Carts follow the customer across nodes.
3. **Local customers get the full experience.** Restricted/financial operations are **not** blocked on
   local — they execute synchronously against CPanel via the `SyncAuthorityGateway` (§7.4) and replicate
   back. Single‑authority preserved, so no double‑sell / balance races.
4. **Local DB engine = MySQL** (`utf8mb4`/InnoDB). Implementation step: read CPanel's exact collation and
   match it locally before the seed (we'll capture it during Phase 0).
5. **`noinv` (decided for you):** invoice numbers are minted **only on CPanel**. Because every checkout —
   including local‑initiated ones — creates its order on CPanel (§7.4), the local node never generates an
   invoice number, so collisions are impossible by construction. The local order code path will call the
   gateway rather than minting `noinv` itself.

**One consequence to acknowledge (not a blocker):**
- Financial actions on the local node require the local→CPanel link to be **up at that moment**. If local is
  cut off from CPanel, customers there can still browse, manage cart, favorites, and tickets (these sync
  later), but checkout / points / gacha are paused until the link returns. This is the correct trade‑off —
  selling shared inventory while partitioned from the authority is exactly what causes double‑sell.

---

## 16. Why this is the correct shape (summary)
- Native replication is physically unavailable on this hosting → logical sync is the only option.
- NAT + single‑URL payment webhooks → money/inventory **must** be CPanel‑authoritative.
- ULID sync‑keys (additive, not a PK rewrite) → global identity with minimal migration risk.
- Outbox + watermarks → captures deletes, preserves order, survives the laptop being offline.
- Local‑only Pause / Sync Now → operator control where it's needed, impossible to trigger in production.
