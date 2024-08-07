<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuPicture;
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
                    'stok_harian' => $menu->sisa_stok,
                    'imagePath' => null,
                ];

                if ($menu->menuPictures->isNotEmpty()) {
                    $firstPicture = $menu->menuPictures->first();
                    $fileName = $firstPicture->file_path;
                    $url = asset('storage/public/menu_images/' . $fileName);
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
                    'stok_harian' => $menu->stok_harian,
                    'deskripsi' => $menu->deskripsi,
                ];

                foreach ($menu->menuPictures as $picture) {
                    $fileName = $picture->file_path;
                    $url = asset('storage/public/menu_images/' . $fileName);
                    $formattedMenu['imagePaths'][] = $url;
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
            'stok_harian' => 'required|integer',
            'gambar' => 'required|array',
            'gambar.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        try {
            $fileNames = [];
            $imageUrls = [];

            if ($request->hasFile('gambar')) {
                foreach ($request->file('gambar') as $gambar) {
                    $path = $gambar->store('public/menu_images');
                    $fileNameOnly = basename($path);

                    $fileNames[] = $fileNameOnly;

                    $imageUrl = asset('storage/public/menu_images/' . $fileNameOnly);
                    $imageUrls[] = $imageUrl;
                }
            }

            $menu = Menu::create([
                'nama_menu' => $request->input('nama_menu'),
                'total' => $request->input('total'),
                'deskripsi' => $request->input('deskripsi'),
                'stok_harian' => $request->input('stok_harian'),
                'sisa_stok' => $request->input('stok_harian')
            ]);

            foreach ($fileNames as $fileName) {
                MenuPicture::create([
                    'menu_id' => $menu->id,
                    'file_path' => $fileName,
                ]);
            }

            return response()->json(['message' => 'Menu berhasil ditambahkan', 'menu' => $menu, 'imageUrls' => $imageUrls]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menambahkan menu' . $e->getMessage()], 500);
        }
    }

    public function getOne($id)
    {
        try {
            $menu = Menu::with('menuPictures', 'ingredient')->find($id);

            if (!$menu) {
                return response()->json(['status' => 'error', 'message' => 'Menu tidak ditemukan.'], 404);
            }

            $fileLinks = [];

            foreach ($menu->menuPictures as $picture) {
                $fileLinks[] = asset('storage/public/menu_images/' . $picture->file_path);
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
            'stok_harian' => 'required|integer',
            'gambar' => 'nullable|array',
            'gambar.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ]);

        $menu->nama_menu = $request->input('nama_menu');
        $menu->total = $request->input('total');
        $menu->deskripsi = $request->input('deskripsi');
        $menu->stok_harian = $request->input('stok_harian');
        $menu->sisa_stok = $request->input('stok_harian');

        $imageUrls = [];

        if ($request->hasFile('gambar')) {
            $menu->menuPictures()->delete();

            foreach ($request->file('gambar') as $gambar) {
                $path = $gambar->store('public/menu_images');
                $fileName = basename($path);

                $menu->menuPictures()->create([
                    'file_path' => $fileName,
                ]);

                $imageUrl = asset('storage/public/menu_images/' . $fileName);
                $imageUrls[] = $imageUrl;
            }
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

        $menuPictures = $menu->menuPictures;

        foreach ($menuPictures as $picture) {
            $filePath = 'public/menu_images/' . $picture->file_path;
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
        }

        $menu->delete();

        return response()->json(['message' => 'Menu berhasil dihapus']);
    }

}
