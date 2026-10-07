<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\AnggotaController;
use App\Http\Controllers\Admin\BiroController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KaryaController;
use App\Http\Controllers\Admin\KegiatanController;
use App\Http\Controllers\Admin\KepengurusanController;
use App\Http\Controllers\Admin\PembinaController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TrashController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnggotaPhotoController;
use App\Http\Controllers\PembinaPhotoController;
use App\Http\Controllers\PublicController;
use App\Livewire\KaryaIndex;
use App\Livewire\KegiatanIndex;
use App\Livewire\KepengurusanPage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/tentang', [PublicController::class, 'tentang'])->name('tentang');

Route::get('/biro', [PublicController::class, 'biroIndex'])->name('biro.index');
Route::get('/biro/{biro}', [PublicController::class, 'biroShow'])->name('biro.show');

// Interactive pages (Livewire full-page components)
Route::get('/kepengurusan', KepengurusanPage::class)->name('kepengurusan');
Route::get('/kepengurusan/{periodeSlug}', KepengurusanPage::class)->name('kepengurusan.arsip');
Route::get('/media/anggota/{id}', AnggotaPhotoController::class)->name('media.anggota');
Route::get('/media/pembina/{pembina}', PembinaPhotoController::class)->name('media.pembina');
Route::get('/kegiatan', KegiatanIndex::class)->name('kegiatan.index');
Route::get('/karya', KaryaIndex::class)->name('karya.index');

Route::get('/kegiatan/{kegiatan}', [PublicController::class, 'kegiatanShow'])->name('kegiatan.show');
Route::get('/karya/{karya}', [PublicController::class, 'karyaShow'])->name('karya.show');

Route::get('/kontak', [PublicController::class, 'kontak'])->name('kontak');
Route::post('/kontak', [PublicController::class, 'kontakSubmit'])->name('kontak.submit');

/*
|--------------------------------------------------------------------------
| Admin routes (auth + role gated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin,admin,admin_biro'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('riwayat', ActivityController::class)->middleware('role:super_admin,admin')->name('riwayat.index');

        Route::resource('kegiatan', KegiatanController::class)->except(['show']);
        Route::resource('karya', KaryaController::class)->except(['show']);
        Route::get('karya/{karya}/preview', [KaryaController::class, 'preview'])->name('karya.preview');
        Route::get('kegiatan/{kegiatan}/preview', [KegiatanController::class, 'preview'])->name('kegiatan.preview');
        Route::get('anggota', [AnggotaController::class, 'index'])->name('anggota.index');
        Route::get('biro/{biro}/deskripsi', [BiroController::class, 'description'])->name('biro.description.edit');
        Route::put('biro/{biro}/deskripsi', [BiroController::class, 'updateDescription'])->name('biro.description.update');
        Route::middleware('role:super_admin,admin')->group(function () {
            Route::get('sampah', [TrashController::class, 'index'])->name('trash.index');
            Route::post('sampah/{type}/{id}/pulihkan', [TrashController::class, 'restore'])->name('trash.restore');
            Route::delete('sampah/{type}/{id}', [TrashController::class, 'purge'])->middleware('role:super_admin')->name('trash.purge');
            Route::resource('anggota', AnggotaController::class)->except(['show', 'index'])->parameters(['anggota' => 'anggota']);
            Route::resource('biro', BiroController::class)->except(['show'])->parameters(['biro' => 'biro']);
            Route::resource('periode', PeriodeController::class)->except(['show'])->parameters(['periode' => 'periode']);
            Route::post('periode/{periode}/pembina', [PembinaController::class, 'store'])->name('pembina.store');
            Route::get('periode/{periode}/pengurus', [KepengurusanController::class, 'periodIndex'])->name('periode.pengurus');
            Route::post('pembina', [PembinaController::class, 'createProfile'])->name('pembina.create-profile');
            Route::put('pembina/{pembina}', [PembinaController::class, 'updateProfile'])->name('pembina.update-profile');
            Route::put('periode/{periode}/pembina/{pembina}', [PembinaController::class, 'update'])->name('pembina.update');
            Route::delete('periode/{periode}/pembina/{pembina}', [PembinaController::class, 'destroy'])->name('pembina.destroy');
            Route::delete('pembina/{pembina}', [PembinaController::class, 'deleteProfile'])->name('pembina.delete-profile');
            Route::resource('kepengurusan', KepengurusanController::class)->except(['show'])->parameters(['kepengurusan' => 'kepengurusan']);

            Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
            Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        });

        // Super admin only
        Route::middleware('role:super_admin')->group(function () {
            Route::resource('users', UserController::class)->except(['show']);
        });
    });

/*
|--------------------------------------------------------------------------
| Auth routes
|--------------------------------------------------------------------------
*/
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}
