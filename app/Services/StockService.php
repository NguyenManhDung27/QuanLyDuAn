<?php

namespace App\Services;

use App\Models\ChiTietPhieuXuat;
use App\Models\ChiTietKiemKe;
use App\Models\DonDatHang;
use App\Models\DonHangBan;
use App\Models\Lo;
use App\Models\NguoiDung;
use App\Models\PhieuKiemKe;
use App\Models\PhieuNhapKho;
use App\Models\PhieuXuatKho;
use App\Models\SanPham;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * F2. Tồn thực (kho K, sản phẩm P)
     */
    public static function getTonThuc(string $maKho, string $maSanPham): int
    {
        return (int) DB::table('Ton_kho as tk')
            ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo')
            ->where('tk.Ma_kho', $maKho)
            ->where('l.Ma_san_pham', $maSanPham)
            ->sum('tk.Ton_thuc');
    }

    /**
     * F3. Đang giữ cho đơn bán (kho K, sản phẩm P)
     */
    public static function getDangGiu(string $maKho, string $maSanPham): int
    {
        return (int) DB::table('Chi_tiet_phieu_xuat as ct')
            ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'ct.Ma_phieu_xuat')
            ->join('Don_hang_ban as db', 'db.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Lo as l', function ($join) {
                $join->on('l.Ma_lo', '=', DB::raw('COALESCE(ct.Ma_lo_thuc_xuat, ct.Ma_lo_phan_bo)'));
            })
            ->where('px.Trang_thai_giao_hang', 'Đang chuẩn bị hàng')
            ->where('db.Ma_kho', $maKho)
            ->where('l.Ma_san_pham', $maSanPham)
            ->sum('ct.So_luong');
    }

    /**
     * F4. Tồn khả dụng = F2 - F3
     */
    public static function getTonKhaDung(string $maKho, string $maSanPham): int
    {
        $tonThuc = self::getTonThuc($maKho, $maSanPham);
        $dangGiu = self::getDangGiu($maKho, $maSanPham);
        return max(0, $tonThuc - $dangGiu);
    }

    /**
     * F5. Số còn lại của các dòng đơn đặt hàng
     * F6. Tồn dự kiến = F2 + tổng F5 của các dòng đơn đặt hàng
     * Trạng thái đơn: 'Chưa gửi NCC', 'Đã gửi NCC', 'Nhận một phần'
     */
    public static function getTonDuKien(string $maKho, string $maSanPham): int
    {
        $tonThuc = self::getTonThuc($maKho, $maSanPham);

        // Lấy các dòng chi tiết đơn đặt hàng active
        $activeOrders = DB::table('Chi_tiet_don_dat_hang as ct')
            ->join('Don_dat_hang as ddh', 'ddh.Ma_don_dat_hang', '=', 'ct.Ma_don_dat_hang')
            ->where('ddh.Ma_kho', $maKho)
            ->where('ct.Ma_san_pham', $maSanPham)
            ->whereIn('ddh.Trang_thai', ['Chưa gửi NCC', 'Đã gửi NCC', 'Nhận một phần'])
            ->select('ct.Ma_don_dat_hang', 'ct.So_luong_dat')
            ->get();

        $tongF5 = 0;
        foreach ($activeOrders as $row) {
            $daNhan = (int) DB::table('Chi_tiet_phieu_nhap as ctn')
                ->join('Phieu_nhap_kho as pn', 'pn.Ma_phieu_nhap', '=', 'ctn.Ma_phieu_nhap')
                ->join('Lo as l', 'l.Ma_lo', '=', 'ctn.Ma_lo')
                ->where('pn.Ma_don_dat_hang', $row->Ma_don_dat_hang)
                ->where('pn.Trang_thai', 'Đã nhập kho')
                ->where('l.Ma_san_pham', $maSanPham)
                ->sum(DB::raw('ctn.So_luong_nhap + ctn.So_luong_hao_hut'));

            $conLai = max(0, (int) $row->So_luong_dat - $daNhan);
            $tongF5 += $conLai;
        }

        return $tonThuc + $tongF5;
    }

    /**
     * Lấy map tồn dự kiến cho nhiều sản phẩm một lúc
     * Trả về mảng: [ Ma_san_pham => Ton_du_kien ]
     */
    public static function getTonDuKienMap(string $maKho, array $maSanPhams = []): array
    {
        $tonThucMap = self::getTonThucMap($maSanPhams);

        $query = DB::table('Chi_tiet_don_dat_hang as ct')
            ->join('Don_dat_hang as ddh', 'ddh.Ma_don_dat_hang', '=', 'ct.Ma_don_dat_hang')
            ->where('ddh.Ma_kho', $maKho)
            ->whereIn('ddh.Trang_thai', ['Chưa gửi NCC', 'Đã gửi NCC', 'Nhận một phần'])
            ->select('ct.Ma_don_dat_hang', 'ct.Ma_san_pham', 'ct.So_luong_dat');

        if (!empty($maSanPhams)) {
            $query->whereIn('ct.Ma_san_pham', $maSanPhams);
        }

        $activeLines = $query->get();

        // Lấy tổng đã nhận theo (Ma_don_dat_hang, Ma_san_pham)
        $daNhanRows = DB::table('Chi_tiet_phieu_nhap as ctn')
            ->join('Phieu_nhap_kho as pn', 'pn.Ma_phieu_nhap', '=', 'ctn.Ma_phieu_nhap')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ctn.Ma_lo')
            ->where('pn.Trang_thai', 'Đã nhập kho')
            ->select('pn.Ma_don_dat_hang', 'l.Ma_san_pham', DB::raw('SUM(ctn.So_luong_nhap + ctn.So_luong_hao_hut) as total'))
            ->groupBy('pn.Ma_don_dat_hang', 'l.Ma_san_pham')
            ->get();

        $daNhanMap = [];
        foreach ($daNhanRows as $dn) {
            $daNhanMap[$dn->Ma_don_dat_hang][$dn->Ma_san_pham] = (int) $dn->total;
        }

        $f5Map = [];
        foreach ($activeLines as $line) {
            $daNhan = $daNhanMap[$line->Ma_don_dat_hang][$line->Ma_san_pham] ?? 0;
            $conLai = max(0, (int) $line->So_luong_dat - $daNhan);
            $f5Map[$line->Ma_san_pham] = ($f5Map[$line->Ma_san_pham] ?? 0) + $conLai;
        }

        $result = [];
        foreach ($maSanPhams as $sp) {
            $thuc = $tonThucMap[$sp][$maKho] ?? 0;
            $f5 = $f5Map[$sp] ?? 0;
            $result[$sp] = $thuc + $f5;
        }

        return $result;
    }

    /**
     * Lấy danh sách lô theo sản phẩm cho một kho, xếp theo FIFO (F12)
     * Trả về mảng: [ Ma_san_pham => [ [thu_tu_xuat, ma_lo, so_lo, ngay_nhap_dau_tien, ngay_san_xuat, han_su_dung, ton_thuc, dang_giu, kha_dung] ] ]
     */
    public static function getChiTietLoMap(string $maKho, array $maSanPhams = []): array
    {
        $query = DB::table('Ton_kho as tk')
            ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo')
            ->where('tk.Ma_kho', $maKho)
            ->where('tk.Ton_thuc', '>', 0)
            ->orderBy('tk.Ngay_nhap_dau_tien', 'asc')
            ->orderBy('tk.Ma_lo', 'asc')
            ->select(
                'l.Ma_san_pham',
                'tk.Ma_lo',
                'l.Ma_lo_ncc',
                'tk.Ngay_nhap_dau_tien',
                'l.Ngay_san_xuat',
                'l.Han_su_dung',
                'tk.Ton_thuc'
            );

        if (!empty($maSanPhams)) {
            $query->whereIn('l.Ma_san_pham', $maSanPhams);
        }

        $lots = $query->get();
        if ($lots->isEmpty()) {
            return [];
        }

        $maLos = $lots->pluck('Ma_lo')->unique()->all();

        $dangGiuRows = DB::table('Chi_tiet_phieu_xuat as ct')
            ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'ct.Ma_phieu_xuat')
            ->join('Don_hang_ban as db', 'db.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->where('px.Trang_thai_giao_hang', 'Đang chuẩn bị hàng')
            ->where('db.Ma_kho', $maKho)
            ->whereIn(DB::raw('COALESCE(ct.Ma_lo_thuc_xuat, ct.Ma_lo_phan_bo)'), $maLos)
            ->select(
                DB::raw('COALESCE(ct.Ma_lo_thuc_xuat, ct.Ma_lo_phan_bo) as Ma_lo'),
                DB::raw('SUM(ct.So_luong) as total')
            )
            ->groupBy(DB::raw('COALESCE(ct.Ma_lo_thuc_xuat, ct.Ma_lo_phan_bo)'))
            ->get();

        $dangGiuMap = [];
        foreach ($dangGiuRows as $dg) {
            $dangGiuMap[$dg->Ma_lo] = (int) $dg->total;
        }

        $map = [];
        $counters = [];
        foreach ($lots as $lot) {
            $sp = $lot->Ma_san_pham;
            $counters[$sp] = ($counters[$sp] ?? 0) + 1;
            $giu = $dangGiuMap[$lot->Ma_lo] ?? 0;
            $khaDung = max(0, (int) $lot->Ton_thuc - $giu);

            $map[$sp][] = [
                'thu_tu_xuat' => $counters[$sp],
                'ma_lo' => $lot->Ma_lo,
                'so_lo' => $lot->Ma_lo_ncc ?: $lot->Ma_lo,
                'ngay_nhap_dau_tien' => $lot->Ngay_nhap_dau_tien,
                'ngay_san_xuat' => $lot->Ngay_san_xuat,
                'han_su_dung' => $lot->Han_su_dung,
                'ton_thuc' => (int) $lot->Ton_thuc,
                'dang_giu' => $giu,
                'kha_dung' => $khaDung,
            ];
        }

        return $map;
    }

    /**
     * Lấy map tồn thực theo kho và sản phẩm cho nhiều sản phẩm một lúc (tối ưu query)
     * Trả về mảng: [ Ma_san_pham => [ Ma_kho => Ton_thuc ] ]
     */
    public static function getTonThucMap(array $maSanPhams = []): array
    {
        $query = DB::table('Ton_kho as tk')
            ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo')
            ->select('tk.Ma_kho', 'l.Ma_san_pham', DB::raw('SUM(tk.Ton_thuc) as total'));

        if (!empty($maSanPhams)) {
            $query->whereIn('l.Ma_san_pham', $maSanPhams);
        }

        $rows = $query->groupBy('tk.Ma_kho', 'l.Ma_san_pham')->get();
        $map = [];
        foreach ($rows as $r) {
            $map[$r->Ma_san_pham][$r->Ma_kho] = (int) $r->total;
        }
        return $map;
    }

    /**
     * Lấy map tồn khả dụng theo kho và sản phẩm cho nhiều sản phẩm một lúc
     * Trả về mảng: [ Ma_san_pham => [ Ma_kho => Ton_kha_dung ] ]
     */
    public static function getTonKhaDungMap(array $maSanPhams = []): array
    {
        // 1. Tồn thực
        $tonThucMap = self::getTonThucMap($maSanPhams);

        // 2. Đang giữ
        $queryGiu = DB::table('Chi_tiet_phieu_xuat as ct')
            ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'ct.Ma_phieu_xuat')
            ->join('Don_hang_ban as db', 'db.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Lo as l', function ($join) {
                $join->on('l.Ma_lo', '=', DB::raw('COALESCE(ct.Ma_lo_thuc_xuat, ct.Ma_lo_phan_bo)'));
            })
            ->where('px.Trang_thai_giao_hang', 'Đang chuẩn bị hàng')
            ->select('db.Ma_kho', 'l.Ma_san_pham', DB::raw('SUM(ct.So_luong) as total'));

        if (!empty($maSanPhams)) {
            $queryGiu->whereIn('l.Ma_san_pham', $maSanPhams);
        }

        $rowsGiu = $queryGiu->groupBy('db.Ma_kho', 'l.Ma_san_pham')->get();
        $giuMap = [];
        foreach ($rowsGiu as $r) {
            $giuMap[$r->Ma_san_pham][$r->Ma_kho] = (int) $r->total;
        }

        // Tính tồn khả dụng = max(0, tồn thực - đang giữ)
        $result = [];
        $khos = ['HN', 'HCM'];
        foreach ($maSanPhams as $sp) {
            foreach ($khos as $kho) {
                $thuc = $tonThucMap[$sp][$kho] ?? 0;
                $giu = $giuMap[$sp][$kho] ?? 0;
                $result[$sp][$kho] = max(0, $thuc - $giu);
            }
        }

        return $result;
    }

    /**
     * Bán 90 ngày qua của sản phẩm tại kho K:
     * Tổng số lượng phiếu xuất giao thành công trong 90 ngày trừ đi số nhập lại từ khách trả hàng
     */
    public static function getSoLuongBan90NgayMap(string $maKho, array $maSanPhams = []): array
    {
        $cutoff = now()->subDays(90)->startOfDay();

        $queryBan = DB::table('Chi_tiet_phieu_xuat as ct')
            ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'ct.Ma_phieu_xuat')
            ->join('Don_hang_ban as db', 'db.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->join('Lo as l', function ($join) {
                $join->on('l.Ma_lo', '=', DB::raw('COALESCE(ct.Ma_lo_thuc_xuat, ct.Ma_lo_phan_bo)'));
            })
            ->where('px.Trang_thai_giao_hang', 'Giao thành công')
            ->where('px.Thoi_diem_ket_qua_giao', '>=', $cutoff)
            ->where('db.Ma_kho', $maKho)
            ->select('l.Ma_san_pham', DB::raw('SUM(ct.So_luong) as total'));

        if (!empty($maSanPhams)) {
            $queryBan->whereIn('l.Ma_san_pham', $maSanPhams);
        }
        $banRows = $queryBan->groupBy('l.Ma_san_pham')->pluck('total', 'Ma_san_pham')->all();

        $queryTra = DB::table('Chi_tiet_phieu_nhap as ctn')
            ->join('Phieu_nhap_kho as pn', 'pn.Ma_phieu_nhap', '=', 'ctn.Ma_phieu_nhap')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ctn.Ma_lo')
            ->where('pn.Loai_nhap', 'Khách trả hàng')
            ->where('pn.Trang_thai', 'Đã nhập kho')
            ->where('pn.Ma_kho', $maKho)
            ->where('pn.Thoi_diem_xac_nhan', '>=', $cutoff)
            ->select('l.Ma_san_pham', DB::raw('SUM(ctn.So_luong_nhap) as total'));

        if (!empty($maSanPhams)) {
            $queryTra->whereIn('l.Ma_san_pham', $maSanPhams);
        }
        $traRows = $queryTra->groupBy('l.Ma_san_pham')->pluck('total', 'Ma_san_pham')->all();

        $result = [];
        foreach ($maSanPhams as $sp) {
            $b = (int)($banRows[$sp] ?? 0);
            $t = (int)($traRows[$sp] ?? 0);
            $result[$sp] = max(0, $b - $t);
        }
        return $result;
    }

    /**
     * Kiểm tra xem trong danh sách mã lô có lô nào đang thuộc phiếu kiểm kê chưa hoàn tất tại kho hay không.
     * Trả về mảng thông tin ['Ma_lo' => ..., 'Ma_phieu_kiem_ke' => ...] hoặc null nếu không có.
     */
    public static function kiemTraLoDangKiemKe(string $maKho, array $maLos): ?array
    {
        if (empty($maLos)) {
            return null;
        }

        $row = DB::table('Chi_tiet_kiem_ke as ct')
            ->join('Phieu_kiem_ke as pkk', 'pkk.Ma_phieu_kiem_ke', '=', 'ct.Ma_phieu_kiem_ke')
            ->where('pkk.Ma_kho', $maKho)
            ->whereIn('pkk.Trang_thai', ['Đang kiểm', 'Chờ duyệt'])
            ->whereIn('ct.Ma_lo', $maLos)
            ->select('ct.Ma_lo', 'pkk.Ma_phieu_kiem_ke')
            ->first();

        if ($row) {
            return [
                'Ma_lo' => $row->Ma_lo,
                'Ma_phieu_kiem_ke' => $row->Ma_phieu_kiem_ke,
            ];
        }

        return null;
    }

    /**
     * Xác nhận nhập kho:
     * - Kiểm tra chặn nếu có lô đang kiểm kê
     * - Cộng Ton_kho.Ton_thuc
     * - Cập nhật Giá vốn bình quân liên hoàn F7
     * - Cập nhật trạng thái chứng từ gốc (Đơn đặt hàng hoặc Phiếu xuất kho)
     * - Cập nhật trạng thái phiếu nhập sang 'Đã nhập kho'
     */
    /**
     * F10. Nhãn "Thiếu tồn" cho một phiếu xuất
     * Trả về true nếu có ít nhất 1 dòng trong phiếu mà tồn thực lô < số lượng cần xuất
     */
    public static function isThieuTon(string $maPhieuXuat, string $maKho): bool
    {
        return DB::table('Chi_tiet_phieu_xuat as ct')
            ->join('Ton_kho as tk', function ($join) use ($maKho) {
                $join->on('tk.Ma_lo', '=', DB::raw('COALESCE(ct.Ma_lo_thuc_xuat, ct.Ma_lo_phan_bo)'))
                     ->where('tk.Ma_kho', $maKho);
            })
            ->where('ct.Ma_phieu_xuat', $maPhieuXuat)
            ->whereRaw('tk.Ton_thuc < ct.So_luong')
            ->exists();
    }

    public static function nhapKho(PhieuNhapKho $phieuNhap): void
    {
        if ($phieuNhap->Trang_thai === 'Đã nhập kho') {
            throw new \Exception("Phiếu nhập kho {$phieuNhap->Ma_phieu_nhap} đã được xác nhận trước đó.");
        }

        $phieuNhap->loadMissing(['chiTiets.lo.sanPham', 'donDatHang', 'phieuXuatKho']);

        $maLos = $phieuNhap->chiTiets->pluck('Ma_lo')->unique()->all();
        $blocked = self::kiemTraLoDangKiemKe($phieuNhap->Ma_kho, $maLos);
        if ($blocked) {
            throw new \Exception("Lô {$blocked['Ma_lo']} đang thuộc phiếu kiểm kê {$blocked['Ma_phieu_kiem_ke']} chưa hoàn tất. Xác nhận lại sau khi kiểm kê xong.");
        }

        DB::transaction(function () use ($phieuNhap) {
            $thoiDiemXacNhan = now();

            foreach ($phieuNhap->chiTiets as $ct) {
                $soLuongNhap = (int) $ct->So_luong_nhap;
                if ($soLuongNhap <= 0) {
                    continue;
                }

                $lo = $ct->lo;
                $sp = $lo->sanPham;
                $maSp = $sp->Ma_san_pham;

                // 1. Tồn trước của sản phẩm trên CẢ HAI KHO (trước khi cộng dòng này)
                $tonTruoc = (int) DB::table('Ton_kho as tk')
                    ->join('Lo as l', 'l.Ma_lo', '=', 'tk.Ma_lo')
                    ->where('l.Ma_san_pham', $maSp)
                    ->sum('tk.Ton_thuc');

                // 2. Xác định Đơn giá nhập theo Loại nhập
                $donGiaNhap = 0;
                if ($phieuNhap->Loai_nhap === 'Nhập từ NCC') {
                    $donGiaNhap = (float) (DB::table('Chi_tiet_don_dat_hang')
                        ->where('Ma_don_dat_hang', $phieuNhap->Ma_don_dat_hang)
                        ->where('Ma_san_pham', $maSp)
                        ->value('Don_gia_mua') ?? 0);
                } elseif (in_array($phieuNhap->Loai_nhap, ['Khách trả hàng', 'Hoàn giao thất bại'])) {
                    $donGiaNhap = (float) (DB::table('Chi_tiet_phieu_xuat')
                        ->where('Ma_phieu_xuat', $phieuNhap->Ma_phieu_xuat)
                        ->where(function ($q) use ($ct) {
                            $q->where('Ma_lo_thuc_xuat', $ct->Ma_lo)
                              ->orWhere('Ma_lo_phan_bo', $ct->Ma_lo);
                        })
                        ->value('Gia_von_xuat') ?? 0);

                    if ($donGiaNhap <= 0) {
                        $donGiaNhap = (float) (DB::table('Chi_tiet_phieu_xuat as px_ct')
                            ->join('Lo as px_l', 'px_l.Ma_lo', '=', DB::raw('COALESCE(px_ct.Ma_lo_thuc_xuat, px_ct.Ma_lo_phan_bo)'))
                            ->where('px_ct.Ma_phieu_xuat', $phieuNhap->Ma_phieu_xuat)
                            ->where('px_l.Ma_san_pham', $maSp)
                            ->value('px_ct.Gia_von_xuat') ?? 0);
                    }
                }

                // 3. Cộng Ton_kho.Ton_thuc tại kho của phiếu nhập
                $tonKho = DB::table('Ton_kho')
                    ->where('Ma_kho', $phieuNhap->Ma_kho)
                    ->where('Ma_lo', $ct->Ma_lo)
                    ->first();

                if ($tonKho) {
                    DB::table('Ton_kho')
                        ->where('Ma_kho', $phieuNhap->Ma_kho)
                        ->where('Ma_lo', $ct->Ma_lo)
                        ->update([
                            'Ton_thuc' => $tonKho->Ton_thuc + $soLuongNhap,
                            'Ngay_nhap_dau_tien' => $tonKho->Ngay_nhap_dau_tien ?: $thoiDiemXacNhan,
                        ]);
                } else {
                    DB::table('Ton_kho')->insert([
                        'Ma_kho' => $phieuNhap->Ma_kho,
                        'Ma_lo' => $ct->Ma_lo,
                        'Ton_thuc' => $soLuongNhap,
                        'Ngay_nhap_dau_tien' => $thoiDiemXacNhan,
                    ]);
                }

                // 4. Cập nhật Giá vốn bình quân liên hoàn F7
                // Gia_von_moi = (Ton_truoc × Gia_von_binh_quan + So_luong_nhap × Don_gia_nhap) / (Ton_truoc + So_luong_nhap)
                // Số lượng hao hụt KHÔNG tham gia công thức vì hàng không vào kho
                $giaVonCu = (float) ($sp->Gia_von_binh_quan ?? 0);
                if ($tonTruoc + $soLuongNhap > 0) {
                    $giaVonMoi = ($tonTruoc * $giaVonCu + $soLuongNhap * $donGiaNhap) / ($tonTruoc + $soLuongNhap);
                    $sp->update(['Gia_von_binh_quan' => round($giaVonMoi)]);
                }
            }

            // 5. Cập nhật trạng thái chứng từ liên quan
            if ($phieuNhap->Loai_nhap === 'Nhập từ NCC' && $phieuNhap->Ma_don_dat_hang) {
                $don = DonDatHang::where('Ma_don_dat_hang', $phieuNhap->Ma_don_dat_hang)->first();
                if ($don) {
                    $chiTietDons = $don->chiTiets;
                    $allCompleted = true;
                    $hasAnyReceived = false;

                    foreach ($chiTietDons as $dLine) {
                        $spLine = $dLine->Ma_san_pham;
                        $daNhanLine = (int) DB::table('Chi_tiet_phieu_nhap as ctn')
                            ->join('Phieu_nhap_kho as pn', 'pn.Ma_phieu_nhap', '=', 'ctn.Ma_phieu_nhap')
                            ->join('Lo as l', 'l.Ma_lo', '=', 'ctn.Ma_lo')
                            ->where('pn.Ma_don_dat_hang', $don->Ma_don_dat_hang)
                            ->where(function ($q) use ($phieuNhap) {
                                $q->where('pn.Trang_thai', 'Đã nhập kho')
                                  ->orWhere('pn.Ma_phieu_nhap', $phieuNhap->Ma_phieu_nhap);
                            })
                            ->where('l.Ma_san_pham', $spLine)
                            ->sum(DB::raw('ctn.So_luong_nhap + ctn.So_luong_hao_hut'));

                        if ($daNhanLine > 0) {
                            $hasAnyReceived = true;
                        }
                        if ($daNhanLine < (int) $dLine->So_luong_dat) {
                            $allCompleted = false;
                        }
                    }

                    if ($allCompleted) {
                        $don->update(['Trang_thai' => 'Đã nhận đủ']);
                    } elseif ($hasAnyReceived) {
                        $don->update(['Trang_thai' => 'Nhận một phần']);
                    }
                }
            } elseif (in_array($phieuNhap->Loai_nhap, ['Khách trả hàng', 'Hoàn giao thất bại']) && $phieuNhap->Ma_phieu_xuat) {
                $px = PhieuXuatKho::where('Ma_phieu_xuat', $phieuNhap->Ma_phieu_xuat)->first();
                if ($px) {
                    $px->update([
                        'Trang_thai_hoan_hang' => 'Đã nhập lại kho',
                        'Thoi_diem_xu_ly_hoan' => $thoiDiemXacNhan,
                    ]);
                }
            }

            // 6. Cập nhật trạng thái phiếu nhập
            $phieuNhap->update([
                'Trang_thai' => 'Đã nhập kho',
                'Thoi_diem_xac_nhan' => $thoiDiemXacNhan,
            ]);

            ActivityLogger::log('Xác nhận nhập kho', 'Phiếu nhập kho', "Xác nhận phiếu nhập kho {$phieuNhap->Ma_phieu_nhap}", $phieuNhap);
        });
    }

    /**
     * NVK-08: Xác nhận xuất kho
     */
    public static function xuatKho(PhieuXuatKho $phieuXuat, NguoiDung $user, string $dvvc, string $vanDon): void
    {
        if ($phieuXuat->Trang_thai_giao_hang !== 'Đang chuẩn bị hàng') {
            throw new \Exception('Chỉ xuất kho cho phiếu ở trạng thái "Đang chuẩn bị hàng".');
        }

        $donBan = $phieuXuat->donHangBan;
        $maKho  = $donBan->Ma_kho;

        // 1. Kiểm tra thiếu tồn (F10)
        if (self::isThieuTon($phieuXuat->Ma_phieu_xuat, $maKho)) {
            throw new \Exception('Phiếu xuất có lô bị thiếu tồn thực tế. Vui lòng đổi lô trước khi xuất kho.');
        }

        // 2. Kiểm tra lô đang kiểm kê
        $lots = $phieuXuat->chiTiets->map(fn ($ct) => $ct->Ma_lo_thuc_xuat ?: $ct->Ma_lo_phan_bo)->unique()->values()->all();
        $biKhoa = self::kiemTraLoDangKiemKe($maKho, $lots);
        if ($biKhoa) {
            $ds = implode(', ', $biKhoa);
            throw new \Exception("Không thể xuất kho vì các lô [{$ds}] đang thuộc phiếu kiểm kê chưa hoàn tất.");
        }

        DB::transaction(function () use ($phieuXuat, $donBan, $user, $maKho, $dvvc, $vanDon) {
            $thoiDiemXuat = now();

            foreach ($phieuXuat->chiTiets as $ct) {
                $maLoXuat = $ct->Ma_lo_thuc_xuat ?: $ct->Ma_lo_phan_bo;
                $lo = Lo::with('sanPham')->find($maLoXuat);

                // Trừ tồn thực Ton_kho.Ton_thuc
                DB::table('Ton_kho')
                    ->where('Ma_kho', $maKho)
                    ->where('Ma_lo', $maLoXuat)
                    ->decrement('Ton_thuc', (int) $ct->So_luong);

                // Chụp giá vốn xuất từ San_pham.Gia_von_binh_quan
                $giaVonXuat = $lo?->sanPham?->Gia_von_binh_quan ?? 0;
                $ct->update(['Gia_von_xuat' => $giaVonXuat]);
            }

            // Cập nhật phiếu xuất
            $phieuXuat->update([
                'Trang_thai_giao_hang' => 'Đang giao',
                'Don_vi_van_chuyen'    => $dvvc,
                'Ma_van_don'           => $vanDon,
                'Ma_nguoi_xuat'        => $user->Ma_nguoi_dung,
                'Thoi_diem_xuat_kho'   => $thoiDiemXuat,
            ]);

            // Cập nhật đơn bán
            $donBan->update(['Trang_thai' => 'Đang giao']);

            ActivityLogger::log('Xuất kho', 'Phiếu xuất kho', "Xuất kho phiếu {$phieuXuat->Ma_phieu_xuat} giao vận đơn {$vanDon} qua {$dvvc}", $phieuXuat);
        });
    }

    /**
     * NVK-08: Đổi lô
     */
    public static function doiLo(
        PhieuXuatKho $phieuXuat,
        NguoiDung $user,
        string $maLoPhanBo,
        string $maLoThucXuat,
        int $soLuongDoi,
        string $lyDo,
        ?int $soLuongHong,
        array $replacements
    ): void {
        if ($phieuXuat->Trang_thai_giao_hang !== 'Đang chuẩn bị hàng') {
            throw new \Exception('Chỉ đổi lô khi phiếu ở trạng thái "Đang chuẩn bị hàng".');
        }

        $donBan = $phieuXuat->donHangBan;
        $maKho  = $donBan->Ma_kho;

        $ct = ChiTietPhieuXuat::where('Ma_phieu_xuat', $phieuXuat->Ma_phieu_xuat)
            ->where('Ma_lo_phan_bo', $maLoPhanBo)
            ->where('Ma_lo_thuc_xuat', $maLoThucXuat)
            ->firstOrFail();

        if ($soLuongDoi <= 0 || $soLuongDoi > (int) $ct->So_luong) {
            throw new \Exception("Số lượng đổi ({$soLuongDoi}) không hợp lệ (tối đa {$ct->So_luong}).");
        }

        $tongLay = 0;
        foreach ($replacements as $rep) {
            $tongLay += (int) ($rep['so_luong'] ?? 0);
        }

        if ($tongLay !== $soLuongDoi) {
            throw new \Exception("Tổng số lượng lấy từ các lô thay thế ({$tongLay}) phải bằng số lượng cần đổi ({$soLuongDoi}).");
        }

        // Kiểm tra kiểm kê lô gốc
        $biKhoaGoc = self::kiemTraLoDangKiemKe($maKho, [$maLoThucXuat]);
        if ($biKhoaGoc) {
            throw new \Exception("Lô gốc {$maLoThucXuat} đang thuộc phiếu kiểm kê chưa hoàn tất, không thể đổi.");
        }

        // Kiểm tra kiểm kê các lô thay thế
        $repLots = array_column($replacements, 'ma_lo');
        $biKhoaRep = self::kiemTraLoDangKiemKe($maKho, $repLots);
        if ($biKhoaRep) {
            $ds = implode(', ', $biKhoaRep);
            throw new \Exception("Lô thay thế [{$ds}] đang thuộc phiếu kiểm kê chưa hoàn tất.");
        }

        DB::transaction(function () use ($phieuXuat, $ct, $user, $maKho, $maLoPhanBo, $maLoThucXuat, $soLuongDoi, $lyDo, $soLuongHong, $replacements) {
            $thoiDiemDoi = now();
            $loPhanBo = Lo::with('sanPham')->find($maLoPhanBo);
            $giaVonHong = null;

            // Nếu lý do hỏng hoặc vỡ: trừ tồn thực lô hỏng ngay lập tức
            if ($lyDo === 'Hỏng hoặc vỡ') {
                $slHong = $soLuongHong ?: $soLuongDoi;
                $giaVonHong = $loPhanBo?->sanPham?->Gia_von_binh_quan ?? 0;

                DB::table('Ton_kho')
                    ->where('Ma_kho', $maKho)
                    ->where('Ma_lo', $maLoThucXuat)
                    ->decrement('Ton_thuc', $slHong);
            }

            // Giảm hoặc xóa dòng chi tiết cũ
            if ((int) $ct->So_luong === $soLuongDoi) {
                $ct->delete();
            } else {
                $ct->decrement('So_luong', $soLuongDoi);
            }

            // Ghi nhận các dòng lô mới
            foreach ($replacements as $rep) {
                $repLot = $rep['ma_lo'];
                $repSl  = (int) $rep['so_luong'];

                if ($repSl <= 0) {
                    continue;
                }

                $existing = ChiTietPhieuXuat::where('Ma_phieu_xuat', $phieuXuat->Ma_phieu_xuat)
                    ->where('Ma_lo_phan_bo', $maLoPhanBo)
                    ->where('Ma_lo_thuc_xuat', $repLot)
                    ->first();

                if ($existing) {
                    $existing->increment('So_luong', $repSl);
                    $existing->update([
                        'Ly_do_doi_lo'       => $lyDo,
                        'So_luong_hong'      => $lyDo === 'Hỏng hoặc vỡ' ? ($soLuongHong ?: $soLuongDoi) : null,
                        'Gia_von_hang_hong'  => $giaVonHong,
                        'Ma_nguoi_doi_lo'    => $user->Ma_nguoi_dung,
                        'Thoi_diem_doi_lo'   => $thoiDiemDoi,
                    ]);
                } else {
                    ChiTietPhieuXuat::create([
                        'Ma_phieu_xuat'      => $phieuXuat->Ma_phieu_xuat,
                        'Ma_lo_phan_bo'      => $maLoPhanBo,
                        'Ma_lo_thuc_xuat'    => $repLot,
                        'So_luong'           => $repSl,
                        'Ly_do_doi_lo'       => $lyDo,
                        'So_luong_hong'      => $lyDo === 'Hỏng hoặc vỡ' ? ($soLuongHong ?: $soLuongDoi) : null,
                        'Gia_von_hang_hong'  => $giaVonHong,
                        'Ma_nguoi_doi_lo'    => $user->Ma_nguoi_dung,
                        'Thoi_diem_doi_lo'   => $thoiDiemDoi,
                    ]);
                }
            }

            ActivityLogger::log('Đổi lô', 'Phiếu xuất kho', "Đổi lô {$maLoThucXuat} ({$soLuongDoi} chai) trên phiếu {$phieuXuat->Ma_phieu_xuat} vì lý do: {$lyDo}", $phieuXuat);
        });
    }

    /**
     * NVK-08: Sửa thông tin vận chuyển khi Đang giao
     */
    public static function capNhatVanChuyen(PhieuXuatKho $phieuXuat, NguoiDung $user, string $dvvc, string $vanDon): void
    {
        if ($phieuXuat->Trang_thai_giao_hang !== 'Đang giao') {
            throw new \Exception('Chỉ sửa thông tin vận chuyển khi phiếu đang ở trạng thái "Đang giao".');
        }

        $phieuXuat->update([
            'Don_vi_van_chuyen' => $dvvc,
            'Ma_van_don'        => $vanDon,
        ]);

        ActivityLogger::log('Cập nhật vận chuyển', 'Phiếu xuất kho', "Sửa thông tin vận chuyển phiếu {$phieuXuat->Ma_phieu_xuat}: {$dvvc} - {$vanDon}", $phieuXuat);
    }

    /**
     * NVK-08: Cập nhật kết quả giao hàng (Giao thành công / Giao hàng thất bại)
     */
    public static function capNhatKetQuaGiao(PhieuXuatKho $phieuXuat, NguoiDung $user, string $ketQua, ?string $lyDoThatBai = null): void
    {
        if ($phieuXuat->Trang_thai_giao_hang !== 'Đang giao') {
            throw new \Exception('Chỉ cập nhật kết quả giao khi phiếu đang ở trạng thái "Đang giao".');
        }

        $donBan = $phieuXuat->donHangBan;

        DB::transaction(function () use ($phieuXuat, $donBan, $user, $ketQua, $lyDoThatBai) {
            $thoiDiem = now();

            if ($ketQua === 'Giao thành công') {
                $phieuXuat->update([
                    'Trang_thai_giao_hang'   => 'Giao thành công',
                    'Thoi_diem_ket_qua_giao' => $thoiDiem,
                ]);

                $donBan->update(['Trang_thai' => 'Hoàn thành']);

                ActivityLogger::log('Giao thành công', 'Phiếu xuất kho', "Ghi nhận giao thành công cho phiếu {$phieuXuat->Ma_phieu_xuat}", $phieuXuat);

            } elseif ($ketQua === 'Giao hàng thất bại') {
                if (empty($lyDoThatBai)) {
                    throw new \Exception('Bắt buộc nhập lý do giao hàng thất bại.');
                }

                $phieuXuat->update([
                    'Trang_thai_giao_hang'   => 'Giao hàng thất bại',
                    'Thoi_diem_ket_qua_giao' => $thoiDiem,
                    'Ly_do_giao_that_bai'    => $lyDoThatBai,
                    'Trang_thai_hoan_hang'   => 'Chờ xử lý',
                ]);

                $donBan->update(['Trang_thai' => 'Giao thất bại']);

                ActivityLogger::log('Giao thất bại', 'Phiếu xuất kho', "Ghi nhận giao thất bại cho phiếu {$phieuXuat->Ma_phieu_xuat}: {$lyDoThatBai}", $phieuXuat);
            } else {
                throw new \Exception("Kết quả giao hàng [{$ketQua}] không hợp lệ.");
            }
        });
    }

    /**
     * F11. Nhãn "Ưu tiên kiểm" trên lô tại một kho
     * Trả về mảng: [ Ma_lo => Ly_do_doi_lo ]
     */
    public static function getLoUuTienKiemMap(string $maKho, ?string $thoiDiemTao = null): array
    {
        $mocQuery = DB::table('Phieu_kiem_ke')
            ->where('Ma_kho', $maKho)
            ->where('Trang_thai', 'Hoàn tất')
            ->whereNotNull('Thoi_diem_duyet');

        if ($thoiDiemTao) {
            $mocQuery->where('Thoi_diem_duyet', '<', $thoiDiemTao);
        }

        $mocThoiGian = $mocQuery->max('Thoi_diem_duyet') ?? '1900-01-01 00:00:00';
        $limitThoiGian = $thoiDiemTao ?? now();

        $rows = DB::table('Chi_tiet_phieu_xuat as ct')
            ->join('Phieu_xuat_kho as px', 'px.Ma_phieu_xuat', '=', 'ct.Ma_phieu_xuat')
            ->join('Don_hang_ban as db', 'db.Ma_don_hang_ban', '=', 'px.Ma_don_hang_ban')
            ->where('db.Ma_kho', $maKho)
            ->whereIn('ct.Ly_do_doi_lo', ['Không tìm thấy lô gốc', 'Hỏng hoặc vỡ'])
            ->where('ct.Thoi_diem_doi_lo', '>', $mocThoiGian)
            ->where('ct.Thoi_diem_doi_lo', '<=', $limitThoiGian)
            ->select('ct.Ma_lo_phan_bo', 'ct.Ly_do_doi_lo')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $map[$r->Ma_lo_phan_bo] = $r->Ly_do_doi_lo;
        }

        return $map;
    }

    /**
     * Lấy map các lô đang thuộc phiếu kiểm kê chưa hoàn tất tại kho
     * Trả về: [ Ma_lo => Ma_phieu_kiem_ke ]
     */
    public static function getLoDangKiemKeMap(string $maKho): array
    {
        $rows = DB::table('Chi_tiet_kiem_ke as ct')
            ->join('Phieu_kiem_ke as pkk', 'pkk.Ma_phieu_kiem_ke', '=', 'ct.Ma_phieu_kiem_ke')
            ->where('pkk.Ma_kho', $maKho)
            ->whereIn('pkk.Trang_thai', ['Đang kiểm', 'Chờ duyệt'])
            ->select('ct.Ma_lo', 'pkk.Ma_phieu_kiem_ke')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $map[$r->Ma_lo] = $r->Ma_phieu_kiem_ke;
        }

        return $map;
    }

    /**
     * QLK-07: Tạo phiếu kiểm kê kho
     */
    public static function taoPhieuKiemKe(NguoiDung $nguoiTao, string $maKho, array $maLos, ?string $maNguoiKiem = null, ?string $ghiChu = null): PhieuKiemKe
    {
        if (empty($maLos)) {
            throw new \Exception('Vui lòng chọn ít nhất một lô để kiểm kê.');
        }

        return DB::transaction(function () use ($nguoiTao, $maKho, $maLos, $maNguoiKiem, $ghiChu) {
            // Kiểm tra khóa theo lô
            $locked = self::kiemTraLoDangKiemKe($maKho, $maLos);
            if ($locked) {
                throw new \Exception("Lô {$locked['Ma_lo']} đang thuộc phiếu kiểm kê {$locked['Ma_phieu_kiem_ke']} chưa hoàn tất. Không thể tạo phiếu kiểm kê mới cho lô này.");
            }

            $maPhieu = CodeGenerator::generateMaPhieuKiemKe();
            $thoiDiemTao = now();

            $pkk = PhieuKiemKe::create([
                'Ma_phieu_kiem_ke' => $maPhieu,
                'Ma_kho' => $maKho,
                'Ma_nguoi_tao' => $nguoiTao->Ma_nguoi_dung,
                'Ma_nguoi_kiem' => $maNguoiKiem ?: null,
                'Thoi_diem_tao' => $thoiDiemTao,
                'Trang_thai' => 'Đang kiểm',
                'Ghi_chu' => $ghiChu ?: null,
            ]);

            // Chụp số lượng tồn hệ thống tại thời điểm tạo
            $tonKhos = DB::table('Ton_kho')
                ->where('Ma_kho', $maKho)
                ->whereIn('Ma_lo', $maLos)
                ->pluck('Ton_thuc', 'Ma_lo');

            foreach ($maLos as $maLo) {
                $slHeThong = (int) ($tonKhos[$maLo] ?? 0);
                DB::table('Chi_tiet_kiem_ke')->insert([
                    'Ma_phieu_kiem_ke' => $maPhieu,
                    'Ma_lo' => $maLo,
                    'So_luong_he_thong' => $slHeThong,
                    'So_luong_thuc_te' => null,
                    'Ly_do_chenh_lech' => null,
                    'Gia_von_dieu_chinh' => null,
                ]);
            }

            ActivityLogger::log('Tạo phiếu kiểm kê', 'Phiếu kiểm kê', "Tạo phiếu kiểm kê {$maPhieu} với " . count($maLos) . " lô", $pkk);

            return $pkk;
        });
    }

    /**
     * NVK-10: Lưu kết quả đếm kiểm kê (Lưu tạm hoặc Hoàn tất đếm)
     */
    public static function luuDemKiemKe(PhieuKiemKe $pkk, NguoiDung $nguoiKiem, array $items, bool $isHoanThanh): array
    {
        if ($pkk->Trang_thai !== 'Đang kiểm') {
            throw new \Exception('Chỉ có thể nhập số đếm khi phiếu ở trạng thái "Đang kiểm".');
        }

        return DB::transaction(function () use ($pkk, $nguoiKiem, $items, $isHoanThanh) {
            // $items có dạng ['Ma_lo' => ['So_luong_thuc_te' => ..., 'Ly_do_chenh_lech' => ...]]
            // Key đã là Ma_lo, không cần keyBy
            $itemsByLo = collect($items); // Giữ nguyên, truy cập bằng $itemsByLo->get($maLo)

            $allDetails = $pkk->chiTiets()->get();
            $soLuongLech = 0;
            $soLuongChuaNhap = 0;

            foreach ($allDetails as $ct) {
                $maLo = $ct->Ma_lo;
                $rowInput = $itemsByLo->get($maLo);

                $val = $rowInput['So_luong_thuc_te'] ?? null;
                $lyDo = trim($rowInput['Ly_do_chenh_lech'] ?? '');

                $slThucTe = ($val !== null && $val !== '') ? (int) $val : null;

                if ($isHoanThanh) {
                    if ($slThucTe === null) {
                        $soLuongChuaNhap++;
                    } elseif ($slThucTe != $ct->So_luong_he_thong) {
                        $soLuongLech++;
                        if (empty($lyDo)) {
                            throw new \Exception("Lô {$maLo} có chênh lệch (" . ($slThucTe - $ct->So_luong_he_thong) . " chai) nhưng chưa nhập lý do chênh lệch.");
                        }
                    }
                }

                // Cập nhật dòng chi tiết
                DB::table('Chi_tiet_kiem_ke')
                    ->where('Ma_phieu_kiem_ke', $pkk->Ma_phieu_kiem_ke)
                    ->where('Ma_lo', $maLo)
                    ->update([
                        'So_luong_thuc_te' => $slThucTe,
                        'Ly_do_chenh_lech' => $lyDo ?: null,
                    ]);
            }

            if ($isHoanThanh) {
                if ($soLuongChuaNhap > 0) {
                    throw new \Exception("Còn {$soLuongChuaNhap} lô chưa nhập số lượng thực tế. Vui lòng đếm đủ các lô.");
                }

                $thoiDiemDemXong = now();

                if ($soLuongLech === 0) {
                    // Không có dòng nào lệch: chuyển thẳng sang 'Hoàn tất' (lô mở khóa ngay)
                    $pkk->update([
                        'Trang_thai' => 'Hoàn tất',
                        'Thoi_diem_dem_xong' => $thoiDiemDemXong,
                        'Ma_nguoi_kiem' => $nguoiKiem->Ma_nguoi_dung,
                    ]);

                    ActivityLogger::log('Hoàn tất đếm kiểm kê', 'Phiếu kiểm kê', "Phiếu kiểm kê {$pkk->Ma_phieu_kiem_ke} khớp 100%, tự động Hoàn tất.", $pkk);

                    return [
                        'status' => 'Hoàn tất',
                        'message' => 'Tất cả lô khớp hoàn toàn. Phiếu kiểm kê đã Hoàn tất thành công!',
                    ];
                } else {
                    // Có ít nhất 1 dòng lệch: chuyển sang 'Chờ duyệt'
                    $pkk->update([
                        'Trang_thai' => 'Chờ duyệt',
                        'Thoi_diem_dem_xong' => $thoiDiemDemXong,
                        'Ma_nguoi_kiem' => $nguoiKiem->Ma_nguoi_dung,
                    ]);

                    ActivityLogger::log('Hoàn thành đếm kiểm kê', 'Phiếu kiểm kê', "Phiếu kiểm kê {$pkk->Ma_phieu_kiem_ke} có {$soLuongLech} lô chênh lệch, chuyển Chờ duyệt.", $pkk);

                    return [
                        'status' => 'Chờ duyệt',
                        'message' => "Đã hoàn thành đếm. Có {$soLuongLech} lô chênh lệch, phiếu đã chuyển sang Chờ duyệt cho Quản lý kho.",
                    ];
                }
            } else {
                // Lưu tạm: vẫn giữ 'Đang kiểm'
                if (!$pkk->Ma_nguoi_kiem) {
                    $pkk->update(['Ma_nguoi_kiem' => $nguoiKiem->Ma_nguoi_dung]);
                }

                ActivityLogger::log('Lưu tạm kiểm kê', 'Phiếu kiểm kê', "Lưu tạm kết quả đếm phiếu {$pkk->Ma_phieu_kiem_ke}", $pkk);

                return [
                    'status' => 'Đang kiểm',
                    'message' => 'Đã lưu tạm kết quả đếm kiểm kê thành công.',
                ];
            }
        });
    }

    /**
     * QLK-08: Quản lý kho Duyệt và điều chỉnh tồn kho
     */
    public static function duyetKiemKe(PhieuKiemKe $pkk, NguoiDung $nguoiDuyet): void
    {
        if ($pkk->Trang_thai !== 'Chờ duyệt') {
            throw new \Exception('Chỉ có thể duyệt phiếu kiểm kê khi phiếu ở trạng thái "Chờ duyệt".');
        }

        $pkk->loadMissing('chiTiets.lo.sanPham');

        DB::transaction(function () use ($pkk, $nguoiDuyet) {
            $thoiDiemDuyet = now();

            foreach ($pkk->chiTiets as $ct) {
                $sp = $ct->lo?->sanPham;
                $giaVon = $sp?->Gia_von_binh_quan ?? 0;

                // 1. Chụp giá vốn điều chỉnh vào chi tiết kiểm kê
                DB::table('Chi_tiet_kiem_ke')
                    ->where('Ma_phieu_kiem_ke', $pkk->Ma_phieu_kiem_ke)
                    ->where('Ma_lo', $ct->Ma_lo)
                    ->update(['Gia_von_dieu_chinh' => $giaVon]);

                // 2. Ghi đè Ton_kho.Ton_thuc = So_luong_thuc_te
                $slThucTe = (int) $ct->So_luong_thuc_te;

                $tonKho = DB::table('Ton_kho')
                    ->where('Ma_kho', $pkk->Ma_kho)
                    ->where('Ma_lo', $ct->Ma_lo)
                    ->first();

                if ($tonKho) {
                    DB::table('Ton_kho')
                        ->where('Ma_kho', $pkk->Ma_kho)
                        ->where('Ma_lo', $ct->Ma_lo)
                        ->update(['Ton_thuc' => $slThucTe]);
                } else {
                    DB::table('Ton_kho')->insert([
                        'Ma_kho' => $pkk->Ma_kho,
                        'Ma_lo' => $ct->Ma_lo,
                        'Ton_thuc' => $slThucTe,
                        'Ngay_nhap_dau_tien' => $thoiDiemDuyet,
                    ]);
                }
            }

            // 3. Chuyển phiếu sang 'Hoàn tất' (lô mở khóa)
            $pkk->update([
                'Trang_thai' => 'Hoàn tất',
                'Ma_nguoi_duyet' => $nguoiDuyet->Ma_nguoi_dung,
                'Thoi_diem_duyet' => $thoiDiemDuyet,
            ]);

            ActivityLogger::log('Duyệt kiểm kê', 'Phiếu kiểm kê', "Duyệt và điều chỉnh tồn kho theo phiếu {$pkk->Ma_phieu_kiem_ke}", $pkk);
        });
    }

    /**
     * QLK-08: Không duyệt kiểm kê (Yêu cầu đếm lại -> chuyển về 'Đang kiểm')
     */
    public static function khongDuyetKiemKe(PhieuKiemKe $pkk, NguoiDung $nguoiDuyet, string $lyDo): void
    {
        if ($pkk->Trang_thai !== 'Chờ duyệt') {
            throw new \Exception('Chỉ có thể từ chối duyệt khi phiếu ở trạng thái "Chờ duyệt".');
        }

        if (empty(trim($lyDo))) {
            throw new \Exception('Bắt buộc nhập lý do không duyệt.');
        }

        DB::transaction(function () use ($pkk, $nguoiDuyet, $lyDo) {
            $pkk->update([
                'Trang_thai' => 'Đang kiểm',
                'Ly_do_khong_duyet' => trim($lyDo),
                'Thoi_diem_dem_xong' => null, // Đặt lại null để nhân viên kho đếm lại
            ]);

            ActivityLogger::log('Từ chối duyệt kiểm kê', 'Phiếu kiểm kê', "Không duyệt phiếu {$pkk->Ma_phieu_kiem_ke}: {$lyDo}", $pkk);
        });
    }

    /**
     * QLK-08: Hủy phiếu kiểm kê (từ 'Đang kiểm' hoặc 'Chờ duyệt')
     */
    public static function huyPhieuKiemKe(PhieuKiemKe $pkk, NguoiDung $nguoiHuy, string $lyDo): void
    {
        if (!in_array($pkk->Trang_thai, ['Đang kiểm', 'Chờ duyệt'])) {
            throw new \Exception('Chỉ có thể hủy phiếu khi đang ở trạng thái "Đang kiểm" hoặc "Chờ duyệt".');
        }

        if (empty(trim($lyDo))) {
            throw new \Exception('Bắt buộc nhập lý do hủy phiếu kiểm kê.');
        }

        DB::transaction(function () use ($pkk, $nguoiHuy, $lyDo) {
            $pkk->update([
                'Trang_thai' => 'Đã hủy',
                'Ly_do_huy' => trim($lyDo),
                'Thoi_diem_huy' => now(),
            ]);

            ActivityLogger::log('Hủy phiếu kiểm kê', 'Phiếu kiểm kê', "Hủy phiếu {$pkk->Ma_phieu_kiem_ke}: {$lyDo}", $pkk);
        });
    }
}

