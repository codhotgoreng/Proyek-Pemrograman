<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Penting: Menggunakan Authenticatable

class Masyarakat extends Authenticatable
{
    use HasFactory;

    // Nama tabel yang terkait dengan model
    protected $table = 'masyarakat';

    // Mendefinisikan 'nik' sebagai primary key tabel
    protected $primaryKey = 'nik';

    // Karena 'nik' bukan auto-incrementing integer, kita set ini ke false
    public $incrementing = false;

    // Menentukan tipe data primary key adalah string
    protected $keyType = 'string';

    // Kolom yang dapat diisi secara massal.
    // Jika Anda menggunakan guarded = ['id'], maka semua kolom lain bisa diisi.
    // Namun, lebih eksplisit dengan fillable adalah praktik yang baik.
    protected $fillable = [
        'nik',
        'nama',
        'username',
        'password',
        'telp',
    ];

    // Kolom yang harus disembunyikan saat serialisasi (misalnya ke JSON)
    protected $hidden = [
        'password',
        // 'remember_token', // Tambahkan ini jika Anda menggunakan fitur "remember me"
    ];

    // Jika Anda memiliki kolom timestamp (created_at, updated_at), ini sudah default true
    // public $timestamps = true;
}