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
      $table->foreignId('organization_id')->nullable()->after('tenant_id');
      $table->string('tenant_name')->nullable()->after('tenant_id');
      $table->string('api_key_id')->nullable()->after('api_key');
      $table->json('api_key_permissions')->nullable()->after('api_key_id');
      $table->boolean('is_integrator')->default(false)->after('base_url');
      $table->string('integrator_status')->default('pending')->after('is_integrator');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('taxly_credentials', function (Blueprint $table) {
      $table->dropColumn([
        'organization_id',
        'tenant_name',
        'api_key_id',
        'api_key_permissions',
        'is_integrator',
        'integrator_status',
      ]);
    });
  }
};
