<?php

use App\Http\Controllers\Webhook\TaxlyWebhookController as WebhookTaxlyWebhookController;
use App\Livewire\Customers\CustomerCreate;
use App\Livewire\Customers\CustomerEdit;
use App\Livewire\Customers\CustomersIndex;
use App\Livewire\Dashboard;
use App\Livewire\Invoices\InvoiceCreate;
use App\Livewire\Invoices\InvoiceEdit;
use App\Livewire\Invoices\InvoiceShow;
use App\Livewire\Invoices\InvoicesIndex;
use App\Livewire\Invoices\TransmittedInvoices;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', Dashboard::class)
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::get('/invoices', InvoicesIndex::class)->name('invoices.index');
    Route::get('/create-invoice', InvoiceCreate::class)->name('invoice.create');
    Route::get('/invoices/{invoice}', InvoiceShow::class)->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', InvoiceEdit::class)->name('invoices.edit');
    Route::get('/customers', CustomersIndex::class)->name('customers.index');
    Route::get('/customers/create', CustomerCreate::class)->name('customers.create');
    Route::get('/customers/{customer}/edit', CustomerEdit::class)->name('customers.edit');
    Route::get('/invoice-exchange', TransmittedInvoices::class)->name('invoice-exchange');
});

Route::post('/taxly/webhook/invoice', [WebhookTaxlyWebhookController::class, 'handle'])
    ->name('taxly.webhook.invoice');

require __DIR__ . '/auth.php';
