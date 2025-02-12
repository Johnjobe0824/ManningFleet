<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rank;
use Carbon\Carbon;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Rank::insert([
            ['level' => 1, 'name' => 'ADM', 'type' => Rank::OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 2, 'name' => 'VADM', 'type' => Rank::OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 3, 'name' => 'RADM', 'type' => Rank::OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 4, 'name' => 'COMMO', 'type' => Rank::OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 5, 'name' => 'CAPT', 'type' => Rank::OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 6, 'name' => 'CDR', 'type' => Rank::OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 7, 'name' => 'LCDR', 'type' => Rank::OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 8, 'name' => 'LT', 'type' => Rank::OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 9, 'name' => 'LTJG', 'type' => Rank::OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 10, 'name' => 'ENS', 'type' => Rank::OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 11, 'name' => 'P/ENS', 'type' => Rank::OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')]
        ]);

        Rank::insert([
            ['level' => 12, 'name' => 'MCPO', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 13, 'name' => 'SCPO', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 14, 'name' => 'CPO', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 15, 'name' => 'PO1', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 16, 'name' => 'PO2', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 17, 'name' => 'PO3', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 18, 'name' => 'SN1', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 19, 'name' => 'SW1', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 20, 'name' => 'SN2', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 21, 'name' => 'SW2', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 22, 'name' => 'ASN', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 23, 'name' => 'ASW', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['level' => 24, 'name' => 'CGNO', 'type' => Rank::NON_OFFICER, 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')]
        ]);
    }
}
