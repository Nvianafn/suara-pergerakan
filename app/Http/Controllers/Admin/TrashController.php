<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karya;
use App\Models\Kegiatan;
use App\Services\ActivityLog;
use App\Services\MediaCleanup;
use Illuminate\Support\Facades\DB;

class TrashController extends Controller
{
    private function model(string $type, int $id): Karya|Kegiatan
    {
        abort_unless(in_array($type, ['karya', 'kegiatan'], true), 404);

        return ($type === 'karya' ? Karya::class : Kegiatan::class)::onlyTrashed()->lockForUpdate()->findOrFail($id);
    }

    public function index()
    {
        return view('admin.trash.index', [
            'karya' => Karya::onlyTrashed()->latest('deleted_at')->paginate(15, ['*'], 'karya_page'),
            'kegiatan' => Kegiatan::onlyTrashed()->latest('deleted_at')->paginate(15, ['*'], 'kegiatan_page'),
        ]);
    }

    public function restore(string $type, int $id)
    {
        DB::transaction(function () use ($type, $id) {
            $content = $this->model($type, $id);
            $content->status = 'draft';
            $content->restore();
            ActivityLog::record($type, 'pemulihan', $id, 'Konten dipulihkan sebagai draft.');
        });

        return back()->with('success', 'Konten dipulihkan sebagai draft.');
    }

    public function purge(string $type, int $id)
    {
        DB::transaction(function () use ($type, $id) {
            $content = $this->model($type, $id);
            MediaCleanup::enqueue('public', $content->thumbnail);
            if ($content instanceof Kegiatan) {
                foreach ($content->foto as $foto) {
                    MediaCleanup::enqueue('public', $foto->path);
                    $foto->delete();
                }
            }
            ActivityLog::record($type, 'penghapusan_permanen', $id, 'Konten sampah dihapus permanen.');
            $content->forceDelete();
        });
        $remaining = MediaCleanup::run();

        return back()->with('success', $remaining ? 'Konten dihapus; cleanup media tertunda dan akan dicoba ulang.' : 'Konten dan media dihapus permanen.');
    }
}
