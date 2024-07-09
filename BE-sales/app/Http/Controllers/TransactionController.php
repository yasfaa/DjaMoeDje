<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Cart;
use GuzzleHttp\Client;
use App\Models\Address;
use App\Models\courier;
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

        $user = Auth::user();

        DB::beginTransaction();

        try {
            $transaction_uuid = (string) Str::uuid();
            $totalPayment = $request->input('amount') + $request->input('selectedCourier.price');
            $items = $request->input('items');

            $transaction = Transaction::create([
                'user_id' => $user->id,
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

            $addresses = Address::find($request->input('address_id'));

            $orderDetails = [
                'transaction_details' => [
                    'order_id' => (string) $transaction_uuid,
                    'gross_amount' => (int) $totalPayment,
                ],
                'customer_details' => [
                    'first_name' => $addresses->nama_penerima,
                    'last_name' => '',
                    'email' => $user->email,
                    'phone' => $addresses->nomor_telepon,
                ],
            ];

            $snapToken = $this->midtrans->createTransaction($orderDetails);
            $paymentUrl = $snapToken->redirect_url;

            payment::create([
                'transaction_id' => $transactionId,
                'payment_uuid' => $transaction_uuid,
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
            Cart::where('user_id', $user->id)->delete();
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

        $transactionsQuery = Transaction::with(['payment', 'cartItems.menu.menuPictures'])
            ->where('user_id', $userId);

        if ($statusFilter) {
            $transactionsQuery->where('status', $statusFilter);
        }

        $transactions = $transactionsQuery->get();

        $transactions->each(function ($transaction) {
            $this->updatePaymentStatus($transaction);
        });

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
                    $menu = $cartItem->menu;
                    $imagePath = null;

                    $firstPicture = $menu->menuPictures->first();
                    if ($firstPicture) {
                        $fileName = $firstPicture->file_path;
                        $imagePath = asset('storage/menu_images/' . $fileName);
                    }

                    return [
                        'menu_id' => $menu->id,
                        'nama_menu' => $menu->nama_menu,
                        'quantity' => $cartItem->quantity,
                        'harga_menu' => $menu->total,
                        'imagePath' => $imagePath,
                    ];
                }),
            ];
        });

        return response()->json(['order' => $response]);
    }


    public function detailOrder($orderId)
    {
        $transaction = Transaction::with(['cartItems.menu.menuPictures', 'address', 'payment', 'courier'])
            ->where('id', $orderId)
            ->first();

        if (!$transaction) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        $address = $transaction->address;
        $fullAddress = $address
            ? "{$address->jalan}, {$address->kota}, {$address->provinsi}, {$address->kode_pos}"
            : 'Alamat tidak tersedia';

        $response = [
            'id_transaksi' => $transaction->id,
            'no_pesanan' => $transaction->payment->payment_uuid,
            'status' => $transaction->status,
            'total' => $transaction->total,
            'biaya_kirim' => $transaction->courier->shipping_cost,
            'created_at' => $transaction->created_at,
            'payment' => $transaction->payment->payment_link,
            'alamat' => $fullAddress,
            'no_resi' => $transaction->courier->waybill_id,
            'detail_kurir' => $transaction->courier_details,
            'menu' => $transaction->cartItems->map(function ($cartItem) {
                $menu = $cartItem->menu;
                $formattedMenu = [
                    'menu_id' => $menu->id,
                    'nama_menu' => $menu->nama_menu,
                    'quantity' => $cartItem->quantity,
                    'harga_menu' => $cartItem->customization_price,
                    'total_menu' => $cartItem->harga_item,
                    'imagePath' => null,
                ];

                $firstPicture = $menu->menuPictures->first();
                if ($firstPicture) {
                    $fileName = $firstPicture->file_path;
                    $url = asset('storage/menu_images/' . $fileName);
                    $formattedMenu['imagePath'] = $url;
                }

                return $formattedMenu;
            }),
        ];

        return response()->json(['order' => $response]);
    }


    private function updatePaymentStatus($transaction)
    {
        try {
            $client = new Client();
            $orderId = $transaction->payment->payment_uuid;
            $url = "https://api.sandbox.midtrans.com/v2/{$orderId}/status";
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . base64_encode(env('MIDTRANS_SERVER_KEY') . ':'),
                ]
            ]);

            $status = json_decode($response->getBody()->getContents(), true);

            if ($status['status_code'] === "200" && $status['transaction_status'] === "settlement") {
                $transaction->update(['status' => 'process']);
            }
        } catch (Exception $e) {
            Log::error('Error retrieving Midtrans order status: ' . $e->getMessage());
        }
    }
}
