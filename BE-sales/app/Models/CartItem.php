<?php

namespace App\Models;

use App\Models\Cart;
use App\Models\CartItemIngredient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable  =[
        'menu_id',
        'quantity',
        'customization'
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function ingredients()
    {
        return $this->hasMany(CartItemIngredient::class);
    }
}
