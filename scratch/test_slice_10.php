<?php

/**
 * TEST SUITE: SLICE 10 (Trang chủ Dashboards, Thông báo, Báo cáo & Xuất Excel)
 *
 * Kiểm tra đầy đủ:
 * 1. 5 Dashboards: ADM-01, QLK-01, NVK-01, KT-01, KD-01
 * 2. Notification Bell: NotificationService count & item list
 * 3. QLK-11: Báo cáo Quản lý kho (4 tabs + Xuất Excel/CSV)
 * 4. KT-04: Báo cáo Kế toán (6 tabs + Xuất Excel/CSV)
 * 5. RBAC & Cross-Role Security
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\NguoiDung;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $message): void {
    global $passed, $failed;
    if ($condition) {
        echo "  [PASS] $message\n";
        $passed++;
    } else {
        echo "  [FAIL] $message\n";
        $failed++;
    }
}

function makeRequest($app, $kernel, string $method, string $uri, array $params = [], ?NguoiDung $user = null) {
    $request = Request::create($uri, $method, $params);
    $app->instance('request', $request);

    if ($user) {
        Auth::login($user);
        $request->setUserResolver(fn() => $user);
    } else {
        Auth::logout();
    }

    return $kernel->handle($request);
}

echo "=== KIỂM THỬ TOÀN DIỆN SLICE 10 ===\n\n";

$admin = NguoiDung::where('Ten_dang_nhap', 'admin')->first();
$qlk = NguoiDung::where('Ten_dang_nhap', 'qlkho.hn')->first();
$nvk = NguoiDung::where('Ten_dang_nhap', 'nvkho.hn')->first();
$kt = NguoiDung::where('Ten_dang_nhap', 'ketoan01')->first();
$kd = NguoiDung::where('Ten_dang_nhap', 'kinhdoanh01')->first();

// --- 1. DASHBOARDS (5 ROLES) ---
echo "--- 1. DASHBOARD & CHUÔNG THÔNG BÁO ---\n";
$resp = makeRequest($app, $kernel, 'GET', '/quan-tri', [], $admin);
assertTest($resp->getStatusCode() === 200, "ADM-01: GET /quan-tri trả về 200");
assertTest(str_contains($resp->getContent(), 'Người dùng bị khóa'), "ADM-01: Chứa card Người dùng bị khóa");

$resp = makeRequest($app, $kernel, 'GET', '/kho', [], $qlk);
assertTest($resp->getStatusCode() === 200, "QLK-01: GET /kho trả về 200");
assertTest(str_contains($resp->getContent(), 'Tình trạng kho hàng'), "QLK-01: Chứa Tình trạng kho hàng");

$resp = makeRequest($app, $kernel, 'GET', '/nv-kho', [], $nvk);
assertTest($resp->getStatusCode() === 200, "NVK-01: GET /nv-kho trả về 200");
assertTest(str_contains($resp->getContent(), 'Phiếu xuất cần xử lý'), "NVK-01: Chứa Phiếu xuất cần xử lý");

$resp = makeRequest($app, $kernel, 'GET', '/ke-toan', [], $kt);
assertTest($resp->getStatusCode() === 200, "KT-01: GET /ke-toan trả về 200");
assertTest(str_contains($resp->getContent(), 'Chỉ số tài chính tháng'), "KT-01: Chứa Chỉ số tài chính tháng");
assertTest(str_contains($resp->getContent(), 'Giá trị tồn kho'), "KT-01: Chứa Giá trị tồn kho");

$resp = makeRequest($app, $kernel, 'GET', '/kinh-doanh', [], $kd);
assertTest($resp->getStatusCode() === 200, "KD-01: GET /kinh-doanh trả về 200");
assertTest(str_contains($resp->getContent(), 'Hiệu quả bán hàng'), "KD-01: Chứa Hiệu quả bán hàng");

// Bell counts
$cAdmin = NotificationService::getCountForUser($admin);
$cQlk = NotificationService::getCountForUser($qlk);
$cNvk = NotificationService::getCountForUser($nvk);
$cKt = NotificationService::getCountForUser($kt);
$cKd = NotificationService::getCountForUser($kd);
assertTest(is_int($cAdmin) && is_int($cQlk) && is_int($cNvk) && is_int($cKt) && is_int($cKd), "Chuông thông báo trả về số nguyên hợp lệ cho cả 5 roles");

// --- 2. BÁO CÁO QUẢN LÝ KHO (QLK-11) ---
echo "\n--- 2. BÁO CÁO QUẢN LÝ KHO (QLK-11) ---\n";
// Tab 0: Nhập – xuất – tồn
$resp = makeRequest($app, $kernel, 'GET', '/kho/bao-cao', ['tab' => 0], $qlk);
assertTest($resp->getStatusCode() === 200, "QLK-11 Tab 0: GET /kho/bao-cao?tab=0 trả về 200");
assertTest(str_contains($resp->getContent(), 'Nhập – xuất – tồn'), "QLK-11 Tab 0: Chứa tiêu đề Nhập – xuất – tồn");
assertTest(str_contains($resp->getContent(), 'Tồn đầu kỳ') && str_contains($resp->getContent(), 'Tồn cuối kỳ'), "QLK-11 Tab 0: Chứa cột Tồn đầu kỳ và Tồn cuối kỳ");
assertTest(!str_contains($resp->getContent(), 'Giá vốn'), "QLK-11 Tab 0: Không hiển thị cột tiền (chỉ số lượng)");

// Tab 1: Giao hàng theo nhà cung cấp
$resp = makeRequest($app, $kernel, 'GET', '/kho/bao-cao', ['tab' => 1], $qlk);
assertTest($resp->getStatusCode() === 200, "QLK-11 Tab 1: GET /kho/bao-cao?tab=1 trả về 200");
assertTest(str_contains($resp->getContent(), 'Giao hàng theo nhà cung cấp'), "QLK-11 Tab 1: Chứa tiêu đề Giao hàng theo nhà cung cấp");
assertTest(str_contains($resp->getContent(), 'Đơn đúng hạn') && str_contains($resp->getContent(), 'Hao hụt'), "QLK-11 Tab 1: Chứa cột Đơn đúng hạn và Hao hụt");

// Tab 2: Hàng hỏng
$resp = makeRequest($app, $kernel, 'GET', '/kho/bao-cao', ['tab' => 2], $qlk);
assertTest($resp->getStatusCode() === 200, "QLK-11 Tab 2: GET /kho/bao-cao?tab=2 trả về 200");
assertTest(str_contains($resp->getContent(), 'Hàng hỏng'), "QLK-11 Tab 2: Chứa tiêu đề Hàng hỏng");
assertTest(str_contains($resp->getContent(), 'SL hỏng'), "QLK-11 Tab 2: Chứa cột SL hỏng");

// Tab 3: Chênh lệch kiểm kê
$resp = makeRequest($app, $kernel, 'GET', '/kho/bao-cao', ['tab' => 3], $qlk);
assertTest($resp->getStatusCode() === 200, "QLK-11 Tab 3: GET /kho/bao-cao?tab=3 trả về 200");
assertTest(str_contains($resp->getContent(), 'Chênh lệch kiểm kê'), "QLK-11 Tab 3: Chứa tiêu đề Chênh lệch kiểm kê");
assertTest(str_contains($resp->getContent(), 'Thực đếm') && str_contains($resp->getContent(), 'Tồn hệ thống'), "QLK-11 Tab 3: Chứa cột Thực đếm và Tồn hệ thống");

// Export QLK-11
$resp = makeRequest($app, $kernel, 'GET', '/kho/bao-cao/xuat-excel', ['tab' => 0], $qlk);
assertTest($resp->getStatusCode() === 200, "QLK-11 Export: GET /kho/bao-cao/xuat-excel?tab=0 trả về 200");
assertTest(str_contains($resp->headers->get('content-type', ''), 'text/csv'), "QLK-11 Export: Header Content-Type là text/csv");
assertTest(str_contains($resp->headers->get('content-disposition', ''), 'attachment'), "QLK-11 Export: Header Content-Disposition là attachment");

// --- 3. BÁO CÁO KẾ TOÁN (KT-04) ---
echo "\n--- 3. BÁO CÁO KẾ TOÁN (KT-04) ---\n";
// Tab 0: Tồn kho
$resp = makeRequest($app, $kernel, 'GET', '/ke-toan/bao-cao', ['tab' => 0], $kt);
assertTest($resp->getStatusCode() === 200, "KT-04 Tab 0: GET /ke-toan/bao-cao?tab=0 trả về 200");
assertTest(str_contains($resp->getContent(), 'Tồn kho'), "KT-04 Tab 0: Chứa tiêu đề Tồn kho");
assertTest(str_contains($resp->getContent(), 'Giá trị tồn'), "KT-04 Tab 0: Chứa cột Giá trị tồn (tiền)");
assertTest(str_contains($resp->getContent(), 'Giá trị tồn kho'), "KT-04 Tab 0: Chứa KPI Giá trị tồn kho");

// Tab 1: Nhập – xuất – tồn
$resp = makeRequest($app, $kernel, 'GET', '/ke-toan/bao-cao', ['tab' => 1], $kt);
assertTest($resp->getStatusCode() === 200, "KT-04 Tab 1: GET /ke-toan/bao-cao?tab=1 trả về 200");
assertTest(str_contains($resp->getContent(), 'Tồn đầu GT') && str_contains($resp->getContent(), 'Tồn cuối GT'), "KT-04 Tab 1: Chứa cột Tồn đầu GT và Tồn cuối GT");

// Tab 2: Doanh thu – lãi gộp
$resp = makeRequest($app, $kernel, 'GET', '/ke-toan/bao-cao', ['tab' => 2], $kt);
assertTest($resp->getStatusCode() === 200, "KT-04 Tab 2: GET /ke-toan/bao-cao?tab=2 trả về 200");
assertTest(str_contains($resp->getContent(), 'Doanh thu') && str_contains($resp->getContent(), 'Lãi gộp'), "KT-04 Tab 2: Chứa Doanh thu và Lãi gộp");
assertTest(str_contains($resp->getContent(), 'Doanh thu và lãi gộp theo tháng'), "KT-04 Tab 2: Chứa biểu đồ 12 tháng");

// Tab 3: Mua hàng
$resp = makeRequest($app, $kernel, 'GET', '/ke-toan/bao-cao', ['tab' => 3], $kt);
assertTest($resp->getStatusCode() === 200, "KT-04 Tab 3: GET /ke-toan/bao-cao?tab=3 trả về 200");
assertTest(str_contains($resp->getContent(), 'Giá trị đặt') && str_contains($resp->getContent(), 'Hao hụt GT'), "KT-04 Tab 3: Chứa Giá trị đặt và Hao hụt GT");

// Tab 4: Hàng trả và giao thất bại
$resp = makeRequest($app, $kernel, 'GET', '/ke-toan/bao-cao', ['tab' => 4], $kt);
assertTest($resp->getStatusCode() === 200, "KT-04 Tab 4: GET /ke-toan/bao-cao?tab=4 trả về 200");
assertTest(str_contains($resp->getContent(), 'SL nhập lại') && str_contains($resp->getContent(), 'GT không nhập'), "KT-04 Tab 4: Chứa SL nhập lại và GT không nhập");

// Tab 5: Điều chỉnh tồn
$resp = makeRequest($app, $kernel, 'GET', '/ke-toan/bao-cao', ['tab' => 5], $kt);
assertTest($resp->getStatusCode() === 200, "KT-04 Tab 5: GET /ke-toan/bao-cao?tab=5 trả về 200");
assertTest(str_contains($resp->getContent(), 'Kiểm kê thừa') || str_contains($resp->getContent(), 'Tổng điều chỉnh'), "KT-04 Tab 5: Chứa KPIs điều chỉnh tồn");

// Export KT-04
$resp = makeRequest($app, $kernel, 'GET', '/ke-toan/bao-cao/xuat-excel', ['tab' => 0], $kt);
assertTest($resp->getStatusCode() === 200, "KT-04 Export: GET /ke-toan/bao-cao/xuat-excel?tab=0 trả về 200");
assertTest(str_contains($resp->headers->get('content-type', ''), 'text/csv'), "KT-04 Export: Header Content-Type là text/csv");

// --- 4. BẢO VỆ PHÂN QUYỀN (RBAC) BÁO CÁO ---
echo "\n--- 4. BẢO VỆ PHÂN QUYỀN (RBAC) ---\n";
$resp = makeRequest($app, $kernel, 'GET', '/kho/bao-cao', [], $nvk);
assertTest(in_array($resp->getStatusCode(), [302, 403]), "NV_KHO không vào được /kho/bao-cao -> 302/403");

$resp = makeRequest($app, $kernel, 'GET', '/ke-toan/bao-cao', [], $nvk);
assertTest(in_array($resp->getStatusCode(), [302, 403]), "NV_KHO không vào được /ke-toan/bao-cao -> 302/403");

$resp = makeRequest($app, $kernel, 'GET', '/kho/bao-cao', [], $kd);
assertTest(in_array($resp->getStatusCode(), [302, 403]), "KINH_DOANH không vào được /kho/bao-cao -> 302/403");

$resp = makeRequest($app, $kernel, 'GET', '/ke-toan/bao-cao', [], $qlk);
assertTest(in_array($resp->getStatusCode(), [302, 403]), "QL_KHO không vào được /ke-toan/bao-cao -> 302/403");

$resp = makeRequest($app, $kernel, 'GET', '/kho/bao-cao', [], $kt);
assertTest(in_array($resp->getStatusCode(), [302, 403]), "KE_TOAN không vào được /kho/bao-cao -> 302/403");

echo "\n=== KẾT QUẢ SLICE 10 ===\n";
echo "PASS: $passed\n";
echo "FAIL: $failed\n";
echo "TỔNG: " . ($passed + $failed) . "\n\n";

if ($failed === 0) {
    echo "✅ Tất cả test SLICE 10 PASS HOÀN TOÀN!\n";
    exit(0);
} else {
    echo "❌ Có $failed test thất bại.\n";
    exit(1);
}
