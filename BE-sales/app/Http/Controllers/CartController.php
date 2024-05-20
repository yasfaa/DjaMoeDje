<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function addMenuToCart(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();

        // Cari atau buat cart untuk pengguna dengan status 'pending'
        $cart = Cart::firstOrCreate(
            ['user_id' => $user->id, 'status' => 'pending'],
            ['total_price' => 0]
        );

        // Cari apakah menu sudah ada di cart
        $existingCartItem = $cart->items()->where('menu_id', $request->menu_id)->first();

        if ($existingCartItem) {
            // Jika item sudah ada di cart, tambahkan kuantitasnya
            $existingCartItem->quantity += $request->quantity;
            $existingCartItem->save();

            // Perbarui total harga cart
            $cart->total_price += $existingCartItem->menu->price * $request->quantity;
        } else {
            // Jika item belum ada di cart, tambahkan item baru
            $newCartItem = $cart->items()->create([
                'menu_id' => $request->menu_id,
                'quantity' => $request->quantity,
                'customizations' => null // Tidak ada kustomisasi
            ]);

            // Perbarui total harga cart
            $cart->total_price += $newCartItem->menu->price * $newCartItem->quantity;
        }

        $cart->save();

        return response()->json($cart->load('items.menu'), 201);
    }
    
    //  Mendapatkan isi cart dari satu user.
    public function getCartContents()
    {
        $user = Auth::user();
        $cart = Cart::with('items.menu')
                    ->where('user_id', $user->id)
                    ->where('status', 'pending')
                    ->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart is empty'], 404);
        }
        dump($cart);
        
        $cartContents = $cart->items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->menu->name,
                'quantity' => $item->quantity,
                'price' => $item->menu->price,
            ];
        });

        return response()->json($cartContents, 200);
    }
}