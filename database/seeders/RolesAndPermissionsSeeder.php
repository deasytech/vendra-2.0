<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
  public function run(): void
  {
    $permissions = [
      'view invoices',
      'create invoices',
      'submit invoices',
      'manage tenants',
      'manage businesses',
      'manage credentials'
    ];

    foreach ($permissions as $perm) {
      Permission::firstOrCreate(['name' => $perm]);
    }

    $roles = [
      'super admin' => Permission::all(),
      'tenant admin' => Permission::whereIn('name', ['view invoices', 'create invoices', 'submit invoices'])->get(),
      'tenant user' => Permission::where('name', 'view invoices')->get(),
    ];

    foreach ($roles as $name => $perms) {
      $role = Role::firstOrCreate(['name' => $name]);
      $role->syncPermissions($perms);
    }
  }
}
