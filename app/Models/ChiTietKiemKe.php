<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChiTietKiemKe extends ChiTietModel
{
    protected $table = 'Chi_tiet_kiem_ke';

    protected $primaryKey = 'Ma_phieu_kiem_ke';

    protected array $khoaGhep = ['Ma_phieu_kiem_ke', 'Ma_lo'];

    public function phieuKiemKe(): BelongsTo
    {
        return $this->belongsTo(PhieuKiemKe::class, 'Ma_phieu_kiem_ke', 'Ma_phieu_kiem_ke');
    }

    public function lo(): BelongsTo
    {
        return $this->belongsTo(Lo::class, 'Ma_lo', 'Ma_lo');
    }

    public function getChenhLechAttribute(): ?int
    {
        if ($this->So_luong_thuc_te === null) {
            return null;
        }

        return (int) $this->So_luong_thuc_te - (int) $this->So_luong_he_thong;
    }

    public function getGiaTriChenhLechAttribute(): ?float
    {
        $cl = $this->chenh_lech;
        if ($cl === null) {
            return null;
        }

        $giaVon = $this->Gia_von_dieu_chinh ?? $this->lo?->sanPham?->Gia_von_binh_quan ?? 0;
        return $cl * (float) $giaVon;
    }
}
