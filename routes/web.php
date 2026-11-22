<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KeToan\DanhMucController as KeToanDanhMucController;
use App\Http\Controllers\Kho\NhaCungCapController as KhoNhaCungCapController;
use App\Http\Controllers\Kho\SanPhamController as KhoSanPhamController;
use App\Http\Controllers\KinhDoanh\DonBanController;
use App\Http\Controllers\KinhDoanh\KhachHangController as KdKhachHangController;
use App\Http\Controllers\KinhDoanh\SanPhamGiaController as KdSanPhamGiaController;
use App\Http\Controllers\QuanTri\KhoController;
use App\Http\Controllers\QuanTri\NguoiDungController;
use App\Http\Controllers\QuanTri\NhatKyController;
use App\Http\Controllers\QuanTri\QuanTriController;
use Illuminate\Support\Facades\Route;

// Redirect trang chủ
Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        return match ($user->Ma_vai_tro) {
            'QUAN_TRI' => redirect()->route('quantri.trang-chu'),
            'QL_KHO' => redirect()->route('kho.trang-chu'),
            'NV_KHO' => redirect()->route('nv-kho.trang-chu'),
            'KE_TOAN' => redirect()->route('ke-toan.trang-chu'),
            'KINH_DOANH' => redirect()->route('kinh-doanh.trang-chu'),
            default => redirect()->route('dashboard'),
        };
    }
    return redirect()->route('login');
});

