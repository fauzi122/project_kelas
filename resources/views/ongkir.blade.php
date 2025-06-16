<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cek Ongkir</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>

    <h2>Cek Ongkir</h2>
    <form id="ongkirForm">
        <label for="province">Provinsi:</label>
        <select name="province" id="province">
            <option value="">Pilih Provinsi</option>
        </select>

        <label for="city">Kota:</label>
        <select name="city" id="city">
            <option value="">Pilih Kota</option>
        </select>

        <label for="weight">Berat (gram):</label>
        <input type="number" name="weight" id="weight" placeholder="Berat dalam gram">

        <label for="courier">Kurir:</label>
        <select name="courier" id="courier">
            <option value="">Pilih Kurir</option>
            <option value="jne">JNE</option>
            <option value="tiki">TIKI</option>
            <option value="pos">POS Indonesia</option>
        </select>

        <button type="submit">Cek Ongkir</button>
    </form>

    <div id="result"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Ambil data provinsi
            fetch('/provinces')
                .then(response => response.json())
                .then(data => {
                    if (data.rajaongkir.status.code === 200) {
                        let provinces = data.rajaongkir.results;
                        let provinceSelect = document.getElementById('province');
                        provinces.forEach(province => {
                            let option = document.createElement('option');
                            option.value = province.province_id;
                            option.textContent = province.province;
                            provinceSelect.appendChild(option);
                        });
                    } else {
                        console.error('Gagal ambil provinsi:', data.rajaongkir.status.description);
                    }
                })
                .catch(error => {
                    console.error('Error ambil provinsi:', error);
                });

            // Ambil data kota berdasarkan provinsi
            document.getElementById('province').addEventListener('change', function () {
                let provinceId = this.value;
                fetch(`/cities?province_id=${provinceId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.rajaongkir.status.code === 200) {
                            let cities = data.rajaongkir.results;
                            let citySelect = document.getElementById('city');
                            citySelect.innerHTML = '<option value="">Pilih Kota</option>';
                            cities.forEach(city => {
                                let option = document.createElement('option');
                                option.value = city.city_id;
                                option.textContent = city.city_name;
                                citySelect.appendChild(option);
                            });
                        } else {
                            console.error('Gagal ambil kota:', data.rajaongkir.status.description);
                        }
                    })
                    .catch(error => {
                        console.error('Error ambil kota:', error);
                    });
            });

            // Cek ongkir
            document.getElementById('ongkirForm').addEventListener('submit', function (event) {
                event.preventDefault();

                let origin = 501; // ID kota asal (Contoh: Yogyakarta)
                let destination = document.getElementById('city').value;
                let weight = document.getElementById('weight').value;
                let courier = document.getElementById('courier').value;

                fetch('/cost', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        origin: origin,
                        destination: destination,
                        weight: weight,
                        courier: courier
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        let resultDiv = document.getElementById('result');
                        resultDiv.innerHTML = '';

                        if (data.rajaongkir.status.code === 200) {
                            let services = data.rajaongkir.results[0].costs;
                            services.forEach(service => {
                                let div = document.createElement('div');
                                div.textContent = `${service.service} : ${service.cost[0].value} Rupiah (${service.cost[0].etd} hari)`;
                                resultDiv.appendChild(div);
                            });
                        } else {
                            console.error('Gagal ambil ongkir:', data.rajaongkir.status.description);
                        }
                    })
                    .catch(error => {
                        console.error('Error ambil ongkir:', error);
                    });
            });
        });
    </script>

</body>
</html>
