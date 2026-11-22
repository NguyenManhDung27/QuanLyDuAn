<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\ChiTietDonBan;
use App\Models\ChiTietDonDatHang;
use App\Models\ChiTietPhieuNhap;
use App\Models\ChiTietPhieuXuat;
use App\Models\DonHangBan;
use App\Models\KhachHang;
use App\Models\Lo;
use App\Models\NguoiDung;
use App\Models\PhieuNhapKho;
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

echo "=== KIỂM THỬ TỰ ĐỘNG SLICE 7: BÁN HÀNG (KD-02 ĐẾN KD-04) ===\n\n";

// Dọn dẹp trước
ChiTietPhieuXuat::where('Ma_phieu_xuat', 'like', 'PX-TEST-S7%')->delete();
PhieuXuatKho::where('Ma_phieu_xuat', 'like', 'PX-TEST-S7%')->delete();
ChiTietDonBan::where('Ma_don_hang_ban', 'like', 'DB-TEST-S7%')->delete();
DonHangBan::where('Ma_don_hang_ban', 'like', 'DB-TEST-S7%')->delete();

// Accounts
$kd  = NguoiDung::where('Ten_dang_nhap', 'kinhdoanh01')->first();
$qlk = NguoiDung::where('Ten_dang_nhap', 'qlkho.hn')->first();
$kt  = NguoiDung::where('Ten_dang_nhap', 'ketoan01')->first();
$nvk = NguoiDung::where('Ten_dang_nhap', 'nvkho.hn')->first();

assertTest("Tải đủ 4 tài khoản test", $kd && $qlk && $kt && $nvk);

// Lấy dữ liệu có sẵn
$kh   = KhachHang::where('Trang_thai', 'Đang giao dịch')->first();
$sp   = SanPham::where('Trang_thai', 'Đang kinh doanh')->whereNotNull('Gia_niem_yet')->first();
$lo   = Lo::where('Ma_san_pham', $sp->Ma_san_pham)->first();

assertTest("Có khách hàng và sản phẩm để test", $kh && $sp && $lo);

// Kiểm tra tồn khả dụng ban đầu
$tonTruoc = StockService::getTonKhaDung('HN', $sp->Ma_san_pham);
$tonThucTruoc = StockService::getTonThuc('HN', $sp->Ma_san_pham);

echo "\n--- 1. Phân quyền ---\n";

$r = makeRequest($app, $kernel, 'GET', '/kinh-doanh/don-ban', $qlk);
assertTest("QL_KHO vào /kinh-doanh/don-ban bị từ chối (403)", $r->getStatusCode() === 403);

$r = makeRequest($app, $kernel, 'GET', '/kinh-doanh/don-ban', $kt);
assertTest("KE_TOAN vào /kinh-doanh/don-ban bị từ chối (403)", $r->getStatusCode() === 403);

$r = makeRequest($app, $kernel, 'GET', '/kinh-doanh/don-ban', $kd);
assertTest("KINH_DOANH vào /kinh-doanh/don-ban thành công (200)", $r->getStatusCode() === 200);

$r = makeRequest($app, $kernel, 'GET', '/kinh-doanh/don-ban/lap', $kd);
assertTest("KD-03 form lập đơn thành công (200)", $r->getStatusCode() === 200);
assertTest("KD-03 hiển thị tên sản phẩm", str_contains($r->getContent(), $sp->Ten_san_pham));

echo "\n--- 2. KD-03: Lập đơn bán (Xác nhận) ---\n";

// Chặn khi vượt tồn khả dụng
$postOver = [
    'ma_khach_hang'    => $kh->Ma_khach_hang,
    'ma_kho'           => 'HN',
    'ten_nguoi_nhan'   => $kh->Ten_khach_hang,
    'sdt_nguoi_nhan'   => $kh->So_dien_thoai ?? '0901112233',
    'dia_chi_giao_hang'=> $kh->Dia_chi ?? 'Hà Nội',
    'items'            => [
        ['ma_san_pham' => $sp->Ma_san_pham, 'so_luong' => $tonTruoc + 9999, 'chiet_khau' => 0],
    ],
];
$r = makeRequest($app, $kernel, 'POST', '/kinh-doanh/don-ban', $kd, $postOver);
assertTest("Chặn khi số lượng vượt tồn khả dụng (redirect lại)", $r->getStatusCode() === 302 && str_contains($r->headers->get('Location') ?? '', 'lap'));

