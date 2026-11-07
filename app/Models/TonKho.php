<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TonKho extends ChiTietModel
{
    protected $table = 'Ton_kho';

    protected $primaryKey = 'Ma_kho';

    protected array $khoaGhep = ['Ma_kho', 'Ma_lo'];

    protected $casts = [
        'Ngay_nhap_dau_tien' => 'datetime',
    ];

    public function kho(): BelongsTo
    {
        return $this->belongsTo(Kho::class, 'Ma_kho', 'Ma_kho');
    }

    public function lo(): BelongsTo
    {
        return $this->belongsTo(Lo::class, 'Ma_lo', 'Ma_lo');
    }
}
