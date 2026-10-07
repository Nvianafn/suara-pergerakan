<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Anggota;
use App\Models\Biro;
use App\Models\User;
use App\Services\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::with('anggota')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'anggotaList' => Anggota::orderBy('nama_lengkap')->get(),
            'biroList' => Biro::orderBy('nama')->get(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except('password')->toArray();
        $data['password'] = $request->input('password');
        $data['must_change_password'] = true;

        $user = DB::transaction(function () use ($data) {
            $user = User::create($data);
            ActivityLog::record('user', 'pembuatan', $user->id, 'Akun dibuat.');

            return $user;
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun "'.$user->name.'" berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'anggotaList' => Anggota::orderBy('nama_lengkap')->get(),
            'biroList' => Biro::orderBy('nama')->get(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = collect($request->validated())->except('password')->toArray();

        if ($request->filled('password')) {
            $data['password'] = $request->input('password');
            $data['must_change_password'] = true;
        }

        $protected = DB::transaction(function () use ($user, $data) {
            $admins = User::where('role', 'super_admin')->where('is_active', true)->lockForUpdate()->get();
            if ($admins->contains('id', $user->id) && $admins->count() === 1
                && ($data['role'] !== 'super_admin' || ! $data['is_active'])) {
                return true;
            }
            $user->update($data);
            if (isset($data['password'])) {
                $user->update(['remember_token' => Str::random(60)]);
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }
            ActivityLog::record('user', 'perubahan', $user->id, 'Akun diperbarui; role '.$user->role.'; status '.($user->is_active ? 'aktif' : 'nonaktif').'.');

            return false;
        });
        if ($protected) {
            return back()->withErrors(['role' => 'Minimal harus ada satu Super Admin aktif.']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun "'.$user->name.'" berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        if ($user->isSuperAdmin() && $user->is_active && User::where('role', 'super_admin')->where('is_active', true)->count() <= 1) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Minimal harus ada satu Super Admin.');
        }

        if (DB::table('riwayat_aktivitas')->where('user_id', $user->id)->exists()
            || DB::table('karya')->where('created_by', $user->id)->exists()
            || DB::table('kegiatan')->where('created_by', $user->id)->exists()) {
            return back()->withErrors(['user' => 'Akun direferensikan konten/riwayat; nonaktifkan akun sebagai gantinya.']);
        }
        DB::transaction(function () use ($user) {
            ActivityLog::record('user', 'penghapusan', $user->id, 'Akun tanpa referensi dihapus.');
            $user->delete();
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun berhasil dihapus.');
    }
}
