<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use Exception;
use App\Models\Cart;
use App\Services\Biteship;
use App\Services\Midtrans;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected $biteship;
    protected $midtrans;


    public function __construct(Biteship $biteship, Midtrans $midtrans)
    {
        $this->biteship = $biteship;
        $this->midtrans = $midtrans;
    }

    public function createOrder(Request $request)
    {
        $transactionId = (string) Str::uuid();

        $totalPayment = $request->input('amount') + $request->input('selectedCourier.price');

        $items = $request->input('items');

        $transaction = Transaction::create([
            'user_id' => auth()->id(),
            'total' => $totalPayment,
            'address_id' => $request->input('address_id'),
            'shipping_cost' => $request->input('selectedCourier.price'),
            'status' => 'pending',
            'courier_details' => json_encode($request->input('selectedCourier')),
            'items' => json_encode($items),
            'transaction_id' => $transactionId,
        ]);
        $transactionId = $transaction->id;

        $orderDetails = [
            'transaction_details' => [
                'order_id' => $transactionId,
                'gross_amount' => $totalPayment,
            ],
            'customer_details' => [
                'first_name' => $request->input('first_name'),
                'last_name' => $request->input('last_name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
            ],
        ];

        try {
            $snapToken = $this->midtrans->createTransaction($orderDetails);
            $paymentUrl = $snapToken->redirect_url;

            $transaction->update(['link' => $paymentUrl]);

            foreach ($items as $item) {
                $cartItem = CartItem::find($item['cart_item_id']);

                if ($cartItem) {
                    $cartItem->update([
                        'order_id' => $transaction->id,
                    ]);
                }
            }

            Cart::where('user_id', auth()->id())
                ->whereIn('buku_id', array_column($items, 'buku_id'))
                ->delete();

            return response()->json(['paymentUrl' => $paymentUrl]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
