<?php

namespace App\Http\Controllers;

use App\Models\Pembina;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembinaPhotoController extends Controller
{
    public function __invoke(Request $request, Pembina $pembina)
    {
        $internal = $request->user()?->is_active && in_array($request->user()?->role, ['admin', 'super_admin'], true);
        if ((! $internal && (! $pembina->setuju_publikasi || ! $pembina->periode()->exists())) || ! $pembina->foto) {
            return response('', 404)->header('Cache-Control', 'no-store');
        }
        $disk = Storage::disk('r2_private');
        if (! $disk->exists($pembina->foto)) {
            return response('', 404)->header('Cache-Control', 'no-store');
        }
        $stream = $disk->readStream($pembina->foto);

        return response()->stream(function () use ($stream) {
            try {
                fpassthru($stream);
            } finally {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => $request->user() ? 'no-store' : 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
