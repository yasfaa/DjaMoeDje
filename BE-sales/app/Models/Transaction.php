<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
