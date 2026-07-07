<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('regions')->insert([
            ['id' => 1, 'name' => 'Nhật Bản', 'shortname' => 'JP', 'description' => null],
            ['id' => 2, 'name' => 'Ngoại Quốc', 'shortname' => 'EN', 'description' => null],
            ['id' => 3, 'name' => 'Indonesia', 'shortname' => 'ID', 'description' => null],
            ['id' => 4, 'name' => 'Dev_is', 'shortname' => 'DV', 'description' => null],
        ]);
    }
}
