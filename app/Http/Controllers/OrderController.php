<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\Produk;
use App\Models\Order;
use App\Models\OrderItem;
use Midtrans\Config;
use Midtrans\Snap;

use Illuminate\Support\Facades\Http;


class OrderController extends Controller
{
    
    private $provinsiKota = [
    'Bangka Belitung' => ['Bangka Barat', 'Bangka Selatan', 'Bangka Tengah'],
    'Jawa Barat' => ['Bandung', 'Bekasi', 'Cirebon', 'Depok', 'Tasikmalaya', 'Karawang', 'Sukabumi'],
    'Jawa Tengah' => ['Semarang', 'Solo', 'Yogyakarta', 'Magelang', 'Tegal', 'Purwokerto'],
    'Jawa Timur' => ['Surabaya', 'Malang', 'Madiun', 'Blitar', 'Kediri', 'Probolinggo'],
    'Sumatera Utara' => ['Medan', 'Binjai', 'Pematang Siantar', 'Sibolga', 'Langkat', 'Karo'],
    'Sumatera Barat' => ['Padang', 'Bukittinggi', 'Payakumbuh', 'Solok', 'Padang Panjang'],
    'Lampung' => ['Bandar Lampung', 'Metro', 'Pringsewu', 'Tanggamus'],
    'Bali' => ['Denpasar', 'Badung', 'Gianyar', 'Karangasem', 'Tabanan'],
    'Nusa Tenggara Barat' => ['Mataram', 'Sumbawa', 'Lombok', 'Bima'],
    'Nusa Tenggara Timur' => ['Kupang', 'Maumere', 'Ruteng', 'Ende'],
    'Kalimantan Barat' => ['Pontianak', 'Singkawang', 'Sambas', 'Ketapang'],
    'Kalimantan Timur' => ['Samarinda', 'Balikpapan', 'Bontang', 'Kutai Kartanegara'],
    'Kalimantan Selatan' => ['Banjarmasin', 'Banjarbaru', 'Martapura'],
    'Sulawesi Utara' => ['Manado', 'Bitung', 'Tomohon', 'Minahasa'],
    'Sulawesi Tengah' => ['Palu', 'Donggala', 'Tolitoli', 'Morowali'],
    'Sulawesi Selatan' => ['Makassar', 'Bone', 'Parepare', 'Maros', 'Sinjai'],
    'Sulawesi Tenggara' => ['Kendari', 'Baubau', 'Kolaka'],
    'Gorontalo' => ['Gorontalo', 'Bone Bolango', 'Pohuwato'],
    'Maluku' => ['Ambon', 'Ternate', 'Bula', 'Namlea'],
    'Papua' => ['Jayapura', 'Timika', 'Merauke', 'Biak'],
    'Maluku Utara' => ['Ternate', 'Tidore', 'Sula'],
    'Papua Barat' => ['Manokwari', 'Sorong', 'Fakfak'],
    'DKI Jakarta' => ['Jakarta Pusat', 'Jakarta Selatan', 'Jakarta Timur', 'Jakarta Barat', 'Jakarta Utara']
];
    public function addToCart($id)
    {
        
        $customer = Customer::where('user_id', Auth::id())->first();
        $produk = Produk::findOrFail($id);

        $order = Order::firstOrCreate(
            ['customer_id' => $customer->id, 'status' => 'pending'],
            ['total_harga' => 0]
            );

        $orderItem = OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'produk_id' => $produk->id],
            ['quantity' => 1, 'harga' => $produk->harga]
        );

        if (!$orderItem->wasRecentlyCreated) {
            $orderItem->quantity++;
            $orderItem->save();
        }

        $order->total_harga += $produk->harga;
        $order->save();

        return redirect()->route('order.cart')->with('success', 'Produk berhasil ditambahkan ke keranjang');
    }

    public function viewCart()
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)
            ->whereIn('status', ['pending', 'paid'])
            ->first();

        if ($order) {
            $order->load('orderItems.produk');
        }

        return view('v_order.cart', compact('order'));
    }

    public function updateCart(Request $request, $id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        if ($order) {
            $orderItem = $order->orderItems()->where('id', $id)->first();
            if ($orderItem) {
                $quantity = $request->input('quantity');
                if ($quantity > $orderItem->produk->stok) {
                    return redirect()->route('order.cart')->with('error', 'Jumlah produk melebihi stok yang tersedia');
                }

                $order->total_harga -= $orderItem->harga * $orderItem->quantity;
                $orderItem->quantity = $quantity;
                $orderItem->save();
                $order->total_harga += $orderItem->harga * $orderItem->quantity;
                $order->save();
            }
        }

        return redirect()->route('order.cart')->with('success', 'Jumlah produk berhasil diperbarui');
    }

    public function removeFromCart(Request $request, $id)
    {
        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        if ($order) {
            $orderItem = OrderItem::where('order_id', $order->id)->where('produk_id', $id)->first();
            if ($orderItem) {
                $order->total_harga -= $orderItem->harga * $orderItem->quantity;
                $orderItem->delete();

                if ($order->total_harga <= 0) {
                    $order->delete();
                } else {
                    $order->save();
                }
            }
        }

        return redirect()->route('order.cart')->with('success', 'Produk berhasil dihapus dari keranjang');
    }

    public function selectShipping(Request $request)
    {
                // Data provinsi dan kota
     $provinsiKota = [
        'Bangka Belitung' => ['Bangka Barat', 'Bangka Selatan', 'Bangka Tengah'],
        'Jawa Barat' => ['Bandung', 'Bekasi', 'Cirebon'],
        'Jawa Tengah' => ['Semarang', 'Solo', 'Yogyakarta']
    ];

        $customer = Customer::where('user_id', Auth::id())->first();
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();
        if (!$order || $order->orderItems->count() == 0) {
            return redirect()->route('order.cart')->with('error', 'Keranjang belanja kosong.');
        }

        return view('v_order.select_shipping', compact('order','provinsiKota'));
    }

    public function updateOngkir(Request $request)
    {
        // dd($request->input('province'));
    
        // Mengambil data customer yang sedang login
        $customer = Customer::where('user_id', Auth::id())->first();
        
        // Mengambil order yang statusnya 'pending' untuk customer yang bersangkutan
        $order = Order::where('customer_id', $customer->id)->where('status', 'pending')->first();

        // Jika order ditemukan
        if ($order) {
            // Mengambil data ongkir dari request, jika tidak ada, gunakan nilai default
            $order->user_id = Auth::id(); 
            $order->kurir = $request->input('kurir'); 
            $order->layanan_ongkir = $request->input('layanan_ongkir', 'Standard'); 
            $order->biaya_ongkir = preg_replace('/\./', '', $request->input('ongkir'));

            $order->estimasi_ongkir = $request->input('estimasi_ongkir', '3-5 days');
            $order->total_berat = $request->input('berat'); 

            $order->alamat = $request->input('alamat') . ', <br>' . 
                            $request->input('city') . ', <br>' . 
                            $request->input('province'); 
    
            $order->pos = $request->input('kode_pos'); // Default '00000' jika tidak ada
            $order->save();

            // Redirect ke halaman pembayaran setelah sukses
            return redirect()->route('order.selectPayment')->with('success', 'Data ongkir berhasil disimpan');
        }

        // Jika order tidak ditemukan, kembali dengan pesan error
        return back()->with('error', 'Gagal menyimpan data ongkir');
    }


    // Menangani proses cek ongkir
    public function cekOngkir(Request $request)
    {
        $provinsi = $request->input('provinsi');
        $kota = $request->input('kota');
        $berat = $request->input('berat');
        $kurir = $request->input('kurir');
        $kode_pos = $request->input('kode_pos');
        $alamat = $request->input('alamat');

        // Menampilkan hasil cek ongkir
        return view('v_order.select_shipping', [
            'provinsiKota' => $this->provinsiKota,
            'provinsiInput' => $provinsi,
            'kotaInput' => $kota,
            'beratInput' => $berat,
            'kurirInput' => $kurir,
            'ongkir' => "" . number_format(rand(50000, 100000), 0, ',', '.'),
            'alamat' => $alamat,
            'kode_pos' => $kode_pos,
        ]);
    }






