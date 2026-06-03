<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ticket extends Model
{
    public const STATUSES = ['open', 'in_progress', 'closed'];

    private const STATUS_COLORS = [
        'open'        => 'bg-amber-500/10 text-amber-500',
        'in_progress' => 'bg-blue-500/10 text-blue-500',
        'closed'      => 'bg-green-500/10 text-green-500',
    ];

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'email',
        'name',
        'type',
        'subject',
        'message',
        'status',
        'ip_address',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $status && in_array($status, self::STATUSES, true)
            ? $query->where('status', $status)
            : $query;
    }

    /**
     * Free-text search across subject, message and email.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('subject', 'like', "%{$term}%")
                ->orWhere('message', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%");
        });
    }

    /**
     * Display name of the requester (account name, supplied guest name, or email).
     */
    public function requesterName(): string
    {
        return $this->user?->name ?: ($this->name ?: ($this->email ?: 'Guest'));
    }

    public function displaySubject(): string
    {
        return $this->subject ?: Str::limit((string) $this->message, 57);
    }

    public function statusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'bg-foreground/10 text-muted-foreground';
    }

    public function mailtoUrl(): string
    {
        $body = "Hi {$this->requesterName()},\n\n\n\n-----\nYour message:\n{$this->message}";

        return 'mailto:'.$this->email
            .'?subject='.rawurlencode('[Ridly Support #'.$this->id.'] '.$this->displaySubject())
            .'&body='.rawurlencode($body);
    }
}
