<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietPhieuNhap extends ChiTietModel
{
    protected $table = 'Chi_tiet_phieu_nhap';

    protected $primaryKey = 'Ma_phieu_nhap';

    protected array $khoaGhep = ['Ma_phieu_nhap', 'Ma_lo'];

    public function phieuNhapKho(): BelongsTo
    {
        return $this->belongsTo(PhieuNhapKho::class, 'Ma_phieu_nhap', 'Ma_phieu_nhap');
    }

    public function lo(): BelongsTo
    {
        return $this->belongsTo(Lo::class, 'Ma_lo', 'Ma_lo');
    }
}
