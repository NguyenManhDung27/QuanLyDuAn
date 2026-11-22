<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\ChiTietDonBan;
use App\Models\ChiTietKiemKe;
use App\Models\ChiTietPhieuXuat;
use App\Models\DonHangBan;
use App\Models\KhachHang;
use App\Models\Lo;
use App\Models\NguoiDung;
use App\Models\PhieuKiemKe;
use App\Models\PhieuXuatKho;
use App\Models\SanPham;
use App\Services\StockService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

app()->instance(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, new class {
    public function handle($request, $next) { return $next($request); }
});

$passed = 0;
$failed = 0;

function assertTest($desc, $cond) {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] {$desc}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$desc}\n";
        $failed++;
    }
}

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

echo "=== KIỂM THỬ TỰ ĐỘNG SLICE 8: XUẤT KHO & GIAO HÀNG (NVK-07, NVK-08) ===\n\n";

// Tài khoản
$nvk = NguoiDung::where('Ten_dang_nhap', 'nvkho.hn')->first();
$qlk = NguoiDung::where('Ten_dang_nhap', 'qlkho.hn')->first();
$kt  = NguoiDung::where('Ten_dang_nhap', 'ketoan01')->first();
$kd  = NguoiDung::where('Ten_dang_nhap', 'kinhdoanh01')->first();

assertTest("Tải đủ 4 tài khoản test", $nvk && $qlk && $kt && $kd);

// Clean up any test records
ChiTietPhieuXuat::where('Ma_phieu_xuat', 'like', '%TEST-S8%')->delete();
PhieuXuatKho::where('Ma_phieu_xuat', 'like', '%TEST-S8%')->delete();
ChiTietDonBan::where('Ma_don_hang_ban', 'like', '%TEST-S8%')->delete();
DonHangBan::where('Ma_don_hang_ban', 'like', '%TEST-S8%')->delete();

echo "\n--- 1. Phân quyền truy cập NVK-07 & NVK-08 ---\n";

$r = makeRequest($app, $kernel, 'GET', '/nv-kho/phieu-xuat', $qlk);
assertTest("QL_KHO vào /nv-kho/phieu-xuat bị từ chối (403)", $r->getStatusCode() === 403);

$r = makeRequest($app, $kernel, 'GET', '/nv-kho/phieu-xuat', $kt);
assertTest("KE_TOAN vào /nv-kho/phieu-xuat bị từ chối (403)", $r->getStatusCode() === 403);

$r = makeRequest($app, $kernel, 'GET', '/nv-kho/phieu-xuat', $kd);
assertTest("KINH_DOANH vào /nv-kho/phieu-xuat bị từ chối (403)", $r->getStatusCode() === 403);

$r = makeRequest($app, $kernel, 'GET', '/nv-kho/phieu-xuat', $nvk);
assertTest("NV_KHO vào /nv-kho/phieu-xuat thành công (200)", $r->getStatusCode() === 200);

echo "\n--- 2. Chuẩn bị Đơn bán & Phiếu xuất test ---\n";

$kh = KhachHang::first();
$sp = SanPham::where('Trang_thai', 'Đang kinh doanh')->whereNotNull('Gia_niem_yet')->first();

// Tạo 2 lô riêng biệt cho cùng sản phẩm tại HN để test đổi lô
$maLoA = 'L26-TEST-A-' . rand(100, 999);
$maLoB = 'L26-TEST-B-' . rand(100, 999);

$loA = Lo::create([
    'Ma_lo'         => $maLoA,
    'Ma_san_pham'   => $sp->Ma_san_pham,
    'Ma_lo_ncc'     => 'NCC-' . $maLoA,
    'Ngay_san_xuat' => '2025-01-01',
    'Han_su_dung'   => '2028-01-01',
]);

$loB = Lo::create([
    'Ma_lo'         => $maLoB,
    'Ma_san_pham'   => $sp->Ma_san_pham,
    'Ma_lo_ncc'     => 'NCC-' . $maLoB,
    'Ngay_san_xuat' => '2025-02-01',
    'Han_su_dung'   => '2028-02-01',
]);

DB::table('Ton_kho')->insert([
    ['Ma_kho' => 'HN', 'Ma_lo' => $maLoA, 'Ton_thuc' => 20, 'Ngay_nhap_dau_tien' => '2026-01-01 10:00:00'],
    ['Ma_kho' => 'HN', 'Ma_lo' => $maLoB, 'Ton_thuc' => 30, 'Ngay_nhap_dau_tien' => '2026-02-01 10:00:00'],
]);

