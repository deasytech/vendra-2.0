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
        Schema::table('taxly_credentials', function (Blueprint $table) {
            $table->string('integrator_contact_email')->nullable()->after('integrator_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taxly_credentials', function (Blueprint $table) {
            $table->dropColumn('integrator_contact_email');
        });
    }
};
