<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Services\TaxlyResourceOptions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProductCreate extends Component
{
    public $name = '';
    public $description = '';
    public $sku = '';
    public $hsn_code = '';
    public $isic_code = '';
    public $product_category = '';
    public $service_category = '';
    public $unit_price = 0;
    public $currency_code = 'NGN';
    public $unit_of_measure = 'KGM';
    public $is_active = true;

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'sku' => 'nullable|string|max:100',
        'hsn_code' => 'nullable|string|max:50|required_without:isic_code',
        'isic_code' => 'nullable|string|max:50|required_without:hsn_code',
        'unit_price' => 'required|numeric|min:0',
        'currency_code' => 'required|string|size:3',
        'unit_of_measure' => 'required|string|max:50',
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'hsn_code.required_without' => 'Enter either an HSN code or an ISIC code for Taxly invoice submission.',
        'isic_code.required_without' => 'Enter either an HSN code or an ISIC code for Taxly invoice submission.',
    ];

    public function save()
    {
        $this->validate();
        $this->syncClassificationCategories();

        Product::create($this->payload());

        session()->flash('message', 'Product created successfully.');

        return redirect()->route('products.index');
    }

    private function payload(): array
    {
        return [
            'tenant_id' => Auth::user()->tenant_id,
            'organization_id' => Auth::user()->organization_id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku ?: null,
            'hsn_code' => $this->hsn_code ?: null,
            'isic_code' => $this->isic_code ?: null,
            'product_category' => $this->product_category ?: null,
            'service_category' => $this->service_category ?: null,
            'unit_price' => $this->unit_price,
            'currency_code' => strtoupper($this->currency_code),
            'unit_of_measure' => $this->unit_of_measure,
            'is_active' => (bool) $this->is_active,
        ];
    }

    public function updatedHsnCode(): void
    {
        $this->product_category = TaxlyResourceOptions::hsCodeDescription($this->hsn_code) ?? '';
    }

    public function updatedIsicCode(): void
    {
        $this->service_category = TaxlyResourceOptions::serviceCodeDescription($this->isic_code) ?? '';
    }

    private function syncClassificationCategories(): void
    {
        $this->product_category = TaxlyResourceOptions::hsCodeDescription($this->hsn_code) ?? '';
        $this->service_category = TaxlyResourceOptions::serviceCodeDescription($this->isic_code) ?? '';
    }

    public function render()
    {
        return view('livewire.products.product-create', [
            'hs_codes' => TaxlyResourceOptions::hsCodes(),
            'service_codes' => TaxlyResourceOptions::serviceCodes(),
            'unit_codes' => TaxlyResourceOptions::quantityCodes(),
        ]);
    }
}
