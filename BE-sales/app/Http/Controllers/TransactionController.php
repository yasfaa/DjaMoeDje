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
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
                'courier_type' => $request->input('selectedCourier.courier_service_code')
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
            if ($transaction->status === 'pending') {
                $this->updatePaymentStatus($transaction);
            }
            if ($transaction->status === 'pending' && Carbon::parse($transaction->created_at)->diffInHours(Carbon::now()) > 24) {
                $transaction->update(['status' => 'expired']);
            }
        });

        $transactions = Transaction::with(['payment', 'cartItems.menu.menuPictures'])
            ->where('user_id', $userId)
            ->get();

        if ($statusFilter) {
            $transactions = $transactions->filter(function ($transaction) use ($statusFilter) {
                return $transaction->status === $statusFilter;
            });
        }

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

                    $customizations = $cartItem->ingredients->map(function ($ingredient) {
                        return [
                            'nama' => $ingredient->ingredient->nama,
                            'quantity' => $ingredient->quantity
                        ];
                    });

                    return [
                        'menu_id' => $menu->id,
                        'nama_menu' => $menu->nama_menu,
                        'quantity' => $cartItem->quantity,
                        'harga_menu' => $menu->total,
                        'customization' => $customizations,
                        'imagePath' => $imagePath,
                    ];
                }),
            ];
        });

        return response()->json(['order' => $response]);
    }

    public function getAdminOrders(Request $request)
    {
        $statusFilter = $request->query('status');

        $transactionsQuery = Transaction::with(['payment', 'cartItems.menu.menuPictures']);

        if ($statusFilter) {
            $transactionsQuery->where('status', $statusFilter);
        }

        $transactions = $transactionsQuery->get();

        $transactions->each(function ($transaction) {
            if ($transaction->status === 'pending') {
                $this->updatePaymentStatus($transaction);
            }
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
                'menu' => $transaction->cartItems->map(function ($cartItem) {
                    $menu = $cartItem->menu;
                    $imagePath = null;

                    $firstPicture = $menu->menuPictures->first();
                    if ($firstPicture) {
                        $fileName = $firstPicture->file_path;
                        $imagePath = asset('storage/menu_images/' . $fileName);
                    }

                    $customizations = $cartItem->ingredients->map(function ($ingredient) {
                        return [
                            'nama' => $ingredient->ingredient->nama,
                            'quantity' => $ingredient->quantity
                        ];
                    });

                    return [
                        'menu_id' => $menu->id,
                        'nama_menu' => $menu->nama_menu,
                        'quantity' => $cartItem->quantity,
                        'harga_menu' => $menu->total,
                        'customization' => $customizations,
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
            'tracking_id' => $transaction->courier->tracking_id,
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
            $transaction->payment->update(['payment_link' => null]);

        } catch (Exception $e) {
            Log::error('Error retrieving Midtrans order status: ' . $e->getMessage());
        }
    }

    public function adminCreateOrder($orderId)
    {
        DB::beginTransaction();

        try {
            $transaction = Transaction::with(['cartItems.menu', 'user', 'address', 'courier'])->findOrFail($orderId);


            $user = $transaction->user;
            $address = $transaction->address;
            $admin = Address::with('user')->whereHas('user', function ($query) {
                $query->where('role', 'Admin');
            })->first();

            $origin_address = implode(', ', array_filter([
                $admin->jalan,
                $admin->kecamatan,
                $admin->kota,
                $admin->provinsi,
                $admin->kode_pos
            ]));

            $destination_address = implode(', ', array_filter([
                $address->jalan,
                $address->kecamatan,
                $address->kota,
                $address->provinsi,
                $address->kode_pos
            ]));

            $dataForBiteship = [
                'shipper_contact_name' => $admin->nama_penerima,
                'shipper_contact_phone' => $admin->nomor_telepon,
                'shipper_contact_email' => $admin->user->email,
                'shipper_organization' => 'DjaMoeDje',
                'origin_contact_name' => $admin->nama_penerima,
                'origin_contact_phone' => $admin->nomor_telepon,
                'origin_address' => $origin_address,
                'origin_coordinate' => [
                    'latitude' => $admin->latitude,
                    'longitude' => $admin->longitude
                ],
                'destination_contact_name' => $address->nama_penerima,
                'destination_contact_phone' => $address->nomor_telepon,
                'destination_contact_email' => $user->email,
                'destination_address' => $destination_address,
                'destination_coordinate' => [
                    'latitude' => $address->latitude,
                    'longitude' => $address->longitude
                ],
                'courier_company' => 'paxel',
                'courier_type' => $transaction->courier->courier_type,
                'delivery_type' => 'now',
                // 'order_note' => $transaction->order_note,
                // 'metadata' => (array) $transaction->metadata,
                'items' => $transaction->cartItems->map(function ($cartItem) {
                    return [
                        'name' => $cartItem->menu->nama_menu,
                        'description' => $cartItem->menu->description,
                        'value' => $cartItem->customization_price,
                        'quantity' => $cartItem->quantity,
                        'height' => 10,
                        'length' => 10,
                        'weight' => 200,
                        'width' => 5
                    ];
                })->toArray()
            ];

            // echo json_encode($dataForBiteship, JSON_PRETTY_PRINT);
            $response = $this->biteship->createOrder($dataForBiteship);

            if (isset($response['id'])) {
                $transaction = Transaction::findOrFail($orderId);
                $transaction->status = 'packing';
                $transaction->save();

                $transaction = Transaction::updateOrCreate(
                    ['id' => $transaction->id],
                    [
                        'bsorder_id' => $response['id'],
                    ]
                );

                $courier = courier::updateOrCreate(
                    ['transaction_id' => $transaction->id],
                    [
                        'waybill_id' => $response['courier']['waybill_id'],
                        'tracking_id' => $response['courier']['tracking_id']
                    ]
                );

                DB::commit();
                return response()->json(['transaction' => $transaction, 'courier' => $courier, 'response' => $response], 201);
            } else {
                throw new Exception('Invalid response from Biteship');
            }
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function retrieveTracking($orderId)
    {
        try {
            $courier = courier::where('transaction_id', $orderId)->first();

            if (!$courier) {
                return response()->json(['status' => 'error', 'message' => 'Courier not found.'], 404);
            }

            $tracking = $courier->tracking_id;
            $trackingData = $this->biteship->retrieveTrackingData($tracking);

            if (!$trackingData) {
                return response()->json(['status' => 'error', 'message' => 'Tracking data not found.'], 404);
            }

            return response()->json(['status' => 'success', 'tracking' => $trackingData], 200);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function biteshipWebhook(Request $request)
    {
        $payload = $request->all();

        // Log incoming request for debugging
        Log::info('Incoming webhook request', ['payload' => $payload]);

        try {
            // Verifikasi webhook, jika Biteship menyediakan mekanisme verifikasi
            // Contoh: HMAC verification, shared secret, dll.

            if (isset($payload['courier_tracking_id']) && isset($payload['status'])) {
                $courier = Courier::where('tracking_id', $payload['courier_tracking_id'])->firstOrFail();

                $transaction = Transaction::findOrFail($courier->transaction_id);
                $transaction->status = $payload['status'];
                $transaction->save();

                Log::info('Webhook received and processed', ['payload' => $payload, 'transaction' => $transaction]);

                return response()->json(['message' => 'Webhook processed successfully', 'data' => $transaction], 200);
            } else {
                Log::error('Invalid payload received', ['payload' => $payload]);
                return response()->json(['message' => 'Invalid payload', 'payload' => $payload], 200);
            }
        } catch (Exception $e) {
            Log::error('Error processing webhook', ['error' => $e->getMessage(), 'payload' => $payload]);
            return response()->json(['message' => 'Error processing webhook', 'error' => $e->getMessage()], 200);
        }
    }
}
