<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'service_category')) {
                $table->string('service_category')->nullable()->after('isic_code');
            }
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_lines', 'service_category')) {
                $table->string('service_category')->nullable()->after('isic_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'service_category')) {
                $table->dropColumn('service_category');
            }
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_lines', 'service_category')) {
                $table->dropColumn('service_category');
            }
        });
    }
};
