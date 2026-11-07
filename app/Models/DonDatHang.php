<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
