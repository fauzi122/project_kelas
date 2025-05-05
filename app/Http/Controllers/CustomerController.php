<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
class CustomerController extends Controller
{
    // Redirect ke Google
    public function redirect()
    {
        // dd('Redirecting to Google...');
        return Socialite::driver('google')->redirect();
    }



    public function callback()
    {
        try {
            // Menonaktifkan verifikasi SSL untuk pengujian lokal
            Http::withOptions([
                'verify' => false, // Nonaktifkan verifikasi SSL
            ]);
    
            // Mendapatkan data pengguna dari Google
            $socialUser = Socialite::driver('google')->user();
            
            // Debugging log
            Log::info('Google User Data: ', (array) $socialUser);
    
            // Cek apakah email sudah terdaftar
            $registeredUser = User::where('email', $socialUser->email)->first();
            if (!$registeredUser) {
                $user = User::create([
                    'nama' => $socialUser->name,
                    'email' => $socialUser->email,
                    'role' => '2', // Role customer
                    'status' => 1, // Status aktif
                    'password' => Hash::make('default_password'),
                ]);
                Customer::create([
                    'user_id' => $user->id,
                    'google_id' => $socialUser->id,
                    'google_token' => $socialUser->token
                ]);
                Auth::login($user);
            } else {
                Auth::login($registeredUser);
            }
    
            return redirect()->intended('beranda');
        } catch (\Exception $e) {
            Log::error('Error during Google login callback: ' . $e->getMessage());
            return redirect('/')->with('error', 'Terjadi kesalahan saat login dengan Google.');
        }
    }
    
    
    public function logout(Request $request)
    {
        Auth::logout(); // Logout pengguna
        $request->session()->invalidate(); // Hapus session
        $request->session()->regenerateToken(); // Regenerate token CSRF
        return redirect('/')->with('success', 'Anda telah berhasil logout.');
    }
}
