<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class CodeGenerator
{
    /**
     * Sinh mã người dùng dạng ND + 4 số (ví dụ: ND0001)
     */
    public static function generateMaNguoiDung(): string
    {
        $last = DB::table('Nguoi_dung')
            ->where('Ma_nguoi_dung', 'LIKE', 'ND%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(Ma_nguoi_dung, 3) AS UNSIGNED) DESC')
            ->value('Ma_nguoi_dung');

        $nextNumber = 1;
        if ($last && preg_match('/ND(\d+)/', $last, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        }

        return 'ND' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Sinh mã sản phẩm dạng SP + 4 số (ví dụ: SP0001)
     */
    public static function generateMaSanPham(): string
    {
        $last = DB::table('San_pham')
            ->where('Ma_san_pham', 'LIKE', 'SP%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(Ma_san_pham, 3) AS UNSIGNED) DESC')
            ->value('Ma_san_pham');

        $nextNumber = 1;
        if ($last && preg_match('/SP(\d+)/', $last, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        }

        return 'SP' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Sinh mã nhà cung cấp dạng NCC + 3 số (ví dụ: NCC001)
     */
    public static function generateMaNhaCungCap(): string
    {
        $last = DB::table('Nha_cung_cap')
            ->where('Ma_ncc', 'LIKE', 'NCC%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(Ma_ncc, 4) AS UNSIGNED) DESC')
            ->value('Ma_ncc');

        $nextNumber = 1;
        if ($last && preg_match('/NCC(\d+)/', $last, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        }

        return 'NCC' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Sinh mã khách hàng dạng KH + 4 số (ví dụ: KH0001)
     */
    public static function generateMaKhachHang(): string
    {
        $last = DB::table('Khach_hang')
            ->where('Ma_khach_hang', 'LIKE', 'KH%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(Ma_khach_hang, 3) AS UNSIGNED) DESC')
            ->value('Ma_khach_hang');

        $nextNumber = 1;
        if ($last && preg_match('/KH(\d+)/', $last, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        }

        return 'KH' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Sinh mã lô dạng L + 2 số năm + 4 số (ví dụ: L260001)
     */
    public static function generateMaLo(): string
    {
        $year2 = date('y');
        $prefix = 'L' . $year2;

        $last = DB::table('Lo')
            ->where('Ma_lo', 'LIKE', $prefix . '%')
            ->lockForUpdate()
            ->orderByRaw('CAST(SUBSTRING(Ma_lo, 4) AS UNSIGNED) DESC')
            ->value('Ma_lo');

        $nextNumber = 1;
        if ($last && preg_match('/' . $prefix . '(\d+)/', $last, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        }

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Sinh mã chứng từ theo năm: PREFIX-YYYY-NNNN
     */
    public static function generateMaChungTu(string $table, string $column, string $prefix): string
    {
        $year = date('Y');
        $like = "{$prefix}-{$year}-%";

        $last = DB::table($table)
            ->where($column, 'LIKE', $like)
            ->lockForUpdate()
            ->orderByRaw("CAST(SUBSTRING({$column}, " . (strlen($prefix) + 7) . ") AS UNSIGNED) DESC")
            ->value($column);

        $nextNumber = 1;
        if ($last && preg_match("/{$prefix}-{$year}-(\\d+)/", $last, $matches)) {
            $nextNumber = (int)$matches[1] + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $year, $nextNumber);
    }

    public static function generateMaDonDatHang(): string
    {
        return self::generateMaChungTu('Don_dat_hang', 'Ma_don_dat_hang', 'DDH');
    }

    public static function generateMaPhieuNhap(): string
    {
        return self::generateMaChungTu('Phieu_nhap_kho', 'Ma_phieu_nhap', 'PN');
    }

    public static function generateMaDonBan(): string
    {
        return self::generateMaChungTu('Don_hang_ban', 'Ma_don_hang_ban', 'DB');
    }

    public static function generateMaPhieuXuat(): string
    {
        return self::generateMaChungTu('Phieu_xuat_kho', 'Ma_phieu_xuat', 'PX');
    }

    public static function generateMaPhieuKiemKe(): string
    {
        return self::generateMaChungTu('Phieu_kiem_ke', 'Ma_phieu_kiem_ke', 'KK');
    }
}

