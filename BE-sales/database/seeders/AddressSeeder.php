<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('addresses')->insert([
            'user_id' => '2', // Use an existing user_id from the users table
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
