<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
Schedule::command('sop:check-deadlines')->everyMinute();
Schedule::command('sop:generate-daily-report')->dailyAt('00:00');
Schedule::command('sop:archive-old-data')->weekly();
Schedule::command('db:backup')->dailyAt('01:00');

