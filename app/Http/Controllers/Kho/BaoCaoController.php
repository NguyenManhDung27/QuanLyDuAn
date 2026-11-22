<?php

namespace App\Http\Controllers\Kho;

use App\Http\Controllers\Controller;
use App\Models\ChiTietDonDatHang;
use App\Models\ChiTietKiemKe;
use App\Models\ChiTietPhieuNhap;
use App\Models\ChiTietPhieuXuat;
use App\Models\DonDatHang;
use App\Models\Kho;
use App\Models\NhaCungCap;
use App\Models\PhieuKiemKe;
use App\Models\PhieuNhapKho;
use App\Models\PhieuXuatKho;
use App\Models\SanPham;
use App\Models\TonKho;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BaoCaoController extends Controller
{
    /**
     * QLK-11: Báo cáo của Quản lý kho (4 tabs)
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $maKho = $user->Ma_kho ?? 'KHO_HN';
        $kho = Kho::find($maKho);

        $tab = (int) $request->input('tab', 0);
        $tuNgay = $request->input('tu_ngay', Carbon::now()->startOfMonth()->toDateString());
        $denNgay = $request->input('den_ngay', Carbon::now()->toDateString());
        $q = trim((string) $request->input('q', ''));

        $data = $this->getDataForTab($tab, $maKho, $tuNgay, $denNgay, $q);

        return view('kho.bao-cao', compact('user', 'kho', 'tab', 'tuNgay', 'denNgay', 'q', 'data'));
    }

    /**
     * Xuất Excel / CSV cho QLK-11
     */
    public function export(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $maKho = $user->Ma_kho ?? 'KHO_HN';

        $tab = (int) $request->input('tab', 0);
        $tuNgay = $request->input('tu_ngay', Carbon::now()->startOfMonth()->toDateString());
        $denNgay = $request->input('den_ngay', Carbon::now()->toDateString());
        $q = trim((string) $request->input('q', ''));

        $data = $this->getDataForTab($tab, $maKho, $tuNgay, $denNgay, $q);

        $tabNames = [
            0 => 'Nhap_Xuat_Ton',
            1 => 'Giao_Hang_NCC',
            2 => 'Hang_Hong',
            3 => 'Chenh_Lech_Kiem_Ke',
        ];
        $tabSlug = $tabNames[$tab] ?? 'Bao_Cao';
        $filename = "QLK_BaoCao_{$tabSlug}_{$tuNgay}_{$denNgay}.csv";

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens cleanly
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

    /**
     * Tính toán dữ liệu theo từng tab
     */
    private function getDataForTab(int $tab, string $maKho, string $tuNgay, string $denNgay, string $q): array
    {
        return match ($tab) {
            0 => $this->getNhapXuatTonData($maKho, $tuNgay, $denNgay, $q),
            1 => $this->getGiaoHangNccData($maKho, $tuNgay, $denNgay, $q),
            2 => $this->getHangHongData($maKho, $tuNgay, $denNgay, $q),
            3 => $this->getChenhLechKiemKeData($maKho, $tuNgay, $denNgay, $q),
            default => $this->getNhapXuatTonData($maKho, $tuNgay, $denNgay, $q),
        };
    }

    /**
     * Tab 0: Nhập – xuất – tồn
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

        // Nhập trong kỳ theo SP
        $nhapMap = DB::table('Phieu_nhap_kho as pn')
            ->join('Chi_tiet_phieu_nhap as ct', 'ct.Ma_phieu_nhap', '=', 'pn.Ma_phieu_nhap')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo')
            ->where('pn.Ma_kho', $maKho)
            ->where('pn.Trang_thai', 'Đã nhập kho')
            ->whereBetween(DB::raw('DATE(pn.Thoi_diem_xac_nhan)'), [$tuNgay, $denNgay])
            ->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(ct.So_luong_nhap) as tong_nhap'))
            ->pluck('tong_nhap', 'l.Ma_san_pham')
            ->all();

        // Xuất trong kỳ theo SP (Xuất bán đã xuất kho + hàng hỏng khi đổi lô)
        $xuatBanMap = DB::table('Phieu_xuat_kho as px')
            ->join('Don_hang_ban as dhb', 'dhb.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Chi_tiet_phieu_xuat as ct', 'ct.Ma_phieu_xuat', '=', 'px.Ma_phieu_xuat')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo_thuc_xuat')
            ->where('dhb.Ma_kho', $maKho)
            ->whereIn('px.Trang_thai_giao_hang', ['Đang giao', 'Giao thành công', 'Giao hàng thất bại'])
            ->whereBetween(DB::raw('DATE(px.Thoi_diem_xuat_kho)'), [$tuNgay, $denNgay])
            ->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(ct.So_luong) as tong_xuat'))
            ->pluck('tong_xuat', 'l.Ma_san_pham')
            ->all();

        $hangHongMap = DB::table('Chi_tiet_phieu_xuat as ct')
            ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'ct.Ma_phieu_xuat')
            ->join('Don_hang_ban as dhb', 'dhb.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo_phan_bo')
            ->where('dhb.Ma_kho', $maKho)
            ->where('ct.So_luong_hong', '>', 0)
            ->whereBetween(DB::raw('DATE(ct.Thoi_diem_doi_lo)'), [$tuNgay, $denNgay])
            ->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(ct.So_luong_hong) as tong_hong'))
            ->pluck('tong_hong', 'l.Ma_san_pham')
            ->all();

        // Điều chỉnh kiểm kê trong kỳ theo SP
        $dieuChinhMap = DB::table('Phieu_kiem_ke as pk')
            ->join('Chi_tiet_kiem_ke as ct', 'ct.Ma_phieu_kiem_ke', '=', 'pk.Ma_phieu_kiem_ke')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo')
            ->where('pk.Ma_kho', $maKho)
            ->where('pk.Trang_thai', 'Hoàn tất')
            ->whereBetween(DB::raw('DATE(pk.Thoi_diem_duyet)'), [$tuNgay, $denNgay])
            ->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(ct.So_luong_thuc_te - ct.So_luong_he_thong) as tong_lech'))
            ->pluck('tong_lech', 'l.Ma_san_pham')
            ->all();

        // Tồn thực hiện tại
        $tonThucMap = DB::table('Ton_kho as tk')
            ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo')
            ->where('tk.Ma_kho', $maKho)
            ->groupBy('l.Ma_san_pham')
            ->select('l.Ma_san_pham', DB::raw('SUM(tk.Ton_thuc) as tong_ton'))
            ->pluck('tong_ton', 'l.Ma_san_pham')
            ->all();

        $cols = [
            ['label' => 'Mã SP', 'align' => 'left'],
            ['label' => 'Sản phẩm', 'align' => 'left'],
            ['label' => 'Tồn đầu kỳ', 'align' => 'right'],
            ['label' => 'Nhập', 'align' => 'right'],
            ['label' => 'Xuất', 'align' => 'right'],
            ['label' => 'Điều chỉnh kiểm kê', 'align' => 'right'],
            ['label' => 'Tồn cuối kỳ', 'align' => 'right'],
        ];

        $rows = [];
        $tDau = 0; $tNhap = 0; $tXuat = 0; $tDC = 0; $tCuoi = 0;

        foreach ($sanPhams as $sp) {
            $msp = $sp->Ma_san_pham;
            $nhap = (int) ($nhapMap[$msp] ?? 0);
            $xuat = (int) ($xuatBanMap[$msp] ?? 0) + (int) ($hangHongMap[$msp] ?? 0);
            $dc = (int) ($dieuChinhMap[$msp] ?? 0);
            $tonCuoi = (int) ($tonThucMap[$msp] ?? 0);
            $tonDau = max(0, $tonCuoi - $nhap + $xuat - $dc);

            // Bỏ qua dòng nếu tất cả bằng 0 và không có tồn
            if ($tonDau === 0 && $nhap === 0 && $xuat === 0 && $dc === 0 && $tonCuoi === 0) {
                continue;
            }

            $tDau += $tonDau;
            $tNhap += $nhap;
            $tXuat += $xuat;
            $tDC += $dc;
            $tCuoi += $tonCuoi;

            $dcFormatted = $dc > 0 ? "+{$dc}" : ($dc < 0 ? "−" . abs($dc) : "0");

            $rows[] = [
                'cells' => [
                    ['v' => $msp, 'align' => 'left'],
                    ['v' => $sp->Ten_san_pham, 'align' => 'left'],
                    ['v' => number_format($tonDau, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($nhap, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($xuat, 0, ',', '.'), 'align' => 'right'],
                    ['v' => $dcFormatted, 'align' => 'right', 'color' => $dc > 0 ? '#1E5B36' : ($dc < 0 ? '#B42318' : null)],
                    ['v' => number_format($tonCuoi, 0, ',', '.'), 'align' => 'right'],
                ],
            ];
        }

        $dcTotalFormatted = $tDC > 0 ? "+{$tDC}" : ($tDC < 0 ? "−" . abs($tDC) : "0");

        $foot = [
            ['v' => 'Tổng cộng', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => number_format($tDau, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($tNhap, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($tXuat, 0, ',', '.'), 'align' => 'right'],
            ['v' => $dcTotalFormatted, 'align' => 'right', 'color' => $tDC > 0 ? '#1E5B36' : ($tDC < 0 ? '#B42318' : null)],
            ['v' => number_format($tCuoi, 0, ',', '.'), 'align' => 'right'],
        ];

        return [
            'name' => 'Nhập – xuất – tồn',
            'desc' => 'Biến động số lượng từng sản phẩm trong kỳ. Tồn cuối kỳ = tồn đầu kỳ + nhập − xuất ± điều chỉnh kiểm kê.',
            'note' => 'Nhập gồm nhập mua và nhập khách trả. Xuất gồm xuất bán và xuất hàng hỏng khi đổi lô.',
            'searchLabel' => 'Sản phẩm',
            'searchPh' => 'Mã, tên sản phẩm',
            'cols' => $cols,
            'rows' => $rows,
            'foot' => $foot,
        ];
    }

    /**
     * Tab 1: Giao hàng theo nhà cung cấp
     */
    private function getGiaoHangNccData(string $maKho, string $tuNgay, string $denNgay, string $q): array
    {
        $nccQuery = NhaCungCap::query();
        if ($q !== '') {
            $nccQuery->where(function ($sub) use ($q) {
                $sub->where('Ma_ncc', 'like', "%{$q}%")
                    ->orWhere('Ten_ncc', 'like', "%{$q}%");
            });
        }
        $nccs = $nccQuery->orderBy('Ten_ncc')->get();

        $cols = [
            ['label' => 'Nhà cung cấp', 'align' => 'left'],
            ['label' => 'Số đơn', 'align' => 'right'],
            ['label' => 'Số lần giao', 'align' => 'right'],
            ['label' => 'Đơn đúng hạn', 'align' => 'right'],
            ['label' => 'Đơn trễ hạn', 'align' => 'right'],
            ['label' => 'SL đặt', 'align' => 'right'],
            ['label' => 'SL nhận đạt', 'align' => 'right'],
            ['label' => 'Hao hụt', 'align' => 'right'],
            ['label' => 'Tỷ lệ hao hụt', 'align' => 'right'],
        ];

        $rows = [];
        $totDon = 0; $totGiao = 0; $totDung = 0; $totTre = 0; $totDat = 0; $totNhan = 0; $totHao = 0;

        foreach ($nccs as $ncc) {
            $donQuery = DonDatHang::where('Ma_ncc', $ncc->Ma_ncc)
                ->where('Ma_kho', $maKho)
                ->whereBetween('Ngay_lap', [$tuNgay, $denNgay])
                ->whereNotIn('Trang_thai', ['Đang cập nhật', 'Từ chối', 'Đề xuất đã hủy']);

            $dons = $donQuery->get();
            $soDon = $dons->count();
            if ($soDon === 0) {
                continue;
            }

            $maDons = $dons->pluck('Ma_don_dat_hang')->all();

            $slDat = (int) ChiTietDonDatHang::whereIn('Ma_don_dat_hang', $maDons)->sum('So_luong_dat');

            $phieuNhaps = PhieuNhapKho::whereIn('Ma_don_dat_hang', $maDons)
                ->where('Trang_thai', 'Đã nhập kho')
                ->get();
            $soLanGiao = $phieuNhaps->count();

            $maPNs = $phieuNhaps->pluck('Ma_phieu_nhap')->all();
            $slNhanDat = (int) ChiTietPhieuNhap::whereIn('Ma_phieu_nhap', $maPNs)->sum('So_luong_nhap');
            $haoHut = (int) ChiTietPhieuNhap::whereIn('Ma_phieu_nhap', $maPNs)->sum('So_luong_hao_hut');

            // Tính đúng hạn / trễ hạn
            $donDungHan = 0;
            foreach ($dons as $d) {
                $lastPn = $phieuNhaps->where('Ma_don_dat_hang', $d->Ma_don_dat_hang)->sortByDesc('Thoi_diem_xac_nhan')->first();
                if ($lastPn && $lastPn->Thoi_diem_xac_nhan) {
                    $ngayNhan = Carbon::parse($lastPn->Thoi_diem_xac_nhan)->toDateString();
                    if ($d->Ngay_du_kien_nhan_du && $ngayNhan <= $d->Ngay_du_kien_nhan_du) {
                        $donDungHan++;
                    }
                } elseif ($d->Ngay_du_kien_nhan_du && Carbon::today()->toDateString() <= $d->Ngay_du_kien_nhan_du) {
                    $donDungHan++;
                }
            }
            $donTreHan = $soDon - $donDungHan;
            $tyLeHaoHut = $slDat > 0 ? round(($haoHut / $slDat) * 100, 1) : 0;

            $totDon += $soDon;
            $totGiao += $soLanGiao;
            $totDung += $donDungHan;
            $totTre += $donTreHan;
            $totDat += $slDat;
            $totNhan += $slNhanDat;
            $totHao += $haoHut;

            $rows[] = [
                'cells' => [
                    ['v' => $ncc->Ten_ncc, 'align' => 'left'],
                    ['v' => number_format($soDon, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($soLanGiao, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($donDungHan, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($donTreHan, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($slDat, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($slNhanDat, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($haoHut, 0, ',', '.'), 'align' => 'right'],
                    ['v' => "{$tyLeHaoHut}%", 'align' => 'right'],
                ],
            ];
        }

        $totTyLe = $totDat > 0 ? round(($totHao / $totDat) * 100, 1) : 0;

        $foot = [
            ['v' => 'Tổng cộng', 'align' => 'left'],
            ['v' => number_format($totDon, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totGiao, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totDung, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totTre, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totDat, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totNhan, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totHao, 0, ',', '.'), 'align' => 'right'],
            ['v' => "{$totTyLe}%", 'align' => 'right'],
        ];

        return [
            'name' => 'Giao hàng theo nhà cung cấp',
            'desc' => 'Mức độ đúng hạn và hao hụt của từng nhà cung cấp, dùng để tham khảo khi lập đề xuất nhập.',
            'note' => 'Đúng hạn: nhận đủ trước hoặc đúng ngày dự kiến nhận đủ. Tỷ lệ hao hụt = hao hụt / số lượng đặt.',
            'searchLabel' => 'Nhà cung cấp',
            'searchPh' => 'Mã, tên nhà cung cấp',
            'cols' => $cols,
            'rows' => $rows,
            'foot' => $foot,
        ];
    }

    /**
     * Tab 2: Hàng hỏng
     */
    private function getHangHongData(string $maKho, string $tuNgay, string $denNgay, string $q): array
    {
        $query = DB::table('Chi_tiet_phieu_xuat as ct')
            ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'ct.Ma_phieu_xuat')
            ->join('Don_hang_ban as dhb', 'dhb.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo_phan_bo')
            ->join('San_pham as sp', 'sp.Ma_san_pham', '=', 'l.Ma_san_pham')
            ->leftJoin('Nguoi_dung as nd', 'nd.Ma_nguoi_dung', '=', 'ct.Ma_nguoi_doi_lo')
            ->where('dhb.Ma_kho', $maKho)
            ->where('ct.So_luong_hong', '>', 0)
            ->whereBetween(DB::raw('DATE(ct.Thoi_diem_doi_lo)'), [$tuNgay, $denNgay]);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('sp.Ma_san_pham', 'like', "%{$q}%")
                    ->orWhere('sp.Ten_san_pham', 'like', "%{$q}%")
                    ->orWhere('ct.Ma_phieu_xuat', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('ct.Thoi_diem_doi_lo', 'desc')
            ->select(
                'ct.Thoi_diem_doi_lo',
                'ct.Ma_phieu_xuat',
                'sp.Ten_san_pham',
                'l.Ma_lo_ncc',
                'ct.So_luong_hong',
                'ct.Ly_do_doi_lo',
                'nd.Ho_ten as Ten_nguoi_ghi_nhan'
            )->get();

        $cols = [
            ['label' => 'Ngày ghi nhận', 'align' => 'left'],
            ['label' => 'Phiếu xuất', 'align' => 'left'],
            ['label' => 'Sản phẩm', 'align' => 'left'],
            ['label' => 'Lô', 'align' => 'left'],
            ['label' => 'SL hỏng', 'align' => 'right'],
            ['label' => 'Ghi chú', 'align' => 'left'],
            ['label' => 'Người ghi nhận', 'align' => 'left'],
        ];

        $rows = [];
        $totHong = 0;

        foreach ($items as $it) {
            $totHong += (int) $it->So_luong_hong;
            $rows[] = [
                'cells' => [
                    ['v' => $it->Thoi_diem_doi_lo ? Carbon::parse($it->Thoi_diem_doi_lo)->format('d/m/Y H:i') : '', 'align' => 'left'],
                    ['v' => $it->Ma_phieu_xuat, 'align' => 'left'],
                    ['v' => $it->Ten_san_pham, 'align' => 'left'],
                    ['v' => $it->Ma_lo_ncc ?? '—', 'align' => 'left'],
                    ['v' => number_format($it->So_luong_hong, 0, ',', '.'), 'align' => 'right'],
                    ['v' => $it->Ly_do_doi_lo ?? '—', 'align' => 'left'],
                    ['v' => $it->Ten_nguoi_ghi_nhan ?? '—', 'align' => 'left'],
                ],
            ];
        }

        $foot = [
            ['v' => 'Tổng cộng', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => number_format($totHong, 0, ',', '.'), 'align' => 'right'],
            ['v' => '', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
        ];

        return [
            'name' => 'Hàng hỏng',
            'desc' => 'Các lần ghi nhận hàng hỏng, vỡ khi đổi lô lúc xuất kho.',
            'note' => 'Số liệu lấy từ các phiếu xuất có đổi lô do hàng hỏng.',
            'searchLabel' => 'Sản phẩm',
            'searchPh' => 'Mã, tên sản phẩm',
            'cols' => $cols,
            'rows' => $rows,
            'foot' => $foot,
        ];
    }

    /**
     * Tab 3: Chênh lệch kiểm kê
     */
    private function getChenhLechKiemKeData(string $maKho, string $tuNgay, string $denNgay, string $q): array
    {
        $query = DB::table('Chi_tiet_kiem_ke as ct')
            ->join('Phieu_kiem_ke as pk', 'pk.Ma_phieu_kiem_ke', '=', 'ct.Ma_phieu_kiem_ke')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ct.Ma_lo')
            ->join('San_pham as sp', 'sp.Ma_san_pham', '=', 'l.Ma_san_pham')
            ->where('pk.Ma_kho', $maKho)
            ->where('pk.Trang_thai', 'Hoàn tất')
            ->whereRaw('ct.So_luong_thuc_te != ct.So_luong_he_thong')
            ->whereBetween(DB::raw('DATE(pk.Thoi_diem_duyet)'), [$tuNgay, $denNgay]);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('sp.Ma_san_pham', 'like', "%{$q}%")
                    ->orWhere('sp.Ten_san_pham', 'like', "%{$q}%")
                    ->orWhere('pk.Ma_phieu_kiem_ke', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('pk.Thoi_diem_duyet', 'desc')
            ->select(
                'pk.Ma_phieu_kiem_ke',
                'pk.Thoi_diem_duyet',
                'sp.Ten_san_pham',
                'l.Ma_lo_ncc',
                'ct.So_luong_he_thong',
                'ct.So_luong_thuc_te',
                'ct.Ly_do_chenh_lech'
            )->get();

        $cols = [
            ['label' => 'Phiếu kiểm kê', 'align' => 'left'],
            ['label' => 'Ngày duyệt', 'align' => 'left'],
            ['label' => 'Sản phẩm', 'align' => 'left'],
            ['label' => 'Lô', 'align' => 'left'],
            ['label' => 'Tồn hệ thống', 'align' => 'right'],
            ['label' => 'Thực đếm', 'align' => 'right'],
            ['label' => 'Chênh lệch', 'align' => 'right'],
            ['label' => 'Lý do', 'align' => 'left'],
        ];

        $rows = [];
        $totHT = 0; $totDem = 0; $totLech = 0;

        foreach ($items as $it) {
            $ht = (int) $it->So_luong_he_thong;
            $dem = (int) $it->So_luong_thuc_te;
            $lech = $dem - $ht;

            $totHT += $ht;
            $totDem += $dem;
            $totLech += $lech;

            $lechFormatted = $lech > 0 ? "+{$lech}" : ($lech < 0 ? "−" . abs($lech) : "0");

            $rows[] = [
                'cells' => [
                    ['v' => $it->Ma_phieu_kiem_ke, 'align' => 'left'],
                    ['v' => $it->Thoi_diem_duyet ? Carbon::parse($it->Thoi_diem_duyet)->format('d/m/Y') : '', 'align' => 'left'],
                    ['v' => $it->Ten_san_pham, 'align' => 'left'],
                    ['v' => $it->Ma_lo_ncc ?? '—', 'align' => 'left'],
                    ['v' => number_format($ht, 0, ',', '.'), 'align' => 'right'],
                    ['v' => number_format($dem, 0, ',', '.'), 'align' => 'right'],
                    ['v' => $lechFormatted, 'align' => 'right', 'color' => $lech > 0 ? '#1E5B36' : '#B42318'],
                    ['v' => $it->Ly_do_chenh_lech ?? '—', 'align' => 'left'],
                ],
            ];
        }

        $totLechFormatted = $totLech > 0 ? "+{$totLech}" : ($totLech < 0 ? "−" . abs($totLech) : "0");

        $foot = [
            ['v' => 'Tổng cộng', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => '', 'align' => 'left'],
            ['v' => number_format($totHT, 0, ',', '.'), 'align' => 'right'],
            ['v' => number_format($totDem, 0, ',', '.'), 'align' => 'right'],
            ['v' => $totLechFormatted, 'align' => 'right', 'color' => $totLech > 0 ? '#1E5B36' : ($totLech < 0 ? '#B42318' : null)],
            ['v' => '', 'align' => 'left'],
        ];

        return [
            'name' => 'Chênh lệch kiểm kê',
            'desc' => 'Kết quả các phiếu kiểm kê đã duyệt, chỉ liệt kê dòng có chênh lệch.',
            'note' => 'Chênh lệch = thực đếm − tồn hệ thống. Số âm là thiếu, số dương là thừa.',
            'searchLabel' => 'Sản phẩm',
            'searchPh' => 'Mã, tên sản phẩm',
            'cols' => $cols,
            'rows' => $rows,
            'foot' => $foot,
        ];
    }
}

