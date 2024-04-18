<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        try {
            $menus = Menu::paginate(5);

            foreach ($menus as $menu) {
                $filePaths[$menu->id] = json_decode($menu->file_path, true);
            }

            return response()->json(['menus' => $menus, 'filePaths' => $filePaths], 200);
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
            'gambar' => 'required|array',
            'gambar.*' => 'image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        try {
            $filePaths = [];

            foreach ($request->file('gambar') as $gambar) {
                $path = $gambar->store('public/menu_imagess');
                $filePaths[] = $path;
            }

            $menu = Menu::create([
                'nama_menu' => $request->input('nama_menu'),
                'total' => $request->input('total'),
                'deskripsi' => $request->input('deskripsi'),
                'file_path' => json_encode($filePaths),
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
            $filePaths = json_decode($menu->file_path, true);

            return response()->json(['status' => 'success', 'menu' => $menu, 'filePaths' => $filePaths], 200);
        } catch (\Exception $e) {

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
            'gambar' => 'array', // Validasi bahwa input gambar merupakan array
            'gambar.*' => 'image|mimes:jpeg,png,jpg,gif|max:4096', // Validasi setiap gambar
        ]);

        $menu->nama_menu = $request->input('nama_menu');
        $menu->total = $request->input('total');
        $menu->deskripsi = $request->input('deskripsi');

        if ($request->hasFile('gambar')) {
            $filePaths = [];
            foreach ($request->file('gambar') as $gambar) {
                $path = $gambar->store('public/menu_images');
                $filePaths[] = $path;
            }
            $menu->file_path = json_encode($filePaths);
        }

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
