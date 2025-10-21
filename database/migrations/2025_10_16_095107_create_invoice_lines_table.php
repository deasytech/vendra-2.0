<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->string('hsn_code')->nullable();
            $table->string('product_category')->nullable();
            $table->decimal('discount_rate', 8, 4)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('fee_rate', 8, 4)->default(0);
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->integer('invoiced_quantity')->default(1);
            $table->decimal('line_extension_amount', 15, 2)->default(0);
            $table->json('item')->nullable();
            $table->json('price')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
