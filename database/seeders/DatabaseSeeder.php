<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TaxlyCredential;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // TaxlyCredential::create([
        //     'auth_type' => 'api_key',
        //     'api_key' => config('services.taxly.api_key'),
        //     'base_url' => config('services.taxly.base_url'),
        // ]);

        // Call the SettingsSeeder to populate default settings
        // $this->call(SettingsSeeder::class);

        // Create Super Admin user and assign role
        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'super@admin.com',
            'is_landlord' => 1,
            'password' => Hash::make('Zr5@PmN8#kHv3$Lw'),
        ]);

        $superAdmin->assignRole('super admin');
    }
}
