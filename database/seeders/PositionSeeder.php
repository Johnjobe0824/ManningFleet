<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $now = Carbon::now();

        $positions = [
            ['id' => 1, 'code' => 'O-4 CO',    'name' => 'Commanding Officer'],
            ['id' => 2, 'code' => 'O-3 EXO',   'name' => 'Executive Officer'],
            ['id' => 3, 'code' => 'O-2 ENGRO', 'name' => 'Junior Officer 2 - Engineering Officer'],
            ['id' => 4, 'code' => 'O-2 DGO',   'name' => 'Junior Officer 2 - Deck and Gunnery Officer'],
            ['id' => 5, 'code' => 'O-1 ENGRO', 'name' => 'Junior Officer 1 - Engineering Officer'],
            ['id' => 6, 'code' => 'O-1 MSO',   'name' => 'Junior Officer 1 - Mess and Supply Officer']
        ];

        foreach ($positions as $position) {
            DB::table('positions')->updateOrInsert(
                ['id' => $position['id']], // Prevent duplicate seeding
                [
                    'code'       => $position['code'],
                    'name'       => $position['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
