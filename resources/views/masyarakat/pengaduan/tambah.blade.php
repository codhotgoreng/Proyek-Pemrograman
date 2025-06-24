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
                        {{-- Mengatur max date ke hari ini dan mengisi nilai default --}}
                        <input type="date" class="form-control" id="tgl_pengaduan" name="tgl_pengaduan" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="nik"><span class="required"> Pembuat Pengaduan</span></label>
                        {{-- Menampilkan nama pengguna yang sedang login dan tidak bisa diubah --}}
                        <p class="form-control-static">
                            @auth('masyarakat') {{-- Memastikan pengguna dengan guard 'masyarakat' sedang login --}}
                                {{ Auth::guard('masyarakat')->user()->nama ?? 'Nama Pengguna Tidak Ditemukan' }}
                            @else
                                Harap Login Sebagai Masyarakat
                            @endauth
                        </p>
                        {{-- Mengirim NIK pengguna secara tersembunyi --}}
                        {{-- Nilai diambil dari user yang sedang login, bukan dari input yang terlihat --}}
                        <input type="hidden" name="nik" value="{{ Auth::guard('masyarakat')->user()->nik ?? '' }}">
                    </div>

                    <div class="form-group">
                        <label for="isi_laporan"><span class="required">Isi Laporan</span></label>
                        <textarea type="text" class="form-control" id="isi_laporan" placeholder="" name="isi_laporan">{{ old('isi_laporan') }}</textarea>
                        @error('isi_laporan')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="lokasi">Lokasi</label><br>
                        <button type="button" class="btn btn-primary mb-2" onclick="ambilLokasi()">Ambil Lokasi Saya</button>
                    
                        <input type="text" name="latitude" id="latitude" class="form-control mb-2" placeholder="Latitude" readonly>
                        <input type="text" name="longitude" id="longitude" class="form-control" placeholder="Longitude" readonly>
                    
                        <small id="lokasiStatus" class="text-muted"></small>
                    </div>
                    
                    <div class="form-group">
                        <label for="foto"><span class="required">Foto</span></label>
                        <input type="file" class="form-control" id="foto" placeholder="foto" name="foto">
                    </div>
                    <div class="col-md-6 mb-3 mt-3">
                        <div class="m-n2">
                            <a href="/masyarakat_pengaduan" type="button" class="btn btn-danger m-2">Kembali</a>
                            <button type="submit" class="btn btn-success m-2">Kirim</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script>
        function ambilLokasi() {
            const statusEl = document.getElementById("lokasiStatus");
    
            if (navigator.geolocation) {
                statusEl.textContent = "Mengambil lokasi...";
                navigator.geolocation.getCurrentPosition(function (position) {
                    document.getElementById("latitude").value = position.coords.latitude;
                    document.getElementById("longitude").value = position.coords.longitude;
                    statusEl.textContent = "Lokasi berhasil diambil!";
                }, function (error) {
                    switch (error.code) {
                        case error.PERMISSION_DENIED:
                            statusEl.textContent = "Izin lokasi ditolak.";
                            break;
                        case error.POSITION_UNAVAILABLE:
                            statusEl.textContent = "Lokasi tidak tersedia.";
                            break;
                        case error.TIMEOUT:
                            statusEl.textContent = "Waktu pengambilan lokasi habis.";
                            break;
                        default:
                            statusEl.textContent = "Terjadi kesalahan saat mengambil lokasi.";
                            break;
                    }
                });
            } else {
                statusEl.textContent = "Browser tidak mendukung geolokasi.";
            }
        }
    </script>
    
@endsection