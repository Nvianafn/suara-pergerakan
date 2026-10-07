<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Pembina;
use App\Models\Periode;
use App\Services\ActivityLog;
use App\Services\MediaCleanup;
use App\Services\PrivateImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PembinaController extends Controller
{
    public function create(Request $request)
    {
        return view('admin.kepengurusan.create-pembina', [
            'periodeList' => Periode::orderByDesc('id')->get(),
            'selectedId' => $request->query('periode'),
            'anggotaList' => Anggota::orderBy('nama_lengkap')->get(),
        ]);
    }

    private function profileRules(?Pembina $pembina = null): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'anggota_id' => ['nullable', Rule::exists('anggota', 'id')->whereNull('deleted_at'), Rule::unique('pembina', 'anggota_id')->ignore($pembina?->id)],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'setuju_publikasi' => ['nullable', 'boolean'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hapus_foto' => ['nullable', 'boolean'],
        ];
    }

    public function edit(Request $request, Pembina $pembina)
    {
        $periode = $request->filled('periode') ? Periode::findOrFail($request->query('periode')) : null;
        $placement = $periode ? $periode->pembina()->where('pembina.id', $pembina->id)->first() : null;
        abort_if($periode && ! $placement, 404);

        return view('admin.kepengurusan.edit-pembina', [
            'profil' => $pembina,
            'periode' => $periode,
            'urutan' => $placement?->pivot->urutan,
            'anggotaList' => Anggota::orderBy('nama_lengkap')->get(),
        ]);
    }

    public function createProfile(Request $request)
    {
        $data = $request->validate($this->profileRules() + [
            'periode_id' => ['nullable', 'exists:periode,id'],
            'urutan' => ['nullable', 'integer', 'between:0,255'],
        ]);
        $newPhoto = null;
        try {
            DB::transaction(function () use ($data, $request, &$newPhoto) {
                $pembina = Pembina::create(collect($data)->except(['foto', 'hapus_foto', 'setuju_publikasi', 'periode_id', 'urutan'])->all() + ['setuju_publikasi' => $request->boolean('setuju_publikasi')]);
                if ($request->hasFile('foto')) {
                    $newPhoto = app(PrivateImageService::class)->store($request->file('foto'), 'pembina/'.$pembina->id);
                    $pembina->foto = $newPhoto;
                    $pembina->save();
                }
                ActivityLog::pembina('pembuatan', $pembina->id, 'Profil Pembina dibuat.');
                if (! empty($data['periode_id'])) {
                    $periode = Periode::findOrFail($data['periode_id']);
                    $periode->pembina()->attach($pembina->id, ['urutan' => $data['urutan'] ?? 0]);
                    ActivityLog::pembina('penempatan', $pembina->id, 'Penempatan Pembina pada periode '.$periode->id);
                }
            });
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('r2_private')->delete($newPhoto);
            }
            throw $exception;
        }

        return redirect()->route('admin.kepengurusan.index', array_filter(['periode' => $data['periode_id'] ?? null]))
            ->with('success', empty($data['periode_id']) ? 'Profil Pembina dibuat. Bisa ditempatkan pada periode nanti.' : 'Pembina berhasil ditambahkan ke periode.');
    }

    public function updateProfile(Request $request, Pembina $pembina)
    {
        $data = $request->validate($this->profileRules($pembina));
        $this->saveProfile($request, $pembina, $data);

        return redirect()->route('admin.kepengurusan.index')->with('success', 'Profil Pembina diperbarui untuk seluruh periode terkait.');
    }

    public function store(Request $request, Periode $periode)
    {
        $data = $request->validate([
            'pembina_id' => ['nullable', 'exists:pembina,id'],
            'nama_lengkap' => ['required_without:pembina_id', 'nullable', 'string', 'max:150'],
            'anggota_id' => ['nullable', Rule::exists('anggota', 'id')->whereNull('deleted_at'), Rule::unique('pembina', 'anggota_id')],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'setuju_publikasi' => ['nullable', 'boolean'],
            'urutan' => ['required', 'integer', 'between:0,255'],
        ]);
        if (! empty($data['pembina_id']) && $periode->pembina()->where('pembina.id', $data['pembina_id'])->exists()) {
            return back()->withErrors(['pembina_id' => 'Pembina sudah ditempatkan pada periode ini.']);
        }
        DB::transaction(function () use ($data, $request, $periode) {
            $pembina = ! empty($data['pembina_id']) ? Pembina::findOrFail($data['pembina_id'])
                : Pembina::create(collect($data)->except(['pembina_id', 'urutan'])->all() + ['setuju_publikasi' => $request->boolean('setuju_publikasi')]);
            if (empty($data['pembina_id'])) {
                ActivityLog::pembina('pembuatan', $pembina->id, 'Profil Pembina dibuat.');
            }
            $periode->pembina()->attach($pembina->id, ['urutan' => $data['urutan']]);
            ActivityLog::pembina('penempatan', $pembina->id, 'Penempatan Pembina pada periode '.$periode->id);
        });

        return back()->with('success', 'Pembina berhasil ditempatkan.');
    }

    public function update(Request $request, Periode $periode, Pembina $pembina)
    {
        abort_unless($periode->pembina()->where('pembina.id', $pembina->id)->exists(), 404);
        $data = $request->validate($this->profileRules($pembina) + [
            'urutan' => ['required', 'integer', 'between:0,255'],
        ]);
        $this->saveProfile($request, $pembina, $data, $periode);

        return redirect()->route('admin.kepengurusan.index', ['periode' => $periode->id])->with('success', 'Profil dan urutan Pembina diperbarui.');
    }

    private function saveProfile(Request $request, Pembina $pembina, array $data, ?Periode $periode = null): void
    {
        $oldPhoto = $pembina->foto;
        $newPhoto = $request->hasFile('foto') ? app(PrivateImageService::class)->store($request->file('foto'), 'pembina/'.$pembina->id) : null;
        try {
            DB::transaction(function () use ($pembina, $periode, $data, $request, $newPhoto, $oldPhoto) {
                $pembina->fill(collect($data)->except(['urutan', 'setuju_publikasi', 'foto', 'hapus_foto'])->all() + ['setuju_publikasi' => $request->boolean('setuju_publikasi')]);
                if ($newPhoto || $request->boolean('hapus_foto')) {
                    $pembina->foto = $newPhoto;
                }
                $pembina->save();
                if ($oldPhoto && ($newPhoto || $request->boolean('hapus_foto'))) {
                    MediaCleanup::enqueue('r2_private', $oldPhoto);
                }
                if ($periode) {
                    $periode->pembina()->updateExistingPivot($pembina->id, ['urutan' => $data['urutan']]);
                }
                ActivityLog::pembina('perubahan', $pembina->id, 'Profil Pembina diperbarui; izin publikasi '.($pembina->setuju_publikasi ? 'aktif' : 'nonaktif').'.');
            });
        } catch (\Throwable $exception) {
            if ($newPhoto) {
                Storage::disk('r2_private')->delete($newPhoto);
            }
            throw $exception;
        }
        MediaCleanup::run();

    }

    public function destroy(Periode $periode, Pembina $pembina)
    {
        abort_unless($periode->pembina()->where('pembina.id', $pembina->id)->exists(), 404);
        DB::transaction(function () use ($periode, $pembina) {
            $periode->pembina()->detach($pembina->id);
            ActivityLog::pembina('pelepasan', $pembina->id, 'Pembina dilepas dari periode '.$periode->id);
        });

        return back()->with('success', 'Pembina dilepas dari periode ini.');
    }

    public function deleteProfile(Pembina $pembina)
    {
        if ($pembina->periode()->exists()) {
            return back()->withErrors(['pembina' => 'Profil digunakan pada '.$pembina->periode()->count().' periode dan tidak dapat dihapus.']);
        }
        $photo = $pembina->foto;
        DB::transaction(function () use ($pembina, $photo) {
            MediaCleanup::enqueue('r2_private', $photo);
            ActivityLog::pembina('penghapusan', $pembina->id, 'Profil Pembina yang belum digunakan dihapus.');
            $pembina->delete();
        });
        MediaCleanup::run();

        return back()->with('success', 'Profil Pembina dihapus.');
    }
}
