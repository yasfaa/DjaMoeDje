<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Menu;
use Illuminate\Http\Request;
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
            $existingCartItem->save();

            // Perbarui total harga cart
            $menu = Menu::find($request->menu_id); // Muat menu
            $cart->harga += $menu->total * $request->quantity;
        } else {
            // Jika item belum ada di cart, tambahkan item baru
            $newCartItem = $cart->items()->create([
                'menu_id' => $request->menu_id,
                'quantity' => $request->quantity,
                'customizations' => null // Tidak ada kustomisasi
            ]);

            // Perbarui total harga cart
            $menu = Menu::find($request->menu_id); // Muat menu
            $cart->harga += $menu->total * $newCartItem->quantity;
        }

        $cart->save();

        return response()->json($cart->load('items.menu'), 201);
    }

    //  Mendapatkan isi cart dari satu user.
    public function index()
    {
        $user = Auth::user();
        $cart = Cart::with('items.menu')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart is empty'], 404);
        }

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