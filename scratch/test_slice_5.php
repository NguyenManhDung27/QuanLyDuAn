<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\DonDatHang;
use App\Models\ChiTietDonDatHang;
use App\Models\NguoiDung;
use App\Models\NhaCungCap;
use App\Models\SanPham;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

echo "=== KIỂM THỬ TỰ ĐỘNG SLICE 5: MUA HÀNG – ĐỀ XUẤT & ĐƠN ĐẶT HÀNG ===\n\n";

// --- Chuẩn bị tài khoản ---
$qlkHn = NguoiDung::where('Ten_dang_nhap', 'qlkho.hn')->first();
$kt    = NguoiDung::where('Ten_dang_nhap', 'ketoan01')->first();
$nvk   = NguoiDung::where('Ten_dang_nhap', 'nvkho.hn')->first();
$kd    = NguoiDung::where('Ten_dang_nhap', 'kinhdoanh01')->first();
$ncc   = NhaCungCap::where('Trang_thai', 'Đang giao dịch')->first();
$sps   = SanPham::where('Trang_thai', 'Đang kinh doanh')->take(2)->pluck('Ma_san_pham')->all();

assertTest("Tải đủ 4 tài khoản + NCC + 2 SP", $qlkHn && $kt && $nvk && $kd && $ncc && count($sps) >= 2);

// Dọn test cũ (chỉ theo kho HN + qlkHn)
// Không dọn trước vì có FK — sẽ dọn sau bằng mã cụ thể

$maDonBefore = DonDatHang::max('Ma_don_dat_hang');

// =============================================================
echo "\n--- 1. Kiểm tra phân quyền truy cập ---\n";
// =============================================================

$r = makeRequest($app, $kernel, 'GET', '/kho/de-xuat', $nvk);
assertTest("NV_KHO /kho/de-xuat → 403", $r->getStatusCode() === 403);

$r = makeRequest($app, $kernel, 'GET', '/kho/de-xuat', $kd);
assertTest("KINH_DOANH /kho/de-xuat → 403", $r->getStatusCode() === 403);

$r = makeRequest($app, $kernel, 'GET', '/ke-toan/de-xuat', $qlkHn);
assertTest("QL_KHO /ke-toan/de-xuat → 403", $r->getStatusCode() === 403);

$r = makeRequest($app, $kernel, 'GET', '/ke-toan/de-xuat', $kt);
assertTest("KE_TOAN /ke-toan/de-xuat → 200", $r->getStatusCode() === 200);

$r = makeRequest($app, $kernel, 'GET', '/kho/de-xuat', $qlkHn);
assertTest("QL_KHO /kho/de-xuat → 200", $r->getStatusCode() === 200);

// =============================================================
echo "\n--- 2. QL_KHO tạo đề xuất nhập kho (Draft) ---\n";
// =============================================================

$postData = [
    'Ma_ncc'               => $ncc->Ma_ncc,
    'Ngay_du_kien_nhan_du' => date('Y-m-d', strtotime('+14 days')),
    'action'               => 'save',   // lưu nháp
    'items' => [
        ['Ma_san_pham' => $sps[0], 'So_luong_dat' => 120, 'Don_gia_mua' => 200000],
        ['Ma_san_pham' => $sps[1], 'So_luong_dat' => 60,  'Don_gia_mua' => 300000],
    ]
];

$resCreate = makeRequest($app, $kernel, 'POST', '/kho/de-xuat/tao', $qlkHn, $postData);
assertTest("POST /kho/de-xuat/tao → redirect 302", $resCreate->getStatusCode() === 302);

// Tìm DDH vừa tạo (Max mã sau khi POST)
$ddh = DonDatHang::where('Ma_nguoi_lap', $qlkHn->Ma_nguoi_dung)
    ->where('Ma_kho', 'HN')
    ->where('Trang_thai', 'Đang cập nhật')
    ->orderByDesc('Ma_don_dat_hang')
    ->first();

assertTest("Đề xuất tồn tại trong DB với mã DDH-*", $ddh && str_starts_with($ddh->Ma_don_dat_hang, 'DDH-'));
assertTest("Trạng thái ban đầu = 'Đang cập nhật'", $ddh?->Trang_thai === 'Đang cập nhật');
assertTest("Kho gắn với user QL_KHO (HN)", $ddh?->Ma_kho === 'HN');
assertTest("Tổng = 120×200k + 60×300k = 42.000.000", $ddh && (int)$ddh->Tong_gia_tri === 42000000);

$maDon = $ddh?->Ma_don_dat_hang;

// =============================================================
echo "\n--- 3. Kế toán không thấy bản nháp (KT-02) ---\n";
// =============================================================

