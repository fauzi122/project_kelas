<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Province;
use App\Models\City;
use App\Models\ShippingCost;

class RajaOngkirController extends Controller
{
   // Method untuk mengambil data provinsi
    // public function getProvinces()
    // {
    //     $provinces = Province::all(); // Ambil semua data provinsi dari database
    //     return view('rajaongkir.index', compact('provinces'));
    // }

// Method untuk mengambil data kota berdasarkan province_id
// public function getCities(Request $request)
// {
//     $provinceId = $request->input('province_id');
    
//     if (!$provinceId) {
//         return response()->json(['error' => 'Province ID is required'], 400);
//     }

//     $cities = City::where('province_id', $provinceId)->get(); // Ambil kota berdasarkan province_id

//     if ($cities->isEmpty()) {
//         return response()->json(['error' => 'No cities found for the given province'], 404);
//     }

//     return response()->json($cities);
// }

// // Method untuk mengambil biaya pengiriman
// public function getCost(Request $request)
// {
//     // Mengambil input dari request
//     $origin = $request->input('origin'); // city_name dari form
//     $destination = $request->input('destination');
//     $weight = $request->input('weight');
//     $courier = $request->input('courier');

//     dd($origin, $destination); // Debugging untuk melihat apakah data dikirimkan dengan benar

//     // Pastikan semua parameter ada
//     if (!$origin || !$destination || !$weight || !$courier) {
//         return redirect()->back()->withErrors('Please fill in all fields.');
//     }

//     // Validasi bahwa origin dan destination adalah kota yang valid
//     $originCity = City::where('city_name', $origin)->first();
//     $destinationCity = City::where('city_name', $destination)->first();

//     if (!$originCity || !$destinationCity) {
//         return redirect()->back()->withErrors('Invalid origin or destination city.');
//     }

//     // Cek biaya pengiriman berdasarkan parameter
//     $shippingCost = ShippingCost::where('origin', $origin)
//         ->where('destination', $destination)
//         ->where('weight', $weight)
//         ->where('courier', $courier)
//         ->first();

//     // Jika tidak ada biaya ditemukan
//     if (!$shippingCost) {
//         return redirect()->back()->withErrors('Shipping cost not found for the given parameters.');
//     }

//     // Mengembalikan biaya pengiriman ke view
//     return view('rajaongkir.index', [
//         'shippingCost' => $shippingCost->cost,
//         'provinces' => Province::all(), // Kirimkan data provinsi untuk dropdown
//     ]);
// }



    public function getProvinces()
    {
         $response = Http::withOptions([
        'verify' => false // Nonaktifkan verifikasi SSL (jangan di production)
    ])->withHeaders([
        'key' => '794a5d197b9cb469ae958ed043ccf921' // Ambil dari .env
    ])->get('https://api.rajaongkir.com/starter/province');
        return response()->json($response->json());
    }



public function getCities(Request $request)
{
    $provinceId = $request->input('province_id');

    $response = Http::withOptions([
        'verify' => false // Nonaktifkan SSL verify (jangan di production)
    ])->withHeaders([
        'key' => '794a5d197b9cb469ae958ed043ccf921'
    ])->get('https://api.rajaongkir.com/starter/city', [
        'province' => $provinceId
    ]);

    return response()->json($response->json());
}


// public function getCost(Request $request)
// {
//     $origin = $request->input('origin');
//     $destination = $request->input('destination');
//     $weight = $request->input('weight');
//     $courier = $request->input('courier');

//     $response = Http::withOptions([
//         'verify' => false // Nonaktifkan SSL verify untuk lokal dev
//     ])->withHeaders([
//         'key' => '794a5d197b9cb469ae958ed043ccf921' // fallback jika .env tidak ada
//     ])->post('https://api.rajaongkir.com/starter/cost', [
//         'origin' => $origin,
//         'destination' => $destination,
//         'weight' => $weight,
//         'courier' => $courier,
//     ]);

//     return response()->json($response->json());
// }


}
