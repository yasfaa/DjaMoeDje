<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'menu_id' => 'required|integer|exists:menus,id',
        ]);
        try {
            $menuId = $request->input('menu_id');
            $ingredients = Ingredient::where('menu_id', $menuId)->get();

            return response()->json($ingredients);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to get ingredient. ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string',
            'harga' => 'nullable|numeric',
        ]);

        try {
            $ingredient = Ingredient::create([
                'nama' => $request->input('nama'),
                'menu_id' => $id,
                'harga' => $request->input('harga', 0)
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
            'nama' => 'nullable|string',
            'harga' => 'nullable|numeric',
        ]);

        if ($request->has('nama')) {
            $ingredient->nama = $request->input('nama');
        }

        if ($request->has('harga')) {
            $ingredient->harga = $request->input('harga');
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