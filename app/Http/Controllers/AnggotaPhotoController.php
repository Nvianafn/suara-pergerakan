<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnggotaPhotoController extends Controller
{
    public function __invoke(Request $request, int $id)
    {
        $anggota = Anggota::find($id);
        $internal = $request->user()?->is_active && in_array($request->user()?->role, ['admin', 'super_admin'], true);
        if (! $anggota || (! $internal && ! $anggota->setuju_publikasi) || ! $anggota->foto) {
            return response('', 404)->header('Cache-Control', 'no-store');
        }
        $disk = Storage::disk('r2_private');
        if (! $disk->exists($anggota->foto)) {
            return response('', 404)->header('Cache-Control', 'no-store');
        }
        $stream = $disk->readStream($anggota->foto);

        return response()->stream(function () use ($stream) {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, ['Content-Type' => 'image/webp', 'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => $request->user() ? 'no-store' : 'private, max-age=3600']);
    }
}
