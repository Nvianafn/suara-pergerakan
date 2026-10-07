<?php

namespace App\Providers;

use App\Models\Biro;
use App\Models\Karya;
use App\Models\Kegiatan;
use App\Policies\BiroPolicy;
use App\Policies\ContentPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Carbon::setLocale(config('app.locale', 'id'));
        Paginator::useTailwind();
        Gate::policy(Biro::class, BiroPolicy::class);
        Gate::policy(Karya::class, ContentPolicy::class);
        Gate::policy(Kegiatan::class, ContentPolicy::class);

        if (! app()->environment('local')) {
            URL::forceScheme('https');
        }
    }
}
