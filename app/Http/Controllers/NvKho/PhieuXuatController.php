<?php

namespace App\Http\Controllers\NvKho;

use App\Http\Controllers\Controller;
use App\Models\ChiTietPhieuXuat;
use App\Models\Lo;
use App\Models\PhieuXuatKho;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PhieuXuatController extends Controller
{
    /**
     * NVK-07: Danh sách phiếu xuất kho
     */
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $maKho     = $user->Ma_kho;
        $tab       = $request->input('tab', 'all');
        $search    = $request->input('search', '');
        $tuNgay    = $request->input('tu_ngay', '');
        $denNgay   = $request->input('den_ngay', '');

        $query = PhieuXuatKho::with(['donHangBan.khachHang', 'chiTiets.loThucXuat.sanPham', 'chiTiets.loPhanBo.sanPham'])
            ->whereHas('donHangBan', function ($q) use ($maKho) {
                if ($maKho) {
                    $q->where('Ma_kho', $maKho);
                }
            })
            ->orderByDesc('Ngay_tao')
            ->orderByDesc('Ma_phieu_xuat');

        if ($tab !== 'all' && !empty($tab)) {
            $query->where('Trang_thai_giao_hang', $tab);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Ma_phieu_xuat', 'like', "%{$search}%")
                  ->orWhere('Ma_van_don', 'like', "%{$search}%")
                  ->orWhereHas('donHangBan', function ($q2) use ($search) {
                      $q2->where('Ma_don_hang_ban', 'like', "%{$search}%")
                         ->orWhere('Ten_nguoi_nhan', 'like', "%{$search}%")
                         ->orWhereHas('khachHang', fn ($q3) => $q3->where('Ten_khach_hang', 'like', "%{$search}%"));
                  });
            });
        }

        if ($tuNgay) {
            $query->whereDate('Ngay_tao', '>=', $tuNgay);
        }
        if ($denNgay) {
            $query->whereDate('Ngay_tao', '<=', $denNgay);
        }

        $phieuXuats = $query->paginate(20)->withQueryString();

        // Đếm theo từng tab
        $counts = [
            'all' => PhieuXuatKho::whereHas('donHangBan', fn ($q) => $maKho ? $q->where('Ma_kho', $maKho) : $q)->count(),
            'Đang chuẩn bị hàng' => PhieuXuatKho::where('Trang_thai_giao_hang', 'Đang chuẩn bị hàng')->whereHas('donHangBan', fn ($q) => $maKho ? $q->where('Ma_kho', $maKho) : $q)->count(),
            'Đang giao' => PhieuXuatKho::where('Trang_thai_giao_hang', 'Đang giao')->whereHas('donHangBan', fn ($q) => $maKho ? $q->where('Ma_kho', $maKho) : $q)->count(),
            'Giao thành công' => PhieuXuatKho::where('Trang_thai_giao_hang', 'Giao thành công')->whereHas('donHangBan', fn ($q) => $maKho ? $q->where('Ma_kho', $maKho) : $q)->count(),
            'Giao hàng thất bại' => PhieuXuatKho::where('Trang_thai_giao_hang', 'Giao hàng thất bại')->whereHas('donHangBan', fn ($q) => $maKho ? $q->where('Ma_kho', $maKho) : $q)->count(),
            'Đã hủy' => PhieuXuatKho::where('Trang_thai_giao_hang', 'Đã hủy')->whereHas('donHangBan', fn ($q) => $maKho ? $q->where('Ma_kho', $maKho) : $q)->count(),
        ];

        // Tính cờ thiếu tồn F10 cho từng phiếu
        $thieu = [];
        foreach ($phieuXuats as $px) {
            if ($px->Trang_thai_giao_hang === 'Đang chuẩn bị hàng') {
                $thieu[$px->Ma_phieu_xuat] = StockService::isThieuTon($px->Ma_phieu_xuat, $px->donHangBan->Ma_kho);
            }
        }

        return view('nvkho.phieu-xuat.index', compact('phieuXuats', 'tab', 'search', 'tuNgay', 'denNgay', 'counts', 'thieu'));
    }

    /**
     * NVK-08: Chi tiết phiếu xuất kho
     */
    public function show(string $ma): View
    {
        $user  = auth()->user();
        $maKho = $user->Ma_kho;

        $phieuXuat = PhieuXuatKho::with([
            'donHangBan.khachHang',
            'donHangBan.kho',
            'nguoiXuat',
            'chiTiets.loPhanBo.sanPham',
            'chiTiets.loThucXuat.sanPham',
            'chiTiets.nguoiDoiLo',
        ])
        ->whereHas('donHangBan', function ($q) use ($maKho) {
            if ($maKho) {
                $q->where('Ma_kho', $maKho);
            }
        })
        ->where('Ma_phieu_xuat', $ma)
        ->firstOrFail();

        $thieu = StockService::isThieuTon($phieuXuat->Ma_phieu_xuat, $phieuXuat->donHangBan->Ma_kho);

        return view('nvkho.phieu-xuat.show', compact('phieuXuat', 'thieu'));
    }

    /**
     * NVK-08: Xác nhận xuất kho
     */
    public function xuatKho(Request $request, string $ma): RedirectResponse
    {
        $user  = auth()->user();
        $maKho = $user->Ma_kho;

        $phieuXuat = PhieuXuatKho::whereHas('donHangBan', function ($q) use ($maKho) {
            if ($maKho) {
                $q->where('Ma_kho', $maKho);
            }
        })->where('Ma_phieu_xuat', $ma)->firstOrFail();

        $request->validate([
            'don_vi_van_chuyen' => 'required|string|max:100',
            'ma_van_don'        => 'required|string|max:50',
        ], [
            'don_vi_van_chuyen.required' => 'Bắt buộc nhập Đơn vị vận chuyển trước khi xuất kho.',
            'ma_van_don.required'        => 'Bắt buộc nhập Mã vận đơn trước khi xuất kho.',
        ]);

        try {
            StockService::xuatKho(
                $phieuXuat,
                $user,
                $request->input('don_vi_van_chuyen'),
                $request->input('ma_van_don')
            );

            return redirect()->route('nv-kho.phieu-xuat.show', $ma)
                ->with('success', "Đã xác nhận xuất kho thành công cho phiếu {$ma}.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * NVK-08: Đổi lô
     */
    public function doiLo(Request $request, string $ma): RedirectResponse
    {
        $user  = auth()->user();
        $maKho = $user->Ma_kho;

        $phieuXuat = PhieuXuatKho::whereHas('donHangBan', function ($q) use ($maKho) {
            if ($maKho) {
                $q->where('Ma_kho', $maKho);
            }
        })->where('Ma_phieu_xuat', $ma)->firstOrFail();

        $request->validate([
            'ma_lo_phan_bo'   => 'required|string|exists:Lo,Ma_lo',
            'ma_lo_thuc_xuat' => 'required|string|exists:Lo,Ma_lo',
            'so_luong_doi'    => 'required|integer|min:1',
            'ly_do_doi_lo'    => 'required|in:Lấy lô khác cho tiện,Không tìm thấy lô gốc,Hỏng hoặc vỡ,Khác',
            'so_luong_hong'   => 'nullable|integer|min:1',
            'replacements'    => 'required|array|min:1',
            'replacements.*.ma_lo'   => 'required|exists:Lo,Ma_lo',
            'replacements.*.so_luong'=> 'required|integer|min:1',
        ]);

        try {
            StockService::doiLo(
                $phieuXuat,
                $user,
                $request->input('ma_lo_phan_bo'),
                $request->input('ma_lo_thuc_xuat'),
                (int) $request->input('so_luong_doi'),
                $request->input('ly_do_doi_lo'),
                $request->filled('so_luong_hong') ? (int) $request->input('so_luong_hong') : null,
                $request->input('replacements', [])
            );

            return redirect()->route('nv-kho.phieu-xuat.show', $ma)
                ->with('success', 'Đã đổi lô xuất thành công.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * NVK-08: Sửa thông tin vận chuyển
     */
    public function updateVanChuyen(Request $request, string $ma): RedirectResponse
    {
        $user  = auth()->user();
        $maKho = $user->Ma_kho;

        $phieuXuat = PhieuXuatKho::whereHas('donHangBan', function ($q) use ($maKho) {
            if ($maKho) {
                $q->where('Ma_kho', $maKho);
            }
        })->where('Ma_phieu_xuat', $ma)->firstOrFail();

        $request->validate([
            'don_vi_van_chuyen' => 'required|string|max:100',
            'ma_van_don'        => 'required|string|max:50',
        ]);

        try {
            StockService::capNhatVanChuyen(
                $phieuXuat,
                $user,
                $request->input('don_vi_van_chuyen'),
                $request->input('ma_van_don')
            );

            return redirect()->route('nv-kho.phieu-xuat.show', $ma)
                ->with('success', 'Đã cập nhật thông tin vận chuyển.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * NVK-08: Cập nhật kết quả giao hàng
     */
    public function capNhatGiaoHang(Request $request, string $ma): RedirectResponse
    {
        $user  = auth()->user();
        $maKho = $user->Ma_kho;

        $phieuXuat = PhieuXuatKho::whereHas('donHangBan', function ($q) use ($maKho) {
            if ($maKho) {
                $q->where('Ma_kho', $maKho);
            }
        })->where('Ma_phieu_xuat', $ma)->firstOrFail();

        $request->validate([
            'ket_qua'            => 'required|in:Giao thành công,Giao hàng thất bại',
            'ly_do_giao_that_bai'=> 'required_if:ket_qua,Giao hàng thất bại|nullable|string|max:1000',
        ], [
            'ly_do_giao_that_bai.required_if' => 'Bắt buộc nhập lý do khi giao hàng thất bại.',
        ]);

        try {
            StockService::capNhatKetQuaGiao(
                $phieuXuat,
                $user,
                $request->input('ket_qua'),
                $request->input('ly_do_giao_that_bai')
            );

            return redirect()->route('nv-kho.phieu-xuat.show', $ma)
                ->with('success', 'Đã cập nhật kết quả giao hàng.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * AJAX API: Lấy danh sách lô thay thế cho sản phẩm để chọn đổi lô
     */
    public function apiLoThayThe(Request $request, string $ma): JsonResponse
    {
        $user  = auth()->user();
        $maKho = $user->Ma_kho;

        $phieuXuat = PhieuXuatKho::where('Ma_phieu_xuat', $ma)->firstOrFail();
        $maLoGoc   = $request->input('ma_lo_goc');
        $loGoc     = Lo::findOrFail($maLoGoc);
        $maSp      = $loGoc->Ma_san_pham;

        // Lấy tất cả lô cùng sản phẩm, cùng kho, sắp theo FIFO F12
        $lots = DB::table('Ton_kho as tk')
            ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo')
            ->where('tk.Ma_kho', $maKho)
            ->where('l.Ma_san_pham', $maSp)
            ->where('tk.Ma_lo', '!=', $maLoGoc)
            ->orderBy('tk.Ngay_nhap_dau_tien')
            ->orderBy('tk.Ma_lo')
            ->select('tk.Ma_lo', 'tk.Ton_thuc', 'tk.Ngay_nhap_dau_tien', 'l.Han_su_dung', 'l.Ma_lo_ncc')
            ->get();

        $data = [];
        $tongKhaDung = 0;

        foreach ($lots as $l) {
            // Tính số đang giữ trên lô này (trừ cho chính phiếu xuất này nếu có)
            $dangGiu = (int) DB::table('Chi_tiet_phieu_xuat as ct')
                ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'ct.Ma_phieu_xuat')
                ->join('Don_hang_ban as db', 'db.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
                ->where('px.Trang_thai_giao_hang', 'Đang chuẩn bị hàng')
                ->where('db.Ma_kho', $maKho)
                ->where(function ($q) use ($l) {
                    $q->where('ct.Ma_lo_thuc_xuat', $l->Ma_lo)
                      ->orWhere(function ($q2) use ($l) {
                          $q2->where('ct.Ma_lo_phan_bo', $l->Ma_lo)->whereNull('ct.Ma_lo_thuc_xuat');
                      });
                })
                ->sum('ct.So_luong');

            $khaDung = max(0, (int) $l->Ton_thuc - $dangGiu);

            // Kiểm tra lô có đang kiểm kê không
            $biKiemKe = StockService::kiemTraLoDangKiemKe($maKho, [$l->Ma_lo]);

            if ($khaDung > 0 && empty($biKiemKe)) {
                $tongKhaDung += $khaDung;
                $data[] = [
                    'ma_lo'              => $l->Ma_lo,
                    'ma_lo_ncc'          => $l->Ma_lo_ncc,
                    'ngay_nhap_dau_tien' => $l->Ngay_nhap_dau_tien ? date('d/m/Y', strtotime($l->Ngay_nhap_dau_tien)) : '—',
                    'han_su_dung'        => $l->Han_su_dung ? date('d/m/Y', strtotime($l->Han_su_dung)) : '—',
                    'ton_thuc'           => (int) $l->Ton_thuc,
                    'kha_dung'           => $khaDung,
                ];
            }
        }

        return response()->json([
            'lots'          => $data,
            'tong_kha_dung' => $tongKhaDung,
        ]);
    }
}

