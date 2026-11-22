<?php

namespace App\Http\Controllers\NvKho;

use App\Http\Controllers\Controller;
use App\Models\PhieuKiemKe;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KiemKeController extends Controller
{
    /**
     * NVK-09: Danh sách phiếu kiểm kê của Nhân viên kho
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'KHO_HN';

        $baseQuery = PhieuKiemKe::with(['nguoiTao', 'nguoiKiem', 'chiTiets'])
            ->where('Ma_kho', $maKho)
            ->where(function ($q) use ($user) {
                $q->where('Ma_nguoi_kiem', $user->Ma_nguoi_dung)
                  ->orWhereNull('Ma_nguoi_kiem');
            });

        // Tabs
        $tab = $request->input('tab', 'can_dem');

        $query = clone $baseQuery;
        switch ($tab) {
            case 'cho_duyet':
                $query->where('Trang_thai', 'Chờ duyệt');
                break;
            case 'hoan_tat':
                $query->where('Trang_thai', 'Hoàn tất');
                break;
            case 'da_huy':
                $query->where('Trang_thai', 'Đã hủy');
                break;
            case 'can_dem':
            default:
                $tab = 'can_dem';
                $query->where('Trang_thai', 'Đang kiểm');
                break;
        }

        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $query->where('Ma_phieu_kiem_ke', 'LIKE', "%{$s}%");
        }

        $counts = [
            'can_dem'   => (clone $baseQuery)->where('Trang_thai', 'Đang kiểm')->count(),
            'cho_duyet' => (clone $baseQuery)->where('Trang_thai', 'Chờ duyệt')->count(),
            'hoan_tat'  => (clone $baseQuery)->where('Trang_thai', 'Hoàn tất')->count(),
            'da_huy'    => (clone $baseQuery)->where('Trang_thai', 'Đã hủy')->count(),
        ];

        $phieuKiemKes = $query->orderBy('Thoi_diem_tao', 'desc')->paginate(15)->withQueryString();

        return view('nvkho.kiem-ke.index', compact('phieuKiemKes', 'counts', 'tab', 'user'));
    }

    /**
     * NVK-10: Màn hình đếm kiểm kê
     */
    public function dem(string $ma): View|RedirectResponse
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'KHO_HN';

        $pkk = PhieuKiemKe::with([
            'kho',
            'nguoiTao',
            'nguoiKiem',
            'chiTiets.lo.sanPham'
        ])
        ->where('Ma_phieu_kiem_ke', $ma)
        ->where('Ma_kho', $maKho)
        ->where(function ($q) use ($user) {
            $q->where('Ma_nguoi_kiem', $user->Ma_nguoi_dung)
              ->orWhereNull('Ma_nguoi_kiem');
        })
        ->firstOrFail();

        // Lấy thông tin lô ưu tiên F11
        $loUuTienMap = StockService::getLoUuTienKiemMap($maKho, $pkk->Thoi_diem_tao);

        // Sắp xếp các dòng: Ưu tiên kiểm lên đầu, sau đó theo FIFO F12
        $details = $pkk->chiTiets->map(function ($ct) use ($loUuTienMap) {
            $ct->is_prio = isset($loUuTienMap[$ct->Ma_lo]);
            $ct->why_prio = $loUuTienMap[$ct->Ma_lo] ?? null;
            return $ct;
        })->sortByDesc('is_prio')->values();

        return view('nvkho.kiem-ke.dem', compact('pkk', 'details', 'user'));
    }

    /**
     * NVK-10: Lưu kết quả đếm (Lưu tạm hoặc Hoàn thành đếm)
     */
    public function luuDem(string $ma, Request $request): RedirectResponse
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'KHO_HN';

        $pkk = PhieuKiemKe::where('Ma_phieu_kiem_ke', $ma)
            ->where('Ma_kho', $maKho)
            ->where(function ($q) use ($user) {
                $q->where('Ma_nguoi_kiem', $user->Ma_nguoi_dung)
                  ->orWhereNull('Ma_nguoi_kiem');
            })
            ->firstOrFail();

        $action = $request->input('action', 'luu_tam');
        $isHoanThanh = ($action === 'hoan_thanh');
        $items = $request->input('items', []);

        try {
            $result = StockService::luuDemKiemKe($pkk, $user, $items, $isHoanThanh);

            if ($isHoanThanh) {
                return redirect()->route('nv-kho.kiem-ke.index')
                    ->with('success', $result['message']);
            }

            return redirect()->route('nv-kho.kiem-ke.dem', $ma)
                ->with('success', $result['message']);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}

