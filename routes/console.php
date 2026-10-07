<?php

use App\Services\MediaCleanup;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('media:cleanup', function () {
    $remaining = MediaCleanup::run();
    $this->info('Cleanup selesai; tertunda: '.$remaining);

    return $remaining ? 1 : 0;
})->purpose('Retry pending media deletions');

Schedule::command('media:cleanup')->everyTenMinutes()->withoutOverlapping();
