<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Single instance so its "outbox table exists?" cache persists per request.
        $this->app->singleton(\App\Services\Sync\SyncRecorder::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Partition the auto-increment id-space per node so concurrent inserts
        // on bidirectional tables never collide and ids stay identical across
        // nodes. Applied as SESSION vars whenever a MySQL connection is opened
        // (no special privileges required). See config/sync.php + plan §5.
        Event::listen(ConnectionEstablished::class, function (ConnectionEstablished $event): void {
            $connection = $event->connection;

            if ($connection->getDriverName() !== 'mysql') {
                return;
            }

            // A connection's id-space belongs to the node that owns its database.
            $nodeId = $connection->getName() === config('sync.remote')
                ? config('sync.remote_node_id')
                : config('sync.node_id');

            $offset = config('sync.node_offsets.'.$nodeId);
            $increment = (int) config('sync.id_increment');

            if ($offset === null || $increment < 1) {
                return;
            }

            try {
                $connection->getPdo()->exec(
                    'SET SESSION auto_increment_increment = '.$increment.', auto_increment_offset = '.(int) $offset
                );
            } catch (\Throwable $e) {
                Log::warning('sync: could not set auto_increment partition: '.$e->getMessage());
            }
        });

        // Support ticket submissions: cap per IP and per email to curb spam.
        RateLimiter::for('tickets', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return [
                Limit::perHour(100)->by('ip:'.$request->ip()),
                Limit::perDay(100)->by('email:'.$email),
            ];
        });
    }
}
