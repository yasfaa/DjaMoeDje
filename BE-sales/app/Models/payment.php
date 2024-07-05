<?php

namespace App\Models;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'payment_uuid',
        'total',
        'payment_link'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
