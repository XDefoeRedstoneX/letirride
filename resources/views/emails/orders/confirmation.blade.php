<x-mail::message>
# Order Confirmed!

Hi {{ $order->user->name }},

Your payment was successful. Here's a summary of your order.

**Invoice:** {{ $order->display_noinv }}
**Date:** {{ $order->created_at->format('d M Y, H:i') }}

<x-mail::table>
| Product | Qty | Price |
|:--------|:---:|------:|
@foreach ($order->orderDetails as $detail)
| {{ $detail->product->name }} | {{ $detail->quantity }} | Rp {{ number_format($detail->total_price_in_cart, 0, ',', '.') }} |
@endforeach
</x-mail::table>

@if ($order->discount_amount > 0)
**Discount:** -Rp {{ number_format($order->discount_amount, 0, ',', '.') }}
@endif

**Total Paid:** Rp {{ number_format($order->total_price_after_discount, 0, ',', '.') }}

Your product keys (if any) are available in your order history. You can view the full details below.

<x-mail::button :url="$viewUrl">
View Order
</x-mail::button>

Thanks for shopping with us!<br>
{{ config('app.name') }}
</x-mail::message>
