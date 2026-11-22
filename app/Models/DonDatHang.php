<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class DonDatHang extends Model
{
    protected $table = 'Don_dat_hang';

    protected $primaryKey = 'Ma_don_dat_hang';

    protected $casts = [
        'Ngay_lap' => 'date',
        'Ngay_du_kien_nhan_du' => 'date',
        'Thoi_diem_duyet' => 'datetime',
        'Ngay_gui_ncc' => 'date',
        'Thoi_diem_huy' => 'datetime',
        'Thoi_diem_dong_thieu' => 'datetime',
    ];

    public function nhaCungCap(): BelongsTo
    {
        return $this->belongsTo(NhaCungCap::class, 'Ma_ncc', 'Ma_ncc');
    }

    public function kho(): BelongsTo
    {
        return $this->belongsTo(Kho::class, 'Ma_kho', 'Ma_kho');
    }

    public function nguoiLap(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_lap', 'Ma_nguoi_dung');
    }

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_lap', 'Ma_nguoi_dung');
    }

    public function nguoiDuyet(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_duyet', 'Ma_nguoi_dung');
    }

    public function nguoiGuiNcc(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_gui_ncc', 'Ma_nguoi_dung');
    }

    public function nguoiHuy(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_huy', 'Ma_nguoi_dung');
    }

    public function nguoiDongThieu(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_dong_thieu', 'Ma_nguoi_dung');
    }

    public function chiTiets(): HasMany
    {
        return $this->hasMany(ChiTietDonDatHang::class, 'Ma_don_dat_hang', 'Ma_don_dat_hang');
    }

    public function phieuNhapKhos(): HasMany
    {
        return $this->hasMany(PhieuNhapKho::class, 'Ma_don_dat_hang', 'Ma_don_dat_hang');
    }

    /**
     * F9. Nhãn "Cần xem xét" trên đơn đặt hàng
     */
    public function getCanXemXetAttribute(): bool
    {
        if (!in_array($this->Trang_thai, ['Đã gửi NCC', 'Nhận một phần'])) {
            return false;
        }

        if ($this->Ngay_du_kien_nhan_du && $this->Ngay_du_kien_nhan_du->lt(now()->startOfDay())) {
            return true;
        }

        return $this->phieuNhapKhos()
            ->where('Trang_thai', 'Đã nhập kho')
            ->where('Dot_giao_cuoi', true)
            ->exists();
    }

    /**
     * Tổng số lượng đặt (tất cả các dòng)
     */
    public function getTongSoLuongDatAttribute(): int
    {
        return (int) $this->chiTiets()->sum('So_luong_dat');
    }

    /**
     * Tổng số lượng đã nhận qua các phiếu nhập đã xác nhận
     */
    public function getTongDaNhanAttribute(): int
    {
        return (int) DB::table('Chi_tiet_phieu_nhap as ctn')
            ->join('Phieu_nhap_kho as pn', 'pn.Ma_phieu_nhap', '=', 'ctn.Ma_phieu_nhap')
            ->where('pn.Ma_don_dat_hang', $this->Ma_don_dat_hang)
            ->where('pn.Trang_thai', 'Đã nhập kho')
            ->sum('ctn.So_luong_nhap');
    }

    /**
     * Lấy tiến độ chi tiết theo từng sản phẩm
     * Trả về mảng: [ Ma_san_pham => [ 'da_nhan' => int, 'hao_hut' => int, 'con_lai' => int ] ]
     */
    public function getTienDoChiTietMap(): array
    {
        $rows = DB::table('Chi_tiet_phieu_nhap as ctn')
            ->join('Phieu_nhap_kho as pn', 'pn.Ma_phieu_nhap', '=', 'ctn.Ma_phieu_nhap')
            ->join('Lo as l', 'l.Ma_lo', '=', 'ctn.Ma_lo')
            ->where('pn.Ma_don_dat_hang', $this->Ma_don_dat_hang)
            ->where('pn.Trang_thai', 'Đã nhập kho')
            ->select(
                'l.Ma_san_pham',
                DB::raw('SUM(ctn.So_luong_nhap) as da_nhan'),
                DB::raw('SUM(ctn.So_luong_hao_hut) as hao_hut')
            )
            ->groupBy('l.Ma_san_pham')
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $map[$r->Ma_san_pham] = [
                'da_nhan' => (int) $r->da_nhan,
                'hao_hut' => (int) $r->hao_hut,
            ];
        }

        $result = [];
        foreach ($this->chiTiets as $ct) {
            $sp = $ct->Ma_san_pham;
            $daNhan = $map[$sp]['da_nhan'] ?? 0;
            $haoHut = $map[$sp]['hao_hut'] ?? 0;
            $conLai = max(0, (int) $ct->So_luong_dat - ($daNhan + $haoHut));
            $result[$sp] = [
                'da_nhan' => $daNhan,
                'hao_hut' => $haoHut,
                'con_lai' => $conLai,
            ];
        }

        return $result;
    }
}
