<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request, $menuId)
    {
        
        try {
            $ingredients = Ingredient::where('menu_id', $menuId)->get();

            return response()->json($ingredients);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to get ingredient. ' . $e->getMessage()], 500);
        }
    }

    public function indexCart(Request $request, $cartItemId)
    {
        $cartItem = CartItem::findOrFail($cartItemId);
        if (!$cartItem) {
                return response()->json(['message' => 'CartItem tidak ditemukan!'], 404);
            }
        try {
            $ingredients = Ingredient::where('menu_id', $cartItem->menu_id)->get();

            return response()->json($ingredients);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to get ingredient. ' . $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string',
            'harga_bahan' => 'nullable|numeric',
            'menu_id' => 'required|integer|exists:menus,id',
        ]);

        try {
            $ingredient = Ingredient::create([
                'nama' => $request->input('nama_bahan'),
                'menu_id' => $request->input('menu_id'),
                'harga' => $request->input('harga_bahan', 0)
            ]);

            return response()->json(['ingredient' => $ingredient, 'message' => 'Ingredient added successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to add ingredient. ' . $e->getMessage()], 500);
        }
    }

    public function getOne($id)
    {
        try {
            $ingredient = Ingredient::findOrFail($id);

            // Jika menu ditemukan, kembalikan respons
            return response()->json(['status' => 'success', 'data' => $ingredient], 200);
        } catch (\Exception $e) {
            // Jika menu tidak ditemukan, kembalikan pesan kesalahan
            return response()->json(['status' => 'error', 'message' => 'Ingredient not found.'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found.'], 404);
        }

        $request->validate([
            'nama_bahan' => 'nullable|string',
            'harga_bahan' => 'nullable|numeric',
        ]);

        if ($request->has('nama')) {
            $ingredient->nama = $request->input('nama_bahan');
        }

        if ($request->has('harga')) {
            $ingredient->harga = $request->input('harga_bahan');
        }

        $ingredient->save();

        return response()->json(['message' => 'Ingredient updated successfully', 'ingredient' => $ingredient]);
    }

    public function destroy($id)
    {
        $ingredient = Ingredient::find($id);

        if (!$ingredient) {
            return response()->json(['error' => 'Ingredient not found.'], 404);
        }

        $ingredient->delete();

        return response()->json(['message' => 'Ingredient deleted successfully']);
    }
}