<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('taxly:sync-quantity-codes')->daily();
Schedule::command('taxly:sync-hs-codes')->daily();
Schedule::command('taxly:sync-service-codes')->daily();
