<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KegiatanRequest;
use App\Models\Biro;
use App\Models\Kegiatan;
use App\Services\ActivityLog;
use App\Services\ContentScope;
use App\Services\GalleryLimits;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class KegiatanController extends Controller
{
    public function __construct(private readonly ImageService $image) {}

    public function index(): View
    {
        return view('admin.kegiatan.index', [
            'kegiatan' => Kegiatan::with('biro')->when(auth()->user()->role === 'admin_biro', fn ($q) => $q->where('biro_id', auth()->user()->biro_id))->latest('tanggal')->paginate(12),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Kegiatan::class);

        return view('admin.kegiatan.create', [
            'biroList' => Biro::orderBy('urutan')->get(),
        ]);
    }

    public function store(KegiatanRequest $request): RedirectResponse
    {
        $data = collect($request->validated())
            ->except(['thumbnail', 'foto', 'caption', 'hapus_foto'])
            ->toArray();
        $data = ContentScope::payload($request, $data);
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->image->store($request->file('thumbnail'), 'kegiatan', 1600);
        }

        $uploads = isset($data['thumbnail']) ? [$data['thumbnail']] : [];
        try {
            $kegiatan = DB::transaction(function () use ($data, $request, &$uploads) {
                $kegiatan = Kegiatan::create($data);
                $this->syncFoto($request, $kegiatan, $uploads);
                ActivityLog::record('kegiatan', 'pembuatan', $kegiatan->id, 'Kegiatan dibuat.');

                return $kegiatan;
            });
        } catch (\Throwable $exception) {
            foreach ($uploads as $path) {
                $this->image->delete($path);
            }
            throw $exception;
        }

        return redirect()->route('admin.kegiatan.index')
            ->with('success', 'Kegiatan "'.$kegiatan->judul.'" berhasil dibuat.');
    }

    public function edit(Kegiatan $kegiatan): View
    {
        Gate::authorize('update', $kegiatan);

        return view('admin.kegiatan.edit', [
            'kegiatan' => $kegiatan->load('foto'),
            'biroList' => Biro::orderBy('urutan')->get(),
        ]);
    }

    public function preview(Kegiatan $kegiatan): View
    {
        Gate::authorize('view', $kegiatan);

        return view('admin.content-preview', ['content' => $kegiatan]);
    }

    public function update(KegiatanRequest $request, Kegiatan $kegiatan): RedirectResponse
    {
        $data = collect($request->validated())
            ->except(['thumbnail', 'foto', 'caption', 'hapus_foto'])
            ->toArray();
        $data = ContentScope::payload($request, $data, $kegiatan);

        $oldThumbnail = $kegiatan->thumbnail;
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->image->store($request->file('thumbnail'), 'kegiatan', 1600);
        }

        $uploads = isset($data['thumbnail']) ? [$data['thumbnail']] : [];
        $removed = [];
        try {
            DB::transaction(function () use ($kegiatan, $data, $request, &$uploads, &$removed) {
                // Serialize gallery changes on the parent row before rechecking capacity.
                Kegiatan::whereKey($kegiatan->id)->lockForUpdate()->firstOrFail();
                app(GalleryLimits::class)->validate($kegiatan, count($request->file('foto', [])), $request->input('hapus_foto', []));
                $kegiatan->update($data);
                foreach ($kegiatan->foto()->whereIn('id', $request->input('hapus_foto', []))->get() as $foto) {
                    $removed[] = $foto->path;
                    ActivityLog::record('kegiatan_foto', 'penghapusan', $foto->id, 'Foto galeri dihapus permanen oleh super_admin.');
                    $foto->delete();
                }
                $this->syncFoto($request, $kegiatan, $uploads);
                ActivityLog::record('kegiatan', 'perubahan', $kegiatan->id, 'Kegiatan diperbarui; status '.$kegiatan->status.'.');
            });
        } catch (\Throwable $exception) {
            foreach ($uploads as $path) {
                $this->image->delete($path);
            }
            throw $exception;
        }
        if (isset($data['thumbnail']) && $oldThumbnail) {
            $removed[] = $oldThumbnail;
        }
        foreach ($removed as $path) {
            $this->image->delete($path);
        }

        return redirect()->route('admin.kegiatan.index')
            ->with('success', 'Kegiatan "'.$kegiatan->judul.'" berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan): RedirectResponse
    {
        Gate::authorize('delete', $kegiatan);
        DB::transaction(function () use ($kegiatan) {
            $kegiatan->delete();
            ActivityLog::record('kegiatan', 'penghapusan', $kegiatan->id, 'Kegiatan di-soft-delete; media dipertahankan.');
        });

        return redirect()->route('admin.kegiatan.index')
            ->with('success', 'Kegiatan berhasil dihapus.');
    }

    private function syncFoto(KegiatanRequest $request, Kegiatan $kegiatan, array &$uploads): void
    {
        if (! $request->hasFile('foto')) {
            return;
        }

        $start = (int) $kegiatan->foto()->max('urutan');
        foreach ($request->file('foto') as $i => $file) {
            $path = $this->image->store($file, 'kegiatan/foto', 1600);
            $uploads[] = $path;
            $kegiatan->foto()->create([
                'path' => $path,
                'caption' => $request->input('caption.'.$i),
                'urutan' => $start + $i + 1,
            ]);
        }
    }
}
