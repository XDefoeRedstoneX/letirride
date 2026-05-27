<x-app-layout>
    @auth
        <div class="px-page">
            <div class="px-page-inner space-y-10"
                 x-data='referralPage(@json($shareUrl), {{ (int) $config->referee_welcome_points }}, {{ (int) $config->referrer_first_purchase_pts }}, {{ $canClaim ? 'true' : 'false' }})'>

                {{-- Header --}}
                <div style="text-align:center;">
                    <h1 class="px-heading">Refer <span class="gold">Friends</span></h1>
                    <p class="px-subheading">SHARE YOUR CODE · EARN WHEN THEY BUY</p>
                </div>
                <div class="px-divider"><div class="px-divider-dot"></div><div class="px-divider-line"></div><div class="px-divider-dot"></div></div>

                {{-- Your code card --}}
                <div class="referral-code-card">
                    <p class="referral-code-label">YOUR REFERRAL CODE</p>
                    <p class="referral-code-value" x-text="code">{{ $user->referral_code }}</p>
                    <div class="referral-code-actions">
                        <button type="button" @click="copyCode()" class="px-btn-gold referral-copy-btn">
                            <span x-text="copyLabel">COPY CODE</span>
                        </button>
                        <button type="button" @click="copyLink()" class="px-btn-ghost referral-copy-btn">
                            <span x-text="linkLabel">COPY SHARE LINK</span>
                        </button>
                    </div>
                    <p class="referral-share-hint">When a friend signs up with your code and completes their first purchase, you earn <strong>{{ number_format($config->referrer_first_purchase_pts) }} points</strong>. They get <strong>{{ number_format($config->referee_welcome_points) }} points</strong> for using your code.</p>
                </div>

                {{-- Stats --}}
                <div class="referral-stats-row">
                    <div class="referral-stat">
                        <p class="referral-stat-label">INVITED</p>
                        <p class="referral-stat-value">{{ number_format($invitedCount) }}</p>
                    </div>
                    <div class="referral-stat">
                        <p class="referral-stat-label">REWARDED</p>
                        <p class="referral-stat-value">{{ number_format($rewardedCount) }}</p>
                    </div>
                    <div class="referral-stat">
                        <p class="referral-stat-label">POINTS EARNED</p>
                        <p class="referral-stat-value">{{ number_format($earnedPoints) }}</p>
                    </div>
                </div>

                {{-- Milestone Progression --}}
                @if ($tiers->isNotEmpty())
                    <div class="referral-progression-card">
                        <div class="referral-progression-header">
                            <div>
                                <h3 class="referral-section-title">REWARD MILESTONES</h3>
                                <p class="referral-section-sub">Hit a tier when that many friends complete a first purchase &mdash; rewards apply automatically.</p>
                            </div>
                            <div class="referral-progression-summary">
                                <span class="referral-progression-summary-value">{{ $unlockedCount }} / {{ $tiers->count() }}</span>
                                <span class="referral-progression-summary-label">UNLOCKED</span>
                            </div>
                        </div>

                        @if ($nextTier)
                            @php
                                $remaining = max(0, $nextTier->threshold - $paidReferrals);
                                $progressPct = $nextTier->threshold > 0 ? min(100, ($paidReferrals / $nextTier->threshold) * 100) : 100;
                            @endphp
                            <div class="referral-progression-track">
                                <div class="referral-progression-track-meta">
                                    <span>NEXT: <strong>{{ $nextTier->title }}</strong></span>
                                    <span>{{ $paidReferrals }} / {{ $nextTier->threshold }} FRIENDS</span>
                                </div>
                                <div class="referral-progression-track-bar">
                                    <div class="referral-progression-track-fill" style="width: {{ $progressPct }}%;"></div>
                                </div>
                                <p class="referral-progression-track-hint">
                                    @if ($remaining > 0)
                                        {{ $remaining }} more paid {{ $remaining === 1 ? 'referral' : 'referrals' }} to unlock.
                                    @else
                                        Waiting on your friend's purchase to finalize this tier.
                                    @endif
                                </p>
                            </div>
                        @endif

                        <div class="referral-tier-list">
                            @foreach ($tiers as $tier)
                                @php
                                    $isUnlocked = $grantedTiers->has($tier->id);
                                    $isNext = $nextTier && $nextTier->id === $tier->id;
                                    $grantedAt = $isUnlocked ? optional($grantedTiers[$tier->id]->created_at)->format('Y-m-d') : null;
                                    $cardClass = $isUnlocked ? 'unlocked' : ($isNext ? 'next' : 'locked');
                                @endphp
                                <div class="referral-tier-card referral-tier-card-{{ $cardClass }}">
                                    <div class="referral-tier-card-head">
                                        <span class="referral-tier-threshold">{{ $tier->threshold }}</span>
                                        <div class="referral-tier-card-title-block">
                                            <h4 class="referral-tier-title">{{ $tier->title }}</h4>
                                            @if ($tier->description)
                                                <p class="referral-tier-desc">{{ $tier->description }}</p>
                                            @endif
                                        </div>
                                        <div class="referral-tier-state">
                                            @if ($isUnlocked)
                                                <span class="referral-tier-state-badge referral-tier-state-unlocked">
                                                    UNLOCKED
                                                </span>
                                                @if ($grantedAt)
                                                    <span class="referral-tier-state-date">{{ $grantedAt }}</span>
                                                @endif
                                            @elseif ($isNext)
                                                <span class="referral-tier-state-badge referral-tier-state-next">
                                                    IN PROGRESS
                                                </span>
                                            @else
                                                <span class="referral-tier-state-badge referral-tier-state-locked">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="square"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                    LOCKED
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="referral-tier-rewards">
                                        @if ($tier->points_reward > 0)
                                            <span class="referral-tier-reward-chip referral-tier-reward-points">+{{ number_format($tier->points_reward) }} PTS</span>
                                        @endif
                                        @if ($tier->discountType)
                                            <span class="referral-tier-reward-chip referral-tier-reward-discount">{{ strtoupper($tier->discountType->name) }}</span>
                                        @endif
                                        @if ($tier->free_spins_reward > 0)
                                            <span class="referral-tier-reward-chip referral-tier-reward-spin">{{ $tier->free_spins_reward }}× FREE SPIN</span>
                                        @endif
                                        @if (! $tier->hasReward())
                                            <span class="referral-tier-reward-chip referral-tier-reward-cosmetic">PRESTIGE</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Claim a code (only if not yet referred AND no paid orders) --}}
                @if ($canClaim)
                    <div class="referral-claim-card">
                        <h3 class="referral-section-title">GOT A CODE FROM A FRIEND?</h3>
                        <p class="referral-section-sub">Enter it before your first purchase to claim your bonus points.</p>
                        <form @submit.prevent="submitClaim" class="referral-claim-form">
                            @csrf
                            <input type="text" x-model="claimInput" maxlength="16"
                                   @input="claimInput = claimInput.toUpperCase()"
                                   class="referral-claim-input"
                                   placeholder="FRIENDCODE"
                                   :disabled="claimSubmitting">
                            <button type="submit" :disabled="claimSubmitting || !claimInput"
                                    class="px-btn-gold referral-claim-btn">
                                <span x-show="!claimSubmitting">CLAIM</span>
                                <span x-show="claimSubmitting">...</span>
                            </button>
                        </form>
                        <p x-show="claimMessage" :class="claimError ? 'referral-claim-error' : 'referral-claim-success'" x-text="claimMessage"></p>
                    </div>
                @elseif ($referrer)
                    <div class="referral-claim-card referral-claim-locked">
                        <h3 class="referral-section-title">REFERRED BY</h3>
                        <p class="referral-section-sub">You were referred by <strong>{{ $referrer->name }}</strong>. Thanks for joining!</p>
                    </div>
                @endif

                {{-- Friends list --}}
                <div class="referral-friends-card">
                    <h3 class="referral-section-title">YOUR FRIENDS</h3>
                    @if ($invited->isEmpty())
                        <p class="referral-friends-empty">No referrals yet. Share your code to start earning.</p>
                    @else
                        <div class="referral-friends-scroll">
                            <table class="referral-friends-table">
                                <thead>
                                    <tr>
                                        <th>FRIEND</th>
                                        <th>JOINED</th>
                                        <th>STATUS</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invited as $ref)
                                        <tr>
                                            <td>{{ $ref->referredUser?->name ?? '—' }}</td>
                                            <td>{{ $ref->created_at?->format('Y-m-d') ?? '—' }}</td>
                                            <td>
                                                @if ($ref->status === \App\Models\Referral::STATUS_FIRST_PURCHASE_REWARDED)
                                                    <span class="referral-status referral-status-rewarded">REWARDED</span>
                                                @elseif ($ref->status === \App\Models\Referral::STATUS_VOID)
                                                    <span class="referral-status referral-status-void">VOID</span>
                                                @else
                                                    <span class="referral-status referral-status-pending">PENDING</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <script>
        function referralPage(shareUrl, welcomePoints, firstPurchasePoints, canClaim) {
            return {
                code: @json($user->referral_code),
                shareUrl,
                copyLabel: 'COPY CODE',
                linkLabel: 'COPY SHARE LINK',
                claimInput: '',
                claimSubmitting: false,
                claimMessage: '',
                claimError: false,
                canClaim,

                async copyCode() {
                    await this.copy(this.code, 'copyLabel', 'COPY CODE');
                },

                async copyLink() {
                    await this.copy(this.shareUrl, 'linkLabel', 'COPY SHARE LINK');
                },

                async copy(text, prop, restore) {
                    try {
                        await navigator.clipboard.writeText(text);
                        this[prop] = 'COPIED!';
                    } catch (e) {
                        this[prop] = 'COPY FAILED';
                    }
                    setTimeout(() => { this[prop] = restore; }, 1500);
                },

                async submitClaim() {
                    if (!this.claimInput || this.claimSubmitting) return;
                    this.claimSubmitting = true;
                    this.claimMessage = '';

                    try {
                        const res = await fetch('{{ route('referrals.claim') }}', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ code: this.claimInput }),
                        });
                        const data = await res.json();
                        if (res.ok) {
                            this.claimMessage = data.message || 'Code applied!';
                            this.claimError = false;
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            this.claimMessage = data.message || 'Could not apply the code.';
                            this.claimError = true;
                        }
                    } catch (e) {
                        this.claimMessage = 'Network error. Try again.';
                        this.claimError = true;
                    } finally {
                        this.claimSubmitting = false;
                    }
                },
            };
        }
        </script>
    @else
        <div class="px-page" x-data="{}">
            <div class="px-empty-state" style="min-height:70vh;">
                <h1 class="px-heading" style="text-align:center;">Refer Friends, Earn <span class="gold">Points</span></h1>
                <p style="font-family:var(--font-sans);font-size:14px;color:var(--text-dim);text-align:center;max-width:480px;line-height:1.7;">Log in to grab your referral code and earn points when your friends shop on Ridly.</p>
                <div style="display:flex;gap:12px;">
                    <button @click="$dispatch('open-auth-modal', { tab: 'login' })" class="px-btn-gold" style="padding:16px 28px;font-size:8px;">LOGIN</button>
                    <button @click="$dispatch('open-auth-modal', { tab: 'signup' })" class="px-btn-ghost" style="padding:16px 28px;font-size:8px;">SIGN UP</button>
                </div>
            </div>
        </div>
    @endauth
</x-app-layout>
