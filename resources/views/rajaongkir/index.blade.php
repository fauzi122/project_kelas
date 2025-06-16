<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raja Ongkir</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Menambahkan meta tag untuk CSRF token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

    <h1>Raja Ongkir - Provinces, Cities, and Shipping Cost</h1>

    <!-- Dropdown Provinsi -->
    <label for="province">Select Province: </label>
    <select id="province" name="province" onchange="getCities()" required>
        <option value="">Select Province</option>
        @foreach ($provinces as $province)
            <option value="{{ $province->id }}">{{ $province->province }}</option>
        @endforeach
    </select>

    <!-- Dropdown Kota (Origin) -->
   <label for="origin">Select Origin City: </label>
<select id="origin" name="origin" required>
    <option value="">Select City</option>
</select>

<!-- Dropdown Kota Tujuan (Destination) -->
<label for="destination">Select Destination City: </label>
<select id="destination" name="destination" required>
    <option value="">Select City</option>
</select>

    <!-- Form Pengiriman -->
    <h2>Shipping Cost</h2>
    <form action="{{ url('/cost') }}" method="POST">
        @csrf <!-- CSRF token -->
        <label for="weight">Weight (grams): </label>
        <input type="number" id="weight" name="weight" required>

        <label for="courier">Select Courier: </label>
        <select id="courier" name="courier" required>
            <option value="jne">JNE</option>
            <option value="tiki">TIKI</option>
            <option value="pos">POS</option>
            <option value="gojek">Gojek</option>
            <option value="grab">Grab</option>
        </select>

        <button type="submit">Get Cost</button>
    </form>

    <!-- Tampilkan hasil biaya pengiriman -->
    @if (isset($shippingCost))
        <h3>Cost: IDR {{ $shippingCost }}</h3>
    @endif

    <script>
        // Fungsi untuk mengambil kota berdasarkan provinsi
// Fungsi untuk mengambil kota berdasarkan provinsi
function getCities() {
    var provinceId = document.getElementById('province').value;
    if (provinceId) {
        fetch('/cities?province_id=' + provinceId)
            .then(response => response.json())
            .then(data => {
                var originSelect = document.getElementById('origin');
                var destinationSelect = document.getElementById('destination');

                // Kosongkan dropdown dan tambahkan pilihan default
                originSelect.innerHTML = '<option value="">Select City</option>';
                destinationSelect.innerHTML = '<option value="">Select City</option>';

                // Tambahkan pilihan kota
                data.forEach(city => {
                    originSelect.innerHTML += `<option value="${city.city_name}">${city.city_name}</option>`;
                    destinationSelect.innerHTML += `<option value="${city.city_name}">${city.city_name}</option>`;
                });

                // Aktifkan dropdown kota
                originSelect.disabled = false;
                destinationSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching cities:', error);
            });
    } else {
        // Reset dan nonaktifkan dropdown kota
        document.getElementById('origin').disabled = true;
        document.getElementById('destination').disabled = true;
        document.getElementById('origin').innerHTML = '<option value="">Select City</option>';
        document.getElementById('destination').innerHTML = '<option value="">Select City</option>';
    }
}

    </script>

</body>
</html>
