<?php

namespace App\Models;

use App\Models\User;
use App\Models\Address;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'order_uuid',
        'status',
        'total'
    ];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'order_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    public function courier()
    {
        return $this->hasOne(courier::class);
    }

    public function payment()
    {
        return $this->hasOne(payment::class);
    }
}
