<?php

use App\Http\Controllers\Webhook\TaxlyWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/taxly/webhook/invoice', [TaxlyWebhookController::class, 'handle'])
  ->name('taxly.webhook.invoice');
