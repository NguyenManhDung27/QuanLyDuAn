<?php

namespace App\Http\Controllers\KeToan;

use App\Http\Controllers\Controller;
use App\Models\DonDatHang;
use App\Models\Kho;
use App\Services\ActivityLogger;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeXuatController extends Controller
{
    /**
     * KT-02: Danh sách đề xuất nhập kho (Kế toán)
     * Thấy cả 2 kho. Không hiển thị đề xuất 'Đang cập nhật' và 'Đề xuất đã hủy'.
     */
    public function index(Request $request): View
    {
        // Ánh xạ trạng thái
        $daDuyetStates = ['Chưa gửi NCC', 'Đã gửi NCC', 'Nhận một phần', 'Đã nhận đủ', 'Đóng thiếu', 'Đơn đã hủy'];
        $allowedStates = array_merge(['Chờ phê duyệt', 'Từ chối'], $daDuyetStates);

        $query = DonDatHang::with(['nhaCungCap', 'kho', 'nguoiLap'])
            ->whereIn('Trang_thai', $allowedStates);

        // Tab trạng thái (Mặc định: 'Chờ phê duyệt')
        $statusTab = $request->input('tab', 'Chờ phê duyệt');
        if ($statusTab === 'Chờ phê duyệt') {
            $query->where('Trang_thai', 'Chờ phê duyệt');
        } elseif ($statusTab === 'Từ chối') {
            $query->where('Trang_thai', 'Từ chối');
        } elseif ($statusTab === 'Đã duyệt') {
            $query->whereIn('Trang_thai', $daDuyetStates);
        }

        // Lọc từ khóa tìm kiếm (Mã đề xuất, Nhà cung cấp)
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('Ma_don_dat_hang', 'like', "%{$search}%")
                  ->orWhereHas('nhaCungCap', function ($sub) use ($search) {
                      $sub->where('Ten_ncc', 'like', "%{$search}%");
                  });
            });
        }

        // Lọc theo Kho
        $selectedKho = $request->input('kho', 'Tất cả');
        if ($selectedKho !== 'Tất cả') {
            $query->where('Ma_kho', $selectedKho);
        }

        // Lọc ngày
        if ($from = $request->input('tu_ngay')) {
            $query->whereDate('Ngay_lap', '>=', $from);
        }
        if ($to = $request->input('den_ngay')) {
            $query->whereDate('Ngay_lap', '<=', $to);
        }

        $proposals = $query->orderBy('Ngay_lap', 'desc')
            ->orderBy('Ma_don_dat_hang', 'desc')
            ->get();

        // Đếm số lượng theo tab
        $counts = [
            'Chờ phê duyệt' => DonDatHang::where('Trang_thai', 'Chờ phê duyệt')->count(),
            'Đã duyệt' => DonDatHang::whereIn('Trang_thai', $daDuyetStates)->count(),
            'Từ chối' => DonDatHang::where('Trang_thai', 'Từ chối')->count(),
            'Tất cả' => DonDatHang::whereIn('Trang_thai', $allowedStates)->count(),
        ];

        return view('ketoan.de-xuat.index', [
            'proposals' => $proposals,
            'statusTab' => $statusTab,
            'counts' => $counts,
            'khos' => Kho::all(),
            'filters' => [
                'search' => $search,
                'kho' => $selectedKho,
                'tu_ngay' => $from,
                'den_ngay' => $to,
            ],
        ]);
    }

    /**
     * KT-03: Chi tiết và duyệt đề xuất
     */
    public function show(string $ma): View
    {
        $allowedStates = ['Chờ phê duyệt', 'Từ chối', 'Chưa gửi NCC', 'Đã gửi NCC', 'Nhận một phần', 'Đã nhận đủ', 'Đóng thiếu', 'Đơn đã hủy'];

        $don = DonDatHang::with(['nhaCungCap', 'kho', 'nguoiLap', 'nguoiDuyet', 'chiTiets.sanPham'])
            ->whereIn('Trang_thai', $allowedStates)
            ->where('Ma_don_dat_hang', $ma)
            ->firstOrFail();

        $maSanPhams = $don->chiTiets->pluck('Ma_san_pham')->all();
        $maKho = $don->Ma_kho;

        // Tính các cột tham khảo:
        // 1. Tồn khả dụng kho nhận
        $tonKhaDungMap = StockService::getTonKhaDungMap($maSanPhams);

        // 2. Số lượng bán 90 ngày qua
        $ban90NgayMap = StockService::getSoLuongBan90NgayMap($maKho, $maSanPhams);

        return view('ketoan.de-xuat.show', [
            'don' => $don,
            'tonKhaDungMap' => $tonKhaDungMap,
            'ban90NgayMap' => $ban90NgayMap,
        ]);
    }

    /**
     * KT-03: Phê duyệt đề xuất
     */
    public function duyet(Request $request, string $ma): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_don_dat_hang', $ma)->firstOrFail();

        if ($don->Trang_thai !== 'Chờ phê duyệt') {
            return back()->with('error', 'Chỉ có thể duyệt đề xuất ở trạng thái Chờ phê duyệt.');
        }

        $don->update([
            'Trang_thai' => 'Chưa gửi NCC',
            'Ma_nguoi_duyet' => $user->Ma_nguoi_dung,
            'Thoi_diem_duyet' => now(),
            'Ghi_chu_phe_duyet' => $request->input('ghi_chu_phe_duyet', $don->Ghi_chu_phe_duyet),
        ]);

        ActivityLogger::log('Duyệt đề xuất', "Duyệt đề xuất {$ma} chuyển sang Chưa gửi NCC", $ma);

        return redirect()->route('ke-toan.de-xuat.index')
            ->with('success', "Đã duyệt đề xuất {$ma}. Đơn chuyển sang trạng thái Chưa gửi NCC.");
    }

    /**
     * KT-03: Từ chối đề xuất
     */
    public function tuChoi(Request $request, string $ma): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_don_dat_hang', $ma)->firstOrFail();

        if ($don->Trang_thai !== 'Chờ phê duyệt') {
            return back()->with('error', 'Chỉ có thể từ chối đề xuất ở trạng thái Chờ phê duyệt.');
        }

        $request->validate([
            'ly_do_tu_choi' => 'required|string|max:1000',
        ], [
            'ly_do_tu_choi.required' => 'Vui lòng nhập lý do từ chối.',
        ]);

        $don->update([
            'Trang_thai' => 'Từ chối',
            'Ma_nguoi_duyet' => $user->Ma_nguoi_dung,
            'Thoi_diem_duyet' => now(),
            'Ly_do_tu_choi' => $request->input('ly_do_tu_choi'),
        ]);

        ActivityLogger::log('Từ chối đề xuất', "Từ chối đề xuất {$ma}: {$request->input('ly_do_tu_choi')}", $ma);

        return redirect()->route('ke-toan.de-xuat.index')
            ->with('success', "Đã từ chối đề xuất {$ma}.");
    }
}

