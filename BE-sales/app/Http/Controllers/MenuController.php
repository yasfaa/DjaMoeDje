<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index()
    {
        try {
            $menus = Menu::paginate(10);
            $formattedMenus = [];

            foreach ($menus as $menu) {
                $formattedMenu = [
                    'id' => $menu->id,
                    'nama' => $menu->nama_menu,
                    'total' => $menu->total,
                    'deskripsi' => $menu->deskripsi,
                    'imagePath' => null,
                ];

                if (!is_null($menu->file_path)) {
                    $paths = json_decode($menu->file_path, true);
                    $url = asset(str_replace('public/', 'storage/', $paths[0]));
                    $formattedMenu['imagePath'] = $url;
                }

                $formattedMenus[] = $formattedMenu;
            }
            return response()->json(['menus' => $formattedMenus], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal mengambil data menu. ' . $e->getMessage()], 500);
        }
    }

    public function getMenuAdmin()
    {
        try {
            $menus = Menu::paginate(10);
            $formattedMenus = [];

            foreach ($menus as $menu) {
                $formattedMenu = [
                    'id' => $menu->id,
                    'nama' => $menu->nama_menu,
                    'total' => $menu->total,
                    'deskripsi' => $menu->deskripsi,
                    'imagePaths' => [],
                ];

                if (!is_null($menu->file_path)) {
                    $paths = json_decode($menu->file_path, true);
                    foreach ($paths as $path) {
                        $url = asset(str_replace('public/', 'storage/', $path));
                        $formattedMenu['imagePaths'][] = $url;
                    }
                }

                $formattedMenus[] = $formattedMenu;
            }

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
            'gambar' => 'required|array',
            'gambar.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        try {
            $filePaths = [];
            $imageUrls = [];

            if ($request->hasFile('gambar')) {

                foreach ($request->file('gambar') as $gambar) {
                    $path = $gambar->store('public/menu_images');
                    $filePaths[] = $path;

                    $imageUrl = asset(str_replace('public/', 'storage/', $path));
                    $imageUrls[] = $imageUrl;
                }
            }

            $menu = Menu::create([
                'nama_menu' => $request->input('nama_menu'),
                'total' => $request->input('total'),
                'deskripsi' => $request->input('deskripsi'),
                'file_path' => json_encode($filePaths),
            ]);

            return response()->json(['message' => 'Menu berhasil ditambahkan', 'menu' => $menu, 'imageUrls' => $imageUrls]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menambahkan menu' . $e->getMessage()], 500);
        }
    }

    public function getOne($id)
    {
        try {
            $menu = Menu::find($id);

            if (!$menu) {
                return response()->json(['status' => 'error', 'message' => 'Menu tidak ditemukan.'], 404);
            }

            $filePaths = json_decode($menu->file_path, true);

            $fileLinks = [];
            if ($filePaths) {
                foreach ($filePaths as $path) {
                    $fileLinks[] = str_replace('/public/', '/storage/', asset($path));
                }
            }

            return response()->json(['status' => 'success', 'menu' => $menu, 'fileLinks' => $fileLinks], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }




    public function update(Request $request, $id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['error' => 'Menu tidak ditemukan.'], 404);
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

        return response()->json(['message' => 'Menu berhasil diperbarui', 'menu' => $menu, 'imageUrls' => $imageUrls]);
    }


    public function destroy($id)
    {
        $menu = Menu::find($id);

        if (!$menu) {
            return response()->json(['error' => 'Menu tidak ditemukan.'], 404);
        }

        // Hapus file foto dari storage
        if (!is_null($menu->file_path)) {
            $filePaths = json_decode($menu->file_path, true);
            foreach ($filePaths as $path) {
                if (Storage::exists($path)) {
                    Storage::delete($path);
                }
            }
        }

        $menu->delete();

        return response()->json(['message' => 'Menu berhasil dihapus']);
    }

}
