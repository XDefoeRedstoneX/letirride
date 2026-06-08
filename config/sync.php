<?php

/*
|--------------------------------------------------------------------------
| Database Synchronization (Local <-> CPanel logical sync)
|--------------------------------------------------------------------------
|
| See docs/database-sync-plan.md for the full design. This file is the single
| source of truth for which tables sync and how. The engine reads it; changing
| policy here needs no engine edits.
|
| Policies:
|   bidirectional    - read/write on both nodes; last-write-wins on updated_at
|   cpanel_authority - CPanel is the system of record; local never writes these
|                      rows locally (interactive ops execute against CPanel)
|   admin_authority  - one-way from the admin node (see admin_node) to the replica
|   (unlisted table) - never synced
|
*/

return [

    // Is the sync engine allowed to run at all (master kill-switch / scheduler guard).
    'enabled' => env('SYNC_ENABLED', false),

    // True only on the local node. Gates the scheduler and the control panel.
    'is_local' => env('SYNC_IS_LOCAL', false),

    // Identity of THIS node. Stamped on every change; used for tie-breaks.
    'node_id' => env('SYNC_NODE_ID', 'cpanel'),

    // The Laravel DB connection name pointing at the remote peer (the CPanel DB).
    'remote' => env('SYNC_REMOTE_CONNECTION', 'cpanel'),

    // Which node owns admin_authority (catalog/config) tables.
    'admin_node' => env('SYNC_ADMIN_NODE', 'cpanel'),

    // The peer node's identity (the DB reachable via the 'remote' connection).
    'remote_node_id' => env('SYNC_REMOTE_NODE_ID', 'cpanel'),

    // Auto-increment id-space partition per node so concurrent inserts on
    // bidirectional tables never collide and ids stay identical across nodes
    // (applied as SESSION vars on connect — no special privileges needed).
    // With increment=2: 'local' mints odd ids, 'cpanel' mints even ids.
    'id_increment' => 2,
    'node_offsets' => [
        'local' => 1,
        'cpanel' => 2,
    ],

    // Pause flag is runtime (toggled by the control panel), stored in cache.
    'pause_cache_key' => 'sync.paused',

    /*
    |--------------------------------------------------------------------------
    | Per-table policy map
    |--------------------------------------------------------------------------
    | 'fks'              => ['column' => 'referenced_table', ...]  FK columns whose
    |                       values are carried as ULIDs and translated on apply.
    | 'column_overrides' => ['column' => 'cpanel']  force a single column to a node's
    |                       value even when the row policy is bidirectional.
    */
    'tables' => [

        // ---- All tables are bidirectional (true master-master) ------------------
        // Both nodes can read AND write. Conflicts resolved by last-write-wins
        // (LWW) on updated_at. This gives a genuine master-master replication
        // story suitable for a Cloud Computing demonstration.

        // -- user / engagement --
        'users' => [
            'policy' => 'bidirectional',
            'fks' => ['referred_by' => 'users'],
        ],
        'favorites' => [
            'policy' => 'bidirectional',
            'fks' => ['user_id' => 'users', 'product_id' => 'products'],
        ],
        'cart_items' => [
            'policy' => 'bidirectional',
            'fks' => ['user_id' => 'users', 'product_id' => 'products'],
        ],
        'tickets' => [
            'policy' => 'bidirectional',
            'fks' => ['user_id' => 'users'],
        ],
        'gacha_histories' => [
            'policy' => 'bidirectional',
            'fks' => ['user_id' => 'users', 'gacha_pool_id' => 'gacha_pools', 'gacha_payment_id' => 'gacha_payments'],
        ],
        'referrals' => [
            'policy' => 'bidirectional',
            'fks' => ['referrer_id' => 'users', 'referred_user_id' => 'users', 'first_purchase_order_id' => 'orders'],
        ],

        // -- orders / financial --
        'orders' => [
            'policy' => 'bidirectional',
            'fks' => ['user_id' => 'users', 'user_discount_id' => 'user_discounts'],
        ],
        'order_details' => [
            'policy' => 'bidirectional',
            'fks' => ['order_id' => 'orders', 'product_id' => 'products'],
        ],
        'product_keys' => [
            'policy' => 'bidirectional',
            'fks' => ['product_id' => 'products', 'order_id' => 'orders', 'reserved_for_order_id' => 'orders'],
        ],
        'gacha_payments' => [
            'policy' => 'bidirectional',
            'fks' => ['user_id' => 'users'],
        ],
        'point_shop_purchases' => [
            'policy' => 'bidirectional',
            'fks' => ['user_id' => 'users', 'point_shop_item_id' => 'point_shop_items'],
        ],
        'user_discounts' => [
            'policy' => 'bidirectional',
            'fks' => ['user_id' => 'users', 'discount_type_id' => 'discount_types', 'order_id' => 'orders'],
        ],
        'user_gacha_states' => [
            'policy' => 'bidirectional',
            'fks' => ['user_id' => 'users'],
        ],
        'user_active_boosters' => [
            'policy' => 'bidirectional',
            'fks' => ['user_id' => 'users', 'gacha_booster_id' => 'gacha_boosters'],
        ],
        'referral_rewards' => [
            'policy' => 'bidirectional',
            'fks' => ['referral_id' => 'referrals', 'tier_id' => 'referral_tiers', 'recipient_id' => 'users', 'order_id' => 'orders'],
        ],
        'topup_credentials' => [
            'policy' => 'bidirectional',
            'fks' => ['order_detail_id' => 'order_details'],
        ],

        // -- catalog / config --
        'categories' => ['policy' => 'bidirectional'],
        'subcategories' => ['policy' => 'bidirectional', 'fks' => ['category_id' => 'categories']],
        'products' => ['policy' => 'bidirectional', 'fks' => ['category_id' => 'categories', 'subcategory_id' => 'subcategories']],
        'product_discounts' => ['policy' => 'bidirectional', 'fks' => ['product_id' => 'products']],
        'discount_types' => ['policy' => 'bidirectional'],
        'faqs' => ['policy' => 'bidirectional'],
        'news' => ['policy' => 'bidirectional'],
        'gacha_pools' => ['policy' => 'bidirectional'],
        'gacha_boosters' => ['policy' => 'bidirectional'],
        'gacha_icons' => ['policy' => 'bidirectional'],
        'gacha_rarity_chances' => ['policy' => 'bidirectional'],
        'point_shop_items' => ['policy' => 'bidirectional'],
        'referral_configs' => ['policy' => 'bidirectional'],
        'referral_tiers' => ['policy' => 'bidirectional'],
    ],
];
