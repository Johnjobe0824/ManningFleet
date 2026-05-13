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
            'user', 'role', 'permission', 'rank', 'vessel','junior_vessel_assignment', 'junior_higher_billet', 'completion_billet', 'slmc_endorsement',
            'exo_deliberation', 'cat2ExoPromotion', 'cat3ExoPromotion', 'exo_icas', 'cat2CoPromotionReassign', 'exo_reassign','position', 'sea_duty', 'document', 'training', 'profile', 'chipsReport',
        ];

        $actions = ['create', 'read', 'update', 'delete'];

        foreach ($resources as $resource) {
            foreach ($actions as $action) {
                // Avoid unnecessary permissions (e.g. no create/read/update for profile)
                if ($resource === 'profile' && $action !== 'read') {
                    continue;
                }

                Permission::firstOrCreate(['name' => "$action $resource"]);
            }
        }
    }
}