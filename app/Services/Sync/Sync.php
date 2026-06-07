<?php

namespace App\Services\Sync;

/**
 * Process-wide guard that lets the sync engine apply remote changes WITHOUT the
 * model observers recording them back into the outbox (which would cause an echo
 * loop). The engine wraps every apply in Sync::withoutRecording(...).
 */
class Sync
{
    protected static bool $applying = false;

    public static function applying(): bool
    {
        return self::$applying;
    }

    /** Run $callback with outbox recording suppressed (used while applying pulled changes). */
    public static function withoutRecording(callable $callback): mixed
    {
        $previous = self::$applying;
        self::$applying = true;

        try {
            return $callback();
        } finally {
            self::$applying = $previous;
        }
    }
}