$maDon1 = 'DB-TEST-S8-1-' . rand(100, 999);
$maPx1  = 'PX-TEST-S8-1-' . rand(100, 999);

$db1 = DonHangBan::create([
    'Ma_don_hang_ban'   => $maDon1,
    'Ma_khach_hang'     => $kh->Ma_khach_hang,
    'Ma_kho'            => 'HN',
    'Ma_nguoi_lap'      => $kd->Ma_nguoi_dung,
    'Thoi_diem_xac_nhan'=> now(),
    'Ten_nguoi_nhan'    => 'Người Nhận Test 1',
    'Sdt_nguoi_nhan'    => '0911222333',
    'Dia_chi_giao_hang' => '123 Phố Huế, Hà Nội',
    'Tong_tien_hang'    => $sp->Gia_niem_yet * 10,
    'Trang_thai'        => 'Đang chuẩn bị hàng',
]);

ChiTietDonBan::create([
    'Ma_don_hang_ban' => $maDon1,
    'Ma_san_pham'     => $sp->Ma_san_pham,
    'So_luong'        => 10,
    'Don_gia_ban'     => $sp->Gia_niem_yet,
    'Chiet_khau'      => 0,
]);

$px1 = PhieuXuatKho::create([
    'Ma_phieu_xuat'       => $maPx1,
    'Ma_don_hang_ban'     => $maDon1,
    'Ngay_tao'            => now()->toDateString(),
    'Trang_thai_giao_hang'=> 'Đang chuẩn bị hàng',
    'Trang_thai_hoan_hang'=> 'Chưa xử lý',
]);

$ctPx1 = ChiTietPhieuXuat::create([
    'Ma_phieu_xuat'   => $maPx1,
    'Ma_lo_phan_bo'   => $maLoA,
    'Ma_lo_thuc_xuat' => $maLoA,
    'So_luong'        => 10,
]);

assertTest("Tạo thành công đơn bán & phiếu xuất test {$maPx1}", $px1 !== null);

// Kiểm tra NVK-07 hiển thị phiếu xuất này
$r = makeRequest($app, $kernel, 'GET', '/nv-kho/phieu-xuat', $nvk);
assertTest("NVK-07 hiển thị mã phiếu xuất {$maPx1}", str_contains($r->getContent(), $maPx1));

// Kiểm tra NVK-08 mở chi tiết
$r = makeRequest($app, $kernel, 'GET', "/nv-kho/phieu-xuat/{$maPx1}", $nvk);
assertTest("NVK-08 mở chi tiết thành công (200)", $r->getStatusCode() === 200);
assertTest("NVK-08 có nút 'Xác nhận xuất kho'", str_contains($r->getContent(), 'Xác nhận xuất kho'));
assertTest("NVK-08 có nút 'Đổi lô'", str_contains($r->getContent(), 'Đổi lô'));

echo "\n--- 3. NVK-08: Đổi lô do hàng hỏng (KB3) ---\n";

// Đổi 4 chai từ lô A sang lô B với lý do 'Hỏng hoặc vỡ', số lượng hỏng 4
$tonThucLoATruoc = (int) DB::table('Ton_kho')->where('Ma_kho', 'HN')->where('Ma_lo', $maLoA)->value('Ton_thuc');

$doiLoData = [
    'ma_lo_phan_bo'   => $maLoA,
    'ma_lo_thuc_xuat' => $maLoA,
    'so_luong_doi'    => 4,
    'ly_do_doi_lo'    => 'Hỏng hoặc vỡ',
    'so_luong_hong'   => 4,
    'replacements'    => [
        ['ma_lo' => $maLoB, 'so_luong' => 4],
    ],
];

$r = makeRequest($app, $kernel, 'POST', "/nv-kho/phieu-xuat/{$maPx1}/doi-lo", $nvk, $doiLoData);
assertTest("Đổi lô chuyển hướng 302", $r->getStatusCode() === 302);

// Kiểm tra chi tiết phiếu xuất sau đổi
$lineOld = ChiTietPhieuXuat::where('Ma_phieu_xuat', $maPx1)->where('Ma_lo_phan_bo', $maLoA)->where('Ma_lo_thuc_xuat', $maLoA)->first();
$lineNew = ChiTietPhieuXuat::where('Ma_phieu_xuat', $maPx1)->where('Ma_lo_phan_bo', $maLoA)->where('Ma_lo_thuc_xuat', $maLoB)->first();

