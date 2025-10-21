<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('tin')->after('service_id')->nullable();
            $table->uuid('business_id')->after('tin')->nullable();
            $table->text('postal_address')->after('phone')->nullable();
            $table->string('slug')->after('legal_name')->nullable();
        });

        DB::table('organizations')->get()->each(function ($organization, $index) {
            $uniqueSlug = Str::slug($organization->legal_name . '-' . $organization->id);

            DB::table('organizations')
                ->where('id', $organization->id)
                ->update([
                    'slug' => $uniqueSlug,
                ]);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->unique('slug');
            $table->index('business_id');
            $table->dropColumn(['street_name', 'city_name', 'postal_zone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropUnique(['tin']);
            $table->dropUnique(['slug']);
            $table->dropUnique(['business_id']);
            $table->dropIndex(['business_id']);

            $table->dropColumn('tin');
            $table->dropColumn('business_id');
            $table->dropColumn('postal_address');
            $table->dropColumn('slug');

            $table->string('street_name')->after('phone')->nullable();
            $table->string('city_name')->after('street_name')->nullable();
            $table->string('postal_zone')->after('city_name')->nullable();
        });
    }
};
