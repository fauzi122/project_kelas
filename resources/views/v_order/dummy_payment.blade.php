<!DOCTYPE html>
<html>
<head>
    <title>Midtrans Dummy Test</title>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-XXXXXXX"></script> {{-- GANTI --}}
</head>
<body>
    <h2>Test Pembayaran Midtrans</h2>
    <p>Silakan klik tombol di bawah untuk mencoba pembayaran dummy.</p>

    <button id="pay-button">Bayar Sekarang</button>

    <script type="text/javascript">
        document.getElementById('pay-button').addEventListener('click', function () {
            snap.pay('{{ $snapToken }}', {
                onSuccess: function(result) {
                    alert("Transaksi berhasil!");
                    console.log(result);
                },
                onPending: function(result) {
                    alert("Transaksi pending.");
                    console.log(result);
                },
                onError: function(result) {
                    alert("Transaksi gagal.");
                    console.log(result);
                },
                onClose: function() {
                    alert('Anda menutup popup tanpa menyelesaikan transaksi.');
                }
            });
        });
    </script>
</body>
</html>
