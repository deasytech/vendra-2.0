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
        Schema::table('invoice_lines', function (Blueprint $table) {
            // Change invoiced_quantity from integer to decimal with 4 decimal places
            $table->decimal('invoiced_quantity', 15, 4)->default(1.0000)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            // Revert back to integer if needed
            $table->integer('invoiced_quantity')->default(1)->change();
        });
    }
};
