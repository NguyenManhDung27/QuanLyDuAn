<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\ChiTietDonBan;
use App\Models\ChiTietDonDatHang;
use App\Models\ChiTietKiemKe;
use App\Models\ChiTietPhieuNhap;
use App\Models\ChiTietPhieuXuat;
use App\Models\DonDatHang;
use App\Models\DonHangBan;
use App\Models\Lo;
use App\Models\NguoiDung;
use App\Models\NhaCungCap;
use App\Models\PhieuKiemKe;
use App\Models\PhieuNhapKho;
use App\Models\PhieuXuatKho;
use App\Models\SanPham;
use App\Models\TonKho;
use App\Services\StockService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Tắt CSRF trong test script CLI
app()->instance(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, new class {
    public function handle($request, $next) { return $next($request); }
});

$passed = 0;
$failed = 0;

function assertTest($description, $condition) {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] {$description}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$description}\n";
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

echo "=== KIỂM THỬ TỰ ĐỘNG SLICE 6: NHẬP KHO (NVK-02 ĐẾN NVK-06) ===\n\n";

// --- Chuẩn bị tài khoản ---
$nvk = NguoiDung::where('Ten_dang_nhap', 'nvkho.hn')->first();
$qlk = NguoiDung::where('Ten_dang_nhap', 'qlkho.hn')->first();
$kt  = NguoiDung::where('Ten_dang_nhap', 'ketoan01')->first();
$kd  = NguoiDung::where('Ten_dang_nhap', 'kinhdoanh01')->first();

assertTest("Tải đủ 4 tài khoản test", $nvk && $qlk && $kt && $kd);

// Dọn dẹp trước nếu có tàn dư từ các lần chạy trước
ChiTietPhieuNhap::where('Ma_phieu_nhap', 'like', '%TEST%')->delete();
PhieuNhapKho::where('Ma_phieu_nhap', 'like', '%TEST%')->delete();
ChiTietDonDatHang::where('Ma_don_dat_hang', 'like', '%TEST%')->delete();
DonDatHang::where('Ma_don_dat_hang', 'like', '%TEST%')->delete();
ChiTietPhieuXuat::where('Ma_phieu_xuat', 'like', '%TEST%')->delete();
PhieuXuatKho::where('Ma_phieu_xuat', 'like', '%TEST%')->delete();
DonHangBan::where('Ma_don_hang_ban', 'like', '%TEST%')->delete();
ChiTietKiemKe::where('Ma_phieu_kiem_ke', 'like', '%TEST%')->delete();
PhieuKiemKe::where('Ma_phieu_kiem_ke', 'like', '%TEST%')->delete();
DB::table('Ton_kho')->whereIn('Ma_lo', Lo::where('Ma_lo_ncc', 'like', '%TEST%')->pluck('Ma_lo'))->delete();
Lo::where('Ma_lo_ncc', 'like', '%TEST%')->delete();
SanPham::where('Ma_san_pham', 'SP0001')->update(['Gia_von_binh_quan' => 800000]);

// =============================================================
echo "\n--- 1. Kiểm tra phân quyền truy cập NV_KHO ---\n";
// =============================================================

$r = makeRequest($app, $kernel, 'GET', '/nv-kho/phieu-nhap', $qlk);
assertTest("QL_KHO vào /nv-kho/phieu-nhap bị từ chối (403)", $r->getStatusCode() === 403);

$r = makeRequest($app, $kernel, 'GET', '/nv-kho/phieu-nhap', $kt);
assertTest("KE_TOAN vào /nv-kho/phieu-nhap bị từ chối (403)", $r->getStatusCode() === 403);

$r = makeRequest($app, $kernel, 'GET', '/nv-kho/phieu-nhap', $kd);
assertTest("KINH_DOANH vào /nv-kho/phieu-nhap bị từ chối (403)", $r->getStatusCode() === 403);

$r = makeRequest($app, $kernel, 'GET', '/nv-kho/phieu-nhap', $nvk);
assertTest("NV_KHO vào /nv-kho/phieu-nhap thành công (200)", $r->getStatusCode() === 200);

$r = makeRequest($app, $kernel, 'GET', '/nv-kho/nhap-kho', $nvk);
assertTest("NV_KHO vào /nv-kho/nhap-kho thành công (200)", $r->getStatusCode() === 200);

// =============================================================
echo "\n--- 2. Chuẩn bị Đơn đặt hàng ở trạng thái 'Đã gửi NCC' ---\n";
// =============================================================

$sp = SanPham::where('Trang_thai', 'Đang kinh doanh')->first();
$ncc = NhaCungCap::first();

