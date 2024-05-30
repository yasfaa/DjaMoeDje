<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasApiTokens, HasFactory;
    protected $fillable = [
        'kode_alamat',
        'nama_penerima',
        'nomor_telepon',
        'jalan',
        'kecamatan',
        'kecamatan',
        'kota',
        'provinsi',
        'kode_pos',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}