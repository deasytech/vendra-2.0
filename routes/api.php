<?php

use App\Http\Controllers\Webhook\TaxlyWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/vendra/webhook', [TaxlyWebhookController::class, 'handle']);
