@extends('masyarakat.partials.main')

@section('content_masyarakat')
<form action="/masyarakat_pengaduan" method="POST" enctype="multipart/form-data">
    @csrf
    @method('post')
    <div class="container-fluid pt-4 px-4">
        <div class="bg-light rounded h-100 p-4">
            <h6 class="mb-4">Form Tambah Pengaduan</h6>
            <div class="bg-light rounded-top p-4 mt-3">
                <div class="form-group">
                    <label for="tgl_pengaduan"><span class="required">Tanggal Pengaduan</span></label>
                    <input type="date" class="form-control" id="tgl_pengaduan" name="tgl_pengaduan" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                </div>

                <div class="form-group">
                    <label for="nik"><span class="required"> Pembuat Pengaduan</span></label>
                    <p class="form-control-static">
                        @auth('masyarakat')
                            {{ Auth::guard('masyarakat')->user()->nama ?? 'Nama Pengguna Tidak Ditemukan' }}
                        @else
                            Harap Login Sebagai Masyarakat
                        @endauth
                    </p>
                    <input type="hidden" name="nik" value="{{ Auth::guard('masyarakat')->user()->nik ?? '' }}">
                </div>

                <div class="form-group">
                    <label for="isi_laporan"><span class="required">Isi Laporan</span></label>
                    <textarea class="form-control" id="isi_laporan" name="isi_laporan">{{ old('isi_laporan') }}</textarea>
                    @error('isi_laporan')
                        <p class="text-danger">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="lokasi">Lokasi</label><br>
                    <button type="button" class="btn btn-primary mb-2" onclick="getLocation()">Ambil Lokasi Saya</button>
                    <input type="hidden" name="latitude" id="latitude" class="form-control mb-2" placeholder="Latitude" readonly>
                    <input type="hidden" name="longitude" id="longitude" class="form-control" placeholder="Longitude" readonly>
                    <small id="lokasiStatus" class="text-muted"></small>
                    <div id="previewMap" style="height: 300px;" class="mt-3"></div>

                </div>

                <div class="form-group">
                    <label for="foto"><span class="required">Foto</span></label>
                    <input type="file" class="form-control" id="foto" name="foto">
                </div>

                <div class="col-md-6 mb-3 mt-3">
                    <div class="m-n2">
                        <a href="/masyarakat_pengaduan" class="btn btn-danger m-2">Kembali</a>
                        <button type="submit" class="btn btn-success m-2" onclick="return validateLocation()">Kirim</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    let map;

    function initMap(lat, lng) {
        const lokasi = { lat: lat, lng: lng };
        map = new google.maps.Map(document.getElementById("previewMap"), {
            zoom: 15,
            center: lokasi,
        });
        new google.maps.Marker({
            position: lokasi,
            map: map,
        });
    }

    function getLocation() {
        if (!navigator.geolocation) {
            alert("Browser tidak mendukung Geolocation.");
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const latitude = position.coords.latitude;
                const longitude = position.coords.longitude;

                document.getElementById('latitude').value = latitude;
                document.getElementById('longitude').value = longitude;
                alert("Lokasi berhasil diambil!");

                initMap(latitude, longitude); // Panggil Google Maps
            },
            function(error) {
                alert("Gagal ambil lokasi: " + (error.message || "Unknown error"));
                console.error(error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    function validateLocation() {
    const lat = document.getElementById('latitude').value;
    const lng = document.getElementById('longitude').value;

    if (!lat || !lng) {
        alert("Silakan ambil lokasi Anda terlebih dahulu.");
        return false;
    }
    return true;
}

</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXBKHpzkqxXQe7C2-GTzmoCpuRjHF6lOw"></script>

@endsection
