<?php

namespace App\Models;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class courier extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'shipping_cost',
        'waybill_id',
        'courier_type',
        'tracking_id'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
