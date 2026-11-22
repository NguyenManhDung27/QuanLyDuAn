<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\ChiTietKiemKe;
use App\Models\Lo;
use App\Models\NguoiDung;
use App\Models\PhieuKiemKe;
use App\Models\TonKho;
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

function assertTest(string $desc, bool $cond): void {
    global $passed, $failed;
    if ($cond) {
        echo "  [PASS] {$desc}\n";
        $passed++;
    } else {
        echo "  [FAIL] {$desc}\n";
        $failed++;
    }
}

function makeRequest($app, $kernel, string $method, string $uri, ?NguoiDung $user, array $data = []) {
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

echo "=== KIỂM THỬ TỰ ĐỘNG SLICE 9: KIỂM KÊ KHO (QLK-06..08, NVK-09, NVK-10) ===\n\n";

// Tài khoản
$qlk = NguoiDung::where('Ten_dang_nhap', 'qlkho.hn')->first();
$nvk = NguoiDung::where('Ten_dang_nhap', 'nvkho.hn')->first();
$kt  = NguoiDung::where('Ten_dang_nhap', 'ketoan01')->first();
$kd  = NguoiDung::where('Ten_dang_nhap', 'kinhdoanh01')->first();

if (!$qlk || !$nvk) {
    echo "[FATAL] Không tìm thấy tài khoản test. Dừng.\n";
    exit(1);
}

$maKho = $qlk->Ma_kho ?? 'KHO_HN';

// Dọn dẹp các phiếu kiểm kê dở dang từ các lần chạy trước để mở khóa các lô
ChiTietKiemKe::whereIn('Ma_phieu_kiem_ke', function ($q) {
    $q->select('Ma_phieu_kiem_ke')->from('Phieu_kiem_ke');
})->delete();
PhieuKiemKe::truncate();

// Lấy lô có tồn > 0 để test
$tonKhoCo = TonKho::where('Ma_kho', $maKho)->where('Ton_thuc', '>', 0)->get();

if ($tonKhoCo->isEmpty()) {
    echo "[FATAL] Không có tồn kho để test. Chạy test slice trước.\n";
    exit(1);
}

// ===================================================================
echo "--- NHÓM 1: RBAC ---\n";
// ===================================================================

// QLK-06: QL_KHO → 200
$resp = makeRequest($app, $kernel, 'GET', '/kho/kiem-ke', $qlk);
assertTest('QLK-06: QL_KHO GET /kho/kiem-ke → 200', $resp->getStatusCode() === 200);

// NV_KHO → redirect (403 hoặc redirect)
$resp = makeRequest($app, $kernel, 'GET', '/kho/kiem-ke', $nvk);
assertTest('QLK-06: NV_KHO GET /kho/kiem-ke → 403/redirect', in_array($resp->getStatusCode(), [302, 403]));

// NVK-09: NV_KHO → 200
$resp = makeRequest($app, $kernel, 'GET', '/nv-kho/kiem-ke', $nvk);
assertTest('NVK-09: NV_KHO GET /nv-kho/kiem-ke → 200', $resp->getStatusCode() === 200);

// QL_KHO → redirect/403
$resp = makeRequest($app, $kernel, 'GET', '/nv-kho/kiem-ke', $qlk);
assertTest('NVK-09: QL_KHO GET /nv-kho/kiem-ke → 403/redirect', in_array($resp->getStatusCode(), [302, 403]));

// KE_TOAN không vào cả hai
if ($kt) {
    $resp = makeRequest($app, $kernel, 'GET', '/kho/kiem-ke', $kt);
    assertTest('RBAC: KE_TOAN không vào /kho/kiem-ke', in_array($resp->getStatusCode(), [302, 403]));
}

// ===================================================================
echo "\n--- NHÓM 2: TRANG TẠO PHIẾU (QLK-07 GET) ---\n";
// ===================================================================

$resp = makeRequest($app, $kernel, 'GET', '/kho/kiem-ke/tao', $qlk);
assertTest('QLK-07: GET /kho/kiem-ke/tao → 200', $resp->getStatusCode() === 200);
assertTest('QLK-07: Trang tạo chứa form checkbox lô', str_contains($resp->getContent(), 'ma_los'));

// ===================================================================
echo "\n--- NHÓM 3: TẠO PHIẾU KIỂM KÊ (QLK-07 POST) ---\n";
// ===================================================================

// Lấy 2 lô đầu tiên có tồn > 0
$dsMaLo = $tonKhoCo->take(2)->pluck('Ma_lo')->toArray();

if (empty($dsMaLo)) {
    echo "  [SKIP] Không có lô để tạo phiếu kiểm kê\n";
} else {
    $countBefore = PhieuKiemKe::count();
    $resp = makeRequest($app, $kernel, 'POST', '/kho/kiem-ke', $qlk, [
        'ma_los'       => $dsMaLo,
        'ma_nguoi_kiem'=> $nvk->Ma_nguoi_dung,
        'ghi_chu'      => 'Test slice 9',
    ]);
    assertTest('QLK-07: POST tạo phiếu → redirect (302)', $resp->getStatusCode() === 302);
    assertTest('QLK-07: Phiếu mới được tạo trong DB', PhieuKiemKe::count() > $countBefore);

    // Lấy phiếu vừa tạo
    $pkk = PhieuKiemKe::with(['chiTiets'])->orderBy('Ma_phieu_kiem_ke', 'desc')->first();
    assertTest('QLK-07: Phiếu có trạng thái Đang kiểm', $pkk && $pkk->Trang_thai === 'Đang kiểm');
    assertTest('QLK-07: Phiếu giao đúng NV_KHO', $pkk && $pkk->Ma_nguoi_kiem == $nvk->Ma_nguoi_dung);
    assertTest('QLK-07: Số chi tiết = số lô được chọn', $pkk && $pkk->chiTiets->count() === count($dsMaLo));

    // Kiểm tra So_luong_he_thong được snapshot đúng
    $firstCt = $pkk->chiTiets->first();
    $tonKhoRecord = TonKho::where('Ma_lo', $firstCt->Ma_lo)->where('Ma_kho', $maKho)->first();
    assertTest('QLK-07: So_luong_he_thong snapshot đúng tồn kho', 
        $firstCt && $tonKhoRecord && (int)$firstCt->So_luong_he_thong === (int)$tonKhoRecord->Ton_thuc);

    $maPKK = $pkk->Ma_phieu_kiem_ke;

    // ===================================================================
    echo "\n--- NHÓM 4: KHÓA LÔ KHI ĐANG KIỂM ---\n";
    // ===================================================================

    $lockedLoMap = StockService::getLoDangKiemKeMap($maKho);
    assertTest('Locking: Các lô trong phiếu bị khóa', !empty($lockedLoMap));
    foreach ($dsMaLo as $ml) {
        assertTest("Locking: Lô {$ml} bị đánh dấu khóa", isset($lockedLoMap[$ml]));
    }

    // ===================================================================
    echo "\n--- NHÓM 5: QLK-08 XEM CHI TIẾT ---\n";
    // ===================================================================

    $resp = makeRequest($app, $kernel, 'GET', "/kho/kiem-ke/{$maPKK}", $qlk);
    assertTest("QLK-08: GET /kho/kiem-ke/{$maPKK} → 200", $resp->getStatusCode() === 200);
    assertTest('QLK-08: Trang show chứa mã phiếu', str_contains($resp->getContent(), $maPKK));

    // ===================================================================
    echo "\n--- NHÓM 6: NVK-09 DANH SÁCH (NV_KHO) ---\n";
    // ===================================================================

    $resp = makeRequest($app, $kernel, 'GET', '/nv-kho/kiem-ke', $nvk);
    assertTest('NVK-09: GET /nv-kho/kiem-ke → 200', $resp->getStatusCode() === 200);
    assertTest('NVK-09: Phiếu giao cho NV_KHO xuất hiện trong danh sách', 
        str_contains($resp->getContent(), $maPKK));

    // NVK khác không thấy phiếu giao cho nvkho.hn
    $nvk2 = NguoiDung::where('Ma_kho', $maKho)
        ->where('Ma_vai_tro', 'NV_KHO')
        ->where('Ma_nguoi_dung', '!=', $nvk->Ma_nguoi_dung)
        ->first();
    if ($nvk2) {
        $resp2 = makeRequest($app, $kernel, 'GET', '/nv-kho/kiem-ke', $nvk2);
        assertTest('NVK-09: NVK khác không thấy phiếu đã giao cho NVK cụ thể',
            !str_contains($resp2->getContent(), $maPKK));
    }

    // ===================================================================
    echo "\n--- NHÓM 7: NVK-10 MÀN HÌNH ĐẾM ---\n";
    // ===================================================================

    $resp = makeRequest($app, $kernel, 'GET', "/nv-kho/kiem-ke/{$maPKK}/dem", $nvk);
    assertTest("NVK-10: GET /nv-kho/kiem-ke/{$maPKK}/dem → 200", $resp->getStatusCode() === 200);
    assertTest('NVK-10: Trang đếm chứa input thực tế', str_contains($resp->getContent(), 'So_luong_thuc_te'));

    // ===================================================================
    echo "\n--- NHÓM 8: NVK-10 LƯU TẠM ---\n";
    // ===================================================================

    // Nhập số cho tất cả lô, khớp hết (không lệch)
    $items = [];
    foreach ($pkk->chiTiets as $ct) {
        $items[$ct->Ma_lo] = ['So_luong_thuc_te' => (string)$ct->So_luong_he_thong, 'Ly_do_chenh_lech' => ''];
    }

    $resp = makeRequest($app, $kernel, 'POST', "/nv-kho/kiem-ke/{$maPKK}/dem", $nvk, [
        'action' => 'luu_tam',
        'items'  => $items,
    ]);
    assertTest('NVK-10 Lưu tạm → redirect', $resp->getStatusCode() === 302);

    $pkk->refresh();
    assertTest('NVK-10 Lưu tạm: Phiếu vẫn ở Đang kiểm', $pkk->Trang_thai === 'Đang kiểm');

    // Kiểm tra số đếm được lưu
    $ct = $pkk->chiTiets->first();
    $ct->refresh();
    assertTest('NVK-10 Lưu tạm: So_luong_thuc_te được lưu', $ct->So_luong_thuc_te !== null);

    // ===================================================================
    echo "\n--- NHÓM 9: NVK-10 HOÀN THÀNH ĐẾM (không lệch → Hoàn tất) ---\n";
    // ===================================================================

    $resp = makeRequest($app, $kernel, 'POST', "/nv-kho/kiem-ke/{$maPKK}/dem", $nvk, [
        'action' => 'hoan_thanh',
        'items'  => $items, // Tất cả khớp
    ]);
    assertTest('NVK-10 Hoàn thành (0 lệch) → redirect', $resp->getStatusCode() === 302);

    $pkk->refresh();
    assertTest('NVK-10 Hoàn thành 0 lệch → Phiếu tự chuyển Hoàn tất', $pkk->Trang_thai === 'Hoàn tất');

    // Kiểm tra lô được mở khóa
    $lockedAfter = StockService::getLoDangKiemKeMap($maKho);
    foreach ($dsMaLo as $ml) {
        assertTest("Locking: Lô {$ml} được mở khóa sau Hoàn tất", !isset($lockedAfter[$ml]));
    }

    // Phiếu Hoàn tất → không có nút duyệt/hủy khi QL_KHO xem
    $resp = makeRequest($app, $kernel, 'GET', "/kho/kiem-ke/{$maPKK}", $qlk);
    assertTest('QLK-08: Phiếu Hoàn tất không có nút Duyệt', 
        !str_contains($resp->getContent(), 'Duyệt và điều chỉnh tồn'));

    // ===================================================================
    echo "\n--- NHÓM 10: TẠO PHIẾU THỨ 2 & NVK-10 VALIDATION ---\n";
    // ===================================================================

    // Cần có lô mới (phiếu trước đã Hoàn tất nên lô không còn locked)
    $tonKhoCo2 = TonKho::where('Ma_kho', $maKho)->where('Ton_thuc', '>', 0)->get();
    $dsMaLo2 = $tonKhoCo2->take(1)->pluck('Ma_lo')->toArray();

    if (!empty($dsMaLo2)) {
        // Tạo phiếu thứ 2
        makeRequest($app, $kernel, 'POST', '/kho/kiem-ke', $qlk, [
            'ma_los'       => $dsMaLo2,
            'ma_nguoi_kiem'=> $nvk->Ma_nguoi_dung,
            'ghi_chu'      => 'Test slice 9 - phiếu có lệch',
        ]);
        $pkk2 = PhieuKiemKe::with(['chiTiets'])->orderBy('Ma_phieu_kiem_ke', 'desc')->first();
        $maPKK2 = $pkk2->Ma_phieu_kiem_ke;

        // --- NHÓM 11: NVK-10 VALIDATION (trên phiếu 2 đang ở Đang kiểm) ---
        echo "\n--- NHÓM 11: NVK-10 VALIDATION ---\n";
        $items2bad = [];
        foreach ($pkk2->chiTiets as $ct2) {
            $items2bad[$ct2->Ma_lo] = [
                'So_luong_thuc_te' => (string)((int)$ct2->So_luong_he_thong - 1),
                'Ly_do_chenh_lech' => '', // Thiếu lý do khi có lệch
            ];
        }
        $respBad = makeRequest($app, $kernel, 'POST', "/nv-kho/kiem-ke/{$maPKK2}/dem", $nvk, [
            'action' => 'hoan_thanh',
            'items'  => $items2bad,
        ]);
        assertTest('NVK-10 Validation: Lệch không có lý do → redirect với lỗi (302)', $respBad->getStatusCode() === 302);
        $pkk2->refresh();
        assertTest('NVK-10 Validation: Phiếu vẫn Đang kiểm sau lỗi validation', $pkk2->Trang_thai === 'Đang kiểm');

        // Hoàn thành đếm có lệch và CÓ lý do → chuyển Chờ duyệt
        $items2 = [];
        foreach ($pkk2->chiTiets as $ct2) {
            $lech = max(0, (int)$ct2->So_luong_he_thong - 1); // Thiếu 1
            $items2[$ct2->Ma_lo] = [
                'So_luong_thuc_te' => (string)$lech,
                'Ly_do_chenh_lech' => 'Test: Mất 1 chai',
            ];
        }

        $resp = makeRequest($app, $kernel, 'POST', "/nv-kho/kiem-ke/{$maPKK2}/dem", $nvk, [
            'action' => 'hoan_thanh',
            'items'  => $items2,
        ]);
        assertTest('NVK-10 Hoàn thành có lệch → redirect', $resp->getStatusCode() === 302);

        $pkk2->refresh();
        assertTest('NVK-10 Hoàn thành có lệch → Chờ duyệt', $pkk2->Trang_thai === 'Chờ duyệt');

        // Lô vẫn locked khi Chờ duyệt
        $lockedPKK2 = StockService::getLoDangKiemKeMap($maKho);
        foreach ($dsMaLo2 as $ml) {
            assertTest("Locking: Lô {$ml} vẫn locked khi Chờ duyệt", isset($lockedPKK2[$ml]));
        }

        // ===================================================================
        echo "\n--- NHÓM 12: QLK-08 KHÔNG DUYỆT ---\n";
        // ===================================================================

        // 12a. Không duyệt thiếu lý do → lỗi
        $respBadKd = makeRequest($app, $kernel, 'POST', "/kho/kiem-ke/{$maPKK2}/khong-duyet", $qlk, [
            'ly_do_khong_duyet' => '',
        ]);
        assertTest('QLK-08 Không duyệt thiếu lý do → redirect lỗi (302/422)', in_array($respBadKd->getStatusCode(), [302, 422]));
        $pkk2->refresh();
        assertTest('QLK-08 Không duyệt thiếu lý do: Phiếu giữ nguyên Chờ duyệt', $pkk2->Trang_thai === 'Chờ duyệt');

        // 12b. Không duyệt hợp lệ → chuyển về Đang kiểm
        $resp = makeRequest($app, $kernel, 'POST', "/kho/kiem-ke/{$maPKK2}/khong-duyet", $qlk, [
            'ly_do_khong_duyet' => 'Test: Đếm lại',
        ]);
        assertTest('QLK-08 Không duyệt → redirect', $resp->getStatusCode() === 302);

        $pkk2->refresh();
        assertTest('QLK-08 Không duyệt: Phiếu → Đang kiểm', $pkk2->Trang_thai === 'Đang kiểm');
        assertTest('QLK-08 Không duyệt: Ly_do_khong_duyet được lưu', !empty($pkk2->Ly_do_khong_duyet));
        assertTest('QLK-08 Không duyệt: Thoi_diem_dem_xong bị xóa', empty($pkk2->Thoi_diem_dem_xong));

        // ===================================================================
        echo "\n--- NHÓM 13: QLK-08 DUYỆT ---\n";
        // ===================================================================

        // Đếm lại phiếu 2 (có lệch + lý do) để quay lại Chờ duyệt
        $pkk2->refresh();
        $items2b = [];
        foreach ($pkk2->chiTiets as $ct2b) {
            $lech = max(0, (int)$ct2b->So_luong_he_thong - 1);
            $items2b[$ct2b->Ma_lo] = [
                'So_luong_thuc_te' => (string)$lech,
                'Ly_do_chenh_lech' => 'Test: Mất 1 chai (đếm lại)',
            ];
        }
        makeRequest($app, $kernel, 'POST', "/nv-kho/kiem-ke/{$maPKK2}/dem", $nvk, [
            'action' => 'hoan_thanh',
            'items'  => $items2b,
        ]);
        $pkk2->refresh();
        assertTest('Setup duyệt: Phiếu 2 ở Chờ duyệt', $pkk2->Trang_thai === 'Chờ duyệt');

        // Lấy giá vốn bình quân để so sánh
        $firstCt2 = $pkk2->chiTiets->first();
        $firstCt2->refresh();
        $sanPham = \App\Models\SanPham::where('Ma_san_pham', $firstCt2->lo->Ma_san_pham ?? '')->first();
        $giaVonBQ = $sanPham ? $sanPham->Gia_von_binh_quan : null;

        $resp = makeRequest($app, $kernel, 'POST', "/kho/kiem-ke/{$maPKK2}/duyet", $qlk);
        assertTest('QLK-08 Duyệt → redirect', $resp->getStatusCode() === 302);

        $pkk2->refresh();
        assertTest('QLK-08 Duyệt: Phiếu → Hoàn tất', $pkk2->Trang_thai === 'Hoàn tất');

        // Kiểm tra Gia_von_dieu_chinh được snapshot
        $firstCt2->refresh();
        if ($giaVonBQ !== null) {
            assertTest('QLK-08 Duyệt: Gia_von_dieu_chinh snapshot đúng',
                abs((float)$firstCt2->Gia_von_dieu_chinh - (float)$giaVonBQ) < 0.01);
        }

        // Kiểm tra Ton_kho.Ton_thuc được ghi đè
        $tonSau = TonKho::where('Ma_lo', $firstCt2->Ma_lo)->where('Ma_kho', $maKho)->value('Ton_thuc');
        assertTest('QLK-08 Duyệt: Ton_kho.Ton_thuc được điều chỉnh theo thực tế',
            (int)$tonSau === (int)$firstCt2->So_luong_thuc_te);

        // Lô được mở khóa sau duyệt
        $lockedAfterDuyet = StockService::getLoDangKiemKeMap($maKho);
        foreach ($dsMaLo2 as $ml) {
            assertTest("Locking: Lô {$ml} mở khóa sau duyệt", !isset($lockedAfterDuyet[$ml]));
        }

        // ===================================================================
        echo "\n--- NHÓM 14: QLK-08 HỦY PHIẾU ---\n";
        // ===================================================================

        // Tạo phiếu thứ 4 để test hủy (dùng lô vừa được mở khóa ở trên)
        $tonKhoCo4 = TonKho::where('Ma_kho', $maKho)->where('Ton_thuc', '>', 0)->first();
        if ($tonKhoCo4) {
            makeRequest($app, $kernel, 'POST', '/kho/kiem-ke', $qlk, [
                'ma_los' => [$tonKhoCo4->Ma_lo],
            ]);
            $pkk4 = PhieuKiemKe::with(['chiTiets'])->orderBy('Ma_phieu_kiem_ke', 'desc')->first();
            $maPKK4 = $pkk4->Ma_phieu_kiem_ke;

            // Xác nhận lô đang bị khóa
            $lockedPKK4 = StockService::getLoDangKiemKeMap($maKho);
            assertTest("Locking: Lô {$tonKhoCo4->Ma_lo} bị khóa bởi {$maPKK4}",
                isset($lockedPKK4[$tonKhoCo4->Ma_lo]));

            // Hủy phiếu
            $resp = makeRequest($app, $kernel, 'POST', "/kho/kiem-ke/{$maPKK4}/huy", $qlk, [
                'ly_do_huy' => 'Test: Hủy để kiểm tra unlock',
            ]);
            assertTest('QLK-08 Hủy phiếu → redirect', $resp->getStatusCode() === 302);

            $pkk4->refresh();
            assertTest('QLK-08 Hủy phiếu: Phiếu → Đã hủy', $pkk4->Trang_thai === 'Đã hủy');
            assertTest('QLK-08 Hủy phiếu: Ly_do_huy được lưu', !empty($pkk4->Ly_do_huy));

            // Lô được mở khóa sau hủy
            $lockedAfterHuy = StockService::getLoDangKiemKeMap($maKho);
            assertTest("Locking: Lô {$tonKhoCo4->Ma_lo} mở khóa sau hủy",
                !isset($lockedAfterHuy[$tonKhoCo4->Ma_lo]));
        }
    }
}

// ===================================================================
echo "\n--- NHÓM 15: MÃ PHIẾU (prefix KK-) ---\n";
// ===================================================================
$anyPkk = PhieuKiemKe::first();
if ($anyPkk) {
    assertTest('Mã phiếu kiểm kê có prefix KK-', str_starts_with($anyPkk->Ma_phieu_kiem_ke, 'KK-'));
}

// ===================================================================
echo "\n=== KẾT QUẢ ===\n";
echo "PASS: {$passed}\n";
echo "FAIL: {$failed}\n";
echo "TỔNG: " . ($passed + $failed) . "\n";
if ($failed === 0) {
    echo "\n✅ Tất cả test PASS!\n";
} else {
    echo "\n❌ Có {$failed} test FAIL.\n";
    exit(1);
}

