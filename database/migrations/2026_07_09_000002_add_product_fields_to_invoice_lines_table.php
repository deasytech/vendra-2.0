<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('invoice_id')->constrained('products')->nullOnDelete();
            $table->string('isic_code')->nullable()->after('hsn_code');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_id');
            $table->dropColumn('isic_code');
        });
    }
};