$resKt = makeRequest($app, $kernel, 'GET', '/ke-toan/de-xuat', $kt);
assertTest("KT-02 → 200", $resKt->getStatusCode() === 200);
// KT-02 ẩn bản nháp: check response không có mã trong <td> (table row), không phải URL
assertTest("KT-02 ẩn bản nháp 'Đang cập nhật'", !str_contains($resKt->getContent(), ">{$maDon}<"));

// =============================================================
echo "\n--- 4. QL_KHO gửi duyệt → Chờ phê duyệt ---\n";
// =============================================================

$r = makeRequest($app, $kernel, 'POST', "/kho/de-xuat/{$maDon}/gui-duyet", $qlkHn);
assertTest("POST /gui-duyet → redirect 302", $r->getStatusCode() === 302);
$ddh->refresh();
assertTest("Trạng thái = 'Chờ phê duyệt'", $ddh->Trang_thai === 'Chờ phê duyệt');

// KT-02 giờ hiển thị
$resKt2 = makeRequest($app, $kernel, 'GET', '/ke-toan/de-xuat', $kt);
assertTest("KT-02 hiển thị đề xuất sau khi gửi duyệt", str_contains($resKt2->getContent(), $maDon));

// =============================================================
echo "\n--- 5. QL_KHO thu hồi đề xuất → Đang cập nhật ---\n";
// =============================================================

$r = makeRequest($app, $kernel, 'POST', "/kho/de-xuat/{$maDon}/thu-hoi", $qlkHn);
assertTest("POST /thu-hoi → redirect 302", $r->getStatusCode() === 302);
$ddh->refresh();
assertTest("Trạng thái = 'Đang cập nhật' sau thu hồi", $ddh->Trang_thai === 'Đang cập nhật');

// Gửi duyệt lại để tiếp tục flow
makeRequest($app, $kernel, 'POST', "/kho/de-xuat/{$maDon}/gui-duyet", $qlkHn);
$ddh->refresh();
assertTest("Gửi lại thành công → 'Chờ phê duyệt'", $ddh->Trang_thai === 'Chờ phê duyệt');

// =============================================================
echo "\n--- 6. Kế toán xem chi tiết và Từ chối (KT-03) ---\n";
// =============================================================

$r = makeRequest($app, $kernel, 'GET', "/ke-toan/de-xuat/{$maDon}", $kt);
assertTest("GET /ke-toan/de-xuat/{ma} → 200", $r->getStatusCode() === 200);

// Từ chối thiếu lý do → validation error (redirect 302, không phải 500)
$r = makeRequest($app, $kernel, 'POST', "/ke-toan/de-xuat/{$maDon}/tu-choi", $kt, ['ly_do_tu_choi' => '']);
assertTest("Từ chối không lý do → validation (không phải 200/500)", in_array($r->getStatusCode(), [302, 422]));

// Từ chối có lý do
$r = makeRequest($app, $kernel, 'POST', "/ke-toan/de-xuat/{$maDon}/tu-choi", $kt, [
    'ly_do_tu_choi' => 'Giá mua quá cao so với hợp đồng khung.'
]);
assertTest("Từ chối thành công → 302", $r->getStatusCode() === 302);
$ddh->refresh();
assertTest("Trạng thái = 'Từ chối'", $ddh->Trang_thai === 'Từ chối');
assertTest("Lý do từ chối được lưu", $ddh->Ly_do_tu_choi === 'Giá mua quá cao so với hợp đồng khung.');
assertTest("Người duyệt = kế toán", $ddh->Ma_nguoi_duyet === $kt->Ma_nguoi_dung);

// =============================================================
echo "\n--- 7. QL_KHO gửi lại đề xuất (Từ chối → Chờ phê duyệt) ---\n";
// =============================================================

// Cập nhật giá rồi gửi lại với action=send trong PUT
$editData = [
    'Ma_ncc'               => $ncc->Ma_ncc,
    'Ngay_du_kien_nhan_du' => date('Y-m-d', strtotime('+14 days')),
    'action'               => 'send',
    'items' => [
        ['Ma_san_pham' => $sps[0], 'So_luong_dat' => 120, 'Don_gia_mua' => 190000],
        ['Ma_san_pham' => $sps[1], 'So_luong_dat' => 60,  'Don_gia_mua' => 290000],
    ]
];
$r = makeRequest($app, $kernel, 'PUT', "/kho/de-xuat/{$maDon}", $qlkHn, $editData);
assertTest("PUT /kho/de-xuat/{ma} gửi lại → 302", $r->getStatusCode() === 302);
$ddh->refresh();
assertTest("Trạng thái = 'Chờ phê duyệt' sau gửi lại", $ddh->Trang_thai === 'Chờ phê duyệt');
assertTest("Tổng tiền cập nhật = 120×190k + 60×290k = 40.200.000", (int)$ddh->Tong_gia_tri === 40200000);

