<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\ChiTietPhieuXuat;
use App\Models\DonHangBan;
use App\Models\NguoiDung;
use App\Models\PhieuXuatKho;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

app()->instance(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, new class {
    public function handle($request, $next) { return $next($request); }
});

function makeRequest($app, $kernel, $method, $uri, $user, $data = []) {
    $req = Request::create($uri, $method, $data);
    $app->instance('request', $req);
    $session = $app->make('session')->driver();
    $session->start();
    $req->setLaravelSession($session);
    if ($user) {
        Auth::login($user);
        $req->setUserResolver(fn() => $user);
    }
    return $kernel->handle($req);
}

$nvk = NguoiDung::where('Ten_dang_nhap', 'nvkho.hn')->first();
$kd  = NguoiDung::where('Ten_dang_nhap', 'kinhdoanh01')->first();
$kh = \App\Models\KhachHang::first();

$maDonBanTest = 'DB-TEST-S6-' . rand(100, 999);
$db = DonHangBan::create([
    'Ma_don_hang_ban' => $maDonBanTest,
    'Ma_khach_hang' => $kh->Ma_khach_hang,
    'Ma_kho' => 'HN',
    'Ma_nguoi_lap' => $kd->Ma_nguoi_dung,
    'Thoi_diem_xac_nhan' => now(),
    'Ten_nguoi_nhan' => $kh->Ten_khach_hang,
    'Sdt_nguoi_nhan' => $kh->So_dien_thoai ?: '0901112233',
    'Dia_chi_giao_hang' => $kh->Dia_chi ?: 'Hà Nội',
    'Tong_tien_hang' => 250000 * 5,
    'Trang_thai' => 'Giao thất bại',
]);

$maPxTest = 'PX-TEST-S6-' . rand(100, 999);
$px = PhieuXuatKho::create([
    'Ma_phieu_xuat' => $maPxTest,
    'Ma_don_hang_ban' => $maDonBanTest,
    'Ngay_tao' => now()->toDateString(),
    'Ma_nguoi_xuat' => $nvk->Ma_nguoi_dung,
    'Thoi_diem_xuat_kho' => now(),
    'Trang_thai_giao_hang' => 'Giao hàng thất bại',
    'Trang_thai_hoan_hang' => 'Chờ xử lý',
    'Ly_do_giao_that_bai' => 'Không liên lạc được người nhận',
]);

$lo = \App\Models\Lo::first();
ChiTietPhieuXuat::create([
    'Ma_phieu_xuat' => $maPxTest,
    'Ma_lo_phan_bo' => $lo->Ma_lo,
    'Ma_lo_thuc_xuat' => $lo->Ma_lo,
    'So_luong' => 5,
    'Gia_von_xuat' => 200000,
]);

$postOut = [
    'mode' => 'out',
    'ma_phieu_xuat' => $maPxTest,
    'loai_nhap' => 'Hoàn giao thất bại',
    'ghi_chu_xu_ly_hoan' => 'Hàng bị vỡ toàn bộ trên đường giao hàng về',
];

$r = makeRequest($app, $kernel, 'POST', '/nv-kho/nhap-kho/xu-ly-hoan', $nvk, $postOut);
echo "Status: " . $r->getStatusCode() . "\n";
echo "Content: " . substr($r->getContent(), 0, 1000) . "\n";

// cleanup
ChiTietPhieuXuat::where('Ma_phieu_xuat', $maPxTest)->delete();
$px->delete();
$db->delete();

