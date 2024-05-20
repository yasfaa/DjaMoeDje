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
        ['nama' => 'Cingur', 'menu_id' => 1, 'harga' => 5000], 
        ['nama' => 'Tahu', 'menu_id' => 1, 'harga' => 0],
        ['nama' => 'Tempe', 'menu_id' => 1, 'harga' => 0],
        ['nama' => 'Lombok', 'menu_id' => 1, 'harga' => 0],
        ['nama' => 'Kupang', 'menu_id' => 1, 'harga' => 0],
        ['nama' => 'Lontong', 'menu_id' => 1, 'harga' => 2500],
        ['nama' => 'Lento', 'menu_id' => 2, 'harga' => 0],
        ['nama' => 'Sate Kerang', 'menu_id' => 2, 'harga' => 0],
        ['nama' => 'Kangkung', 'menu_id' => 1, 'harga' => 0],
        ['nama' => 'Tauge', 'menu_id' => 1, 'harga' => 0],
        ['nama' => 'Bengkoang', 'menu_id' => 1, 'harga' => 0],
        ['nama' => 'Nanas', 'menu_id' => 1, 'harga' => 0],
    ]);
}

}
