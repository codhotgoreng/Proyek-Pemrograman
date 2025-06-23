<?php

namespace App\Http\Controllers;

use App\Models\Pengaduan;
use App\Models\Masyarakat; // Pastikan model Masyarakat sudah di-import
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth; // Penting: Import Facades Auth

class MasyarakatPengaduanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Mendapatkan pengaduan berdasarkan NIK pengguna yang sedang login
        // Ini memastikan setiap masyarakat hanya melihat pengaduannya sendiri.
        // Jika Anda ingin semua pengaduan terlihat oleh semua masyarakat, hapus filter where('nik', ...)
        $pengaduan = Pengaduan::where('nik', Auth::guard('masyarakat')->user()->nik ?? null)
                                ->latest()
                                ->get();

        return view('masyarakat.pengaduan.pengaduan', ['pengaduan' => $pengaduan]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Di sini kita tidak perlu mengirim data $masyarakat lagi
        // karena NIK pembuat pengaduan akan otomatis diambil dari user yang login.
        // Kita bisa menghapus baris Masyarakat::latest()->get(); jika tidak digunakan di view 'tambah'.
        return view('masyarakat.pengaduan.tambah');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Pastikan pengguna 'masyarakat' sudah login sebelum membuat pengaduan
        if (!Auth::guard('masyarakat')->check()) {
            return redirect()->back()->with('error', 'Anda harus login sebagai masyarakat untuk membuat pengaduan.');
        }

        // Ambil objek pengguna yang sedang login
        $user = Auth::guard('masyarakat')->user();

        // Validasi data input dari formulir
        $validatedData = $request->validate([
            // tgl_pengaduan: wajib diisi, berupa tanggal, dan tidak boleh melebihi hari ini
            'tgl_pengaduan' => 'required|date|before_or_equal:' . date('Y-m-d'),
            // nik tidak perlu divalidasi dari request karena akan diambil dari user yang login
            'isi_laporan' => 'required|string|max:2000', // Batasi panjang isi laporan
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Opsional, hanya gambar, max 2MB
        ]);

        $fotoName = null;
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $extension = $foto->getClientOriginalExtension();
            $imageName = date('Ymd_His') . '_' . uniqid() . '.' . $extension; // Tambah uniqid untuk lebih unik
            $foto->move(public_path('foto'), $imageName);
            $fotoName = $imageName; // Simpan nama file foto untuk database
        }

        // Buat record pengaduan baru
        Pengaduan::create([
            'tgl_pengaduan' => $validatedData['tgl_pengaduan'],
            'nik' => $user->nik, // NIK diambil dari pengguna yang sedang login
            'isi_laporan' => $validatedData['isi_laporan'],
            'foto' => $fotoName, // Gunakan $fotoName yang mungkin null jika tidak ada foto
            'status' => '0', // Set status awal pengaduan (misal: 0 = Menunggu)
        ]);

        return redirect('masyarakat_pengaduan')->with('success', 'Pengaduan Anda berhasil dikirim!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Pengaduan  $pengaduan
     * @return \Illuminate\Http\Response
     */
    public function show(Pengaduan $pengaduan)
    {
        // Implementasi untuk menampilkan detail satu pengaduan (jika diperlukan)
        // Anda mungkin ingin menambahkan pengecekan apakah pengaduan ini milik user yang login.
        if (Auth::guard('masyarakat')->user()->nik !== $pengaduan->nik) {
            abort(403, 'Unauthorized action.'); // Mencegah user melihat pengaduan orang lain
        }
        return view('masyarakat.pengaduan.show', compact('pengaduan'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $id_pengaduan
     * @return \Illuminate\Http\Response
     */
    public function edit($id_pengaduan) // Ubah parameter menjadi $id_pengaduan string
    {
        $pengaduan = Pengaduan::where('id_pengaduan', $id_pengaduan)->firstOrFail(); // Gunakan firstOrFail

        // Pastikan hanya pemilik pengaduan yang bisa mengedit
        if (Auth::guard('masyarakat')->user()->nik !== $pengaduan->nik) {
            abort(403, 'Anda tidak memiliki izin untuk mengedit pengaduan ini.');
        }

        // Tidak perlu mengambil data Masyarakat lagi di sini
        return view('masyarakat.pengaduan.edit', ['pengaduan' => $pengaduan]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id_pengaduan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id_pengaduan) // Ubah parameter menjadi $id_pengaduan string
    {
        $pengaduan = Pengaduan::where('id_pengaduan', $id_pengaduan)->firstOrFail();

        // Pastikan hanya pemilik pengaduan yang bisa mengupdate
        if (Auth::guard('masyarakat')->user()->nik !== $pengaduan->nik) {
            abort(403, 'Anda tidak memiliki izin untuk memperbarui pengaduan ini.');
        }

        // Validasi data yang masuk
        $validatedData = $request->validate([
            // tgl_pengaduan: wajib diisi, berupa tanggal, dan tidak boleh melebihi hari ini
            'tgl_pengaduan' => 'required|date|before_or_equal:' . date('Y-m-d'),
            // nik tidak perlu divalidasi atau diupdate dari request karena harus tetap sama dengan user yang login
            'isi_laporan' => 'required|string|max:2000',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $updateData = $request->only([
            'tgl_pengaduan',
            'isi_laporan',
        ]);

        if ($request->hasFile('foto')) {
            // Hapus gambar lama jika ada dan bukan gambar default atau placeholder
            if ($pengaduan->foto && File::exists(public_path('foto/' . $pengaduan->foto))) {
                File::delete(public_path('foto/' . $pengaduan->foto));
            }

            // Simpan gambar baru
            $foto = $request->file('foto');
            $extension = $foto->getClientOriginalExtension();
            $imageName = date('Ymd_His') . '_' . uniqid() . '.' . $extension;
            $foto->move(public_path('foto'), $imageName);
            $updateData['foto'] = $imageName;
        }

        // Update pengaduan
        $pengaduan->update($updateData);

        return redirect('masyarakat_pengaduan')->with('success', 'Pengaduan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id_pengaduan
     * @return \Illuminate\Http\Response
     */
    public function destroy($id_pengaduan) // Ubah parameter menjadi $id_pengaduan string
    {
        $pengaduan = Pengaduan::where('id_pengaduan', $id_pengaduan)->firstOrFail();

        // Pastikan hanya pemilik pengaduan yang bisa menghapus
        if (Auth::guard('masyarakat')->user()->nik !== $pengaduan->nik) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus pengaduan ini.');
        }

        // Hapus file foto jika ada
        if ($pengaduan->foto && File::exists(public_path('foto/' . $pengaduan->foto))) {
            File::delete(public_path('foto/' . $pengaduan->foto));
        }

        // Hapus record pengaduan dari database
        $pengaduan->delete();

        return redirect('masyarakat_pengaduan')->with('success', 'Pengaduan berhasil dihapus!');
    }
}