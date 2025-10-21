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
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('transmitted');

            // Add new enum column
            $table->enum('transmit', [
                'PENDING',
                'TRANSMITTING',
                'TRANSMITTED',
                'ACKNOWLEDGED',
                'FAILED',
            ])->default('TRANSMITTING')->after('metadata');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('transmit');

            // Restore old column
            $table->boolean('transmitted')->default(false)->after('metadata');
        });
    }
};
