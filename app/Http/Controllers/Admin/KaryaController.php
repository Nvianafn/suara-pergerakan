<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\KaryaRequest;
use App\Models\Anggota;
use App\Models\Biro;
use App\Models\Karya;
use App\Models\Periode;
use App\Services\ActivityLog;
use App\Services\ContentScope;
use App\Services\FeaturedWorks;
use App\Services\ImageService;
use App\Services\MediaCleanup;
use App\Services\PublicMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class KaryaController extends Controller
{
    public function __construct(private readonly ImageService $image) {}

    public function index(Request $request): View
    {
        $filters = $request->validate(['tipe' => ['nullable', 'in:artikel,esai,puisi,berita'], 'status' => ['nullable', 'in:draft,published'], 'penulis' => ['nullable', 'string', 'max:150'], 'biro_id' => ['nullable', 'integer', 'exists:biro,id']]);

        return view('admin.karya.index', [
            'biroList' => Biro::orderBy('nama')->get(),
            'karya' => Karya::with('anggota')->when(auth()->user()->role === 'admin_biro', fn ($q) => $q->where('biro_id', auth()->user()->biro_id)->where('created_by', auth()->id())->where('status', 'draft'))
                ->when($filters['tipe'] ?? null, fn ($q, $v) => $q->where('tipe', $v))
                ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
                ->when($filters['biro_id'] ?? null, fn ($q, $v) => $q->where('biro_id', $v))
                ->when($filters['penulis'] ?? null, fn ($q, $v) => $q->where(fn ($q) => $q->where('penulis_nama', 'like', '%'.$v.'%')->orWhere('penulis_tipe', $v)->orWhereHas('anggota', fn ($q) => $q->where('nama_lengkap', 'like', '%'.$v.'%'))))
                ->latest('created_at')->paginate(12)->withQueryString(),
        ]);
    }

    public function bulk(Request $request): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'super_admin'], true), 403);
        $data = $request->validate(['action' => ['required', 'in:publish,draft,delete'], 'ids' => ['required', 'array', 'min:1', 'max:100'], 'ids.*' => ['required', 'integer', 'distinct', 'exists:karya,id']]);
        DB::transaction(function () use ($data) {
            app(FeaturedWorks::class)->validateSelection([]);
            foreach (Karya::whereIn('id', $data['ids'])->orderBy('id')->lockForUpdate()->get() as $work) {
                Gate::authorize($data['action'] === 'delete' ? 'delete' : 'update', $work);
                if ($data['action'] === 'delete') {
                    $work->delete();
                } else {
                    $status = $data['action'] === 'publish' ? 'published' : 'draft';
                    app(FeaturedWorks::class)->validateSelection(['status' => $status, 'is_featured' => $status === 'published' && $work->is_featured], $work);
                    $work->update(['status' => $status, 'is_featured' => $status === 'published' && $work->is_featured]);
                }
                ActivityLog::record('karya', 'aksi_massal', $work->id, 'Aksi massal: '.$data['action'].'. Media dipertahankan.');
            }
        });

        return back()->with('success', 'Aksi massal karya berhasil.');
    }

    public function create(): View|RedirectResponse
    {
        Gate::authorize('create', Karya::class);
        if (! Periode::exists()) {
            return redirect()->route('admin.dashboard')->withErrors(['periode_id' => 'Buat periode terlebih dahulu sebelum menambahkan karya.']);
        }

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

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $this->image->store($request->file('thumbnail'), 'karya', 1400);
        }

        try {
            DB::transaction(function () use ($karya, $data) {
                app(FeaturedWorks::class)->validateSelection($data, $karya);
                $locked = Karya::whereKey($karya->id)->lockForUpdate()->firstOrFail();
                if (isset($data['thumbnail'])) {
                    MediaCleanup::enqueue(PublicMedia::disk(), $locked->thumbnail);
                }
                $karya->update($data);
                ActivityLog::record('karya', 'perubahan', $karya->id, 'Karya diperbarui; status '.$karya->status.'.');
            });
        } catch (\Throwable $exception) {
            $this->image->delete($data['thumbnail'] ?? null);
            throw $exception;
        }
        MediaCleanup::run();

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
