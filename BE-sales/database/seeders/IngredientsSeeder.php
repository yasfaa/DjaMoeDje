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
            ['nama_bahan' => 'Cingur'],
            ['nama_bahan' => 'Tahu'],
            ['nama_bahan' => 'Tempe'],
            ['nama_bahan' => 'Lombok'],
            ['nama_bahan' => 'Kupang'],
            ['nama_bahan' => 'Lontong'],
            ['nama_bahan' => 'Lento'],
            ['nama_bahan' => 'Sate Kerang'],
            ['nama_bahan' => 'Kangkung'],
            ['nama_bahan' => 'Tauge'],
            ['nama_bahan' => 'Bengkoang'],
            ['nama_bahan' => 'Nanas'],
        ]);
    }
}