// Tạo đơn đặt hàng test
$maDonTest = 'DDH-TEST-S6-' . rand(100, 999);
$donTest = DonDatHang::create([
    'Ma_don_dat_hang' => $maDonTest,
    'Ma_ncc' => $ncc->Ma_ncc,
    'Ma_kho' => 'HN',
    'Ma_nguoi_lap' => $qlk->Ma_nguoi_dung,
    'Ngay_lap' => now()->toDateString(),
    'Ngay_du_kien_nhan_du' => now()->addDays(7)->toDateString(),
    'Trang_thai' => 'Đã gửi NCC',
    'Tong_gia_tri' => 50 * 200000,
]);

ChiTietDonDatHang::create([
    'Ma_don_dat_hang' => $maDonTest,
    'Ma_san_pham' => $sp->Ma_san_pham,
    'So_luong_dat' => 50,
    'Don_gia_mua' => 200000,
]);

assertTest("Tạo Đơn đặt hàng test {$maDonTest} ở trạng thái 'Đã gửi NCC'", $donTest && $donTest->Trang_thai === 'Đã gửi NCC');

// Màn hình NVK-03 hiển thị đơn này
$r = makeRequest($app, $kernel, 'GET', '/nv-kho/nhap-kho', $nvk, ['loai' => 'Nhập từ NCC']);
assertTest("NVK-03 hiển thị đơn đặt hàng {$maDonTest}", str_contains($r->getContent(), $maDonTest));

// Màn hình NVK-04 mở form nhập đơn
$r = makeRequest($app, $kernel, 'GET', "/nv-kho/nhap-kho/ncc/{$maDonTest}", $nvk);
assertTest("NVK-04 mở form nhập hàng thành công (200)", $r->getStatusCode() === 200);

// =============================================================
echo "\n--- 3. NVK-04: Lưu nháp Phiếu nhập kho (Đang cập nhật) ---\n";
// =============================================================

$postDraft = [
    'action' => 'save',
    'so_phieu_giao_ncc' => 'PGN-NCC-001',
    'dot_giao_cuoi' => 0,
    'items' => [
        [
            'ma_san_pham' => $sp->Ma_san_pham,
            'ma_lo' => 'LOT-TEST-DRAFT-1',
            'ngay_san_xuat' => '2023-01-01',
            'han_su_dung' => '2030-01-01',
            'so_luong_nhap' => 20,
            'so_luong_hao_hut' => 0,
        ]
    ]
];

$r = makeRequest($app, $kernel, 'POST', "/nv-kho/nhap-kho/ncc/{$maDonTest}", $nvk, $postDraft);
assertTest("Lưu nháp phiếu nhập chuyển hướng 302", $r->getStatusCode() === 302);

$pnDraft = PhieuNhapKho::where('Ma_don_dat_hang', $maDonTest)->where('Trang_thai', 'Đang cập nhật')->first();
assertTest("Phiếu nhập tạo thành công với mã PN-* và trạng thái 'Đang cập nhật'", $pnDraft && str_starts_with($pnDraft->Ma_phieu_nhap, 'PN-'));

// Kiểm tra tồn kho chưa bị thay đổi
$loDraft = Lo::where('Ma_lo_ncc', 'LOT-TEST-DRAFT-1')->first();
$tonDraft = $loDraft ? (int)DB::table('Ton_kho')->where('Ma_kho', 'HN')->where('Ma_lo', $loDraft->Ma_lo)->value('Ton_thuc') : 0;
assertTest("Lưu nháp: Tồn kho chưa đổi (= 0)", $tonDraft === 0);

// Đơn hàng vẫn giữ nguyên 'Đã gửi NCC'
$donTest->refresh();
assertTest("Lưu nháp: Đơn đặt hàng vẫn giữ 'Đã gửi NCC'", $donTest->Trang_thai === 'Đã gửi NCC');

// =============================================================
echo "\n--- 4. NVK-04 & StockService: Xác nhận nhập kho & Tính F7 ---\n";
// =============================================================

$tonTruoc = StockService::getTonThuc('HN', $sp->Ma_san_pham) + StockService::getTonThuc('HCM', $sp->Ma_san_pham);
$giaVonCu = (float) $sp->Gia_von_binh_quan;
$donGiaMua = 200000;
$soLuongNhap = 30;
$soLuongHaoHut = 2;

// Tính giá vốn F7 lý thuyết
$giaVonKyVong = round(($tonTruoc * $giaVonCu + $soLuongNhap * $donGiaMua) / ($tonTruoc + $soLuongNhap));

