<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Order;

// Check user 11 (most active - has the latest orders)
echo "=== User 11 Orders ===" . PHP_EOL;
$orders = Order::where('user_id', 11)->orderByDesc('id')->get();
foreach ($orders as $o) {
    echo "  ID:{$o->id} INV:{$o->noinv} Status:{$o->status} Total:{$o->total_price_after_discount} Created:{$o->created_at}" . PHP_EOL;
}

// The pending check logic
echo PHP_EOL . "=== existingPending check for user 11 ===" . PHP_EOL;
$existingPending = Order::where('user_id', 11)
    ->where('status', 'pending')
    ->latest()
    ->first();

if ($existingPending) {
    echo "FOUND: ID:{$existingPending->id} INV:{$existingPending->noinv}" . PHP_EOL;
} else {
    echo "NOT FOUND (no pending orders for user 11)" . PHP_EOL;
}

// Check the 'latest()' scope - it uses created_at by default, but model has UPDATED_AT = null
echo PHP_EOL . "=== Testing latest() scope ===" . PHP_EOL;
echo "latest() defaults to 'created_at' column" . PHP_EOL;
$test = Order::where('user_id', 11)->where('status', 'pending')->latest()->toRawSql();
echo "SQL: {$test}" . PHP_EOL;

// Check if the callback webhook can actually reach local dev
echo PHP_EOL . "=== Midtrans Config ===" . PHP_EOL;
echo "Server Key: " . config('midtrans.server_key') . PHP_EOL;
echo "Is Production: " . var_export(config('midtrans.is_production'), true) . PHP_EOL;
