<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BiroRequest;
use App\Models\Biro;
use App\Models\Karya;
use App\Models\User;
use App\Services\ActivityLog;
use App\Services\ImageService;
use App\Services\MediaCleanup;
use App\Services\PublicMedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BiroController extends Controller
{
    public function __construct(private readonly ImageService $image) {}

    public function index(): View
    {
        return view('admin.biro.index', [
            'biro' => Biro::withCount(['kepengurusan', 'kegiatan'])->orderBy('urutan')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.biro.create');
    }

    public function description(Biro $biro): View
    {
        Gate::authorize('updateDescription', $biro);

        return view('admin.biro.description', ['biro' => $biro]);
    }

    public function updateDescription(Request $request, Biro $biro): RedirectResponse
    {
        Gate::authorize('updateDescription', $biro);
        $data = $request->validate(['deskripsi' => ['nullable', 'string', 'max:10000']]);
        DB::transaction(function () use ($biro, $data) {
            $biro->update($data);
            ActivityLog::record('biro', 'perubahan', $biro->id, 'Deskripsi biro diperbarui.');
        });

        return back()->with('success', 'Deskripsi biro diperbarui.');
    }

    public function store(BiroRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except('logo')->toArray();
        $data['urutan'] = $data['urutan'] ?? (Biro::max('urutan') + 1);
        $data['is_aktif'] = $request->boolean('is_aktif', true);

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->image->store($request->file('logo'), 'biro', 400);
        }

        try {
            $biro = DB::transaction(function () use ($data) {
                $biro = Biro::create($data);
                ActivityLog::record('biro', 'pembuatan', $biro->id, 'Biro dibuat.');

                return $biro;
            });
        } catch (\Throwable $exception) {
            $this->image->delete($data['logo'] ?? null);
            throw $exception;
        }

        return redirect()->route('admin.biro.index')
            ->with('success', 'Biro "'.$biro->nama.'" berhasil dibuat.');
    }

    public function edit(Biro $biro): View
    {
        return view('admin.biro.edit', ['biro' => $biro]);
    }

    public function update(BiroRequest $request, Biro $biro): RedirectResponse
    {
        $data = collect($request->validated())->except('logo')->toArray();
        $oldLogo = $biro->logo;

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->image->store($request->file('logo'), 'biro', 400);
        }

        try {
            DB::transaction(function () use ($biro, $data, $oldLogo) {
                $biro->update($data);
                if (isset($data['logo'])) {
                    MediaCleanup::enqueue(PublicMedia::disk(), $oldLogo);
                }
                ActivityLog::record('biro', 'perubahan', $biro->id, 'Biro diperbarui; status '.($biro->is_aktif ? 'aktif' : 'nonaktif').'.');
            });
        } catch (\Throwable $exception) {
            $this->image->delete($data['logo'] ?? null);
            throw $exception;
        }
        MediaCleanup::run();

        return redirect()->route('admin.biro.index')
            ->with('success', 'Biro "'.$biro->nama.'" berhasil diperbarui.');
    }

    public function destroy(Biro $biro): RedirectResponse
    {
        if ($biro->isBph()) {
            return redirect()->route('admin.biro.index')
                ->with('error', 'Badan Pengurus Harian tidak bisa dihapus.');
        }

        if ($biro->kepengurusan()->exists() || $biro->kegiatan()->withTrashed()->exists()
            || Karya::withTrashed()->where('biro_id', $biro->id)->exists()
            || User::where('biro_id', $biro->id)->exists()) {
            return redirect()->route('admin.biro.index')
                ->with('error', 'Biro tidak bisa dihapus karena masih terhubung dengan pengurus atau kegiatan.');
        }

        DB::transaction(function () use ($biro) {
            MediaCleanup::enqueue(PublicMedia::disk(), $biro->logo);
            $biro->delete();
            ActivityLog::record('biro', 'penghapusan', $biro->id, 'Biro tanpa relasi dihapus.');
        });
        MediaCleanup::run();

        return redirect()->route('admin.biro.index')
            ->with('success', 'Biro berhasil dihapus.');
    }
}
