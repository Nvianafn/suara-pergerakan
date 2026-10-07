<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KaryaRequest;
use App\Models\Anggota;
use App\Models\Karya;
use App\Services\ActivityLog;
use App\Services\ContentScope;
use App\Services\FeaturedWorks;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class KaryaController extends Controller
{
    public function __construct(private readonly ImageService $image) {}

    public function index(): View
    {
        return view('admin.karya.index', [
            'karya' => Karya::with('anggota')->when(auth()->user()->role === 'admin_biro', fn ($q) => $q->where('biro_id', auth()->user()->biro_id)->where('created_by', auth()->id())->where('status', 'draft'))->latest('created_at')->paginate(12),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Karya::class);

        return view('admin.karya.create', [
            'anggotaList' => Anggota::orderBy('nama_lengkap')->get(),
        ]);
    }

    public function store(KaryaRequest $request): RedirectResponse
    {
        $data = $this->payload($request);
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->image->store($request->file('thumbnail'), 'karya', 1400);
        }

        try {
            $karya = DB::transaction(function () use ($data) {
                app(FeaturedWorks::class)->validateSelection($data);
                $karya = Karya::create($data);
                ActivityLog::record('karya', 'pembuatan', $karya->id, 'Karya dibuat.');

                return $karya;
            });
        } catch (\Throwable $exception) {
            $this->image->delete($data['thumbnail'] ?? null);
            throw $exception;
        }

        return redirect()->route('admin.karya.index')
            ->with('success', 'Karya "'.$karya->judul.'" berhasil dibuat.');
    }

    public function edit(Karya $karya): View
    {
        Gate::authorize('update', $karya);

        return view('admin.karya.edit', [
            'karya' => $karya,
            'anggotaList' => Anggota::orderBy('nama_lengkap')->get(),
        ]);
    }

    public function preview(Karya $karya): View
    {
        Gate::authorize('view', $karya);

        return view('admin.content-preview', ['content' => $karya]);
    }

    public function update(KaryaRequest $request, Karya $karya): RedirectResponse
    {
        $data = $this->payload($request, $karya);

        $oldThumbnail = $karya->thumbnail;
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->image->store($request->file('thumbnail'), 'karya', 1400);
        }

        try {
            DB::transaction(function () use ($karya, $data) {
                app(FeaturedWorks::class)->validateSelection($data, $karya);
                $karya->update($data);
                ActivityLog::record('karya', 'perubahan', $karya->id, 'Karya diperbarui; status '.$karya->status.'.');
            });
        } catch (\Throwable $exception) {
            $this->image->delete($data['thumbnail'] ?? null);
            throw $exception;
        }
        if (isset($data['thumbnail']) && $oldThumbnail) {
            $this->image->delete($oldThumbnail);
        }

        return redirect()->route('admin.karya.index')
            ->with('success', 'Karya "'.$karya->judul.'" berhasil diperbarui.');
    }

    public function destroy(Karya $karya): RedirectResponse
    {
        Gate::authorize('delete', $karya);
        DB::transaction(function () use ($karya) {
            $karya->delete();
            ActivityLog::record('karya', 'penghapusan', $karya->id, 'Karya di-soft-delete; media dipertahankan.');
        });

        return redirect()->route('admin.karya.index')
            ->with('success', 'Karya berhasil dihapus.');
    }

    /**
     * Build the persisted payload: normalize tags and resolve publish time.
     */
    private function payload(KaryaRequest $request, ?Karya $karya = null): array
    {
        $data = collect($request->validated())
            ->except(['thumbnail', 'tags'])
            ->toArray();

        $data['tags'] = collect(explode(',', (string) $request->input('tags')))
            ->map(fn ($t) => trim($t))
            ->filter()
            ->values()
            ->all();

        $data['anggota_id'] = $data['penulis_tipe'] === 'anggota' ? ($data['anggota_id'] ?? null) : null;
        $data['is_featured'] = $request->boolean('is_featured');
        $data['penulis_nama'] = $data['penulis_tipe'] === 'nama_bebas' ? ($data['penulis_nama'] ?? null) : null;

        return ContentScope::payload($request, $data, $karya);
    }
}
