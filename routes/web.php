<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BerandaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginPelangganController;
use App\Http\Controllers\OrderController;


// register
Route::controller(RegisterController::class)->group(function () {
    Route::get('/register', 'register');
    Route::post('/store-register', 'store')->name('store.register');
});

// login pelanggan
Route::controller(LoginPelangganController::class)->group(function () {
    Route::get('/login', 'index')->name('login');
    Route::post('/post-login', 'store')->name('postlogin');
    Route::post('/logout-pelanggan', 'logout')->name('logoutpelanggan');
});

// Group route untuk customer
Route::prefixware('is.customer')->group(function () {

        Route::controller(CustomerController::class)->group(function () {
            Route::get('/customer/akun/{id}', 'akun')->name('customer.akun');
            Route::put('/customer/updateakun/{id}', 'updateAkun')->name('customer.updateakun');
        });

        // order
        Route::controller(OrderController::class)->group(function () {
            Route::post('add-to-cart/{id}', 'addToCart')->name('order.addToCart');
            Route::get('cart', 'viewCart')->name('order.cart');
        });


});


Route::get('/auth/redirect', [CustomerController::class, 'redirect'])->name('auth.redirect');
Route::get('/auth/google/callback', [CustomerController::class, 'callback'])->name('auth.callback');

Route::get('/', function () {
    return redirect()->route('beranda');
});

Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
Route::get('backend/beranda', [BerandaController::class, 'berandaBackend'])->name('backend.beranda')->middleware('auth');
Route::get('backend/login', [LoginController::class, 'loginBackend'])->name('backend1.login');
Route::post('backend/login', [LoginController::class, 'authenticateBackend'])->name('backend.login');
Route::post('backend/logout', [LoginController::class, 'logoutBackend'])->name('backend.logout');

// Route untuk User
// Route::resource('backend/user', UserController::class)->middleware('auth');
Route::resource('backend/user', UserController::class, ['as' => 'backend'])->middleware('auth');
// Route untuk laporan user
Route::get('backend/laporan/formuser', [UserController::class, 'formUser'])->name('backend.laporan.formuser')->middleware('auth');
Route::post('backend/laporan/cetakuser', [UserController::class, 'cetakUser'])->name('backend.laporan.cetakuser')->middleware('auth');
// Route untuk Kategori
Route::resource('backend/kategori', KategoriController::class, ['as' => 'backend'])->middleware('auth');
// Route untuk Produk
Route::resource('backend/produk', ProdukController::class, ['as' => 'backend'])->middleware('auth');
// Route untuk menambahkan foto
Route::post('foto-produk/store', [ProdukController::class, 'storeFoto'])->name('backend.foto_produk.store')->middleware('auth');
// Route untuk menghapus foto
Route::delete('foto-produk/{id}', [ProdukController::class, 'destroyFoto'])->name('backend.foto_produk.destroy')->middleware('auth');
// Route untuk laporan produk
Route::get('backend/laporan/formproduk', [ProdukController::class, 'formProduk'])->name('backend.laporan.formproduk')->middleware('auth');
Route::post('backend/laporan/cetakproduk', [ProdukController::class, 'cetakProduk'])->name('backend.laporan.cetakproduk')->middleware('auth');
// Frontend
Route::get('/beranda', [BerandaController::class, 'index'])->name('beranda');
Route::get('/produk/detail/{id}', [ProdukController::class, 'detail'])->name('produk.detail');
Route::get('/produk/kategori/{id}', [ProdukController::class,'produkKategori'])->name('produk.kategori');
Route::get('/produk/all', [ProdukController::class, 'produkAll'])->name('produk.all');

/// Logout
Route::post('/logout', [CustomerController::class, 'logout'])->name('logout');