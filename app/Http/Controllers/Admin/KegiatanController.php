<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KegiatanRequest;
use App\Models\Biro;
use App\Models\Kegiatan;
use App\Models\Periode;
use App\Services\ActivityLog;
use App\Services\ContentScope;
use App\Services\GalleryLimits;
use App\Services\ImageService;
use App\Services\MediaCleanup;
use App\Services\PublicMedia;
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

    public function create(): View|RedirectResponse
    {
        if (! Periode::exists()) {
            return redirect()->route('admin.dashboard')->withErrors(['periode_id' => 'Buat periode terlebih dahulu sebelum menambahkan kegiatan.']);
        }
        Gate::authorize('create', Kegiatan::class);

        return view('admin.kegiatan.create', [
            'biroList' => Biro::orderBy('urutan')->get(),
        ]);
    }

    public function store(KegiatanRequest $request): RedirectResponse
    {
        $data = collect($request->validated())
            ->except(['thumbnail', 'foto', 'caption', 'existing_caption', 'hapus_foto'])
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

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->image->store($request->file('thumbnail'), 'kegiatan', 1600);
        }

        $uploads = isset($data['thumbnail']) ? [$data['thumbnail']] : [];
        try {
            DB::transaction(function () use ($kegiatan, $data, $request, &$uploads) {
                // Serialize gallery changes on the parent row before rechecking capacity.
                $locked = Kegiatan::whereKey($kegiatan->id)->lockForUpdate()->firstOrFail();
                if (isset($data['thumbnail'])) {
                    MediaCleanup::enqueue(PublicMedia::disk(), $locked->thumbnail);
                }
                app(GalleryLimits::class)->validate($kegiatan, count($request->file('foto', [])), $request->input('hapus_foto', []));
                $kegiatan->update($data);
                foreach ($request->input('existing_caption', []) as $id => $caption) {
                    $kegiatan->foto()->whereKey($id)->update(['caption' => $caption]);
                }
                foreach ($kegiatan->foto()->whereIn('id', $request->input('hapus_foto', []))->get() as $foto) {
                    MediaCleanup::enqueue(PublicMedia::disk(), $foto->path);
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
        MediaCleanup::run();

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
