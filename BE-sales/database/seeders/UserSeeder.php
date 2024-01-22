<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('iniakunadmin'),
            'role' => 'Admin',
        ]);

        DB::table('users')->insert([
            'name' => 'yogi',
            'email' => 'yogi@yogi.com',
            'password' => bcrypt('yogiyogi'),
        ]);

        DB::table('addresses')->insert([
            'user_id' => '3',
            'nama_penerima' => 'Rizky Agung Prayogi',
            'nomor_telepon' => '08524637895',
            'jalan' => 'Jalan Raya Wonocolo No. 49',
            'kelurahan' => 'Sememi',
            'kecamatan' => 'Lakarsantri',
            'kota' => 'Surabaya',
            'kode_pos' => '637895',
        ]);
    }
}