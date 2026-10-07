<?php

namespace App\Services;

use App\Models\Periode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContentScope
{
    public static function payload(Request $request, array $data, ?Model $existing = null): array
    {
        if (! $existing && ! Periode::exists()) {
            throw ValidationException::withMessages(['periode_id' => 'Buat periode terlebih dahulu.']);
        }
        if ($request->user()->role === 'admin_biro') {
            $data['biro_id'] = $request->user()->biro_id;
            $data['periode_id'] = $existing ? $existing->periode_id : Periode::aktif()->value('id');
            if (! $existing && ! $data['periode_id']) {
                throw ValidationException::withMessages(['periode_id' => 'Belum ada periode aktif. Hubungi admin.']);
            }
            if ($request->routeIs('admin.karya.*')) {
                $data['status'] = 'draft';
            }
        }

        return $data;
    }
}
