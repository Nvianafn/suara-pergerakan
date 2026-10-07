<?php

namespace App\Http\Middleware;

use App\Services\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Guard admin routes by role. Usage: ->middleware('role:super_admin,admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->is_active || (! empty($roles) && ! in_array($user->role, $roles, true))) {
            ActivityLog::record('akses', 'penolakan', $user->id, 'Akses ditolak: '.($request->route()?->getName() ?? 'rute tanpa nama'));
            abort(403, 'Kamu tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}
