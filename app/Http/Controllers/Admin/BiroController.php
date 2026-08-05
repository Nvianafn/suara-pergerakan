<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BiroRequest;
use App\Models\Biro;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
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

    public function store(BiroRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except('logo')->toArray();
        $data['urutan'] = $data['urutan'] ?? (Biro::max('urutan') + 1);

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->image->store($request->file('logo'), 'biro', 400);
        }

        $biro = Biro::create($data);

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

        if ($request->hasFile('logo')) {
            $this->image->delete($biro->logo);
            $data['logo'] = $this->image->store($request->file('logo'), 'biro', 400);
        }

        $biro->update($data);

        return redirect()->route('admin.biro.index')
            ->with('success', 'Biro "'.$biro->nama.'" berhasil diperbarui.');
    }

    public function destroy(Biro $biro): RedirectResponse
    {
        if ($biro->isBph()) {
            return redirect()->route('admin.biro.index')
                ->with('error', 'Badan Pengurus Harian tidak bisa dihapus.');
        }

        if ($biro->kepengurusan()->exists() || $biro->kegiatan()->exists()) {
            return redirect()->route('admin.biro.index')
                ->with('error', 'Biro tidak bisa dihapus karena masih terhubung dengan pengurus atau kegiatan.');
        }

        $this->image->delete($biro->logo);
        $biro->delete();

        return redirect()->route('admin.biro.index')
            ->with('success', 'Biro berhasil dihapus.');
    }
}
