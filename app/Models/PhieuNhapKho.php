<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhieuNhapKho extends Model
{
    protected $table = 'Phieu_nhap_kho';

    protected $primaryKey = 'Ma_phieu_nhap';

    protected $casts = [
        'Ngay_lap' => 'date',
        'Thoi_diem_xac_nhan' => 'datetime',
        'Dot_giao_cuoi' => 'boolean',
    ];

    public function kho(): BelongsTo
    {
        return $this->belongsTo(Kho::class, 'Ma_kho', 'Ma_kho');
    }

    public function donDatHang(): BelongsTo
    {
        return $this->belongsTo(DonDatHang::class, 'Ma_don_dat_hang', 'Ma_don_dat_hang');
    }

    public function phieuXuatKho(): BelongsTo
    {
        return $this->belongsTo(PhieuXuatKho::class, 'Ma_phieu_xuat', 'Ma_phieu_xuat');
    }

    public function nguoiLap(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_lap', 'Ma_nguoi_dung');
    }

    public function chiTiets(): HasMany
    {
        return $this->hasMany(ChiTietPhieuNhap::class, 'Ma_phieu_nhap', 'Ma_phieu_nhap');
    }
}
