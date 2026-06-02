<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ticket extends Model
{
    public const STATUSES = ['open', 'in_progress', 'closed'];

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

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

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
        return $this->subject ?: Str::limit((string) $this->message, 57, '...');
    }
}
