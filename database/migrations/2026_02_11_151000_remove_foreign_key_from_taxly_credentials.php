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
      // Drop the foreign key constraint
      $table->dropForeign(['tenant_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('taxly_credentials', function (Blueprint $table) {
      // Re-add the foreign key constraint
      $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();
    });
  }
};
