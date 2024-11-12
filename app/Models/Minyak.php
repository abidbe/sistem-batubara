<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Minyak extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'tanggal',
        'masuk',
        'keluar',
        'keterangan',
        'nama_pengguna',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'masuk' => 'integer',
        'keluar' => 'integer',
    ];

    // Optional: Menambah method untuk mendapatkan stock terakhir
    public static function getLastStock()
    {
        return static::orderBy('id', 'desc')->first()?->stock ?? 0;
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class)->withDefault([
            'nama' => '-'  // Nilai default jika unit null
        ]);
    }
}
