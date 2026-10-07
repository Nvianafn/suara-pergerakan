<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate(['user_id' => ['nullable', 'integer', 'exists:users,id'], 'subjek_tipe' => ['nullable', 'string', 'max:50'], 'from' => ['nullable', 'date_format:Y-m-d'], 'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from']]);

        return view('admin.riwayat.index', [
            'users' => User::orderBy('name')->get(['id', 'name']),
            'types' => DB::table('riwayat_aktivitas')->distinct()->orderBy('subjek_tipe')->pluck('subjek_tipe'),
            'activities' => DB::table('riwayat_aktivitas')
                ->leftJoin('users', 'users.id', '=', 'riwayat_aktivitas.user_id')
                ->select('riwayat_aktivitas.*', 'users.name as user_name')
                ->when($filters['user_id'] ?? null, fn ($q, $value) => $q->where('riwayat_aktivitas.user_id', $value))
                ->when($filters['subjek_tipe'] ?? null, fn ($q, $value) => $q->where('subjek_tipe', $value))
                ->when($filters['from'] ?? null, fn ($q, $value) => $q->where('riwayat_aktivitas.created_at', '>=', Carbon::parse($value, 'Asia/Jakarta')->utc()))
                ->when($filters['to'] ?? null, fn ($q, $value) => $q->where('riwayat_aktivitas.created_at', '<', Carbon::parse($value, 'Asia/Jakarta')->addDay()->utc()))
                ->orderByDesc('riwayat_aktivitas.id')->paginate(15)->withQueryString(),
        ]);
    }
}