$postConfirm = [
    'action' => 'confirm',
    'so_phieu_giao_ncc' => 'PGN-NCC-002',
    'dot_giao_cuoi' => 0,
    'items' => [
        [
            'ma_san_pham' => $sp->Ma_san_pham,
            'ma_lo' => 'LOT-TEST-CONFIRM-1',
            'ngay_san_xuat' => '2023-02-01',
            'han_su_dung' => '2030-02-01',
            'so_luong_nhap' => $soLuongNhap,
            'so_luong_hao_hut' => $soLuongHaoHut,
            'ly_do_hao_hut' => 'Vỡ 2 chai trong quá trình vận chuyển',
        ]
    ]
];

$r = makeRequest($app, $kernel, 'POST', "/nv-kho/nhap-kho/ncc/{$maDonTest}", $nvk, $postConfirm);
assertTest("Xác nhận nhập kho chuyển hướng 302", $r->getStatusCode() === 302);

$pnConfirmed = PhieuNhapKho::where('Ma_don_dat_hang', $maDonTest)->where('Trang_thai', 'Đã nhập kho')->first();
assertTest("Phiếu nhập chuyển thành công sang 'Đã nhập kho'", $pnConfirmed !== null);

// Kiểm tra tồn kho tăng đúng bằng số lượng nhận đạt
$loConfirmed = Lo::where('Ma_lo_ncc', 'LOT-TEST-CONFIRM-1')->first();
$tonSau = (int)DB::table('Ton_kho')->where('Ma_kho', 'HN')->where('Ma_lo', $loConfirmed->Ma_lo)->value('Ton_thuc');
assertTest("Tồn thực của lô mới tăng đúng {$soLuongNhap} chai", $tonSau === $soLuongNhap);

// Kiểm tra giá vốn F7
$sp->refresh();
assertTest("Giá vốn bình quân F7 được cập nhật đúng công thức (thực tế: {$sp->Gia_von_binh_quan}, kỳ vọng: {$giaVonKyVong})", (int)$sp->Gia_von_binh_quan === (int)$giaVonKyVong);

// Đơn đặt hàng chuyển sang 'Nhận một phần' (nhận 30 + hao hụt 2 = 32 / 50)
$donTest->refresh();
assertTest("Đơn đặt hàng cập nhật sang 'Nhận một phần'", $donTest->Trang_thai === 'Nhận một phần');

// =============================================================
echo "\n--- 5. Chặn xác nhận nhập kho khi Lô đang kiểm kê ---\n";
// =============================================================

// Tạo phiếu kiểm kê giả lập đang kiểm lô LOT-TEST-CONFIRM-1
$maPKK = 'PKK-TEST-' . rand(100, 999);
$pkk = PhieuKiemKe::create([
    'Ma_phieu_kiem_ke' => $maPKK,
    'Ma_kho' => 'HN',
    'Ma_nguoi_tao' => $qlk->Ma_nguoi_dung,
    'Ma_nguoi_kiem' => $nvk->Ma_nguoi_dung,
    'Thoi_diem_tao' => now(),
    'Trang_thai' => 'Đang kiểm',
]);

ChiTietKiemKe::create([
    'Ma_phieu_kiem_ke' => $maPKK,
    'Ma_lo' => $loConfirmed->Ma_lo,
    'So_luong_he_thong' => $tonSau,
]);

// Tạo 1 phiếu nhập khác cũng nhập vào lô này
$maPnKK = 'PN-TEST-KK-' . rand(1000, 9999);
$pnTestKiemKe = PhieuNhapKho::create([
    'Ma_phieu_nhap' => $maPnKK,
    'Ma_kho' => 'HN',
    'Loai_nhap' => 'Nhập từ NCC',
    'Ma_don_dat_hang' => $maDonTest,
    'Ma_nguoi_lap' => $nvk->Ma_nguoi_dung,
    'Ngay_lap' => now()->toDateString(),
    'Trang_thai' => 'Đang cập nhật',
]);

ChiTietPhieuNhap::create([
    'Ma_phieu_nhap' => $maPnKK,
    'Ma_lo' => $loConfirmed->Ma_lo,
    'So_luong_nhap' => 5,
    'So_luong_hao_hut' => 0,
]);

$biChan = false;
try {
    StockService::nhapKho($pnTestKiemKe);
} catch (\Exception $e) {
    if (str_contains($e->getMessage(), 'chưa hoàn tất')) {
        $biChan = true;
    }
}
assertTest("Chặn xác nhận nhập kho khi lô thuộc phiếu kiểm kê chưa hoàn tất", $biChan);

