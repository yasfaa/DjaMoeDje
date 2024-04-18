<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngredientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ingredients')->insert([
            ['nama_bahan' => 'Cingur','menu_id' => 1],
            ['nama_bahan' => 'Tahu','menu_id' => 1],
            ['nama_bahan' => 'Tempe','menu_id' => 1],
            ['nama_bahan' => 'Lombok','menu_id' => 1],
            ['nama_bahan' => 'Kupang','menu_id' => 1],
            ['nama_bahan' => 'Lontong','menu_id' => 1],
            ['nama_bahan' => 'Lento','menu_id' => 2],
            ['nama_bahan' => 'Sate Kerang','menu_id' => 2],
            ['nama_bahan' => 'Kangkung','menu_id' => 1],
            ['nama_bahan' => 'Tauge','menu_id' => 1],
            ['nama_bahan' => 'Bengkoang','menu_id' => 1],
            ['nama_bahan' => 'Nanas','menu_id' => 1],
        ]);
    }
}
