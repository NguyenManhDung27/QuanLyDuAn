<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NguoiDung;
use App\Models\DonHangBan;

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
app()->instance(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, new class {
    public function handle($request, $next) { return $next($request); }
});

$kd = NguoiDung::where('Ten_dang_nhap', 'kinhdoanh01')->first();

// Find the latest don
$don = DonHangBan::where('Ma_kho', 'HN')->orderByDesc('Thoi_diem_xac_nhan')->first();
echo "Don: " . ($don?->Ma_don_hang_ban ?? 'NONE') . " - " . ($don?->Trang_thai ?? '') . "\n";

$req = Request::create("/kinh-doanh/don-ban/{$don->Ma_don_hang_ban}", 'GET');
$app->instance('request', $req);
$session = $app->make('session')->driver(); $session->start();
$req->setLaravelSession($session);
Auth::login($kd); $req->setUserResolver(fn() => $kd);
$res = $kernel->handle($req);
echo "Status: " . $res->getStatusCode() . "\n";
echo substr($res->getContent(), 0, 1500) . "\n";

