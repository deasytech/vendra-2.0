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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->string('invoice_reference')->index(); // e.g. INV0001
            $table->string('irn')->nullable()->index(); // IRN returned by Taxly/FIRS: INV000002-...
            $table->date('issue_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('invoice_type_code')->nullable();
            $table->string('payment_status')->default('PENDING');
            $table->json('note')->nullable(); // encrypted note if necessary
            $table->json('payment_terms_note')->nullable();
            $table->json('accounting_supplier_party')->nullable();
            $table->json('accounting_customer_party')->nullable();
            $table->json('legal_monetary_total')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('transmitted')->default(false);
            $table->boolean('delivered')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'invoice_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
