<?php

namespace App\Http\Controllers\KinhDoanh;

use App\Http\Controllers\Controller;
use App\Models\ChiTietDonBan;
use App\Models\ChiTietPhieuXuat;
use App\Models\DonHangBan;
use App\Models\KhachHang;
use App\Models\Kho;
use App\Models\PhieuXuatKho;
use App\Models\SanPham;
use App\Services\ActivityLogger;
use App\Services\CodeGenerator;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DonBanController extends Controller
{
    /**
     * KD-02: Danh sách đơn hàng bán
     */
    public function index(Request $request): View
    {
        $user      = auth()->user();
        $search    = $request->input('search', '');
        $trangThai = $request->input('trang_thai', '');
        $maKho     = $request->input('ma_kho', '');

        $query = DonHangBan::with(['khachHang', 'kho', 'phieuXuatKho'])
            ->orderByDesc('Thoi_diem_xac_nhan');

        if ($user->Ma_kho) {
            $query->where('Ma_kho', $user->Ma_kho);
        } elseif ($maKho) {
            $query->where('Ma_kho', $maKho);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Ma_don_hang_ban', 'like', "%{$search}%")
                  ->orWhere('Ten_nguoi_nhan', 'like', "%{$search}%")
                  ->orWhere('Sdt_nguoi_nhan', 'like', "%{$search}%")
                  ->orWhereHas('khachHang', fn ($q2) => $q2->where('Ten_khach_hang', 'like', "%{$search}%"));
            });
        }

        if ($trangThai) {
            $query->where('Trang_thai', $trangThai);
        }

        $donBans = $query->paginate(20)->withQueryString();
        $khos    = Kho::where('Trang_thai', 'Hoạt động')->get();

        // Tính nhãn Thiếu tồn cho mỗi đơn
        $thieu = [];
        foreach ($donBans as $db) {
            $px = $db->phieuXuatKho;
            if ($px && $px->Trang_thai_giao_hang === 'Đang chuẩn bị hàng') {
                $thieu[$db->Ma_don_hang_ban] = StockService::isThieuTon($px->Ma_phieu_xuat, $db->Ma_kho);
            }
        }

        return view('kinhdoanh.don-ban.index', compact('donBans', 'search', 'trangThai', 'maKho', 'khos', 'thieu'));
    }

    /**
     * KD-03: Form lập đơn bán
     */
    public function create(): View
    {
        $user       = auth()->user();
        $khachHangs = KhachHang::where('Trang_thai', 'Đang giao dịch')->orderBy('Ten_khach_hang')->get();
        $khos       = Kho::where('Trang_thai', 'Hoạt động')->orderBy('Ten_kho')->get();
        $sanPhams   = SanPham::where('Trang_thai', 'Đang kinh doanh')->orderBy('Ten_san_pham')->get();

        // Build tồn khả dụng per sản phẩm cho kho mặc định của user
        $maKho = $user->Ma_kho ?? ($khos->first()->Ma_kho ?? null);
        $tonKhaDung = [];
        foreach ($sanPhams as $sp) {
            $tonKhaDung[$sp->Ma_san_pham] = StockService::getTonKhaDung($maKho, $sp->Ma_san_pham);
        }

        return view('kinhdoanh.don-ban.create', compact('khachHangs', 'khos', 'sanPhams', 'tonKhaDung', 'maKho'));
    }

    /**
     * KD-03: Xác nhận đơn bán → tạo DonHangBan + ChiTietDonBan + PhieuXuatKho + ChiTiet FIFO
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'ma_khach_hang'    => 'required|exists:Khach_hang,Ma_khach_hang',
            'ma_kho'           => 'required|exists:Kho,Ma_kho',
            'ten_nguoi_nhan'   => 'required|string|max:100',
            'sdt_nguoi_nhan'   => 'required|string|max:20',
            'dia_chi_giao_hang'=> 'required|string|max:255',
            'items'            => 'required|array|min:1',
            'items.*.ma_san_pham'   => 'required|exists:San_pham,Ma_san_pham',
            'items.*.so_luong'      => 'required|integer|min:1',
            'items.*.chiet_khau'    => 'nullable|numeric|min:0|max:100',
        ]);

        $maKho = $request->input('ma_kho');
        $items = collect($request->input('items'))->filter(fn ($it) => (int)($it['so_luong'] ?? 0) > 0)->values();

        if ($items->isEmpty()) {
            return back()->withInput()->with('error', 'Phiếu phải có ít nhất một sản phẩm với số lượng > 0.');
        }

        // Kiểm tra tồn khả dụng trước khi mở transaction
        foreach ($items as $it) {
            $maSp = $it['ma_san_pham'];
            $sl   = (int) $it['so_luong'];
            $sp   = SanPham::find($maSp);

            if (!$sp->Gia_niem_yet) {
                return back()->withInput()->with('error', "Sản phẩm {$sp->Ten_san_pham} chưa có giá niêm yết, không thể lập đơn.");
            }

            $kha_dung = StockService::getTonKhaDung($maKho, $maSp);
            if ($sl > $kha_dung) {
                return back()->withInput()->with('error', "Sản phẩm {$sp->Ten_san_pham}: số lượng đặt ({$sl}) vượt tồn khả dụng ({$kha_dung} chai).");
            }
        }

        try {
            $donBan = DB::transaction(function () use ($request, $user, $maKho, $items) {
                $maDon   = CodeGenerator::generateMaDonBan();
                $maPhieuXuat = CodeGenerator::generateMaPhieuXuat();
                $tongTien = 0;
                $chiTiets = [];

                // Tính tổng tiền và chuẩn bị chi tiết
                foreach ($items as $it) {
                    $maSp     = $it['ma_san_pham'];
                    $sl       = (int) $it['so_luong'];
                    $chietKhau = (float) ($it['chiet_khau'] ?? 0);
                    $sp       = SanPham::find($maSp);
                    $donGiaBan = (int) round($sp->Gia_niem_yet * (1 - $chietKhau / 100));

                    $tongTien += $sl * $donGiaBan;

                    $chiTiets[] = [
                        'ma_san_pham'  => $maSp,
                        'so_luong'     => $sl,
                        'chiet_khau'   => $chietKhau,
                        'don_gia_ban'  => $donGiaBan,
                    ];
                }

                // Tạo đơn hàng bán
                $donBan = DonHangBan::create([
                    'Ma_don_hang_ban'   => $maDon,
                    'Ma_khach_hang'     => $request->input('ma_khach_hang'),
                    'Ma_kho'            => $maKho,
                    'Ma_nguoi_lap'      => $user->Ma_nguoi_dung,
                    'Thoi_diem_xac_nhan'=> now(),
                    'Ten_nguoi_nhan'    => $request->input('ten_nguoi_nhan'),
                    'Sdt_nguoi_nhan'    => $request->input('sdt_nguoi_nhan'),
                    'Dia_chi_giao_hang' => $request->input('dia_chi_giao_hang'),
                    'Tong_tien_hang'    => $tongTien,
                    'Trang_thai'        => 'Đang chuẩn bị hàng',
                ]);

                // Tạo chi tiết đơn bán
                foreach ($chiTiets as $ct) {
                    ChiTietDonBan::create([
                        'Ma_don_hang_ban' => $maDon,
                        'Ma_san_pham'     => $ct['ma_san_pham'],
                        'So_luong'        => $ct['so_luong'],
                        'Don_gia_ban'     => $ct['don_gia_ban'],
                        'Chiet_khau'      => $ct['chiet_khau'],
                    ]);
                }

                // Tạo phiếu xuất kho
                PhieuXuatKho::create([
                    'Ma_phieu_xuat'       => $maPhieuXuat,
                    'Ma_don_hang_ban'     => $maDon,
                    'Ngay_tao'            => now()->toDateString(),
                    'Trang_thai_giao_hang'=> 'Đang chuẩn bị hàng',
                    'Trang_thai_hoan_hang'=> 'Chưa xử lý',
                ]);

                // Phân bổ lô FIFO (F12) cho từng sản phẩm
                foreach ($chiTiets as $ct) {
                    $slConLai = $ct['so_luong'];

                    // Lô FIFO: theo Ngay_nhap_dau_tien ASC, Ma_lo ASC
                    $lots = DB::table('Ton_kho as tk')
                        ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo')
                        ->where('tk.Ma_kho', $maKho)
                        ->where('l.Ma_san_pham', $ct['ma_san_pham'])
                        ->where('tk.Ton_thuc', '>', 0)
                        ->orderBy('tk.Ngay_nhap_dau_tien')
                        ->orderBy('tk.Ma_lo')
                        ->select('tk.Ma_lo', 'tk.Ton_thuc')
                        ->get();

                    // Trừ số đang giữ cho các phiếu xuất khác
                    foreach ($lots as $lot) {
                        $dangGiuLo = (int) DB::table('Chi_tiet_phieu_xuat as ct')
                            ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'ct.Ma_phieu_xuat')
                            ->join('Don_hang_ban as db', 'db.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
                            ->where('px.Trang_thai_giao_hang', 'Đang chuẩn bị hàng')
                            ->where('db.Ma_kho', $maKho)
                            ->where(function ($q) use ($lot) {
                                $q->where('ct.Ma_lo_thuc_xuat', $lot->Ma_lo)
                                  ->orWhere(function ($q2) use ($lot) {
                                      $q2->where('ct.Ma_lo_phan_bo', $lot->Ma_lo)
                                         ->whereNull('ct.Ma_lo_thuc_xuat');
                                  });
                            })
                            ->sum('ct.So_luong');

                        $khaDungLo = max(0, (int)$lot->Ton_thuc - $dangGiuLo);

                        if ($khaDungLo <= 0 || $slConLai <= 0) {
                            continue;
                        }

                        $slLayLo = min($khaDungLo, $slConLai);

                        ChiTietPhieuXuat::create([
                            'Ma_phieu_xuat'  => $maPhieuXuat,
                            'Ma_lo_phan_bo'  => $lot->Ma_lo,
                            'Ma_lo_thuc_xuat'=> $lot->Ma_lo, // ban đầu giống nhau
                            'So_luong'       => $slLayLo,
                        ]);

                        $slConLai -= $slLayLo;

                        if ($slConLai <= 0) {
                            break;
                        }
                    }

                    if ($slConLai > 0) {
                        // Không đủ hàng (race condition), rollback
                        throw new \Exception("Không đủ tồn khả dụng cho sản phẩm trong kho sau khi phân bổ lô.");
                    }
                }

                ActivityLogger::log('Xác nhận đơn bán', 'Đơn hàng bán', "Tạo đơn hàng bán {$maDon}", $donBan);

                return $donBan;
            });

            return redirect()->route('kinh-doanh.don-ban.show', $donBan->Ma_don_hang_ban)
                ->with('success', "Đã xác nhận đơn hàng {$donBan->Ma_don_hang_ban} thành công.");

        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * KD-04: Chi tiết đơn hàng bán
     */
    public function show(string $ma): View
    {
        $user = auth()->user();

        $query = DonHangBan::with([
            'khachHang',
            'kho',
            'nguoiLap',
            'chiTiets.sanPham',
            'phieuXuatKho',
        ]);

        if ($user->Ma_kho) {
            $query->where('Ma_kho', $user->Ma_kho);
        }

        $donBan = $query->where('Ma_don_hang_ban', $ma)->firstOrFail();

        $px = $donBan->phieuXuatKho;
        $thieu = $px ? StockService::isThieuTon($px->Ma_phieu_xuat, $donBan->Ma_kho) : false;

        return view('kinhdoanh.don-ban.show', compact('donBan', 'px', 'thieu'));
    }

    /**
     * KD-04: Lưu thông tin giao hàng (chỉ người nhận, SĐT, địa chỉ)
     */
    public function updateGiaoHang(Request $request, string $ma): RedirectResponse
    {
        $user  = auth()->user();
        $query = DonHangBan::where('Ma_don_hang_ban', $ma);

        if ($user->Ma_kho) {
            $query->where('Ma_kho', $user->Ma_kho);
        }

        $donBan = $query->firstOrFail();

        if ($donBan->Trang_thai !== 'Đang chuẩn bị hàng') {
            return back()->with('error', 'Chỉ sửa thông tin giao hàng khi đơn còn ở trạng thái "Đang chuẩn bị hàng".');
        }

        $request->validate([
            'ten_nguoi_nhan'    => 'required|string|max:100',
            'sdt_nguoi_nhan'    => 'required|string|max:20',
            'dia_chi_giao_hang' => 'required|string|max:255',
        ]);

        $donBan->update([
            'Ten_nguoi_nhan'    => $request->input('ten_nguoi_nhan'),
            'Sdt_nguoi_nhan'    => $request->input('sdt_nguoi_nhan'),
            'Dia_chi_giao_hang' => $request->input('dia_chi_giao_hang'),
        ]);

        ActivityLogger::log('Cập nhật giao hàng', 'Đơn hàng bán', "Cập nhật thông tin giao hàng đơn {$ma}", $donBan);

        return redirect()->route('kinh-doanh.don-ban.show', $ma)
            ->with('success', 'Đã cập nhật thông tin giao hàng.');
    }

    /**
     * KD-04: Hủy đơn hàng bán
     */
    public function huyDon(Request $request, string $ma): RedirectResponse
    {
        $user  = auth()->user();
        $query = DonHangBan::where('Ma_don_hang_ban', $ma);

        if ($user->Ma_kho) {
            $query->where('Ma_kho', $user->Ma_kho);
        }

        $donBan = $query->firstOrFail();

        if ($donBan->Trang_thai !== 'Đang chuẩn bị hàng') {
            return back()->with('error', 'Chỉ hủy đơn khi còn ở trạng thái "Đang chuẩn bị hàng".');
        }

        $request->validate([
            'ly_do_huy' => 'required|string|max:500',
        ], ['ly_do_huy.required' => 'Bắt buộc nhập lý do hủy đơn.']);

        DB::transaction(function () use ($donBan, $request) {
            // Chuyển phiếu xuất sang Đã hủy (giải phóng hàng giữ)
            PhieuXuatKho::where('Ma_don_hang_ban', $donBan->Ma_don_hang_ban)
                ->update(['Trang_thai_giao_hang' => 'Đã hủy']);

            $donBan->update([
                'Trang_thai'    => 'Đã hủy',
                'Thoi_diem_huy' => now(),
                'Ly_do_huy'     => $request->input('ly_do_huy'),
            ]);

            ActivityLogger::log('Hủy đơn bán', 'Đơn hàng bán', "Hủy đơn {$donBan->Ma_don_hang_ban}: {$request->input('ly_do_huy')}", $donBan);
        });

        return redirect()->route('kinh-doanh.don-ban.index')
            ->with('success', "Đã hủy đơn hàng {$donBan->Ma_don_hang_ban}.");
    }

    /**
     * AJAX: Lấy tồn khả dụng per sản phẩm cho một kho
     */
    public function tonKhaDung(Request $request): \Illuminate\Http\JsonResponse
    {
        $maKho  = $request->input('ma_kho', auth()->user()->Ma_kho);
        $sanPhams = SanPham::where('Trang_thai', 'Đang kinh doanh')->get(['Ma_san_pham']);
        $result = [];
        foreach ($sanPhams as $sp) {
            $result[$sp->Ma_san_pham] = StockService::getTonKhaDung($maKho, $sp->Ma_san_pham);
        }
        return response()->json($result);
    }
}

