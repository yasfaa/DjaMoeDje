<?php

namespace App\Models;

use App\Models\CartItem;
use App\Models\Ingredient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CartItemIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_item_id',
        'ingredient_id',
        'quantity'
    ];

    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }
}
