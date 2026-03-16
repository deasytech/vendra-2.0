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
    Schema::table('settings', function (Blueprint $table) {
      $table->foreignId('tenant_id')->nullable()->after('metadata')->constrained()->nullOnDelete();
      $table->foreignId('organization_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
      $table->foreignId('user_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();

      $table->dropUnique(['key']);
      $table->unique(['key', 'tenant_id', 'organization_id', 'user_id'], 'settings_scope_unique');
      $table->index(['tenant_id', 'organization_id', 'user_id'], 'settings_scope_index');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('settings', function (Blueprint $table) {
      $table->dropUnique('settings_scope_unique');
      $table->dropIndex('settings_scope_index');
      $table->unique('key');

      $table->dropConstrainedForeignId('user_id');
      $table->dropConstrainedForeignId('organization_id');
      $table->dropConstrainedForeignId('tenant_id');
    });
  }
};
