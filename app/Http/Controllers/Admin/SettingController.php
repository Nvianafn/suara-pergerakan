<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ActivityLog;
use App\Services\ImageService;
use App\Services\MediaCleanup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Setting keys grouped by section for the form.
     */
    private const FIELDS = [
        'nama_rayon',
        'logo', 'favicon', 'hero_image', 'og_image',
        'tagline', 'tentang_deskripsi', 'tentang_sejarah', 'visi', 'misi', 'peta_url',
        'deskripsi_singkat',
        'email_kontak',
        'no_wa',
        'alamat',
        'sosmed_instagram', 'sosmed_facebook', 'sosmed_youtube', 'sosmed_tiktok', 'sosmed_x',
    ];

    public function edit(): View
    {
        $settings = [];
        foreach (self::FIELDS as $key) {
            $settings[$key] = Setting::get($key, '');
        }

        return view('admin.settings.edit', ['settings' => $settings]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_rayon' => ['required', 'string', 'max:150'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'favicon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'tentang_deskripsi' => ['nullable', 'string', 'max:50000'],
            'tentang_sejarah' => ['nullable', 'string', 'max:50000'],
            'visi' => ['nullable', 'string', 'max:2000'],
            'misi' => ['nullable', 'string', 'max:50000'],
            'peta_url' => ['nullable', 'url:https', 'max:500', 'regex:~^https://(?:www\.)?(?:google\.com/maps(?:[/?]|$)|maps\.google\.com(?:[/?]|$)|maps\.app\.goo\.gl/|goo\.gl/maps/)~i'],
            'deskripsi_singkat' => ['nullable', 'string', 'max:500'],
            'email_kontak' => ['nullable', 'email', 'max:150'],
            'no_wa' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'sosmed_instagram' => ['nullable', 'url:http,https', 'max:255'],
            'sosmed_facebook' => ['nullable', 'url:http,https', 'max:255'],
            'sosmed_youtube' => ['nullable', 'url:http,https', 'max:255'],
            'sosmed_tiktok' => ['nullable', 'url:http,https', 'max:255'],
            'sosmed_x' => ['nullable', 'url:http,https', 'max:255'],
        ]);

        $uploads = [];
        try {
            foreach (['logo' => 512, 'favicon' => 128, 'hero_image' => 1920, 'og_image' => 1200] as $key => $width) {
                unset($validated[$key]);
                if ($request->hasFile($key)) {
                    $uploads[$key] = app(ImageService::class)->store($request->file($key), 'settings', $width);
                    $validated[$key] = $uploads[$key];
                }
            }
            DB::transaction(function () use ($validated, $uploads) {
                if (DB::getDriverName() === 'sqlsrv') {
                    DB::statement("DECLARE @result int; EXEC @result = sp_getapplock @Resource = 'site-settings', @LockMode = 'Exclusive', @LockOwner = 'Transaction', @LockTimeout = 10000; IF @result < 0 THROW 50001, 'Could not lock site settings', 1;");
                }
                foreach ($uploads as $key => $path) {
                    $old = Setting::where('key', $key)->lockForUpdate()->value('value');
                    if ($old && $old !== $path) {
                        MediaCleanup::enqueue('public', $old);
                    }
                }
                foreach ($validated as $key => $value) {
                    Setting::put($key, $value ?? '');
                }
                ActivityLog::record('settings', 'perubahan', 0, 'Pengaturan konten situs diperbarui: '.implode(', ', array_keys($validated)));
            });
        } catch (\Throwable $exception) {
            foreach ($uploads as $path) {
                MediaCleanup::enqueue('public', $path);
            }
            MediaCleanup::run();
            throw $exception;
        }
        MediaCleanup::run();

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Pengaturan situs berhasil disimpan.');
    }
}
