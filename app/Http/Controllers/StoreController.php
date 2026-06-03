<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\News;
use App\Models\Product;
use App\Models\ReferralConfig;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    /**
     * Storefront — browse hub plus the full catalog on a single page.
     * Supports ?group=, ?brand=, ?search= and ?buy= deep links.
     */
    public function showStore()
    {
        return view('pages.products', $this->storeData());
    }

    /**
     * Shared payload: every active product (shaped for the front-end) plus the
     * current user's favorite ids.
     */
    private function storeData(): array
    {
        // Show the referral prompt only to fresh accounts that haven't been
        // referred yet AND haven't bought anything yet — those are the only
        // users still eligible to claim a code.
        $showReferralPrompt = false;
        $referralWelcomePoints = 0;
        if (Auth::check()) {
            $user = Auth::user();
            $isFresh = $user->created_at && $user->created_at->gte(now()->subDays(7));
            if (! $user->referred_by && $isFresh) {
                $hasPaid = $user->orders()->where('status', 'paid')->exists();
                if (! $hasPaid) {
                    $showReferralPrompt = true;
                    $referralWelcomePoints = (int) (ReferralConfig::current()->referee_welcome_points);
                }
            }
        }

        $products = Product::query()
            ->with(['category', 'subcategory', 'discount'])
            ->withCount(['productKeys as available_keys_count' => function ($query) {
                $query->where('status', 'available')
                    ->whereNull('reserved_for_order_id');
            }])
            ->where('is_active', true)
            ->get()
            ->map(function (Product $product) {
                $type = $product->type ?? 'voucher';
                $stock = (int) ($product->available_keys_count ?? 0);
                // direct_topup products don't consume keys, so they're always in stock
                $inStock = $type === 'direct_topup' ? true : $stock > 0;

                $salePrice = $product->discountedPrice();
                $originalPrice = (float) $product->price;
                $d = $product->discount && $product->discount->isCurrentlyActive() ? $product->discount : null;

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $salePrice,
                    'original_price' => $originalPrice,
                    'discount_label' => $d?->label,
                    'discount_pct' => ($d && $d->type === 'percentage') ? (float) $d->value : null,
                    'discount_fixed' => ($d && $d->type === 'fixed') ? (float) $d->value : null,
                    'category' => $product->category?->name ?? 'Other',
                    'subcategory' => $product->subcategory?->name ?? 'Other',
                    'product_type' => $type,
                    'stock' => $stock,
                    'in_stock' => $inStock,
                    'image' => $product->imageUrl(),
                ];
            })
            ->values();

        $favoriteIds = Auth::check()
            ? Favorite::where('user_id', Auth::id())
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->values()
            : collect();

        $newsImages = News::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (News $n) => asset($n->image))
            ->values()
            ->toArray();

        if (empty($newsImages)) {
            $newsImages = collect(glob(public_path('news/*.{jpg,jpeg,png,gif,webp}'), GLOB_BRACE))
                ->map(fn (string $p) => asset('news/'.basename($p)))
                ->values()
                ->toArray();
        }

        return [
            'products' => $products,
            'favoriteIds' => $favoriteIds,
            'newsImages' => $newsImages,
            'showReferralPrompt' => $showReferralPrompt,
            'referralWelcomePoints' => $referralWelcomePoints,
        ];
    }
}
