<?php

namespace App\Models;

use App\Models\Menu;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'menu_id',
        'harga'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public $timestamps = false;
}
