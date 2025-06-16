<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransTestController extends Controller
{
    public function selectPayment()
    {
        // Dummy customer
        $customer = (object)[
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
        ];

        // Dummy item_details
        $itemDetails = [
            [
                'id' => 'ITEM001',
                'price' => 120000, // integer
                'quantity' => 1,   // integer
                'name' => 'Kaos Polos Premium'
            ],
            [
                'id' => 'ITEM002',
                'price' => 45000,
                'quantity' => 2,
                'name' => 'Topi Rajut Dingin'
            ]
        ];

        // Hitung total
        $grossAmount = array_sum(array_map(function ($item) {
            return $item['price'] * $item['quantity'];
        }, $itemDetails));

        // Konfigurasi Midtrans
        Config::$serverKey = 'SB-Mid-server-XXXXXXX'; // GANTI dengan sandbox server key kamu
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;
        Config::$curlOptions[CURLOPT_SSL_VERIFYPEER] = false;

        // Buat parameter Snap
        $params = [
            'transaction_details' => [
                'order_id' => 'ORDER-' . time(),
                'gross_amount' => $grossAmount
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->phone
            ]
        ];
dd([
    'grossAmount' => $grossAmount,
    'itemDetailsTotal' => array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $itemDetails)),
]);

        // Dapatkan Snap Token
        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            return response()->json([
                'error_code' => 'MIDTRANS_ERROR',
                'message' => $e->getMessage(),
                'params' => $params
            ]);
        }

        return view('v_order.dummy_payment', compact('snapToken'));
    }
}
