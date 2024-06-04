<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Menu;
use App\Models\CartItem;
use Illuminate\Http\Request;
use App\Models\CartItemIngredient;
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
            $existingCartItem->harga_item = $existingCartItem->customization_price * $existingCartItem->quantity;
            $existingCartItem->save();
        } else {
            // Jika item belum ada di cart, tambahkan item baru
            $cart->items()->create([
                'menu_id' => $request->menu_id,
                'quantity' => $request->quantity,
                'customization_price' => $menu->total,
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

        // Mapping data cart items untuk response JSON
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
                'deskripsi' => $item->menu->deskripsi,
                'harga_menu' => $item->menu->total,
                'quantity' => $item->quantity,
                'harga_total_item' => $item->harga_item,
                'harga_dasar' => $item->customization_price,
                'select' => $item->select,
                'customization' => $item->customization,
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
        // Ambil item cart berdasarkan ID
        $cartItem = CartItem::findOrFail($cartItemId);

        // Tandai item sebagai dipilih
        $cartItem->select = 1;
        $cartItem->save();

        // Ambil cart terkait
        $cart = $cartItem->cart;

        // Hitung total harga
        $totalHarga = $cart->items()->where('select', '1')->sum('harga_item');

        $cart->harga = $totalHarga;
        $cart->save();

        return response()->json([
            'message' => 'Cart item selected successfully',
            'total_harga' => $totalHarga,
            'select' => $cartItem->select,
        ], 200);
    }

    public function unselectCartItem($cartItemId)
    {
        // Ambil item cart berdasarkan ID
        $cartItem = CartItem::findOrFail($cartItemId);

        // Tandai item sebagai tidak dipilih
        $cartItem->select = 0;
        $cartItem->save();

        // Ambil cart terkait
        $cart = $cartItem->cart;

        // Hitung total harga
        $totalHarga = $cart->items()->where('select', '1')->sum('harga_item');

        $cart->harga = $totalHarga;
        $cart->save();

        return response()->json([
            'message' => 'Cart item unselected successfully',
            'total_harga' => $totalHarga,
            'select' => $cartItem->select,
        ], 200);
    }

    public function update(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        // Ambil item cart berdasarkan ID
        $cartItem = CartItem::findOrFail($cartItemId);

        // Perbarui kuantitas dan harga item
        $cartItem->quantity = $request->quantity;
        $cartItem->harga_item = $cartItem->customization_price * $request->quantity;
        $cartItem->save();

        // Perbarui total harga cart
        $cart = $cartItem->cart;
        $cart->harga = $cart->items()->where('select', '1')->sum('harga_item');
        $cart->save();

        return response()->json([
            'message' => 'Cart item updated successfully',
            'quantity' => $cartItem->quantity,
            'harga_dasar' => $cartItem->customization_price,
            'harga_total_item' => $cartItem->harga_item,
            'total_harga' => $cart->harga
        ], 200);
    }

    public function destroy($cartItemId)
    {
        // Ambil item cart berdasarkan ID
        $cartItem = CartItem::find($cartItemId);

        if (!$cartItem) {
            return response()->json(['error' => 'Ingredient not found.'], 404);
        }

        $cartItem->delete();

        return response()->json(['message' => 'Ingredient deleted successfully']);
    }

    public function addCustomization(Request $request, $cartItemId)
    {
        $request->validate([
            'ingredient_id' => 'required|exists:ingredients,id',
            'quantity' => 'required|integer|min:1'
        ]);

        // Ambil item cart berdasarkan ID
        $cartItem = CartItem::findOrFail($cartItemId);

        // Cari atau buat entri kustomisasi di cart_item_ingredients
        $cartItemIngredient = CartItemIngredient::updateOrCreate(
            [
                'cart_item_id' => $cartItemId,
                'ingredient_id' => $request->ingredient_id
            ],
            [
                'quantity' => $request->quantity
            ]
        );

        // Perbarui kustomisasi dan harga item
        $this->updateCartItemCustomization($cartItem);

        return response()->json([
            'message' => 'Customization added successfully',
            'customizations' => $cartItem->customizations,
            'harga_item' => $cartItem->harga_item,
            'total_harga' => $cartItem->cart->harga
        ], 200);
    }


    private function updateCartItemCustomization(CartItem $cartItem)
    {
        // Ambil semua kustomisasi dari item cart
        $ingredients = $cartItem->ingredients ?? collect();
        $totalCustomPrice = 0;
        $customizationDetails = [];

        // Ambil harga menu
        $menu = $cartItem->menu;

        // Ambil bahan dari menu
        $menuIngredients = $menu->ingredients ?? collect();
        $basePriceWithoutIngredients = $menu->total;

        // Hitung harga item dasar tanpa bahan yang tidak ada di CartItemIngredient
        foreach ($menuIngredients as $menuIngredient) {
            $cartItemIngredient = $ingredients->firstWhere('ingredient_id', $menuIngredient->id);
            $quantityInCart = $cartItemIngredient ? $cartItemIngredient->quantity : 0;

            if ($quantityInCart == 0) {
                $basePriceWithoutIngredients -= $menuIngredient->harga;
            } elseif ($quantityInCart > 1) {
                $basePriceWithoutIngredients += $menuIngredient->harga * ($quantityInCart - 1);
            }
        }

        // Hitung total harga kustomisasi dan buat detail kustomisasi
        foreach ($ingredients as $ingredient) {
            $ingredientModel = $ingredient->ingredient;
            $totalCustomPrice += $ingredientModel->harga * $ingredient->quantity;

            $customizationDetails[] = [
                'nama' => $ingredientModel->nama,
                'quantity' => $ingredient->quantity
            ];
        }

        // Simpan detail kustomisasi dan harga item yang baru
        $cartItem->customizations = json_encode($customizationDetails);
        $cartItem->customization_price = $totalCustomPrice;
        $cartItem->harga_item = $basePriceWithoutIngredients * $cartItem->quantity + $totalCustomPrice * $cartItem->quantity;
        $cartItem->save();

        // Perbarui total harga cart
        $cart = $cartItem->cart;
        $cart->harga = $cart->items()->where('select', 1)->sum('harga_item');
        $cart->save();
    }
}
