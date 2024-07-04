<?php

namespace App\Services;

use Midtrans\Snap;
use Midtrans\Config;

class Midtrans
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        Config::$isSanitized = env('MIDTRANS_IS_SANITIZED');
        Config::$is3ds = env('MIDTRANS_IS_3DS');
    }

    public function createTransaction($orderDetails)
    {
        return Snap::createTransaction($orderDetails);
    }
}