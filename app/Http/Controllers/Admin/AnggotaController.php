<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnggotaRequest;
use App\Models\Anggota;
use App\Models\Pembina;
use App\Models\User;
use App\Services\ActivityLog;
use App\Services\MediaCleanup;
use App\Services\PrivateImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnggotaController extends Controller
{
    public function __construct(private readonly PrivateImageService $image) {}

    public function index(Request $request): View
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:150'], 'angkatan' => ['nullable', 'integer', 'between:1900,2200'], 'status' => ['nullable', 'in:aktif,alumni,non-aktif'], 'prodi' => ['nullable', 'string', 'max:100']]);
        $query = Anggota::query()
            ->when($filters['q'] ?? null, fn ($q, $value) => $q->where('nama_lengkap', 'like', '%'.$value.'%'))
            ->when($filters['angkatan'] ?? null, fn ($q, $value) => $q->where('angkatan', $value))
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when($filters['prodi'] ?? null, fn ($q, $value) => $q->where('prodi', $value));
        if (auth()->user()->role === 'admin_biro') {
            return view('admin.anggota.limited', [
                'anggota' => $query->select(['id', 'nama_lengkap', 'angkatan', 'status', 'setuju_publikasi'])->orderBy('nama_lengkap')->paginate(15)->withQueryString(),
            ]);
        }

        return view('admin.anggota.index', [
            'anggota' => $query->orderBy('nama_lengkap')->paginate(15)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.anggota.create');
    }

    public function store(AnggotaRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except('foto')->toArray();
        $data['setuju_publikasi'] = $request->boolean('setuju_publikasi');

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->image->store($request->file('foto'), 'anggota');
        }

        try {
            $anggota = DB::transaction(function () use ($data) {
                $anggota = Anggota::create($data);
                ActivityLog::record('anggota', 'pembuatan', $anggota->id, 'Anggota dibuat; izin publikasi '.($anggota->setuju_publikasi ? 'aktif' : 'nonaktif').'.');

                return $anggota;
            });
        } catch (\Throwable $exception) {
            $this->image->delete($data['foto'] ?? null);
            throw $exception;
        }

        return redirect()->route('admin.anggota.index')
            ->with('success', 'Anggota "'.$anggota->nama_lengkap.'" berhasil ditambahkan.');
    }

    public function edit(Anggota $anggota): View
    {
        return view('admin.anggota.edit', ['anggota' => $anggota]);
    }

    public function update(AnggotaRequest $request, Anggota $anggota): RedirectResponse
    {
        $data = collect($request->validated())->except('foto')->toArray();
        $data['setuju_publikasi'] = $request->boolean('setuju_publikasi');
        $oldPhoto = $anggota->foto;

        if ($request->hasFile('foto')) {
            $data['foto'] = $this->image->store($request->file('foto'), 'anggota');
        }

        try {
            DB::transaction(function () use ($anggota, $data, $oldPhoto) {
                $anggota->update($data);
                if (isset($data['foto'])) {
                    MediaCleanup::enqueue('r2_private', $oldPhoto);
                }
                ActivityLog::record('anggota', 'perubahan', $anggota->id, 'Data anggota diperbarui; izin publikasi '.($anggota->setuju_publikasi ? 'aktif' : 'nonaktif').'.');
            });
        } catch (\Throwable $exception) {
            $this->image->delete($data['foto'] ?? null);
            throw $exception;
        }
        MediaCleanup::run();

        return redirect()->route('admin.anggota.index')
            ->with('success', 'Data "'.$anggota->nama_lengkap.'" berhasil diperbarui.');
    }

    public function destroy(Anggota $anggota): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'super_admin', 403);
        if ($anggota->kepengurusan()->exists() || $anggota->karya()->withTrashed()->exists()
            || Pembina::where('anggota_id', $anggota->id)->exists()
            || User::where('anggota_id', $anggota->id)->exists()) {
            return back()->withErrors(['anggota' => 'Anggota memiliki relasi; gunakan status alumni/non-aktif.']);
        }
        DB::transaction(function () use ($anggota) {
            $anggota->delete();
            ActivityLog::record('anggota', 'penghapusan', $anggota->id, 'Anggota dihapus secara soft delete.');
        });

        return redirect()->route('admin.anggota.index')
            ->with('success', 'Anggota berhasil dihapus.');
    }
}
