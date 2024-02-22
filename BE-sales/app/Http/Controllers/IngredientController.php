<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index()
    {
        try {
            $ingredients = Ingredient::all();

            return response()->json(['ingredients' => $ingredients], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve ingredients. ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string',
        ]);

        try {
            $ingredient = Ingredient::create([
                'nama_bahan' => $request->input('nama_bahan'),
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
            'nama_bahan' => 'required|string',
        ]);

        $ingredient->nama_bahan = $request->input('nama_bahan');
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
