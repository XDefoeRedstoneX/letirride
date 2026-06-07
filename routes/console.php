<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Local node only: run a sync cycle every minute. The command itself re-checks
// enabled/paused/lock, so this is a safe no-op on CPanel or while paused.
Schedule::command('sync:run')
    ->everyMinute()
    ->withoutOverlapping()
    ->when(fn () => config('sync.is_local') && config('sync.enabled'));
