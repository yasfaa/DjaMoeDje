<?php

namespace App\Models;

use App\Models\CartItem;
use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_menu',
        'total',
        'deskripsi',
        'file_path',
    ];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function ingredient()
    {
        return $this->hasMany(Ingredient::class);
    }
}