// =========================================================================
// 1. XÁC THỰC (AUTH-01 ĐẾN AUTH-05) - 06-luong-man-hinh.md mục 1.1
// =========================================================================
Route::middleware('guest')->group(function () {
    // AUTH-01: Đăng nhập
    Route::get('/dang-nhap', [AuthController::class, 'showLoginForm'])->name('login');
    Route::get('/login', fn() => redirect()->route('login'));
    Route::post('/dang-nhap', [AuthController::class, 'login'])->name('login.post');
    Route::post('/login', [AuthController::class, 'login']);

    // AUTH-03: Quên mật khẩu
    Route::get('/quen-mat-khau', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::get('/forgot-password', fn() => redirect()->route('password.request'));
    Route::post('/quen-mat-khau', [AuthController::class, 'sendResetLink'])->name('password.email');

    // AUTH-04: Đặt lại mật khẩu
    Route::get('/dat-lai-mat-khau/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    // Đăng xuất
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');
    Route::post('/logout', [AuthController::class, 'logout']);

    // AUTH-02: Đổi mật khẩu lần đầu
    Route::get('/doi-mat-khau-lan-dau', [AuthController::class, 'showFirstLoginChangePassword'])->name('auth.doi-mat-khau-lan-dau');
    Route::get('/first-login/change-password', fn() => redirect()->route('auth.doi-mat-khau-lan-dau'))->name('first-login.change-password');
    Route::post('/doi-mat-khau-lan-dau', [AuthController::class, 'updateFirstLoginPassword'])->name('auth.doi-mat-khau-lan-dau.post');
    Route::post('/first-login/change-password', [AuthController::class, 'updateFirstLoginPassword'])->name('first-login.change-password.post');

    // AUTH-05: Đổi mật khẩu
    Route::get('/doi-mat-khau', [AuthController::class, 'showChangePassword'])->name('profile.change-password');
    Route::get('/change-password', fn() => redirect()->route('profile.change-password'))->name('password.change');
    Route::post('/doi-mat-khau', [AuthController::class, 'updatePassword'])->name('profile.change-password.post');
    Route::post('/change-password', [AuthController::class, 'updatePassword'])->name('password.change.post');

    // Dashboard chung (fallback)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // API chuông thông báo (Live polling)
    Route::get('/thong-bao/dem', [DashboardController::class, 'getNotificationSummary'])->name('thong-bao.dem');
});

// =========================================================================
// 2. LÁT CẮT 2: QUẢN TRỊ HỆ THỐNG (ADM-01 ĐẾN ADM-06)
// =========================================================================
Route::middleware(['auth', 'role:QUAN_TRI'])->prefix('quan-tri')->name('quantri.')->group(function () {
    // ADM-01: Trang chủ Quản trị
    Route::get('/', [QuanTriController::class, 'trangChu'])->name('trang-chu');

    // ADM-02, 03, 04: Quản lý người dùng
    Route::get('/nguoi-dung', [NguoiDungController::class, 'index'])->name('nguoi-dung.index');
    Route::get('/nguoi-dung/them', [NguoiDungController::class, 'create'])->name('nguoi-dung.create');
    Route::post('/nguoi-dung/them', [NguoiDungController::class, 'store'])->name('nguoi-dung.store');
    Route::get('/nguoi-dung/{ma}', [NguoiDungController::class, 'edit'])->name('nguoi-dung.edit');
    Route::post('/nguoi-dung/{ma}', [NguoiDungController::class, 'update'])->name('nguoi-dung.update');
    Route::post('/nguoi-dung/{ma}/toggle-lock', [NguoiDungController::class, 'toggleLock'])->name('nguoi-dung.toggle-lock');
    Route::delete('/nguoi-dung/{ma}', [NguoiDungController::class, 'destroy'])->name('nguoi-dung.destroy');

    // ADM-05: Quản lý kho
    Route::get('/kho', [KhoController::class, 'index'])->name('kho.index');
    Route::post('/kho', [KhoController::class, 'store'])->name('kho.store');
    Route::post('/kho/{ma}', [KhoController::class, 'update'])->name('kho.update');

    // ADM-06: Nhật ký hệ thống
    Route::get('/nhat-ky', [NhatKyController::class, 'index'])->name('nhat-ky.index');
});

// =========================================================================
// 3. ĐIỀU HƯỚNG CÁC VAI TRÒ KHÁC (QL_KHO, NV_KHO, KE_TOAN, KINH_DOANH)
// =========================================================================
Route::middleware(['auth', 'role:QL_KHO'])->prefix('kho')->name('kho.')->group(function () {
    Route::get('/', [DashboardController::class, 'qlKho'])->name('trang-chu');

    // QLK-02: Theo dõi tồn kho
    Route::get('/ton-kho', [\App\Http\Controllers\Kho\TonKhoController::class, 'index'])->name('ton-kho.index');

    // QLK-03, QLK-04: Đề xuất nhập kho
    Route::get('/de-xuat', [\App\Http\Controllers\Kho\DeXuatController::class, 'index'])->name('de-xuat.index');
    Route::get('/de-xuat/tao', [\App\Http\Controllers\Kho\DeXuatController::class, 'create'])->name('de-xuat.create');
    Route::post('/de-xuat/tao', [\App\Http\Controllers\Kho\DeXuatController::class, 'store'])->name('de-xuat.store');
    Route::get('/de-xuat/{ma}', [\App\Http\Controllers\Kho\DeXuatController::class, 'show'])->name('de-xuat.show');
    Route::get('/de-xuat/{ma}/sua', [\App\Http\Controllers\Kho\DeXuatController::class, 'edit'])->name('de-xuat.edit');
    Route::put('/de-xuat/{ma}', [\App\Http\Controllers\Kho\DeXuatController::class, 'update'])->name('de-xuat.update');
    Route::delete('/de-xuat/{ma}', [\App\Http\Controllers\Kho\DeXuatController::class, 'destroy'])->name('de-xuat.destroy');
    Route::post('/de-xuat/{ma}/gui-duyet', [\App\Http\Controllers\Kho\DeXuatController::class, 'guiDuyet'])->name('de-xuat.gui-duyet');
    Route::post('/de-xuat/{ma}/thu-hoi', [\App\Http\Controllers\Kho\DeXuatController::class, 'thuHoi'])->name('de-xuat.thu-hoi');
    Route::post('/de-xuat/{ma}/gui-lai', [\App\Http\Controllers\Kho\DeXuatController::class, 'guiLai'])->name('de-xuat.gui-lai');
    Route::post('/de-xuat/{ma}/huy-de-xuat', [\App\Http\Controllers\Kho\DeXuatController::class, 'huyDeXuat'])->name('de-xuat.huy');

    // QLK-05: Đơn đặt hàng
    Route::get('/don-dat-hang', [\App\Http\Controllers\Kho\DonDatHangController::class, 'index'])->name('don-dat-hang.index');
    Route::post('/don-dat-hang/{ma}/xac-nhan-gui-ncc', [\App\Http\Controllers\Kho\DonDatHangController::class, 'xacNhanGuiNcc'])->name('don-dat-hang.gui-ncc');
    Route::post('/don-dat-hang/{ma}/huy-don', [\App\Http\Controllers\Kho\DonDatHangController::class, 'huyDon'])->name('don-dat-hang.huy');
    Route::post('/don-dat-hang/{ma}/ngay-du-kien', [\App\Http\Controllers\Kho\DonDatHangController::class, 'capNhatNgayDuKien'])->name('don-dat-hang.ngay-du-kien');
    Route::post('/don-dat-hang/{ma}/dong-thieu', [\App\Http\Controllers\Kho\DonDatHangController::class, 'dongThieu'])->name('don-dat-hang.dong-thieu');

    // QLK-06, 07, 08: Kiểm kê kho
    Route::get('/kiem-ke', [\App\Http\Controllers\Kho\KiemKeController::class, 'index'])->name('kiem-ke.index');
    Route::get('/kiem-ke/tao', [\App\Http\Controllers\Kho\KiemKeController::class, 'create'])->name('kiem-ke.create');
    Route::post('/kiem-ke', [\App\Http\Controllers\Kho\KiemKeController::class, 'store'])->name('kiem-ke.store');
    Route::get('/kiem-ke/{ma}', [\App\Http\Controllers\Kho\KiemKeController::class, 'show'])->name('kiem-ke.show');
    Route::post('/kiem-ke/{ma}/duyet', [\App\Http\Controllers\Kho\KiemKeController::class, 'duyet'])->name('kiem-ke.duyet');
    Route::post('/kiem-ke/{ma}/khong-duyet', [\App\Http\Controllers\Kho\KiemKeController::class, 'khongDuyet'])->name('kiem-ke.khong-duyet');
    Route::post('/kiem-ke/{ma}/huy', [\App\Http\Controllers\Kho\KiemKeController::class, 'huy'])->name('kiem-ke.huy');

    // QLK-09: Quản lý sản phẩm
    Route::get('/san-pham', [KhoSanPhamController::class, 'index'])->name('san-pham.index');
    Route::post('/san-pham', [KhoSanPhamController::class, 'store'])->name('san-pham.store');
    Route::put('/san-pham/{ma}', [KhoSanPhamController::class, 'update'])->name('san-pham.update');
    Route::delete('/san-pham/{ma}', [KhoSanPhamController::class, 'destroy'])->name('san-pham.destroy');

    // QLK-10: Quản lý nhà cung cấp
    Route::get('/nha-cung-cap', [KhoNhaCungCapController::class, 'index'])->name('nha-cung-cap.index');
    Route::post('/nha-cung-cap', [KhoNhaCungCapController::class, 'store'])->name('nha-cung-cap.store');
    Route::put('/nha-cung-cap/{ma}', [KhoNhaCungCapController::class, 'update'])->name('nha-cung-cap.update');
    Route::delete('/nha-cung-cap/{ma}', [KhoNhaCungCapController::class, 'destroy'])->name('nha-cung-cap.destroy');

    // QLK-11: Báo cáo kho
    Route::get('/bao-cao', [\App\Http\Controllers\Kho\BaoCaoController::class, 'index'])->name('bao-cao.index');
    Route::get('/bao-cao/xuat-excel', [\App\Http\Controllers\Kho\BaoCaoController::class, 'export'])->name('bao-cao.export');
});

Route::middleware(['auth', 'role:NV_KHO'])->prefix('nv-kho')->name('nv-kho.')->group(function () {
    Route::get('/', [DashboardController::class, 'nvKho'])->name('trang-chu');

    // NVK-02: Danh sách phiếu nhập kho
    Route::get('/phieu-nhap', [\App\Http\Controllers\NvKho\PhieuNhapController::class, 'index'])->name('phieu-nhap.index');
    Route::get('/phieu-nhap/{ma}', [\App\Http\Controllers\NvKho\PhieuNhapController::class, 'show'])->name('phieu-nhap.show');
    Route::post('/phieu-nhap/{ma}/xac-nhan', [\App\Http\Controllers\NvKho\PhieuNhapController::class, 'xacNhan'])->name('phieu-nhap.xac-nhan');
    Route::delete('/phieu-nhap/{ma}', [\App\Http\Controllers\NvKho\PhieuNhapController::class, 'destroy'])->name('phieu-nhap.destroy');

    // NVK-03: Chọn chứng từ gốc để nhập kho
    Route::get('/nhap-kho', [\App\Http\Controllers\NvKho\NhapKhoController::class, 'index'])->name('nhap-kho.index');
    Route::get('/nhap-kho/ncc', function() { return redirect()->route('nv-kho.nhap-kho.index', ['loai' => 'Nhập từ NCC']); })->name('nhap-kho.ncc-list');
    Route::get('/nhap-kho/khach-tra', function() { return redirect()->route('nv-kho.nhap-kho.index', ['loai' => 'Khách trả hàng']); })->name('nhap-kho.khach-tra');
    Route::get('/nhap-kho/hoan-giao', function() { return redirect()->route('nv-kho.nhap-kho.index', ['loai' => 'Hoàn giao thất bại']); })->name('nhap-kho.hoan-giao');

    // NVK-04: Nhập hàng từ NCC theo đơn đặt hàng
    Route::get('/nhap-kho/ncc/{ma_don}', [\App\Http\Controllers\NvKho\NhapKhoController::class, 'nhapNcc'])->name('nhap-kho.ncc');
    Route::post('/nhap-kho/ncc/{ma_don}', [\App\Http\Controllers\NvKho\NhapKhoController::class, 'storeNcc'])->name('nhap-kho.ncc.store');

    // NVK-05 & NVK-06: Xử lý hàng hoàn (Khách trả hàng & Hoàn giao thất bại)
    Route::post('/nhap-kho/xu-ly-hoan', [\App\Http\Controllers\NvKho\NhapKhoController::class, 'xuLyHoan'])->name('nhap-kho.xu-ly-hoan');

    // NVK-07: Phiếu xuất kho
    Route::get('/phieu-xuat', [\App\Http\Controllers\NvKho\PhieuXuatController::class, 'index'])->name('phieu-xuat.index');
    // NVK-08: Chi tiết phiếu xuất
    Route::get('/phieu-xuat/{ma}', [\App\Http\Controllers\NvKho\PhieuXuatController::class, 'show'])->name('phieu-xuat.show');
    Route::post('/phieu-xuat/{ma}/xuat-kho', [\App\Http\Controllers\NvKho\PhieuXuatController::class, 'xuatKho'])->name('phieu-xuat.xuat-kho');
    Route::post('/phieu-xuat/{ma}/doi-lo', [\App\Http\Controllers\NvKho\PhieuXuatController::class, 'doiLo'])->name('phieu-xuat.doi-lo');
    Route::put('/phieu-xuat/{ma}/van-chuyen', [\App\Http\Controllers\NvKho\PhieuXuatController::class, 'updateVanChuyen'])->name('phieu-xuat.update-van-chuyen');
    Route::post('/phieu-xuat/{ma}/giao-hang', [\App\Http\Controllers\NvKho\PhieuXuatController::class, 'capNhatGiaoHang'])->name('phieu-xuat.cap-nhat-giao-hang');
    Route::get('/phieu-xuat/{ma}/api/lo-thay-the', [\App\Http\Controllers\NvKho\PhieuXuatController::class, 'apiLoThayThe'])->name('phieu-xuat.api-lo-thay-the');

    // NVK-09, NVK-10: Kiểm kê kho
    Route::get('/kiem-ke', [\App\Http\Controllers\NvKho\KiemKeController::class, 'index'])->name('kiem-ke.index');
    Route::get('/kiem-ke/{ma}/dem', [\App\Http\Controllers\NvKho\KiemKeController::class, 'dem'])->name('kiem-ke.dem');
    Route::post('/kiem-ke/{ma}/dem', [\App\Http\Controllers\NvKho\KiemKeController::class, 'luuDem'])->name('kiem-ke.luu-dem');
});

Route::middleware(['auth', 'role:KE_TOAN'])->prefix('ke-toan')->name('ke-toan.')->group(function () {
    Route::get('/', [DashboardController::class, 'keToan'])->name('trang-chu');

    // KT-02, KT-03: Phê duyệt đề xuất nhập kho
    Route::get('/de-xuat', [\App\Http\Controllers\KeToan\DeXuatController::class, 'index'])->name('de-xuat.index');
    Route::get('/de-xuat/{ma}', [\App\Http\Controllers\KeToan\DeXuatController::class, 'show'])->name('de-xuat.show');
    Route::post('/de-xuat/{ma}/duyet', [\App\Http\Controllers\KeToan\DeXuatController::class, 'duyet'])->name('de-xuat.duyet');
    Route::post('/de-xuat/{ma}/tu-choi', [\App\Http\Controllers\KeToan\DeXuatController::class, 'tuChoi'])->name('de-xuat.tu-choi');

    // KT-05, KT-06, KT-07: Danh mục chỉ xem
    Route::get('/san-pham', [KeToanDanhMucController::class, 'sanPham'])->name('san-pham.index');
    Route::get('/nha-cung-cap', [KeToanDanhMucController::class, 'nhaCungCap'])->name('nha-cung-cap.index');
    Route::get('/khach-hang', [KeToanDanhMucController::class, 'khachHang'])->name('khach-hang.index');

    // KT-04: Báo cáo kế toán
    Route::get('/bao-cao', [\App\Http\Controllers\KeToan\BaoCaoController::class, 'index'])->name('bao-cao.index');
    Route::get('/bao-cao/xuat-excel', [\App\Http\Controllers\KeToan\BaoCaoController::class, 'export'])->name('bao-cao.export');
});

Route::middleware(['auth', 'role:KINH_DOANH'])->prefix('kinh-doanh')->name('kinh-doanh.')->group(function () {
    Route::get('/', [DashboardController::class, 'kinhDoanh'])->name('trang-chu');

    // KD-05: Khách hàng
    Route::get('/khach-hang', [KdKhachHangController::class, 'index'])->name('khach-hang.index');
    Route::post('/khach-hang', [KdKhachHangController::class, 'store'])->name('khach-hang.store');
    Route::put('/khach-hang/{ma}', [KdKhachHangController::class, 'update'])->name('khach-hang.update');
    Route::delete('/khach-hang/{ma}', [KdKhachHangController::class, 'destroy'])->name('khach-hang.destroy');

    // KD-06: Sản phẩm & Giá niêm yết
    Route::get('/san-pham', [KdSanPhamGiaController::class, 'index'])->name('san-pham.index');
    Route::post('/san-pham/{ma}/gia', [KdSanPhamGiaController::class, 'updatePrice'])->name('san-pham.update-gia');

    // KD-02: Danh sách đơn hàng bán
    Route::get('/don-ban', [DonBanController::class, 'index'])->name('don-ban.index');
    // KD-03: Lập đơn bán
    Route::get('/don-ban/lap', [DonBanController::class, 'create'])->name('don-ban.create');
    Route::post('/don-ban', [DonBanController::class, 'store'])->name('don-ban.store');
    // KD-04: Chi tiết
    Route::get('/don-ban/{ma}', [DonBanController::class, 'show'])->name('don-ban.show');
    Route::put('/don-ban/{ma}/giao-hang', [DonBanController::class, 'updateGiaoHang'])->name('don-ban.update-giao-hang');
    Route::post('/don-ban/{ma}/huy', [DonBanController::class, 'huyDon'])->name('don-ban.huy');
    // AJAX: tồn khả dụng
    Route::get('/don-ban/api/ton-kha-dung', [DonBanController::class, 'tonKhaDung'])->name('don-ban.ton-kha-dung');
});