// Màn hình NVK-02 hiển thị badge 'Chờ kiểm kê xong'
$r = makeRequest($app, $kernel, 'GET', '/nv-kho/phieu-nhap', $nvk);
assertTest("NVK-02 hiển thị nhãn 'Chờ kiểm kê xong'", str_contains($r->getContent(), 'Chờ kiểm kê xong'));

// Dọn dẹp phiếu kiểm kê test
ChiTietKiemKe::where('Ma_phieu_kiem_ke', $maPKK)->delete();
$pkk->delete();
ChiTietPhieuNhap::where('Ma_phieu_nhap', $maPnKK)->delete();
$pnTestKiemKe->delete();

// =============================================================
echo "\n--- 6. Nhập hàng đủ và Đợt giao cuối → Đã nhận đủ ---\n";
// =============================================================

// Nhập 18 chai còn lại (32 + 18 = 50)
$maPnFinal = 'PN-TEST-FINAL-' . rand(1000, 9999);
$pnFinal = PhieuNhapKho::create([
    'Ma_phieu_nhap' => $maPnFinal,
    'Ma_kho' => 'HN',
    'Loai_nhap' => 'Nhập từ NCC',
    'Ma_don_dat_hang' => $maDonTest,
    'Ma_nguoi_lap' => $nvk->Ma_nguoi_dung,
    'Ngay_lap' => now()->toDateString(),
    'Dot_giao_cuoi' => 1,
    'Trang_thai' => 'Đang cập nhật',
]);

ChiTietPhieuNhap::create([
    'Ma_phieu_nhap' => $maPnFinal,
    'Ma_lo' => $loConfirmed->Ma_lo,
    'So_luong_nhap' => 18,
    'So_luong_hao_hut' => 0,
]);

StockService::nhapKho($pnFinal);
$donTest->refresh();
assertTest("Nhận đủ 50 chai → Đơn đặt hàng chuyển thành 'Đã nhận đủ'", $donTest->Trang_thai === 'Đã nhận đủ');

// =============================================================
echo "\n--- 7. NVK-05 / NVK-06: Xử lý hàng hoàn ---\n";
// =============================================================

// Giả lập đơn bán và phiếu xuất
$maDonBanTest = 'DB-TEST-S6-' . rand(100, 999);
$kh = \App\Models\KhachHang::first();
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

ChiTietPhieuXuat::create([
    'Ma_phieu_xuat' => $maPxTest,
    'Ma_lo_phan_bo' => $loConfirmed->Ma_lo,
    'Ma_lo_thuc_xuat' => $loConfirmed->Ma_lo,
    'So_luong' => 5,
    'Gia_von_xuat' => 200000,
]);

// 7a. Nhánh Không nhập kho
$postOut = [
    'mode' => 'out',
    'ma_phieu_xuat' => $maPxTest,
    'loai_nhap' => 'Hoàn giao thất bại',
    'ghi_chu_xu_ly_hoan' => 'Hàng bị vỡ toàn bộ trên đường giao hàng về',
];

$r = makeRequest($app, $kernel, 'POST', '/nv-kho/nhap-kho/xu-ly-hoan', $nvk, $postOut);
assertTest("Xử lý Không nhập kho chuyển hướng 302", $r->getStatusCode() === 302);

$px->refresh();
assertTest("Phiếu xuất đổi sang 'Đã xử lý – không nhập kho'", $px->Trang_thai_hoan_hang === 'Đã xử lý – không nhập kho');
assertTest("Ghi chú xử lý hoàn được lưu", $px->Ghi_chu_xu_ly_hoan === 'Hàng bị vỡ toàn bộ trên đường giao hàng về');

// 7b. Nhánh Nhập lại kho (tạo 1 đơn bán & phiếu xuất khác)
$maDonBanTest2 = 'DB-TEST-S6-B-' . rand(100, 999);
$db2 = DonHangBan::create([
    'Ma_don_hang_ban' => $maDonBanTest2,
    'Ma_khach_hang' => $kh->Ma_khach_hang,
    'Ma_kho' => 'HN',
    'Ma_nguoi_lap' => $kd->Ma_nguoi_dung,
    'Thoi_diem_xac_nhan' => now(),
    'Ten_nguoi_nhan' => $kh->Ten_khach_hang,
    'Sdt_nguoi_nhan' => $kh->So_dien_thoai ?: '0901112233',
    'Dia_chi_giao_hang' => $kh->Dia_chi ?: 'Hà Nội',
    'Tong_tien_hang' => 250000 * 4,
    'Trang_thai' => 'Giao thất bại',
]);

