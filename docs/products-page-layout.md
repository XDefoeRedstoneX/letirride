# Ridly — Products Page Refactor Layout

> **Status:** PROPOSAL / wireframe only. No code written yet.
> **Goal:** Replace the nested category → subcategory accordion (which collapses
> products out of view and makes the page feel empty) with a flat,
> browse-first marketplace layout in the spirit of bahterastore.id / itemku.com,
> while keeping Ridly's dark-navy + gold pixel/retro identity.

---

## 1. The problem with the current page

```
Category accordion (gold header bar)
   └─ Subcategory accordion  ← COLLAPSED by default
          └─ product grid    ← hidden until 2 clicks deep
```

- Two levels of toggling before a single product is visible.
- Subcategories collapsed on load → big empty gold/navy bars, no merchandise.
- No "browse by brand" entry point, which is the signature of every voucher
  marketplace and the thing that fills the screen with color + intent.

---

## 2. Target model (bahtera / itemku)

A voucher marketplace is browsed **brand-first**: the shopper thinks
"I want *Steam*" or "I want *Netflix*", taps the brand, and sees its variants.
So the page leads with a dense grid of brand tiles, then shows products flat.

Two browsing modes coexist:

- **Default (no brand selected):** show every product in one flat grid, plus
  the brand tiles up top as shortcuts.
- **Brand selected:** grid filters to that brand; a clear "active brand" chip
  with an ✕ to reset.

No accordions anywhere.

---

## 3. Full-page wireframe — DESKTOP (≥1200px)

```
╔══════════════════════════════════════════════════════════════════════╗
║                        [ pixel-framed HERO carousel ]                  ║   ← unchanged (news/1..4.jpg)
║                              ▣ RIDLY NEWS ▣                             ║
╚══════════════════════════════════════════════════════════════════════╝

   WELCOME, <user>  /  DIGITAL PRODUCTS                                       ← page-header (unchanged)
   WHAT ARE YOU LOOKING FOR TODAY?
   ·──────────────────────────────────────────────────────────────────·     ← px-divider

   ┌────────────────────────────────────────────────────────────────────┐
   │ 🔍  SEARCH PRODUCTS...                                              │   ← search-wrap (unchanged)
   └────────────────────────────────────────────────────────────────────┘

   [ ALL ] [ GAMING ] [ ENTERTAINMENT ] [ MOBILE TOP-UP ] [ SOFTWARE ] →     ← category pills = px-tab
                                                                              (filters BOTH brands + grid)

   ┌─ ▣ BROWSE BY BRAND ──────────────────────────────────── 12 BRANDS ─┐
   │                                                                      │
   │   ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐        │
   │   │ LOGO │  │ LOGO │  │ LOGO │  │ LOGO │  │ LOGO │  │ LOGO │        │   ← BRAND TILE GRID
   │   │      │  │      │  │      │  │      │  │      │  │      │        │     6 cols desktop
   │   │STEAM │  │NETFLX│  │SPOTFY│  │DISCRD│  │ G-PLY│  │ YT   │        │
   │   └──────┘  └──────┘  └──────┘  └──────┘  └──────┘  └──────┘        │
   │   ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐        │
   │   │ ...  │  │ ...  │  │ ...  │  │ ...  │  │ ...  │  │ +N   │        │
   │   └──────┘  └──────┘  └──────┘  └──────┘  └──────┘  └──────┘        │
   └──────────────────────────────────────────────────────────────────┘

   ┌─ ▣ ALL PRODUCTS ───────────────────  showing 24 · sorted: popular ▾ ─┐  ← section bar (sort optional)
   │                                                                       │
   │  ┌────────┐  ┌────────┐  ┌────────┐  ┌────────┐                      │
   │  │  IMG   │  │  IMG   │  │  IMG   │  │  IMG   │                      │   ← FLAT PRODUCT GRID
   │  │ [cat]  │  │ [cat]⚡│  │ [cat]  │  │ [cat]  │                      │     4 cols desktop
   │  ├────────┤  ├────────┤  ├────────┤  ├────────┤                      │     (reuses product-card)
   │  │ name   │  │ name   │  │ name   │  │ name   │                      │
   │  │Rp 50k  │  │Rp 25k  │  │Rp 90k  │  │Rp 12k  │                      │
   │  │5 stock │  │TOP-UP  │  │SOLD OUT│  │2 stock │                      │
   │  │ ♡  BUY │  │ ♡  BUY │  │ ♡ SOLD │  │ ♡  BUY │                      │
   │  └────────┘  └────────┘  └────────┘  └────────┘                      │
   │  ┌────────┐  ┌────────┐  ┌────────┐  ┌────────┐                      │
   │  │  ...   │  │  ...   │  │  ...   │  │  ...   │                      │
   │  └────────┘  └────────┘  └────────┘  └────────┘                      │
   └───────────────────────────────────────────────────────────────────┘
```

