<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        $created = Address::create(
            [
                'jalan' => $request->jalan,
                'kelurahan' => $request->kelurahan,
                'kecamatan' => $request->kecamatan,
                'kota' => $request->kota,
                'kode_pos' => $request->kode_pos,
                'email' => $request->user()->email
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
        $address = Address::where('email', $request->user()->email)->get();

        if (!$address) {
            return response()->json(['error' => 'Address not found'], 404);
        }

        return response()->json($address);
    }
    /**
     * Display the specified resource.
     */
    public function show(Address $address)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        $address = Address::find($id);

        if (!$address) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json($address);
    }


    /**
     * Update the specified resource in storage.
     */
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

    /**
     * Remove the specified resource from storage.
     */
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
