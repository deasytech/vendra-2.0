<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Customer;
use App\Models\Organization;
use App\Models\Setting;
use App\Services\TaxlyService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Throwable;
use App\Models\TaxlyCredential;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InvoiceEdit extends Component
{
    public $invoice;
    public $tenant_id;
    public $organization_id;
    public $customer_id;
    public $business_id;
    public $service_id;
    public $invoice_reference;
    public $issue_date;
    public $due_date;
    public $invoice_type_code = '396';
    public $document_currency_code = 'NGN';
    public $invoice_lines = [];
    public $supplier = [];
    public $customer = [];
    public $legal_monetary_total = [];

    public $selected_customer_id;
    public $customer_type = 'customer';

    public $customer_name;
    public $customer_tin;
    public $customer_email;
    public $customer_phone;

    public $submitting = false;
    public $message;
    public $validating = false;
    public $submit_to_firs = false;

    public $withholding_tax_rate = 5.0;
    public $taxes = [];
    public $withholding_tax_enabled = false;
    public $selected_currency = 'NGN';
    public $selected_currency_symbol = '₦';

    // VAT and totals
    public $vat_rate = 7.5; // 7.5% VAT
    public $sub_total = 0;
    public $vat_amount = 0;
    public $total_amount = 0;
    public $withholding_tax_amount = 0;

    // Dynamic data
    public $invoice_types = [];
    public $currencies = [];
    public $allowance_charges = [];

    public $hsn_code, $product_category;

    // Project settings from backend
    public $projectSettings = [];

    protected $tax_category_id = 'LOCAL_SALES_TAX';

    protected $rules = [
        'invoice_reference' => 'required|string|max:255',
        'issue_date' => 'required|date',
        'invoice_lines' => 'required|array|min:1',
        'invoice_lines.*.item.name' => 'required|string',
        'invoice_lines.*.item.description' => 'required|string',
        'invoice_lines.*.invoiced_quantity' => 'required|numeric|min:0.01',
        'invoice_lines.*.price.price_amount' => 'required|numeric|min:0',
    ];

    public function mount(Invoice $invoice)
    {
        $this->invoice = $invoice;

        // Load settings from backend
        $this->loadSettings();

        // Load existing invoice data
        $this->loadInvoiceData();

        $this->setDefaultSupplier();
        $this->loadInvoiceTypes();
        $this->loadCurrencies();
        $this->loadTaxes();
        $this->computeTotals();
    }

    /**
     * Load settings from backend
     */
    private function loadSettings()
    {
        // Load withholding tax settings
        $this->withholding_tax_rate = Setting::getValue('withholding_tax_rate', 5.0);
        $this->withholding_tax_enabled = Setting::getValue('withholding_tax_enabled', true);

        // Load project settings for use in views if needed
        $this->projectSettings = Setting::getProjectSettings();
    }

    /**
     * Load existing invoice data into form fields
     */
    private function loadInvoiceData()
    {
        $this->invoice_reference = $this->invoice->invoice_reference;
        $this->issue_date = $this->invoice->issue_date ? $this->invoice->issue_date->format('Y-m-d') : null;
        $this->due_date = $this->invoice->due_date ? $this->invoice->due_date->format('Y-m-d') : null;
        $this->invoice_type_code = $this->invoice->invoice_type_code ?? '396';
        $this->document_currency_code = $this->invoice->document_currency_code ?? 'NGN';
        $this->selected_currency = $this->document_currency_code;

        // Set currency symbol
        $currency = collect($this->currencies)->firstWhere('code', $this->document_currency_code);
        $this->selected_currency_symbol = $currency['symbol'] ?? '₦';

        // Load supplier data
        $this->supplier = $this->invoice->accounting_supplier_party ?? [];
        $this->organization_id = $this->invoice->organization_id;
        $this->tenant_id = $this->invoice->tenant_id;
        $this->service_id = $this->invoice->service_id;
        $this->business_id = $this->invoice->business_id;

        // Load customer data
        $this->customer = $this->invoice->accounting_customer_party ?? [];
        $this->customer_id = $this->invoice->customer_id;

        if ($this->customer_id) {
            $customer = Customer::find($this->customer_id);
            if ($customer) {
                $this->selected_customer_id = $this->customer_id;
                $this->customer_name = $customer->name;
                $this->customer_tin = $customer->tin;
                $this->customer_email = $customer->email;
                $this->customer_phone = $customer->phone;
            }
        }

        // Load invoice lines
        $this->invoice_lines = [];
        foreach ($this->invoice->lines as $line) {
            $this->invoice_lines[] = [
                'id' => $line->id,
                'hsn_code' => $line->hsn_code ?? $this->generateHsnCode(),
                'product_category' => $line->product_category ?? 'General Items',
                'invoiced_quantity' => $line->invoiced_quantity ?? $line->quantity ?? 1,
                'price' => [
                    'price_amount' => $line->price['price_amount'] ?? 0,
                    'base_quantity' => $line->price['base_quantity'] ?? 1,
                    'price_unit' => $line->price['price_unit'] ?? $this->document_currency_code . ' per 1'
                ],
                'item' => [
                    'name' => $line->item['name'] ?? $line->description ?? '',
                    'description' => $line->item['description'] ?? $line->description ?? ''
                ],
                'order' => $line->order ?? 0,
                'selected_tax' => 'STANDARD_VAT',
                'tax_amount' => 0
            ];
        }

        // If no lines exist, add a default one
        if (empty($this->invoice_lines)) {
            $this->invoice_lines = [
                [
                    'hsn_code' => $this->generateHsnCode(),
                    'product_category' => 'General Items',
                    'invoiced_quantity' => 1,
                    'price' => [
                        'price_amount' => 0,
                        'base_quantity' => 1,
                        'price_unit' => $this->document_currency_code . ' per 1'
                    ],
                    'item' => ['name' => '', 'description' => ''],
                    'order' => 0,
                    'selected_tax' => 'STANDARD_VAT',
                    'tax_amount' => 0
                ]
            ];
        }

        // Load monetary totals
        $this->legal_monetary_total = $this->invoice->legal_monetary_total ?? [];
    }

    /**
     * Set default supplier fields if organizations exist
     */
    private function setDefaultSupplier()
    {
        $organization = Organization::first();

        if ($organization) {
            $this->supplier = $organization->toPartyObject();

            // Set other internal references
            $this->organization_id = $organization->id;
            $this->tenant_id = $organization->tenant_id ?? Auth::user()?->tenant_id;
            $this->service_id = $organization->service_id;
            $this->business_id = $organization->business_id;
        } else {
            // Fallback defaults if no organization exists
            $this->supplier = [
                'party_name' => 'Unknown Supplier',
                'tin' => '',
                'email' => '',
                'telephone' => '',
                'postal_address' => [
                    'street_name' => 'Unknown Street',
                    'city_name' => 'Unknown City',
                    'postal_zone' => '000000',
                    'country' => 'NG',
                ],
            ];
        }
    }

    /**
     * Load invoice types dynamically from Taxly service
     */
    private function loadInvoiceTypes()
    {
        try {
            // Fallback to default types if API fails
            $this->invoice_types = [
                ['code' => '396', 'value' => 'Standard Invoice'],
                ['code' => '380', 'value' => 'Credit Note'],
                ['code' => '381', 'value' => 'Commercial Invoice'],
                ['code' => '384', 'value' => 'Debit Note'],
                ['code' => '385', 'value' => 'Self Billed Invoice'],
                ['code' => '388', 'value' => 'Factored Invoice'],
                ['code' => '389', 'value' => 'Statement of Account'],
            ];
        } catch (\Throwable $e) {
            Log::warning('Failed to load invoice types from Taxly API', ['error' => $e->getMessage()]);
            // Fallback to default types
            $this->invoice_types = [
                ['code' => '396', 'value' => 'Standard Invoice'],
                ['code' => '380', 'value' => 'Credit Note'],
                ['code' => '381', 'value' => 'Commercial Invoice'],
                ['code' => '384', 'value' => 'Debit Note'],
                ['code' => '385', 'value' => 'Self Billed Invoice'],
                ['code' => '388', 'value' => 'Factored Invoice'],
                ['code' => '389', 'value' => 'Statement of Account'],
            ];
        }
    }

    private function loadCurrencies()
    {
        $this->currencies = [
            [
                'symbol' => '₦',
                'name' => 'Nigerian Naira',
                'code' => 'NGN',
            ],
            [
                'symbol' => '$',
                'name' => 'US Dollar',
                'code' => 'USD',
            ],
            [
                'symbol' => 'CA$',
                'name' => 'Canadian Dollar',
                'code' => 'CAD',
            ],
            [
                'symbol' => '€',
                'name' => 'Euro',
                'code' => 'EUR',
            ],
            [
                'symbol' => '£',
                'name' => 'British Pound Sterling',
                'code' => 'GBP',
            ],
            [
                'symbol' => 'GH₵',
                'name' => 'Ghanaian Cedi',
                'code' => 'GHS',
            ],
        ];
    }

    private function loadTaxes()
    {
        $this->taxes = [
            ['code' => 'STANDARD_VAT', 'name' => 'Standard Value-Added Tax', 'percent' => 7.5],
            ['code' => 'ZERO_VAT', 'name' => 'Zero Value-Added Tax', 'percent' => 0.0],
            ['code' => 'ZERO_GST', 'name' => 'Zero Goods and Services Tax', 'percent' => 0.0],
        ];
    }

    public function updatedSelectedCustomerId($value)
    {
        if ($value) {
            $customer = Customer::find($value);

            if ($customer) {
                $party = $customer->toPartyObject();
                $this->customer_id = $customer->id;

                $this->customer_name = $party['party_name'] ?? '';
                $this->customer_tin = $party['tin'] ?? '';
                $this->customer_email = $party['email'] ?? '';
                $this->customer_phone = $this->customer_phone;

                $this->customer = [
                    'party_name' => $this->customer_name,
                    'tin' => $customer->tin,
                    'email' => $customer->email,
                    'telephone' => $this->customer_phone,
                    'business_description' => $customer->business_description ?? '',
                    'postal_address' => [
                        'street_name' => $customer->street_name ?? '',
                        'city_name' => $customer->city_name ?? '',
                        'postal_zone' => $customer->postal_zone ?? '',
                        'country' => 'NG',
                    ],
                ];
            }
        } else {
            $this->customer = [
                'party_name' => '',
                'tin' => '',
                'email' => '',
                'telephone' => '',
                'business_description' => '',
                'postal_address' => [
                    'street_name' => '',
                    'city_name' => '',
                    'postal_zone' => '',
                    'country' => 'NG',
                ],
            ];
        }
    }

    public function getCustomersProperty()
    {
        return Customer::all();
    }

    public function getOrganizationsProperty()
    {
        return Organization::all();
    }

    public function addLine()
    {
        $this->invoice_lines[] = [
            'hsn_code' => $this->generateHsnCode(),
            'product_category' => 'General Items',
            'invoiced_quantity' => 1,
            'price' => [
                'price_amount' => 0,
                'base_quantity' => 1,
                'price_unit' => $this->selected_currency . ' per 1'
            ],
            'item' => ['name' => '', 'description' => ''],
            'order' => count($this->invoice_lines),
            'selected_tax' => 'STANDARD_VAT',
            'tax_amount' => 0
        ];
        $this->computeTotals();
    }

    public function removeLine($index)
    {
        if (isset($this->invoice_lines[$index])) {
            array_splice($this->invoice_lines, $index, 1);
            $this->computeTotals();
        }
    }

    // Live update when invoice line data changes
    public function updatedInvoiceLines($value, $key)
    {
        $this->computeTotals();
    }

    // Live update when quantity changes
    public function updatedInvoiceLinesQuantity($value, $key)
    {
        $this->computeTotals();
    }

    // Live update when price changes
    public function updatedInvoiceLinesPrice($value, $key)
    {
        $this->computeTotals();
    }

    protected function computeTotals()
    {
        $lineTotal = 0;

        foreach ($this->invoice_lines as $index => $line) {
            $price = (float) ($line['price']['price_amount'] ?? 0);
            $qty = (float) ($line['invoiced_quantity'] ?? 1);
            $selectedTax = $line['selected_tax'] ?? 'STANDARD_VAT';

            // Calculate line extension
            $lineExtension = $price * $qty;
            $lineTotal += $lineExtension;

            // Update line tax amount (for display per-line)
            $taxRate = $this->getTaxRate($selectedTax);
            $lineTaxAmount = round($lineExtension * ($taxRate / 100), 2);
            $this->invoice_lines[$index]['tax_amount'] = $lineTaxAmount;
        }

        $this->sub_total = round($lineTotal, 2);

        // Calculate allowances/charges based on sub_total
        $allowanceTotal = 0;
        foreach ($this->allowance_charges as $charge) {
            $amt = (float) ($charge['amount'] ?? 0);
            if (($charge['amount_type'] ?? 'fixed') === 'percent') {
                $amt = round($this->sub_total * ($amt / 100), 2);
            }

            if (!empty($charge['charge_indicator'])) {
                $allowanceTotal += $amt;
            } else {
                $allowanceTotal -= $amt;
            }
        }

        // Taxable amount is subtotal plus allowances/charges
        $taxable = $this->sub_total + $allowanceTotal;
        if ($taxable < 0) {
            $taxable = 0;
        }

        $this->vat_amount = round($taxable * ($this->vat_rate / 100), 2);
        $this->total_amount = round($taxable + $this->vat_amount, 2);

        // Apply withholding tax if enabled (on taxable amount only, excluding VAT)
        if ($this->withholding_tax_enabled) {
            $this->withholding_tax_amount = round($taxable * ($this->withholding_tax_rate / 100), 2);
            $this->total_amount = round($taxable + $this->vat_amount - $this->withholding_tax_amount, 2);
        } else {
            $this->withholding_tax_amount = 0;
            $this->total_amount = round($taxable + $this->vat_amount, 2);
        }

        $this->legal_monetary_total = [
            'tax_exclusive_amount' => $taxable,
            'tax_inclusive_amount' => $this->total_amount,
            'line_extension_amount' => $this->sub_total,
            'payable_amount' => $this->total_amount,
        ];
    }

    public function updatedSelectedCurrency($value)
    {
        $currency = collect($this->currencies)->firstWhere('code', $value);
        $this->selected_currency_symbol = $currency['symbol'] ?? '₦';
        $this->document_currency_code = $value;

        // Update price units for all invoice lines
        foreach ($this->invoice_lines as $index => $line) {
            $this->invoice_lines[$index]['price']['price_unit'] = $value . ' per 1';
        }

        $this->computeTotals();
    }

    public function updatedWithholdingTaxEnabled($value)
    {
        $this->computeTotals();
    }

    public function updatedInvoiceLinesSelectedTax($value, $key)
    {
        $this->computeTotals();
    }

    private function getTaxRate($taxCode)
    {
        $tax = collect($this->taxes)->firstWhere('code', $taxCode);
        return $tax ? $tax['percent'] : 0;
    }

    public function addAllowanceCharge($isCharge = true)
    {
        $this->allowance_charges[] = [
            'charge_indicator' => $isCharge,
            'amount' => 0,
            'amount_type' => 'fixed', // 'fixed' or 'percent'
            'reason' => ''
        ];
        $this->computeTotals();
    }

    public function removeAllowanceCharge($index)
    {
        if (isset($this->allowance_charges[$index])) {
            array_splice($this->allowance_charges, $index, 1);
            $this->computeTotals();
        }
    }

    public function updatedAllowanceCharges($value, $key)
    {
        $this->computeTotals();
    }

    private function getFormattedAllowanceCharges()
    {
        return array_map(function ($charge) {
            $amount = (float) ($charge['amount'] ?? 0);
            if (($charge['amount_type'] ?? 'fixed') === 'percent') {
                $amount = round(($this->sub_total * ($amount / 100)), 2);
            }

            return [
                'charge_indicator' => $charge['charge_indicator'],
                'amount' => (float) $amount,
                'amount_type' => $charge['amount_type'] ?? 'fixed'
            ];
        }, $this->allowance_charges);
    }

    public function updateInvoice()
    {
        $this->validate();

        $this->submitting = true;
        $this->computeTotals();

        $this->ensureEntityIdentifiers();

        DB::beginTransaction();

        try {
            // Update the invoice
            $this->invoice->update([
                'invoice_reference' => $this->invoice_reference,
                'issue_date' => $this->issue_date,
                'due_date' => $this->due_date,
                'invoice_type_code' => $this->invoice_type_code,
                'document_currency_code' => $this->document_currency_code,
                'customer_id' => $this->customer_id,
                'accounting_supplier_party' => $this->supplier,
                'accounting_customer_party' => $this->customer,
                'legal_monetary_total' => $this->legal_monetary_total,
            ]);

            // Delete existing invoice lines and create new ones
            $this->invoice->lines()->delete();

            foreach ($this->invoice_lines as $i => $line) {
                $lineData = [
                    'invoice_id' => $this->invoice->id,
                    'hsn_code' => $line['hsn_code'] ?? $this->generateHsnCode(),
                    'product_category' => $line['product_category'] ?? 'General Items',
                    'invoiced_quantity' => $line['invoiced_quantity'] ?? 1,
                    'quantity' => $line['invoiced_quantity'] ?? 1,
                    'price' => [
                        'price_amount' => $line['price']['price_amount'] ?? 0,
                        'base_quantity' => $line['price']['base_quantity'] ?? 1,
                        'price_unit' => $line['price']['price_unit'] ?? $this->document_currency_code . ' per 1'
                    ],
                    'item' => [
                        'name' => $line['item']['name'] ?? '',
                        'description' => $line['item']['description'] ?? ''
                    ],
                    'description' => $line['item']['description'] ?? '',
                    'line_total' => ((float) ($line['price']['price_amount'] ?? 0)) * ((float) ($line['invoiced_quantity'] ?? 1)),
                    'order' => $i
                ];

                InvoiceLine::create($lineData);
            }

            DB::commit();

            $this->message = 'Invoice updated successfully!';
            session()->flash('success', $this->message);
            return redirect()->route('invoices.show', $this->invoice);
        } catch (Throwable $e) {
            DB::rollBack();

            $readableError = $this->extractReadableFirsError($e);
            $this->addError('submission', $readableError);
            $this->message = $readableError;

            Log::error('Invoice update error', ['error' => $readableError]);
        } finally {
            $this->submitting = false;
        }
    }

    /**
     * Ensure required IDs (customer_id, service_id, tenant_id) are populated
     */
    private function ensureEntityIdentifiers()
    {
        if (!$this->organization_id && $this->selected_supplier_id) {
            $organization = Organization::find($this->selected_supplier_id);
            if ($organization) {
                $this->organization_id = $organization->id;
                $this->service_id = $organization->service_id ?? null;
                $this->tenant_id = $organization->tenant_id ?? Auth::user()->tenant_id ?? null;
            }
        }
    }

    /**
     * Generate a random 8-digit HSN code
     */
    private function generateHsnCode(): string
    {
        return strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
    }

    public function render()
    {
        return view('livewire.invoices.invoice-edit');
    }

    public function validateInvoice()
    {
        $this->validating = true;
        $this->validate();

        $this->ensureEntityIdentifiers();

        try {
            $this->computeTotals();

            if (empty($this->customer['postal_address'])) {
                $this->customer['postal_address'] = [
                    'street_name' => 'Unknown Street',
                    'city_name' => 'Unknown City',
                    'postal_zone' => '000000',
                    'country' => 'NG',
                ];
            }

            // build payload for Taxly validation - include all required fields
            $payload = [
                'channel' => 'api',
                'business_id' => $this->business_id,
                'invoice_reference' => $this->invoice_reference,
                'irn' => $this->invoice_reference . '-' . $this->service_id . '-' . now()->format('Ymd'),
                'issue_date' => $this->issue_date,
                'due_date' => $this->due_date,
                'issue_time' => now()->format('H:i:s'),
                'invoice_type_code' => $this->invoice_type_code,
                'document_currency_code' => $this->document_currency_code,
                'tax_currency_code' => $this->document_currency_code, // Required field
                'payment_status' => 'PENDING', // Required field
                'accounting_supplier_party' => $this->supplier,
                'legal_monetary_total' => $this->legal_monetary_total,
                'invoice_line' => $this->formatInvoiceLinesForTaxly(),
                // Add required fields for validation
                'payment_means' => [
                    [
                        'payment_means_code' => '10',
                        'payment_due_date' => $this->due_date,
                    ],
                ],
                'allowance_charge' => $this->getFormattedAllowanceCharges(),
                'tax_total' => [
                    [
                        'tax_amount' => $this->vat_amount,
                        'tax_subtotal' => [
                            [
                                'taxable_amount' => $this->sub_total,
                                'tax_amount' => $this->vat_amount,
                                'tax_category' => [
                                    'id' => $this->tax_category_id,
                                    'percent' => $this->vat_rate,
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            // Only include customer party if customer is selected
            if ($this->customer_id || !empty($this->customer['party_name'])) {
                $payload['accounting_customer_party'] = $this->customer;
            }
            Log::debug('Invoice validation payload', ['payload' => $payload]);
            // call Taxly service for validation
            $cred = TaxlyCredential::first();
            $taxly = new TaxlyService($cred);

            // validate invoice structure
            $taxly->validateInvoice($payload);

            $this->message = 'Invoice validation successful! Ready to submit.';
            $this->dispatch('validation-success', message: 'Invoice structure is valid');
        } catch (Throwable $e) {
            $readableError = $this->extractReadableFirsError($e);
            $this->addError('validation', $readableError);
            $this->message = $readableError;

            Log::error('Invoice validation error', ['error' => $readableError]);
        } finally {
            $this->validating = false;
        }
    }

    public function validateIRN()
    {
        $this->validating = true;

        try {
            $this->validate();

            $this->ensureEntityIdentifiers();

            $this->computeTotals();

            // build payload for Taxly validation
            $payload = [
                'invoice_reference' => $this->invoice_reference,
                'irn' => $this->invoice_reference . '-' . $this->service_id . '-' . now()->format('Ymd'),
                'business_id' => $this->business_id,
            ];

            Log::debug('IRN validation payload', ['payload' => $payload]);

            $cred = TaxlyCredential::first();
            $taxly = new TaxlyService($cred);

            // validate irn structure
            $taxly->validateIrn($payload);

            $this->message = 'IRN validation successful! Ready to submit.';
            $this->dispatch('validation-success', message: 'IRN structure is valid');
        } catch (Throwable $e) {
            $readableError = $this->extractReadableFirsError($e);
            $this->addError('validation', $readableError);
            $this->message = $readableError;

            Log::error('IRN validation error', [
                'error' => $readableError,
                'full_exception' => $e->getMessage() // Log full message separately for debugging
            ]);
        } finally {
            $this->validating = false;
        }
    }

    /**
     * Format invoice lines for Taxly API with proper data types
     */
    private function formatInvoiceLinesForTaxly(): array
    {
        return array_map(function ($line, $index) {
            return [
                'hsn_code' => $line['hsn_code'] ?? $this->generateHsnCode(),
                'product_category' => $line['product_category'] ?? 'General Items',
                'invoiced_quantity' => (float) ($line['invoiced_quantity'] ?? 0),
                'line_extension_amount' => (float) (($line['price']['price_amount'] ?? 0) * ($line['invoiced_quantity'] ?? 0)),
                'item' => [
                    'name' => $line['item']['name'] ?? '',
                    'description' => $line['item']['description'] ?? '',
                ],
                'price' => [
                    'price_amount' => (float) ($line['price']['price_amount'] ?? 0),
                    'base_quantity' => (float) ($line['price']['base_quantity'] ?? 1),
                    'price_unit' => $line['price']['price_unit'] ?? 'NGN per 1',
                ],
                'order' => $index,
            ];
        }, $this->invoice_lines, array_keys($this->invoice_lines));
    }

    public function extractReadableFirsError(Throwable $e)
    {
        // Attempt to extract JSON body from the exception message
        preg_match('/\{.*\}/s', $e->getMessage(), $matches);

        if (!empty($matches)) {
            $json = json_decode($matches[0], true);

            // If JSON is valid and structured
            if (json_last_error() === JSON_ERROR_NONE) {
                $error = $json['data']['error'] ?? null;

                if (!empty($error['details'])) {
                    return $error['details'];
                }

                if (!empty($error['public_message'])) {
                    return $error['public_message'];
                }

                if (!empty($json['message'])) {
                    return $json['message'];
                }
            }
        }

        // Fallback to generic message
        return $e->getMessage();
    }
}
