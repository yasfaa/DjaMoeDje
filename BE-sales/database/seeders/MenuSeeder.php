<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            ['nama_menu' => 'Rujak Cingur', 'total' => 27000, 'deskripsi' => 'Rujak cingur standard'],
            ['nama_menu' => 'Lontong Kupang', 'total' => 15000, 'deskripsi' => 'Lontong kupang dengan sate kupang'],
        ]);
    }
}
