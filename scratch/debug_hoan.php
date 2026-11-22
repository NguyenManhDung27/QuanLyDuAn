<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$nvk = App\Models\NguoiDung::where('Vai_tro', 'NV_KHO')->first();
$kh = App\Models\KhachHang::first();

$db = App\Models\DonHangBan::create([
    'Ma_don_hang_ban' => 'DB-DBG-1',
    'Ma_khach_hang' => $kh->Ma_khach_hang,
    'Ma_kho' => 'HN',
    'Ma_nguoi_lap' => $nvk->Ma_nguoi_dung,
    'Thoi_diem_xac_nhan' => now(),
    'Ten_nguoi_nhan' => 'A',
    'Sdt_nguoi_nhan' => '0901',
    'Dia_chi_giao_hang' => 'HN',
    'Tong_tien_hang' => 1000,
    'Trang_thai' => 'Giao thất bại'
]);

$px = App\Models\PhieuXuatKho::create([
    'Ma_phieu_xuat' => 'PX-DBG-1',
    'Ma_don_hang_ban' => 'DB-DBG-1',
    'Ngay_tao' => now()->toDateString(),
    'Ma_nguoi_xuat' => $nvk->Ma_nguoi_dung,
    'Thoi_diem_xuat_kho' => now(),
    'Trang_thai_giao_hang' => 'Giao hàng thất bại',
    'Trang_thai_hoan_hang' => 'Chờ xử lý'
]);

$session = $app['session']->driver();
$session->start();

$req = Illuminate\Http\Request::create('/nv-kho/nhap-kho/xu-ly-hoan', 'POST', [
    'mode' => 'out',
    'ma_phieu_xuat' => 'PX-DBG-1',
    'loai_nhap' => 'Hoàn giao thất bại',
    'ghi_chu_xu_ly_hoan' => 'Test'
]);
$req->setLaravelSession($session);
$req->setUserResolver(fn() => $nvk);

$res = $kernel->handle($req);

echo "STATUS: " . $res->getStatusCode() . "\n";
if ($res->isRedirection()) {
    echo "REDIRECT TO: " . $res->headers->get('Location') . "\n";
}
if ($session->has('errors')) {
    var_dump($session->get('errors'));
}
if ($session->has('error')) {
    echo "ERROR FLASH: " . $session->get('error') . "\n";
}
if ($session->has('success')) {
    echo "SUCCESS FLASH: " . $session->get('success') . "\n";
}

$px->delete();
$db->delete();

