<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lo extends Model
{
    protected $table = 'Lo';

    protected $primaryKey = 'Ma_lo';

    protected $casts = [
        'Ngay_san_xuat' => 'date',
        'Han_su_dung' => 'date',
    ];

    public function sanPham(): BelongsTo
    {
        return $this->belongsTo(SanPham::class, 'Ma_san_pham', 'Ma_san_pham');
    }

    public function tonKhos(): HasMany
    {
        return $this->hasMany(TonKho::class, 'Ma_lo', 'Ma_lo');
    }
}
