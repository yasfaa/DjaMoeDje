<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\Biteship;
use App\Services\Midtrans;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

            $transaction->update(['payment_link' => $paymentUrl]);

            foreach ($items as $item) {
                $cartItem = CartItem::find($item['cart_item_id']);

                if ($cartItem) {
                    $cartItem->update([
                        'order_id' => $transaction->id,
                    ]);
                }
            }

            Cart::where('user_id', auth()->id())
                ->delete();

            return response()->json(['paymentUrl' => $paymentUrl]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUserOrders(Request $request)
    {
        $userId = Auth::id();
        $statusFilter = $request->query('status');

        $transactionsQuery = Transaction::with(['cartItems.menu'])
            ->where('user_id', $userId);

        if ($statusFilter) {
            $transactionsQuery->where('status', $statusFilter);
        }

        $transactions = $transactionsQuery->get();

        $transactions->each(function ($transaction) {
            if ($transaction->status === 'pending' && Carbon::parse($transaction->created_at)->diffInHours(Carbon::now()) > 24) {
                $transaction->update(['status' => 'expired']);
            }
        });

        $response = $transactions->map(function ($transaction) {
            return [
                'id_transaksi' => $transaction->id,
                'no_pesanan' => $transaction->transaction_id,
                'status' => $transaction->status,
                'total' => $transaction->total,
                'created_at' => $transaction->created_at,
                'payment' => $transaction->payment_link,
                'menu' => $transaction->cartItems->map(function ($cartItem) {
                    $formattedMenu = [
                        'menu_id' =>$cartItem->menu->id,
                        'nama_menu' => $cartItem->menu->nama_menu,
                        'quantity' => $cartItem->quantity,
                        'harga_menu' => $cartItem->customization_price,
                        'imagePath' => null,
                    ];

                    if (!is_null($cartItem->menu->file_path)) {
                        $paths = json_decode($cartItem->menu->file_path, true);
                        if (!empty($paths)) {
                            $url = asset(str_replace('public/', 'storage/', $paths[0]));
                            $formattedMenu['imagePath'] = $url;
                        }
                    }

                    return $formattedMenu;
                }),
            ];
        });

        return response()->json(['order' => $response]);
    }
}
