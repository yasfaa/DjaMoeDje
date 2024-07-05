<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('menus')->insert([
            [
                'id' => 1,
                'nama_menu' => 'Rujak Cingur',
                'total' => 27000,
                'deskripsi' => 'Rujak cingur standard',
                'file_path' => json_encode([
                    "public/menu_images/Rujak_cingur.jpg"
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'nama_menu' => 'Lontong Kupang',
                'total' => 15000,
                'deskripsi' => 'Lontong kupang dengan sate kupang',
                'file_path' => json_encode([
                    "public/menu_images/lontong_kupang.png"
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'nama_menu' => 'Tahu Telur',
                'total' => 10000,
                'deskripsi' => 'Tahu telur khas surabaya dengan petis mahal',
                'file_path' => json_encode([
                    "public/menu_images/RKdDiJECl32up3xMVDaBOJb8OyPFJgt0F26qBc1O.jpg",
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