public function selectPayment()
{
    $customer = Auth::user();
    $order = Order::where('user_id', $customer->id)->where('status', 'pending')->first();

    if (!$order) {
        return redirect()->route('order.cart')->with('error', 'Keranjang belanja kosong.');
    }

    $order->load('orderItems.produk');
    $origin = session('origin');
    $originName = session('originName');

    // Siapkan item_details Midtrans
    $itemDetails = [];
    foreach ($order->orderItems as $item) {
        $itemDetails[] = [
            'id' => 'PROD-' . $item->produk->id,
            'price' => (int) $item->harga,
            'quantity' => (int) $item->quantity,
            'name' => substr($item->produk->nama, 0, 50)
        ];
    }

    // Hitung total harga dari item_details
    $grossAmount = array_sum(array_map(function ($item) {
        return $item['price'] * $item['quantity'];
    }, $itemDetails));

    // Siapkan konfigurasi Midtrans
    Config::$serverKey = config('midtrans.server_key');
    Config::$isProduction = false;
    Config::$isSanitized = true;
    Config::$is3ds = true;
    Config::$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;

    // Buat Snap Token
    try {
        $snapToken = Snap::getSnapToken([
            'transaction_details' => [
                'order_id' => 'ORDER-' . $order->id . '-' . time(),
                'gross_amount' => $grossAmount
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone ?? '081234567890'
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Midtrans Error:', [
            'message' => $e->getMessage()
        ]);

        return response()->json([
            'error_code' => 'MIDTRANS_ERROR',
            'message' => $e->getMessage()
        ]);
    }

    // Kirim ke tampilan pembayaran
    return view('v_order.select_payment', [
        'order' => $order,
        'origin' => $origin,
        'originName' => $originName,
        'snapToken' => $snapToken,
    ]);
}



public function callback(Request $request)
    {
        // dd($request->all());
        $serverKey = config('midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        if ($hashed == $request->signature_key) {
            $order = Order::find($request->order_id);
            if ($order) {
                $order->update(['status' => 'Paid']);
            }
        }
    }

    public function complete() // Untuk kondisi local
    {
        // Dapatkan customer yang login
        $customer = Auth::user();

        // Cari order dengan status 'pending' milik customer tersebut
        $order = Order::where('customer_id', $customer->customer->id)
            ->where('status', 'pending')
            ->first();

        if ($order) {
            // Update status order menjadi 'Paid'
            $order->status = 'Paid';
            $order->save();
        }

        // Redirect ke halaman riwayat dengan pesan sukses
        return redirect()->route('order.history')->with('success', 'Checkout berhasil');
    }

    // public function complete() // Untuk kondisi sudah memiliki domain
    // {
    //     // Logika untuk halaman setelah pembayaran berhasil
    //     return redirect()->route('order.history')->with('success', 'Checkout berhasil');
    // }

    public function orderHistory()
    {
        $customer = Customer::where('user_id', Auth::id())->first();;;
        // $orders = Order::where('customer_id', $customer->id)->where('status', 'completed')->get();
        $statuses = ['Paid', 'Kirim', 'Selesai'];
        $orders = Order::where('customer_id', $customer->id)
            ->whereIn('status', $statuses)
            ->orderBy('id', 'desc')
            ->get();
        return view('v_order.history', compact('orders'));
    }

    public function invoiceFrontend($id)
    {
        $order = Order::findOrFail($id);
        return view('backend.v_pesanan.invoice', [
            'judul' => 'Pesanan',
            'subJudul' => 'Pesanan Proses',
            'judul' => 'Data Transaksi',
            'order' => $order,
        ]);
    }
}

    