assertTest("Dòng cũ giảm còn 6 chai (10 - 4)", $lineOld?->So_luong === 6);
assertTest("Dòng mới tạo thành công với 4 chai lấy từ lô B", $lineNew?->So_luong === 4);
assertTest("Dòng mới lưu đúng lý do 'Hỏng hoặc vỡ'", $lineNew?->Ly_do_doi_lo === 'Hỏng hoặc vỡ');
assertTest("Dòng mới lưu số lượng hỏng = 4", $lineNew?->So_luong_hong === 4);
assertTest("Dòng mới ghi nhận người đổi", $lineNew?->Ma_nguoi_doi_lo === $nvk->Ma_nguoi_dung);

// Kiểm tra tồn thực của lô A bị trừ ngay 4 chai hỏng
$tonThucLoASau = (int) DB::table('Ton_kho')->where('Ma_kho', 'HN')->where('Ma_lo', $maLoA)->value('Ton_thuc');
assertTest("Tồn thực lô A bị trừ ngay 4 chai hỏng (20 -> 16)", $tonThucLoASau === $tonThucLoATruoc - 4);

echo "\n--- 4. NVK-08: Xác nhận xuất kho ---\n";

// Thử xuất kho không có thông tin vận chuyển -> validation error
$r = makeRequest($app, $kernel, 'POST', "/nv-kho/phieu-xuat/{$maPx1}/xuat-kho", $nvk, [
    'don_vi_van_chuyen' => '',
    'ma_van_don' => '',
]);
assertTest("Xuất kho thiếu ĐVVC / vận đơn bị từ chối (không phải 200)", $r->getStatusCode() !== 200);

$tonLoATruocXuat = (int) DB::table('Ton_kho')->where('Ma_kho', 'HN')->where('Ma_lo', $maLoA)->value('Ton_thuc');
$tonLoBTruocXuat = (int) DB::table('Ton_kho')->where('Ma_kho', 'HN')->where('Ma_lo', $maLoB)->value('Ton_thuc');

// Xuất kho hợp lệ
$r = makeRequest($app, $kernel, 'POST', "/nv-kho/phieu-xuat/{$maPx1}/xuat-kho", $nvk, [
    'don_vi_van_chuyen' => 'Giao Hàng Nhanh',
    'ma_van_don' => 'GHN-TEST-999',
]);
assertTest("Xác nhận xuất kho chuyển hướng 302", $r->getStatusCode() === 302);

$px1->refresh();
$db1->refresh();

assertTest("Phiếu xuất chuyển sang 'Đang giao'", $px1->Trang_thai_giao_hang === 'Đang giao');
assertTest("Đơn bán chuyển sang 'Đang giao'", $db1->Trang_thai === 'Đang giao');
assertTest("Lưu đơn vị vận chuyển 'Giao Hàng Nhanh'", $px1->Don_vi_van_chuyen === 'Giao Hàng Nhanh');
assertTest("Lưu mã vận đơn 'GHN-TEST-999'", $px1->Ma_van_don === 'GHN-TEST-999');
assertTest("Đóng dấu thời điểm xuất kho", $px1->Thoi_diem_xuat_kho !== null);

// Tồn thực của 2 lô bị trừ theo số lượng thực xuất: Lô A trừ 6, Lô B trừ 4
$tonLoASauXuat = (int) DB::table('Ton_kho')->where('Ma_kho', 'HN')->where('Ma_lo', $maLoA)->value('Ton_thuc');
$tonLoBSauXuat = (int) DB::table('Ton_kho')->where('Ma_kho', 'HN')->where('Ma_lo', $maLoB)->value('Ton_thuc');

assertTest("Tồn thực lô A bị trừ 6 chai khi xuất kho", $tonLoASauXuat === $tonLoATruocXuat - 6);
assertTest("Tồn thực lô B bị trừ 4 chai khi xuất kho", $tonLoBSauXuat === $tonLoBTruocXuat - 4);

// Kiểm tra chụp giá vốn xuất (F7)
$lineOld->refresh();
$lineNew->refresh();
assertTest("Dòng 1 được chụp giá vốn xuất", $lineOld->Gia_von_xuat > 0);
assertTest("Dòng 2 được chụp giá vốn xuất", $lineNew->Gia_von_xuat > 0);

