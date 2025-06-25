<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            'user', 'role', 'permission', 'rank', 'vessel',
            'position', 'sea_duty', 'document', 'training', 'profile', 'chipsReport',
        ];

        $actions = ['create', 'read', 'update', 'delete'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                // Avoid unnecessary permissions (e.g. no create/read/update for profile)
                if ($resource === 'profile' && $action !== 'delete') {
                    continue;
                }

                Permission::firstOrCreate(['name' => "$action $resource"]);
            }
        }
    }
}