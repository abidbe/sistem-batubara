<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timesheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'tanggal',
        'jam_kerja',
        'hm_awal',
        'hm_akhir',
        'keterangan'
    ];


    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    protected static function booted()
    {
        static::creating(function ($timesheet) {
            // Set HM awal berdasarkan timesheet sebelumnya atau HM unit
            $previousTimesheet = static::where('unit_id', $timesheet->unit_id)
                ->where('tanggal', '<', $timesheet->tanggal)
                ->orderBy('tanggal', 'desc')
                ->first();

            $timesheet->hm_awal = $previousTimesheet ?
                $previousTimesheet->hm_akhir :
                $timesheet->unit->hm;

            // Set HM akhir
            $timesheet->hm_akhir = $timesheet->hm_awal + $timesheet->jam_kerja;

            // Update HM unit
            $timesheet->unit->hm = $timesheet->hm_akhir;
            $timesheet->unit->save();
        });

        static::updating(function ($timesheet) {
            // Ambil data original sebelum update
            $originalTimesheet = $timesheet->getOriginal();

            // Jika tanggal berubah, recalculate HM awal
            if ($timesheet->tanggal != $originalTimesheet['tanggal']) {
                $previousTimesheet = static::where('unit_id', $timesheet->unit_id)
                    ->where('tanggal', '<', $timesheet->tanggal)
                    ->orderBy('tanggal', 'desc')
                    ->first();

                $timesheet->hm_awal = $previousTimesheet ?
                    $previousTimesheet->hm_akhir :
                    $timesheet->unit->hm;
            }

            // Update HM akhir berdasarkan jam_kerja baru
            $timesheet->hm_akhir = $timesheet->hm_awal + $timesheet->jam_kerja;

            // Update HM unit dan update semua timesheet setelahnya
            static::updateSubsequentTimesheets($timesheet);
        });

        static::updated(function ($timesheet) {
            // Update HM unit ke HM akhir dari timesheet terbaru
            $latestTimesheet = static::where('unit_id', $timesheet->unit_id)
                ->orderBy('tanggal', 'desc')
                ->first();

            if ($latestTimesheet) {
                $timesheet->unit->hm = $latestTimesheet->hm_akhir;
                $timesheet->unit->save();
            }
        });

        static::deleted(function ($timesheet) {
            // Ambil timesheet setelah yang dihapus
            $subsequentTimesheets = static::where('unit_id', $timesheet->unit_id)
                ->where('tanggal', '>', $timesheet->tanggal)
                ->orderBy('tanggal', 'asc')
                ->get();

            // Update HM untuk timesheet-timesheet setelahnya
            $previousHM = $timesheet->hm_awal; // Gunakan HM awal dari timesheet yang dihapus
            foreach ($subsequentTimesheets as $subsequentTimesheet) {
                $subsequentTimesheet->hm_awal = $previousHM;
                $subsequentTimesheet->hm_akhir = $subsequentTimesheet->hm_awal + $subsequentTimesheet->jam_kerja;
                $subsequentTimesheet->save();
                $previousHM = $subsequentTimesheet->hm_akhir;
            }

            // Update HM unit ke timesheet terakhir atau HM awal jika tidak ada timesheet lain
            $latestTimesheet = static::where('unit_id', $timesheet->unit_id)
                ->orderBy('tanggal', 'desc')
                ->first();

            $timesheet->unit->hm = $latestTimesheet ?
                $latestTimesheet->hm_akhir :
                $timesheet->hm_awal;
            $timesheet->unit->save();
        });
    }

    // Helper method untuk update timesheet berikutnya
    private static function updateSubsequentTimesheets($timesheet)
    {
        $subsequentTimesheets = static::where('unit_id', $timesheet->unit_id)
            ->where('tanggal', '>', $timesheet->tanggal)
            ->orderBy('tanggal', 'asc')
            ->get();

        $previousHM = $timesheet->hm_akhir;
        foreach ($subsequentTimesheets as $subsequentTimesheet) {
            $subsequentTimesheet->hm_awal = $previousHM;
            $subsequentTimesheet->hm_akhir = $subsequentTimesheet->hm_awal + $subsequentTimesheet->jam_kerja;
            $subsequentTimesheet->save();
            $previousHM = $subsequentTimesheet->hm_akhir;
        }
    }


}