$maPxTest2 = 'PX-TEST-S6-B-' . rand(100, 999);
$px2 = PhieuXuatKho::create([
    'Ma_phieu_xuat' => $maPxTest2,
    'Ma_don_hang_ban' => $maDonBanTest2,
    'Ngay_tao' => now()->toDateString(),
    'Ma_nguoi_xuat' => $nvk->Ma_nguoi_dung,
    'Thoi_diem_xuat_kho' => now(),
    'Trang_thai_giao_hang' => 'Giao hàng thất bại',
    'Trang_thai_hoan_hang' => 'Chờ xử lý',
    'Ly_do_giao_that_bai' => 'Khách từ chối nhận',
]);

ChiTietPhieuXuat::create([
    'Ma_phieu_xuat' => $maPxTest2,
    'Ma_lo_phan_bo' => $loConfirmed->Ma_lo,
    'Ma_lo_thuc_xuat' => $loConfirmed->Ma_lo,
    'So_luong' => 4,
    'Gia_von_xuat' => 200000,
]);

$tonTruocHoan = (int)DB::table('Ton_kho')->where('Ma_kho', 'HN')->where('Ma_lo', $loConfirmed->Ma_lo)->value('Ton_thuc');

$postIn = [
    'mode' => 'in',
    'ma_phieu_xuat' => $maPxTest2,
    'loai_nhap' => 'Hoàn giao thất bại',
    'ly_do_tra_hang' => 'Khách hủy đơn khi giao',
    'ghi_chu_xu_ly_hoan' => 'Nhận lại đủ 4 chai nguyên vẹn',
    'items' => [
        [
            'ma_lo' => $loConfirmed->Ma_lo,
            'so_luong_nhap' => 4,
        ]
    ]
];

$r = makeRequest($app, $kernel, 'POST', '/nv-kho/nhap-kho/xu-ly-hoan', $nvk, $postIn);
assertTest("Nhập lại kho từ hoàn giao chuyển hướng 302", $r->getStatusCode() === 302);

$px2->refresh();
assertTest("Phiếu xuất đổi sang 'Đã nhập lại kho'", $px2->Trang_thai_hoan_hang === 'Đã nhập lại kho');

$tonSauHoan = (int)DB::table('Ton_kho')->where('Ma_kho', 'HN')->where('Ma_lo', $loConfirmed->Ma_lo)->value('Ton_thuc');
assertTest("Tồn kho được cộng lại đúng 4 chai", $tonSauHoan === $tonTruocHoan + 4);

// =============================================================
// Dọn dẹp dữ liệu test
// =============================================================
ChiTietPhieuNhap::whereIn('Ma_phieu_nhap', [$pnDraft->Ma_phieu_nhap, $pnConfirmed->Ma_phieu_nhap, $pnFinal->Ma_phieu_nhap])->delete();
PhieuNhapKho::whereIn('Ma_phieu_nhap', [$pnDraft->Ma_phieu_nhap, $pnConfirmed->Ma_phieu_nhap, $pnFinal->Ma_phieu_nhap])->delete();
ChiTietDonDatHang::where('Ma_don_dat_hang', $maDonTest)->delete();
$donTest->delete();

ChiTietPhieuNhap::whereHas('phieuNhapKho', function($q) use ($maPxTest2) {
    $q->where('Ma_phieu_xuat', $maPxTest2);
})->delete();
PhieuNhapKho::where('Ma_phieu_xuat', $maPxTest2)->delete();

ChiTietPhieuXuat::whereIn('Ma_phieu_xuat', [$maPxTest, $maPxTest2])->delete();
PhieuXuatKho::whereIn('Ma_phieu_xuat', [$maPxTest, $maPxTest2])->delete();
$db->delete();
if (isset($db2)) {
    $db2->delete();
}

if ($loDraft) {
    DB::table('Ton_kho')->where('Ma_lo', $loDraft->Ma_lo)->delete();
    $loDraft->delete();
}
if ($loConfirmed) {
    DB::table('Ton_kho')->where('Ma_lo', $loConfirmed->Ma_lo)->delete();
    $loConfirmed->delete();
}
SanPham::where('Ma_san_pham', 'SP0001')->update(['Gia_von_binh_quan' => 800000]);

echo "\n======================================================\n";
echo "KẾT QUẢ KIỂM THỬ: {$passed} PASS, {$failed} FAIL\n";
echo "======================================================\n";

exit($failed > 0 ? 1 : 0);
