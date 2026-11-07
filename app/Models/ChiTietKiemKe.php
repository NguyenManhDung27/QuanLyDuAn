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
}
