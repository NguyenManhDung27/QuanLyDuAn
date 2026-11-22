<?php

namespace App\Http\Controllers;

use App\Models\ChiTietDonBan;
use App\Models\ChiTietPhieuNhap;
use App\Models\ChiTietPhieuXuat;
use App\Models\DonDatHang;
use App\Models\DonHangBan;
use App\Models\KhachHang;
use App\Models\Kho;
use App\Models\Lo;
use App\Models\NguoiDung;
use App\Models\PhieuKiemKe;
use App\Models\PhieuNhapKho;
use App\Models\PhieuXuatKho;
use App\Models\SanPham;
use App\Models\TonKho;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard fallback / redirect
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->Phai_doi_mat_khau) {
            return redirect()->route('first-login.change-password')
                ->with('warning', 'Bạn cần đổi mật khẩu lần đầu trước khi truy cập hệ thống.');
        }

        return match ($user->Ma_vai_tro) {
            'QUAN_TRI' => redirect()->route('quantri.trang-chu'),
            'QL_KHO' => $this->qlKho(),
            'NV_KHO' => $this->nvKho(),
            'KE_TOAN' => $this->keToan(),
            'KINH_DOANH' => $this->kinhDoanh(),
            default => view('dashboard', compact('user')),
        };
    }

    /**
     * QLK-01: Trang chủ Quản lý kho
     */
    public function qlKho(): View
    {
        $user = Auth::user();
        $maKho = $user->Ma_kho ?? 'KHO_HN';
        $kho = Kho::find($maKho);

        // Action cards từ NotificationService
        $actionData = NotificationService::getRoleActionItems($user);
        $cards = $actionData['cards'] ?? [];

        // Thống kê tổng quan
        $tongSanPham = SanPham::where('Trang_thai', 'Đang kinh doanh')->count();
        $tongTonThuc = (int) TonKho::where('Ma_kho', $maKho)->sum('Ton_thuc');

        // Lô sắp hết hạn trong 30 ngày tới
        $today = Carbon::today();
        $thirtyDaysLater = Carbon::today()->addDays(30);

        $soLoSapHetHan = DB::table('Ton_kho as tk')
            ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo')
            ->where('tk.Ma_kho', $maKho)
            ->where('tk.Ton_thuc', '>', 0)
            ->whereNotNull('l.Han_su_dung')
            ->whereBetween('l.Han_su_dung', [$today->toDateString(), $thirtyDaysLater->toDateString()])
            ->count();

        // 5 đơn đặt hàng / đề xuất gần nhất
        $donGanDay = DonDatHang::where('Ma_kho', $maKho)
            ->with(['nhaCungCap', 'nguoiLap'])
            ->orderBy('Ngay_lap', 'desc')
            ->orderBy('Ma_don_dat_hang', 'desc')
            ->take(5)
            ->get();

        // 5 phiếu kiểm kê gần nhất
        $kiemKeGanDay = PhieuKiemKe::where('Ma_kho', $maKho)
            ->with(['nguoiTao', 'nguoiKiem'])
            ->orderBy('Thoi_diem_tao', 'desc')
            ->take(5)
            ->get();

        return view('kho.trang-chu', compact(
            'user', 'kho', 'cards',
            'tongSanPham', 'tongTonThuc', 'soLoSapHetHan',
            'donGanDay', 'kiemKeGanDay'
        ));
    }

    /**
     * NVK-01: Trang chủ Nhân viên kho
     */
    public function nvKho(): View
    {
        $user = Auth::user();
        $maKho = $user->Ma_kho ?? 'KHO_HN';
        $kho = Kho::find($maKho);

        // Action cards từ NotificationService
        $actionData = NotificationService::getRoleActionItems($user);
        $cards = $actionData['cards'] ?? [];

        // 5 phiếu xuất cần xử lý gần nhất
        $phieuXuatGanDay = PhieuXuatKho::join('Don_hang_ban', 'Don_hang_ban.Ma_don_hang_ban', '=', 'Phieu_xuat_kho.Ma_don_hang_ban')
            ->where('Don_hang_ban.Ma_kho', $maKho)
            ->whereIn('Phieu_xuat_kho.Trang_thai_giao_hang', ['Đang chuẩn bị hàng', 'Đang giao'])
            ->select('Phieu_xuat_kho.*', 'Don_hang_ban.Ten_nguoi_nhan', 'Don_hang_ban.Ma_don_hang_ban')
            ->orderBy('Phieu_xuat_kho.Ngay_tao', 'desc')
            ->orderBy('Phieu_xuat_kho.Ma_phieu_xuat', 'desc')
            ->take(5)
            ->get();

        // 5 phiếu nhập đang cập nhật gần nhất
        $phieuNhapGanDay = PhieuNhapKho::where('Ma_kho', $maKho)
            ->where('Trang_thai', 'Đang cập nhật')
            ->orderBy('Ngay_lap', 'desc')
            ->orderBy('Ma_phieu_nhap', 'desc')
            ->take(5)
            ->get();

        return view('nvkho.trang-chu', compact('user', 'kho', 'cards', 'phieuXuatGanDay', 'phieuNhapGanDay'));
    }

    /**
     * KT-01: Trang chủ Kế toán
     */
    public function keToan(): View
    {
        $user = Auth::user();

        // Action cards
        $actionData = NotificationService::getRoleActionItems($user);
        $cards = $actionData['cards'] ?? [];

        $startOfMonth = Carbon::now()->startOfMonth()->toDateTimeString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateTimeString();

        // 1. Giá trị tồn kho hiện tại (cả 2 kho) = Σ(Ton_thuc * Gia_von_binh_quan)
        $giaTriTonKho = (float) (DB::table('Ton_kho as tk')
            ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo')
            ->join('San_pham as sp', 'sp.Ma_san_pham', '=', 'l.Ma_san_pham')
            ->where('tk.Ton_thuc', '>', 0)
            ->select(DB::raw('SUM(tk.Ton_thuc * COALESCE(sp.Gia_von_binh_quan, 0)) as total'))
            ->value('total') ?? 0);

        // 2. Doanh thu tháng này = Σ(Chi_tiet_don_ban.So_luong * Don_gia_ban) của đơn Hoàn thành
        $doanhThuThang = (float) (DB::table('Don_hang_ban as dhb')
            ->join('Chi_tiet_don_ban as ct', 'ct.Ma_don_hang_ban', '=', 'dhb.Ma_don_hang_ban')
            ->where('dhb.Trang_thai', 'Hoàn thành')
            ->whereBetween('dhb.Thoi_diem_xac_nhan', [$startOfMonth, $endOfMonth])
            ->select(DB::raw('SUM(ct.So_luong * ct.Don_gia_ban) as total'))
            ->value('total') ?? 0);

        // 3. Giá vốn hàng bán tháng này = Σ(Chi_tiet_phieu_xuat.So_luong * Gia_von_xuat) của đơn giao thành công
        $giaVonThang = (float) (DB::table('Phieu_xuat_kho as px')
            ->join('Chi_tiet_phieu_xuat as ct', 'ct.Ma_phieu_xuat', '=', 'px.Ma_phieu_xuat')
            ->where('px.Trang_thai_giao_hang', 'Giao thành công')
            ->whereBetween('px.Thoi_diem_xuat_kho', [$startOfMonth, $endOfMonth])
            ->select(DB::raw('SUM(ct.So_luong * COALESCE(ct.Gia_von_xuat, 0)) as total'))
            ->value('total') ?? 0);

        // 4. Lãi gộp tháng này
        $laiGopThang = $doanhThuThang - $giaVonThang;

        // 5. Giá trị nhập mua tháng này
        $giaTriNhapMua = (float) (DB::table('Phieu_nhap_kho as pn')
            ->join('Don_dat_hang as ddh', 'ddh.Ma_don_dat_hang', '=', 'pn.Ma_don_dat_hang')
            ->join('Chi_tiet_phieu_nhap as ct', 'ct.Ma_phieu_nhap', '=', 'pn.Ma_phieu_nhap')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo')
            ->join('Chi_tiet_don_dat_hang as ctdh', function ($j) {
                $j->on('ctdh.Ma_don_dat_hang', '=', 'ddh.Ma_don_dat_hang')
                    ->on('ctdh.Ma_san_pham', '=', 'l.Ma_san_pham');
            })
            ->where('pn.Loai_nhap', 'Nhập từ NCC')
            ->where('pn.Trang_thai', 'Đã nhập kho')
            ->whereBetween('pn.Thoi_diem_xac_nhan', [$startOfMonth, $endOfMonth])
            ->select(DB::raw('SUM(ct.So_luong_nhap * COALESCE(ctdh.Don_gia_mua, 0)) as total'))
            ->value('total') ?? 0);

        // 6. Hàng mất / hỏng tháng này
        $hangHongDoiLo = (float) (DB::table('Chi_tiet_phieu_xuat')
            ->where('So_luong_hong', '>', 0)
            ->whereBetween('Thoi_diem_doi_lo', [$startOfMonth, $endOfMonth])
            ->select(DB::raw('SUM(So_luong_hong * COALESCE(Gia_von_hang_hong, 0)) as total'))
            ->value('total') ?? 0);

        $hangMatHongThang = $hangHongDoiLo;

        // 5 đề xuất nhập kho chờ duyệt hoặc mới nhất
        $deXuatGanDay = DonDatHang::with(['kho', 'nhaCungCap', 'nguoiLap'])
            ->orderByRaw("CASE WHEN Trang_thai = 'Chờ phê duyệt' THEN 1 ELSE 2 END")
            ->orderBy('Ngay_lap', 'desc')
            ->orderBy('Ma_don_dat_hang', 'desc')
            ->take(5)
            ->get();

        return view('ketoan.trang-chu', compact(
            'user', 'cards',
            'giaTriTonKho', 'doanhThuThang', 'giaVonThang',
            'laiGopThang', 'giaTriNhapMua', 'hangMatHongThang',
            'deXuatGanDay'
        ));
    }

    /**
     * KD-01: Trang chủ Kinh doanh
     */
    public function kinhDoanh(): View
    {
        $user = Auth::user();

        // Action cards
        $actionData = NotificationService::getRoleActionItems($user);
        $cards = $actionData['cards'] ?? [];

        $startOfMonth = Carbon::now()->startOfMonth()->toDateTimeString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateTimeString();

        // Thống kê kinh doanh
        $tongKhachHang = KhachHang::where('Trang_thai', 'Đang giao dịch')->count();

        $donThanhCongThang = DonHangBan::where('Trang_thai', 'Hoàn thành')
            ->whereBetween('Thoi_diem_xac_nhan', [$startOfMonth, $endOfMonth])
            ->count();

        $doanhThuThang = (float) DonHangBan::where('Trang_thai', 'Hoàn thành')
            ->whereBetween('Thoi_diem_xac_nhan', [$startOfMonth, $endOfMonth])
            ->sum('Tong_tien_hang');

        // 5 đơn hàng bán gần đây nhất
        $donBanGanDay = DonHangBan::with(['khachHang', 'kho', 'nguoiLap'])
            ->orderBy('Thoi_diem_xac_nhan', 'desc')
            ->orderBy('Ma_don_hang_ban', 'desc')
            ->take(5)
            ->get();

        return view('kinhdoanh.trang-chu', compact(
            'user', 'cards',
            'tongKhachHang', 'donThanhCongThang', 'doanhThuThang',
            'donBanGanDay'
        ));
    }
}
