<?php

use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Requests\PeriodeRequest;
use App\Models\Periode;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$period = Periode::findOrFail($argv[1]);
$user = User::where('role', 'super_admin')->firstOrFail();
Auth::login($user);
$data = ['nama' => $period->nama, 'tahun_mulai' => $period->tahun_mulai, 'tahun_selesai' => $period->tahun_selesai, 'is_aktif' => true];
$request = PeriodeRequest::create('/', 'PUT', $data);
$request->setUserResolver(fn () => $user);
$request->setValidator(Validator::make($data, $request->rules()));
file_put_contents($argv[2], 'ready');
while (! file_exists($argv[3])) {
    usleep(10000);
}
$app->make(PeriodeController::class)->update($request, $period);
