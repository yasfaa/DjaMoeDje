<?php

namespace App\Console\Commands;

use App\Models\Menu;
use Illuminate\Console\Command;

class ResetStokHarian extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-stok-harian';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function resetStokHarian()
{
    try {
        $menus = Menu::all();

        foreach ($menus as $menu) {
            $menu->sisa_stok = $menu->stok_harian;
            $menu->save();
        }

        return response()->json(['message' => 'Stok harian berhasil di-reset'], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Gagal mereset stok harian: ' . $e->getMessage()], 500);
    }
}

}
