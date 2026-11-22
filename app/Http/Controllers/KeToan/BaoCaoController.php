<?php

namespace App\Http\Controllers\KeToan;

use App\Http\Controllers\Controller;
use App\Models\ChiTietDonBan;
use App\Models\ChiTietDonDatHang;
use App\Models\ChiTietKiemKe;
use App\Models\ChiTietPhieuNhap;
use App\Models\ChiTietPhieuXuat;
use App\Models\DonDatHang;
use App\Models\DonHangBan;
use App\Models\KhachHang;
use App\Models\Kho;
use App\Models\NhaCungCap;
use App\Models\PhieuKiemKe;
use App\Models\PhieuNhapKho;
use App\Models\PhieuXuatKho;
use App\Models\SanPham;
use App\Models\TonKho;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BaoCaoController extends Controller
{
    /**
     * KT-04: Báo cáo của Kế toán (6 tabs, xem cả 2 kho, có số tiền)
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $khos = Kho::all();

        $tab = (int) $request->input('tab', 0);
        $tuNgay = $request->input('tu_ngay', Carbon::now()->startOfMonth()->toDateString());
        $denNgay = $request->input('den_ngay', Carbon::now()->toDateString());
        $taiNgay = $request->input('tai_ngay', Carbon::now()->toDateString());
        $maKho = $request->input('kho', 'all');
        $q = trim((string) $request->input('q', ''));

        $data = $this->getDataForTab($tab, $maKho, $tuNgay, $denNgay, $taiNgay, $q);

        return view('ketoan.bao-cao', compact('user', 'khos', 'tab', 'tuNgay', 'denNgay', 'taiNgay', 'maKho', 'q', 'data'));
    }

    /**
     * Xuất Excel / CSV cho KT-04
     */
    public function export(Request $request): StreamedResponse
    {
        $tab = (int) $request->input('tab', 0);
        $tuNgay = $request->input('tu_ngay', Carbon::now()->startOfMonth()->toDateString());
        $denNgay = $request->input('den_ngay', Carbon::now()->toDateString());
        $taiNgay = $request->input('tai_ngay', Carbon::now()->toDateString());
        $maKho = $request->input('kho', 'all');
        $q = trim((string) $request->input('q', ''));

        $data = $this->getDataForTab($tab, $maKho, $tuNgay, $denNgay, $taiNgay, $q);

        $tabNames = [
            0 => 'Ton_Kho',
            1 => 'Nhap_Xuat_Ton',
            2 => 'Doanh_Thu_Lai_Gop',
            3 => 'Mua_Hang',
            4 => 'Hang_Tra_Va_Giao_That_Bai',
            5 => 'Dieu_Chinh_Ton',
        ];
        $tabSlug = $tabNames[$tab] ?? 'Bao_Cao_Ke_Toan';
        $filename = "KT_BaoCao_{$tabSlug}_{$denNgay}.csv";

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");

            // Header row
            $headers = array_map(fn($col) => $col['label'], $data['cols']);
            fputcsv($handle, $headers);

            // Data rows
            foreach ($data['rows'] as $row) {
                $line = array_map(fn($cell) => $cell['v'], $row['cells']);
                fputcsv($handle, $line);
            }

            // Footer row
            if (!empty($data['foot'])) {
                $footLine = array_map(fn($f) => $f['v'], $data['foot']);
                fputcsv($handle, $footLine);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function getDataForTab(int $tab, string $maKho, string $tuNgay, string $denNgay, string $taiNgay, string $q): array
    {
        return match ($tab) {
            0 => $this->getTonKhoData($maKho, $taiNgay, $q),
            1 => $this->getNhapXuatTonData($maKho, $tuNgay, $denNgay, $q),
            2 => $this->getDoanhThuLaiGopData($maKho, $tuNgay, $denNgay, $q),
            3 => $this->getMuaHangData($maKho, $tuNgay, $denNgay, $q),
            4 => $this->getHangTraData($maKho, $tuNgay, $denNgay, $q),
            5 => $this->getDieuChinhData($maKho, $tuNgay, $denNgay, $q),
            default => $this->getTonKhoData($maKho, $taiNgay, $q),
        };
    }

    /**
     * Tab 0: Tồn kho
     */
    private function getTonKhoData(string $maKho, string $taiNgay, string $q): array
    {
        $spQuery = SanPham::query();
        if ($q !== '') {
            $spQuery->where(function ($sub) use ($q) {
                $sub->where('Ma_san_pham', 'like', "%{$q}%")
                    ->orWhere('Ten_san_pham', 'like', "%{$q}%");
            });
        }
        $sanPhams = $spQuery->orderBy('Ma_san_pham')->get();

        // Query tồn thực theo kho
        $tkQuery = DB::table('Ton_kho as tk')
            ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo');
        if ($maKho !== 'all') {
            $tkQuery->where('tk.Ma_kho', $maKho);
        }
        $tonThucMap = $tkQuery->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(tk.Ton_thuc) as tong_ton'))
            ->pluck('tong_ton', 'l.Ma_san_pham')
            ->all();

        // Query đang giữ (F3)
        $pxQuery = DB::table('Phieu_xuat_kho as px')
            ->join('Don_hang_ban as dhb', 'dhb.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Chi_tiet_phieu_xuat as ct', 'ct.Ma_phieu_xuat', '=', 'px.Ma_phieu_xuat')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo_phan_bo')
            ->where('px.Trang_thai_giao_hang', 'Đang chuẩn bị hàng');
        if ($maKho !== 'all') {
            $pxQuery->where('dhb.Ma_kho', $maKho);
        }
        $dangGiuMap = $pxQuery->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(ct.So_luong) as tong_giu'))
            ->pluck('tong_giu', 'l.Ma_san_pham')
            ->all();

        $cols = [
            ['label' => 'Mã SP', 'align' => 'left'],
            ['label' => 'Sản phẩm', 'align' => 'left'],
            ['label' => 'Tồn thực', 'align' => 'right'],
            ['label' => 'Đang giữ', 'align' => 'right'],
            ['label' => 'Khả dụng', 'align' => 'right'],
            ['label' => 'Giá vốn BQ', 'align' => 'right'],
            ['label' => 'Giá trị tồn', 'align' => 'right'],
        ];

        $rows = [];
        $totTon = 0; $totGiu = 0; $totKhaDung = 0; $totGiaTri = 0; $soMaCoTon = 0;

        foreach ($sanPhams as $sp) {
            $msp = $sp->Ma_san_pham;
            $ton = (int) ($tonThucMap[$msp] ?? 0);
            $giu = (int) ($dangGiuMap[$msp] ?? 0);
            $kd = max(0, $ton - $giu);
            $giaVon = (float) ($sp->Gia_von_binh_quan ?? 0);
            $giaTri = $ton * $giaVon;

            if ($ton > 0) {
                $soMaCoTon++;
            }

            if ($ton === 0 && $giu === 0) {
                continue;
            }

            $totTon += $ton;
            $totGiu += $giu;
            $totKhaDung += $kd;
            $totGiaTri += $giaTri;

            $rows[] = [
                'cells' => [
                    ['v' => $msp, 'align' => 'left'],
                    ['v' => $sp->Ten_san_pham, 'align' => 'left'],
                    ['v' => number_format($ton, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($giu, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($kd, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($giaVon, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => number_format($giaTri, 0, ',', '.') . ' đ', 'align' => 'right'],
                ],
            ];
        }

        $foot = [
            ['v' => 'Tổng cộng', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => number_format($totTon, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totGiu, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totKhaDung, 0, ',', '.'), 'align' => 'right'],
            ['v' => '', 'align' => 'right'],
            ['v' => number_format($totGiaTri, 0, ',', '.') . ' đ', 'align' => 'right'],
        ];

        $kpis = [
            ['label' => 'Giá trị tồn kho', 'value' => number_format($totGiaTri, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => true],
            ['label' => 'Số chai tồn', 'value' => number_format($totTon, 0, ',', '.'), 'delta' => '', 'highlight' => false],
            ['label' => 'Đang giữ cho đơn', 'value' => number_format($totGiu, 0, ',', '.'), 'delta' => '', 'highlight' => false],
            ['label' => 'Số mã sản phẩm có tồn', 'value' => number_format($soMaCoTon, 0, ',', '.'), 'delta' => '', 'highlight' => false],
        ];

        return [
            'name' => 'Tồn kho',
            'desc' => 'Số lượng và giá trị tồn tại một thời điểm. Giá trị = tồn thực × giá vốn bình quân.',
            'note' => 'Giá vốn bình quân tính chung toàn công ty.',
            'searchLabel' => 'Sản phẩm',
            'searchPh' => 'Mã, tên sản phẩm',
            'range' => false,
            'kpis' => $kpis,
            'cols' => $cols,
            'rows' => $rows,
            'foot' => $foot,
            'hasChart' => false,
        ];
    }

    /**
     * Tab 1: Nhập – xuất – tồn (Kế toán, có giá trị)
     */
    private function getNhapXuatTonData(string $maKho, string $tuNgay, string $denNgay, string $q): array
    {
        $spQuery = SanPham::query();
        if ($q !== '') {
            $spQuery->where(function ($sub) use ($q) {
                $sub->where('Ma_san_pham', 'like', "%{$q}%")
                    ->orWhere('Ten_san_pham', 'like', "%{$q}%");
            });
        }
        $sanPhams = $spQuery->orderBy('Ma_san_pham')->get();

        // Nhập trong kỳ
        $nhapQ = DB::table('Phieu_nhap_kho as pn')
            ->join('Chi_tiet_phieu_nhap as ct', 'ct.Ma_phieu_nhap', '=', 'pn.Ma_phieu_nhap')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo')
            ->where('pn.Trang_thai', 'Đã nhập kho')
            ->whereBetween(DB::raw('DATE(pn.Thoi_diem_xac_nhan)'), [$tuNgay, $denNgay]);
        if ($maKho !== 'all') {
            $nhapQ->where('pn.Ma_kho', $maKho);
        }
        $nhapMap = $nhapQ->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(ct.So_luong_nhap) as tong_nhap'))
            ->pluck('tong_nhap', 'l.Ma_san_pham')
            ->all();

        // Xuất bán
        $xuatQ = DB::table('Phieu_xuat_kho as px')
            ->join('Don_hang_ban as dhb', 'dhb.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Chi_tiet_phieu_xuat as ct', 'ct.Ma_phieu_xuat', '=', 'px.Ma_phieu_xuat')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo_thuc_xuat')
            ->whereIn('px.Trang_thai_giao_hang', ['Đang giao', 'Giao thành công', 'Giao hàng thất bại'])
            ->whereBetween(DB::raw('DATE(px.Thoi_diem_xuat_kho)'), [$tuNgay, $denNgay]);
        if ($maKho !== 'all') {
            $xuatQ->where('dhb.Ma_kho', $maKho);
        }
        $xuatBanMap = $xuatQ->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(ct.So_luong) as tong_xuat'), DB::raw('SUM(ct.So_luong * COALESCE(ct.Gia_von_xuat, 0)) as tong_gt_xuat'))
            ->get()
            ->keyBy('Ma_san_pham');

        // Hàng hỏng
        $hhQ = DB::table('Chi_tiet_phieu_xuat as ct')
            ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'ct.Ma_phieu_xuat')
            ->join('Don_hang_ban as dhb', 'dhb.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo_phan_bo')
            ->where('ct.So_luong_hong', '>', 0)
            ->whereBetween(DB::raw('DATE(ct.Thoi_diem_doi_lo)'), [$tuNgay, $denNgay]);
        if ($maKho !== 'all') {
            $hhQ->where('dhb.Ma_kho', $maKho);
        }
        $hangHongMap = $hhQ->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(ct.So_luong_hong) as tong_hong'), DB::raw('SUM(ct.So_luong_hong * COALESCE(ct.Gia_von_hang_hong, 0)) as tong_gt_hong'))
            ->get()
            ->keyBy('Ma_san_pham');

        // Điều chỉnh kiểm kê
        $dcQ = DB::table('Phieu_kiem_ke as pk')
            ->join('Chi_tiet_kiem_ke as ct', 'ct.Ma_phieu_kiem_ke', '=', 'pk.Ma_phieu_kiem_ke')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo')
            ->where('pk.Trang_thai', 'Hoàn tất')
            ->whereBetween(DB::raw('DATE(pk.Thoi_diem_duyet)'), [$tuNgay, $denNgay]);
        if ($maKho !== 'all') {
            $dcQ->where('pk.Ma_kho', $maKho);
        }
        $dieuChinhMap = $dcQ->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(ct.So_luong_thuc_te - ct.So_luong_he_thong) as tong_lech'))
            ->pluck('tong_lech', 'l.Ma_san_pham')
            ->all();

        // Tồn thực hiện tại
        $tonQ = DB::table('Ton_kho as tk')
            ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo');
        if ($maKho !== 'all') {
            $tonQ->where('tk.Ma_kho', $maKho);
        }
        $tonThucMap = $tonQ->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(tk.Ton_thuc) as tong_ton'))
            ->pluck('tong_ton', 'l.Ma_san_pham')
            ->all();

        $cols = [
            ['label' => 'Sản phẩm', 'align' => 'left'],
            ['label' => 'Tồn đầu SL', 'align' => 'right'],
            ['label' => 'Tồn đầu GT', 'align' => 'right'],
            ['label' => 'Nhập SL', 'align' => 'right'],
            ['label' => 'Nhập GT', 'align' => 'right'],
            ['label' => 'Xuất SL', 'align' => 'right'],
            ['label' => 'Xuất GT', 'align' => 'right'],
            ['label' => 'Điều chỉnh SL', 'align' => 'right'],
            ['label' => 'Tồn cuối SL', 'align' => 'right'],
            ['label' => 'Tồn cuối GT', 'align' => 'right'],
        ];

        $rows = [];
        $totDauSL = 0; $totDauGT = 0; $totNhapSL = 0; $totNhapGT = 0;
        $totXuatSL = 0; $totXuatGT = 0; $totDCSL = 0; $totCuoiSL = 0; $totCuoiGT = 0;

        foreach ($sanPhams as $sp) {
            $msp = $sp->Ma_san_pham;
            $giaVon = (float) ($sp->Gia_von_binh_quan ?? 0);

            $nhap = (int) ($nhapMap[$msp] ?? 0);
            $nhapGT = $nhap * $giaVon;

            $xbItem = $xuatBanMap[$msp] ?? null;
            $hhItem = $hangHongMap[$msp] ?? null;
            $xuat = (int) ($xbItem->tong_xuat ?? 0) + (int) ($hhItem->tong_hong ?? 0);
            $xuatGT = (float) ($xbItem->tong_gt_xuat ?? 0) + (float) ($hhItem->tong_gt_hong ?? 0);

            $dc = (int) ($dieuChinhMap[$msp] ?? 0);
            $tonCuoi = (int) ($tonThucMap[$msp] ?? 0);
            $tonCuoiGT = $tonCuoi * $giaVon;

            $tonDau = max(0, $tonCuoi - $nhap + $xuat - $dc);
            $tonDauGT = $tonDau * $giaVon;

            if ($tonDau === 0 && $nhap === 0 && $xuat === 0 && $dc === 0 && $tonCuoi === 0) {
                continue;
            }

            $totDauSL += $tonDau; $totDauGT += $tonDauGT;
            $totNhapSL += $nhap; $totNhapGT += $nhapGT;
            $totXuatSL += $xuat; $totXuatGT += $xuatGT;
            $totDCSL += $dc;
            $totCuoiSL += $tonCuoi; $totCuoiGT += $tonCuoiGT;

            $dcFormatted = $dc > 0 ? "+{$dc}" : ($dc < 0 ? "−" . abs($dc) : "0");

            $rows[] = [
                'cells' => [
                    ['v' => $sp->Ten_san_pham, 'align' => 'left'],
                    ['v' => number_format($tonDau, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($tonDauGT, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => number_format($nhap, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($nhapGT, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => number_format($xuat, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($xuatGT, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => $dcFormatted, 'align' => 'right', 'color' => $dc > 0 ? '#1E5B36' : ($dc < 0 ? '#B42318' : null)],
                    ['v' => number_format($tonCuoi, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($tonCuoiGT, 0, ',', '.') . ' đ', 'align' => 'right'],
                ],
            ];
        }

        $dcTotFormatted = $totDCSL > 0 ? "+{$totDCSL}" : ($totDCSL < 0 ? "−" . abs($totDCSL) : "0");

        $foot = [
            ['v' => 'Tổng cộng', 'align' => 'left'],
            ['v' => number_format($totDauSL, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totDauGT, 0, ',', '.') . ' đ', 'align' => 'right'],
            ['v' => number_format($totNhapSL, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totNhapGT, 0, ',', '.') . ' đ', 'align' => 'right'],
            ['v' => number_format($totXuatSL, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totXuatGT, 0, ',', '.') . ' đ', 'align' => 'right'],
            ['v' => $dcTotFormatted, 'align' => 'right', 'color' => $totDCSL > 0 ? '#1E5B36' : ($totDCSL < 0 ? '#B42318' : null)],
            ['v' => number_format($totCuoiSL, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totCuoiGT, 0, ',', '.') . ' đ', 'align' => 'right'],
        ];

        $kpis = [
            ['label' => 'Tồn đầu kỳ (GT)', 'value' => number_format($totDauGT, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => false],
            ['label' => 'Nhập trong kỳ (GT)', 'value' => number_format($totNhapGT, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => false],
            ['label' => 'Xuất trong kỳ (GT)', 'value' => number_format($totXuatGT, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => false],
            ['label' => 'Tồn cuối kỳ (GT)', 'value' => number_format($totCuoiGT, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => true],
        ];

        return [
            'name' => 'Nhập – xuất – tồn',
            'desc' => 'Biến động số lượng và giá trị trong kỳ. Tồn cuối = tồn đầu + nhập − xuất ± điều chỉnh.',
            'note' => 'Điều chỉnh là chênh lệch kiểm kê đã duyệt.',
            'searchLabel' => 'Sản phẩm',
            'searchPh' => 'Mã, tên sản phẩm',
            'range' => true,
            'kpis' => $kpis,
            'cols' => $cols,
            'rows' => $rows,
            'foot' => $foot,
            'hasChart' => false,
        ];
    }

    /**
     * Tab 2: Doanh thu – lãi gộp
     */
    private function getDoanhThuLaiGopData(string $maKho, string $tuNgay, string $denNgay, string $q): array
    {
        $query = DB::table('Don_hang_ban as dhb')
            ->join('Chi_tiet_don_ban as ctdb', 'ctdb.Ma_don_hang_ban', '=', 'dhb.Ma_don_hang_ban')
            ->join('San_pham as sp', 'sp.Ma_san_pham', '=', 'ctdb.Ma_san_pham')
            ->join('Phieu_xuat_kho as px', 'px.Ma_don_hang_ban', '=', 'dhb.Ma_don_hang_ban')
            ->where('dhb.Trang_thai', 'Hoàn thành')
            ->whereBetween(DB::raw('DATE(dhb.Thoi_diem_xac_nhan)'), [$tuNgay, $denNgay]);

        if ($maKho !== 'all') {
            $query->where('dhb.Ma_kho', $maKho);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('sp.Ma_san_pham', 'like', "%{$q}%")
                    ->orWhere('sp.Ten_san_pham', 'like', "%{$q}%")
                    ->orWhere('dhb.Ten_nguoi_nhan', 'like', "%{$q}%");
            });
        }

        // Bán hàng theo SP
        $banHangs = $query->groupBy('sp.Ma_san_pham', 'sp.Ten_san_pham')
            ->select(
                'sp.Ma_san_pham',
                'sp.Ten_san_pham',
                DB::raw('COUNT(DISTINCT dhb.Ma_don_hang_ban) as so_don'),
                DB::raw('SUM(ctdb.So_luong) as sl_ban'),
                DB::raw('SUM(ctdb.So_luong * ctdb.Don_gia_ban) as doanh_thu'),
                DB::raw('SUM(ctdb.So_luong * (sp.Gia_niem_yet - ctdb.Don_gia_ban)) as tong_chiet_khau')
            )->get();

        // Giá vốn bán hàng từ Chi_tiet_phieu_xuat
        $cogsQ = DB::table('Phieu_xuat_kho as px')
            ->join('Don_hang_ban as dhb', 'dhb.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Chi_tiet_phieu_xuat as ct', 'ct.Ma_phieu_xuat', '=', 'px.Ma_phieu_xuat')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo_thuc_xuat')
            ->where('dhb.Trang_thai', 'Hoàn thành')
            ->whereBetween(DB::raw('DATE(dhb.Thoi_diem_xac_nhan)'), [$tuNgay, $denNgay]);
        if ($maKho !== 'all') {
            $cogsQ->where('dhb.Ma_kho', $maKho);
        }
        $cogsMap = $cogsQ->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(ct.So_luong * COALESCE(ct.Gia_von_xuat, 0)) as tong_gia_von'))
            ->pluck('tong_gia_von', 'l.Ma_san_pham')
            ->all();

        // Trừ hàng khách trả đã nhập kho lại
        $traQ = DB::table('Phieu_nhap_kho as pn')
            ->join('Chi_tiet_phieu_nhap as ct', 'ct.Ma_phieu_nhap', '=', 'pn.Ma_phieu_nhap')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo')
            ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'pn.Ma_phieu_xuat')
            ->join('Don_hang_ban as dhb', 'dhb.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Chi_tiet_don_ban as ctdb', function ($j) {
                $j->on('ctdb.Ma_don_hang_ban', '=', 'dhb.Ma_don_hang_ban')
                  ->on('ctdb.Ma_san_pham', '=', 'l.Ma_san_pham');
            })
            ->where('pn.Loai_nhap', 'Khách trả hàng')
            ->where('pn.Trang_thai', 'Đã nhập kho')
            ->whereBetween(DB::raw('DATE(pn.Thoi_diem_xac_nhan)'), [$tuNgay, $denNgay]);
        if ($maKho !== 'all') {
            $traQ->where('pn.Ma_kho', $maKho);
        }
        $traMap = $traQ->groupBy('l.Ma_san_pham')
            ->select(
                'l.Ma_san_pham',
                DB::raw('SUM(ct.So_luong_nhap) as sl_tra'),
                DB::raw('SUM(ct.So_luong_nhap * ctdb.Don_gia_ban) as dt_tra')
            )->get()->keyBy('Ma_san_pham');

        $cols = [
            ['label' => 'Sản phẩm', 'align' => 'left'],
            ['label' => 'Số đơn', 'align' => 'right'],
            ['label' => 'SL bán', 'align' => 'right'],
            ['label' => 'Doanh thu', 'align' => 'right'],
            ['label' => 'Chiết khấu', 'align' => 'right'],
            ['label' => 'Giá vốn', 'align' => 'right'],
            ['label' => 'Lãi gộp', 'align' => 'right'],
            ['label' => 'Tỷ lệ lãi gộp', 'align' => 'right'],
        ];

        $rows = [];
        $totDon = 0; $totSL = 0; $totDT = 0; $totCK = 0; $totGV = 0; $totLG = 0;

        foreach ($banHangs as $b) {
            $msp = $b->Ma_san_pham;
            $tra = $traMap[$msp] ?? null;
            $slTra = (int) ($tra->sl_tra ?? 0);
            $dtTra = (float) ($tra->dt_tra ?? 0);

            $slBan = max(0, (int) $b->sl_ban - $slTra);
            $dt = max(0, (float) $b->doanh_thu - $dtTra);
            $ck = max(0, (float) $b->tong_chiet_khau);
            $gv = (float) ($cogsMap[$msp] ?? 0);
            $lg = $dt - $gv;
            $tyLe = $dt > 0 ? round(($lg / $dt) * 100, 1) : 0;

            $totDon += (int) $b->so_don;
            $totSL += $slBan;
            $totDT += $dt;
            $totCK += $ck;
            $totGV += $gv;
            $totLG += $lg;

            $rows[] = [
                'cells' => [
                    ['v' => $b->Ten_san_pham, 'align' => 'left'],
                    ['v' => number_format($b->so_don, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($slBan, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($dt, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => number_format($ck, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => number_format($gv, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => number_format($lg, 0, ',', '.') . ' đ', 'align' => 'right', 'color' => $lg >= 0 ? '#1E5B36' : '#B42318'],
                    ['v' => "{$tyLe}%", 'align' => 'right', 'color' => $tyLe >= 0 ? '#1E5B36' : '#B42318'],
                ],
            ];
        }

        $totTyLe = $totDT > 0 ? round(($totLG / $totDT) * 100, 1) : 0;

        $foot = [
            ['v' => 'Tổng cộng', 'align' => 'left'],
            ['v' => number_format($totDon, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totSL, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totDT, 0, ',', '.') . ' đ', 'align' => 'right'],
            ['v' => number_format($totCK, 0, ',', '.') . ' đ', 'align' => 'right'],
            ['v' => number_format($totGV, 0, ',', '.') . ' đ', 'align' => 'right'],
            ['v' => number_format($totLG, 0, ',', '.') . ' đ', 'align' => 'right', 'color' => $totLG >= 0 ? '#1E5B36' : '#B42318'],
            ['v' => "{$totTyLe}%", 'align' => 'right', 'color' => $totTyLe >= 0 ? '#1E5B36' : '#B42318'],
        ];

        $kpis = [
            ['label' => 'Doanh thu', 'value' => number_format($totDT, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => true],
            ['label' => 'Giá vốn', 'value' => number_format($totGV, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => false],
            ['label' => 'Lãi gộp', 'value' => number_format($totLG, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => false],
            ['label' => 'Tỷ lệ lãi gộp', 'value' => "{$totTyLe}%", 'delta' => '', 'highlight' => false],
        ];

        // Biểu đồ 12 tháng
        $bars = [];
        $currentYear = Carbon::now()->year;
        for ($m = 1; $m <= 12; $m++) {
            $mStart = Carbon::create($currentYear, $m, 1)->startOfMonth()->toDateTimeString();
            $mEnd = Carbon::create($currentYear, $m, 1)->endOfMonth()->toDateTimeString();

            $mDT = (float) DB::table('Don_hang_ban as dhb')
                ->join('Chi_tiet_don_ban as ctdb', 'ctdb.Ma_don_hang_ban', '=', 'dhb.Ma_don_hang_ban')
                ->where('dhb.Trang_thai', 'Hoàn thành')
                ->whereBetween('dhb.Thoi_diem_xac_nhan', [$mStart, $mEnd])
                ->sum(DB::raw('ctdb.So_luong * ctdb.Don_gia_ban'));

            $mGV = (float) DB::table('Phieu_xuat_kho as px')
                ->join('Chi_tiet_phieu_xuat as ct', 'ct.Ma_phieu_xuat', '=', 'px.Ma_phieu_xuat')
                ->where('px.Trang_thai_giao_hang', 'Giao thành công')
                ->whereBetween('px.Thoi_diem_xuat_kho', [$mStart, $mEnd])
                ->sum(DB::raw('ct.So_luong * COALESCE(ct.Gia_von_xuat, 0)'));

            $mLG = max(0, $mDT - $mGV);

            $bars[] = [
                'm' => "T{$m}",
                'dt' => $mDT,
                'lg' => $mLG,
                'tip' => "Tháng {$m}: DT " . number_format($mDT, 0, ',', '.') . ' đ, Lãi gộp ' . number_format($mLG, 0, ',', '.') . ' đ',
            ];
        }

        $maxVal = max(1, ...array_map(fn($b) => max($b['dt'], $b['lg']), $bars));
        foreach ($bars as &$b) {
            $b['revHeight'] = round(($b['dt'] / $maxVal) * 100);
            $b['gpHeight'] = round(($b['lg'] / $maxVal) * 100);
        }

        return [
            'name' => 'Doanh thu – lãi gộp',
            'desc' => 'Theo đơn giao thành công trong kỳ, chưa thuế GTGT.',
            'note' => 'Giá vốn theo giá vốn chụp lúc xuất kho. Hàng khách trả được trừ theo số nhập lại.',
            'searchLabel' => 'Khách hàng / nhân viên',
            'searchPh' => 'Tên khách hàng hoặc mã sản phẩm',
            'range' => true,
            'kpis' => $kpis,
            'cols' => $cols,
            'rows' => $rows,
            'foot' => $foot,
            'hasChart' => true,
            'bars' => $bars,
        ];
    }

    /**
     * Tab 3: Mua hàng
     */
    private function getMuaHangData(string $maKho, string $tuNgay, string $denNgay, string $q): array
    {
        $query = DonDatHang::with(['kho', 'nhaCungCap', 'chiTiets', 'phieuNhapKhos.chiTiets'])
            ->whereBetween('Ngay_lap', [$tuNgay, $denNgay])
            ->whereNotIn('Trang_thai', ['Đang cập nhật', 'Từ chối', 'Đề xuất đã hủy']);

        if ($maKho !== 'all') {
            $query->where('Ma_kho', $maKho);
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('Ma_don_dat_hang', 'like', "%{$q}%")
                    ->orWhereHas('nhaCungCap', function ($ncc) use ($q) {
                        $ncc->where('Ten_ncc', 'like', "%{$q}%");
                    });
            });
        }

        $dons = $query->orderBy('Ngay_lap', 'desc')->get();

        $cols = [
            ['label' => 'Đơn đặt hàng', 'align' => 'left'],
            ['label' => 'Kho', 'align' => 'left'],
            ['label' => 'Giá trị đặt', 'align' => 'right'],
            ['label' => 'Đã nhận', 'align' => 'right'],
            ['label' => 'Hao hụt SL', 'align' => 'right'],
            ['label' => 'Hao hụt GT', 'align' => 'right'],
            ['label' => 'Đóng thiếu GT', 'align' => 'right'],
            ['label' => 'Còn chờ nhận', 'align' => 'right'],
        ];

        $rows = [];
        $totDat = 0; $totDaNhan = 0; $totHaoSL = 0; $totHaoGT = 0; $totDongThieu = 0; $totChoNhan = 0;

        foreach ($dons as $d) {
            $giaTriDat = (float) $d->Tong_gia_tri;

            // Tính đơn giá mua từng sản phẩm để tính hao hụt
            $dgMap = $d->chiTiets->pluck('Don_gia_mua', 'Ma_san_pham')->all();

            $daNhanGT = 0;
            $haoSL = 0;
            $haoGT = 0;

            foreach ($d->phieuNhapKhos->where('Trang_thai', 'Đã nhập kho') as $pn) {
                foreach ($pn->chiTiets as $ct) {
                    $spId = $ct->lo?->Ma_san_pham;
                    $dg = (float) ($dgMap[$spId] ?? 0);
                    $daNhanGT += $ct->So_luong_nhap * $dg;
                    $haoSL += $ct->So_luong_hao_hut;
                    $haoGT += $ct->So_luong_hao_hut * $dg;
                }
            }

            $dongThieuGT = 0;
            if ($d->Trang_thai === 'Đóng thiếu') {
                $dongThieuGT = max(0, $giaTriDat - $daNhanGT - $haoGT);
                $choNhanGT = 0;
            } else {
                $choNhanGT = max(0, $giaTriDat - $daNhanGT - $haoGT);
            }

            $totDat += $giaTriDat;
            $totDaNhan += $daNhanGT;
            $totHaoSL += $haoSL;
            $totHaoGT += $haoGT;
            $totDongThieu += $dongThieuGT;
            $totChoNhan += $choNhanGT;

            $rows[] = [
                'cells' => [
                    ['v' => $d->Ma_don_dat_hang, 'align' => 'left'],
                    ['v' => $d->kho->Ten_kho ?? '—', 'align' => 'left'],
                    ['v' => number_format($giaTriDat, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => number_format($daNhanGT, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => number_format($haoSL, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($haoGT, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => number_format($dongThieuGT, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => number_format($choNhanGT, 0, ',', '.') . ' đ', 'align' => 'right'],
                ],
            ];
        }

        $foot = [
            ['v' => 'Tổng cộng', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => number_format($totDat, 0, ',', '.') . ' đ', 'align' => 'right'],
            ['v' => number_format($totDaNhan, 0, ',', '.') . ' đ', 'align' => 'right'],
            ['v' => number_format($totHaoSL, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totHaoGT, 0, ',', '.') . ' đ', 'align' => 'right'],
            ['v' => number_format($totDongThieu, 0, ',', '.') . ' đ', 'align' => 'right'],
            ['v' => number_format($totChoNhan, 0, ',', '.') . ' đ', 'align' => 'right'],
        ];

        $kpis = [
            ['label' => 'Giá trị đặt', 'value' => number_format($totDat, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => true],
            ['label' => 'Giá trị đã nhận', 'value' => number_format($totDaNhan, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => false],
            ['label' => 'Hao hụt GT', 'value' => number_format($totHaoGT, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => false],
            ['label' => 'Còn chờ nhận', 'value' => number_format($totChoNhan, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => false],
        ];

        return [
            'name' => 'Mua hàng',
            'desc' => 'Giá trị đặt, đã nhận, hao hụt và đóng thiếu theo nhà cung cấp.',
            'note' => 'Hao hụt và đóng thiếu tính theo đơn giá mua.',
            'searchLabel' => 'Nhà cung cấp',
            'searchPh' => 'Tên nhà cung cấp hoặc mã đơn',
            'range' => true,
            'kpis' => $kpis,
            'cols' => $cols,
            'rows' => $rows,
            'foot' => $foot,
            'hasChart' => false,
        ];
    }

    /**
     * Tab 4: Hàng trả và giao thất bại
     */
    private function getHangTraData(string $maKho, string $tuNgay, string $denNgay, string $q): array
    {
        $query = PhieuXuatKho::with(['donHangBan.khachHang', 'chiTiets'])
            ->whereNotNull('Trang_thai_hoan_hang')
            ->whereBetween(DB::raw('COALESCE(DATE(Thoi_diem_xu_ly_hoan), DATE(Thoi_diem_ket_qua_giao), DATE(Ngay_tao))'), [$tuNgay, $denNgay]);

        if ($maKho !== 'all') {
            $query->whereHas('donHangBan', function ($dhb) use ($maKho) {
                $dhb->where('Ma_kho', $maKho);
            });
        }
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('Ma_phieu_xuat', 'like', "%{$q}%")
                    ->orWhere('Ma_don_hang_ban', 'like', "%{$q}%")
                    ->orWhereHas('donHangBan.khachHang', function ($kh) use ($q) {
                        $kh->where('Ten_khach_hang', 'like', "%{$q}%");
                    });
            });
        }

        $pxs = $query->orderBy('Ngay_tao', 'desc')->get();

        $cols = [
            ['label' => 'Phiếu xuất', 'align' => 'left'],
            ['label' => 'Đơn bán', 'align' => 'left'],
            ['label' => 'Khách hàng', 'align' => 'left'],
            ['label' => 'SL nhập lại', 'align' => 'right'],
            ['label' => 'SL không nhập', 'align' => 'right'],
            ['label' => 'GT không nhập', 'align' => 'right'],
            ['label' => 'Lý do', 'align' => 'left'],
        ];

        $rows = [];
        $totNhapLai = 0; $totKhongNhap = 0; $totGTKhongNhap = 0;

        foreach ($pxs as $px) {
            $slXuat = (int) $px->chiTiets->sum('So_luong');
            $giaVonAvg = (float) $px->chiTiets->avg('Gia_von_xuat');

            // Xem có phiếu nhập hàng trả tương ứng không
            $pn = PhieuNhapKho::where('Ma_phieu_xuat', $px->Ma_phieu_xuat)->where('Trang_thai', 'Đã nhập kho')->first();
            $slNhapLai = $pn ? (int) $pn->chiTiets->sum('So_luong_nhap') : 0;
            $slKhongNhap = max(0, $slXuat - $slNhapLai);
            $gtKhongNhap = $slKhongNhap * $giaVonAvg;

            $lyDo = $px->Ghi_chu_xu_ly_hoan ?? $px->Ly_do_giao_that_bai ?? 'Hàng hoàn';

            $totNhapLai += $slNhapLai;
            $totKhongNhap += $slKhongNhap;
            $totGTKhongNhap += $gtKhongNhap;

            $rows[] = [
                'cells' => [
                    ['v' => $px->Ma_phieu_xuat, 'align' => 'left'],
                    ['v' => $px->Ma_don_hang_ban, 'align' => 'left'],
                    ['v' => $px->donHangBan->khachHang->Ten_khach_hang ?? 'Khách lẻ', 'align' => 'left'],
                    ['v' => number_format($slNhapLai, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($slKhongNhap, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($gtKhongNhap, 0, ',', '.') . ' đ', 'align' => 'right'],
                    ['v' => $lyDo, 'align' => 'left'],
                ],
            ];
        }

        $foot = [
            ['v' => 'Tổng cộng', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => number_format($totNhapLai, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totKhongNhap, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totGTKhongNhap, 0, ',', '.') . ' đ', 'align' => 'right'],
            ['v' => '', 'align' => 'left'],
        ];

        $kpis = [
            ['label' => 'Số phiếu phát sinh', 'value' => number_format($pxs->count(), 0, ',', '.'), 'delta' => '', 'highlight' => false],
            ['label' => 'SL nhập lại kho', 'value' => number_format($totNhapLai, 0, ',', '.'), 'delta' => '', 'highlight' => false],
            ['label' => 'SL không nhập kho', 'value' => number_format($totKhongNhap, 0, ',', '.'), 'delta' => '', 'highlight' => false],
            ['label' => 'Giá trị hàng mất', 'value' => number_format($totGTKhongNhap, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => true],
        ];

        return [
            'name' => 'Hàng trả và giao thất bại',
            'desc' => 'Phần nhập lại kho và phần không nhập kho của hàng khách trả và hàng giao thất bại.',
            'note' => 'Giá trị không nhập kho là hàng mất, tính theo giá vốn lúc xuất.',
            'searchLabel' => 'Khách hàng',
            'searchPh' => 'Tên khách hàng, mã đơn',
            'range' => true,
            'kpis' => $kpis,
            'cols' => $cols,
            'rows' => $rows,
            'foot' => $foot,
            'hasChart' => false,
        ];
    }

    /**
     * Tab 5: Điều chỉnh tồn (Kiểm kê + Hàng hỏng)
     */
    private function getDieuChinhData(string $maKho, string $tuNgay, string $denNgay, string $q): array
    {
        $cols = [
            ['label' => 'Ngày', 'align' => 'left'],
            ['label' => 'Chứng từ', 'align' => 'left'],
            ['label' => 'Kho', 'align' => 'left'],
            ['label' => 'Sản phẩm', 'align' => 'left'],
            ['label' => 'Lô', 'align' => 'left'],
            ['label' => 'SL', 'align' => 'right'],
            ['label' => 'Giá trị', 'align' => 'right'],
        ];

        $rows = [];
        $totThuaGT = 0; $totThieuGT = 0; $totHongGT = 0; $totNetGT = 0;

        // 1. Chênh lệch kiểm kê đã duyệt
        $kkQ = DB::table('Chi_tiet_kiem_ke as ct')
            ->join('Phieu_kiem_ke as pk', 'pk.Ma_phieu_kiem_ke', '=', 'ct.Ma_phieu_kiem_ke')
            ->join('Kho as k', 'k.Ma_kho', '=', 'pk.Ma_kho')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo')
            ->join('San_pham as sp', 'sp.Ma_san_pham', '=', 'l.Ma_san_pham')
            ->where('pk.Trang_thai', 'Hoàn tất')
            ->whereRaw('ct.So_luong_thuc_te != ct.So_luong_he_thong')
            ->whereBetween(DB::raw('DATE(pk.Thoi_diem_duyet)'), [$tuNgay, $denNgay]);

        if ($maKho !== 'all') {
            $kkQ->where('pk.Ma_kho', $maKho);
        }
        if ($q !== '') {
            $kkQ->where(function ($sub) use ($q) {
                $sub->where('sp.Ma_san_pham', 'like', "%{$q}%")
                    ->orWhere('sp.Ten_san_pham', 'like', "%{$q}%")
                    ->orWhere('pk.Ma_phieu_kiem_ke', 'like', "%{$q}%");
            });
        }

        $kkItems = $kkQ->select(
            'pk.Thoi_diem_duyet as Ngay',
            'pk.Ma_phieu_kiem_ke as Chung_tu',
            'k.Ten_kho',
            'sp.Ten_san_pham',
            'l.Ma_lo_ncc',
            DB::raw('(ct.So_luong_thuc_te - ct.So_luong_he_thong) as SL'),
            DB::raw('COALESCE(ct.Gia_von_dieu_chinh, sp.Gia_von_binh_quan, 0) as Don_gia')
        )->get();

        foreach ($kkItems as $it) {
            $sl = (int) $it->SL;
            $gt = $sl * (float) $it->Don_gia;
            $totNetGT += $gt;
            if ($gt > 0) {
                $totThuaGT += $gt;
            } else {
                $totThieuGT += abs($gt);
            }

            $slFormatted = $sl > 0 ? "+{$sl}" : "−" . abs($sl);
            $gtFormatted = ($gt >= 0 ? "+" : "−") . number_format(abs($gt), 0, ',', '.') . ' đ';

            $rows[] = [
                'cells' => [
                    ['v' => $it->Ngay ? Carbon::parse($it->Ngay)->format('d/m/Y') : '', 'align' => 'left'],
                    ['v' => $it->Chung_tu, 'align' => 'left'],
                    ['v' => $it->Ten_kho, 'align' => 'left'],
                    ['v' => $it->Ten_san_pham, 'align' => 'left'],
                    ['v' => $it->Ma_lo_ncc ?? '—', 'align' => 'left'],
                    ['v' => $slFormatted, 'align' => 'right', 'color' => $sl > 0 ? '#1E5B36' : '#B42318'],
                    ['v' => $gtFormatted, 'align' => 'right', 'color' => $gt >= 0 ? '#1E5B36' : '#B42318'],
                ],
            ];
        }

        // 2. Hàng hỏng khi đổi lô
        $hhQ = DB::table('Chi_tiet_phieu_xuat as ct')
            ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'ct.Ma_phieu_xuat')
            ->join('Don_hang_ban as dhb', 'dhb.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Kho as k', 'k.Ma_kho', '=', 'dhb.Ma_kho')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo_phan_bo')
            ->join('San_pham as sp', 'sp.Ma_san_pham', '=', 'l.Ma_san_pham')
            ->where('ct.So_luong_hong', '>', 0)
            ->whereBetween(DB::raw('DATE(ct.Thoi_diem_doi_lo)'), [$tuNgay, $denNgay]);

        if ($maKho !== 'all') {
            $hhQ->where('dhb.Ma_kho', $maKho);
        }
        if ($q !== '') {
            $hhQ->where(function ($sub) use ($q) {
                $sub->where('sp.Ma_san_pham', 'like', "%{$q}%")
                    ->orWhere('sp.Ten_san_pham', 'like', "%{$q}%")
                    ->orWhere('px.Ma_phieu_xuat', 'like', "%{$q}%");
            });
        }

        $hhItems = $hhQ->select(
            'ct.Thoi_diem_doi_lo as Ngay',
            'px.Ma_phieu_xuat as Chung_tu',
            'k.Ten_kho',
            'sp.Ten_san_pham',
            'l.Ma_lo_ncc',
            'ct.So_luong_hong as SL',
            DB::raw('COALESCE(ct.Gia_von_hang_hong, sp.Gia_von_binh_quan, 0) as Don_gia')
        )->get();

        foreach ($hhItems as $it) {
            $sl = (int) $it->SL;
            $gt = -1 * ($sl * (float) $it->Don_gia);
            $totHongGT += abs($gt);
            $totNetGT += $gt;

            $slFormatted = "−{$sl}";
            $gtFormatted = "−" . number_format(abs($gt), 0, ',', '.') . ' đ';

            $rows[] = [
                'cells' => [
                    ['v' => $it->Ngay ? Carbon::parse($it->Ngay)->format('d/m/Y') : '', 'align' => 'left'],
                    ['v' => $it->Chung_tu . ' (Hỏng)', 'align' => 'left'],
                    ['v' => $it->Ten_kho, 'align' => 'left'],
                    ['v' => $it->Ten_san_pham, 'align' => 'left'],
                    ['v' => $it->Ma_lo_ncc ?? '—', 'align' => 'left'],
                    ['v' => $slFormatted, 'align' => 'right', 'color' => '#B42318'],
                    ['v' => $gtFormatted, 'align' => 'right', 'color' => '#B42318'],
                ],
            ];
        }

        $totNetFormatted = ($totNetGT >= 0 ? "+" : "−") . number_format(abs($totNetGT), 0, ',', '.') . ' đ';

        $foot = [
            ['v' => 'Tổng cộng', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => '', 'align' => 'right'],
            ['v' => $totNetFormatted, 'align' => 'right', 'color' => $totNetGT >= 0 ? '#1E5B36' : '#B42318'],
        ];

        $kpis = [
            ['label' => 'Kiểm kê thừa', 'value' => number_format($totThuaGT, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => false],
            ['label' => 'Kiểm kê thiếu', 'value' => number_format($totThieuGT, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => false],
            ['label' => 'Hàng hỏng', 'value' => number_format($totHongGT, 0, ',', '.') . ' đ', 'delta' => '', 'highlight' => false],
            ['label' => 'Tổng điều chỉnh', 'value' => $totNetFormatted, 'delta' => '', 'highlight' => true],
        ];

        return [
            'name' => 'Điều chỉnh tồn',
            'desc' => 'Chênh lệch kiểm kê đã duyệt và hàng hỏng ghi nhận khi đổi lô.',
            'note' => 'Kiểm kê tính theo giá vốn lúc duyệt; hàng hỏng tính theo giá vốn lúc đổi lô.',
            'searchLabel' => 'Sản phẩm',
            'searchPh' => 'Mã, tên sản phẩm',
            'range' => true,
            'kpis' => $kpis,
            'cols' => $cols,
            'rows' => $rows,
            'foot' => $foot,
            'hasChart' => false,
        ];
    }
}

