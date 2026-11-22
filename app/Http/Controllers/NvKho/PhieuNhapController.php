<?php

namespace App\Http\Controllers\NvKho;

use App\Http\Controllers\Controller;
use App\Models\PhieuNhapKho;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhieuNhapController extends Controller
{
    /**
     * NVK-02: Danh sách phiếu nhập kho
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'HN';

        $query = PhieuNhapKho::with(['nguoiLap', 'donDatHang', 'phieuXuatKho', 'chiTiets.lo'])
            ->where('Ma_kho', $maKho);

        // Tab lọc theo trạng thái
        $statusTab = $request->input('tab', 'Tất cả');
        if ($statusTab === 'Đang cập nhật') {
            $query->where('Trang_thai', 'Đang cập nhật');
        } elseif ($statusTab === 'Đã nhập kho') {
            $query->where('Trang_thai', 'Đã nhập kho');
        }

        // Lọc loại nhập
        $loaiNhap = $request->input('loai_nhap', 'Tất cả');
        if ($loaiNhap !== 'Tất cả') {
            $query->where('Loai_nhap', $loaiNhap);
        }

        // Lọc từ khóa tìm kiếm (Mã phiếu, Mã đơn đặt hàng, Mã phiếu xuất)
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('Ma_phieu_nhap', 'like', "%{$search}%")
                  ->orWhere('Ma_don_dat_hang', 'like', "%{$search}%")
                  ->orWhere('Ma_phieu_xuat', 'like', "%{$search}%");
            });
        }

        // Lọc theo ngày lập
        if ($from = $request->input('tu_ngay')) {
            $query->whereDate('Ngay_lap', '>=', $from);
        }
        if ($to = $request->input('den_ngay')) {
            $query->whereDate('Ngay_lap', '<=', $to);
        }

        $slips = $query->orderBy('Ngay_lap', 'desc')
            ->orderBy('Ma_phieu_nhap', 'desc')
            ->get();

        // Kiểm tra xem mỗi phiếu có lô nào đang kiểm kê không
        $blockedMap = [];
        foreach ($slips as $slip) {
            if ($slip->Trang_thai === 'Đang cập nhật') {
                $maLos = $slip->chiTiets->pluck('Ma_lo')->unique()->all();
                $blocked = StockService::kiemTraLoDangKiemKe($maKho, $maLos);
                $blockedMap[$slip->Ma_phieu_nhap] = !empty($blocked);
            } else {
                $blockedMap[$slip->Ma_phieu_nhap] = false;
            }
        }

        // Đếm số lượng theo tab
        $counts = [
            'Tất cả' => PhieuNhapKho::where('Ma_kho', $maKho)->count(),
            'Đang cập nhật' => PhieuNhapKho::where('Ma_kho', $maKho)->where('Trang_thai', 'Đang cập nhật')->count(),
            'Đã nhập kho' => PhieuNhapKho::where('Ma_kho', $maKho)->where('Trang_thai', 'Đã nhập kho')->count(),
        ];

        return view('nvkho.phieu-nhap.index', [
            'slips' => $slips,
            'statusTab' => $statusTab,
            'loaiNhap' => $loaiNhap,
            'counts' => $counts,
            'blockedMap' => $blockedMap,
            'filters' => [
                'search' => $search,
                'loai_nhap' => $loaiNhap,
                'tu_ngay' => $from,
                'den_ngay' => $to,
            ],
        ]);
    }

    /**
     * Chi tiết phiếu nhập kho
     */
    public function show(string $ma): View
    {
        $user = auth()->user();
        $slip = PhieuNhapKho::with([
            'kho',
            'nguoiLap',
            'donDatHang.nhaCungCap',
            'phieuXuatKho.donHangBan.khachHang',
            'chiTiets.lo.sanPham'
        ])
        ->where('Ma_kho', $user->Ma_kho)
        ->where('Ma_phieu_nhap', $ma)
        ->firstOrFail();

        $maLos = $slip->chiTiets->pluck('Ma_lo')->unique()->all();
        $blocked = StockService::kiemTraLoDangKiemKe($slip->Ma_kho, $maLos);

        return view('nvkho.phieu-nhap.show', [
            'slip' => $slip,
            'blocked' => $blocked,
        ]);
    }

    /**
     * Xác nhận nhập kho
     */
    public function xacNhan(string $ma): RedirectResponse
    {
        $user = auth()->user();
        $slip = PhieuNhapKho::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_phieu_nhap', $ma)
            ->firstOrFail();

        if ($slip->Trang_thai !== 'Đang cập nhật') {
            return back()->with('error', 'Phiếu nhập kho này đã được xác nhận trước đó.');
        }

        try {
            StockService::nhapKho($slip);
            return redirect()->route('nv-kho.phieu-nhap.show', $ma)
                ->with('success', "Đã xác nhận nhập kho thành công cho phiếu {$ma}. Tồn kho và giá vốn đã được cập nhật.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Xóa phiếu nhập kho đang cập nhật
     */
    public function destroy(string $ma): RedirectResponse
    {
        $user = auth()->user();
        $slip = PhieuNhapKho::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_phieu_nhap', $ma)
            ->firstOrFail();

        if ($slip->Trang_thai !== 'Đang cập nhật') {
            return back()->with('error', 'Chỉ có thể xóa phiếu nhập kho ở trạng thái Đang cập nhật.');
        }

        \DB::transaction(function () use ($slip, $ma) {
            $slip->chiTiets()->delete();
            $slip->delete();
            ActivityLogger::log('Xóa phiếu nhập', 'Phiếu nhập kho', "Xóa phiếu nhập kho {$ma}", $slip);
        });

        return redirect()->route('nv-kho.phieu-nhap.index')
            ->with('success', "Đã xóa phiếu nhập kho {$ma}.");
    }
}

