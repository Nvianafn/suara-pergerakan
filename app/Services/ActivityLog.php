<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class ActivityLog
{
    public static function pembina(string $aksi, int $id, string $ringkasan): void
    {
        self::record('pembina', $aksi, $id, $ringkasan);
    }

    public static function record(string $type, string $aksi, int $id, string $ringkasan): void
    {
        DB::table('riwayat_aktivitas')->insert([
            'user_id' => auth()->id(), 'aksi' => $aksi,
            'subjek_tipe' => $type, 'subjek_id' => $id,
            'ringkasan' => $ringkasan, 'created_at' => now(),
        ]);
    }
}
