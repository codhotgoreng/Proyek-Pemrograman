<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengaduan extends Model
{
    use HasFactory;

    protected $guarded = ['id_pengaduan'];
    protected $table = 'pengaduan';

    // Tambahkan ini:
    protected $primaryKey = 'id_pengaduan';
    public $incrementing = true; // optional, tapi baik jika eksplisit
    protected $keyType = 'int'; // karena kolomnya tipe integer

    public function masyarakat()
    {
        return $this->belongsTo(Masyarakat::class, 'nik', 'nik');
    }
}
