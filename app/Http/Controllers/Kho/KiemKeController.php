<?php

namespace App\Http\Controllers\Kho;

use App\Http\Controllers\Controller;
use App\Models\Kho;
use App\Models\NguoiDung;
use App\Models\PhieuKiemKe;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KiemKeController extends Controller
{
    /**
     * QLK-06: Danh sách phiếu kiểm kê
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'KHO_HN';

        $query = PhieuKiemKe::with(['nguoiTao', 'nguoiKiem', 'chiTiets'])
            ->where('Ma_kho', $maKho);

        // Lọc theo trạng thái
        $tab = $request->input('trang_thai', 'all');
        if ($tab !== 'all' && !empty($tab)) {
            $query->where('Trang_thai', $tab);
        }

        // Tìm kiếm theo mã phiếu
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('Ma_phieu_kiem_ke', 'LIKE', "%{$search}%")
                  ->orWhereHas('nguoiKiem', function ($nq) use ($search) {
                      $nq->where('Ho_ten', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Đếm số lượng theo từng tab
        $counts = [
            'all'       => PhieuKiemKe::where('Ma_kho', $maKho)->count(),
            'Đang kiểm' => PhieuKiemKe::where('Ma_kho', $maKho)->where('Trang_thai', 'Đang kiểm')->count(),
            'Chờ duyệt' => PhieuKiemKe::where('Ma_kho', $maKho)->where('Trang_thai', 'Chờ duyệt')->count(),
            'Hoàn tất'  => PhieuKiemKe::where('Ma_kho', $maKho)->where('Trang_thai', 'Hoàn tất')->count(),
            'Đã hủy'    => PhieuKiemKe::where('Ma_kho', $maKho)->where('Trang_thai', 'Đang hủy')->count(),
        ];
        // Sửa 'Đã hủy'
        $counts['Đã hủy'] = PhieuKiemKe::where('Ma_kho', $maKho)->where('Trang_thai', 'Đã hủy')->count();

        $phieuKiemKes = $query->orderBy('Thoi_diem_tao', 'desc')->paginate(15)->withQueryString();

        return view('kho.kiem-ke.index', compact('phieuKiemKes', 'counts', 'tab', 'maKho'));
    }

    /**
     * QLK-07: Màn hình tạo phiếu kiểm kê
     */
    public function create(Request $request): View
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'KHO_HN';
        $kho = Kho::find($maKho);

        // 1. Lấy map lô ưu tiên kiểm (F11)
        $loUuTienMap = StockService::getLoUuTienKiemMap($maKho);

        // 2. Lấy map các lô đang bị khóa bởi phiếu kiểm kê khác
        $loDangKiemMap = StockService::getLoDangKiemKeMap($maKho);

        // 3. Lấy danh sách tồn kho các lô có tồn thực > 0
        $lotsQuery = DB::table('Ton_kho as tk')
            ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo')
            ->join('San_pham as sp', 'sp.Ma_san_pham', '=', 'l.Ma_san_pham')
            ->where('tk.Ma_kho', $maKho)
            ->where('tk.Ton_thuc', '>', 0)
            ->select(
                'tk.Ma_lo',
                'l.Ma_san_pham',
                'sp.Ten_san_pham',
                'sp.Nien_vu',
                'sp.Dung_tich',
                'sp.Loai_vang',
                'l.Ma_lo_ncc',
                'l.Ngay_san_xuat',
                'l.Han_su_dung',
                'tk.Ngay_nhap_dau_tien',
                'tk.Ton_thuc'
            );

        if ($request->filled('search')) {
            $s = trim($request->input('search'));
            $lotsQuery->where(function ($q) use ($s) {
                $q->where('sp.Ten_san_pham', 'LIKE', "%{$s}%")
                  ->orWhere('sp.Ma_san_pham', 'LIKE', "%{$s}%")
                  ->orWhere('tk.Ma_lo', 'LIKE', "%{$s}%")
                  ->orWhere('l.Ma_lo_ncc', 'LIKE', "%{$s}%");
            });
        }

        if ($request->filled('loai_vang') && $request->input('loai_vang') !== 'Tất cả') {
            $lotsQuery->where('sp.Loai_vang', $request->input('loai_vang'));
        }

        // Lấy tất cả và sắp xếp: ưu tiên kiểm F11 lên đầu, sau đó theo FIFO F12
        $rawLots = $lotsQuery->orderBy('tk.Ngay_nhap_dau_tien', 'asc')->orderBy('tk.Ma_lo', 'asc')->get();

        $lots = $rawLots->map(function ($it) use ($loUuTienMap, $loDangKiemMap) {
            $isPrio = isset($loUuTienMap[$it->Ma_lo]);
            $why = $loUuTienMap[$it->Ma_lo] ?? null;
            $lockedBy = $loDangKiemMap[$it->Ma_lo] ?? null;

            $it->is_prio = $isPrio;
            $it->why_prio = $why;
            $it->locked_by = $lockedBy;
            $it->is_locked = !empty($lockedBy);
            // Mặc định chọn nếu là ưu tiên và không bị khóa
            $it->checked = ($isPrio && empty($lockedBy));

            return $it;
        })->sortByDesc('is_prio')->values();

        // Danh sách nhân viên kho cùng kho
        $nhanVienKhos = NguoiDung::where('Ma_kho', $maKho)
            ->where('Ma_vai_tro', 'NV_KHO')
            ->where('Trang_thai', 'Hoạt động')
            ->get();

        return view('kho.kiem-ke.create', [
            'kho'          => $kho,
            'lots'         => $lots,
            'nvKhos'       => $nhanVienKhos,
            'loUuTienMap'  => $loUuTienMap,
            'loDangKiemMap'=> $loDangKiemMap,
            'maPKK'        => \App\Services\CodeGenerator::generateMaPhieuKiemKe(),
        ]);
    }

    /**
     * QLK-07: Lưu tạo phiếu kiểm kê
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'KHO_HN';

        $request->validate([
            'ma_los' => 'required|array|min:1',
            'ma_los.*' => 'required|string',
            'ma_nguoi_kiem' => 'nullable|string|exists:Nguoi_dung,Ma_nguoi_dung',
            'ghi_chu' => 'nullable|string|max:1000',
        ], [
            'ma_los.required' => 'Vui lòng chọn ít nhất một lô để kiểm kê.',
            'ma_los.min' => 'Vui lòng chọn ít nhất một lô để kiểm kê.',
        ]);

        try {
            $pkk = StockService::taoPhieuKiemKe(
                $user,
                $maKho,
                $request->input('ma_los'),
                $request->input('ma_nguoi_kiem'),
                $request->input('ghi_chu')
            );

            return redirect()->route('kho.kiem-ke.show', $pkk->Ma_phieu_kiem_ke)
                ->with('success', "Đã tạo phiếu kiểm kê {$pkk->Ma_phieu_kiem_ke} thành công.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * QLK-08: Xem chi tiết phiếu kiểm kê & duyệt
     */
    public function show(string $ma, Request $request): View
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho;

        $query = PhieuKiemKe::with([
            'kho',
            'nguoiTao',
            'nguoiKiem',
            'nguoiDuyet',
            'chiTiets.lo.sanPham'
        ])->where('Ma_phieu_kiem_ke', $ma);

        if ($maKho) {
            $query->where('Ma_kho', $maKho);
        }

        $pkk = $query->firstOrFail();

        // Lấy thông tin ưu tiên tại thời điểm tạo phiếu
        $loUuTienMap = StockService::getLoUuTienKiemMap($pkk->Ma_kho, $pkk->Thoi_diem_tao);

        $onlyDiff = $request->boolean('only_diff', false);

        $details = $pkk->chiTiets->map(function ($ct) use ($loUuTienMap) {
            $ct->is_prio = isset($loUuTienMap[$ct->Ma_lo]);
            $ct->why_prio = $loUuTienMap[$ct->Ma_lo] ?? null;
            return $ct;
        });

        if ($onlyDiff) {
            $details = $details->filter(function ($ct) {
                return $ct->So_luong_thuc_te !== null && $ct->So_luong_thuc_te != $ct->So_luong_he_thong;
            })->values();
        }

        return view('kho.kiem-ke.show', compact('pkk', 'details', 'loUuTienMap', 'onlyDiff'));
    }

    /**
     * QLK-08: Duyệt và điều chỉnh tồn kho
     */
    public function duyet(string $ma): RedirectResponse
    {
        $user = auth()->user();
        $pkk = PhieuKiemKe::where('Ma_phieu_kiem_ke', $ma)
            ->where('Ma_kho', $user->Ma_kho ?? 'KHO_HN')
            ->firstOrFail();

        try {
            StockService::duyetKiemKe($pkk, $user);
            return redirect()->route('kho.kiem-ke.show', $ma)
                ->with('success', 'Đã phê duyệt và điều chỉnh tồn kho thành công theo kết quả kiểm kê.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * QLK-08: Không duyệt (yêu cầu đếm lại)
     */
    public function khongDuyet(string $ma, Request $request): RedirectResponse
    {
        $user = auth()->user();
        $pkk = PhieuKiemKe::where('Ma_phieu_kiem_ke', $ma)
            ->where('Ma_kho', $user->Ma_kho ?? 'KHO_HN')
            ->firstOrFail();

        $request->validate([
            'ly_do_khong_duyet' => 'required|string|max:1000',
        ], [
            'ly_do_khong_duyet.required' => 'Bắt buộc nhập lý do không duyệt.',
        ]);

        try {
            StockService::khongDuyetKiemKe($pkk, $user, $request->input('ly_do_khong_duyet'));
            return redirect()->route('kho.kiem-ke.show', $ma)
                ->with('success', 'Đã từ chối duyệt. Phiếu được chuyển về trạng thái Đang kiểm để nhân viên kho đếm lại.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * QLK-08: Hủy phiếu kiểm kê
     */
    public function huy(string $ma, Request $request): RedirectResponse
    {
        $user = auth()->user();
        $pkk = PhieuKiemKe::where('Ma_phieu_kiem_ke', $ma)
            ->where('Ma_kho', $user->Ma_kho ?? 'KHO_HN')
            ->firstOrFail();

        $request->validate([
            'ly_do_huy' => 'required|string|max:1000',
        ], [
            'ly_do_huy.required' => 'Bắt buộc nhập lý do hủy phiếu.',
        ]);

        try {
            StockService::huyPhieuKiemKe($pkk, $user, $request->input('ly_do_huy'));
            return redirect()->route('kho.kiem-ke.show', $ma)
                ->with('success', 'Đã hủy phiếu kiểm kê.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

