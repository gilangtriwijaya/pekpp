<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AnalyticsRolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'analyst', 'viewer'];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r]);
        }
    }
}
