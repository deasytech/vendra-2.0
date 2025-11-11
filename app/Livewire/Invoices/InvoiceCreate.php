<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Customer;
use App\Models\Organization;
use App\Services\TaxlyService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Throwable;
use App\Models\TaxlyCredential;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class InvoiceCreate extends Component
{
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

    public $customer_name;
    public $customer_tin;
    public $customer_email;
    public $customer_phone;

    public $submitting = false;
    public $message;
    public $validating = false;
    public $submit_to_firs = false;

    // VAT and totals
    public $vat_rate = 7.5; // 7.5% VAT
    public $sub_total = 0;
    public $vat_amount = 0;
    public $total_amount = 0;

    // Dynamic data
    public $invoice_types = [];

    public $hsn_code, $product_category;

    protected $tax_category_id = 'LOCAL_SALES_TAX';

    protected $rules = [
        'invoice_reference' => 'required|string|max:255',
        'issue_date' => 'required|date',
        'invoice_lines' => 'required|array|min:1',
        'invoice_lines.*.item.name' => 'required|string',
        'invoice_lines.*.item.description' => 'required|string',
        'invoice_lines.*.invoiced_quantity' => 'required|integer|min:1',
        'invoice_lines.*.price.price_amount' => 'required|numeric|min:0',
    ];

    public function mount()
    {
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

        $this->setDefaultSupplier();
        $this->invoice_reference = 'INV' . strtoupper(uniqid());
        $this->issue_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->loadInvoiceTypes();

        $this->invoice_lines = [
            [
                'hsn_code' => $this->hsn_code = $this->generateHsnCode(),
                'product_category' => $this->product_category,
                'invoiced_quantity' => 1,
                'price' => [
                    'price_amount' => 0,
                    'base_quantity' => 1,
                    'price_unit' => 'NGN per 1'
                ],
                'item' => ['name' => '', 'description' => ''],
                'order' => 0
            ]
        ];
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
            $cred = TaxlyCredential::first();
            $taxly = new TaxlyService($cred);
            $response = $taxly->getInvoiceTypes();

            if (isset($response['data']) && is_array($response['data'])) {
                $this->invoice_types = $response['data'];
            } else {
                // Fallback to default types if API fails
                $this->invoice_types = [
                    ['code' => '396', 'value' => 'Standard Invoice'],
                    ['code' => '381', 'value' => 'Commercial Invoice'],
                    ['code' => '389', 'value' => 'Proforma Invoice'],
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to load invoice types from Taxly API', ['error' => $e->getMessage()]);
            // Fallback to default types
            $this->invoice_types = [
                ['code' => '396', 'value' => 'Standard Invoice'],
                ['code' => '381', 'value' => 'Commercial Invoice'],
                ['code' => '389', 'value' => 'Proforma Invoice'],
            ];
        }
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
                $this->customer_phone = $party['telephone'] ?? '';

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
        $this->invoice_lines[] = ['hsn_code' => $this->hsn_code = $this->generateHsnCode(), 'product_category' => $this->product_category, 'invoiced_quantity' => 1, 'price' => ['price_amount' => 0, 'base_quantity' => 1, 'price_unit' => 'NGN per 1'], 'item' => ['name' => '', 'description' => ''], 'order' => count($this->invoice_lines)];
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

        foreach ($this->invoice_lines as $line) {
            $price = (float) ($line['price']['price_amount'] ?? 0);
            $qty = (float) ($line['invoiced_quantity'] ?? 1);

            $lineExtension = $price * $qty;
            $lineTotal += $lineExtension;
        }

        $this->sub_total = round($lineTotal, 2);
        $this->vat_amount = round($this->sub_total * ($this->vat_rate / 100), 2);
        $this->total_amount = round($this->sub_total + $this->vat_amount, 2);

        $this->legal_monetary_total = [
            'tax_exclusive_amount' => $this->sub_total,
            'tax_inclusive_amount' => $this->total_amount,
            'line_extension_amount' => $this->sub_total,
            'payable_amount' => $this->total_amount,
        ];
    }

    public function submitInvoice()
    {
        $this->validate();

        $this->submitting = true;
        $this->computeTotals();

        $this->ensureEntityIdentifiers();

        DB::beginTransaction();

        try {
            // Generate IRN for the invoice
            $irn = $this->invoice_reference . '-' . $this->service_id . '-' . now()->format('Ymd');

            // create internal invoice record
            $invoice = Invoice::create([
                'tenant_id' => $this->tenant_id,
                'organization_id' => $this->organization_id,
                'customer_id' => $this->customer_id,
                'invoice_reference' => $this->invoice_reference,
                'issue_date' => $this->issue_date,
                'due_date' => $this->due_date,
                'invoice_type_code' => $this->invoice_type_code,
                'payment_status' => 'PENDING',
                'accounting_supplier_party' => $this->supplier,
                'accounting_customer_party' => $this->customer,
                'legal_monetary_total' => $this->legal_monetary_total,
                'irn' => $irn, // Store IRN for future transmission
            ]);

            foreach ($this->invoice_lines as $i => $line) {
                $line['order'] = $i;
                InvoiceLine::create(array_merge($line, ['invoice_id' => $invoice->id]));
            }

            // build payload for Taxly submission (but NOT transmission)
            $payload = [
                'channel' => 'api',
                'business_id' => $this->business_id,
                'invoice_reference' => $this->invoice_reference,
                'irn' => $irn,
                'issue_date' => $this->issue_date,
                'due_date' => $this->due_date,
                'issue_time' => now()->format('H:i:s'),
                'invoice_type_code' => $this->invoice_type_code,
                'document_currency_code' => $this->document_currency_code,
                'tax_currency_code' => $this->document_currency_code,
                'payment_status' => 'PENDING',
                'accounting_supplier_party' => $this->supplier,
                'accounting_customer_party' => $this->customer,
                'legal_monetary_total' => $this->legal_monetary_total,
                'invoice_line' => $this->formatInvoiceLinesForTaxly(),
                // Add required fields for submission
                'payment_means' => [
                    [
                        'payment_means_code' => '10',
                        'payment_due_date' => $this->due_date,
                    ],
                ],
                'allowance_charge' => [
                    [
                        'charge_indicator' => true,
                        'amount' => $this->total_amount,
                    ],
                ],
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

            Log::debug('Invoice submission payload', ['payload' => $payload]);

            // call Taxly service to submit to FIRS (but not transmit)
            $cred = TaxlyCredential::first();
            $taxly = new TaxlyService($cred);

            // submit invoice to FIRS (this is the submission step, not transmission)
            $response = $taxly->submitInvoice($payload);

            Log::info('Invoice submitted to FIRS', [
                'invoice_id' => $invoice->id,
                'response' => $response,
            ]);

            // record successful submission (but not transmission) - status should be PENDING
            $invoice->transmissions()->create([
                'action' => 'submit',
                'request_payload' => $payload,
                'response_payload' => $response,
                'status' => 'PENDING'
            ]);

            DB::commit();

            $this->message = 'Invoice submitted to FIRS successfully. Ready for transmission.';

            // Redirect to invoice index to enable transmission
            return redirect()->route('invoices.index');
        } catch (Throwable $e) {
            DB::rollBack();
            // store failure transmission if invoice exists
            if (!empty($invoice ?? null)) {
                try {
                    $invoice->transmissions()->create([
                        'action' => 'submit',
                        'request_payload' => $payload ?? null,
                        'response_payload' => ['error' => $e->getMessage()],
                        'status' => 'failure'
                    ]);
                } catch (Throwable $inner) {
                    Log::error('Failed to store submission failure: ' . $inner->getMessage());
                }
            }

            $this->addError('submission', 'Failed to submit invoice to FIRS: ' . $e->getMessage());
            $this->message = 'Failed to submit invoice to FIRS. See errors.';
            Log::error('Invoice submission error', ['error' => $e->getMessage()]);
        } finally {
            $this->submitting = false;
        }
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
                'accounting_customer_party' => $this->customer,
                'legal_monetary_total' => $this->legal_monetary_total,
                'invoice_line' => $this->formatInvoiceLinesForTaxly(),
                // Add required fields for validation
                'payment_means' => [
                    [
                        'payment_means_code' => '10',
                        'payment_due_date' => $this->due_date,
                    ],
                ],
                'allowance_charge' => [
                    [
                        'charge_indicator' => true,
                        'amount' => $this->total_amount,
                    ],
                ],
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
            Log::debug('Invoice validation payload', ['payload' => $payload]);
            // call Taxly service for validation
            $cred = TaxlyCredential::first();
            $taxly = new TaxlyService($cred);

            // validate invoice structure
            $taxly->validateInvoice($payload);

            $this->message = 'Invoice validation successful! Ready to submit.';
            $this->dispatch('validation-success', message: 'Invoice structure is valid');
        } catch (Throwable $e) {
            $this->addError('validation', 'Validation failed: ' . $e->getMessage());
            $this->message = 'Validation failed. Please check your invoice data.';
            Log::error('Invoice validation error', ['error' => $e->getMessage()]);
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

            $cred = TaxlyCredential::first();
            $taxly = new TaxlyService($cred);

            // validate irn structure
            $taxly->validateIrn($payload);

            $this->message = 'IRN validation successful! Ready to submit.';
            $this->dispatch('validation-success', message: 'IRN structure is valid');
        } catch (Throwable $e) {
            $this->addError('validation', 'Validation failed: ' . $e->getMessage());
            $this->message = 'Validation failed. Please check your irn data.';
            Log::error('IRN validation error', ['error' => $e->getMessage()]);
        } finally {
            $this->validating = false;
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
     * Format invoice lines for Taxly API with proper data types
     */
    private function formatInvoiceLinesForTaxly(): array
    {
        return array_map(function ($line, $index) {
            return [
                'hsn_code' => $line['hsn_code'] ?? $this->hsn_code,
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

    /**
     * Generate a random 8-digit HSN code
     */
    private function generateHsnCode(): string
    {
        return strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
    }

    public function render()
    {
        return view('livewire.invoices.invoice-create');
    }
}
