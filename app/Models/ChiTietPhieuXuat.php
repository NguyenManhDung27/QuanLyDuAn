<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietPhieuXuat extends ChiTietModel
{
    protected $table = 'Chi_tiet_phieu_xuat';

    protected $primaryKey = 'Ma_phieu_xuat';

    protected array $khoaGhep = ['Ma_phieu_xuat', 'Ma_lo_phan_bo', 'Ma_lo_thuc_xuat'];

    protected $casts = [
        'Thoi_diem_doi_lo' => 'datetime',
    ];

    public function phieuXuatKho(): BelongsTo
    {
        return $this->belongsTo(PhieuXuatKho::class, 'Ma_phieu_xuat', 'Ma_phieu_xuat');
    }

    public function loPhanBo(): BelongsTo
    {
        return $this->belongsTo(Lo::class, 'Ma_lo_phan_bo', 'Ma_lo');
    }

    public function loThucXuat(): BelongsTo
    {
        return $this->belongsTo(Lo::class, 'Ma_lo_thuc_xuat', 'Ma_lo');
    }

    public function nguoiDoiLo(): BelongsTo
    {
        return $this->belongsTo(NguoiDung::class, 'Ma_nguoi_doi_lo', 'Ma_nguoi_dung');
    }
}
