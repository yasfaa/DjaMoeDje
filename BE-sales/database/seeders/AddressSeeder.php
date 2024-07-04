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
            'user_id' => 2, 
            'nama_penerima' => 'Udin',
            'nomor_telepon' => '089737485982',
            'jalan' => 'Jalan Cempaka Putih Barat XI No, 2',
            'kecamatan' => 'Cempaka Putih',
            'kota' => 'Central Jakarta',
            'provinsi' => 'Special Region of Jakarta',
            'kode_pos' => 10570,
            'latitude' => -6.1832899,
            'longitude' => 106.8654067
        ]);    
        
        DB::table('addresses')->insert([
            'user_id' => 1, 
            'nama_penerima' => 'Prijo Widodo',
            'nomor_telepon' => '0818510687',
            'jalan' => 'Jalan Wonocolo Gang IV',
            'kecamatan' => 'Wonocolo',
            'kota' => 'Surabaya',
            'provinsi' => 'East Java',
            'kode_pos' => 60237,
            'latitude' => -7.3177856624486,
            'longitude' => 112.73695707321
        ]);   
    }
}
