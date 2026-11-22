<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Http\Kernel');
$kernel->bootstrap();

app()->instance(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, new class {
    public function handle($request, $next) { return $next($request); }
});

$nvk = App\Models\NguoiDung::where('Ten_dang_nhap', 'nvkho.hn')->first();

// Tạo đơn test
$don = App\Models\DonDatHang::create([
    'Ma_don_dat_hang' => 'DDH-DEBUG-1',
    'Ma_ncc' => App\Models\NhaCungCap::first()->Ma_ncc,
    'Ma_kho' => 'HN',
    'Ma_nguoi_lap' => App\Models\NguoiDung::where('Ten_dang_nhap', 'qlkho.hn')->first()->Ma_nguoi_dung,
    'Ngay_lap' => now()->toDateString(),
    'Trang_thai' => 'Đã gửi NCC',
    'Tong_gia_tri' => 1000000,
]);

$req = Illuminate\Http\Request::create('/nv-kho/nhap-kho/ncc/DDH-DEBUG-1', 'POST', [
    'action' => 'save',
    'items' => [
        [
            'ma_san_pham' => 'SP0001',
            'ma_lo' => 'LOT-DEBUG-1',
            'so_luong_nhap' => 10,
        ]
    ]
]);
$app->instance('request', $req);
$session = $app->make('session')->driver();
$session->start();
$req->setLaravelSession($session);
Illuminate\Support\Facades\Auth::login($nvk);
$req->setUserResolver(fn() => $nvk);

$res = $kernel->handle($req);
echo "Status: " . $res->getStatusCode() . "\n";
if ($session->get('errors')) {
    echo "Validation errors: " . json_encode($session->get('errors')->all()) . "\n";
}
if ($session->get('error')) {
    echo "Error flash: " . $session->get('error') . "\n";
}

$don->delete();
