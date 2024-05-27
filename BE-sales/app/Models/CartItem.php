<?php

namespace App\Models;

use App\Models\Cart;
use App\Models\Menu;
use App\Models\CartItemIngredient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable  =[
        'cart_id',
        'menu_id',
        'quantity',
        'harga_item',
        'select',
        'customization'

    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function ingredients()
    {
        return $this->hasMany(CartItemIngredient::class);
    }
    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
