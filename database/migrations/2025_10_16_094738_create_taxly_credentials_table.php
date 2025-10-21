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
        Schema::create('taxly_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('auth_type')->default('api_key'); // 'api_key' or 'token'
            $table->string('api_key')->nullable();
            $table->text('token')->nullable(); // bearer token
            $table->timestamp('token_expires_at')->nullable();
            $table->string('base_url')->default(config('services.taxly.base_url', 'https://taxly.ng'));
            $table->text('meta')->nullable(); // json meta
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxly_credentials');
    }
};
