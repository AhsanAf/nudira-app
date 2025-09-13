<?php

namespace App\Support;

use Carbon\Carbon;

class Date
{
    /**
     * Ubah berbagai format tanggal UI (d-m-Y, d/m/Y, dst) → ISO `Y-m-d` untuk DB.
     * Return null jika tidak valid (agar validasi bisa menolak).
     */
    public static function toIso(?string $value): ?string
    {
        if (!$value) return null;
        $value = trim($value);

        // daftar format yang kamu pakai di UI
        $formats = [
            'd-m-Y', 'd/m/Y',
            'Y-m-d', 'Y/m/d',
            'd M Y', 'd M y',
        ];

        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                // coba format berikutnya
            }
        }

        // fallback: biarkan Carbon deteksi sendiri (bisa gagal juga)
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Parse aman untuk ditampilkan (kembalikan Carbon|null).
     */
    public static function toCarbon(?string $value): ?Carbon
    {
        if (!$value) return null;
        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