// =============================================================
echo "\n--- 8. Kế toán phê duyệt đề xuất ---\n";
// =============================================================

$r = makeRequest($app, $kernel, 'POST', "/ke-toan/de-xuat/{$maDon}/duyet", $kt, [
    'ghi_chu_phe_duyet' => 'Đồng ý giá mới theo thương lượng.'
]);
assertTest("POST /duyet → 302", $r->getStatusCode() === 302);
$ddh->refresh();
assertTest("Trạng thái = 'Chưa gửi NCC'", $ddh->Trang_thai === 'Chưa gửi NCC');
assertTest("Ghi chú phê duyệt được lưu", $ddh->Ghi_chu_phe_duyet === 'Đồng ý giá mới theo thương lượng.');
assertTest("Người duyệt & thời điểm duyệt ghi nhận", $ddh->Ma_nguoi_duyet === $kt->Ma_nguoi_dung && !empty($ddh->Thoi_diem_duyet));

// KE_TOAN không thể gửi NCC
$r = makeRequest($app, $kernel, 'POST', "/kho/don-dat-hang/{$maDon}/xac-nhan-gui-ncc", $kt);
assertTest("KE_TOAN không thể gửi NCC → 403", $r->getStatusCode() === 403);

// =============================================================
echo "\n--- 9. QL_KHO xác nhận gửi NCC → Đã gửi NCC ---\n";
// =============================================================

$r = makeRequest($app, $kernel, 'POST', "/kho/don-dat-hang/{$maDon}/xac-nhan-gui-ncc", $qlkHn);
assertTest("POST /xac-nhan-gui-ncc → 302", $r->getStatusCode() === 302);
$ddh->refresh();
assertTest("Trạng thái = 'Đã gửi NCC'", $ddh->Trang_thai === 'Đã gửi NCC');
assertTest("Ngày gửi NCC được đóng dấu", !empty($ddh->Ngay_gui_ncc));

// =============================================================
echo "\n--- 10. Cập nhật ngày dự kiến nhận đủ ---\n";
// =============================================================

$newDate = date('Y-m-d', strtotime('+21 days'));
$r = makeRequest($app, $kernel, 'POST', "/kho/don-dat-hang/{$maDon}/ngay-du-kien", $qlkHn, [
    'ngay_du_kien_nhan_du' => $newDate
]);
assertTest("POST /ngay-du-kien → 302", $r->getStatusCode() === 302);
$ddh->refresh();
assertTest("Ngày dự kiến nhận đủ được cập nhật", $ddh->Ngay_du_kien_nhan_du?->toDateString() === $newDate);

// =============================================================
echo "\n--- 11. Đóng thiếu đơn đặt hàng ---\n";
// =============================================================

$r = makeRequest($app, $kernel, 'POST', "/kho/don-dat-hang/{$maDon}/dong-thieu", $qlkHn, [
    'ly_do_dong_thieu' => 'NCC báo hết hàng SP2, đồng ý kết thúc đơn.'
]);
assertTest("POST /dong-thieu → 302", $r->getStatusCode() === 302);
$ddh->refresh();
assertTest("Trạng thái = 'Đóng thiếu'", $ddh->Trang_thai === 'Đóng thiếu');

// =============================================================
echo "\n--- 12. Nhật ký hoạt động ---\n";
// =============================================================

$logCount = Activity::where('description', 'like', "%{$maDon}%")
    ->orWhere('properties->subject_id', $maDon)
    ->count();
$logCount = $logCount ?: Activity::where('log_name', 'like', "%{$maDon}%")->count();
// Fallback: đếm qua event text search
if ($logCount === 0) {
    $logCount = DB::table('activity_log')->where('description', 'like', "%{$maDon}%")->count();
}
assertTest("Có ít nhất 5 bản ghi nhật ký ({$logCount} ghi nhận)", $logCount >= 5);

// =============================================================
// Dọn dẹp
ChiTietDonDatHang::where('Ma_don_dat_hang', $maDon)->delete();
DonDatHang::where('Ma_don_dat_hang', $maDon)->delete();
DB::table('activity_log')->where('description', 'like', "%{$maDon}%")->delete();

echo "\n======================================================\n";
echo "KẾT QUẢ: {$passed} PASS, {$failed} FAIL\n";
echo "======================================================\n";

exit($failed > 0 ? 1 : 0);
