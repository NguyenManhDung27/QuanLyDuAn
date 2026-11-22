<?php

namespace App\Http\Controllers\NvKho;

use App\Http\Controllers\Controller;
use App\Models\ChiTietPhieuNhap;
use App\Models\DonDatHang;
use App\Models\Lo;
use App\Models\PhieuNhapKho;
use App\Models\PhieuXuatKho;
use App\Services\ActivityLogger;
use App\Services\CodeGenerator;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NhapKhoController extends Controller
{
    /**
     * NVK-03: Màn hình chọn chứng từ nhập kho
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'HN';

        $loai = $request->input('loai', 'Nhập từ NCC');
        $search = trim($request->input('search', ''));

        // 1. Nhập từ NCC: Đơn đặt hàng đang chờ nhận (Đã gửi NCC, Nhận một phần)
        $queryOrders = DonDatHang::with(['nhaCungCap', 'chiTiets'])
            ->where('Ma_kho', $maKho)
            ->whereIn('Trang_thai', ['Đã gửi NCC', 'Nhận một phần']);

        if ($search && $loai === 'Nhập từ NCC') {
            $queryOrders->where(function ($q) use ($search) {
                $q->where('Ma_don_dat_hang', 'like', "%{$search}%")
                  ->orWhereHas('nhaCungCap', function ($sub) use ($search) {
                      $sub->where('Ten_ncc', 'like', "%{$search}%");
                  });
            });
        }
        $orders = $queryOrders->orderBy('Ngay_lap', 'desc')->get();

        // 2. Khách trả hàng: Phiếu xuất đã giao thành công và chưa xử lý hoàn
        $queryKhach = PhieuXuatKho::with(['donHangBan.khachHang', 'chiTiets.lo.sanPham'])
            ->whereHas('donHangBan', function ($q) use ($maKho) {
                $q->where('Ma_kho', $maKho);
            })
            ->where('Trang_thai_giao_hang', 'Giao thành công')
            ->whereNull('Trang_thai_hoan_hang');

        if ($search && $loai === 'Khách trả hàng') {
            $queryKhach->where(function ($q) use ($search) {
                $q->where('Ma_phieu_xuat', 'like', "%{$search}%")
                  ->orWhere('Ma_don_hang_ban', 'like', "%{$search}%")
                  ->orWhereHas('donHangBan.khachHang', function ($sub) use ($search) {
                      $sub->where('Ten_khach_hang', 'like', "%{$search}%")
                          ->orWhere('So_dien_thoai', 'like', "%{$search}%");
                  });
            });
        }
        $slipsKhach = $queryKhach->orderBy('Thoi_diem_ket_qua_giao', 'desc')->get();

        // 3. Hoàn giao thất bại: Phiếu xuất giao thất bại và đang chờ xử lý hoàn
        $queryHoan = PhieuXuatKho::with(['donHangBan.khachHang', 'chiTiets.lo.sanPham'])
            ->whereHas('donHangBan', function ($q) use ($maKho) {
                $q->where('Ma_kho', $maKho);
            })
            ->where('Trang_thai_giao_hang', 'Giao hàng thất bại')
            ->where('Trang_thai_hoan_hang', 'Chờ xử lý');

        if ($search && $loai === 'Hoàn giao thất bại') {
            $queryHoan->where(function ($q) use ($search) {
                $q->where('Ma_phieu_xuat', 'like', "%{$search}%")
                  ->orWhere('Ma_don_hang_ban', 'like', "%{$search}%")
                  ->orWhereHas('donHangBan.khachHang', function ($sub) use ($search) {
                      $sub->where('Ten_khach_hang', 'like', "%{$search}%")
                          ->orWhere('So_dien_thoai', 'like', "%{$search}%");
                  });
            });
        }
        $slipsHoan = $queryHoan->orderBy('Thoi_diem_ket_qua_giao', 'desc')->get();

        return view('nvkho.nhap-kho.index', [
            'loai' => $loai,
            'search' => $search,
            'orders' => $orders,
            'slipsKhach' => $slipsKhach,
            'slipsHoan' => $slipsHoan,
        ]);
    }

    /**
     * NVK-04: Form nhập hàng từ nhà cung cấp theo đơn đặt hàng
     */
    public function nhapNcc(string $maDon): View
    {
        $user = auth()->user();
        $don = DonDatHang::with(['nhaCungCap', 'chiTiets.sanPham'])
            ->where('Ma_kho', $user->Ma_kho)
            ->where('Ma_don_dat_hang', $maDon)
            ->whereIn('Trang_thai', ['Đã gửi NCC', 'Nhận một phần'])
            ->firstOrFail();

        $tienDoMap = $don->getTienDoChiTietMap();

        // Lấy danh sách lô đã có của các sản phẩm trong đơn để gợi ý
        $maSanPhams = $don->chiTiets->pluck('Ma_san_pham')->all();
        $existingLots = Lo::whereIn('Ma_san_pham', $maSanPhams)
            ->orderBy('Ma_lo', 'desc')
            ->get()
            ->groupBy('Ma_san_pham');

        // Số lần đã giao thành công của đơn này
        $soLanDaGiao = PhieuNhapKho::where('Ma_don_dat_hang', $maDon)
            ->where('Trang_thai', 'Đã nhập kho')
            ->count();

        return view('nvkho.nhap-kho.ncc', [
            'don' => $don,
            'tienDoMap' => $tienDoMap,
            'existingLots' => $existingLots,
            'soLanDaGiao' => $soLanDaGiao,
        ]);
    }

    /**
     * NVK-04: Lưu / Xác nhận nhập hàng từ NCC
     */
    public function storeNcc(Request $request, string $maDon): RedirectResponse
    {
        $user = auth()->user();
        $don = DonDatHang::where('Ma_kho', $user->Ma_kho)
            ->where('Ma_don_dat_hang', $maDon)
            ->whereIn('Trang_thai', ['Đã gửi NCC', 'Nhận một phần'])
            ->firstOrFail();

        $action = $request->input('action', 'save'); // 'save' hoặc 'confirm'

        $request->validate([
            'so_phieu_giao_ncc' => 'nullable|string|max:50',
            'dot_giao_cuoi' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.ma_san_pham' => 'required|exists:San_pham,Ma_san_pham',
            'items.*.ma_lo' => 'required|string|max:20',
            'items.*.so_luong_nhap' => 'nullable|integer|min:0',
            'items.*.so_luong_hao_hut' => 'nullable|integer|min:0',
            'items.*.ly_do_hao_hut' => 'nullable|string|max:1000',
            'items.*.ngay_san_xuat' => 'nullable|date',
            'items.*.han_su_dung' => 'nullable|date|after_or_equal:items.*.ngay_san_xuat',
        ]);

        $items = $request->input('items', []);
        $validItems = [];

        foreach ($items as $it) {
            $slNhap = (int) ($it['so_luong_nhap'] ?? 0);
            $slHaoHut = (int) ($it['so_luong_hao_hut'] ?? 0);

            if ($slNhap > 0 || $slHaoHut > 0) {
                if ($slHaoHut > 0 && empty($it['ly_do_hao_hut'])) {
                    return back()->withInput()->with('error', 'Vui lòng nhập lý do hao hụt cho các dòng có hàng hao hụt.');
                }
                $validItems[] = $it;
            }
        }

        if (empty($validItems)) {
            return back()->withInput()->with('error', 'Phiếu nhập phải có ít nhất một dòng sản phẩm với số lượng nhận đạt hoặc hao hụt lớn hơn 0.');
        }

        try {
            $phieu = DB::transaction(function () use ($request, $user, $don, $maDon, $validItems) {
                $maPhieu = CodeGenerator::generateMaPhieuNhap();

                $phieu = PhieuNhapKho::create([
                    'Ma_phieu_nhap' => $maPhieu,
                    'Ma_kho' => $user->Ma_kho,
                    'Loai_nhap' => 'Nhập từ NCC',
                    'Ma_don_dat_hang' => $maDon,
                    'Ma_nguoi_lap' => $user->Ma_nguoi_dung,
                    'Ngay_lap' => now()->toDateString(),
                    'So_phieu_giao_ncc' => $request->input('so_phieu_giao_ncc'),
                    'Dot_giao_cuoi' => $request->boolean('dot_giao_cuoi'),
                    'Trang_thai' => 'Đang cập nhật',
                ]);

                foreach ($validItems as $it) {
                    $maSp = $it['ma_san_pham'];
                    $maLoNcc = trim($it['ma_lo']);

                    // Tìm hoặc tạo Lô
                    $lo = Lo::where('Ma_san_pham', $maSp)
                        ->where(function ($q) use ($maLoNcc) {
                            $q->where('Ma_lo', $maLoNcc)
                              ->orWhere('Ma_lo_ncc', $maLoNcc);
                        })
                        ->first();

                    if (!$lo) {
                        $maLoMoi = CodeGenerator::generateMaLo();
                        $lo = Lo::create([
                            'Ma_lo' => $maLoMoi,
                            'Ma_san_pham' => $maSp,
                            'Ma_lo_ncc' => $maLoNcc,
                            'Ngay_san_xuat' => $it['ngay_san_xuat'] ?? null,
                            'Han_su_dung' => $it['han_su_dung'] ?? null,
                        ]);
                    }

                    ChiTietPhieuNhap::create([
                        'Ma_phieu_nhap' => $maPhieu,
                        'Ma_lo' => $lo->Ma_lo,
                        'So_luong_nhap' => (int) ($it['so_luong_nhap'] ?? 0),
                        'So_luong_hao_hut' => (int) ($it['so_luong_hao_hut'] ?? 0),
                        'Ly_do_hao_hut' => $it['ly_do_hao_hut'] ?? null,
                    ]);
                }

                ActivityLogger::log('Tạo phiếu nhập', 'Phiếu nhập kho', "Tạo phiếu nhập kho {$maPhieu} từ đơn đặt hàng {$maDon}", $phieu);

                return $phieu;
            });

            if ($action === 'confirm') {
                StockService::nhapKho($phieu);
                return redirect()->route('nv-kho.phieu-nhap.show', $phieu->Ma_phieu_nhap)
                    ->with('success', "Đã xác nhận nhập kho thành công cho phiếu {$phieu->Ma_phieu_nhap}.");
            }

            return redirect()->route('nv-kho.phieu-nhap.index')
                ->with('success', "Đã lưu nháp phiếu nhập {$phieu->Ma_phieu_nhap}.");

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * NVK-05 & NVK-06: Xử lý hàng hoàn (Khách trả hàng & Hoàn giao thất bại)
     */
    public function xuLyHoan(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $maKho = $user->Ma_kho ?? 'HN';

        $mode = $request->input('mode', 'in'); // 'in' = Nhập lại kho, 'out' = Không nhập kho
        $maPhieuXuat = $request->input('ma_phieu_xuat');
        $loaiNhap = $request->input('loai_nhap'); // 'Khách trả hàng' hoặc 'Hoàn giao thất bại'

        $px = PhieuXuatKho::with(['donHangBan', 'chiTiets.lo.sanPham'])
            ->whereHas('donHangBan', function ($q) use ($maKho) {
                $q->where('Ma_kho', $maKho);
            })
            ->where('Ma_phieu_xuat', $maPhieuXuat)
            ->firstOrFail();

        // Nhánh 1: Không nhập kho
        if ($mode === 'out') {
            $request->validate([
                'ghi_chu_xu_ly_hoan' => 'required|string|max:1000',
            ], [
                'ghi_chu_xu_ly_hoan.required' => 'Bắt buộc nhập ghi chú / lý do không nhập kho.',
            ]);

            $px->update([
                'Trang_thai_hoan_hang' => 'Đã xử lý – không nhập kho',
                'Thoi_diem_xu_ly_hoan' => now(),
                'Ghi_chu_xu_ly_hoan' => $request->input('ghi_chu_xu_ly_hoan'),
            ]);

            ActivityLogger::log('Xử lý hoàn hàng', 'Phiếu xuất kho', "Xác nhận không nhập kho cho phiếu xuất {$maPhieuXuat}: {$request->input('ghi_chu_xu_ly_hoan')}", $px);

            return redirect()->route('nv-kho.phieu-nhap.index')
                ->with('success', "Đã xác nhận không nhập kho cho phiếu xuất {$maPhieuXuat}.");
        }

        // Nhánh 2: Nhập lại kho
        $request->validate([
            'ly_do_tra_hang' => 'required|string|max:1000',
            'ghi_chu_xu_ly_hoan' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.ma_lo' => 'required|exists:Lo,Ma_lo',
            'items.*.so_luong_nhap' => 'required|integer|min:0',
        ], [
            'ly_do_tra_hang.required' => 'Vui lòng nhập lý do hàng hoàn về / khách trả.',
        ]);

        $items = $request->input('items', []);
        $hasAny = false;
        $tongXuat = 0;
        $tongNhapLai = 0;

        foreach ($items as $it) {
            $sl = (int) ($it['so_luong_nhap'] ?? 0);
            if ($sl > 0) {
                $hasAny = true;
            }
            $tongNhapLai += $sl;
        }

        if (!$hasAny) {
            return back()->withInput()->with('error', 'Vui lòng nhập số lượng thực nhận lớn hơn 0 cho ít nhất một lô.');
        }

        // Kiểm tra xem có nhận thiếu so với số đã xuất không
        foreach ($px->chiTiets as $ctPx) {
            $tongXuat += (int) $ctPx->So_luong;
        }

        if ($tongNhapLai < $tongXuat && empty($request->input('ghi_chu_xu_ly_hoan'))) {
            return back()->withInput()->with('error', 'Số lượng thực nhận ít hơn số đã xuất. Bắt buộc nhập Ghi chú xử lý hoàn giải trình số lượng chênh lệch (hàng mất).');
        }

        try {
            $phieu = DB::transaction(function () use ($request, $user, $px, $loaiNhap, $items) {
                $maPhieu = CodeGenerator::generateMaPhieuNhap();

                $phieu = PhieuNhapKho::create([
                    'Ma_phieu_nhap' => $maPhieu,
                    'Ma_kho' => $user->Ma_kho,
                    'Loai_nhap' => $loaiNhap,
                    'Ma_phieu_xuat' => $px->Ma_phieu_xuat,
                    'Ma_nguoi_lap' => $user->Ma_nguoi_dung,
                    'Ngay_lap' => now()->toDateString(),
                    'Ly_do_tra_hang' => $request->input('ly_do_tra_hang'),
                    'Trang_thai' => 'Đang cập nhật',
                ]);

                foreach ($items as $it) {
                    $sl = (int) ($it['so_luong_nhap'] ?? 0);
                    if ($sl > 0) {
                        ChiTietPhieuNhap::create([
                            'Ma_phieu_nhap' => $maPhieu,
                            'Ma_lo' => $it['ma_lo'],
                            'So_luong_nhap' => $sl,
                            'So_luong_hao_hut' => 0,
                        ]);
                    }
                }

                if ($request->filled('ghi_chu_xu_ly_hoan')) {
                    $px->update(['Ghi_chu_xu_ly_hoan' => $request->input('ghi_chu_xu_ly_hoan')]);
                }

                ActivityLogger::log('Tạo phiếu nhập', 'Phiếu nhập kho', "Tạo phiếu nhập hoàn {$maPhieu} từ phiếu xuất {$px->Ma_phieu_xuat}", $phieu);

                return $phieu;
            });

            // Mặc định thực hiện xác nhận nhập kho
            StockService::nhapKho($phieu);

            return redirect()->route('nv-kho.phieu-nhap.show', $phieu->Ma_phieu_nhap)
                ->with('success', "Đã xác nhận nhập kho thành công cho phiếu hoàn {$phieu->Ma_phieu_nhap}.");

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}

