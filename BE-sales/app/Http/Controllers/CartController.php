<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $menu = Menu::findOrFail($request->menu_id);

        // Cari atau buat cart untuk pengguna dengan status 'pending'
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
            ['harga' => 0]
        );

        // Cari apakah menu sudah ada di cart
        $existingCartItem = $cart->items()->where('menu_id', $request->menu_id)->first();

        if ($existingCartItem) {
            // Jika item sudah ada di cart, tambahkan kuantitasnya
            $existingCartItem->quantity += $request->quantity;
            $existingCartItem->harga_item = $menu->total * $existingCartItem->quantity;
            $existingCartItem->save();
        } else {
            // Jika item belum ada di cart, tambahkan item baru
            $newCartItem = $cart->items()->create([
                'menu_id' => $request->menu_id,
                'quantity' => $request->quantity,
                'harga_item' => $menu->total * $request->quantity,
                'customizations' => null,
            ]);
        }

        // Perbarui total harga cart
        $cart->harga = $cart->items()->where('select', '1')->sum('harga_item');
        $cart->save();


        return response()->json($cart->load('items.menu'), 201);
    }

    public function index()
    {
        $user = Auth::user();

        // Ambil cart dengan status 'pending' untuk user yang sedang login
        $cart = Cart::with('items.menu')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart is empty'], 404);
        }
        $totalHarga = $cart->harga;

        $cartContents = $cart->items->map(function ($item) {
            $menu = $item->menu;
            $imagePath = null;

            if (!is_null($menu->file_path)) {
                $paths = json_decode($menu->file_path, true);
                $imagePath = asset(str_replace('public/', 'storage/', $paths[0]));
            }

            return [
                'id' => $item->id,
                'name' => $item->menu->nama_menu,
                'harga_menu' => $item->menu->total,
                'quantity' => $item->quantity,
                'harga' => $item->harga_item,
                'select' => $item->select,
                'foto' => $imagePath,
            ];
        });

        return response()->json([
            'items' => $cartContents,
            'total_harga' => $totalHarga,
        ], 200);
    }

    public function selectCartItem($cartItemId)
    {
        $cartItem = CartItem::findOrFail($cartItemId);

        $cartItem->select = 1;
        $cartItem->save();

        // Ambil cart terkait
        $cart = $cartItem->cart;

        // Hitung harga total 
        $totalHarga = $cart->items()->where('select', '1')->sum('harga_item');

        $cart->harga = $totalHarga;
        $cart->save();

        return response()->json([
            'message' => 'Cart item selected successfully',
            'total_harga' => $totalHarga,
        ], 200);
    }

    public function unselectCartItem($cartItemId)
    {
        $cartItem = CartItem::findOrFail($cartItemId);

        $cartItem->select = 0;
        $cartItem->save();

        // Ambil cart terkait
        $cart = $cartItem->cart;

        // Hitung harga total 
        $totalHarga = $cart->items()->where('select', '1')->sum('harga_item');

        $cart->harga = $totalHarga;
        $cart->save();

        return response()->json([
            'message' => 'Cart item unselected successfully',
            'total_harga' => $totalHarga,
        ], 200);
    }
    public function update(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $cartItem = CartItem::findOrFail($cartItemId);
        $menu = $cartItem->menu;

        $cartItem->quantity = $request->quantity;
        // Perbarui harga item 
        $cartItem->harga_item = $menu->total * $request->quantity;
        $cartItem->save();

        // Ambil keranjang terkait
        $cart = $cartItem->cart;

        // Perbarui total harga keranjang berdasarkan kuantitas yang diperbarui
        $cart->harga = $cart->items()->where('select', '1')->sum('harga_item');
        $cart->save();

        return response()->json([
            'message' => 'Cart item updated successfully',
            'quantity' => $cartItem->quantity,
            'harga' => $cartItem->harga_item,
            'total_harga' => $cart->harga

        ], 200);
    }
}
