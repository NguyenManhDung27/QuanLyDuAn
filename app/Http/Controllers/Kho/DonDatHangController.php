<?php

namespace App\Http\Controllers\Kho;

use App\Http\Controllers\Controller;
use App\Models\DonDatHang;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DonDatHangController extends Controller
{
    /**
     * QLK-05: Danh sách đơn đặt hàng
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'HN';

        $query = DonDatHang::with(['nhaCungCap', 'chiTiets', 'phieuNhapKhos'])
            ->where('Ma_kho', $maKho)
            ->whereIn('Trang_thai', ['Chưa gửi NCC', 'Đã gửi NCC', 'Nhận một phần', 'Đã nhận đủ', 'Đóng thiếu', 'Đơn đã hủy']);

        // Tab trạng thái
        $statusTab = $request->input('tab', 'Tất cả');
        if ($statusTab !== 'Tất cả') {
            $query->where('Trang_thai', $statusTab);
        }

        // Lọc từ khóa tìm kiếm (Mã đơn, Tên nhà cung cấp)
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('Ma_don_dat_hang', 'like', "%{$search}%")
                  ->orWhereHas('nhaCungCap', function ($sub) use ($search) {
                      $sub->where('Ten_ncc', 'like', "%{$search}%");
                  });
            });
        }

        // Lọc "Chỉ hiện đơn cần xem xét" (F9)
        $canXemXetOnly = $request->boolean('can_xem_xet');
        if ($canXemXetOnly) {
            $today = now()->toDateString();
            $query->whereIn('Trang_thai', ['Đã gửi NCC', 'Nhận một phần'])
                ->where(function ($q) use ($today) {
                    $q->where('Ngay_du_kien_nhan_du', '<', $today)
                      ->orWhereHas('phieuNhapKhos', function ($sub) {
                          $sub->where('Trang_thai', 'Đã nhập kho')->where('Dot_giao_cuoi', true);
                      });
                });
        }

        $orders = $query->orderBy('Ngay_lap', 'desc')
            ->orderBy('Ma_don_dat_hang', 'desc')
            ->get();

        // Đếm số lượng cho từng tab
        $counts = [
            'Tất cả' => DonDatHang::where('Ma_kho', $maKho)
                ->whereIn('Trang_thai', ['Chưa gửi NCC', 'Đã gửi NCC', 'Nhận một phần', 'Đã nhận đủ', 'Đóng thiếu', 'Đơn đã hủy'])
                ->count(),
            'Chưa gửi NCC' => DonDatHang::where('Ma_kho', $maKho)->where('Trang_thai', 'Chưa gửi NCC')->count(),
            'Đã gửi NCC' => DonDatHang::where('Ma_kho', $maKho)->where('Trang_thai', 'Đã gửi NCC')->count(),
            'Nhận một phần' => DonDatHang::where('Ma_kho', $maKho)->where('Trang_thai', 'Nhận một phần')->count(),
            'Đã nhận đủ' => DonDatHang::where('Ma_kho', $maKho)->where('Trang_thai', 'Đã nhận đủ')->count(),
            'Đóng thiếu' => DonDatHang::where('Ma_kho', $maKho)->where('Trang_thai', 'Đóng thiếu')->count(),
            'Đơn đã hủy' => DonDatHang::where('Ma_kho', $maKho)->where('Trang_thai', 'Đơn đã hủy')->count(),
        ];

        return view('kho.don-dat-hang.index', [
            'orders' => $orders,
            'statusTab' => $statusTab,
            'counts' => $counts,
            'filters' => [
                'search' => $search,
                'can_xem_xet' => $canXemXetOnly,
            ],
        ]);
    }

    /**
     * Xác nhận đã gửi NCC (Chuyển trạng thái từ 'Chưa gửi NCC' -> 'Đã gửi NCC')
     */
    public function xacNhanGuiNcc(string $ma): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        if ($don->Trang_thai !== 'Chưa gửi NCC') {
            return back()->with('error', 'Chỉ có thể xác nhận gửi NCC cho đơn ở trạng thái Chưa gửi NCC.');
        }

        $don->update([
            'Trang_thai' => 'Đã gửi NCC',
            'Ma_nguoi_gui_ncc' => $user->Ma_nguoi_dung,
            'Ngay_gui_ncc' => now()->toDateString(),
        ]);

        ActivityLogger::log('Xác nhận gửi NCC', "Xác nhận đã gửi đơn đặt hàng {$ma} cho nhà cung cấp", $ma);

        return back()->with('success', "Đã xác nhận gửi đơn {$ma} cho nhà cung cấp.");
    }

    /**
     * Hủy đơn đặt hàng chưa gửi NCC
     */
    public function huyDon(Request $request, string $ma): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        if ($don->Trang_thai !== 'Chưa gửi NCC') {
            return back()->with('error', 'Chỉ có thể hủy đơn đặt hàng khi chưa gửi NCC.');
        }

        $request->validate([
            'ly_do_huy' => 'required|string|max:1000',
        ], [
            'ly_do_huy.required' => 'Vui lòng nhập lý do hủy đơn.',
        ]);

        $don->update([
            'Trang_thai' => 'Đơn đã hủy',
            'Ma_nguoi_huy' => $user->Ma_nguoi_dung,
            'Thoi_diem_huy' => now(),
            'Ly_do_huy' => $request->input('ly_do_huy'),
        ]);

        ActivityLogger::log('Hủy đơn đặt hàng', "Hủy đơn đặt hàng {$ma}: {$request->input('ly_do_huy')}", $ma);

        return redirect()->route('kho.don-dat-hang.index')->with('success', "Đã hủy đơn đặt hàng {$ma}.");
    }

    /**
     * Cập nhật ngày dự kiến nhận đủ (khi Đã gửi NCC hoặc Nhận một phần)
     */
    public function capNhatNgayDuKien(Request $request, string $ma): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        if (!in_array($don->Trang_thai, ['Đã gửi NCC', 'Nhận một phần'])) {
            return back()->with('error', 'Chỉ có thể cập nhật ngày dự kiến cho đơn Đã gửi NCC hoặc Nhận một phần.');
        }

        $request->validate([
            'ngay_du_kien_nhan_du' => 'required|date',
        ], [
            'ngay_du_kien_nhan_du.required' => 'Vui lòng chọn ngày dự kiến nhận đủ mới.',
        ]);

        $oldDate = $don->Ngay_du_kien_nhan_du ? $don->Ngay_du_kien_nhan_du->format('d/m/Y') : 'Chưa có';
        $don->update(['Ngay_du_kien_nhan_du' => $request->input('ngay_du_kien_nhan_du')]);

        ActivityLogger::log('Cập nhật ngày dự kiến', "Cập nhật ngày dự kiến nhận đủ đơn {$ma}: {$oldDate} -> " . date('d/m/Y', strtotime($request->input('ngay_du_kien_nhan_du'))), $ma);

        return back()->with('success', "Đã cập nhật ngày dự kiến nhận đủ cho đơn {$ma}.");
    }

    /**
     * Đóng thiếu đơn đặt hàng
     */
    public function dongThieu(Request $request, string $ma): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        if (!in_array($don->Trang_thai, ['Đã gửi NCC', 'Nhận một phần'])) {
            return back()->with('error', 'Chỉ có thể đóng thiếu đơn ở trạng thái Đã gửi NCC hoặc Nhận một phần.');
        }

        // Kiểm tra không có phiếu nhập nào của đơn đang ở trạng thái 'Đang cập nhật'
        $hasPending = $don->phieuNhapKhos()->where('Trang_thai', 'Đang cập nhật')->exists();
        if ($hasPending) {
            return back()->with('error', 'Đơn đang có phiếu nhập ở trạng thái Đang cập nhật. Vui lòng xác nhận hoặc xóa phiếu nhập trước khi đóng thiếu.');
        }

        $request->validate([
            'ly_do_dong_thieu' => 'required|string|max:1000',
        ], [
            'ly_do_dong_thieu.required' => 'Vui lòng nhập lý do đóng thiếu.',
        ]);

        $don->update([
            'Trang_thai' => 'Đóng thiếu',
            'Ma_nguoi_dong_thieu' => $user->Ma_nguoi_dung,
            'Thoi_diem_dong_thieu' => now(),
            'Ly_do_dong_thieu' => $request->input('ly_do_dong_thieu'),
        ]);

        ActivityLogger::log('Đóng thiếu đơn', "Đóng thiếu đơn đặt hàng {$ma}: {$request->input('ly_do_dong_thieu')}", $ma);

        return back()->with('success', "Đã đóng thiếu đơn đặt hàng {$ma}.");
    }
}

