<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        try {
            $menus = Menu::paginate(10);

            // Menginisialisasi array untuk menyimpan data menu beserta satu URL dari imagePath
            $formattedMenus = [];

            // Mengisi array formattedMenus dengan data yang diinginkan
            foreach ($menus as $menu) {
                $formattedMenu = [
                    'id' => $menu->id,
                    'nama' => $menu->nama_menu,
                    'total' => $menu->total,
                    'deskripsi' => $menu->deskripsi,
                    'imagePath' => null, // Inisialisasi imagePath menjadi null
                ];

                // Jika imagePath tidak kosong, ambil satu URL saja
                if (!is_null($menu->file_path)) {
                    $paths = json_decode($menu->file_path, true);
                    $url = asset(str_replace('public/', 'storage/', $paths[0])); // Ambil URL pertama saja
                    $formattedMenu['imagePath'] = $url;
                }

                $formattedMenus[] = $formattedMenu;
            }

            // Mengembalikan response dengan data formattedMenus
            return response()->json(['menus' => $formattedMenus], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data menu. ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_menu' => 'required|string',
            'total' => 'required|numeric',
            'deskripsi' => 'required|string',
            'gambar' => 'nullable|array',
            'gambar.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        try {
            $filePaths = [];
            $imageUrls = [];

            foreach ($request->file('gambar') as $gambar) {
                $path = $gambar->store('public/menu_images');
                $filePaths[] = $path;

                $imageUrl = asset(str_replace('public/', 'storage/', $path));
                $imageUrls[] = $imageUrl;
            }

            $menu = Menu::create([
                'nama_menu' => $request->input('nama_menu'),
                'total' => $request->input('total'),
                'deskripsi' => $request->input('deskripsi'),
                'file_path' => json_encode($filePaths),
            ]);

            return response()->json(['message' => 'Menu updated successfully', 'menu' => $menu, 'imageUrls' => $imageUrls]);
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
            'gambar' => 'nullable|array',
            'gambar.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);


        $menu->nama_menu = $request->input('nama_menu');
        $menu->total = $request->input('total');
        $menu->deskripsi = $request->input('deskripsi');

        $imageUrls = [];

        if ($request->hasFile('gambar')) {
            $filePaths = [];
            foreach ($request->file('gambar') as $gambar) {
                $path = $gambar->store('public/menu_images');
                $filePaths[] = $path;

                $imageUrl = asset(str_replace('public/', 'storage/', $path));
                $imageUrls[] = $imageUrl;
            }
            $menu->file_path = json_encode($filePaths);
        }

        $menu->save();

        return response()->json(['message' => 'Menu updated successfully', 'menu' => $menu, 'imageUrls' => $imageUrls]);
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
