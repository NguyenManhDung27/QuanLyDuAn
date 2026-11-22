<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\NguoiDung;
use App\Models\SanPham;
use App\Services\StockService;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

// Tắt CSRF trong test script CLI này
app()->instance(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, new class {
    public function handle($request, $next) {
        return $next($request);
    }
});

function makeRequest($app, $kernel, $method, $uri, $user = null, $data = []) {
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

echo "=== KIỂM THỬ LÁT CẮT 4: TỒN KHO & LÔ (QLK-02) ===\n\n";

$passCount = 0;
$failCount = 0;

function assertCondition(bool $cond, string $msg, &$passCount, &$failCount) {
    if ($cond) {
        echo " [PASS] $msg\n";
        $passCount++;
    } else {
        echo " [FAIL] $msg\n";
        $failCount++;
    }
}

// 1. Kiểm tra tính toán StockService trực tiếp
echo "--- 1. Kiểm tra StockService calculations ---\n";

// SP0001 tại HN (L2201: 10 chai)
$f2_sp1_hn = StockService::getTonThuc('HN', 'SP0001');
assertCondition($f2_sp1_hn === 10, "SP0001 Tồn thực tại HN là 10 (actual: $f2_sp1_hn)", $passCount, $failCount);

$f4_sp1_hn = StockService::getTonKhaDung('HN', 'SP0001');
assertCondition($f4_sp1_hn === 10, "SP0001 Khả dụng tại HN là 10 (actual: $f4_sp1_hn)", $passCount, $failCount);

$f6_sp1_hn = StockService::getTonDuKien('HN', 'SP0001');
assertCondition($f6_sp1_hn === 10, "SP0001 Dự kiến tại HN là 10 (actual: $f6_sp1_hn)", $passCount, $failCount);

// SP0003: L2303 (HN: 15, HCM: 20), L2304 (HN: 25) => HN = 40, HCM = 20, Toàn công ty = 60
$f2_sp3_hn = StockService::getTonThuc('HN', 'SP0003');
assertCondition($f2_sp3_hn === 40, "SP0003 Tồn thực tại HN là 40 (actual: $f2_sp3_hn)", $passCount, $failCount);

$f2_sp3_hcm = StockService::getTonThuc('HCM', 'SP0003');
assertCondition($f2_sp3_hcm === 20, "SP0003 Tồn thực tại HCM là 20 (actual: $f2_sp3_hcm)", $passCount, $failCount);

$tonThucMap = StockService::getTonThucMap(['SP0001', 'SP0003']);
$tongSp3 = array_sum($tonThucMap['SP0003'] ?? []);
assertCondition($tongSp3 === 60, "SP0003 Tồn thực toàn công ty là 60 (actual: $tongSp3)", $passCount, $failCount);

// Kiểm tra danh sách lô xếp theo FIFO (F12)
$lotsMap = StockService::getChiTietLoMap('HN', ['SP0003']);
$lotsSp3 = $lotsMap['SP0003'] ?? [];
assertCondition(count($lotsSp3) === 2, "SP0003 tại HN có 2 lô (actual: " . count($lotsSp3) . ")", $passCount, $failCount);
assertCondition(
    isset($lotsSp3[0]) && $lotsSp3[0]['ma_lo'] === 'L2303' && $lotsSp3[0]['thu_tu_xuat'] === 1,
    "Lô 1 của SP0003 là L2303 (ngày nhập 01/02/2026)",
    $passCount,
    $failCount
);
assertCondition(
    isset($lotsSp3[1]) && $lotsSp3[1]['ma_lo'] === 'L2304' && $lotsSp3[1]['thu_tu_xuat'] === 2,
    "Lô 2 của SP0003 là L2304 (ngày nhập 20/02/2026)",
    $passCount,
    $failCount
);

// 2. Kiểm tra truy cập HTTP và phân quyền
echo "\n--- 2. Phân quyền và HTTP Response ---\n";

// Không đăng nhập -> redirect sang login
$resGuest = makeRequest($app, $kernel, 'GET', '/kho/ton-kho');
assertCondition($resGuest->getStatusCode() === 302, "Khách chưa đăng nhập truy cập /kho/ton-kho bị 302 chuyển hướng", $passCount, $failCount);

// Vai trò KẾ TOÁN -> 403 Forbidden
$keToan = NguoiDung::where('Ten_dang_nhap', 'ketoan01')->first();
$resKeToan = makeRequest($app, $kernel, 'GET', '/kho/ton-kho', $keToan);
assertCondition($resKeToan->getStatusCode() === 403, "Kế toán truy cập /kho/ton-kho bị 403 Forbidden (actual: " . $resKeToan->getStatusCode() . ")", $passCount, $failCount);

// Vai trò QUẢN LÝ KHO (qlkho.hn) -> 200 OK
$qlKhoHn = NguoiDung::where('Ten_dang_nhap', 'qlkho.hn')->first();
$resQlKho = makeRequest($app, $kernel, 'GET', '/kho/ton-kho', $qlKhoHn);
assertCondition($resQlKho->getStatusCode() === 200, "QL_KHO (HN) truy cập /kho/ton-kho trả về 200 OK", $passCount, $failCount);

$content = $resQlKho->getContent();
assertCondition(strpos($content, 'Theo dõi tồn kho') !== false, "View chứa tiêu đề 'Theo dõi tồn kho'", $passCount, $failCount);
assertCondition(strpos($content, 'Kho Hà Nội') !== false, "View hiển thị tên kho 'Kho Hà Nội'", $passCount, $failCount);
assertCondition(strpos($content, 'SP0001') !== false, "View hiển thị mã sản phẩm 'SP0001'", $passCount, $failCount);
assertCondition(strpos($content, 'Château Dupont Rouge') !== false, "View hiển thị tên sản phẩm 'Château Dupont Rouge'", $passCount, $failCount);
assertCondition(strpos($content, 'LOT-FR-2201') !== false, "View hiển thị số lô 'LOT-FR-2201'", $passCount, $failCount);
assertCondition(strpos($content, 'Tạo đề xuất nhập kho') !== false, "View có nút 'Tạo đề xuất nhập kho'", $passCount, $failCount);

// 3. Kiểm tra bộ lọc
echo "\n--- 3. Kiểm tra chức năng lọc ---\n";
// Lọc search 'Chablis'
$resSearch = makeRequest($app, $kernel, 'GET', '/kho/ton-kho?search=Chablis', $qlKhoHn);
$contentSearch = $resSearch->getContent();
assertCondition(strpos($contentSearch, 'Chablis Blanc') !== false && strpos($contentSearch, 'Château Dupont') === false, "Lọc search 'Chablis' chỉ hiện SP0002", $passCount, $failCount);

// Lọc loại vang 'Đỏ'
$resLoai = makeRequest($app, $kernel, 'GET', '/kho/ton-kho?loai_vang=' . urlencode('Đỏ'), $qlKhoHn);
$contentLoai = $resLoai->getContent();
assertCondition(strpos($contentLoai, 'Château Dupont Rouge') !== false && strpos($contentLoai, 'Chablis Blanc') === false, "Lọc loại vang 'Đỏ' loại trừ vang trắng", $passCount, $failCount);

echo "\n============================================\n";
echo "KẾT QUẢ KIỂM THỬ: $passCount PASS, $failCount FAIL\n";
echo "============================================\n";