echo "\n--- 5. NVK-08: Sửa thông tin vận chuyển khi Đang giao ---\n";

$r = makeRequest($app, $kernel, 'PUT', "/nv-kho/phieu-xuat/{$maPx1}/van-chuyen", $nvk, [
    'don_vi_van_chuyen' => 'Viettel Post',
    'ma_van_don' => 'VTP-8888',
]);
assertTest("Sửa vận chuyển chuyển hướng 302", $r->getStatusCode() === 302);
$px1->refresh();
assertTest("Cập nhật thành 'Viettel Post'", $px1->Don_vi_van_chuyen === 'Viettel Post');
assertTest("Cập nhật thành 'VTP-8888'", $px1->Ma_van_don === 'VTP-8888');

echo "\n--- 6. NVK-08: Ghi nhận Giao thất bại ---\n";

// Thử giao thất bại không có lý do -> validation error
$r = makeRequest($app, $kernel, 'POST', "/nv-kho/phieu-xuat/{$maPx1}/giao-hang", $nvk, [
    'ket_qua' => 'Giao hàng thất bại',
    'ly_do_giao_that_bai' => '',
]);
assertTest("Giao thất bại không có lý do bị từ chối", $r->getStatusCode() !== 200);

// Giao thất bại hợp lệ
$r = makeRequest($app, $kernel, 'POST', "/nv-kho/phieu-xuat/{$maPx1}/giao-hang", $nvk, [
    'ket_qua' => 'Giao hàng thất bại',
    'ly_do_giao_that_bai' => 'Khách đi vắng không thể nhận hàng',
]);
assertTest("Ghi nhận giao thất bại chuyển hướng 302", $r->getStatusCode() === 302);

$px1->refresh();
$db1->refresh();

assertTest("Phiếu xuất chuyển sang 'Giao hàng thất bại'", $px1->Trang_thai_giao_hang === 'Giao hàng thất bại');
assertTest("Phiếu xuất có Trang_thai_hoan_hang = 'Chờ xử lý'", $px1->Trang_thai_hoan_hang === 'Chờ xử lý');
assertTest("Lưu lý do giao thất bại", $px1->Ly_do_giao_that_bai === 'Khách đi vắng không thể nhận hàng');
assertTest("Đơn bán chuyển sang 'Giao thất bại'", $db1->Trang_thai === 'Giao thất bại');

echo "\n--- 7. NVK-08: Ghi nhận Giao thành công (trên đơn thứ 2) ---\n";

$maDon2 = 'DB-TEST-S8-2-' . rand(100, 999);
$maPx2  = 'PX-TEST-S8-2-' . rand(100, 999);

$db2 = DonHangBan::create([
    'Ma_don_hang_ban'   => $maDon2,
    'Ma_khach_hang'     => $kh->Ma_khach_hang,
    'Ma_kho'            => 'HN',
    'Ma_nguoi_lap'      => $kd->Ma_nguoi_dung,
    'Thoi_diem_xac_nhan'=> now(),
    'Ten_nguoi_nhan'    => 'Người Nhận Test 2',
    'Sdt_nguoi_nhan'    => '0911222334',
    'Dia_chi_giao_hang' => '456 Cầu Giấy, Hà Nội',
    'Tong_tien_hang'    => $sp->Gia_niem_yet * 2,
    'Trang_thai'        => 'Đang chuẩn bị hàng',
]);

ChiTietDonBan::create([
    'Ma_don_hang_ban' => $maDon2,
    'Ma_san_pham'     => $sp->Ma_san_pham,
    'So_luong'        => 2,
    'Don_gia_ban'     => $sp->Gia_niem_yet,
    'Chiet_khau'      => 0,
]);

$px2 = PhieuXuatKho::create([
    'Ma_phieu_xuat'       => $maPx2,
    'Ma_don_hang_ban'     => $maDon2,
    'Ngay_tao'            => now()->toDateString(),
    'Trang_thai_giao_hang'=> 'Đang chuẩn bị hàng',
    'Trang_thai_hoan_hang'=> 'Chưa xử lý',
]);

ChiTietPhieuXuat::create([
    'Ma_phieu_xuat'   => $maPx2,
    'Ma_lo_phan_bo'   => $maLoB,
    'Ma_lo_thuc_xuat' => $maLoB,
    'So_luong'        => 2,
]);

