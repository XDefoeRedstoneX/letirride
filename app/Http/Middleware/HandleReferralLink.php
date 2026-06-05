<?php

namespace App\Http\Middleware;

use App\Services\ReferralService;
use App\Support\ReferralLink;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Turns a `?ref=CODE` share link into an automatic referral claim.
 *
 *  - Logged-in visitor: the code is redeemed immediately and we redirect to a
 *    clean URL (without ?ref) so a refresh can't re-trigger it.
 *  - Guest visitor: the code is stashed in the session and claimed the moment
 *    they authenticate (login / signup / Google) — see AuthController. We leave
 *    the ?ref param in place so the auth modal still pops pre-filled.
 */
class HandleReferralLink
{
    public function __construct(private readonly ReferralService $referrals) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Only plain page navigations that actually carry a code are relevant.
        if (! $request->isMethod('get') || ! $request->filled('ref') || $request->expectsJson()) {
            return $next($request);
        }

        $code = strtoupper(trim((string) $request->query('ref')));
        if ($code === '') {
            return $next($request);
        }

        if (Auth::check()) {
            [$key, $message] = ReferralLink::feedbackFor(
                $this->referrals->claimCode(Auth::user(), $code)
            );

            // Strip ?ref so the claim isn't repeated on refresh/back.
            $query = $request->query();
            unset($query['ref']);
            $clean = $request->url().(empty($query) ? '' : '?'.http_build_query($query));

            return redirect($clean)->with($key, $message);
        }

        // Guest: remember the code so it survives until they sign in.
        $request->session()->put(ReferralLink::SESSION_KEY, $code);

        return $next($request);
    }
}
