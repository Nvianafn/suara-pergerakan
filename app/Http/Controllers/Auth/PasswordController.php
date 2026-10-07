<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('auth.password', ['mode' => 'change']);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', PasswordRule::min(8), 'different:current_password'],
        ]);
        DB::transaction(function () use ($request, $data) {
            $request->user()->update(['password' => $data['password'], 'must_change_password' => false, 'remember_token' => Str::random(60)]);
            DB::table('sessions')->where('user_id', $request->user()->id)->where('id', '!=', $request->session()->getId())->delete();
            ActivityLog::record('user', 'perubahan', $request->user()->id, 'Password akun diganti oleh pemilik.');
        });
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')->with('success', 'Password berhasil diganti.');
    }

    public function forgot()
    {
        return view('auth.password', ['mode' => 'forgot']);
    }

    public function email(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);
        if (User::where('email', $data['email'])->where('is_active', true)->exists()) {
            Password::sendResetLink($data);
        }

        return back()->with('status', 'Jika akun aktif ditemukan, tautan reset password akan dikirim ke email tersebut.');
    }

    public function reset(Request $request, string $token)
    {
        return view('auth.password', ['mode' => 'reset', 'token' => $token, 'email' => $request->query('email')]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['token' => ['required'], 'email' => ['required', 'email'], 'password' => ['required', 'confirmed', PasswordRule::min(8)]]);
        $status = Password::reset($data, function (User $user, string $password) {
            if (! $user->is_active) {
                throw ValidationException::withMessages(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa.']);
            }
            DB::transaction(function () use ($user, $password) {
                $user->update(['password' => $password, 'must_change_password' => false, 'remember_token' => Str::random(60)]);
                DB::table('sessions')->where('user_id', $user->id)->delete();
                ActivityLog::record('user', 'perubahan', $user->id, 'Password akun direset melalui tautan email.');
            });
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => 'Tautan reset tidak valid atau sudah kedaluwarsa.']);
        }

        return redirect()->route('login')->with('status', 'Password berhasil direset. Silakan masuk dengan password baru.');
    }
}
