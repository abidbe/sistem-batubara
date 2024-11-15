<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_tipe',
        'nama',
        'foto',
        'tahun',
        'kondisi',
        'hm',
        'keterangan'
    ];
}
