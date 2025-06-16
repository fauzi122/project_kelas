@extends('v_layouts.app')

@section('content')
<!-- Template -->
<div class="col-md-12">
    <div class="order-summary clearfix">
        <div class="section-title">
            <p>KERANJANG</p>
            <h3 class="title">Keranjang Belanja</h3>
        </div>

        <!-- Success Message -->
        @if(session()->has('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>{{ session('success') }}</strong>
            </div>
        @endif
        <!-- End Success Message -->

        <!-- Error Message -->
        @if(session()->has('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <strong>{{ session('error') }}</strong>
            </div>
        @endif
        <!-- End Error Message -->

        @if($order && $order->orderItems->count() > 0)
            <table class="shopping-cart-table table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th></th>
                        <th class="text-center">Harga</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Total</th>
                        <th class="text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalHarga = 0;
                        $totalBerat = 0;
                    @endphp
                    @foreach($order->orderItems as $item)
                        @php
                            $totalHarga += $item->harga * $item->quantity;
                            $totalBerat += $item->produk->berat * $item->quantity;
                        @endphp
                        <tr>
                            <td class="thumb">
                                <img src="{{ asset('storage/img-produk/thumb_sm_' . $item->produk->foto) }}" alt="">
                            </td>
                            <td class="details">
                                <a>{{ $item->produk->nama_produk }}</a>
                                <ul>
                                    <li><span>Berat: {{ $item->produk->berat }} Gram</span></li>
                                    <li><span>Stok: {{ $item->produk->stok }} Gram</span></li>
                                </ul>
                            </td>
                            <td class="price text-center">
                                <strong>Rp. {{ number_format($item->harga, 0, ',', '.') }}</strong>
                            </td>
                            <td class="qty text-center">
                                {{-- <form action="{{ route('order.updateCart', $item->id) }}" method="post">
                                    @csrf
                                    <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" style="width: 60px;">
                                    <button type="submit" class="btn btn-sm btn-warning">Update</button>
                                </form> --}}
                                <form action="{{ route('order.updateCart', $item->id) }}" method="post" class="quantity-form" id="quantityForm">
                                    @csrf
                                    <div class="quantity-input">
                                        <button type="button" class="quantity-btn minus">-</button>
                                        <input type="text" name="quantity" value="{{ $item->quantity }}" class="quantity-field" oninput="validateInput(this)" onchange="updateQuantityAndScroll(this)">
                                        <button type="button" class="quantity-btn plus">+</button>
                                    </div>
                                </form>
 
                            </td>
                            <td class="total text-center">
                                <strong class="primary-color">Rp. {{ number_format($item->harga * $item->quantity, 0, ',', '.') }}</strong>
                            </td>
                            <td class="text-right">
                                <form action="{{ route('order.remove', $item->produk->id) }}" method="post" id="remove-form-{{ $item->produk->id }}">
                                    @csrf
                                    <button type="button" class="main-btn icon-btn" onclick="confirmDelete({{ $item->produk->id }})">
                                        <i class="fa fa-close"></i>
                                    </button>
                                </form>

                                
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <form action="{{ route('order.selectShipping') }}" method="post">
                @csrf
                <input type="hidden" name="total_price" value="{{ $totalHarga }}">
                <input type="hidden" name="total_weight" value="{{ $totalBerat }}">
                <div class="pull-right">
                    <button class="primary-btn">Pilih Pengiriman</button>
                </div>
            </form>
        @else
            <p>Keranjang belanja kosong.</p>
        @endif

        
    </div>
</div>
<!-- End Template -->
<style>
    .quantity-input {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quantity-btn {
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        padding: 5px 10px;
        font-size: 16px;
        cursor: pointer;
        display: inline-block;
        width: 40px;
        height: 40px;
        text-align: center;
        line-height: 1;
    }

    .quantity-field {
        text-align: center;
        width: 60px;
        margin: 0;
        padding: 5px;
        border: 1px solid #ccc;
        font-size: 16px;
        height: 40px; /* Menyamakan tinggi dengan tombol */
    }

    .quantity-btn:hover {
        background-color: #ddd;
    }
</style>

<script>
    // Fungsi untuk memastikan input hanya berupa angka
    function validateInput(input) {
        // Hanya biarkan angka yang bisa dimasukkan
        input.value = input.value.replace(/[^0-9]/g, '');
    }

    // Fungsi untuk mengupdate kuantitas ketika tombol "+" atau "-" diklik
    document.querySelector('.minus').addEventListener('click', function() {
        let inputField = document.querySelector('.quantity-field');
        let value = parseInt(inputField.value) || 1; // Default ke 1 jika kosong atau NaN
        if (value > 1) {
            inputField.value = value - 1;
            inputField.form.submit(); // Submit form setelah mengubah kuantitas
            scrollToForm(); // Scroll ke form setelah submit
        }
    });

    document.querySelector('.plus').addEventListener('click', function() {
        let inputField = document.querySelector('.quantity-field');
        let value = parseInt(inputField.value) || 1; // Default ke 1 jika kosong atau NaN
        inputField.value = value + 1;
        inputField.form.submit(); // Submit form setelah mengubah kuantitas
        scrollToForm(); // Scroll ke form setelah submit
    });

    // Fungsi untuk mengupdate kuantitas dan scroll ke form
    function updateQuantityAndScroll(inputField) {
        inputField.form.submit(); // Submit form setelah mengubah kuantitas
        scrollToForm(); // Scroll ke form setelah submit
    }

    // Fungsi untuk scroll otomatis ke form setelah update
    function scrollToForm() {
        const formElement = document.getElementById('quantityForm');
        formElement.scrollIntoView({
            behavior: 'smooth',
            block: 'center' // Mengatur posisi elemen di tengah layar
        });
    }
</script>

                                
<script>
    function confirmDelete(itemId) {
        // Tampilkan konfirmasi menggunakan dialog bawaan browser
        if (confirm('Apakah Anda yakin ingin menghapus item ini dari keranjang?')) {
            // Jika pengguna mengklik "OK", kirimkan form untuk menghapus item
            document.getElementById('remove-form-' + itemId).submit();
        }
    }
</script>
@endsection
