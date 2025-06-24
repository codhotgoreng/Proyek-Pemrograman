@extends('masyarakat.partials.main')
@section('content_masyarakat')
    <form action="/masyarakat_pengaduan/{{ $pengaduan->id_pengaduan }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('put')
        <div class="col-12">
            <div class="bg-light rounded h-100 p-4">
                <h6 class="mb-4">Form Edit Pengaduan</h6>

                <div class="form-group">
                    <label for="tgl_pengaduan"><span class="required">Tanggal Pengaduan</span></label>
                    <input type="date" class="form-control" id="tgl_pengaduan" name="tgl_pengaduan" max="{{ date('Y-m-d') }}" value="{{ $pengaduan->tgl_pengaduan }}">
                </div>

                <div class="form-group">
                    <label><span class="required">Pembuat Pengaduan</span></label>
                    <input type="text" class="form-control" value="{{ $pengaduan->masyarakat->nama ?? '-' }}" readonly>
                    <input type="hidden" name="nik" value="{{ $pengaduan->nik }}">
                </div>

                <div class="form-group">
                    <label for="isi_laporan"><span class="required">Isi Laporan</span></label>
                    <textarea type="text" class="form-control" id="isi_laporan" name="isi_laporan">{{ $pengaduan->isi_laporan }}</textarea>
                </div>

                {{-- Lokasi --}}
                <div class="form-group">
                    <label for="lokasi"><span class="required">Lokasi</span></label><br>
                    <button type="button" class="btn btn-primary mb-2" onclick="getLocation()">Ambil Lokasi Saya</button>

                    <input type="hidden" name="latitude" id="latitude" value="{{ $pengaduan->latitude ?? '' }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ $pengaduan->longitude ?? '' }}">
                    <div id="previewMap" style="height: 300px;" class="mt-3"></div>

                    <small id="lokasiStatus" class="text-muted">
                        @if($pengaduan->latitude && $pengaduan->longitude)
                            Lokasi sebelumnya: Lat {{ $pengaduan->latitude }}, Lng {{ $pengaduan->longitude }}
                        @endif
                    </small>
                </div>

                <div class="form-group">
                    <label for="foto"><span class="required">Foto</span></label>
                    <input type="file" class="form-control" id="foto" name="foto">
                    @if ($pengaduan->foto)
                        <img src="{{ asset('foto/' . $pengaduan->foto) }}" width="60" alt="Foto" class="mt-3">
                    @else
                        <span>Tidak ada foto</span>
                    @endif
                </div>

                <div class="col-md-6 mb-3 mt-3">
                    <div class="m-n2">
                        <a href="/masyarakat_pengaduan" type="button" class="btn btn-danger m-2">Kembali</a>
                        <button type="submit" class="btn btn-success m-2">Kirim</button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- Script Lokasi --}}
    <script>
        function getLocation() {
            if (!navigator.geolocation) {
                alert("Browser tidak mendukung Geolocation.");
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                    document.getElementById('lokasiStatus').innerText = "Lokasi berhasil diambil: Lat " + position.coords.latitude + ", Lng " + position.coords.longitude;
                },
                function(error) {
                    let pesan = "";
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            pesan = "Izin lokasi ditolak.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            pesan = "Informasi lokasi tidak tersedia.";
                            break;
                        case error.TIMEOUT:
                            pesan = "Permintaan lokasi melebihi waktu tunggu.";
                            break;
                        default:
                            pesan = "Terjadi kesalahan tidak diketahui.";
                    }
                    document.getElementById('lokasiStatus').innerText = pesan;
                    console.error("Gagal ambil lokasi:", error);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }
    </script>
    {{-- Script Lokasi dan Google Maps --}}
<script>
    let map;
    let marker;

    function initMap(lat = -6.200000, lng = 106.816666) {
        const lokasi = { lat: parseFloat(lat), lng: parseFloat(lng) };

        map = new google.maps.Map(document.getElementById("previewMap"), {
            center: lokasi,
            zoom: 15,
        });

        marker = new google.maps.Marker({
            position: lokasi,
            map: map,
            title: "Lokasi Anda",
        });
    }

    function getLocation() {
        if (!navigator.geolocation) {
            alert("Browser tidak mendukung Geolocation.");
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                document.getElementById("latitude").value = lat;
                document.getElementById("longitude").value = lng;

                document.getElementById("lokasiStatus").innerText = `Lokasi berhasil diambil: Lat ${lat}, Lng ${lng}`;

                initMap(lat, lng);
            },
            function (error) {
                let pesan = "";
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        pesan = "Izin lokasi ditolak.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        pesan = "Informasi lokasi tidak tersedia.";
                        break;
                    case error.TIMEOUT:
                        pesan = "Permintaan lokasi melebihi waktu tunggu.";
                        break;
                    default:
                        pesan = "Terjadi kesalahan tidak diketahui.";
                }
                document.getElementById("lokasiStatus").innerText = pesan;
                console.error("Gagal ambil lokasi:", error);
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0,
            }
        );
    }

    // Inisialisasi map saat halaman dimuat (jika ada koordinat lama)
    document.addEventListener("DOMContentLoaded", function () {
        const lat = document.getElementById("latitude").value;
        const lng = document.getElementById("longitude").value;
        if (lat && lng) {
            initMap(lat, lng);
        } else {
            initMap(); // default location Jakarta
        }
    });
</script>


    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDXBKHpzkqxXQe7C2-GTzmoCpuRjHF6lOw"></script>

@endsection