// Xuất kho đơn 2
StockService::xuatKho($px2, $nvk, 'Xe công ty', 'XE-01');

// Cập nhật giao thành công
$r = makeRequest($app, $kernel, 'POST', "/nv-kho/phieu-xuat/{$maPx2}/giao-hang", $nvk, [
    'ket_qua' => 'Giao thành công',
]);
assertTest("Cập nhật giao thành công chuyển hướng 302", $r->getStatusCode() === 302);

$px2->refresh();
$db2->refresh();
assertTest("Phiếu xuất 2 chuyển sang 'Giao thành công'", $px2->Trang_thai_giao_hang === 'Giao thành công');
assertTest("Đơn bán 2 chuyển sang 'Hoàn thành'", $db2->Trang_thai === 'Hoàn thành');

echo "\n--- 8. Chặn xuất kho khi Lô đang kiểm kê ---\n";

$maPKK = 'KK-TEST-S8-' . rand(100, 999);
$pkk = PhieuKiemKe::create([
    'Ma_phieu_kiem_ke' => $maPKK,
    'Ma_kho' => 'HN',
    'Ma_nguoi_tao' => $qlk->Ma_nguoi_dung,
    'Thoi_diem_tao' => now(),
    'Trang_thai' => 'Đang kiểm',
]);

ChiTietKiemKe::create([
    'Ma_phieu_kiem_ke' => $maPKK,
    'Ma_lo' => $maLoB,
    'So_luong_he_thong' => 10,
]);

$maDon3 = 'DB-TEST-S8-3-' . rand(100, 999);
$maPx3  = 'PX-TEST-S8-3-' . rand(100, 999);
$db3 = DonHangBan::create([
    'Ma_don_hang_ban' => $maDon3,
    'Ma_khach_hang' => $kh->Ma_khach_hang,
    'Ma_kho' => 'HN',
    'Ma_nguoi_lap' => $kd->Ma_nguoi_dung,
    'Thoi_diem_xac_nhan' => now(),
    'Ten_nguoi_nhan' => 'Test 3',
    'Sdt_nguoi_nhan' => '0901',
    'Dia_chi_giao_hang' => 'HN',
    'Tong_tien_hang' => 1000,
    'Trang_thai' => 'Đang chuẩn bị hàng',
]);
$px3 = PhieuXuatKho::create([
    'Ma_phieu_xuat' => $maPx3,
    'Ma_don_hang_ban' => $maDon3,
    'Ngay_tao' => now()->toDateString(),
    'Trang_thai_giao_hang' => 'Đang chuẩn bị hàng',
]);
ChiTietPhieuXuat::create([
    'Ma_phieu_xuat' => $maPx3,
    'Ma_lo_phan_bo' => $maLoB,
    'Ma_lo_thuc_xuat' => $maLoB,
    'So_luong' => 1,
]);

$biChanKiemKe = false;
try {
    StockService::xuatKho($px3, $nvk, 'GHN', 'GHN-KK');
} catch (\Exception $e) {
    if (str_contains($e->getMessage(), 'chưa hoàn tất')) {
        $biChanKiemKe = true;
    }
}
assertTest("Chặn xuất kho khi có lô thuộc phiếu kiểm kê chưa hoàn tất", $biChanKiemKe);

// Dọn dẹp phiếu kiểm kê test
ChiTietKiemKe::where('Ma_phieu_kiem_ke', $maPKK)->delete();
$pkk->delete();

// Dọn dẹp dữ liệu test
ChiTietPhieuXuat::whereIn('Ma_phieu_xuat', [$maPx1, $maPx2, $maPx3])->delete();
PhieuXuatKho::whereIn('Ma_phieu_xuat', [$maPx1, $maPx2, $maPx3])->delete();
ChiTietDonBan::whereIn('Ma_don_hang_ban', [$maDon1, $maDon2, $maDon3])->delete();
DonHangBan::whereIn('Ma_don_hang_ban', [$maDon1, $maDon2, $maDon3])->delete();
DB::table('Ton_kho')->whereIn('Ma_lo', [$maLoA, $maLoB])->delete();
$loA->delete();
$loB->delete();

echo "\n======================================================\n";
echo "KẾT QUẢ KIỂM THỬ: {$passed} PASS, {$failed} FAIL\n";
echo "======================================================\n";

exit($failed > 0 ? 1 : 0);

