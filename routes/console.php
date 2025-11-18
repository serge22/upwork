<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

$fetchSchedule = config('app.fetch_jobs_schedule', 'everyTwoMinutes');
Schedule::command('app:fetch-jobs')->{$fetchSchedule}()->withoutOverlapping();
Schedule::command('app:cleanup-old-jobs')->daily();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
