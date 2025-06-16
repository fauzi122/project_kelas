<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Ongkir</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Fungsi untuk mengubah kota berdasarkan provinsi yang dipilih
        $(document).ready(function() {
            $('#provinsi').change(function() {
                var provinsi = $(this).val();
                var kotaOptions = '<option value="">Pilih Kota</option>';
                
                // Ambil kota berdasarkan provinsi yang dipilih
                var kotaList = @json($provinsiKota);
                if (provinsi && kotaList[provinsi]) {
                    kotaList[provinsi].forEach(function(kota) {
                        kotaOptions += `<option value="${kota}">${kota}</option>`;
                    });
                }

                $('#kota').html(kotaOptions);
            });
        });
    </script>
</head>
<body>
    <h1>Cek Ongkir</h1>

    <form action="{{ route('cekOngkir') }}" method="POST">
        @csrf
        <div>
            <label for="provinsi">Provinsi:</label>
            <select name="provinsi" id="provinsi">
                <option value="">Pilih Provinsi</option>
                @foreach($provinsiKota as $provinsi => $kotaList)
                    <option value="{{ $provinsi }}">{{ $provinsi }}</option>
                @endforeach
            </select>
        </div>
        
        <div>
            <label for="kota">Kota:</label>
            <select name="kota" id="kota">
                <option value="">Pilih Kota</option>
            </select>
        </div>

        <div>
            <label for="berat">Berat (gram):</label>
            <input type="number" name="berat" id="berat" value="1" />
        </div>

        <div>
            <label for="kurir">Kurir:</label>
            <select name="kurir" id="kurir">
                <option value="JNE">JNE</option>
                <option value="Tiki">Tiki</option>
                <option value="Pos">Pos</option>
            </select>
        </div>

        <button type="submit">Cek Ongkir</button>
    </form>

    @if(isset($ongkir))
        <h3>Hasil Ongkir</h3>
        <table border="1">
            <tr>
                <th>Provinsi</th>
                <th>Kota</th>
                <th>Berat</th>
                <th>Kurir</th>
                <th>Ongkir</th>
            </tr>
            <tr>
                <td>{{ $provinsiInput }}</td>
                <td>{{ $kotaInput }}</td>
                <td>{{ $beratInput }} gram</td>
                <td>{{ $kurirInput }}</td>
                <td>{{ $ongkir }}</td>
            </tr>
        </table>
    @endif
</body>
</html>
