<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateInitialSuperAdmin extends Command
{
    protected $signature = 'app:buat-super-admin';

    protected $description = 'Membuat super_admin pertama tanpa kredensial bawaan.';

    public function handle(): int
    {
        if (User::where('role', 'super_admin')->exists()) {
            $this->error('Super admin sudah ada. Gunakan CMS untuk mengelola akun berikutnya.');

            return self::FAILURE;
        }
        $interactive = $this->input->isInteractive();
        $data = [
            'name' => $interactive ? $this->ask('Nama lengkap') : config('bootstrap_admin.name'),
            'email' => $interactive ? $this->ask('Email akun') : config('bootstrap_admin.email'),
            'password' => $interactive ? $this->secret('Password sementara (minimal 8 karakter)') : config('bootstrap_admin.password'),
        ];
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
        ]);
        if ($validator->fails()) {
            $this->error('Data bootstrap tidak valid. Isi nama, email unik, dan password minimal 8 karakter.');

            return self::FAILURE;
        }
        $created = DB::transaction(function () use ($data) {
            if (DB::getDriverName() === 'sqlsrv') {
                DB::statement("DECLARE @result int; EXEC @result = sp_getapplock @Resource = 'initial-super-admin', @LockMode = 'Exclusive', @LockOwner = 'Transaction', @LockTimeout = 10000; IF @result < 0 THROW 50001, 'Could not lock admin bootstrap', 1;");
            }
            if (User::where('role', 'super_admin')->lockForUpdate()->exists()) {
                return false;
            }
            User::create($data + ['role' => 'super_admin', 'is_active' => true, 'must_change_password' => true]);

            return true;
        });
        if (! $created) {
            $this->error('Super admin sudah dibuat oleh eksekusi lain.');

            return self::FAILURE;
        }
        $this->info('Super admin pertama berhasil dibuat. Wajib mengganti password saat login.');

        return self::SUCCESS;
    }
}
