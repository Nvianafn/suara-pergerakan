<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    public function __invoke()
    {
        return view('admin.riwayat.index', [
            'activities' => DB::table('riwayat_aktivitas')
                ->leftJoin('users', 'users.id', '=', 'riwayat_aktivitas.user_id')
                ->select('riwayat_aktivitas.*', 'users.name as user_name')
                ->orderByDesc('riwayat_aktivitas.id')->paginate(20),
        ]);
    }
}
