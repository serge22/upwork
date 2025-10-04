<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Schedule::command('app:fetch-jobs')->everyTwoMinutes()->withoutOverlapping();
Schedule::command('app:cleanup-old-jobs')->daily();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
