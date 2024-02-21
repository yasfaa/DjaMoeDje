<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('orders')->insert([
            'user_id' => 3, // Replace with the actual user ID
            'tanggal_pesan' => now(),
            'total_harga' => 25000, // Replace with the actual total harga
            'status_pesanan' => 'Dalam Proses',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
