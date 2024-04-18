<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        try {
            $menus = Menu::all();

            return response()->json(['menus' => $menus], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve menus. ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required|string',
            'total' => 'required|numeric',
            'deskripsi' => 'required|string',
        ]);

        try {
            $menu = Menu::create([
                'nama_menu' => $request->input('nama_menu'),
                'total' => $request->input('total'),
                'deskripsi' => $request->input('deskripsi'),
            ]);

            return response()->json(['menu' => $menu, 'message' => 'Menu added successfully'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to add menu. ' . $e->getMessage()], 500);
        }
    }

    public function getOne($id)
    {
        try {
            $menu = Menu::findOrFail($id);

            // Jika menu ditemukan, kembalikan respons
            return response()->json(['status' => 'success', 'data' => $menu], 200);
        } catch (\Exception $e) {
            // Jika menu tidak ditemukan, kembalikan pesan kesalahan
            return response()->json(['status' => 'error', 'message' => 'Menu not found.'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['error' => 'Menu not found.'], 404);
        }

        $request->validate([
            'nama_menu' => 'required|string',
            'total' => 'required|numeric',
            'deskripsi' => 'required|string',
        ]);

        $menu->nama_menu = $request->input('nama_menu');
        $menu->total = $request->input('total');
        $menu->deskripsi = $request->input('deskripsi');

        $menu->save();

        return response()->json(['message' => 'Menu updated successfully', 'menu' => $menu]);
    }

    public function destroy($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['error' => 'Menu not found.'], 404);
        }

        $menu->delete();

        return response()->json(['message' => 'Menu deleted successfully']);
    }
}
