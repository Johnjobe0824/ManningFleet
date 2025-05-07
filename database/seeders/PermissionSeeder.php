<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'delete user']);
        Permission::create(['name' => 'update user']);
        Permission::create(['name' => 'read user']);
        Permission::create(['name' => 'create user']);

        Permission::create(['name' => 'delete role']);
        Permission::create(['name' => 'update role']);
        Permission::create(['name' => 'read role']);
        Permission::create(['name' => 'create role']);

        Permission::create(['name' => 'delete permission']);
        Permission::create(['name' => 'update permission']);
        Permission::create(['name' => 'read permission']);
        Permission::create(['name' => 'create permission']);

        Permission::create(['name' => 'delete rank']);
        Permission::create(['name' => 'update rank']);
        Permission::create(['name' => 'read rank']);
        Permission::create(['name' => 'create rank']);

        Permission::create(['name' => 'delete vessel']);
        Permission::create(['name' => 'update vessel']);
        Permission::create(['name' => 'read vessel']);
        Permission::create(['name' => 'create vessel']);

        ermission::create(['name' => 'delete position']);
        Permission::create(['name' => 'update position']);
        Permission::create(['name' => 'read position']);
        Permission::create(['name' => 'create position']);

        ermission::create(['name' => 'delete profile']);
    }
}
