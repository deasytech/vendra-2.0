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
    Schema::table('invoice_transmissions', function (Blueprint $table) {
      $table->string('irn')->nullable()->after('invoice_id');
      $table->text('message')->nullable()->after('status');
      $table->text('error')->nullable()->after('message');
      $table->timestamp('transmitted_at')->nullable()->after('error');

      // Add index for faster lookup by IRN
      $table->index('irn');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('invoice_transmissions', function (Blueprint $table) {
      $table->dropIndex(['irn']);
      $table->dropColumn(['irn', 'message', 'error', 'transmitted_at']);
    });
  }
};
