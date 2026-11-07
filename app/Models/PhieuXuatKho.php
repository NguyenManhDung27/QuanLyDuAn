<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhieuXuatKho extends Model
{
    protected $table = 'Phieu_xuat_kho';

    protected $primaryKey = 'Ma_phieu_xuat';

    protected $casts = [
        'Ngay_tao' => 'date',
        'Thoi_diem_xuat_kho' => 'datetime',
        'Thoi_diem_ket_qua_giao' => 'datetime',
        'Thoi_diem_xu_ly_hoan' => 'datetime',
    ];

    public function donHangBan(): BelongsTo
    {
        return $this->belongsTo(DonHangBan::class, 'Ma_don_hang_ban', 'Ma_don_hang_ban');
    }

    public function nguoiXuat(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_xuat', 'Ma_nguoi_dung');
    }

    public function chiTiets(): HasMany
    {
        return $this->hasMany(ChiTietPhieuXuat::class, 'Ma_phieu_xuat', 'Ma_phieu_xuat');
    }

    public function phieuNhapKhos(): HasMany
    {
        return $this->hasMany(PhieuNhapKho::class, 'Ma_phieu_xuat', 'Ma_phieu_xuat');
    }
}
