<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\NguoiDung;
use App\Services\NotificationService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

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

echo "=== KIỂM THỬ TRANG CHỦ DASHBOARD 5 ROLES (SLICE 10) ===\n\n";

$adm = NguoiDung::where('Ten_dang_nhap', 'admin')->first();
$qlk = NguoiDung::where('Ten_dang_nhap', 'qlkho.hn')->first();
$nvk = NguoiDung::where('Ten_dang_nhap', 'nvkho.hn')->first();
$kt  = NguoiDung::where('Ten_dang_nhap', 'ketoan01')->first();
$kd  = NguoiDung::where('Ten_dang_nhap', 'kinhdoanh01')->first();

// --- 1. QUẢN TRỊ (ADM-01) ---
echo "--- 1. QUẢN TRỊ (ADM-01) ---\n";
$resp = makeRequest($app, $kernel, 'GET', '/quan-tri', $adm);
assertTest('ADM-01: GET /quan-tri → 200', $resp->getStatusCode() === 200);
assertTest('ADM-01: Chứa thông tin tài khoản bị khóa', str_contains($resp->getContent(), 'Tài khoản bị khóa') || str_contains($resp->getContent(), 'Người dùng bị khóa'));
assertTest('ADM-01: Bell count trả về số nguyên', is_int(NotificationService::getCountForUser($adm)));

// --- 2. QUẢN LÝ KHO (QLK-01) ---
echo "\n--- 2. QUẢN LÝ KHO (QLK-01) ---\n";
$resp = makeRequest($app, $kernel, 'GET', '/kho', $qlk);
assertTest('QLK-01: GET /kho → 200', $resp->getStatusCode() === 200);
assertTest('QLK-01: Chứa Việc cần xử lý', str_contains($resp->getContent(), 'Việc cần xử lý'));
assertTest('QLK-01: Chứa tình trạng kho hàng', str_contains($resp->getContent(), 'Tình trạng kho hàng'));
assertTest('QLK-01: Bell count trả về số nguyên >= 0', NotificationService::getCountForUser($qlk) >= 0);

// --- 3. NHÂN VIÊN KHO (NVK-01) ---
echo "\n--- 3. NHÂN VIÊN KHO (NVK-01) ---\n";
$resp = makeRequest($app, $kernel, 'GET', '/nv-kho', $nvk);
assertTest('NVK-01: GET /nv-kho → 200', $resp->getStatusCode() === 200);
assertTest('NVK-01: Chứa Việc cần xử lý', str_contains($resp->getContent(), 'Việc cần xử lý'));
assertTest('NVK-01: Chứa Phiếu xuất cần xử lý', str_contains($resp->getContent(), 'Phiếu xuất cần xử lý'));
assertTest('NVK-01: Bell count trả về số nguyên >= 0', NotificationService::getCountForUser($nvk) >= 0);

// --- 4. KẾ TOÁN (KT-01) ---
echo "\n--- 4. KẾ TOÁN (KT-01) ---\n";
$resp = makeRequest($app, $kernel, 'GET', '/ke-toan', $kt);
assertTest('KT-01: GET /ke-toan → 200', $resp->getStatusCode() === 200);
assertTest('KT-01: Chứa Chỉ số tài chính tháng', str_contains($resp->getContent(), 'Chỉ số tài chính'));
assertTest('KT-01: Chứa Giá trị tồn kho hiện tại', str_contains($resp->getContent(), 'Giá trị tồn kho hiện tại'));
assertTest('KT-01: Chứa Doanh thu tháng này', str_contains($resp->getContent(), 'Doanh thu tháng này'));
assertTest('KT-01: Bell count trả về số nguyên >= 0', NotificationService::getCountForUser($kt) >= 0);

// --- 5. KINH DOANH (KD-01) ---
echo "\n--- 5. KINH DOANH (KD-01) ---\n";
$resp = makeRequest($app, $kernel, 'GET', '/kinh-doanh', $kd);
assertTest('KD-01: GET /kinh-doanh → 200', $resp->getStatusCode() === 200);
assertTest('KD-01: Chứa Việc cần xử lý', str_contains($resp->getContent(), 'Việc cần xử lý'));
assertTest('KD-01: Chứa Hiệu quả bán hàng', str_contains($resp->getContent(), 'Hiệu quả bán hàng'));
assertTest('KD-01: Bell count trả về số nguyên >= 0', NotificationService::getCountForUser($kd) >= 0);

// --- 6. ROOT REDIRECT THEO ROLE ---
echo "\n--- 6. ROOT REDIRECT THEO ROLE ---\n";
$resp = makeRequest($app, $kernel, 'GET', '/', $qlk);
assertTest('Root / redirect QL_KHO về /kho', $resp->isRedirect(url('/kho')));

$resp = makeRequest($app, $kernel, 'GET', '/', $nvk);
assertTest('Root / redirect NV_KHO về /nv-kho', $resp->isRedirect(url('/nv-kho')));

$resp = makeRequest($app, $kernel, 'GET', '/', $kt);
assertTest('Root / redirect KE_TOAN về /ke-toan', $resp->isRedirect(url('/ke-toan')));

$resp = makeRequest($app, $kernel, 'GET', '/', $kd);
assertTest('Root / redirect KINH_DOANH về /kinh-doanh', $resp->isRedirect(url('/kinh-doanh')));

$resp = makeRequest($app, $kernel, 'GET', '/', $adm);
assertTest('Root / redirect QUAN_TRI về /quan-tri', $resp->isRedirect(url('/quan-tri')));

// --- 7. CHẶN TRUY CẬP CHÉO DASHBOARD (RBAC) ---
echo "\n--- 7. CHẶN TRUY CẬP CHÉO DASHBOARD (RBAC) ---\n";
$resp = makeRequest($app, $kernel, 'GET', '/kho', $kd);
assertTest('KD không vào được /kho → redirect/403', in_array($resp->getStatusCode(), [302, 403]));

$resp = makeRequest($app, $kernel, 'GET', '/ke-toan', $nvk);
assertTest('NVK không vào được /ke-toan → redirect/403', in_array($resp->getStatusCode(), [302, 403]));

$resp = makeRequest($app, $kernel, 'GET', '/quan-tri', $qlk);
assertTest('QLK không vào được /quan-tri → redirect/403', in_array($resp->getStatusCode(), [302, 403]));

echo "\n=== KẾT QUẢ ===\n";
echo "PASS: {$passed}\n";
echo "FAIL: {$failed}\n";
echo "TỔNG: " . ($passed + $failed) . "\n";
if ($failed === 0) {
    echo "\n✅ Tất cả test Dashboard PASS!\n";
} else {
    echo "\n❌ Có {$failed} test FAIL.\n";
    exit(1);
}