### When a brand tile is clicked

```
   [ ALL ] [ GAMING ] ...                                                     ← pills stay
   ┌─ ▣ BROWSE BY BRAND ─────────────────────────────────────────────────┐
   │   (selected tile gets gold border + glow; others dim slightly)        │
   └───────────────────────────────────────────────────────────────────┘

   ┌─ ▣ STEAM WALLET  ✕ clear ──────────────────────────  4 products ──────┐   ← active-brand bar
   │   ┌────────┐  ┌────────┐  ┌────────┐  ┌────────┐                      │
   │   │ IDR 12k│  │ IDR 60k│  │IDR 120k│  │IDR 250k│                      │   ← only this brand's items
   │   └────────┘  └────────┘  └────────┘  └────────┘                      │
   └───────────────────────────────────────────────────────────────────┘
```

---

## 4. Wireframe — MOBILE (≤640px)

```
┌─────────────────────────┐
│   [ HERO carousel ]     │
│       ▣ NEWS ▣          │
├─────────────────────────┤
│ WELCOME, <user>         │
│ ·─────────────────────· │
│ ┌─────────────────────┐ │
│ │🔍 SEARCH...         │ │
│ └─────────────────────┘ │
│ [ALL][GAMING][STREAM]→  │  ← pills scroll-x
│                         │
│ ▣ BROWSE BY BRAND       │
│ ┌────┐┌────┐┌────┐      │  ← brand tiles: 3 cols, scroll-x row OR wrap
│ │STM ││NFX ││SPT │      │
│ └────┘└────┘└────┘      │
│ ┌────┐┌────┐┌────┐      │
│ │DSC ││GPL ││YT  │      │
│ └────┘└────┘└────┘      │
│                         │
│ ▣ ALL PRODUCTS          │
│ ┌─────────┐┌─────────┐  │  ← product grid: 2 cols
│ │  card   ││  card   │  │
│ └─────────┘└─────────┘  │
│ ┌─────────┐┌─────────┐  │
│ │  card   ││  card   │  │
│ └─────────┘└─────────┘  │
└─────────────────────────┘
```

---

## 5. Component anatomy

### 5.1 Brand tile (NEW)
A square card representing one **brand** = one subcategory grouping
(e.g. "Steam Wallet", "Netflix", "Spotify"). This is the visual filler.

```
┌─────────────┐
│   ╭─────╮   │   - square (aspect-ratio 1/1) or 4/5
│   │ IMG │   │   - brand logo, object-fit: contain, dark inner bg (#06111f)
│   ╰─────╯   │   - image-rendering: pixelated (matches card-img)
│  STEAM      │   - name: Geist sans, 600, small
│  6 items    │   - count: text-dim, tiny
└─────────────┘
  default: 3px var(--dark-line) border, 4px hard shadow
  hover:   gold border + translate(-2px,-2px)  (reuse px-border-card behavior)
  active:  gold border + var(--gold-glow) ring
```

- **Grid:** `repeat(auto-fill, minmax(120px, 1fr))` →
  6/desktop, 4/tablet, 3/mobile. Fills width regardless of brand count → no empty gaps.
- **Image source:** reuse the existing `/products/*.png` (steam-wallet, netflix,
  spotify, discord, google-play, youtube). Per your instruction, every brand
  tile image uses `alt="steam-wallet.png"`. Filename can be anything.
- **Fallback:** brand with no dedicated logo → `steam-wallet.png`.

### 5.2 Category pills (KEEP, restyle)
- Reuse `.px-tab` / `.px-tab-active` / `.px-tab-inactive`.
- Clicking a pill filters **both** the brand grid and the product grid.
- "ALL" resets.

### 5.3 Section bar (NEW, replaces gold accordion header)
- Slim gold-accented title row: `▣ ALL PRODUCTS` left, count (and optional
  sort dropdown) right. **Not** a toggle — always open.
- Reuses the pixel `▣` motif + `--gold` underline already in the design.

### 5.4 Product card (KEEP as-is)
- The current `.product-card` is good — image, badges, title, price, stock
  line, ♡ + BUY. No structural change. Just lives in one flat grid now
  instead of nested under two accordions.

### 5.5 Buy modal (KEEP as-is)
- No change.

---

## 6. Data / mapping notes — CONFIRMED against the seeder

The DB makes this mapping unambiguous: **every subcategory is already a brand.**

- **Pills = the 5 categories:** `Games`, `Wallet Top-Ups`, `In-Game Currency`,
  `Gift Cards`, `Subscriptions` (+ `ALL`).
