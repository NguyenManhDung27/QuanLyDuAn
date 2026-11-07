<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DonHangBan extends Model
{
    protected $table = 'Don_hang_ban';

    protected $primaryKey = 'Ma_don_hang_ban';

    protected $casts = [
        'Thoi_diem_xac_nhan' => 'datetime',
        'Thoi_diem_huy' => 'datetime',
    ];

    public function khachHang(): BelongsTo
    {
        return $this->belongsTo(KhachHang::class, 'Ma_khach_hang', 'Ma_khach_hang');
    }

    public function kho(): BelongsTo
    {
        return $this->belongsTo(Kho::class, 'Ma_kho', 'Ma_kho');
    }

    public function nguoiLap(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_lap', 'Ma_nguoi_dung');
    }

    public function chiTiets(): HasMany
    {
        return $this->hasMany(ChiTietDonBan::class, 'Ma_don_hang_ban', 'Ma_don_hang_ban');
    }

    public function phieuXuatKho(): HasOne
    {
        return $this->hasOne(PhieuXuatKho::class, 'Ma_don_hang_ban', 'Ma_don_hang_ban');
    }
}
