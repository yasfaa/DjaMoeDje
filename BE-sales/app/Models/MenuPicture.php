<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuPicture extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'file_path',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
