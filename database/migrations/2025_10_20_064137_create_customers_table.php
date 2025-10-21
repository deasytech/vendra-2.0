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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Customer or company name
            $table->string('tin')->nullable(); // Tax Identification Number
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('business_description')->nullable();
            $table->string('street_name')->nullable();
            $table->string('city_name')->nullable();
            $table->string('postal_zone')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('NG');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