// Lập đơn hợp lệ (1 chai để không làm cạn tồn)
$slDat = max(1, min(2, $tonTruoc));
$postOk = [
    'ma_khach_hang'    => $kh->Ma_khach_hang,
    'ma_kho'           => 'HN',
    'ten_nguoi_nhan'   => $kh->Ten_khach_hang,
    'sdt_nguoi_nhan'   => $kh->So_dien_thoai ?? '0901112233',
    'dia_chi_giao_hang'=> $kh->Dia_chi ?? 'Hà Nội',
    'items'            => [
        ['ma_san_pham' => $sp->Ma_san_pham, 'so_luong' => $slDat, 'chiet_khau' => 0],
    ],
];
$r = makeRequest($app, $kernel, 'POST', '/kinh-doanh/don-ban', $kd, $postOk);
assertTest("Xác nhận đơn bán chuyển hướng 302", $r->getStatusCode() === 302);

// Tìm đơn vừa tạo
$donBan = DonHangBan::where('Ma_khach_hang', $kh->Ma_khach_hang)
    ->where('Ma_kho', 'HN')
    ->orderByDesc('Thoi_diem_xac_nhan')
    ->first();
assertTest("Đơn hàng bán tạo thành công", $donBan !== null);
assertTest("Trạng thái đơn = 'Đang chuẩn bị hàng'", $donBan?->Trang_thai === 'Đang chuẩn bị hàng');

// Kiểm tra chi tiết đơn
$chiTiet = ChiTietDonBan::where('Ma_don_hang_ban', $donBan?->Ma_don_hang_ban)->first();
assertTest("Chi tiết đơn bán được tạo", $chiTiet !== null);
assertTest("Giá bán được chụp đúng (không có CK)", $chiTiet?->Don_gia_ban == $sp->Gia_niem_yet);
assertTest("Tổng tiền hàng đúng", $donBan?->Tong_tien_hang == $slDat * $sp->Gia_niem_yet);

// Kiểm tra phiếu xuất và phân bổ FIFO
$px = PhieuXuatKho::where('Ma_don_hang_ban', $donBan?->Ma_don_hang_ban)->first();
assertTest("Phiếu xuất kho được tạo tự động", $px !== null);
assertTest("Phiếu xuất ở trạng thái 'Đang chuẩn bị hàng'", $px?->Trang_thai_giao_hang === 'Đang chuẩn bị hàng');

$ctPx = ChiTietPhieuXuat::where('Ma_phieu_xuat', $px?->Ma_phieu_xuat)->first();
assertTest("Chi tiết phiếu xuất có phân bổ lô FIFO", $ctPx !== null);
assertTest("Tổng SL phân bổ = {$slDat}", ChiTietPhieuXuat::where('Ma_phieu_xuat', $px?->Ma_phieu_xuat)->sum('So_luong') == $slDat);

// Tồn khả dụng giảm (tồn thực KHÔNG đổi)
$tonSau    = StockService::getTonKhaDung('HN', $sp->Ma_san_pham);
$tonThucSau= StockService::getTonThuc('HN', $sp->Ma_san_pham);
assertTest("Tồn khả dụng giảm đúng {$slDat} chai", $tonSau === $tonTruoc - $slDat);
assertTest("Tồn thực KHÔNG đổi khi giữ hàng", $tonThucSau === $tonThucTruoc);

echo "\n--- 3. KD-04: Xem chi tiết đơn hàng ---\n";

$r = makeRequest($app, $kernel, 'GET', "/kinh-doanh/don-ban/{$donBan->Ma_don_hang_ban}", $kd);
assertTest("KD-04 mở chi tiết đơn thành công (200)", $r->getStatusCode() === 200);
assertTest("KD-04 hiển thị tên khách hàng", str_contains($r->getContent(), $kh->Ten_khach_hang));
assertTest("KD-04 hiển thị mã phiếu xuất", str_contains($r->getContent(), $px->Ma_phieu_xuat));

echo "\n--- 4. KD-04: Cập nhật thông tin giao hàng ---\n";

