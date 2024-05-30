<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Services\Biteship;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    protected $biteship;

    public function __construct(Biteship $biteship)
    {
        $this->biteship = $biteship;
    }
    public function getAreas(Request $request)
    {
        $input = $request->query('input', '');

        try {
            $areas = $this->biteship->getAreas($input);
            return response()->json($areas);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
    {
        $created = Address::create(
            [
                'kode_alamat' => $request->kode_alamat,
                'nama_penerima' => $request->nama_penerima,
                'nomor_telepon' => $request->nomor_telepon,
                'jalan' => $request->jalan,
                'kecamatan' => $request->kecamatan,
                'kota' => $request->kota,
                'provinsi' => $request->provinsi,
                'kode_pos' => $request->kode_pos,
                'user_id' => $request->user()->id
            ]
        );

        if ($created) {
            return response()->json([
                'message' => 'Successfuly Add Address!'
            ], 201);
        } else {
            return response()->json([
                'message' => 'Server error'
            ], 500);
        }
    }

    public function getAlamat(Request $request)
    {
        $address = Address::where('user_id', $request->user()->id)->get();

        if (!$address) {
            return response()->json(['error' => 'Address not found'], 404);
        }

        return response()->json($address);
    }

    public function edit(Request $request, $id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json($address);
    }

    public function update(Request $request, $id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['message' => 'Address not found'], 404);
        }

        $address->update([
            'jalan' => $request->jalan,
            'kelurahan' => $request->kelurahan,
            'kecamatan' => $request->kecamatan,
            'kota' => $request->kota,
            'kode_pos' => $request->kode_pos,
        ]);

        return response()->json(['message' => 'Successfully updated Address'], 200);
    }

    public function destroy($id)
    {
        $address = Address::where('id', $id)->first();
        if (!$address) {
            return response()->json(['success' => false, "Message" => "Address Gagal Dihapus"], 404);
        }
        $address->delete();
        return response()->json(['success' => true, "Message" => "Address Berhasil Dihapus"], 200);
    }
}
