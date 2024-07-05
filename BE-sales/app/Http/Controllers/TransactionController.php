<?php

namespace App\Http\Controllers;

use App\Models\courier;
use Exception;
use App\Models\Cart;
use GuzzleHttp\Client;
use App\Models\payment;
use App\Models\CartItem;
use App\Services\Biteship;
use App\Services\Midtrans;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        DB::beginTransaction();

        try {
            $transaction_uuid = (string) Str::uuid();
            $totalPayment = $request->input('amount') + $request->input('selectedCourier.price');
            $items = $request->input('items');

            $transaction = Transaction::create([
                'user_id' => auth()->id(),
                'total' => $totalPayment,
                'address_id' => $request->input('address_id'),
                'status' => 'pending',
            ]);

            $transactionId = $transaction->id;

            if (!$transactionId) {
                throw new Exception('Failed to create transaction.');
            }

            courier::create([
                'transaction_id' => $transactionId,
                'shipping_cost' => $request->input('selectedCourier.price'),
            ]);

            $orderDetails = [
                'transaction_details' => [
                    'order_id' => (string) $transactionId, 
                    'gross_amount' => (int) $totalPayment,
                ],
                'customer_details' => [
                    'first_name' => $request->input('first_name'),
                    'last_name' => $request->input('last_name'),
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone'),
                ],
            ];

            $snapToken = $this->midtrans->createTransaction($orderDetails);
            $paymentUrl = $snapToken->redirect_url;

            payment::create([
                'transaction_id' => $transactionId,
                'payment_uuid' => $transaction_uuid,
                'total' => $totalPayment,
                'payment_link' => $paymentUrl,
            ]);

            foreach ($items as $item) {
                $cartItem = CartItem::find($item['cart_item_id']);

                if ($cartItem) {
                    $cartItem->update([
                        'order_id' => $transactionId,
                    ]);
                }
            }
            Cart::where('user_id', auth()->id())->delete();
            DB::commit();

            return response()->json(['paymentUrl' => $paymentUrl]);
        } catch (Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getUserOrders(Request $request)
    {
        $userId = Auth::id();
        $statusFilter = $request->query('status');

        $transactionsQuery = Transaction::with(['payment','cartItems.menu'])
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
                'no_pesanan' => $transaction->payment->payment_uuid,
                'status' => $transaction->status,
                'total' => $transaction->total,
                'created_at' => $transaction->created_at,
                'payment' => $transaction->payment->payment_link,
                'menu' => $transaction->cartItems->map(function ($cartItem) {
                    $formattedMenu = [
                        'menu_id' => $cartItem->menu->id,
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


    public function detailOrder($orderId)
    {

        $transactionsQuery = Transaction::with(['cartItems.menu', 'address'])
            ->where('id', $orderId);

        $transactions = $transactionsQuery->get();

        $response = $transactions->map(function ($transaction) {
            $address = $transaction->address;
            $fullAddress = $address
                ? "{$address->jalan}, {$address->kota}, {$address->provinsi}, {$address->kode_pos}"
                : 'Alamat tidak tersedia';

            return [
                'id_transaksi' => $transaction->id,
                'no_pesanan' => $transaction->transaction_id,
                'status' => $transaction->status,
                'total' => $transaction->total,
                'biaya_kirim' => $transaction->shipping_cost,
                'created_at' => $transaction->created_at,
                'payment' => $transaction->payment_link,
                'alamat' => $fullAddress,
                'no_resi' => $transaction->waybill_id,
                'detail_kurir' => $transaction->courier_details,
                'menu' => $transaction->cartItems->map(function ($cartItem) {
                    $formattedMenu = [
                        'menu_id' => $cartItem->menu->id,
                        'nama_menu' => $cartItem->menu->nama_menu,
                        'quantity' => $cartItem->quantity,
                        'harga_menu' => $cartItem->customization_price,
                        'total_menu' => $cartItem->harga_item,
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

    public function getOrderStatus(Request $request)
    {
        $orderId = $request->query('order_id');
        if (!$orderId) {
            return response()->json(['error' => 'Order ID is required'], 400);
        }

        $client = new Client();
        $url = "https://api.sandbox.midtrans.com/v2/{$orderId}/status";

        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode(env('MIDTRANS_SERVER_KEY') . ':'),
                ]
            ]);

            $status = json_decode($response->getBody()->getContents(), true);

            if ($status['status_code'] === "200" && $status['transaction_status'] === "capture") {
                $transaction = Transaction::where('transaction_id', $status['order_id'])->first();
                if ($transaction) {
                    $transaction->status = 'process';
                    $transaction->save();
                }
            }

            return response()->json($status);
        } catch (Exception $e) {
            Log::error('Error retrieving Midtrans order status: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to retrieve order status'], 500);
        }
    }
}
