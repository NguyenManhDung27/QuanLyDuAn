<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhieuKiemKe extends Model
{
    protected $table = 'Phieu_kiem_ke';

    protected $primaryKey = 'Ma_phieu_kiem_ke';

    protected $casts = [
        'Thoi_diem_tao' => 'datetime',
        'Thoi_diem_dem_xong' => 'datetime',
        'Thoi_diem_duyet' => 'datetime',
        'Thoi_diem_huy' => 'datetime',
    ];

    public function kho(): BelongsTo
    {
        return $this->belongsTo(Kho::class, 'Ma_kho', 'Ma_kho');
    }

    public function nguoiTao(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_tao', 'Ma_nguoi_dung');
    }

    public function nguoiKiem(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_kiem', 'Ma_nguoi_dung');
    }

    public function nguoiDuyet(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_duyet', 'Ma_nguoi_dung');
    }

    public function chiTiets(): HasMany
    {
        return $this->hasMany(ChiTietKiemKe::class, 'Ma_phieu_kiem_ke', 'Ma_phieu_kiem_ke');
    }
}
