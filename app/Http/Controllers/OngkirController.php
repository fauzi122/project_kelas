<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


    class OngkirController extends Controller
{
    // Data provinsi dan kota
    private $provinsiKota = [
        'Bangka Belitung' => ['Bangka Barat', 'Bangka Selatan', 'Bangka Tengah'],
        'Jawa Barat' => ['Bandung', 'Bekasi', 'Cirebon'],
        'Jawa Tengah' => ['Semarang', 'Solo', 'Yogyakarta']
    ];

    // Menampilkan form cek ongkir
    public function index()
    {
        // Menampilkan form cek ongkir dengan provinsi dan kota
        return view('cek_ongkir', ['provinsiKota' => $this->provinsiKota]);
    }

    // Menangani proses cek ongkir
    public function cekOngkir(Request $request)
    {
        $provinsi = $request->input('provinsi');
        $kota = $request->input('kota');
        $berat = $request->input('berat');
        $kurir = $request->input('kurir');

        // Menampilkan hasil cek ongkir
        return view('cek_ongkir', [
            'provinsiKota' => $this->provinsiKota,
            'provinsiInput' => $provinsi,
            'kotaInput' => $kota,
            'beratInput' => $berat,
            'kurirInput' => $kurir,
           'ongkir' => rand(50000, 100000)

        ]);
    }
}
