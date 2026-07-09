<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ProductsIndex extends Component
{
    public function deleteProduct($productId)
    {
        $product = Product::findOrFail($productId);

        if ($product->invoiceLines()->exists()) {
            $product->update(['is_active' => false]);
            session()->flash('message', 'Product has been archived because it is already used on invoices.');
            return;
        }

        $product->delete();
        session()->flash('message', 'Product deleted successfully.');
    }

    public function render()
    {
        $products = Product::where('tenant_id', Auth::user()->tenant_id)
            ->latest()
            ->paginate(10);

        return view('livewire.products.products-index', [
            'products' => $products,
        ]);
    }
}