- **Brand tiles = the 17 subcategories**, grouped under their category:

  | Category          | Brands (subcategories)                                          | # |
  |-------------------|------------------------------------------------------------------|---|
  | Games             | Capcom                                                           | 1 |
  | Wallet Top-Ups    | Steam · PlayStation · Nintendo                                   | 3 |
  | In-Game Currency  | Riot/Valorant · Mobile Legends · Fortnite                       | 3 |
  | Gift Cards        | Roblox                                                           | 1 |
  | Subscriptions     | Genshin · Netflix · Spotify · Discord · Xbox · YouTube · Canva · OpenAI · Adobe | 9 |

- **Click a brand → see its variants** (exactly the itemku model): e.g. Steam →
  *Wallet Rp150k / Rp750k*; Fortnite → *1000 / 2500 / 5000 / 12500 V-Bucks*;
  Netflix → *1 Month HD*.
- **Brand → image:** only 6 logos exist today (`steam-wallet`, `netflix`,
  `spotify`, `discord`, `google-play`, `youtube`); most products fall back to
  `steam-wallet.png`. Proposal: add one logo per brand under e.g.
  `/public/brands/<name>.png` (filenames free, your call). Per your rule, **every
  brand-tile `<img>` uses `alt="steam-wallet.png"`**, and any missing logo falls
  back to `steam-wallet.png`. Until you supply logos, all tiles render the
  fallback — layout still holds.

### ⚠️ Exception: the "Games" category (Game Keys) is NOT brand-tiled

A game key (`Pragmata`, `Resident Evil Requiem` under Games → Capcom) is a
**one-off title**, not a denomination of a brand. So the subcategory→brand
treatment **does not apply to the `Games` category**:

- **No brand tiles** are generated for `Games`. The `Capcom` subcategory is not
  surfaced as a clickable brand.
- Game titles render **directly as product cards** in the flat product grid
  (each game = its own card), the same way they look today.
- The brand grid (`▣ BROWSE BY BRAND`) is built only from subcategories of the
  other four categories (Wallet Top-Ups, In-Game Currency, Gift Cards,
  Subscriptions).
- When the `GAMES` pill is selected: brand grid hides, grid shows the game-key
  cards straight away.

Implementation hook: when iterating subcategories for the brand grid, skip any
whose category is `Games` (and route those products to the direct grid instead).
- **Controller reuse:** `subcategoriesForCategory()` already returns the brand
  list; `productsForSub()` already returns a brand's variants. New code mostly
  reuses these. The `expandedCategories` / `expandedSubcategories` state and the
  `toggleCategory` / `toggleSubcategory` methods get deleted, replaced by a single
  `selectedBrand` (+ existing `activeFilter`).

---

## 7. What changes vs. what stays

| Element                     | Action   |
|-----------------------------|----------|
| Hero carousel               | KEEP     |
| Page header + divider       | KEEP     |
| Search bar                  | KEEP     |
| Category pills (`.px-tab`)  | KEEP, now also filter brand grid |
| Category accordion header   | REMOVE   |
| Subcategory accordion       | REMOVE   |
| `expandedCategories` state  | REMOVE   |
| **Brand tile grid**         | **ADD**  |
| **Active-brand filter bar** | **ADD**  |
| **Flat product grid**       | **ADD** (product-card reused) |
| Product card markup         | KEEP     |
| Buy modal                   | KEEP     |
| Empty state                 | KEEP     |

---

## 8. Decisions — my recommendation (data-grounded)

1. **Brand granularity** → **subcategory**, *except the `Games` category*, whose
   titles are shown as direct product cards (no brand tiles). Confirmed — see §6
   and the Games exception. So 16 subcategories become brand tiles; Capcom does
   not.
2. **Default product grid (no brand selected):** group the flat grid under small
   **non-collapsible** category sub-headings (`🎮 GAMES`, `📱 WALLET TOP-UPS`, …)
   so the long list stays scannable but nothing is ever hidden. Picking a brand
   collapses it to that brand's variants only.
3. **Brand tile shape** → **square (1:1)**, matching the existing `card-img-wrap`
   ratio and pixel look. (itemku uses ~4:5; happy to switch.)
4. **Sort control** → **skip for v1.** With ≤9 items per category it adds little;
   easy to add later.

### Still genuinely need your call

- **(a)** OK to add brand logos under `/public/brands/`? If you'd rather I derive
  each brand's tile image from one of its product images instead (keeping only
  the 6 existing files), say so — tiles would mostly show the `steam-wallet`
  fallback until real logos land.
- **(b)** Default grid grouped by category sub-headings (rec. above), or one
  single continuous grid with no headings at all?

> Green-light §8 (a)/(b) and I'll implement against the existing `app.css` tokens
> + Alpine controller — no new dependencies.
```