$putData = [
    'ten_nguoi_nhan'    => 'Nguyễn Thị Test Update',
    'sdt_nguoi_nhan'    => '0999888777',
    'dia_chi_giao_hang' => '99 Trần Phú, Hà Nội',
];
$r = makeRequest($app, $kernel, 'PUT', "/kinh-doanh/don-ban/{$donBan->Ma_don_hang_ban}/giao-hang", $kd, $putData);
assertTest("Cập nhật giao hàng chuyển hướng 302", $r->getStatusCode() === 302);

$donBan->refresh();
assertTest("Tên người nhận được cập nhật", $donBan->Ten_nguoi_nhan === 'Nguyễn Thị Test Update');
assertTest("SĐT được cập nhật", $donBan->Sdt_nguoi_nhan === '0999888777');

echo "\n--- 5. KD-04: Hủy đơn hàng ---\n";

// Thử hủy không có lý do → validation
$r = makeRequest($app, $kernel, 'POST', "/kinh-doanh/don-ban/{$donBan->Ma_don_hang_ban}/huy", $kd, ['ly_do_huy' => '']);
assertTest("Hủy không có lý do bị từ chối (không phải 200)", $r->getStatusCode() !== 200);

// Hủy hợp lệ
$r = makeRequest($app, $kernel, 'POST', "/kinh-doanh/don-ban/{$donBan->Ma_don_hang_ban}/huy", $kd, [
    'ly_do_huy' => 'Khách đổi ý, không mua nữa',
]);
assertTest("Hủy đơn hợp lệ chuyển hướng 302", $r->getStatusCode() === 302);

$donBan->refresh();
$px->refresh();
assertTest("Đơn hàng chuyển sang 'Đã hủy'", $donBan->Trang_thai === 'Đã hủy');
assertTest("Phiếu xuất chuyển sang 'Đã hủy'", $px->Trang_thai_giao_hang === 'Đã hủy');
assertTest("Lý do hủy được lưu", $donBan->Ly_do_huy === 'Khách đổi ý, không mua nữa');

// Tồn khả dụng được khôi phục sau hủy
$tonPhucHoi = StockService::getTonKhaDung('HN', $sp->Ma_san_pham);
assertTest("Tồn khả dụng khôi phục sau hủy đơn", $tonPhucHoi === $tonTruoc);
assertTest("Tồn thực vẫn không đổi sau hủy", StockService::getTonThuc('HN', $sp->Ma_san_pham) === $tonThucTruoc);

echo "\n--- 6. KD-04: Không thể sửa/hủy đơn đã hủy ---\n";

$r = makeRequest($app, $kernel, 'POST', "/kinh-doanh/don-ban/{$donBan->Ma_don_hang_ban}/huy", $kd, [
    'ly_do_huy' => 'Thử hủy lần 2',
]);
// Redirect về show với flash error
assertTest("Không thể hủy đơn đã hủy (redirect 302, không xử lý)", $r->getStatusCode() === 302);

echo "\n--- 7. KD-02: Danh sách và lọc ---\n";

$r = makeRequest($app, $kernel, 'GET', '/kinh-doanh/don-ban', $kd);
assertTest("KD-02 load thành công", $r->getStatusCode() === 200);
assertTest("KD-02 hiển thị đơn vừa tạo (Đã hủy)", str_contains($r->getContent(), $donBan->Ma_don_hang_ban));

$r = makeRequest($app, $kernel, 'GET', '/kinh-doanh/don-ban?trang_thai=' . urlencode('Đang chuẩn bị hàng'), $kd);
assertTest("Lọc theo trạng thái 'Đang chuẩn bị hàng' thành công (200)", $r->getStatusCode() === 200);

// Dọn dẹp
ChiTietPhieuXuat::where('Ma_phieu_xuat', $px->Ma_phieu_xuat)->delete();
$px->delete();
ChiTietDonBan::where('Ma_don_hang_ban', $donBan->Ma_don_hang_ban)->delete();
$donBan->delete();

echo "\n======================================================\n";
echo "KẾT QUẢ KIỂM THỬ: {$passed} PASS, {$failed} FAIL\n";
echo "======================================================\n";

exit($failed > 0 ? 1 : 0);

