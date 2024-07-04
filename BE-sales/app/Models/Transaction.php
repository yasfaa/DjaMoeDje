<?php

namespace App\Models;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'total',
        'shipping_cost',
        'transaction_id',
        'bsorder_id',
        'waybill_id',
        'status',
        'courier_details',
        'items',
        'payment_link'

    ];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'order_id', 'id');
    }
}
